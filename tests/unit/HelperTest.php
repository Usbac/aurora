<?php

final class HelperTest extends \PHPUnit\Framework\TestCase
{
    public function testGetPath(): void
    {
        $this->assertEquals(dirname(__DIR__, 2) . '/app', \Aurora\System\Helper::getPath('app'));
        $this->assertEquals(dirname(__DIR__, 2) . '/app/views', \Aurora\System\Helper::getPath('app/views'));
        $this->assertEquals(dirname(__DIR__, 2) . '/file.txt', \Aurora\System\Helper::getPath('/file.txt'));
    }

    public function testCurrentPath(): void
    {
        $this->assertEquals('', \Aurora\System\Helper::getCurrentPath());
        $_GET['url'] = '/admin/posts';
        $this->assertEquals('admin/posts', \Aurora\System\Helper::getCurrentPath());
        $_GET['url'] = '/admin/posts?user=3&order=title';
        $this->assertEquals('admin/posts', \Aurora\System\Helper::getCurrentPath());
        $_GET['url'] = 'blog?page=2';
        $this->assertEquals('blog', \Aurora\System\Helper::getCurrentPath());
    }

    public function testGetUrl(): void
    {
        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['HTTPS'] = false;
        $this->assertEquals('http://localhost', \Aurora\System\Helper::getUrl());
        $this->assertEquals('http://localhost/admin/posts', \Aurora\System\Helper::getUrl('admin/posts'));
        $this->assertEquals('http://localhost/blog', \Aurora\System\Helper::getUrl('/blog'));

        $_SERVER['SERVER_NAME'] = 'aurora.com';
        $_SERVER['HTTPS'] = true;
        $this->assertEquals('https://aurora.com', \Aurora\System\Helper::getUrl());
        $this->assertEquals('https://aurora.com/admin/posts', \Aurora\System\Helper::getUrl('admin/posts'));
        $this->assertEquals('https://aurora.com/blog', \Aurora\System\Helper::getUrl('/blog'));
    }

    public function testIsValidId(): void
    {
        $this->assertTrue(\Aurora\System\Helper::isValidId(0));
        $this->assertTrue(\Aurora\System\Helper::isValidId(2));
        $this->assertTrue(\Aurora\System\Helper::isValidId('0'));
        $this->assertTrue(\Aurora\System\Helper::isValidId('2'));
        $this->assertFalse(\Aurora\System\Helper::isValidId(false));
        $this->assertFalse(\Aurora\System\Helper::isValidId(''));
        $this->assertFalse(\Aurora\System\Helper::isValidId(null));
    }

    public function testGetUserIp(): void
    {
        $_SERVER['REMOTE_ADDR'] = '192.168.0.1';
        $this->assertEquals('192.168.0.1', \Aurora\System\Helper::getUserIP());
        $_SERVER['HTTP_CLIENT_IP'] = '192.168.0.2';
        $this->assertEquals('192.168.0.2', \Aurora\System\Helper::getUserIP());
    }

    public function testCopy(): void
    {
        $test_dir = dirname(__DIR__);
        $this->assertTrue(\Aurora\System\Helper::copy("$test_dir/fixtures/files", "$test_dir/fixtures/files2"));
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
