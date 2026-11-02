<?php

declare(strict_types=1);

/**
 * Runner HTTP del andamiaje P01. No usa framework ni dependencias.
 *
 * Uso:
 *   php tests/api/run.php --base-url=http://127.0.0.1:8081/api/v1
 *
 * Estados:
 *   PASS             verificación ejecutada y correcta.
 *   FAIL             verificación ejecutada y fallida.
 *   BLOCKED          no ejecutable por dependencia (p. ej. MySQL ausente).
 *   NOT_IMPLEMENTED  endpoint del contrato aún no implementado (501 esperado).
 *
 * Sale con código 1 solo si existe FAIL. Nunca simula resultados.
 */

$baseUrl = 'http://127.0.0.1:8081/api/v1';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--base-url=')) {
        $baseUrl = substr($argument, strlen('--base-url='));
    }
}

$baseUrl = rtrim($baseUrl, '/');
$sessionName = getenv('PCD_SESSION_NAME') ?: 'pcd_test_sid';
$origin = 'http://localhost:5173';

/**
 * @param list<string> $headers
 * @return array{status:int, headers:array<string,string>, body:string, json:mixed, error:string}
 */
function http_request(string $method, string $url, array $headers = [], ?string $body = null): array
{
    $handle = curl_init($url);
    curl_setopt_array($handle, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => false,
    ]);

    if ($headers !== []) {
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
    }

    if ($body !== null) {
        curl_setopt($handle, CURLOPT_POSTFIELDS, $body);
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

/** @var list<array{id:string, run:callable}> $checks */
$checks = [];
$base = $baseUrl;

$checks[] = ['id' => 'health_status', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');

    return $response['status'] === 200
        ? ['PASS', '200']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'health_json_content_type', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');
    $contentType = header_value($response, 'content-type');

    return str_contains($contentType, 'application/json')
        ? ['PASS', $contentType]
        : ['FAIL', 'Content-Type: ' . $contentType];
}];

$checks[] = ['id' => 'health_ok_true', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');

    return json_path($response, 'ok') === true
        ? ['PASS', 'ok=true']
        : ['FAIL', 'ok ausente/falso'];
}];

$checks[] = ['id' => 'health_service_name', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');

    return json_path($response, 'service') === 'pcd-api'
        ? ['PASS', 'service=pcd-api']
        : ['FAIL', 'service distinto'];
}];

$checks[] = ['id' => 'health_db_status_reported', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');
    $status = json_path($response, 'database.status');

    return in_array($status, ['up', 'down'], true)
        ? ['PASS', (string) $status]
        : ['FAIL', 'database.status ausente'];
}];

$checks[] = ['id' => 'db_connectivity', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');
    $status = json_path($response, 'database.status');

    if ($status === 'up') {
        return ['PASS', 'MySQL responde'];
    }

    return ['BLOCKED', 'MySQL no disponible en este entorno'];
}];

$checks[] = ['id' => 'request_id_in_body', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');
    $requestId = json_path($response, 'request_id');

    return is_string($requestId) && strlen($requestId) >= 8
        ? ['PASS', $requestId]
        : ['FAIL', 'request_id inválido'];
}];

$checks[] = ['id' => 'request_id_header_echo', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');
    $header = header_value($response, 'x-request-id');
    $body = (string) json_path($response, 'request_id');

    return $header !== '' && $header === $body
        ? ['PASS', 'X-Request-Id coincide']
        : ['FAIL', 'cabecera/cuerpo no coinciden'];
}];

$checks[] = ['id' => 'unknown_route_404', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/ruta-inexistente');

    return $response['status'] === 404 && json_path($response, 'error.code') === 'not_found'
        ? ['PASS', '404 not_found']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'method_not_allowed_405', 'run' => static function () use ($base): array {
    $response = http_request('DELETE', $base . '/health');
    $allow = header_value($response, 'allow');

    return $response['status'] === 405 && str_contains($allow, 'GET')
        ? ['PASS', '405 Allow: ' . $allow]
        : ['FAIL', 'HTTP ' . $response['status'] . ' Allow: ' . $allow];
}];

$checks[] = ['id' => 'error_envelope_shape', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/ruta-inexistente');
    $code = json_path($response, 'error.code');
    $message = json_path($response, 'error.message');
    $requestId = json_path($response, 'request_id');

    return is_string($code) && is_string($message) && is_string($requestId)
        ? ['PASS', 'error + request_id']
        : ['FAIL', 'sobre incompleto'];
}];

