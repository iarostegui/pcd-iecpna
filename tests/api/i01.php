<?php

declare(strict_types=1);

/**
 * Runner HTTP I01 — autenticación, sesiones, contexto, sedes y GPS.
 *
 * Uso:
 *   php tests/api/i01.php --base-url=http://127.0.0.1:8081/api/v1 --json=i01-evidencia.json
 *
 * Requiere MySQL real, esquema 001, seed 002 y aprovisionamiento UAT. Las
 * credenciales vienen del entorno (PCD_UAT_DIGITADOR_PASSWORD, etc.).
 * No imprime ni registra contraseñas.
 *
 * Estados: PASS, FAIL, BLOCKED. Sale 1 si existe FAIL o BLOCKED.
 */

$baseUrl = 'http://127.0.0.1:8081/api/v1';
$jsonPath = null;

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--base-url=')) {
        $baseUrl = substr($argument, strlen('--base-url='));
    }
    if (str_starts_with($argument, '--json=')) {
        $jsonPath = substr($argument, strlen('--json='));
    }
}

$baseUrl = rtrim($baseUrl, '/');
$sessionName = getenv('PCD_SESSION_NAME') ?: 'pcd_test_sid';
$origin = 'http://localhost:5173';
$suffix = strtoupper(bin2hex(random_bytes(3)));

$passwords = [
    'DIGITADOR' => getenv('PCD_UAT_DIGITADOR_PASSWORD') ?: '',
    'ADMIN' => getenv('PCD_ADMIN_PASSWORD') ?: '',
    'REPORTES' => getenv('PCD_REPORTES_PASSWORD') ?: '',
];

$db = db_connect();

/** @return array{status:int, headers:array<string,string>, body:string, json:mixed, error:string} */
function http_request(string $method, string $url, array $headers = [], ?string $body = null, ?string $cookieFile = null): array
{
    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
    ]);

    if ($headers !== []) {
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
    }

    if ($body !== null) {
        curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
    }

    if ($cookieFile !== null) {
        curl_setopt($handle, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($handle, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'HEAD') {
        curl_setopt($handle, CURLOPT_NOBODY, true);
    }

    $raw = (string) curl_exec($handle);
    $error = (string) curl_error($handle);
    $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
    $headerSize = (int) curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    curl_close($handle);

    $rawHeaders = substr($raw, 0, $headerSize);
    $responseBody = substr($raw, $headerSize);
    $parsedHeaders = [];

    foreach (preg_split('/\R/', $rawHeaders) ?: [] as $line) {
        if (!str_contains($line, ':')) {
            continue;
        }

        [$name, $value] = explode(':', $line, 2);
        $parsedHeaders[strtolower(trim($name))] = trim($value);
    }

    return [
        'status' => $status,
        'headers' => $parsedHeaders,
        'body' => $responseBody,
        'json' => json_decode($responseBody, true),
        'error' => $error,
    ];
}

/** @return array<int, array{status:int, body:string, json:mixed, error:string}> */
function http_parallel(array $requests): array
{
    $multi = curl_multi_init();
    $handles = [];

    foreach ($requests as $index => $request) {
        $handle = curl_init($request['url']);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $request['method'] ?? 'POST',
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => $request['headers'] ?? [],
        ]);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $request['body'] ?? '');
        if (isset($request['cookie'])) {
            curl_setopt($handle, CURLOPT_COOKIEJAR, $request['cookie']);
            curl_setopt($handle, CURLOPT_COOKIEFILE, $request['cookie']);
        }
        curl_multi_add_handle($multi, $handle);
        $handles[$index] = $handle;
    }

    $running = null;
    do {
        curl_multi_exec($multi, $running);
        if ($running > 0) {
            curl_multi_select($multi, 1.0);
        }
    } while ($running > 0);

    $results = [];
    foreach ($handles as $index => $handle) {
        $body = (string) curl_multi_getcontent($handle);
        $results[$index] = [
            'status' => (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE),
            'body' => $body,
            'json' => json_decode($body, true),
            'error' => (string) curl_error($handle),
        ];
        curl_multi_remove_handle($multi, $handle);
        curl_close($handle);
    }
    curl_multi_close($multi);
    ksort($results);

    return $results;
}

function header_value(array $response, string $name): string
{
    return $response['headers'][strtolower($name)] ?? '';
}

function json_path(array $response, string $path): mixed
{
    $value = $response['json'];

    foreach (explode('.', $path) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }

        $value = $value[$segment];
    }

    return $value;
}

function cookie_value(string $cookieFile, string $name): ?string
{
    if (!is_file($cookieFile)) {
        return null;
    }

    foreach (preg_split('/\R/', (string) file_get_contents($cookieFile)) ?: [] as $line) {
        if ($line === '') {
            continue;
        }
        // curl marca las cookies HttpOnly con el prefijo #HttpOnly_.
        if (str_starts_with($line, '#HttpOnly_')) {
            $line = substr($line, strlen('#HttpOnly_'));
        } elseif (str_starts_with($line, '#')) {
            continue;
        }
        $parts = preg_split('/\t/', $line);
        if ($parts !== false && count($parts) >= 7 && $parts[5] === $name) {
            return $parts[6];
        }
    }

    return null;
}

