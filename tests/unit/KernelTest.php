<?php

final class KernelTest extends \PHPUnit\Framework\TestCase
{
    public function testConfig(): void
    {
        $kernel = new \Aurora\System\Kernel([
            'bootstrap' => fn() => null,
            'content' => 'tests/fixtures/content',
            'mail' => fn($to, $subject, $message) => mail($to, $subject, $message),
            'views' => 'tests/fixtures/views',
        ]);

        $this->assertEquals('tests/fixtures/views', $kernel::config('views'));
        $this->assertEquals('tests/fixtures/content', $kernel::config('content'));
        $this->assertIsCallable($kernel::config('mail'));
        $this->assertNull($kernel::config('another'));
    }

    /**
     * @runInSeparateProcess
     */
    public function testInit(): void
    {
        $kernel = new \Aurora\System\Kernel([
            'bootstrap' => function (\Aurora\System\Kernel $kernel) {
                $kernel->router->get('blog', fn() => 'Blog');
                $kernel->router->get('blog/hello', fn() => 'Hello');
                $kernel->router->code(404, fn() => 'Not found');
            },
            'content' => 'tests/fixtures/content',
            'mail' => fn($to, $subject, $message) => mail($to, $subject, $message),
            'views' => 'tests/fixtures/views',
        ]);

        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        $kernel->init('blog');
        $this->assertEquals('Blog', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();

        ob_start();
        $kernel->init('blog/hello');
        $this->assertEquals('Hello', ob_get_contents());
        $this->assertEquals(200, http_response_code());
        ob_end_clean();

        ob_start();
        $kernel->init('blog/what?');
        $this->assertEquals('Not found', ob_get_contents());
        $this->assertEquals(404, http_response_code());
        ob_end_clean();
    }
}
