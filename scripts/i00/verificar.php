#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Verificador técnico I00 (MySQL 8 real + entorno + seguridad).
 *
 * Uso:
 *   PCD_DB_HOST=127.0.0.1 PCD_DB_NAME=pcd_test PCD_DB_USER=root PCD_DB_PASSWORD=... \
 *     php scripts/i00/verificar.php --json=.tmp/i00-evidencia.json
 *
 * Requiere que 001_initial.sql y 002_catalogos_calidad.sql ya se hayan ejecutado.
 * No modifica el esquema: las pruebas negativas usan transacción + rollback.
 * Estados: PASS, FAIL, BLOCKED, NOT_IMPLEMENTED. Sale con 1 si hay FAIL.
 */

$root = dirname(__DIR__, 2);
$options = getopt('', ['json::']);
$jsonPath = $options['json'] ?? ($root . '/.tmp/i00-evidencia.json');

/** @var list<array{id:string, grupo:string, control:string, estado:string, detalle:string}> $results */
$results = [];

function add_result(array &$results, string $id, string $grupo, string $control, string $estado, string $detalle): void
{
    $results[] = compact('id', 'grupo', 'control', 'estado', 'detalle');
}

function run_check(array &$results, string $id, string $grupo, string $control, callable $check): void
{
    try {
        [$estado, $detalle] = $check();
    } catch (Throwable $exception) {
        [$estado, $detalle] = ['FAIL', $exception->getMessage()];
    }

    add_result($results, $id, $grupo, $control, $estado, $detalle);
}

function env_value(string $key): ?string
{
    $value = getenv($key);

    return $value === false ? null : $value;
}

function command_first_line(string $command): ?string
{
    $output = [];
    $code = 1;
    @exec($command . ' 2>&1', $output, $code);

    return $code === 0 && isset($output[0]) ? trim((string) $output[0]) : null;
}

function source_contains(string $file, array $needles): bool
{
    if (!is_file($file)) {
        return false;
    }

    $contents = (string) file_get_contents($file);

    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            return false;
        }
    }

    return true;
}

// ---------------------------------------------------------------------------
// A. Entorno técnico
// ---------------------------------------------------------------------------
run_check($results, 'E-01', 'Entorno', 'PHP >= 8.2', static function (): array {
    return version_compare(PHP_VERSION, '8.2.0', '>=')
        ? ['PASS', 'PHP ' . PHP_VERSION]
        : ['FAIL', 'PHP ' . PHP_VERSION . ' < 8.2'];
});

run_check($results, 'E-02', 'Entorno', 'Extensiones PHP requeridas', static function (): array {
    $required = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl', 'session'];
    $missing = array_values(array_filter($required, static fn (string $ext): bool => !extension_loaded($ext)));

    return $missing === []
        ? ['PASS', implode(', ', $required)]
        : ['FAIL', 'faltan: ' . implode(', ', $missing)];
});

run_check($results, 'E-03', 'Entorno', 'Composer disponible', static function () use ($root): array {
    $version = command_first_line('composer --version');

    if ($version !== null) {
        return ['PASS', $version];
    }

    if (is_file($root . '/.tools/composer.phar')) {
        return ['PASS', 'composer.phar local en .tools (CI usa composer global)'];
    }

    return ['FAIL', 'composer no disponible'];
});

run_check($results, 'E-04', 'Entorno', 'Node.js >= 20 y npm', static function (): array {
    $node = command_first_line('node -v');
    $npm = command_first_line('npm -v');

    if ($node === null || $npm === null) {
        return ['FAIL', 'node/npm no disponibles'];
    }

    $major = (int) ltrim($node, 'v');

    return $major >= 20
        ? ['PASS', $node . ' / npm ' . $npm]
        : ['FAIL', 'Node ' . $node . ' < 20'];
});

run_check($results, 'E-05', 'Entorno', 'Build React/TS/Vite generado', static function () use ($root): array {
    $index = $root . '/frontend/dist/index.html';

    return is_file($index)
        ? ['PASS', 'frontend/dist/index.html']
        : ['BLOCKED', 'Ejecute (cd frontend && npm ci && npm run build)'];
});

