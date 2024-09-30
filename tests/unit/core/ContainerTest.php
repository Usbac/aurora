<?php

final class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGet(): void
    {
        \Aurora\Core\Container::set('array', [ 1, 2, 3 ]);
        $this->assertEquals([ 1, 2, 3 ], \Aurora\Core\Container::get('array'));

        \Aurora\Core\Container::set('closure', fn($key) => "Value is $key");
        $this->assertEquals("Value is foo", \Aurora\Core\Container::get('closure', [ 'foo' ]));

        $this->expectException(\InvalidArgumentException::class);
        \Aurora\Core\Container::get('non-existent');
    }
}
