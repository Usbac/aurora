<?php

namespace Aurora\Tests\Modules;

final class PostTest extends \Aurora\Tests\Modules\Base
{
    protected ?\Aurora\App\Modules\Post $mod;

    public function __construct()
    {
        parent::__construct();
        $this->mod = new \Aurora\App\Modules\Post($this->db, $this->language);
    }

    public function testAdd(): void
    {
        $this->assertEquals(1, $this->mod->add([
            'title' => 'Traveling while working remotely',
            'slug' => 'traveling-while-working-remotely',
            'description' => 'How to travel while working remotely',
            'html' => '<p>Lorem ipsum dolor sit amet consectetur</p>',
            'user_id' => 3,
            'image' => null,
            'image_alt' => '',
            'status' => 1,
            'meta_title' => 'Traveling while working remotely',
            'meta_description' => 'A post about how to travel while working remotely',
            'canonical_url' => '/traveling-while-working-remotely',
            'published_at' => '10/11/2024, 20:05',
        ]));
        $this->assertEquals(2, $this->mod->add([
            'title' => 'Top 5 beaches',
            'slug' => 'top-beaches',
            'description' => 'The best beaches in the whole world',
            'html' => '',
            'user_id' => null,
            'image' => null,
            'image_alt' => '',
            'status' => 0,
            'meta_title' => 'Top 5 beaches',
            'meta_description' => 'The best beaches',
            'canonical_url' => '/top-beaches',
            'published_at' => '10/11/2024, 20:05',
        ]));
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Traveling while working remotely',
                'slug' => 'traveling-while-working-remotely',
                'description' => 'How to travel while working remotely',
                'html' => '<p>Lorem ipsum dolor sit amet consectetur</p>',
                'user_id' => 3,
                'image' => null,
                'image_alt' => '',
                'status' => 1,
                'meta_title' => 'Traveling while working remotely',
                'meta_description' => 'A post about how to travel while working remotely',
                'canonical_url' => '/traveling-while-working-remotely',
                'published_at' => 1728677100,
            ],
            [
                'id' => 2,
                'title' => 'Top 5 beaches',
                'slug' => 'top-beaches',
                'description' => 'The best beaches in the whole world',
                'html' => '',
                'user_id' => null,
                'image' => null,
                'image_alt' => '',
                'status' => 0,
                'meta_title' => 'Top 5 beaches',
                'meta_description' => 'The best beaches',
                'canonical_url' => '/top-beaches',
                'published_at' => 1728677100,
            ],
        ], $this->db->query('SELECT * FROM posts')->fetchAll());
    }

    /**
     * @depends on testAdd
     */
    public function testSave(): void
    {
        $this->assertEquals(2, $this->mod->save(2, [
            'title' => 'Top 5 beaches',
            'slug' => 'top-beaches',
            'description' => 'The best beaches in the whole world',
            'html' => '',
            'user_id' => null,
            'image' => null,
            'image_alt' => '',
            'status' => 0,
            'meta_title' => 'Top 5 beaches',
            'meta_description' => 'The best beaches',
            'canonical_url' => '/top-beaches',
            'published_at' => '10/11/2024, 20:05',
        ]));
        $this->assertEquals([
            [
                'id' => 1,
                'title' => 'Traveling while working remotely',
                'slug' => 'traveling-while-working-remotely',
                'description' => 'How to travel while working remotely',
                'html' => '<p>Lorem ipsum dolor sit amet consectetur</p>',
                'user_id' => 3,
                'image' => null,
                'image_alt' => '',
                'status' => 1,
                'meta_title' => 'Traveling while working remotely',
                'meta_description' => 'A post about how to travel while working remotely',
                'canonical_url' => '/traveling-while-working-remotely',
                'published_at' => 1728677100,
            ],
            [
                'id' => 2,
                'title' => 'Top 5 beaches',
                'slug' => 'top-beaches',
                'description' => 'The best beaches in the whole world',
                'html' => '',
                'user_id' => null,
                'image' => null,
                'image_alt' => '',
                'status' => 0,
                'meta_title' => 'Top 5 beaches',
                'meta_description' => 'The best beaches',
                'canonical_url' => '/top-beaches',
                'published_at' => 1728677100,
            ],
        ], $this->db->query('SELECT * FROM posts')->fetchAll());
    }

    /**
     * @depends on testSave
     */
    public function testCheckFields(): void
    {
        \Aurora\App\Permission::set([ 'edit_posts' => 1, 'publish_posts' => 1 ], 1);
        $this->assertEquals([
            'title' => 'Invalid value',
            'slug' => 'Invalid value. Slug may only contain alpha-numeric characters, underscores, and dashes',
        ], $this->mod->checkFields([ 'title' => '', 'slug' => '' ], 0));

        $this->assertEquals([], $this->mod->checkFields([ 'title' => 'Top countries', 'slug' => 'top-countries' ], 1));

        $this->assertEquals([], $this->mod->checkFields([ 'title' => 'Tech', 'slug' => 'tech' ], 1));

        $this->assertEquals([
            'slug' => 'Slug already in use, try a different one',
        ], $this->mod->checkFields([ 'title' => 'Top beaches', 'slug' => 'top-beaches' ], 1));

        \Aurora\App\Permission::set([ 'edit_posts' => 2 ], 1);
        $this->assertEquals([
            'You do not have permissions to perform this action',
        ], $this->mod->checkFields([ 'title' => 'Travel', 'slug' => 'travel' ], 0));

        \Aurora\App\Permission::set([ 'edit_posts' => 1, 'publish_posts' => 2 ], 1);

        $this->assertEquals([], $this->mod->checkFields([ 'title' => 'Tech', 'slug' => 'tech', 'status' => 0 ], 1));

        $this->assertEquals([
            'You are not allowed to handle published posts',
        ], $this->mod->checkFields([ 'title' => 'Tech', 'slug' => 'tech', 'status' => 1 ], 1));
    }

    public function testGetCondition(): void
    {
        $this->assertEquals('', $this->mod->getCondition([]));
        $this->assertEquals("(posts.title LIKE '%Tech%' OR posts.description LIKE '%Tech%')", $this->mod->getCondition([ 'search' => 'Tech' ]));
        $this->assertEquals('posts.status AND posts.published_at > ' . time(), $this->mod->getCondition([ 'status' => 'scheduled' ]));
        $this->assertEquals('posts.status AND posts.published_at <= ' . time(), $this->mod->getCondition([ 'status' => 1 ]));
    }
}