function db_connect(): ?PDO
{
    $host = getenv('PCD_DB_HOST');
    if ($host === false || $host === '') {
        return null;
    }

    try {
        $pdo = new PDO(
            sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $host,
                (int) (getenv('PCD_DB_PORT') ?: 3306),
                (string) (getenv('PCD_DB_NAME') ?: ''),
            ),
            (string) (getenv('PCD_DB_USER') ?: ''),
            (string) (getenv('PCD_DB_PASSWORD') ?: ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
        $pdo->exec("SET time_zone = '+00:00'");

        return $pdo;
    } catch (Throwable) {
        return null;
    }
}

function db_one(?PDO $pdo, string $sql, array $params = []): ?array
{
    if ($pdo === null) {
        return null;
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();

    return $row === false ? null : $row;
}

function db_exec(?PDO $pdo, string $sql, array $params = []): void
{
    if ($pdo === null) {
        return;
    }
    $statement = $pdo->prepare($sql);
    $statement->execute($params);
}

/**
 * @return array{jar:string, response:array, auth:?array, csrf:?string}
 */
function login(int $presbiterioId, string $codigo, string $password, string $suffix): array
{
    global $baseUrl;

    $jar = sys_get_temp_dir() . '/pcd-i01-' . $suffix . '-' . bin2hex(random_bytes(4)) . '.txt';
    @unlink($jar);

    $response = http_request(
        'POST',
        $baseUrl . '/auth/login',
        ['Content-Type: application/json', 'Origin: http://localhost:5173'],
        (string) json_encode(['presbiterio_id' => $presbiterioId, 'usuario' => $codigo, 'password' => $password]),
        $jar,
    );

    $auth = is_array($response['json']) ? $response['json'] : null;
    $csrf = $auth !== null && isset($auth['csrf_token']) && is_string($auth['csrf_token']) ? $auth['csrf_token'] : null;

    return ['jar' => $jar, 'response' => $response, 'auth' => $auth, 'csrf' => $csrf];
}

function csrf_token(array $session): ?string
{
    global $baseUrl;

    if (is_string($session['csrf'] ?? null) && $session['csrf'] !== '') {
        return $session['csrf'];
    }

    $response = http_request('GET', $baseUrl . '/auth/me', [], null, $session['jar']);

    return is_array($response['json']) && isset($response['json']['csrf_token'])
        ? (string) $response['json']['csrf_token']
        : null;
}

function auth_get(string $path, array $session): array
{
    global $baseUrl;

    return http_request('GET', $baseUrl . $path, [], null, $session['jar']);
}

function auth_write(string $method, string $path, array $session, mixed $body, array $extraHeaders = []): array
{
    global $baseUrl;

    $headers = ['Content-Type: application/json'];
    $token = csrf_token($session);
    if ($token !== null) {
        $headers[] = 'X-CSRF-Token: ' . $token;
    }
    foreach ($extraHeaders as $header) {
        $headers[] = $header;
    }

    return http_request($method, $baseUrl . $path, $headers, (string) json_encode($body), $session['jar']);
}

function presbiterio_ids(?PDO $pdo): array
{
    if ($pdo === null) {
        return [];
    }
    $rows = $pdo->query("SELECT id, codigo FROM organizacion WHERE tipo='PRESBITERIO' AND activo=1")->fetchAll();
    $result = [];
    foreach ($rows as $row) {
        $result[(string) $row['codigo']] = (int) $row['id'];
    }

    return $result;
}

function cleanup_login_events(?PDO $pdo, string $like = 'I01QA%'): void
{
    if ($pdo === null) {
        return;
    }
    db_exec($pdo, 'DELETE FROM login_evento WHERE codigo_intentado LIKE ?', [$like]);
}

function cleanup_code_events(?PDO $pdo, string $codigo): void
{
    if ($pdo === null) {
        return;
    }
    db_exec($pdo, 'DELETE FROM login_evento WHERE codigo_intentado = ?', [$codigo]);
}

function cleanup_ip_events(?PDO $pdo, ?string $ip): void
{
    if ($pdo === null || $ip === null) {
        return;
    }
    db_exec($pdo, "DELETE FROM login_evento WHERE ip = INET6_ATON(?) AND resultado = 'FALLIDO'", [$ip]);
}

function assert_keys_only(array $payload, array $allowed): bool
{
    foreach (array_keys($payload) as $key) {
        if (!in_array($key, $allowed, true)) {
            return false;
        }
    }

    return true;
}

function contains_sensitive_coordinates(mixed $value): bool
{
    if (is_array($value)) {
        foreach (array_keys($value) as $key) {
            if (in_array((string) $key, ['latitud', 'longitud', 'geo_latitud', 'geo_longitud', 'precision_m'], true)) {
                return true;
            }
        }
        foreach ($value as $item) {
            if (contains_sensitive_coordinates($item)) {
                return true;
            }
        }
    }

    return false;
}

$requiredPasswords = $passwords['DIGITADOR'] !== '' && $passwords['ADMIN'] !== '' && $passwords['REPORTES'] !== '';
$presbiterios = presbiterio_ids($db);

if ($db === null) {
    fwrite(STDERR, "I01 runner: MySQL no disponible; no se puede ejecutar la suite obligatoria." . PHP_EOL);
}

if (!$requiredPasswords) {
    fwrite(STDERR, "I01 runner: faltan PCD_UAT_DIGITADOR_PASSWORD / PCD_ADMIN_PASSWORD / PCD_REPORTES_PASSWORD." . PHP_EOL);
}

$lim = $presbiterios['LIM'] ?? 1;
$tru = $presbiterios['TRU'] ?? 2;
$chi = $presbiterios['CHI'] ?? 3;
$ipLocal = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';

// Evita que residuos de una ejecución previa alteren los umbrales.
cleanup_login_events($db, 'I01QA%');
cleanup_ip_events($db, '127.0.0.1');

$checks = [];
$run = static function (string $id, string $ca, callable $work) use (&$checks): void {
    try {
        [$status, $detail] = $work();
    } catch (Throwable $exception) {
        $status = 'FAIL';
        $detail = get_class($exception) . ': ' . $exception->getMessage();
    }

    $checks[] = ['id' => $id, 'ca' => $ca, 'status' => $status, 'detail' => $detail];
};

$run('CA-56_presbiterios_publicos', 'CA-56', static function () use ($baseUrl): array {
    $response = http_request('GET', $baseUrl . '/presbiterios');
    if ($response['status'] !== 200 || !is_array($response['json'])) {
        return ['FAIL', 'HTTP ' . $response['status']];
    }
    if (count($response['json']) < 4) {
        return ['FAIL', 'lista incompleta: ' . count($response['json'])];
    }
    foreach ($response['json'] as $item) {
        if (!is_array($item) || !assert_keys_only($item, ['id', 'codigo', 'nombre'])) {
            return ['FAIL', 'proyección con claves no públicas'];
        }
        if (!isset($item['id'], $item['codigo'], $item['nombre'])) {
            return ['FAIL', 'proyección incompleta'];
        }
    }
    foreach (['password', 'usuario', 'usuarios', 'hash', 'codigo_usuario'] as $needle) {
        if (str_contains(strtolower($response['body']), $needle)) {
            return ['FAIL', 'expone ' . $needle];
        }
    }

    return ['PASS', '4+ presbiterios activos, solo id/codigo/nombre'];
});

$run('CA-01_login_sin_presbiterio', 'CA-01', static function () use ($baseUrl): array {
    $response = http_request('POST', $baseUrl . '/auth/login', ['Content-Type: application/json'], (string) json_encode(['usuario' => 'LIM-DIG-01', 'password' => 'x']));
    $cookie = header_value($response, 'set-cookie');

    return $response['status'] === 422 && json_path($response, 'error.code') === 'validation_error' && $cookie === ''
        ? ['PASS', '422 sin Set-Cookie']
        : ['FAIL', 'HTTP ' . $response['status'] . ' cookie=' . ($cookie !== '' ? 'sí' : 'no')];
});

$run('CA-57_sin_enumeracion', 'CA-57', static function () use ($baseUrl): array {
    $wrongExisting = http_request(
        'POST',
        $baseUrl . '/auth/login',
        ['Content-Type: application/json'],
        (string) json_encode(['presbiterio_id' => 3, 'usuario' => 'CHI-DIG-01', 'password' => 'definitivamente-incorrecta']),
    );
    $wrongMissing = http_request(
        'POST',
        $baseUrl . '/auth/login',
        ['Content-Type: application/json'],
        (string) json_encode(['presbiterio_id' => 3, 'usuario' => 'I01QA-NOEXISTE', 'password' => 'definitivamente-incorrecta']),
    );

    $same = $wrongExisting['status'] === $wrongMissing['status']
        && json_path($wrongExisting, 'error.code') === json_path($wrongMissing, 'error.code')
        && json_path($wrongExisting, 'error.message') === json_path($wrongMissing, 'error.message')
        && header_value($wrongExisting, 'set-cookie') === ''
        && header_value($wrongMissing, 'set-cookie') === '';

    return $same
        ? ['PASS', '401 indistinguible, sin cookie']
        : ['FAIL', 'respuestas distinguibles'];
});

$run('CA-02_presbiterio_ajeno', 'CA-02', static function () use ($lim, $tru, $passwords): array {
    $session = login($tru, 'LIM-DIG-01', $passwords['DIGITADOR'], 'ajeno-' . bin2hex(random_bytes(3)));
    $response = $session['response'];

    return $response['status'] === 401 && json_path($response, 'error.code') === 'invalid_credentials' && header_value($response, 'set-cookie') === ''
        ? ['PASS', '401 genérico, sin sesión']
        : ['FAIL', 'HTTP ' . $response['status']];
});

$run('CA-05_usuario_inactivo', 'CA-05', static function () use ($db, $lim, $passwords): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    db_exec($db, "UPDATE usuario SET activo=0 WHERE codigo='LIM-DIG-05'");

    try {
        $session = login($lim, 'LIM-DIG-05', $passwords['DIGITADOR'], 'inactivo-' . bin2hex(random_bytes(3)));
        $response = $session['response'];
        $event = db_one($db, "SELECT resultado FROM login_evento WHERE codigo_intentado='LIM-DIG-05' ORDER BY id DESC LIMIT 1");

        return $response['status'] === 401
            && json_path($response, 'error.code') === 'invalid_credentials'
            && (($event['resultado'] ?? null) === 'FALLIDO')
            ? ['PASS', '401 + login_evento FALLIDO']
            : ['FAIL', 'HTTP ' . $response['status'] . ' evento=' . (string) ($event['resultado'] ?? '—')];
    } finally {
        db_exec($db, "UPDATE usuario SET activo=1 WHERE codigo='LIM-DIG-05'");
    }
});

$run('CA-03_cuentas_por_presbiterio', 'CA-03', static function () use ($db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $rows = $db->query("SELECT presbiterio_id, COUNT(*) total FROM usuario WHERE perfil='DIGITADOR' AND activo=1 GROUP BY presbiterio_id")->fetchAll();
    if (count($rows) < 4) {
        return ['FAIL', 'presbiterios con digitadores: ' . count($rows)];
    }
    foreach ($rows as $row) {
        if ((int) $row['total'] !== 6) {
            return ['FAIL', 'presbiterio ' . $row['presbiterio_id'] . ' tiene ' . $row['total']];
        }
    }

    return ['PASS', '6 DIGITADORES activos por presbiterio'];
});

$run('CA-04_admin_reportes_nacional', 'CA-04', static function () use ($lim, $passwords): array {
    $admin = login($lim, 'ADMIN-NAC', $passwords['ADMIN'], 'admin-' . bin2hex(random_bytes(3)));
    $reportes = login($lim, 'REPORTES-NAC', $passwords['REPORTES'], 'rep-' . bin2hex(random_bytes(3)));
    $adminOk = $admin['response']['status'] === 200 && ($admin['auth']['perfil'] ?? null) === 'ADMIN';
    $reportesOk = $reportes['response']['status'] === 200
        && ($reportes['auth']['perfil'] ?? null) === 'REPORTES'
        && ($reportes['auth']['sede_id'] ?? null) === null;

    return $adminOk && $reportesOk
        ? ['PASS', 'ADMIN y REPORTES nacionales autenticados']
        : ['FAIL', 'ADMIN o REPORTES no autenticaron con perfil esperado'];
});

$run('CA-06_hash_sin_texto_claro', 'CA-06', static function () use ($db, $lim, $passwords): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $row = db_one($db, "SELECT password_hash FROM usuario WHERE codigo='LIM-DIG-01'");
    $hash = (string) ($row['password_hash'] ?? '');
    $isStrong = str_starts_with($hash, '$argon2id$') || preg_match('/^\$2[ay]\$(1[2-9]|[2-9][0-9])\$/', $hash) === 1;
    $session = login($lim, 'LIM-DIG-01', $passwords['DIGITADOR'], 'hash-' . bin2hex(random_bytes(3)));

    if (!$isStrong || $hash === $passwords['DIGITADOR']) {
        return ['FAIL', 'hash ausente/débil o texto claro'];
    }
    if (str_contains($session['response']['body'], $passwords['DIGITADOR']) || str_contains($session['response']['body'], $hash)) {
        return ['FAIL', 'respuesta expone credencial/hash'];
    }

    return ['PASS', 'hash fuerte, sin texto claro en BD ni respuesta'];
});

