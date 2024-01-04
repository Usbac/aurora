<?php

final class ViewTest extends \PHPUnit\Framework\TestCase
{
    public function testGet(): void
    {
        $view = new \Aurora\System\View(dirname(__DIR__) . '/fixtures/views');

        $this->assertEquals("Hello, John.\n\nThis is a footer.\nCopyright 2024",
            $view->get('hello.html', [ 'name' => 'John', 'year' => 2024 ]));

        $this->assertEquals("This is a footer.\nCopyright 2025",
            $view->get('footer.html', [ 'year' => 2025 ]));

        $this->expectException(Error::class);
        @$view->get('another_view.html');
    }

    public function testHelperClass(): void
    {
        $view = new \Aurora\System\View(dirname(__DIR__) . '/fixtures/views', new Class {
            public function prettyName($name)
            {
                return "Mr $name";
            }
        });

        $this->assertEquals("Hello, Mr Alex.\n", $view->get('hello2.html', [ 'name' => 'Alex' ]));
    }
}
