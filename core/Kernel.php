<?php

namespace Aurora\Core;

final class Kernel
{
    public const VERSION = '0.3.3';

    /**
     * System configuration
     * @var array
     */
    private static array $config = [];

    /**
     * Route system
     * @var \Aurora\Core\Route
     */
    public \Aurora\Core\Route $router;

    /**
     * @param array $config the system configuration.
     * Expected keys:
     * bootstrap -> php file to load at the start of the request
     * content -> Directory of the public content
     * mail -> Closure for sending emails
     * views -> Directory of the views
     */
    public function __construct(array $config)
    {
        $this->router = new Route;

        foreach ($config as $key => $val) {
            self::$config[$key] = $val;
        }

        if (is_callable(self::$config['bootstrap'] ?? null)) {
            self::$config['bootstrap']($this);
        }
    }

    /**
     * Initializes the given url
     * @param string $url the url
     */
    public function init(string $url): void
    {
        $this->router->handleRoute($url);
        $this->router->handleRouteCode(http_response_code());
    }

    /**
     * Returns the specified config value
     * @param string $key the key to obtain
     * @return mixed the config value or null if it does not exist
     */
    public static function config(string $key): mixed
    {
        return self::$config[$key] ?? null;
    }
}
