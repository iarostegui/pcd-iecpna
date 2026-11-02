<?php

declare(strict_types=1);

namespace Pcd\Http;

/**
 * Router explícito y ligero: sin framework, sin dependencias externas.
 * Los patrones usan segmentos {param}; HEAD se resuelve con la ruta GET.
 */
final class Router
{
    /** @var array<int, array{method: string, pattern: string, regex: string, handler: callable, csrf: bool}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler, bool $csrf = false): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $pattern,
            'regex' => '#^' . preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $pattern) . '$#',
            'handler' => $handler,
            'csrf' => $csrf,
        ];
    }

    /**
     * @return array{status:int, handler?:callable, params?:array<string,string>, csrf?:bool, allow?:list<string>}
     */
    public function resolve(Request $request): array
    {
        $path = rtrim($request->path, '/') ?: '/';
        $allowed = [];

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            if ($route['method'] === $request->method || ($request->method === 'HEAD' && $route['method'] === 'GET')) {
                $params = array_filter(
                    $matches,
                    static fn (int|string $key): bool => is_string($key),
                    ARRAY_FILTER_USE_KEY,
                );

                return [
                    'status' => 200,
                    'handler' => $route['handler'],
                    'params' => $params,
                    'csrf' => $route['csrf'],
                ];
            }

            $allowed[$route['method']] = true;
        }

        if ($allowed !== []) {
            $methods = array_keys($allowed);
            sort($methods);

            return ['status' => 405, 'allow' => $methods];
        }

        return ['status' => 404];
    }

    /** @return list<string> */
    public function patterns(): array
    {
        return array_map(static fn (array $route): string => $route['method'] . ' ' . $route['pattern'], $this->routes);
    }
}
