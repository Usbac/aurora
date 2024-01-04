<?php

final class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGet(): void
    {
        \Aurora\System\Container::set('array', [ 1, 2, 3 ]);
        $this->assertEquals([ 1, 2, 3 ], \Aurora\System\Container::get('array'));

        \Aurora\System\Container::set('closure', fn($key) => "Value is $key");
        $this->assertEquals("Value is foo", \Aurora\System\Container::get('closure', [ 'foo' ]));

        $this->expectException(\InvalidArgumentException::class);
        \Aurora\System\Container::get('non-existent');
    }
}