$run('CA-58_secretos_separados', 'CA-58', static function () use ($lim, $passwords, $db): array {
    $adminWrong = login($lim, 'ADMIN-NAC', $passwords['DIGITADOR'], 'admin-wrong-' . bin2hex(random_bytes(3)));
    $digWrong = login($lim, 'LIM-DIG-01', $passwords['ADMIN'], 'dig-wrong-' . bin2hex(random_bytes(3)));
    $hashes = $db === null ? [] : $db->query("SELECT codigo, password_hash FROM usuario WHERE codigo IN ('ADMIN-NAC','REPORTES-NAC','LIM-DIG-01')")->fetchAll();
    $distinct = count($hashes) === 3 && count(array_unique(array_column($hashes, 'password_hash'))) === 3;

    return $adminWrong['response']['status'] === 401
        && $digWrong['response']['status'] === 401
        && $distinct
        ? ['PASS', 'claves cruzadas rechazadas; hashes distintos']
        : ['FAIL', 'claves o hashes no separados'];
});

$run('CA-08_sedes_del_presbiterio', 'CA-08', static function () use ($lim, $tru, $passwords, $suffix): array {
    $limSession = login($lim, 'LIM-DIG-01', $passwords['DIGITADOR'], 'lim-sedes-' . bin2hex(random_bytes(3)));
    $truSession = login($tru, 'TRU-DIG-01', $passwords['DIGITADOR'], 'tru-sedes-' . bin2hex(random_bytes(3)));

    $limSede = auth_write('POST', '/sedes', $limSession, ['nombre_oficial' => 'I01QA LIM ' . $suffix], ['Idempotency-Key: lim-' . $suffix]);
    $truSede = auth_write('POST', '/sedes', $truSession, ['nombre_oficial' => 'I01QA TRU ' . $suffix], ['Idempotency-Key: tru-' . $suffix]);

    if ($limSede['status'] !== 201 || $truSede['status'] !== 201) {
        return ['FAIL', 'no se pudieron crear sedes de prueba'];
    }

    $list = auth_get('/sedes', $limSession);
    $items = json_path($list, 'items');
    if (!is_array($items)) {
        return ['FAIL', 'respuesta de sedes inválida'];
    }
    foreach ($items as $item) {
        if ((int) ($item['presbiterio_id'] ?? 0) !== $lim) {
            return ['FAIL', 'sede de otro presbiterio en la lista'];
        }
    }

    return ['PASS', 'solo sedes del presbiterio LIM'];
});

$run('CA-10_alta_y_seleccion_sede', 'CA-10', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-02', $passwords['DIGITADOR'], 'alta-' . bin2hex(random_bytes(3)));
    $name = 'I01QA Alta ' . $suffix;
    $created = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $name], ['Idempotency-Key: alta-' . $suffix]);

    if ($created['status'] !== 201 || !is_array($created['json']) || !isset($created['json']['id'])) {
        return ['FAIL', 'HTTP ' . $created['status']];
    }

    $sedeId = (int) $created['json']['id'];
    $selected = auth_write('PUT', '/contexto/sede', $session, ['sede_id' => $sedeId]);

    return $selected['status'] === 200 && (int) json_path($selected, 'sede_id') === $sedeId
        ? ['PASS', '201 + contexto actualizado sin aprobación']
        : ['FAIL', 'contexto HTTP ' . $selected['status']];
});

$run('CA-09_sede_fuera_de_presbiterio', 'CA-09', static function () use ($lim, $passwords, $db, $suffix): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-03', $passwords['DIGITADOR'], 'ajena-' . bin2hex(random_bytes(3)));
    $truSede = db_one($db, "SELECT id FROM organizacion WHERE tipo='SEDE' AND nombre LIKE 'I01QA TRU %' ORDER BY id DESC LIMIT 1");
    if ($truSede === null) {
        return ['BLOCKED', 'no hay sede TRU de prueba'];
    }
    $before = auth_get('/auth/me', $session);
    $response = auth_write('PUT', '/contexto/sede', $session, ['sede_id' => (int) $truSede['id']]);
    $after = auth_get('/auth/me', $session);

    return $response['status'] === 403
        && json_path($response, 'error.code') === 'sede_fuera_de_contexto'
        && (int) json_path($before, 'presbiterio_id') === (int) json_path($after, 'presbiterio_id')
        ? ['PASS', '403 y contexto intacto']
        : ['FAIL', 'HTTP ' . $response['status']];
});