run_check($results, 'E-06', 'Entorno', 'Variables documentadas sin secretos', static function () use ($root): array {
    $file = $root . '/backend/.env.example';

    if (!is_file($file)) {
        return ['FAIL', 'falta backend/.env.example'];
    }

    $contents = (string) file_get_contents($file);
    $required = ['PCD_DB_HOST', 'PCD_DB_NAME', 'PCD_DB_USER', 'PCD_ALLOWED_ORIGINS', 'PCD_SESSION_NAME'];
    $missing = array_values(array_filter($required, static fn (string $key): bool => !str_contains($contents, $key . '=')));

    if ($missing !== []) {
        return ['FAIL', 'faltan variables: ' . implode(', ', $missing)];
    }

    if (preg_match('/^PCD_(UAT_DIGITADOR|ADMIN|REPORTES)_PASSWORD=.+$/m', $contents) === 1) {
        return ['FAIL', '.env.example contiene contraseñas'];
    }

    return ['PASS', 'backend/.env.example completo y vacío'];
});

run_check($results, 'E-07', 'Entorno', 'Hosting cPanel real (PHP/MySQL/HTTPS)', static function (): array {
    return env_value('PCD_HOSTING_VERIFIED') === '1'
        ? ['PASS', 'verificado por operador']
        : ['BLOCKED', 'Sin acceso al hosting; no se inventan versiones'];
});

run_check($results, 'E-08', 'Entorno', 'Despliegue sin Node residente', static function () use ($root): array {
    $htaccess = $root . '/backend/public/.htaccess';
    $readme = $root . '/backend/README.md';

    return is_file($htaccess) && source_contains($readme, ['DocumentRoot', 'Node'])
        ? ['PASS', 'front controller + .htaccess; build estático documentado']
        : ['FAIL', 'documentación de despliegue incompleta'];
});

// ---------------------------------------------------------------------------
// B. Base de datos MySQL 8
// ---------------------------------------------------------------------------
$pdo = null;
$dbError = null;

try {
    if (env_value('PCD_DB_HOST') === null || env_value('PCD_DB_NAME') === null || env_value('PCD_DB_USER') === null) {
        throw new RuntimeException('PCD_DB_HOST/NAME/USER no definidos');
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        (string) env_value('PCD_DB_HOST'),
        (int) (env_value('PCD_DB_PORT') ?? 3306),
        (string) env_value('PCD_DB_NAME'),
    );
    $pdo = new PDO($dsn, (string) env_value('PCD_DB_USER'), (string) (env_value('PCD_DB_PASSWORD') ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 3,
    ]);
} catch (Throwable $exception) {
    $dbError = $exception->getMessage();
}

/**
 * @param array<string, mixed> $params
 * @return list<array<string, mixed>>
 */
function db_all(PDO $pdo, string $sql, array $params = []): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    // information_schema devuelve columnas en MAYÚSCULAS; se normalizan a minúsculas.
    return array_map(
        static fn (array $row): array => array_change_key_case($row, CASE_LOWER),
        $statement->fetchAll(),
    );
}

function db_value(PDO $pdo, string $sql, array $params = []): mixed
{
    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchColumn();
}

function db_check(array &$results, ?PDO $pdo, ?string $error, string $id, string $control, callable $check): void
{
    if (!$pdo instanceof PDO) {
        add_result($results, $id, 'Base de datos', $control, 'BLOCKED', 'MySQL no disponible: ' . ($error ?? 'sin conexión'));

        return;
    }

    run_check($results, $id, 'Base de datos', $control, static fn (): array => $check($pdo));
}

db_check($results, $pdo, $dbError, 'D-01', '11 tablas exactas', static function (PDO $pdo): array {
    $expected = ['auditoria_evento', 'catalogo', 'ficha', 'grupo_crecer', 'idempotencia', 'login_evento', 'organizacion', 'persona', 'sesion', 'ubigeo', 'usuario'];
    $rows = array_column(db_all($pdo, "SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'"), 'table_name');
    sort($rows);

    return $rows === $expected
        ? ['PASS', count($rows) . ' tablas']
        : ['FAIL', 'encontradas: ' . implode(', ', $rows)];
});

