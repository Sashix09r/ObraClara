<?php

declare(strict_types=1);

namespace ObraClara\Http;

use ObraClara\Support\NotFoundException;

/**
 * Encaminhador mínimo: método + padrão com {id} numérico.
 */
final class Router
{
    /** @var list<array{method: string, regex: string, keys: list<string>, handler: array{class-string, string}}> */
    private array $routes = [];

    /** @param array{class-string, string} $handler */
    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /** @param array{class-string, string} $handler */
    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /** @param array{class-string, string} $handler */
    private function add(string $method, string $pattern, array $handler): void
    {
        $keys = [];
        $regex = preg_replace_callback(
            '/\{([a-z_]+)\}/',
            static function (array $matches) use (&$keys): string {
                $keys[] = $matches[1];

                return '(\d+)';
            },
            $pattern
        );

        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#',
            'keys'    => $keys,
            'handler' => $handler,
        ];
    }

    /**
     * @return array<string, string>|null parâmetros capturados
     */
    public function match(string $method, string $path): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches) === 1) {
                array_shift($matches);

                return array_combine($route['keys'], $matches) ?: [];
            }
        }

        return null;
    }

    public function dispatch(string $method, string $path): void
    {
        $params = $this->match($method, $path);

        if ($params === null) {
            throw new NotFoundException('A página pedida não existe.');
        }

        [$class, $action] = $this->handlerFor($method, $path);
        $controller = new $class();
        $controller->{$action}($params);
    }

    /**
     * @return array{class-string, string}
     */
    private function handlerFor(string $method, string $path): array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['regex'], $path) === 1) {
                return $route['handler'];
            }
        }

        throw new NotFoundException('A página pedida não existe.');
    }
}