$run('CA-11_duplicado_normalizado', 'CA-11', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-04', $passwords['DIGITADOR'], 'dup-' . bin2hex(random_bytes(3)));
    $base = 'I01QA Sede Jesús María ' . $suffix;
    $first = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $base], ['Idempotency-Key: dup-a-' . $suffix]);
    $second = auth_write('POST', '/sedes', $session, ['nombre_oficial' => '  i01qa   sede JESUS MARIA ' . $suffix . '  '], ['Idempotency-Key: dup-b-' . $suffix]);

    return $first['status'] === 201
        && $second['status'] === 409
        && json_path($second, 'error.code') === 'sede_duplicada'
        ? ['PASS', '409 por igualdad normalizada (tildes/espacios/caso)']
        : ['FAIL', 'HTTP ' . $first['status'] . '/' . $second['status']];
});

$run('CA-12_similitud_solo_advierte', 'CA-12', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-06', $passwords['DIGITADOR'], 'sim-' . bin2hex(random_bytes(3)));
    $base = 'I01QA Sede Central ' . $suffix;
    $first = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $base], ['Idempotency-Key: sim-a-' . $suffix]);
    $second = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $base . ' Anexo'], ['Idempotency-Key: sim-b-' . $suffix]);

    return $first['status'] === 201
        && $second['status'] === 201
        && is_string(json_path($second, 'advertencia'))
        && json_path($second, 'advertencia') !== ''
        ? ['PASS', '201 con advertencia, sin bloqueo']
        : ['FAIL', 'HTTP ' . $first['status'] . '/' . $second['status']];
});

$run('CA-13_concurrencia_sede', 'CA-13', static function () use ($lim, $passwords, $suffix): array {
    $a = login($lim, 'LIM-DIG-01', $passwords['DIGITADOR'], 'conc-a-' . bin2hex(random_bytes(3)));
    $b = login($lim, 'LIM-DIG-02', $passwords['DIGITADOR'], 'conc-b-' . bin2hex(random_bytes(3)));
    $name = 'I01QA Concurrente ' . $suffix;

    $requests = [
        [
            'method' => 'POST',
            'url' => $GLOBALS['baseUrl'] . '/sedes',
            'headers' => [
                'Content-Type: application/json',
                'X-CSRF-Token: ' . (string) csrf_token($a),
                'Idempotency-Key: conc-a-' . $suffix,
            ],
            'body' => (string) json_encode(['nombre_oficial' => $name]),
            'cookie' => $a['jar'],
        ],
        [
            'method' => 'POST',
            'url' => $GLOBALS['baseUrl'] . '/sedes',
            'headers' => [
                'Content-Type: application/json',
                'X-CSRF-Token: ' . (string) csrf_token($b),
                'Idempotency-Key: conc-b-' . $suffix,
            ],
            'body' => (string) json_encode(['nombre_oficial' => $name]),
            'cookie' => $b['jar'],
        ],
    ];
    $results = http_parallel($requests);
    $statuses = array_map(static fn (array $r): int => $r['status'], $results);
    sort($statuses);

    return $statuses === [201, 409]
        ? ['PASS', 'solo una creación (201/409)']
        : ['FAIL', 'estados ' . implode('/', $statuses)];
});

$run('CA-112_idempotencia_sede', 'CA-112', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-03', $passwords['DIGITADOR'], 'idem-' . bin2hex(random_bytes(3)));
    $name = 'I01QA Idempotente ' . $suffix;
    $key = 'idem-key-' . $suffix;
    $first = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $name], ['Idempotency-Key: ' . $key]);
    $replay = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $name], ['Idempotency-Key: ' . $key]);
    $conflict = auth_write('POST', '/sedes', $session, ['nombre_oficial' => $name . ' otro'], ['Idempotency-Key: ' . $key]);

    return $first['status'] === 201
        && $replay['status'] === 201
        && (int) json_path($replay, 'id') === (int) json_path($first, 'id')
        && $conflict['status'] === 409
        && json_path($conflict, 'error.code') === 'idempotency_conflict'
        ? ['PASS', 'replay idéntico y conflicto de payload']
        : ['FAIL', 'estados ' . $first['status'] . '/' . $replay['status'] . '/' . $conflict['status']];
});

$run('CA-63_csrf_y_me', 'CA-63', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-04', $passwords['DIGITADOR'], 'csrf-' . bin2hex(random_bytes(3)));
    $me = auth_get('/auth/me', $session);
    $token = csrf_token($session);
    $noToken = http_request(
        'POST',
        $GLOBALS['baseUrl'] . '/sedes',
        ['Content-Type: application/json', 'Idempotency-Key: csrf-no-' . $suffix],
        (string) json_encode(['nombre_oficial' => 'I01QA CSRF ' . $suffix]),
        $session['jar'],
    );
    $withToken = auth_write('POST', '/sedes', $session, ['nombre_oficial' => 'I01QA CSRF ' . $suffix], ['Idempotency-Key: csrf-yes-' . $suffix]);

    return $me['status'] === 200
        && is_string(json_path($me, 'csrf_token'))
        && $noToken['status'] === 403
        && json_path($noToken, 'error.code') === 'csrf_invalid'
        && $withToken['status'] === 201
        ? ['PASS', 'CSRF recuperable y obligatorio']
        : ['FAIL', 'me=' . $me['status'] . ' sinToken=' . $noToken['status'] . ' conToken=' . $withToken['status']];
});

$run('I01-O03_cookie_flags', 'I01-O03', static function () use ($lim, $passwords, $sessionName): array {
    $session = login($lim, 'LIM-DIG-04', $passwords['DIGITADOR'], 'o03-' . bin2hex(random_bytes(3)));
    $cookie = header_value($session['response'], 'set-cookie');
    $lower = strtolower($cookie);
    $expectedSecure = getenv('PCD_ENV') === 'production'
        || in_array(strtolower((string) getenv('PCD_COOKIE_SECURE')), ['1', 'true', 'yes', 'on'], true);

    $ok = $cookie !== ''
        && str_starts_with($cookie, $sessionName . '=')
        && str_contains($lower, 'httponly')
        && str_contains($lower, 'samesite=lax')
        && str_contains($lower, 'path=/')
        && !str_contains($lower, 'domain=')
        && (!$expectedSecure || str_contains($lower, 'secure'));

    return $ok
        ? ['PASS', 'cookie HttpOnly/SameSite=Lax/Path=/ sin Domain; Secure ' . ($expectedSecure ? 'verificado' : 'cubierto por prueba de producción')]
        : ['FAIL', 'atributos incompletos: ' . $cookie];
});

$run('CA-14_cambio_sede_conserva_presbiterio', 'CA-14', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-05', $passwords['DIGITADOR'], 'ctx-' . bin2hex(random_bytes(3)));
    $a = auth_write('POST', '/sedes', $session, ['nombre_oficial' => 'I01QA Contexto A ' . $suffix], ['Idempotency-Key: ctx-a-' . $suffix]);
    $b = auth_write('POST', '/sedes', $session, ['nombre_oficial' => 'I01QA Contexto B ' . $suffix], ['Idempotency-Key: ctx-b-' . $suffix]);
    if ($a['status'] !== 201 || $b['status'] !== 201) {
        return ['FAIL', 'no se crearon sedes de contexto'];
    }
    $first = auth_write('PUT', '/contexto/sede', $session, ['sede_id' => (int) $a['json']['id']]);
    $geo = auth_write('POST', '/auth/ubicacion', $session, ['estado' => 'DENEGADA', 'capturada_en' => gmdate('Y-m-d\TH:i:s\Z')]);
    $second = auth_write('PUT', '/contexto/sede', $session, ['sede_id' => (int) $b['json']['id']]);
    $geoAgain = auth_write('POST', '/auth/ubicacion', $session, ['estado' => 'OK', 'latitud' => -12.0, 'longitud' => -77.0, 'capturada_en' => gmdate('Y-m-d\TH:i:s\Z')]);
    $me = auth_get('/auth/me', $session);

    return $first['status'] === 200
        && $geo['status'] === 200
        && $second['status'] === 200
        && (int) json_path($second, 'presbiterio_id') === $lim
        && (int) json_path($second, 'sede_id') === (int) $b['json']['id']
        && $geoAgain['status'] === 409
        && json_path($me, 'geo_estado') === 'DENEGADA'
        ? ['PASS', 'sede cambia, presbiterio se conserva y GPS no se reautoriza']
        : ['FAIL', 'estados ' . $first['status'] . '/' . $second['status'] . ' geo2=' . $geoAgain['status']];
});

