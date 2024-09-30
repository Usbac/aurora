<?php

final class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testGetPath(): void
    {
        $this->assertEquals(dirname(__DIR__, 2) . '/app', \Aurora\Core\Helper::getPath('app'));
        $this->assertEquals(dirname(__DIR__, 2) . '/app/views', \Aurora\Core\Helper::getPath('app/views'));
        $this->assertEquals(dirname(__DIR__, 2) . '/file.txt', \Aurora\Core\Helper::getPath('/file.txt'));
    }

    public function testCurrentPath(): void
    {
        $this->assertEquals('', \Aurora\Core\Helper::getCurrentPath());
        $_GET['url'] = '/admin/posts';
        $this->assertEquals('admin/posts', \Aurora\Core\Helper::getCurrentPath());
        $_GET['url'] = '/admin/posts?user=3&order=title';
        $this->assertEquals('admin/posts', \Aurora\Core\Helper::getCurrentPath());
        $_GET['url'] = 'blog?page=2';
        $this->assertEquals('blog', \Aurora\Core\Helper::getCurrentPath());
    }

    public function testGetUrl(): void
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTPS'] = false;
        $this->assertEquals('http://localhost', \Aurora\Core\Helper::getUrl());
        $this->assertEquals('http://localhost/admin/posts', \Aurora\Core\Helper::getUrl('admin/posts'));
        $this->assertEquals('http://localhost/blog', \Aurora\Core\Helper::getUrl('/blog'));

        $_SERVER['SERVER_NAME'] = 'aurora.com';
        $_SERVER['HTTPS'] = true;
        $this->assertEquals('https://aurora.com', \Aurora\Core\Helper::getUrl());
        $this->assertEquals('https://aurora.com/admin/posts', \Aurora\Core\Helper::getUrl('admin/posts'));
        $this->assertEquals('https://aurora.com/blog', \Aurora\Core\Helper::getUrl('/blog'));
    }

    public function testIsValidId(): void
    {
        $this->assertTrue(\Aurora\Core\Helper::isValidId(0));
        $this->assertTrue(\Aurora\Core\Helper::isValidId(2));
        $this->assertTrue(\Aurora\Core\Helper::isValidId('0'));
        $this->assertTrue(\Aurora\Core\Helper::isValidId('2'));
        $this->assertFalse(\Aurora\Core\Helper::isValidId(false));
        $this->assertFalse(\Aurora\Core\Helper::isValidId(''));
        $this->assertFalse(\Aurora\Core\Helper::isValidId(null));
    }

    public function testGetUserIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $this->assertEquals('192.168.0.1', \Aurora\Core\Helper::getUserIP());
        $_SERVER['HTTP_CLIENT_IP'] = '192.168.0.2';
        $this->assertEquals('192.168.0.2', \Aurora\Core\Helper::getUserIP());
    }

    public function testCopy(): void
    {
        $test_dir = dirname(__DIR__);
        $this->assertTrue(\Aurora\Core\Helper::copy("$test_dir/fixtures/files", "$test_dir/fixtures/files2"));
        $this->assertFileEquals("$test_dir/fixtures/files/a.txt", "$test_dir/fixtures/files2/a.txt");
        $this->assertFileExists("$test_dir/fixtures/files2/b");
        $this->assertFileEquals("$test_dir/fixtures/files/b/c.txt", "$test_dir/fixtures/files2/b/c.txt");
    }

    public static function tearDownAfterClass(): void
    {
        $dir = dirname(__DIR__) . '/fixtures/files2';

        foreach (new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            ) as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }

        rmdir($dir);
    }
}
