<?php

final class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testCode(): void
    {
        $route = new \Aurora\System\Route();
        $route->code(200, fn() => 'Success');
        $route->code(301, fn() => 'Redirect');
        $route->code(404, fn() => 'Not found');
        $route->code(418, fn() => 'I\'m a teapot');

        ob_start();
        $route->handleRouteCode(200);
        $this->assertEquals('Success', ob_get_contents());
        ob_end_clean();

        ob_start();
        $route->handleRouteCode(404);
        $this->assertEquals('Not found', ob_get_contents());
        ob_end_clean();

        ob_start();
        $route->handleRouteCode(500);
        $this->assertEquals('', ob_get_contents());
        ob_end_clean();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRouting(): void
    {
        $route = new \Aurora\System\Route();
        $route->get('admin', fn() => 'Admin');
        $route->get('admin/users', fn() => 'Users');
        $route->get('json:api/admin/users', fn() => json_encode([ 'John', 'Alex', 'Kia' ]));
        $route->get('admin/users/{id}', fn() => 'User id: ' . $_GET['id']);
        $route->post('admin/users/{id}', fn() => '[POST] User id: ' . $_GET['id']);
        $route->any('blog', fn() => 'Blog');
        $route->any('blog/*', fn() => 'Blog subpage');

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $this->assertRoute($route, 'admin', 'Admin');
        $this->assertRoute($route, 'admin/users', 'Users');
        $this->assertRoute($route, 'api/admin/users', '["John","Alex","Kia"]');
        $this->assertRoute($route, 'api/admin/users///', '["John","Alex","Kia"]');
        $this->assertRoute($route, 'admin/users/2', 'User id: 2');
        $this->assertRoute($route, 'admin/whatever', '');
        $this->assertRoute($route, 'blog', 'Blog');
        $this->assertRoute($route, 'blog/whatever', 'Blog subpage');
        $this->assertRoute($route, 'blog/whatever/2', 'Blog subpage');

        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->assertRoute($route, 'admin/users/2', '[POST] User id: 2');

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $this->assertRoute($route, 'admin/users', '');
        $this->assertRoute($route, 'blog/whatever', 'Blog subpage');
        $this->assertRoute($route, 'blog/whatever/2', 'Blog subpage');

        /**
         * Middlewares
         */

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $route->middleware('admin/*', function() {
            echo '[MIDDLEWARE]';
        });

        $this->assertRoute($route, 'admin', 'Admin');
        $this->assertRoute($route, 'admin/users', '[MIDDLEWARE]Users');
        $this->assertRoute($route, 'admin/users/2', '[MIDDLEWARE]User id: 2');

        $route->middleware('admin/*', function() {
            echo '[MIDDLEWARE 2]';
        });

        $this->assertRoute($route, 'admin/users', '[MIDDLEWARE][MIDDLEWARE 2]Users');
        $this->assertRoute($route, 'admin/users/2', '[MIDDLEWARE][MIDDLEWARE 2]User id: 2');

        $_SERVER['REQUEST_METHOD'] = 'PUT';

        $this->assertRoute($route, 'admin/users', '[MIDDLEWARE][MIDDLEWARE 2]');
        $this->assertRoute($route, 'admin/users/2', '[MIDDLEWARE][MIDDLEWARE 2]');
    }

    private function assertRoute($route, $url, $expected_content)
    {
        ob_start();
        $route->handleRoute($url);
        $this->assertEquals($expected_content, ob_get_contents());
        ob_end_clean();
    }
}