db_check($results, $pdo, $dbError, 'D-02', '146 columnas', static function (PDO $pdo): array {
    $count = (int) db_value($pdo, 'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE()');

    return $count === 146 ? ['PASS', '146 columnas'] : ['FAIL', $count . ' columnas'];
});

db_check($results, $pdo, $dbError, 'D-03', '23 claves foráneas', static function (PDO $pdo): array {
    $count = (int) db_value($pdo, "SELECT COUNT(*) FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND constraint_type = 'FOREIGN KEY'");

    return $count === 23 ? ['PASS', '23 FK'] : ['FAIL', $count . ' FK'];
});

db_check($results, $pdo, $dbError, 'D-04', 'Restricciones UNIQUE esperadas', static function (PDO $pdo): array {
    $expected = [
        'uq_org_codigo', 'uq_org_presb_nombre', 'uq_org_sede_nombre', 'uq_org_id_padre',
        'uq_usuario_codigo', 'uq_sesion_hash', 'uq_catalogo_categoria_codigo',
        'uq_persona_identidad', 'uq_ficha_id_tipo', 'uq_gc_ficha', 'uq_idem_usuario_clave',
    ];
    $rows = array_column(db_all($pdo, "SELECT constraint_name FROM information_schema.table_constraints WHERE table_schema = DATABASE() AND constraint_type = 'UNIQUE'"), 'constraint_name');
    $missing = array_values(array_diff($expected, $rows));

    return $missing === []
        ? ['PASS', count($expected) . ' UNIQUE presentes']
        : ['FAIL', 'faltan: ' . implode(', ', $missing)];
});

db_check($results, $pdo, $dbError, 'D-05', 'Restricciones CHECK presentes', static function (PDO $pdo): array {
    $expected = ['chk_org_jerarquia', 'chk_sesion_geo', 'chk_ubigeo_nivel', 'chk_gc_tipo'];
    $rows = array_column(db_all($pdo, 'SELECT constraint_name FROM information_schema.check_constraints WHERE constraint_schema = DATABASE()'), 'constraint_name');
    $missing = array_values(array_diff($expected, $rows));

    return $missing === []
        ? ['PASS', count($expected) . ' CHECK presentes']
        : ['FAIL', 'faltan: ' . implode(', ', $missing)];
});

db_check($results, $pdo, $dbError, 'D-06', 'Columna generada nombre_raiz_norm', static function (PDO $pdo): array {
    $rows = db_all($pdo, "SELECT table_name, column_name, extra FROM information_schema.columns WHERE table_schema = DATABASE() AND extra LIKE '%GENERATED%'");
    $found = array_filter($rows, static fn (array $row): bool => $row['table_name'] === 'organizacion' && $row['column_name'] === 'nombre_raiz_norm' && str_contains((string) $row['extra'], 'STORED'));

    return count($found) === 1
        ? ['PASS', 'organizacion.nombre_raiz_norm STORED']
        : ['FAIL', 'columna generada ausente o distinta'];
});

db_check($results, $pdo, $dbError, 'D-07', 'Índices de idempotencia', static function (PDO $pdo): array {
    $rows = array_column(db_all($pdo, "SELECT index_name FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'idempotencia'"), 'index_name');
    $unique = array_values(array_unique($rows));

    return in_array('uq_idem_usuario_clave', $unique, true) && in_array('ix_idem_created_at', $unique, true)
        ? ['PASS', 'uq_idem_usuario_clave + ix_idem_created_at']
        : ['FAIL', 'faltan índices de idempotencia'];
});

