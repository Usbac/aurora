<?php

namespace Aurora\Tests\Modules;

final class PageTest extends \Aurora\Tests\Modules\Base
{
    protected ?\Aurora\App\Modules\Page $mod;

    public function __construct()
    {
        parent::__construct();
        $this->mod = new \Aurora\App\Modules\Page($this->db, $this->language);
    }

    public function testAdd(): void
    {
        $time = time();
        $this->assertEquals(1, $this->mod->add([
            'title' => 'Home',
            'slug' => 'home',
            'html' => '<h2>Welcome to our website</h2>',
            'status' => 1,
            'static' => 0,
            'static_file' => '',
            'meta_title' => 'Home',
            'meta_description' => 'Home page',
            'canonical_url' => '/home',
            'edited_at' => $time,
        ]));
        $this->assertEquals(2, $this->mod->add([
            'title' => 'About',
            'slug' => 'about',
            'html' => '',
            'status' => 1,
            'static' => 1,
            'static_file' => '/about.html',
            'meta_title' => 'About',
            'meta_description' => 'About us',
            'canonical_url' => '/about-us',
            'edited_at' => $time,
        ]));
        $this->assertEquals([
            [
                'id' => 1,
                'slug' => 'home',
                'meta_title' => 'Home',
                'meta_description' => 'Home page',
                'title' => 'Home',
                'html' => '<h2>Welcome to our website</h2>',
                'status' => 1,
                'static' => 0,
                'static_file' => '',
                'canonical_url' => '/home',
                'edited_at' => $time,
            ],
            [
                'id' => 2,
                'slug' => 'about',
                'meta_title' => 'About',
                'meta_description' => 'About us',
                'title' => 'About',
                'html' => '',
                'status' => 1,
                'static' => 1,
                'static_file' => '/about.html',
                'canonical_url' => '/about-us',
                'edited_at' => $time,
            ],
        ], $this->db->query('SELECT * FROM pages')->fetchAll());
    }

    /**
     * @depends on testAdd
     */
    public function testSave(): void
    {
        $time = time();
        $this->assertEquals(2, $this->mod->save(2, [
            'id' => 2,
            'slug' => 'know-the-team',
            'meta_title' => 'Know the team',
            'meta_description' => 'Know the team',
            'title' => 'Know the team',
            'html' => '',
            'status' => 1,
            'static' => 1,
            'static_file' => '/know-team.html',
            'canonical_url' => '/know-the-team',
        ]));
        $this->assertEquals([
            [
                'id' => 1,
                'slug' => 'home',
                'meta_title' => 'Home',
                'meta_description' => 'Home page',
                'title' => 'Home',
                'html' => '<h2>Welcome to our website</h2>',
                'status' => 1,
                'static' => 0,
                'static_file' => '',
                'canonical_url' => '/home',
                'edited_at' => $time,
            ],
            [
                'id' => 2,
                'slug' => 'know-the-team',
                'meta_title' => 'Know the team',
                'meta_description' => 'Know the team',
                'title' => 'Know the team',
                'html' => '',
                'status' => 1,
                'static' => 1,
                'static_file' => '/know-team.html',
                'canonical_url' => '/know-the-team',
                'edited_at' => $time,
            ],
        ], $this->db->query('SELECT * FROM pages')->fetchAll());
    }

    /**
     * @depends on testSave
     */
    public function testCheckFields(): void
    {
        \Aurora\App\Permission::set([ 'edit_pages' => 1 ], 1);
        $this->assertEquals([
            'title' => 'Invalid value',
        ], $this->mod->checkFields([ 'title' => '', 'slug' => '' ], 0));

        $this->assertEquals([], $this->mod->checkFields([ 'title' => 'Tech', 'slug' => '' ], 1));

        $this->assertEquals([], $this->mod->checkFields([ 'title' => 'Tech', 'slug' => 'tech' ], 1));

        $this->assertEquals([
            'slug' => 'Slug already in use, try a different one',
        ], $this->mod->checkFields([ 'title' => 'Health & Wellness', 'slug' => 'home' ], 2));

        \Aurora\App\Permission::set([ 'edit_pages' => 2 ], 1);
        $this->assertEquals([
            'You do not have permissions to perform this action',
        ], $this->mod->checkFields([ 'title' => 'Travel', 'slug' => 'travel' ], 0));
    }

    public function testGetCondition(): void
    {
        $this->assertEquals('', $this->mod->getCondition([]));
        $this->assertEquals("pages.title LIKE '%Tech%'", $this->mod->getCondition([ 'search' => 'Tech' ]));
        $this->assertEquals("pages.status = 1 AND pages.title LIKE '%Tech%'", $this->mod->getCondition([ 'search' => 'Tech', 'status' => 1 ]));
    }
}
