<?php

namespace Aurora\Tests\Modules;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

final class UserTest extends \Aurora\Tests\Modules\Base
{
    use ArraySubsetAsserts;

    protected ?\Aurora\App\Modules\User $mod;

    public function __construct()
    {
        parent::__construct();
        $this->mod = new \Aurora\App\Modules\User($this->db, $this->language);
    }

    public function testAdd(): void
    {
        $time = time();
        $this->assertEquals(1, $this->mod->add([
            'name' => 'Sebastian Castellanos',
            'slug' => 'sebastian-castellanos',
            'email' => 'sebas.cas@mail.com',
            'password' => 'sebas123',
            'status' => 1,
            'image' => null,
            'bio' => 'Detective',
            'role' => 2,
        ]));
        $this->assertEquals(2, $this->mod->add([
            'name' => 'Leon Kennedy',
            'slug' => 'leon-kennedy',
            'email' => 'leon98@mail.com',
            'password' => 'leon98*',
            'status' => 1,
            'image' => null,
            'bio' => 'Police Officer',
            'role' => 1,
        ]));

        $users = $this->db->query('SELECT * FROM users')->fetchAll();

        self::assertArraySubset([
            'id' => 1,
            'name' => 'Sebastian Castellanos',
            'slug' => 'sebastian-castellanos',
            'email' => 'sebas.cas@mail.com',
            'image' => '',
            'status' => 1,
            'bio' => 'Detective',
            'role' => 2,
        ], $users[0]);
        $this->assertEqualsWithDelta($time, $users[0]['created_at'], 10);

        self::assertArraySubset([
            'id' => 2,
            'name' => 'Leon Kennedy',
            'slug' => 'leon-kennedy',
            'email' => 'leon98@mail.com',
            'image' => '',
            'status' => 1,
            'bio' => 'Police Officer',
            'role' => 1,
        ], $users[1]);
        $this->assertEqualsWithDelta($time, $users[1]['created_at'], 10);
    }

    /**
     * @depends on testAdd
     */
    public function testGetPassword(): void
    {
        $user = $this->db->query('SELECT * FROM users WHERE id = 1')->fetch();
        $this->assertTrue(password_verify('sebas123', $user['password']));

        $user = $this->db->query('SELECT * FROM users WHERE id = 2')->fetch();
        $this->assertTrue(password_verify('leon98*', $user['password']));
    }

    /**
     * @depends on testAdd
     */
    public function testSave(): void
    {
        $this->assertEquals(2, $this->mod->save(2, [
            'name' => 'Leon Kennedy',
            'slug' => 'leon-kennedy',
            'email' => 'leon98@new-email.com',
            'password' => 'leon98*',
            'status' => 1,
            'image' => null,
            'bio' => 'Rookie',
            'role' => 3,
        ]));

        self::assertArraySubset([
            'id' => 2,
            'name' => 'Leon Kennedy',
            'slug' => 'leon-kennedy',
            'email' => 'leon98@new-email.com',
            'status' => 1,
            'image' => null,
            'bio' => 'Rookie',
            'role' => 3,
        ], $this->db->query('SELECT * FROM users WHERE id = 2')->fetch());
    }

    /**
     * @depends on testSave
     */
    public function testCheckFields(): void
    {
        $user = &$GLOBALS['user'];
        $user = [ 'role' => 1 ];
        \Aurora\App\Permission::set([ 'edit_users' => 1 ], 1);

        $this->assertEquals([
            'invalid_slug',
            'invalid_value',
            'bad_password',
        ], $this->mod->checkFields([ 'name' => 'John', 'slug' => '' ], '', $user));

        $this->assertEquals([
            'no_permission',
        ], $this->mod->checkFields([ 'name' => 'John', 'slug' => 'john', 'email' => 'john@mail.com' ], 1, $user));

        $user = [ 'id' => 3, 'role' => 3, 'role_slug' => 'admin' ];

        $this->assertEquals([], $this->mod->checkFields([ 'name' => 'John', 'slug' => 'john', 'email' => 'john@mail.com' ], 1, $user));

        $user = [ 'id' => 3, 'role' => 3, 'role_slug' => 'admin' ];

        $this->assertContains('no_permission', $this->mod->checkFields([
            'name' => 'John',
            'slug' => 'john',
            'email' => 'john@mail.com',
            'role' => 3,
        ], 2, $user));

        $this->assertEquals([
            'repeated_slug',
        ], $this->mod->checkFields([ 'name' => 'John', 'slug' => 'leon-kennedy', 'email' => 'john@mail.com' ], 1, $user));

        $this->assertEquals([
            'bad_password',
        ], $this->mod->checkFields([ 'name' => 'John', 'slug' => 'john', 'email' => 'john@mail.com', 'password' => '123', 'password_confirm' => '123' ], 1, $user));

        $this->assertEquals([
            'bad_password_confirm',
        ], $this->mod->checkFields([ 'name' => 'John', 'slug' => 'john', 'email' => 'john@mail.com', 'password' => '123456789', 'password_confirm' => '123' ], 1, $user));

        $this->assertEquals([], $this->mod->checkFields([ 'name' => 'John', 'slug' => 'john', 'email' => 'john@mail.com', 'password' => '123456789', 'password_confirm' => '123456789' ], 1, $user));
    }

    public function testGetCondition(): void
    {
        $this->assertEquals('', $this->mod->getCondition([]));
        $this->assertEquals("(users.name LIKE '%John%' OR users.email LIKE '%John%')", $this->mod->getCondition([ 'search' => 'John' ]));
    }
}
