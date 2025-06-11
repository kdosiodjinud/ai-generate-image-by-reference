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

    public function testCanBeInstantiated(): void
    {
        $instance = new AiGenerateImageByReference($this->fakeApiKey);
        $this->assertInstanceOf(AiGenerateImageByReference::class, $instance);
    }

    public function testConstructorAcceptsLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $instance = new AiGenerateImageByReference($this->fakeApiKey, [], $logger);
        $this->assertInstanceOf(AiGenerateImageByReference::class, $instance);
    }

    public function testGenerateReturnsNullOnInvalidJson(): void
    {
        $client = $this->createMockedClient([
            new Response(200, [], 'not-json'),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $ai = new AiGenerateImageByReference($this->fakeApiKey, [], $logger, $client);

        $tempFile1 = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempFile1, 'image-data');

        $result = $ai->generate([$tempFile1], 'test prompt');

        unlink($tempFile1);

        $this->assertNull($result);
    }

    public function testGenerateReturnsBase64Image(): void
    {
        $base64 = base64_encode('fake-image');
        $client = $this->createMockedClient([
            new Response(200, [], json_encode(['data' => [['b64_json' => $base64]]])),
        ]);

        $logger = $this->createMock(LoggerInterface::class);
        $ai = new AiGenerateImageByReference($this->fakeApiKey, [], $logger, $client);

        $tempFile1 = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempFile1, 'image-1');

        $tempFile2 = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($tempFile2, 'image-2');

        $result = $ai->generate([$tempFile1, $tempFile2], 'draw me like one of your French cats');

        unlink($tempFile1);
        unlink($tempFile2);

        $this->assertIsString($result);
        $this->assertEquals($base64, $result);
    }
}
