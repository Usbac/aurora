<?php

final class LinkTest extends \Aurora\Tests\Base
{
    protected ?\Aurora\App\Modules\Link $mod;

    public function __construct()
    {
        parent::__construct();
        $this->mod = new \Aurora\App\Modules\Link($this->db, $this->language);
    }

    public function testAdd(): void
    {
        $this->assertEquals(1, $this->mod->add([ 'title' => 'Home', 'url' => '/', 'order' => 0, 'status' => 1 ]));
        $this->assertEquals(2, $this->mod->add([ 'title' => 'About', 'url' => '/about', 'order' => 1, 'status' => 1 ]));
        $this->assertEquals(3, $this->mod->add([ 'title' => 'Contact Us', 'url' => '/contact', 'order' => 2, 'status' => 0 ]));
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Home',
                'url' => '/',
                'order' => 0,
                'status' => 1,
            ],
            [
                'id' => 2,
                'title' => 'About',
                'url' => '/about',
                'order' => 1,
                'status' => 1,
            ],
            [
                'id' => 3,
                'title' => 'Contact Us',
                'url' => '/contact',
                'order' => 2,
                'status' => 0,
            ],
        ], $this->db->query('SELECT * FROM links')->fetchAll());
    }

    /**
     * @depends on testAdd
     */
    public function testGetHeaderLinks(): void
    {
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Home',
                'url' => '/',
                'order' => 0,
                'status' => 1,
            ],
            [
                'id' => 2,
                'title' => 'About',
                'url' => '/about',
                'order' => 1,
                'status' => 1,
            ],
        ], $this->mod->getHeaderLinks());
    }

    /**
     * @depends on testAdd
     */
    public function testSave(): void
    {
        $this->assertEquals(2, $this->mod->save(2, [ 'title' => 'About Us', 'url' => '/about-us', 'order' => 10, 'status' => 1 ]));
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Home',
                'url' => '/',
                'order' => 0,
                'status' => 1,
            ],
            [
                'id' => 2,
                'title' => 'About Us',
                'url' => '/about-us',
                'order' => 10,
                'status' => 1,
            ],
            [
                'id' => 3,
                'title' => 'Contact Us',
                'url' => '/contact',
                'order' => 2,
                'status' => 0,
            ],
        ], $this->db->query('SELECT * FROM links')->fetchAll());
    }

    public function testCheckFields(): void
    {
        \Aurora\App\Permission::set([ 'edit_links' => 1 ], 1);
        $this->assertEquals([
            'title' => 'Invalid value',
        ], $this->mod->checkFields([ 'title' => '' ]));

        $this->assertEquals([], $this->mod->checkFields([ 'title' => 'Home' ]));

        \Aurora\App\Permission::set([ 'edit_links' => 2 ], 1);
        $this->assertEquals([
            'You do not have permissions to perform this action',
            'title' => 'Invalid value',
        ], $this->mod->checkFields([ 'title' => '' ]));
    }

    public function testGetCondition(): void
    {
        $this->assertEquals('', $this->mod->getCondition([]));
        $this->assertEquals("links.status = 1", $this->mod->getCondition([ 'status' => 1 ]));
        $this->assertEquals("(links.title LIKE '%Home%' OR links.url LIKE '%Home%')", $this->mod->getCondition([ 'search' => 'Home' ]));
        $this->assertEquals("links.status = 1 AND (links.title LIKE '%Home%' OR links.url LIKE '%Home%')", $this->mod->getCondition([ 'status' => 1, 'search' => 'Home' ]));
    }
}
