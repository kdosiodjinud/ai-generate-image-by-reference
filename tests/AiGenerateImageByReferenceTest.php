<?php

declare(strict_types=1);

namespace AiGenerateImageByReference\Tests;

use AiGenerateImageByReference\AiGenerateImageByReference;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AiGenerateImageByReferenceTest extends TestCase
{
    private string $fakeApiKey = 'sk-test';
    private array $history = [];

    private function createMockedClient(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($this->history));

        return new Client(['handler' => $handlerStack]);
    }

    private function createTempImage(string $content = 'image-content'): string
    {
        $path = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($path, $content);

        return $path;
    }

    public function testCanBeInstantiated(): void
    {
        $instance = new AiGenerateImageByReference($this->fakeApiKey);
        $this->assertInstanceOf(AiGenerateImageByReference::class, $instance);
    }

    public function testConstructorAcceptsLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $client = $this->createMockedClient([]);
        $instance = new AiGenerateImageByReference($this->fakeApiKey, $logger, $client);
        $this->assertInstanceOf(AiGenerateImageByReference::class, $instance);
    }

    public function testSetStyleImage(): void
    {
        $ai = new AiGenerateImageByReference($this->fakeApiKey);
        $path = $this->createTempImage();
        $result = $ai->setStyleImage($path);
        $this->assertInstanceOf(AiGenerateImageByReference::class, $result);
        unlink($path);
    }

    public function testAddContentImageAndClear(): void
    {
        $ai = new AiGenerateImageByReference($this->fakeApiKey);
        $path = $this->createTempImage();
        $result = $ai->addContentImage($path, 'A beautiful flower');
        $this->assertInstanceOf(AiGenerateImageByReference::class, $result);

        $ai->clearContentImages();
        unlink($path);
    }

    public function testGenerateReturnsNullOnInvalidJson(): void
    {
        $client = $this->createMockedClient([
            new Response(200, [], 'not-json'),
        ]);
        $logger = $this->createMock(LoggerInterface::class);
        $ai = new AiGenerateImageByReference($this->fakeApiKey, $logger, $client);

        $styleImage = $this->createTempImage();
        $ai->setStyleImage($styleImage);
        $ai->addContentImage($styleImage, 'desc');

        $result = $ai->generate('Prompt here');
        $this->assertNull($result);
        unlink($styleImage);
    }

    public function testGenerateReturnsBase64Image(): void
    {
        $base64 = base64_encode('fake-image');
        $client = $this->createMockedClient([
            new Response(200, [], json_encode(['data' => [['b64_json' => $base64]]])),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $ai = new AiGenerateImageByReference($this->fakeApiKey, $logger, $client);

        $styleImage = $this->createTempImage();
        $contentImage = $this->createTempImage();

        $ai->setStyleImage($styleImage);
        $ai->addContentImage($contentImage, 'a sleepy dragon');

        $result = $ai->generate('dragon in a castle');
        $this->assertIsString($result);
        $this->assertEquals($base64, $result);

        unlink($styleImage);
        unlink($contentImage);
    }

    public function testGenerateWithUnknownOptions(): void
    {
        $base64 = base64_encode('another-fake-image');
        $client = $this->createMockedClient([
            new Response(200, [], json_encode(['data' => [['b64_json' => $base64]]])),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $ai = new AiGenerateImageByReference($this->fakeApiKey, $logger, $client);

        $styleImage = $this->createTempImage();
        $contentImage = $this->createTempImage();

        $ai->setStyleImage($styleImage)
            ->addContentImage($contentImage, 'robot dog');

        $result = $ai->generate('cool dog', ['FOO' => 'bar']); // FOO is unknown option
        $this->assertIsString($result);

        unlink($styleImage);
        unlink($contentImage);
    }
}