$run('CA-94_gps_una_vez', 'CA-94', static function () use ($lim, $passwords, $db, $sessionName, $suffix): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-06', $passwords['DIGITADOR'], 'geo-' . bin2hex(random_bytes(3)));
    $first = auth_write('POST', '/auth/ubicacion', $session, ['estado' => 'DENEGADA', 'capturada_en' => gmdate('Y-m-d\TH:i:s\Z')]);
    $second = auth_write('POST', '/auth/ubicacion', $session, ['estado' => 'OK', 'latitud' => -12.0, 'longitud' => -77.0, 'capturada_en' => gmdate('Y-m-d\TH:i:s\Z')]);
    $me = auth_get('/auth/me', $session);
    $sid = cookie_value($session['jar'], $sessionName);
    $row = $sid === null ? null : db_one($db, 'SELECT geo_estado, geo_latitud, geo_longitud, geo_capturada_en FROM sesion WHERE session_ref_hash = ?', [hash('sha256', $sid)]);

    return $first['status'] === 200
        && json_path($first, 'estado') === 'DENEGADA'
        && $second['status'] === 409
        && json_path($second, 'error.code') === 'ubicacion_ya_capturada'
        && json_path($me, 'geo_estado') === 'DENEGADA'
        && !contains_sensitive_coordinates($me['json'])
        && ($row['geo_estado'] ?? null) === 'DENEGADA'
        && $row['geo_capturada_en'] !== null
        ? ['PASS', 'DENEGADA persistida, segundo intento 409, sin coordenadas en /auth/me']
        : ['FAIL', 'first=' . $first['status'] . ' second=' . $second['status'] . ' geo=' . (string) ($row['geo_estado'] ?? '—')];
});

$run('CA-76_nueva_sesion_gps', 'CA-76', static function () use ($lim, $passwords): array {
    $first = login($lim, 'LIM-DIG-02', $passwords['DIGITADOR'], 'geo-new-a-' . bin2hex(random_bytes(3)));
    $geoFirst = auth_write('POST', '/auth/ubicacion', $first, ['estado' => 'DENEGADA', 'capturada_en' => gmdate('Y-m-d\TH:i:s\Z')]);
    $logout = auth_write('POST', '/auth/logout', $first, []);
    $second = login($lim, 'LIM-DIG-02', $passwords['DIGITADOR'], 'geo-new-b-' . bin2hex(random_bytes(3)));
    $me = auth_get('/auth/me', $second);
    $geoSecond = auth_write('POST', '/auth/ubicacion', $second, ['estado' => 'DENEGADA', 'capturada_en' => gmdate('Y-m-d\TH:i:s\Z')]);

    return $geoFirst['status'] === 200
        && $logout['status'] === 204
        && $second['response']['status'] === 200
        && json_path($me, 'geo_estado') === null
        && $geoSecond['status'] === 200
        ? ['PASS', 'nuevo login inicia sesión GPS nueva; la revocación no duplica la anterior']
        : ['FAIL', 'estados ' . $geoFirst['status'] . '/' . $logout['status'] . '/' . $geoSecond['status']];
});

$run('I01-O02_gps_concurrente', 'I01-O02', static function () use ($lim, $passwords, $db, $sessionName): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }

    $session = login($lim, 'LIM-DIG-03', $passwords['DIGITADOR'], 'o02-' . bin2hex(random_bytes(3)));
    $token = csrf_token($session);
    $sid = cookie_value($session['jar'], $sessionName);
    if ($sid === null) {
        return ['FAIL', 'cookie de sesión ausente'];
    }
    $capturada = gmdate('Y-m-d\TH:i:s\Z');
    $cookieHeader = 'Cookie: ' . $sessionName . '=' . $sid;

    $requests = [
        [
            'method' => 'POST',
            'url' => $GLOBALS['baseUrl'] . '/auth/ubicacion',
            'headers' => ['Content-Type: application/json', 'X-CSRF-Token: ' . (string) $token, $cookieHeader],
            'body' => (string) json_encode([
                'estado' => 'OK',
                'latitud' => -12.10,
                'longitud' => -77.10,
                'precision_m' => 10,
                'capturada_en' => $capturada,
            ]),
        ],
        [
            'method' => 'POST',
            'url' => $GLOBALS['baseUrl'] . '/auth/ubicacion',
            'headers' => ['Content-Type: application/json', 'X-CSRF-Token: ' . (string) $token, $cookieHeader],
            'body' => (string) json_encode([
                'estado' => 'DENEGADA',
                'capturada_en' => $capturada,
            ]),
        ],
    ];

    $results = http_parallel($requests);
    $statuses = array_map(static fn (array $r): int => $r['status'], $results);
    sort($statuses);
    $winner = null;
    foreach ($results as $result) {
        if ($result['status'] === 200) {
            $winner = $result;
            break;
        }
    }

    $row = $sid === null ? null : db_one(
        $db,
        'SELECT geo_estado, geo_latitud, geo_longitud, geo_precision_m, geo_capturada_en
         FROM sesion WHERE session_ref_hash = ?',
        [hash('sha256', $sid)],
    );

    $winnerState = is_array($winner['json'] ?? null) ? ($winner['json']['estado'] ?? null) : null;
    $dbState = $row['geo_estado'] ?? null;
    $stateMatches = $winnerState !== null && $winnerState === $dbState;
    $coordsMatch = true;
    if ($winnerState === 'OK') {
        $coordsMatch = $row !== null
            && $row['geo_latitud'] !== null
            && abs((float) $row['geo_latitud'] - (-12.10)) < 0.0000001
            && abs((float) $row['geo_longitud'] - (-77.10)) < 0.0000001;
    } elseif ($winnerState === 'DENEGADA') {
        $coordsMatch = $row !== null && $row['geo_latitud'] === null && $row['geo_longitud'] === null;
    }

    return $statuses === [200, 409]
        && $stateMatches
        && $coordsMatch
        && ($row['geo_capturada_en'] ?? null) !== null
        ? ['PASS', 'dos POST concurrentes: 200/409 y estado ganador ' . (string) $winnerState . ' sin sobrescritura']
        : ['FAIL', 'estados=' . implode('/', $statuses) . ' ganador=' . (string) $winnerState . ' db=' . (string) $dbState];
});

$run('CA-115_presbiterios_fijos', 'CA-115', static function () use ($baseUrl): array {
    $list = http_request('GET', $baseUrl . '/presbiterios');
    $codes = [];
    foreach (is_array($list['json']) ? $list['json'] : [] as $item) {
        $codes[] = (string) ($item['codigo'] ?? '');
    }
    sort($codes);
    $write = http_request('POST', $baseUrl . '/presbiterios', ['Content-Type: application/json'], '{}');

    return $codes === ['CHI', 'IQU', 'LIM', 'TRU'] && $write['status'] === 405
        ? ['PASS', 'LIM/TRU/CHI/IQU; sin POST de presbiterios']
        : ['FAIL', 'codes=' . implode(',', $codes) . ' post=' . $write['status']];
});