db_check($results, $pdo, $dbError, 'D-08', 'Charset y collation utf8mb4', static function (PDO $pdo): array {
    $tables = db_all($pdo, 'SELECT table_name, table_collation FROM information_schema.tables WHERE table_schema = DATABASE()');
    $bad = array_filter($tables, static fn (array $row): bool => $row['table_collation'] !== 'utf8mb4_unicode_ci');
    $charsets = array_column(db_all($pdo, 'SELECT DISTINCT character_set_name FROM information_schema.columns WHERE table_schema = DATABASE() AND character_set_name IS NOT NULL'), 'character_set_name');

    if ($bad !== []) {
        return ['FAIL', 'collation distinta: ' . implode(', ', array_column($bad, 'table_name'))];
    }

    return $charsets === ['utf8mb4']
        ? ['PASS', 'utf8mb4 / utf8mb4_unicode_ci']
        : ['FAIL', 'charsets: ' . implode(', ', $charsets)];
});

db_check($results, $pdo, $dbError, 'D-09', 'created_at/updated_at en las 11 tablas', static function (PDO $pdo): array {
    $rows = db_all($pdo, "SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND column_name IN ('created_at','updated_at')");
    $byTable = [];

    foreach ($rows as $row) {
        $byTable[$row['table_name']][$row['column_name']] = true;
    }

    $missing = [];

    foreach (['auditoria_evento', 'catalogo', 'ficha', 'grupo_crecer', 'idempotencia', 'login_evento', 'organizacion', 'persona', 'sesion', 'ubigeo', 'usuario'] as $table) {
        if (!isset($byTable[$table]['created_at'], $byTable[$table]['updated_at'])) {
            $missing[] = $table;
        }
    }

    return $missing === [] && count($rows) === 22
        ? ['PASS', '22 columnas (11 × 2)']
        : ['FAIL', 'faltan en: ' . implode(', ', $missing)];
});

db_check($results, $pdo, $dbError, 'D-10', 'Seed de presbiterios y catálogos', static function (PDO $pdo): array {
    $presbiterios = array_column(db_all($pdo, "SELECT codigo FROM organizacion WHERE tipo = 'PRESBITERIO' ORDER BY codigo"), 'codigo');
    $catalogo = (int) db_value($pdo, 'SELECT COUNT(*) FROM catalogo');
    $sedes = (int) db_value($pdo, "SELECT COUNT(*) FROM organizacion WHERE tipo = 'SEDE'");

    if ($presbiterios !== ['CHI', 'IQU', 'LIM', 'TRU']) {
        return ['FAIL', 'presbiterios: ' . implode(', ', $presbiterios)];
    }

    if ($catalogo < 1) {
        return ['FAIL', 'catálogo vacío'];
    }

    return $sedes === 0
        ? ['PASS', '4 presbiterios, ' . $catalogo . ' filas de catálogo, 0 sedes ficticias']
        : ['FAIL', $sedes . ' sedes sembradas'];
});

db_check($results, $pdo, $dbError, 'D-11', 'FK rechaza referencia inválida', static function (PDO $pdo): array {
    $pdo->beginTransaction();

    try {
        $pdo->prepare("INSERT INTO usuario (codigo, nombre_display, perfil, presbiterio_id, password_hash) VALUES ('I00-FK', 'x', 'DIGITADOR', 999999, 'x')")->execute();
        $pdo->rollBack();

        return ['FAIL', 'la FK permitió una referencia inválida'];
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['PASS', 'error ' . (int) $exception->getCode() . ' al violar FK'];
    }
});

db_check($results, $pdo, $dbError, 'D-12', 'CHECK rechaza sede sin padre', static function (PDO $pdo): array {
    $pdo->beginTransaction();

    try {
        $pdo->prepare("INSERT INTO organizacion (tipo, padre_id, codigo, nombre, nombre_norm) VALUES ('SEDE', NULL, 'I00-CHK', 'Sede I00', 'SEDE I00')")->execute();
        $pdo->rollBack();

        return ['FAIL', 'el CHECK permitió una sede sin padre'];
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['PASS', 'error ' . (int) $exception->getCode() . ' al violar CHECK'];
    }
});

