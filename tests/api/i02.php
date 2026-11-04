#!/usr/bin/env php
<?php
declare(strict_types=1);

/** I02: pruebas HTTP reales contra API/MySQL con datos exclusivamente sintéticos.
 * Se ejecuta en GitHub Actions después de I01. No sustituye aprobación H-24.
 */
$base = 'http://127.0.0.1:8081/api/v1';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--base-url=')) $base = rtrim(substr($arg, 11), '/');
}
$checks = [];
$clean = ['fichas' => [], 'personas' => [], 'sedes' => [], 'keys' => []];
$auths = [];
$pdo = null;
function checkI02(string $id, bool $ok, string $reason = ''): void {
    global $checks;
    $checks[] = ['id' => $id, 'estado' => $ok ? 'PASS' : 'FAIL', 'detalle' => $reason];
    echo ($ok ? 'PASS' : 'FAIL') . ' ' . $id . ($reason ? ' — ' . $reason : '') . PHP_EOL;
}
final class I02Client {
    public \CurlHandle $curl;
    public ?string $csrf = null;
    public function __construct(private string $base) {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, '');
    }
    /** @return array{code:int,body:array<string,mixed>} */
    public function send(string $method, string $path, ?array $body = null, ?string $key = null): array {
        $headers = ['Accept: application/json'];
        if ($body !== null) $headers[] = 'Content-Type: application/json';
        if ($this->csrf !== null) $headers[] = 'X-CSRF-Token: ' . $this->csrf;
        if ($key !== null) $headers[] = 'Idempotency-Key: ' . $key;
        curl_setopt_array($this->curl, [
            CURLOPT_URL => $this->base . $path, CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR),
            CURLOPT_TIMEOUT => 20,
        ]);
        $text = curl_exec($this->curl);
        if ($text === false) {
            throw new RuntimeException('HTTP I02: fallo de conexión: ' . curl_error($this->curl));
        }
        $decoded = json_decode($text, true);
        return ['code' => (int) curl_getinfo($this->curl, CURLINFO_HTTP_CODE),
            'body' => is_array($decoded) ? $decoded : []];
    }
    public function login(int $presb, string $usuario, string $password): array {
        $result = $this->send('POST', '/auth/login', [
            'presbiterio_id' => $presb, 'usuario' => $usuario, 'password' => $password
        ]);
        if ($result['code'] === 200) $this->csrf = $result['body']['csrf_token'] ?? null;
        return $result;
    }
    public function selectSede(int $id): array {
        return $this->send('PUT', '/contexto/sede', ['sede_id' => $id]);
    }
}
function h24(array $changes = []): array {
    return array_merge([
        'nombres' => 'PERSONA PRUEBA CI',
        'apellido_paterno' => 'APELLIDO',
        'apellido_materno' => 'MATERNO',
        'tipo_documento' => 'DNI',
        'numero_documento' => '00123456',
        'telefono' => '999888777',
    ], $changes);
}
function pcdKey(): string {
    global $clean;
    $key = 'i02-ci-' . bin2hex(random_bytes(9));
    $clean['keys'][] = $key;
    return $key;
}
/**
 * Dispara dos POST simultáneos sobre conexiones/sesiones HTTP separadas,
 * preservando cookies en cada CurlHandle. CI usa PHP_CLI_SERVER_WORKERS=4.
 * @param list<array{client:I02Client,body:array<string,mixed>,key:string}> $jobs
 * @return list<array{code:int,body:array<string,mixed>}>
 */
