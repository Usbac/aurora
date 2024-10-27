<?php

final class DBTest extends \PHPUnit\Framework\TestCase
{
    private static \Aurora\Core\DB $db;

    private static $db_file;

    public function testQuery(): void
    {
        self::$db_file = dirname(__DIR__, 2) . '/fixtures/db.sqlite';
        file_put_contents(self::$db_file, '');

        self::$db = new \Aurora\Core\DB('sqlite:' . self::$db_file);

        $this->assertInstanceOf(\PDOStatement::class, self::$db->query('CREATE TABLE users (
            `id` INTEGER PRIMARY KEY,
            `name` TEXT,
            `slug` VARCHAR(255) UNIQUE
        )'));

        $this->assertInstanceOf(\PDOStatement::class, self::$db->query('CREATE TABLE links (
            `id` INTEGER PRIMARY KEY,
            `title` TEXT,
            `url` TEXT,
            `status` INTEGER
        )'));

        $this->expectException(\PDOException::class);
        self::$db->query('SELECT * FROM users WHERE');
    }

    /**
     * @depends testQuery
     */
    public function testInsert(): void
    {
        $this->assertEquals(1, self::$db->insert('users', [
            'name' => 'Alex',
            'slug' => 'alex',
        ]));
        $this->assertEquals(2, self::$db->insert('users', [
            'name' => 'John Doe',
            'slug' => 'john-doe',
        ]));
    }

    /**
     * @depends testInsert
     */
    public function testUpdate(): void
    {
        $this->assertFalse(self::$db->update('users', [ 'name' => 'Alex 2' ], 5));
        $this->assertTrue(self::$db->update('users', [ 'name' => 'Alex 2' ], 1));
        $this->assertEquals([
            'id' => 1,
            'name' => 'Alex 2',
            'slug' => 'alex',
        ], self::$db->query('SELECT * FROM users WHERE id = 1')->fetch());
    }

    /**
     * @depends testUpdate
     */
    public function testReplace(): void
    {
        // New
        $this->assertEquals(3, self::$db->replace('users', [
            'id' => 3,
            'name' => 'New user',
            'slug' => 'new',
        ]));
        $this->assertEquals([
            'id' => 3,
            'name' => 'New user',
            'slug' => 'new',
        ], self::$db->query('SELECT * FROM users WHERE id = 3')->fetch());

        // Replaced
        $this->assertEquals(1, self::$db->replace('users', [
            'id' => 1,
            'name' => 'Alex 3',
            'slug' => 'alex',
        ]));
        $this->assertEquals([
            'id' => 1,
            'name' => 'Alex 3',
            'slug' => 'alex',
        ], self::$db->query('SELECT * FROM users WHERE id = 1')->fetch());
    }

    /**
     * @depends testReplace
     */
    public function testCount(): void
    {
        $this->assertEquals(3, self::$db->count('users'));
        $this->assertEquals(0, self::$db->count('links'));
    }

    /**
     * @depends testCount
     */
    public function testDelete(): void
    {
        $this->assertTrue(self::$db->delete('users', 3));
        $this->assertTrue(self::$db->delete('users', 1));
        $this->assertEmpty(self::$db->query('SELECT * FROM users WHERE id = 1')->fetch());
    }

    public function testEscape(): void
    {
        $this->assertEquals('users', self::$db->escape('users"'));
        $this->assertEquals('users ', self::$db->escape('users\' ='));
        $this->assertEquals('table ', self::$db->escape('table ?'));
    }

    public static function tearDownAfterClass(): void
    {
        unlink(self::$db_file);
    }
}