db_check($results, $pdo, $dbError, 'D-13', 'UNIQUE de raíz y columna generada', static function (PDO $pdo): array {
    $pdo->beginTransaction();

    try {
        $pdo->prepare("INSERT INTO organizacion (tipo, padre_id, codigo, nombre, nombre_norm) VALUES ('PRESBITERIO', NULL, 'I00-DUP', 'Lima', 'LIMA')")->execute();
        $pdo->rollBack();

        return ['FAIL', 'se permitió duplicar el presbiterio raíz'];
    } catch (PDOException $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['PASS', 'error ' . (int) $exception->getCode() . ' por UNIQUE de nombre_raiz_norm'];
    }
});

db_check($results, $pdo, $dbError, 'D-14', 'Aprovisionamiento UAT (26 cuentas)', static function (PDO $pdo): array {
    $total = (int) db_value($pdo, 'SELECT COUNT(*) FROM usuario');

    if ($total === 0) {
        return ['BLOCKED', 'bootstrap UAT no ejecutado en esta BD'];
    }

    $digitadores = (int) db_value($pdo, "SELECT COUNT(*) FROM usuario WHERE perfil = 'DIGITADOR'");
    $argon = (int) db_value($pdo, "SELECT COUNT(*) FROM usuario WHERE password_hash LIKE '\\$argon2id\\$%'");

    return $total === 26 && $digitadores === 24 && $argon === 26
        ? ['PASS', '26 cuentas, 24 DIGITADORES, 26 Argon2id']
        : ['FAIL', 'total=' . $total . ' digitadores=' . $digitadores . ' argon2id=' . $argon];
});

db_check($results, $pdo, $dbError, 'D-15', 'GPS solo en sesión', static function (PDO $pdo): array {
    $rows = db_all($pdo, "SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = DATABASE() AND column_name LIKE 'geo_%'");
    $tables = array_values(array_unique(array_column($rows, 'table_name')));
    sort($tables);

    return $tables === ['sesion']
        ? ['PASS', 'columnas geo_* solo en sesion']
        : ['FAIL', 'geo_* en: ' . implode(', ', $tables)];
});

// ---------------------------------------------------------------------------
// C. Seguridad y secretos
// ---------------------------------------------------------------------------
run_check($results, 'S-01', 'Seguridad', '.env ignorado y no versionado', static function () use ($root): array {
    $gitignore = (string) @file_get_contents($root . '/.gitignore');
    $tracked = trim((string) @shell_exec('git -C ' . escapeshellarg($root) . ' ls-files backend/.env'));
    $ignored = str_contains($gitignore, '.env');

    return $ignored && $tracked === ''
        ? ['PASS', '.env ignorado y ausente de Git']
        : ['FAIL', '.env no está protegido'];
});

run_check($results, 'S-02', 'Seguridad', 'Sin claves privadas ni tokens en Git', static function () use ($root): array {
    $pattern = 'BEGIN (RSA|OPENSSH|EC|PGP) PRIVATE KEY|AKIA[0-9A-Z]{16}|ghp_[A-Za-z0-9]{20,}|github_pat_[A-Za-z0-9_]{20,}';
    $output = (string) @shell_exec('git -C ' . escapeshellarg($root) . ' grep -nE ' . escapeshellarg($pattern) . ' -- . 2>/dev/null');

    return trim($output) === ''
        ? ['PASS', 'sin claves ni tokens detectados']
        : ['FAIL', 'coincidencias en Git: ' . substr(trim($output), 0, 200)];
});

run_check($results, 'S-03', 'Seguridad', 'UAT separado de producción', static function () use ($root): array {
    $script = $root . '/scripts/bootstrap-provision.php';
    $ok = source_contains($script, ['admin-prod', 'PCD_ENV', 'production', 'no puede ejecutarse en producción']);

    return $ok
        ? ['PASS', 'modo uat bloqueado en producción; admin-prod separado']
        : ['FAIL', 'separación UAT/producción no verificable'];
});

run_check($results, 'S-04', 'Seguridad', 'Argon2id disponible', static function (): array {
    if (!defined('PASSWORD_ARGON2ID')) {
        return ['BLOCKED', 'PHP local sin Argon2id; CI lo verifica con PHP 8.2'];
    }

    $hash = password_hash('i00-sintetico', PASSWORD_ARGON2ID);

    return str_starts_with((string) $hash, '$argon2id$')
        ? ['PASS', 'PASSWORD_ARGON2ID operativo']
        : ['FAIL', 'hash Argon2id no generado'];
});

