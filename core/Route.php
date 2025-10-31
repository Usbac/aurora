<?php

namespace Aurora\Core;

/**
 * @method void get(string|array $url, \Closure $func)
 * @method void post(string|array $url, \Closure $func)
 * @method void put(string|array $url, \Closure $func)
 * @method void patch(string|array $url, \Closure $func)
 * @method void delete(string|array $url, \Closure $func)
 * @method void any(string|array $url, \Closure $func)
 */
final class Route
{
    private const GET_FORMAT = '/\{(.*)\}/';
    private const PREFIXES = [
        'csv:'   => 'text/csv',
        'json:'  => 'application/json',
        'pdf:'   => 'application/pdf',
        'plain:' => 'text/plain',
        'xml:'   => 'application/xml',
    ];
    private const HTTP_METHODS = [
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'any',
    ];

    /**
     * List of routes
     * @var array
     */
    private array $routes = [];

    /**
     * List of middlewares
     * @var array
     */
    private array $middlewares = [];

    /**
     * List of routes for status codes
     * @var array
     */
    private array $codes = [];

    /**
     * Proxy for the HTTP Methods
     * @throws \InvalidArgumentException
     * @param string $name the method name
     * @param mixed $args the method arguments
     */
    public function __call(string $name, $args): void
    {
        if (!in_array($name, self::HTTP_METHODS)) {
            return;
        }

        $routes = $args[0] ?? null;

        if (!is_string($routes) && !is_array($routes)) {
            throw new \InvalidArgumentException('url must be of type string or array');
        } elseif (!isset($args[1]) || (!is_array($args[1]) && !($args[1] instanceof \Closure))) {
            throw new \InvalidArgumentException('func must be an instance of \Closure');
        }

        foreach (is_array($routes) ? $routes : [ $routes ] as $route) {
            $this->addRoute(
                $route,
                strtoupper($name),
                $args[1],
                is_numeric($args[2] ?? null) ? ((int) $args[2]) : null
            );
        }
    }

    /**
     * Adds a middleware
     * @param string $url the url
     * @param \Closure $func the function to be executed
     */
    public function middleware(string $url, \Closure $func): void
    {
        $this->middlewares[] = [
            'url'    => $url,
            'action' => $func,
        ];
    }

    /**
     * Adds a route that will work only for a status code
     * @param int $code the status code
     * @param \Closure $func the function to be executed
     * when getting the status code
     */
    public function code(int $code, \Closure $func): void
    {
        $this->codes[$code] = $func;
    }

    /**
     * Handles a code route
     * @param int $code the status code
     */
    public function handleRouteCode(int $code): void
    {
        if (array_key_exists($code, $this->codes)) {
            echo $this->codes[$code]();
        }
    }

    /**
     * Handles a url
     * @param string $url the url to handle
     * @param array $request_body the request body data
     */
    public function handleRoute(string $url, array $request_body = []): void
    {
        $current = array_filter(explode('/', $url));
        $len = count($current) - 1;

        foreach ($this->middlewares as $middleware) {
            $route = array_filter(explode('/', $middleware['url']));

            if ($this->matchesRoute($current, $len, $route)) {
                $this->mapParameters($current, $route);
                $middleware['action']($request_body);
            }
        }

        foreach ($this->routes as $val) {
            if ($val['method'] !== 'ANY' &&
                $val['method'] !== $_SERVER['REQUEST_METHOD']) {
                continue;
            }

            $route = array_filter(explode('/', $val['url']));

            if ($this->matchesRoute($current, $len, $route)) {
                $this->mapParameters($current, $route);
                header('Content-Type: ' . $val['content_type']);
                http_response_code($val['status'] ?? 200);
                echo $val['action']($request_body);
                return;
            }
        }

        http_response_code(404);
    }

    /**
     * Adds a route to the list
     * @param mixed $url the url
     * @param string $method the url HTTP method
     * @param mixed $function the url function
     * @param int|null $status the HTTP response code
     */
    private function addRoute(string $url, string $method, $function, ?int $status): void
    {
        $content_type = 'text/html';

        // Remove content-type prefix from route
        foreach (self::PREFIXES as $key => $val) {
            if (str_starts_with($url, $key)) {
                $url = mb_substr($url, mb_strlen($key));
                $content_type = $val;
            }
        }

        $url = trim($url, '/');

        preg_match(self::GET_FORMAT, $url, $matches);

        $this->routes[] = [
            'url'          => $url,
            'action'       => $function,
            'method'       => $method,
            'status'       => $status,
            'content_type' => $content_type,
        ];
    }

    /**
     * Returns true if the current route matches the given one, false otherwise
     * @param array $current the current route array
     * @param int $current_len the size of the current route array
     * @param array $route the route array to test
     * @return bool true if the current route matches the given one, false otherwise
     */
    private function matchesRoute(array $current, int $current_len, array $route): bool
    {
        if (empty($current) && empty($route)) {
            return true;
        }

        $route_len = count($route) - 1;

        if (($route[0] ?? '') === '*') {
            return true;
        }

        for ($i = 0; $i <= $route_len && $i <= $current_len; $i++) {
            if ($current[$i] !== $route[$i] && !$this->isGet($route[$i]) && $route[$i] !== '*') {
                break;
            }

            if ($route[$i] === '*' ||
                ($i === $route_len && $i === $current_len)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Maps the current route GET parameters
     * @param array $current the current route array
     * @param array $route the route array which matches the current route
     */
    private function mapParameters(array $current, array $route): void
    {
        $current_len = count($current) - 1;
        $route_len = count($route) - 1;

        for ($i = 0; $i <= $route_len && $i <= $current_len; $i++) {
            if ($this->isGet($route[$i])) {
                $_GET[preg_replace(self::GET_FORMAT, '$1', $route[$i])] = $current[$i];
            }
        }
    }

    /**
     * Returns true if a string has the format of a GET variable, false otherwise
     * @param string $str the string
     * @return bool true if the string has the format of a route GET variable, false otherwise
     */
    private function isGet(string $str): bool
    {
        return preg_match(self::GET_FORMAT, $str);
    }
}