$run('CA-123_unique_sede', 'CA-123', static function () use ($db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $rows = $db->query("SELECT DISTINCT index_name FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name='organizacion'")->fetchAll(PDO::FETCH_COLUMN);
    $indexes = array_map('strval', $rows);

    return in_array('uq_org_presb_nombre', $indexes, true) && in_array('uq_org_sede_nombre', $indexes, true)
        ? ['PASS', 'UNIQUE de presbiterio raíz y de sede por padre presentes']
        : ['FAIL', 'índices=' . implode(',', $indexes)];
});

$run('CA-113_last_seen_acotado', 'CA-113', static function () use ($lim, $passwords, $db, $sessionName): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-01', $passwords['DIGITADOR'], 'touch-' . bin2hex(random_bytes(3)));
    $sid = cookie_value($session['jar'], $sessionName);
    if ($sid === null) {
        return ['FAIL', 'cookie de sesión ausente'];
    }
    $hash = hash('sha256', $sid);
    $before = db_one($db, 'SELECT last_seen FROM sesion WHERE session_ref_hash = ?', [$hash]);
    auth_get('/auth/me', $session);
    auth_get('/auth/me', $session);
    auth_get('/auth/me', $session);
    $after = db_one($db, 'SELECT last_seen FROM sesion WHERE session_ref_hash = ?', [$hash]);

    return $before !== null && $after !== null && $before['last_seen'] === $after['last_seen']
        ? ['PASS', 'tres GET no actualizaron last_seen']
        : ['FAIL', 'last_seen cambió en GET sucesivos'];
});

$run('CA-61_inactividad', 'CA-61', static function () use ($lim, $passwords, $db, $sessionName): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-02', $passwords['DIGITADOR'], 'idle-' . bin2hex(random_bytes(3)));
    $sid = cookie_value($session['jar'], $sessionName);
    if ($sid === null) {
        return ['FAIL', 'cookie ausente'];
    }
    $hash = hash('sha256', $sid);
    db_exec($db, 'UPDATE sesion SET last_seen = DATE_SUB(NOW(6), INTERVAL 46 MINUTE) WHERE session_ref_hash = ?', [$hash]);
    $response = auth_get('/auth/me', $session);
    $row = db_one($db, 'SELECT revocada_en FROM sesion WHERE session_ref_hash = ?', [$hash]);

    return $response['status'] === 401
        && json_path($response, 'error.code') === 'session_expired'
        && ($row['revocada_en'] ?? null) !== null
        ? ['PASS', '401 session_expired y sesión revocada']
        : ['FAIL', 'HTTP ' . $response['status']];
});

$run('CA-62_vida_absoluta', 'CA-62', static function () use ($lim, $passwords, $db, $sessionName): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-03', $passwords['DIGITADOR'], 'abs-' . bin2hex(random_bytes(3)));
    $sid = cookie_value($session['jar'], $sessionName);
    if ($sid === null) {
        return ['FAIL', 'cookie ausente'];
    }
    $hash = hash('sha256', $sid);
    db_exec($db, 'UPDATE sesion SET fecha_login = DATE_SUB(NOW(6), INTERVAL 13 HOUR), last_seen = NOW(6) WHERE session_ref_hash = ?', [$hash]);
    $response = auth_get('/auth/me', $session);

    return $response['status'] === 401 && json_path($response, 'error.code') === 'session_expired'
        ? ['PASS', '401 pese a actividad reciente']
        : ['FAIL', 'HTTP ' . $response['status']];
});

$run('CA-64_65_revocacion', 'CA-64', static function () use ($lim, $passwords, $db, $sessionName): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-04', $passwords['DIGITADOR'], 'rev-' . bin2hex(random_bytes(3)));
    $sid = cookie_value($session['jar'], $sessionName);
    if ($sid === null) {
        return ['FAIL', 'cookie ausente'];
    }
    $user = db_one($db, "SELECT id, auth_version FROM usuario WHERE codigo='LIM-DIG-04'");
    if ($user === null) {
        return ['FAIL', 'usuario ausente'];
    }
    db_exec($db, 'UPDATE usuario SET auth_version = auth_version + 1 WHERE id = ?', [(int) $user['id']]);

    try {
        $response = auth_get('/auth/me', $session);
        $row = db_one($db, 'SELECT revocada_en FROM sesion WHERE session_ref_hash = ?', [hash('sha256', $sid)]);

        return $response['status'] === 401
            && json_path($response, 'error.code') === 'session_revoked'
            && ($row['revocada_en'] ?? null) !== null
            ? ['PASS', 'auth_version revoca la sesión']
            : ['FAIL', 'HTTP ' . $response['status']];
    } finally {
        db_exec($db, 'UPDATE usuario SET auth_version = auth_version - 1 WHERE id = ?', [(int) $user['id']]);
    }
});

$run('CA-64_cuenta_inactiva_revoca_sesion', 'CA-64', static function () use ($lim, $passwords, $db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-06', $passwords['DIGITADOR'], 'inactive-' . bin2hex(random_bytes(3)));
    $user = db_one($db, "SELECT id FROM usuario WHERE codigo='LIM-DIG-06'");
    if ($user === null) {
        return ['FAIL', 'usuario ausente'];
    }
    db_exec($db, 'UPDATE usuario SET activo=0 WHERE id = ?', [(int) $user['id']]);

    try {
        $response = auth_get('/auth/me', $session);

        return $response['status'] === 401 && json_path($response, 'error.code') === 'session_revoked'
            ? ['PASS', 'cuenta inactiva revoca la sesión existente']
            : ['FAIL', 'HTTP ' . $response['status']];
    } finally {
        db_exec($db, 'UPDATE usuario SET activo=1 WHERE id = ?', [(int) $user['id']]);
    }
});

$run('logout_real', 'CA-63', static function () use ($lim, $passwords, $db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'LIM-DIG-05', $passwords['DIGITADOR'], 'logout-' . bin2hex(random_bytes(3)));
    $response = auth_write('POST', '/auth/logout', $session, []);
    $me = auth_get('/auth/me', $session);
    $event = db_one($db, "SELECT resultado FROM login_evento WHERE codigo_intentado='LIM-DIG-05' AND resultado='LOGOUT' ORDER BY id DESC LIMIT 1");

    return $response['status'] === 204
        && $me['status'] === 401
        && ($event['resultado'] ?? null) === 'LOGOUT'
        ? ['PASS', '204, sesión invalidada y LOGOUT registrado']
        : ['FAIL', 'logout=' . $response['status'] . ' me=' . $me['status']];
});

$run('sin_autenticacion', 'CA-84', static function (): array {
    $me = http_request('GET', $GLOBALS['baseUrl'] . '/auth/me');
    $sedes = http_request('GET', $GLOBALS['baseUrl'] . '/sedes');
    $post = http_request('POST', $GLOBALS['baseUrl'] . '/sedes', ['Content-Type: application/json'], '{}');

    $all401 = $me['status'] === 401 && $sedes['status'] === 401 && $post['status'] === 401;
    $sameCode = json_path($me, 'error.code') === 'unauthenticated'
        && json_path($sedes, 'error.code') === 'unauthenticated'
        && json_path($post, 'error.code') === 'unauthenticated';

    return $all401 && $sameCode
        ? ['PASS', '401 antes de CSRF en todas las rutas protegidas']
        : ['FAIL', 'estados ' . $me['status'] . '/' . $sedes['status'] . '/' . $post['status']];
});