run_check($results, 'S-05', 'Seguridad', 'Sesión preparada (HttpOnly/SameSite/Secure)', static function () use ($root): array {
    $ok = source_contains($root . '/backend/src/Security/Csrf.php', ['httponly', 'samesite', 'secure', 'session_set_cookie_params']);

    return $ok
        ? ['PASS', 'configuración de cookie revisada; emisión real en I01']
        : ['FAIL', 'configuración de sesión incompleta'];
});

run_check($results, 'S-06', 'Seguridad', 'HTTPS previsto en producción', static function () use ($root): array {
    $ok = source_contains($root . '/backend/src/Security/Csrf.php', ["Env::get('PCD_ENV') === 'production'", "'secure' => \$secure"]);

    return $ok
        ? ['PASS', 'cookie Secure en producción/HTTPS; TLS del hosting BLOQUEADO']
        : ['FAIL', 'condición HTTPS no encontrada'];
});

run_check($results, 'S-07', 'Seguridad', 'Sin PII real ni dumps versionados', static function () use ($root): array {
    $files = trim((string) @shell_exec('git -C ' . escapeshellarg($root) . " ls-files '*.sql' '*.dump' '*.csv'"));
    $allowed = ['database/migrations/001_initial.sql', 'database/seeds/002_catalogos_calidad.sql'];
    $unexpected = array_values(array_diff(array_filter(explode("\n", $files)), $allowed));

    return $unexpected === []
        ? ['PASS', 'solo migración y seed de catálogos']
        : ['FAIL', 'archivos de datos: ' . implode(', ', $unexpected)];
});

run_check($results, 'S-08', 'Seguridad', 'GPS opcional, una vez por sesión', static function (): array {
    return ['NOT_IMPLEMENTED', 'Comportamiento funcional de I01; el modelo ya limita geo_* a sesion'];
});

// ---------------------------------------------------------------------------
// Salida
// ---------------------------------------------------------------------------
$counts = ['PASS' => 0, 'FAIL' => 0, 'BLOCKED' => 0, 'NOT_IMPLEMENTED' => 0];

fwrite(STDOUT, "I00 — Verificación técnica PCD\n");
fwrite(STDOUT, sprintf("%-6s %-14s %-46s %-16s %s\n", 'ID', 'GRUPO', 'CONTROL', 'ESTADO', 'DETALLE'));
fwrite(STDOUT, str_repeat('-', 120) . PHP_EOL);

foreach ($results as $result) {
    $counts[$result['estado']] = ($counts[$result['estado']] ?? 0) + 1;
    fwrite(STDOUT, sprintf(
        "%-6s %-14s %-46s %-16s %s\n",
        $result['id'],
        $result['grupo'],
        mb_strimwidth($result['control'], 0, 44, '…'),
        $result['estado'],
        $result['detalle'],
    ));
}

fwrite(STDOUT, str_repeat('-', 120) . PHP_EOL);
fwrite(STDOUT, sprintf(
    "Resumen: PASS=%d FAIL=%d BLOCKED=%d NOT_IMPLEMENTED=%d Total=%d\n",
    $counts['PASS'],
    $counts['FAIL'],
    $counts['BLOCKED'],
    $counts['NOT_IMPLEMENTED'],
    count($results),
));

$payload = [
    'generated_at' => gmdate('c'),
    'php' => PHP_VERSION,
    'database' => $pdo instanceof PDO ? (string) env_value('PCD_DB_NAME') : null,
    'summary' => $counts,
    'checks' => $results,
];

$directory = dirname($jsonPath);

if (!is_dir($directory)) {
    mkdir($directory, 0775, true);
}

file_put_contents($jsonPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
fwrite(STDOUT, 'JSON: ' . $jsonPath . PHP_EOL);

exit($counts['FAIL'] > 0 ? 1 : 0);
