<?php

namespace Interop\Http\Factory;

use Exception;
use InvalidArgumentException;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

abstract class StreamFactoryTestCase extends TestCase
{
    use StreamHelper;

    /**
     * @var StreamFactoryInterface
     */
    protected $factory;

    /**
     * @return StreamFactoryInterface
     */
    abstract protected function createStreamFactory();

    public function setUp(): void
    {
        $this->factory = $this->createStreamFactory();
    }

    protected function assertStream($stream, $content)
    {
        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame($content, (string) $stream);
    }

    public function testCreateStreamWithoutArgument()
    {
        $stream = $this->factory->createStream();

        $this->assertStream($stream, '');
    }

    public function testCreateStreamWithEmptyString()
    {
        $string = '';

        $stream = $this->factory->createStream($string);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamWithASCIIString()
    {
        $string = 'would you like some crumpets?';

        $stream = $this->factory->createStream($string);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamWithMultiByteMultiLineString()
    {
        $string = "would you\r\nlike some\n\u{1F950}?";

        $stream = $this->factory->createStream($string);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromFile()
    {
        $string = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();

        file_put_contents($filename, $string);

        $stream = $this->factory->createStreamFromFile($filename);

        $this->assertStream($stream, $string);
    }

    public function testCreateStreamFromNonExistingFile()
    {
        $filename = $this->createTemporaryFile();
        unlink($filename);

        $this->expectException(RuntimeException::class);
        $stream = $this->factory->createStreamFromFile($filename);
    }

    public function testCreateStreamFromInvalidFileName()
    {
        $this->expectException(RuntimeException::class);
        $stream = $this->factory->createStreamFromFile('');
    }

    public function testCreateStreamFromFileIsReadOnlyByDefault()
    {
        $string = 'would you like some crumpets?';
        $filename = $this->createTemporaryFile();

        $stream = $this->factory->createStreamFromFile($filename);

        $this->expectException(RuntimeException::class);
        $stream->write($string);
    }

    public function testCreateStreamFromFileWithWriteOnlyMode()
    {
        $filename = $this->createTemporaryFile();

        $stream = $this->factory->createStreamFromFile($filename, 'w');

        $this->expectException(RuntimeException::class);
        $stream->read(1);
    }

    public function testCreateStreamFromFileWithNoMode()
    {
        $filename = $this->createTemporaryFile();

        $this->expectException(Exception::class);
        $stream = $this->factory->createStreamFromFile($filename, '');
    }

    public function testCreateStreamFromFileWithInvalidMode()
    {
        $filename = $this->createTemporaryFile();

        $this->expectException(Exception::class);
        $stream = $this->factory->createStreamFromFile($filename, "\u{2620}");
    }

    public function testCreateStreamFromResource()
    {
        $string = 'would you like some crumpets?';
        $resource = $this->createTemporaryResource($string);

        $stream = $this->factory->createStreamFromResource($resource);

        $this->assertStream($stream, $string);
    }
}