function concurrentPostsI02(string $base, array $jobs): array {
    $multi = curl_multi_init();
    foreach ($jobs as $job) {
        $client = $job['client'];
        $headers = ['Accept: application/json', 'Content-Type: application/json',
            'X-CSRF-Token: ' . $client->csrf, 'Idempotency-Key: ' . $job['key']];
        curl_setopt_array($client->curl, [
            CURLOPT_URL => $base . '/personas',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($job['body'], JSON_THROW_ON_ERROR),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        curl_multi_add_handle($multi, $client->curl);
    }
    do {
        $status = curl_multi_exec($multi, $running);
        if ($running) curl_multi_select($multi, 0.1);
    } while ($running && $status === CURLM_OK);
    $results = [];
    foreach ($jobs as $job) {
        $h = $job['client']->curl;
        $body = json_decode((string) curl_multi_getcontent($h), true);
        $results[] = [
            'code' => (int) curl_getinfo($h, CURLINFO_HTTP_CODE),
            'body' => is_array($body) ? $body : [],
        ];
        curl_multi_remove_handle($multi, $h);
    }
    curl_multi_close($multi);
    return $results;
}
try {
    require dirname(__DIR__, 2) . '/backend/vendor/autoload.php';
    $pdo = \Pcd\Database\Db::fromEnv()->pdo();
    $presbs = $pdo->query("SELECT id, codigo FROM organizacion WHERE tipo='PRESBITERIO'")->fetchAll(\PDO::FETCH_KEY_PAIR);
    $ids = array_flip($presbs);
    $password = getenv('PCD_UAT_DIGITADOR_PASSWORD') ?: '';
    if ($password === '') throw new RuntimeException('Falta clave sintética UAT.');
    $lim = new I02Client($base); $auths[] = $lim;
    $tru = new I02Client($base); $auths[] = $tru;
    $reports = new I02Client($base); $auths[] = $reports;
    $anon = new I02Client($base); $auths[] = $anon;

    $a = $lim->login((int) $ids['LIM'], 'LIM-DIG-01', $password);
    $b = $tru->login((int) $ids['TRU'], 'TRU-DIG-01', $password);
    checkI02('I02-HTTP-01', $a['code'] === 200 && $b['code'] === 200, 'sesiones UAT separadas');
    $sedeA = $lim->send('POST', '/sedes', ['nombre_oficial' => 'I02-PRUEBA-' . bin2hex(random_bytes(5))], pcdKey());
    $sedeB = $tru->send('POST', '/sedes', ['nombre_oficial' => 'I02-PRUEBA-' . bin2hex(random_bytes(5))], pcdKey());
    foreach ([$sedeA, $sedeB] as $sede) {
        if (isset($sede['body']['id'])) $clean['sedes'][] = (int) $sede['body']['id'];
    }
    checkI02('I02-HTTP-02', $sedeA['code'] === 201 && $sedeB['code'] === 201, 'sedes sintéticas');
    if (!isset($sedeA['body']['id'], $sedeB['body']['id'])) throw new RuntimeException('No se crearon sedes CI.');
    $lim->selectSede((int) $sedeA['body']['id']);
    $tru->selectSede((int) $sedeB['body']['id']);
    $dni = str_pad((string) random_int(1000000, 8999999), 8, '0', STR_PAD_LEFT);
    $payload = h24(['tipo_documento' => 'DNI', 'numero_documento' => $dni,
        'nombres' => 'PERSONA PRUEBA CI', 'fecha_nacimiento' => '2024-02-29',
        'conyuge' => ['tipo_documento' => 'DNI', 'numero_documento' => '00000001'],
        'fecha_papel' => '2026-11-02']);
    $key = pcdKey();
    $first = $lim->send('POST', '/personas', $payload, $key);
    if (isset($first['body']['ficha_id'])) $clean['fichas'][] = (int) $first['body']['ficha_id'];
    if (isset($first['body']['persona_id'])) $clean['personas'][] = (int) $first['body']['persona_id'];
    checkI02('I02-HTTP-03', $first['code'] === 201 && ($first['body']['persona_reutilizada'] ?? true) === false, 'alta Persona + ficha');
    if ($first['code'] !== 201) throw new RuntimeException('Alta I02 falló; no continuar con datos inexistentes.');
    $again = $lim->send('POST', '/personas', $payload, $key);
    checkI02('I02-HTTP-04', $again['code'] === 201 && $again['body']['ficha_id'] === $first['body']['ficha_id'],
        'reintento idempotente');
    $changed = $lim->send('POST', '/personas', h24(['nombres' => 'OTRO']), $key);
    checkI02('I02-HTTP-05', $changed['code'] === 409, 'misma clave distinta carga');
    $second = $tru->send('POST', '/personas', h24([
        'tipo_documento' => 'DNI', 'numero_documento' => $dni, 'nombres' => 'NOMBRE DIVERGENTE'
    ]), pcdKey());
    if (isset($second['body']['ficha_id'])) $clean['fichas'][] = (int) $second['body']['ficha_id'];
    checkI02('I02-HTTP-06', $second['code'] === 201
        && ($second['body']['persona_reutilizada'] ?? false) === true
        && $second['body']['persona_id'] === $first['body']['persona_id'], 'reutilización entre presbiterios');
    $detail = $tru->send('GET', '/personas/' . $first['body']['persona_id']);
    checkI02('I02-HTTP-07', $detail['code'] === 200
        && ($detail['body']['persona']['nombres'] ?? '') === 'PERSONA PRUEBA CI'
        && count($detail['body']['fichas'] ?? []) >= 2, 'consolidado inmutable');
    $fakeId = $first['body']['persona_id'];
    $mismatch = $tru->send('POST', '/personas/' . $fakeId . '/fichas', h24([
        'tipo_documento' => 'DNI', 'numero_documento' => str_pad((string) random_int(1000000, 8999999), 8, '0', STR_PAD_LEFT)
    ]), pcdKey());
    checkI02('I02-HTTP-08', $mismatch['code'] === 409, 'impedir falso vínculo por ID');
    // Caso positivo de anexo por ID: antes la ruta fallaba por servicio no inicializado.
    $attached = $lim->send('POST', '/personas/' . $first['body']['persona_id'] . '/fichas',
        h24(['numero_documento' => $dni, 'nombres' => 'OTRA FICHA DEL MISMO DNI']),
        pcdKey());
    if (isset($attached['body']['ficha_id'])) $clean['fichas'][] = (int) $attached['body']['ficha_id'];
    checkI02('I02-A01', $attached['code'] === 201
        && ($attached['body']['persona_id'] ?? null) === $first['body']['persona_id']
        && ($attached['body']['persona_reutilizada'] ?? null) === true
        && ($attached['body']['ficha_id'] ?? null) !== $first['body']['ficha_id'],
        'anexo autorizado de nueva ficha a Persona existente');

    $date = $lim->send('POST', '/personas', ['fecha_nacimiento' => '2026-02-30'], pcdKey());
    checkI02('I02-HTTP-09', $date['code'] === 422, 'fecha imposible rechazada');
    $extra = $lim->send('POST', '/personas', ['subtipo_documento' => 'PROHIBIDO'], pcdKey());
    checkI02('I02-HTTP-10', $extra['code'] === 422, 'propiedad fuera de contrato');
    $missing = $lim->send('POST', '/personas', h24(['telefono' => '']), pcdKey());
    checkI02('I02-H24-01', $missing['code'] === 422, 'teléfono obligatorio');
    $missingSurname = $lim->send('POST', '/personas', h24(['apellido_materno' => null]), pcdKey());
    checkI02('I02-H24-02', $missingSurname['code'] === 422, 'apellido materno obligatorio');
    $anonResult = $anon->send('POST', '/personas', $payload, pcdKey());
    checkI02('I02-HTTP-11', $anonResult['code'] === 401, 'requiere sesión');
    $rep = $reports->login((int) $ids['LIM'], 'REPORTES-NAC', getenv('PCD_REPORTES_PASSWORD') ?: '');
    checkI02('I02-HTTP-12', $rep['code'] === 200, 'REPORTES autenticado');
    $repSave = $reports->send('POST', '/personas', $payload, pcdKey());
    checkI02('I02-HTTP-13', $repSave['code'] === 403, 'REPORTES solo lectura');
    $catalog = $lim->send('GET', '/catalogos');
    $ubigeo = $lim->send('GET', '/ubigeos?tipo=DEPARTAMENTO');
    checkI02('I02-HTTP-14', $catalog['code'] === 200 && $ubigeo['code'] === 200, 'catálogos desde BD, sin inventarlos');
    // I02-U01..U04: el catálogo de 2114 códigos suministrados se importó
    // en MySQL 8 real, preservando códigos con ceros y jerarquía por API.
    $deps = $lim->send('GET', '/ubigeos?tipo=DEPARTAMENTO');
    checkI02('I02-U01', $deps['code'] === 200 && count($deps['body']) === 25
        && in_array('01', array_column($deps['body'], 'codigo'), true),
        '25 departamentos y código 01 preservado');
    $provs = $lim->send('GET', '/ubigeos?tipo=PROVINCIA&padre_codigo=15');
    $dists = $lim->send('GET', '/ubigeos?tipo=DISTRITO&padre_codigo=1501');
    checkI02('I02-U02', $provs['code'] === 200 && $dists['code'] === 200
        && in_array('1501', array_column($provs['body'], 'codigo'), true)
        && in_array('150101', array_column($dists['body'], 'codigo'), true),
        'jerarquía Lima / Provincia Lima / Distrito Lima');
    $geoPersona = $lim->send('POST', '/personas', h24([
        'tipo_documento' => 'DNI', 'numero_documento' => str_pad((string) random_int(1000000, 8999999), 8, '0', STR_PAD_LEFT),
        'ubigeo_dpto_codigo' => '15', 'ubigeo_prov_codigo' => '1501',
        'ubigeo_dist_codigo' => '150101',
    ]), pcdKey());
    if (isset($geoPersona['body']['ficha_id'])) $clean['fichas'][] = (int) $geoPersona['body']['ficha_id'];
    if (isset($geoPersona['body']['persona_id'])) $clean['personas'][] = (int) $geoPersona['body']['persona_id'];
    checkI02('I02-U03', $geoPersona['code'] === 201, 'Persona con ubigeo real del archivo recibido');
    $badGeo = $lim->send('POST', '/personas', h24([
        'ubigeo_dpto_codigo' => '15', 'ubigeo_prov_codigo' => '0101',
    ]), pcdKey());
    checkI02('I02-U04', $badGeo['code'] === 422, 'rechazar provincia de otro departamento');
    // C.E. sin verificación remota: captura de formato orientativo y NO fusión
    // del documento sin prueba de identidad otorgada por Migraciones.
    $ceA = $lim->send('POST', '/personas', h24([
        'tipo_documento' => 'CE', 'numero_documento' => 'N000123456',
    ]), pcdKey());
    $ceB = $lim->send('POST', '/personas', h24([
        'tipo_documento' => 'CE', 'numero_documento' => 'N000123456',
    ]), pcdKey());
    foreach ([$ceA, $ceB] as $record) {
        if (isset($record['body']['ficha_id'])) $clean['fichas'][] = (int) $record['body']['ficha_id'];
        if (isset($record['body']['persona_id'])) $clean['personas'][] = (int) $record['body']['persona_id'];
    }
    checkI02('I02-CE01', $ceA['code'] === 201 && $ceB['code'] === 201
        && ($ceA['body']['persona_id'] ?? null) !== ($ceB['body']['persona_id'] ?? null),
        'C.E. se captura sin fusionar automáticamente');
    $paper = $lim->send('POST', '/personas', h24([
        'tipo_documento' => 'OTRO', 'numero_documento' => 'PAPEL-PRUEBA',
        'control_papel' => '{"ficha_completa":true,"persona_validada":true}',
    ]), pcdKey());
    if (isset($paper['body']['ficha_id'])) $clean['fichas'][] = (int) $paper['body']['ficha_id'];
    if (isset($paper['body']['persona_id'])) $clean['personas'][] = (int) $paper['body']['persona_id'];
    $paperDetail = isset($paper['body']['ficha_id'])
        ? $lim->send('GET', '/fichas/' . $paper['body']['ficha_id']) : ['code' => 0, 'body' => []];
    checkI02('I02-H14', $paper['code'] === 201 && $paperDetail['code'] === 200
        && str_contains((string) ($paperDetail['body']['control_papel'] ?? ''), '"persona_validada":true'),
        'marcas del formulario físico conservadas en ficha');
    $check = $pdo->prepare("SELECT COUNT(*) FROM auditoria_evento WHERE recurso_tipo = 'FICHA' AND recurso_id IN (?, ?)");
    $check->execute([$first['body']['ficha_id'], $second['body']['ficha_id']]);
    checkI02('I02-HTTP-15', (int) $check->fetchColumn() === 0, 'alta sin auditoría_evento');
    $line = $pdo->prepare("SELECT COUNT(*) FROM persona WHERE clave_identidad = ?");
    $line->execute(['DNI:' . $dni]);
    checkI02('I02-HTTP-16', (int) $line->fetchColumn() === 1, 'UNIQUE de identidad preservado');

    // Concurrencia real: dos sesiones del mismo usuario, misma clave/payload.
    $limOther = new I02Client($base); $auths[] = $limOther;
    $limOtherLogin = $limOther->login((int) $ids['LIM'], 'LIM-DIG-01', $password);
    if ($limOtherLogin['code'] !== 200) throw new RuntimeException('Segunda sesión de carrera falló.');
    $limOther->selectSede((int) $sedeA['body']['id']);
    $raceDni = str_pad((string) random_int(1000000, 8999999), 8, '0', STR_PAD_LEFT);
    $racePayload = h24(['tipo_documento' => 'DNI', 'numero_documento' => $raceDni, 'nombres' => 'PRUEBA PARALELA']);
    $raceKey = pcdKey();
    $races = concurrentPostsI02($base, [
        ['client' => $lim, 'body' => $racePayload, 'key' => $raceKey],
        ['client' => $limOther, 'body' => $racePayload, 'key' => $raceKey],
    ]);
    foreach ($races as $race) {
        if (isset($race['body']['ficha_id'])) $clean['fichas'][] = (int) $race['body']['ficha_id'];
        if (isset($race['body']['persona_id'])) $clean['personas'][] = (int) $race['body']['persona_id'];
    }
    checkI02('I02-HTTP-17', $races[0]['code'] === 201 && $races[1]['code'] === 201
        && $races[0]['body']['ficha_id'] === $races[1]['body']['ficha_id'],
        'doble clic simultáneo una sola ficha');
    $p = $pdo->prepare('SELECT COUNT(*) FROM persona WHERE clave_identidad = ?');
    $p->execute(['DNI:' . $raceDni]);
    checkI02('I02-HTTP-18', (int) $p->fetchColumn() === 1, 'identidad confiable única bajo carrera');
    // Mismo DNI, claves distintas: una Persona, dos fichas legítimas.
    $nextKeyA = pcdKey(); $nextKeyB = pcdKey();
    $parallel = concurrentPostsI02($base, [
        ['client' => $lim, 'body' => $racePayload, 'key' => $nextKeyA],
        ['client' => $limOther, 'body' => $racePayload, 'key' => $nextKeyB],
    ]);
    foreach ($parallel as $race) {
        if (isset($race['body']['ficha_id'])) $clean['fichas'][] = (int) $race['body']['ficha_id'];
        if (isset($race['body']['persona_id'])) $clean['personas'][] = (int) $race['body']['persona_id'];
    }
    checkI02('I02-HTTP-19', $parallel[0]['code'] === 201 && $parallel[1]['code'] === 201
        && ($parallel[0]['body']['persona_id'] ?? 0) === ($parallel[1]['body']['persona_id'] ?? -1)
        && ($parallel[0]['body']['ficha_id'] ?? 0) !== ($parallel[1]['body']['ficha_id'] ?? 0),
        'dos fuentes paralelas independientes sobre misma Persona');
} catch (Throwable $e) {
    checkI02('I02-SETUP', false, get_class($e) . ': ' . $e->getMessage());
} finally {
    if ($pdo !== null) {
        try {
            foreach (array_unique($clean['keys']) as $key) {
                $pdo->prepare('DELETE FROM idempotencia WHERE clave = ?')->execute([$key]);
            }
            foreach (array_unique($clean['fichas']) as $id) {
                $pdo->prepare('DELETE FROM ficha WHERE id = ?')->execute([$id]);
            }
            foreach (array_unique($clean['personas']) as $id) {
                $pdo->prepare('DELETE FROM persona WHERE id = ?')->execute([$id]);
            }
            foreach (array_unique($clean['sedes']) as $id) {
                // Las sesiones UAT hacen referencia a la sede seleccionada.
                // Desvincularla antes de eliminar la sede sintética; no tocar
                // información de fichas auténticas (CI usa BD aislada).
                $pdo->prepare('UPDATE sesion SET sede_id = NULL WHERE sede_id = ?')->execute([$id]);
                $pdo->prepare('DELETE FROM organizacion WHERE id = ?')->execute([$id]);
            }
        } catch (Throwable $e) {
            checkI02('I02-CLEANUP', false, 'Limpieza sintética falló: ' . $e->getMessage());
        }
    }
}
$fail = count(array_filter($checks, static fn (array $r): bool => $r['estado'] === 'FAIL'));
echo "I02 HTTP: PASS=" . (count($checks) - $fail) . " FAIL=$fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);