$run('CA-84_error_dto', 'CA-84', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'LIM-DIG-06', $passwords['DIGITADOR'], 'dto-' . bin2hex(random_bytes(3)));
    $notFound = http_request('GET', $GLOBALS['baseUrl'] . '/ruta-inexistente');
    $validation = auth_write('PUT', '/contexto/sede', $session, ['sede_id' => 0]);
    $forbidden = auth_write('PUT', '/contexto/presbiterio', $session, ['presbiterio_id' => 2]);
    $duplicate = auth_write('POST', '/sedes', $session, ['nombre_oficial' => 'I01QA DTO ' . $suffix], ['Idempotency-Key: dto-a-' . $suffix]);
    $duplicate2 = auth_write('POST', '/sedes', $session, ['nombre_oficial' => 'i01qa dto ' . $suffix], ['Idempotency-Key: dto-b-' . $suffix]);

    foreach ([$notFound, $validation, $forbidden, $duplicate2] as $response) {
        if (!is_string(json_path($response, 'error.code')) || !is_string(json_path($response, 'error.message')) || !is_string(json_path($response, 'request_id'))) {
            return ['FAIL', 'sobre de error incompleto'];
        }
    }

    return $duplicate['status'] === 201
        && $duplicate2['status'] === 409
        && $forbidden['status'] === 403
        && $validation['status'] === 422
        ? ['PASS', '401/403/409/422 con error + request_id']
        : ['FAIL', 'estados ' . $validation['status'] . '/' . $forbidden['status'] . '/' . $duplicate2['status']];
});

$run('CA-66_admin_contexto', 'CA-66', static function () use ($lim, $tru, $passwords, $db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $session = login($lim, 'ADMIN-NAC', $passwords['ADMIN'], 'adminctx-' . bin2hex(random_bytes(3)));
    $changed = auth_write('PUT', '/contexto/presbiterio', $session, ['presbiterio_id' => $tru]);
    $audit = db_one($db, "SELECT accion FROM auditoria_evento WHERE accion='CAMBIO_PRESBITERIO' ORDER BY id DESC LIMIT 1");
    $invalid = auth_write('PUT', '/contexto/presbiterio', $session, ['presbiterio_id' => 999999]);

    return $changed['status'] === 200
        && (int) json_path($changed, 'presbiterio_id') === $tru
        && json_path($changed, 'sede_id') === null
        && ($audit['accion'] ?? null) === 'CAMBIO_PRESBITERIO'
        && $invalid['status'] === 422
        ? ['PASS', 'cambio a TRU, sede NULL y auditoría']
        : ['FAIL', 'HTTP ' . $changed['status'] . ' invalid=' . $invalid['status']];
});

$run('CA-67_reportes_solo_lectura', 'CA-67', static function () use ($lim, $passwords, $suffix): array {
    $session = login($lim, 'REPORTES-NAC', $passwords['REPORTES'], 'rep-read-' . bin2hex(random_bytes(3)));
    $create = auth_write('POST', '/sedes', $session, ['nombre_oficial' => 'I01QA Reportes ' . $suffix], ['Idempotency-Key: rep-read-' . $suffix]);
    $context = auth_write('PUT', '/contexto/presbiterio', $session, ['presbiterio_id' => 2]);
    $list = auth_get('/sedes', $session);

    return $create['status'] === 403
        && $context['status'] === 403
        && $list['status'] === 200
        ? ['PASS', 'escritura 403; lectura permitida']
        : ['FAIL', 'crear=' . $create['status'] . ' contexto=' . $context['status']];
});

$run('CA-07_throttle_cuenta', 'CA-07', static function () use ($chi): array {
    $code = 'I01QA-THROTTLE-' . bin2hex(random_bytes(3));
    $statuses = [];
    for ($i = 0; $i < 5; $i++) {
        $response = http_request(
            'POST',
            $GLOBALS['baseUrl'] . '/auth/login',
            ['Content-Type: application/json'],
            (string) json_encode(['presbiterio_id' => $chi, 'usuario' => $code, 'password' => 'mala-' . $i]),
        );
        $statuses[] = $response['status'];
    }
    $sixth = http_request(
        'POST',
        $GLOBALS['baseUrl'] . '/auth/login',
        ['Content-Type: application/json'],
        (string) json_encode(['presbiterio_id' => $chi, 'usuario' => $code, 'password' => 'mala-final']),
    );

    return $statuses === [401, 401, 401, 401, 401]
        && $sixth['status'] === 429
        && json_path($sixth, 'error.code') === 'too_many_attempts'
        ? ['PASS', '5 fallos -> 401; sexto -> 429']
        : ['FAIL', 'estados ' . implode('/', $statuses) . ' sexto=' . $sixth['status']];
});

$run('CA-59_bloqueo_no_revela', 'CA-59', static function () use ($chi, $db): array {
    cleanup_ip_events($db, '127.0.0.1');
    $existing = 'CHI-DIG-06';
    $missing = 'I01QA-NOEXISTE-' . bin2hex(random_bytes(3));
    $bodies = [];

    foreach ([$existing, $missing] as $code) {
        for ($i = 0; $i < 5; $i++) {
            http_request(
                'POST',
                $GLOBALS['baseUrl'] . '/auth/login',
                ['Content-Type: application/json'],
                (string) json_encode(['presbiterio_id' => $chi, 'usuario' => $code, 'password' => 'mala-' . $i]),
            );
        }
        $response = http_request(
            'POST',
            $GLOBALS['baseUrl'] . '/auth/login',
            ['Content-Type: application/json'],
            (string) json_encode(['presbiterio_id' => $chi, 'usuario' => $code, 'password' => 'mala-final']),
        );
        $bodies[] = ['status' => $response['status'], 'code' => json_path($response, 'error.code'), 'message' => json_path($response, 'error.message')];
    }

    cleanup_login_events($db, 'I01QA%');

    return $bodies[0] === $bodies[1] && $bodies[0]['status'] === 429
        ? ['PASS', '429 idéntico para cuenta existente e inexistente']
        : ['FAIL', 'respuestas distintas: ' . json_encode($bodies)];
});

$run('CA-60_throttle_ip', 'CA-60', static function () use ($chi, $lim, $passwords, $db): array {
    cleanup_ip_events($db, '127.0.0.1');
    $statuses = [];
    for ($i = 0; $i < 20; $i++) {
        $response = http_request(
            'POST',
            $GLOBALS['baseUrl'] . '/auth/login',
            ['Content-Type: application/json'],
            (string) json_encode(['presbiterio_id' => $chi, 'usuario' => 'I01QA-IP-' . $i . '-' . bin2hex(random_bytes(2)), 'password' => 'mala']),
        );
        $statuses[] = $response['status'];
    }
    $blocked = http_request(
        'POST',
        $GLOBALS['baseUrl'] . '/auth/login',
        ['Content-Type: application/json'],
        (string) json_encode(['presbiterio_id' => $lim, 'usuario' => 'LIM-DIG-01', 'password' => $passwords['DIGITADOR']]),
    );
    cleanup_ip_events($db, '127.0.0.1');
    cleanup_login_events($db, 'I01QA%');

    return count(array_filter($statuses, static fn (int $s): bool => $s === 401)) === 20
        && $blocked['status'] === 429
        && json_path($blocked, 'error.code') === 'too_many_attempts'
        ? ['PASS', '20 fallos por IP -> 429 temporal; se limpia la ventana de prueba']
        : ['FAIL', 'fallos=' . count(array_filter($statuses, static fn (int $s): bool => $s === 401)) . ' bloqueo=' . $blocked['status']];
});