$checks[] = ['id' => 'malformed_json_400', 'run' => static function () use ($base): array {
    $response = http_request('POST', $base . '/auth/login', ['Content-Type: application/json'], '{');

    return $response['status'] === 400 && json_path($response, 'error.code') === 'invalid_json'
        ? ['PASS', '400 invalid_json']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'login_placeholder_501', 'run' => static function () use ($base): array {
    $payload = json_encode(['presbiterio_id' => 1, 'usuario' => 'LIM-DIG-01', 'password' => 'x']);
    $response = http_request('POST', $base . '/auth/login', ['Content-Type: application/json'], (string) $payload);

    return $response['status'] === 501 && json_path($response, 'error.code') === 'not_implemented'
        ? ['NOT_IMPLEMENTED', '501 not_implemented (I01)']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'auth_me_placeholder_501', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/auth/me');

    return $response['status'] === 501 && json_path($response, 'error.code') === 'not_implemented'
        ? ['NOT_IMPLEMENTED', '501 not_implemented (I01)']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'presbiterios_placeholder_501', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/presbiterios');

    return $response['status'] === 501 && json_path($response, 'error.code') === 'not_implemented'
        ? ['NOT_IMPLEMENTED', '501 not_implemented (I01)']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'csrf_required_on_logout', 'run' => static function () use ($base, $sessionName): array {
    $response = http_request(
        'POST',
        $base . '/auth/logout',
        ['Cookie: ' . $sessionName . '=testsession123'],
    );

    return $response['status'] === 403 && json_path($response, 'error.code') === 'csrf_invalid'
        ? ['PASS', '403 csrf_invalid']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'cors_preflight_allowed', 'run' => static function () use ($base, $origin): array {
    $response = http_request('OPTIONS', $base . '/health', ['Origin: ' . $origin]);

    return $response['status'] === 204 && header_value($response, 'access-control-allow-origin') === $origin
        ? ['PASS', '204 CORS']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'security_headers', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/health');
    $nosniff = header_value($response, 'x-content-type-options');
    $cache = header_value($response, 'cache-control');

    return str_contains($nosniff, 'nosniff') && str_contains($cache, 'no-store')
        ? ['PASS', 'nosniff + no-store']
        : ['FAIL', 'cabeceras ausentes'];
}];

$checks[] = ['id' => 'no_stack_trace', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/ruta-inexistente');
    $leaks = ['Stack trace', 'vendor/', 'SQLSTATE', '.php'];

    foreach ($leaks as $needle) {
        if (str_contains($response['body'], $needle)) {
            return ['FAIL', 'respuesta contiene ' . $needle];
        }
    }

    return ['PASS', 'sin trazas'];
}];

$checks[] = ['id' => 'head_health_ok', 'run' => static function () use ($base): array {
    $response = http_request('HEAD', $base . '/health');

    return $response['status'] === 200 && $response['body'] === ''
        ? ['PASS', '200 sin cuerpo']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'nested_unknown_404', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base . '/auth/desconocido');

    return $response['status'] === 404
        ? ['PASS', '404']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$checks[] = ['id' => 'base_path_404', 'run' => static function () use ($base): array {
    $response = http_request('GET', $base);

    return $response['status'] === 404
        ? ['PASS', '404 en raíz del API']
        : ['FAIL', 'HTTP ' . $response['status']];
}];

$results = [];

foreach ($checks as $check) {
    try {
        [$status, $note] = $check['run']();
    } catch (Throwable $exception) {
        $status = 'FAIL';
        $note = $exception->getMessage();
    }

    $results[] = ['id' => $check['id'], 'status' => $status, 'note' => $note];
}

$counts = ['PASS' => 0, 'FAIL' => 0, 'BLOCKED' => 0, 'NOT_IMPLEMENTED' => 0];

fwrite(STDOUT, 'PCD API runner — ' . $baseUrl . PHP_EOL);
fwrite(STDOUT, sprintf("%-28s %-16s %s\n", 'VERIFICACIÓN', 'ESTADO', 'DETALLE'));
fwrite(STDOUT, str_repeat('-', 78) . PHP_EOL);

foreach ($results as $result) {
    $counts[$result['status']] = ($counts[$result['status']] ?? 0) + 1;
    fwrite(STDOUT, sprintf("%-28s %-16s %s\n", $result['id'], $result['status'], $result['note']));
}

fwrite(STDOUT, str_repeat('-', 78) . PHP_EOL);
fwrite(STDOUT, sprintf(
    "Resumen: PASS=%d FAIL=%d BLOCKED=%d NOT_IMPLEMENTED=%d Total=%d\n",
    $counts['PASS'],
    $counts['FAIL'],
    $counts['BLOCKED'],
    $counts['NOT_IMPLEMENTED'],
    count($results),
));

if ($counts['FAIL'] > 0) {
    fwrite(STDERR, 'Runner: existen verificaciones FAIL.' . PHP_EOL);
    exit(1);
}

exit(0);
