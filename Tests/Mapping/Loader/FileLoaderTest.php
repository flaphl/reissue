<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Element\Reissue\Tests\Mapping\Loader;

use Flaphl\Element\Reissue\Exception\InvalidArgumentException;
use Flaphl\Element\Reissue\Mapping\Loader\FileLoader;
use PHPUnit\Framework\TestCase;

class FileLoaderTest extends TestCase
{
    private string $tempFile;

    protected function setUp(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($this->tempFile, 'test content');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }
    }

    public function testConstructorWithValidFile(): void
    {
        $loader = $this->getMockForAbstractClass(FileLoader::class, [$this->tempFile]);
        
        $this->assertEquals($this->tempFile, $loader->getFile());
    }

    public function testConstructorWithNonExistentFileThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mapping file "/non/existent/file.xml" does not exist.');

        $this->getMockForAbstractClass(FileLoader::class, ['/non/existent/file.xml']);
    }

    public function testConstructorWithUnreadableFileThrowsException(): void
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('File permission tests not reliable on Windows');
        }

        $unreadableFile = tempnam(sys_get_temp_dir(), 'unreadable_');
        chmod($unreadableFile, 0000);

        try {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('is not readable');

            $this->getMockForAbstractClass(FileLoader::class, [$unreadableFile]);
        } finally {
            chmod($unreadableFile, 0644);
            unlink($unreadableFile);
        }
    }

    public function testGetFileReturnsCorrectPath(): void
    {
        $loader = $this->getMockForAbstractClass(FileLoader::class, [$this->tempFile]);
        
        $this->assertEquals($this->tempFile, $loader->getFile());
        $this->assertIsString($loader->getFile());
    }
}