$run('I01-O01_aislamiento_presbiterios', 'I01-O01', static function () use ($lim, $tru, $passwords, $db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }

    cleanup_ip_events($db, '127.0.0.1');
    cleanup_code_events($db, 'LIM-DIG-06');

    $wrongContextStatuses = [];
    for ($i = 0; $i < 5; $i++) {
        $response = http_request(
            'POST',
            $GLOBALS['baseUrl'] . '/auth/login',
            ['Content-Type: application/json'],
            (string) json_encode(['presbiterio_id' => $tru, 'usuario' => 'LIM-DIG-06', 'password' => 'mala-contexto-' . $i]),
        );
        $wrongContextStatuses[] = $response['status'];
    }

    $sixthWrongContext = http_request(
        'POST',
        $GLOBALS['baseUrl'] . '/auth/login',
        ['Content-Type: application/json'],
        (string) json_encode(['presbiterio_id' => $tru, 'usuario' => 'LIM-DIG-06', 'password' => 'mala-contexto-final']),
    );
    $validInOwnContext = login($lim, 'LIM-DIG-06', $passwords['DIGITADOR'], 'o01-limit-' . bin2hex(random_bytes(3)));
    $otherUserInOtherContext = login($tru, 'TRU-DIG-01', $passwords['DIGITADOR'], 'o01-tru-' . bin2hex(random_bytes(3)));

    cleanup_code_events($db, 'LIM-DIG-06');
    cleanup_ip_events($db, '127.0.0.1');

    return $wrongContextStatuses === [401, 401, 401, 401, 401]
        && $sixthWrongContext['status'] === 429
        && $validInOwnContext['response']['status'] === 200
        && $otherUserInOtherContext['response']['status'] === 200
        ? ['PASS', '5 fallos en TRU no bloquean LIM-DIG-06 en LIM; otro usuario TRU tampoco se afecta']
        : ['FAIL', 'TRU=' . implode('/', $wrongContextStatuses) . ' sexto=' . $sixthWrongContext['status']
            . ' LIM=' . $validInOwnContext['response']['status'] . ' TRU=' . $otherUserInOtherContext['response']['status']];
});

$run('CA-91_esquema_11_tablas', 'CA-91', static function () use ($db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type='BASE TABLE' ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
    $fk = $db->query("SELECT COUNT(*) FROM information_schema.referential_constraints WHERE constraint_schema = DATABASE()")->fetchColumn();
    $forbidden = array_intersect($tables, ['usuario_presbiterio', 'alcance_consulta', 'tipo_documento_detalle', 'geo_sesion']);

    return count($tables) === 11 && (int) $fk === 23 && $forbidden === []
        ? ['PASS', '11 tablas y 23 FK; sin tablas prohibidas']
        : ['FAIL', 'tablas=' . count($tables) . ' fk=' . $fk . ' prohibidas=' . implode(',', $forbidden)];
});

$run('CA-92_jerarquia_y_ficha_cruzada', 'CA-92', static function () use ($db): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $lim = db_one($db, "SELECT id FROM organizacion WHERE tipo='PRESBITERIO' AND codigo='LIM'");
    $tru = db_one($db, "SELECT id FROM organizacion WHERE tipo='PRESBITERIO' AND codigo='TRU'");
    $user = db_one($db, "SELECT id FROM usuario WHERE codigo='LIM-DIG-01'");
    if ($lim === null || $tru === null || $user === null) {
        return ['BLOCKED', 'faltan presbiterios/usuarios sintéticos para CA-92'];
    }
    $sede = db_one($db, "SELECT id, padre_id FROM organizacion WHERE tipo='SEDE' AND padre_id = ? ORDER BY id LIMIT 1", [(int) $lim['id']]);
    if ($sede === null) {
        return ['BLOCKED', 'no hay sede LIM para CA-92'];
    }
    if ((int) $sede['padre_id'] !== (int) $lim['id']) {
        return ['FAIL', 'sede con padre incorrecto'];
    }

    $db->beginTransaction();
    try {
        $db->prepare('INSERT INTO persona (nombres, created_by) VALUES (?, ?)')->execute(['I01QA Ficha cruzada', (int) $user['id']]);
        $personaId = (int) $db->lastInsertId();
        $db->prepare('INSERT INTO ficha (persona_id, presbiterio_id, sede_id, tipo_ficha, created_by) VALUES (?, ?, ?, ?, ?)')
            ->execute([$personaId, (int) $tru['id'], (int) $sede['id'], 'PERSONA', (int) $user['id']]);
        $db->rollBack();

        return ['FAIL', 'la FK permitió una ficha con sede de otro presbiterio'];
    } catch (PDOException) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        return ['PASS', 'FK compuesta rechaza sede de otro presbiterio'];
    }
});

$run('CA-114_hashing', 'CA-114', static function () use ($db, $passwords): array {
    if ($db === null) {
        return ['BLOCKED', 'MySQL ausente'];
    }
    $row = db_one($db, "SELECT password_hash FROM usuario WHERE codigo='LIM-DIG-01'");
    $hash = (string) ($row['password_hash'] ?? '');
    $strong = str_starts_with($hash, '$argon2id$') || preg_match('/^\$2[ay]\$(1[2-9]|[2-9][0-9])\$/', $hash) === 1;
    $verifies = $hash !== '' && password_verify($passwords['DIGITADOR'], $hash);

    return $strong && $verifies
        ? ['PASS', 'hash fuerte y password_verify correcto']
        : ['FAIL', 'hash débil o no verificable'];
});

$run('CA-110_sin_coordenadas_en_me', 'CA-110', static function () use ($lim, $passwords): array {
    $session = login($lim, 'LIM-DIG-01', $passwords['DIGITADOR'], 'nocoord-' . bin2hex(random_bytes(3)));
    $me = auth_get('/auth/me', $session);

    return $me['status'] === 200 && !contains_sensitive_coordinates($me['json'])
        ? ['PASS', '/auth/me sin latitud/longitud/precisión']
        : ['FAIL', 'HTTP ' . $me['status'] . ' o coordenadas expuestas'];
});

$results = $checks;
$counts = ['PASS' => 0, 'FAIL' => 0, 'BLOCKED' => 0];

fwrite(STDOUT, 'PCD I01 runner — ' . $baseUrl . PHP_EOL);
fwrite(STDOUT, sprintf("%-40s %-8s %-8s %s\n", 'VERIFICACIÓN', 'CA', 'ESTADO', 'DETALLE'));
fwrite(STDOUT, str_repeat('-', 110) . PHP_EOL);

foreach ($results as $result) {
    $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;
    fwrite(STDOUT, sprintf("%-40s %-8s %-8s %s\n", $result['id'], $result['ca'], $result['status'], $result['detail']));
}

fwrite(STDOUT, str_repeat('-', 110) . PHP_EOL);
fwrite(STDOUT, sprintf(
    "Resumen: PASS=%d FAIL=%d BLOCKED=%d Total=%d\n",
    $counts['PASS'],
    $counts['FAIL'],
    $counts['BLOCKED'],
    count($results),
));

$evidence = [
    'suite' => 'I01',
    'base_url' => $baseUrl,
    'generated_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'php' => PHP_VERSION,
    'mysql' => $db !== null ? (string) $db->query('SELECT VERSION()')->fetchColumn() : null,
    'checks' => $results,
    'summary' => $counts,
];

if ($jsonPath !== null) {
    file_put_contents($jsonPath, json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

if ($counts['FAIL'] > 0 || $counts['BLOCKED'] > 0) {
    fwrite(STDERR, 'Runner I01: existen FAIL o BLOCKED; no se declara PASS global.' . PHP_EOL);
    exit(1);
}

exit(0);
