<?php

final class LanguageTest extends \PHPUnit\Framework\TestCase
{
    public function testCode(): void
    {
        $lang = new \Aurora\System\Language();
        $this->assertEquals($lang->getCode(), '');
        $lang->setCode('en');
        $this->assertEquals($lang->getCode(), 'en');
    }

    public function testGet(): void
    {
        $lang = new \Aurora\System\Language([
            'en' => [
                'hello' => 'Hello',
                'world' => 'World',
            ],
            'es' => [
                'hello' => 'Hola',
                'world' => 'Mundo',
            ],
        ]);
        $this->expectException(\InvalidArgumentException::class);
        $lang->get('hello');

        $lang->setCode('en');
        $this->assertEquals('Hello', $lang->get('hello'));
        $this->assertEquals('World', $lang->get('world'));

        $lang->setCode('es');
        $this->assertEquals('Hola', $lang->get('hello'));
        $this->assertEquals('Mundo', $lang->get('world'));
    }

    public function testGetAll(): void
    {
        $lang = new \Aurora\System\Language([
            'en' => [
                'hello' => 'Hello',
                'world' => 'World',
            ],
            'es' => [
                'hello' => 'Hola',
                'world' => 'Mundo',
            ],
            'pr' => [
                'hello' => 'Olá',
                'world' => 'Mundo',
            ],
        ]);

        $this->assertEquals([
            'en',
            'es',
            'pr',
        ], $lang->getAll());
    }
}
