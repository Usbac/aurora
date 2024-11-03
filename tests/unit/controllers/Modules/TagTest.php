<?php

final class TagTest extends \Aurora\Tests\Base
{
    protected ?\Aurora\App\Modules\Tag $mod;

    public function __construct()
    {
        parent::__construct();
        $this->mod = new \Aurora\App\Modules\Tag($this->db, $this->language);
    }

    public function testAdd(): void
    {
        $this->assertEquals(1, $this->mod->add([ 'name' => 'Tech', 'slug' => 'tech', 'description' => 'Technology', 'meta_title' => 'Tech', 'meta_description' => 'All about tech' ]));
        $this->assertEquals(2, $this->mod->add([ 'name' => 'Health', 'slug' => 'health', 'description' => 'Health and Wellness', 'meta_title' => 'Health', 'meta_description' => 'All about health' ]));
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'Tech',
                'slug' => 'tech',
                'description' => 'Technology',
                'meta_title' => 'Tech',
                'meta_description' => 'All about tech',
            ],
            [
                'id' => 2,
                'name' => 'Health',
                'slug' => 'health',
                'description' => 'Health and Wellness',
                'meta_title' => 'Health',
                'meta_description' => 'All about health',
            ],
        ], $this->db->query('SELECT * FROM tags')->fetchAll());
    }

    /**
     * @depends on testAdd
     */
    public function testSave(): void
    {
        $this->assertEquals(2, $this->mod->save(2, [ 'name' => 'Health & Wellness', 'slug' => 'health-wellness', 'description' => 'Health and Wellness', 'meta_title' => 'Health & Wellness', 'meta_description' => 'All about health and wellness' ]));
        $this->assertEquals([
            [
                'id' => 1,
                'name' => 'Tech',
                'slug' => 'tech',
                'description' => 'Technology',
                'meta_title' => 'Tech',
                'meta_description' => 'All about tech',
            ],
            [
                'id' => 2,
                'name' => 'Health & Wellness',
                'slug' => 'health-wellness',
                'description' => 'Health and Wellness',
                'meta_title' => 'Health & Wellness',
                'meta_description' => 'All about health and wellness',
            ],
        ], $this->db->query('SELECT * FROM tags')->fetchAll());
    }

    /**
     * @depends on testSave
     */
    public function testCheckFields(): void
    {
        \Aurora\App\Permission::set([ 'edit_tags' => 1 ], 1);
        $this->assertEquals([
            'name' => 'Invalid value',
            'slug' => 'Invalid value. Slug may only contain alpha-numeric characters, underscores, and dashes',
        ], $this->mod->checkFields([ 'name' => '', 'slug' => '' ], 0));

        $this->assertEquals([], $this->mod->checkFields([ 'name' => 'Tech', 'slug' => 'tech' ], 1));

        $this->assertEquals([
            'slug' => 'Slug already in use, try a different one',
        ], $this->mod->checkFields([ 'name' => 'Health & Wellness', 'slug' => 'health-wellness' ], 10000));

        \Aurora\App\Permission::set([ 'edit_tags' => 2 ], 1);
        $this->assertEquals([
            'You do not have permissions to perform this action',
        ], $this->mod->checkFields([ 'name' => 'Travel', 'slug' => 'travel' ], 0));
    }

    public function testGetCondition(): void
    {
        $this->assertEquals('', $this->mod->getCondition([]));
        $this->assertEquals("(tags.name LIKE '%Tech%' OR tags.slug LIKE '%Tech%')", $this->mod->getCondition([ 'search' => 'Tech' ]));
    }
}
