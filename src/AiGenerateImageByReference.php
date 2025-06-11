<?php

declare(strict_types=1);

namespace AiGenerateImageByReference;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class AiGenerateImageByReference
{
    private const string API_ENDPOINT_BASE_URL = 'https://api.openai.com';
    private const string API_ENDPOINT_EDIT = '/v1/images/edits';
    private const int API_ENDPOINT_TIMEOUT = 300;

    private const string MODEL = 'gpt-image-1'; // options are for this model
    private const string SIZE_1024_1024 = '1024x1024';
    private const string SIZE_1536_1024 = '1536x1024';

    private const string SIZE_1024x1536 = '1024x1536';

    private const string SIZE_AUTO = 'auto';
    private const string NUMBER_OF_IMAGES = '1'; // 1-10
    private const string BACKGROUND_TRANSPARENT = 'transparent';
    private const string BACKGROUND_OPAQUE = 'opaque';
    private const string BACKGROUND_AUTO = 'auto';

    private array $optionsDefaults = [
        'MODEL' => self::MODEL,
        'SIZE' => self::SIZE_1024_1024,
        'N' => self::NUMBER_OF_IMAGES,
        'BACKGROUND' => self::BACKGROUND_TRANSPARENT,
    ];

    private Client $client;

    public function __construct(
        private readonly string $openAiApiKey,
        array $options = [],
        private readonly ?LoggerInterface $logger = null,
        ?Client $client = null
    ) {
        $this->loadOptions($options);

        $this->client = $client ?? new Client([
            'base_uri' => self::API_ENDPOINT_BASE_URL,
            'timeout' => self::API_ENDPOINT_TIMEOUT,
        ]);
    }

    /**
     * Generate an image based on first image style and next images as content.
     */
    public function generate(array $imageUrls, string $userPrompt = '', array $options = []): ?string
    {
        $this->loadOptions($options);

        $prompt = 'Generate an image from the second (and next) images, in the style of the first image. ';
        $prompt .= 'On final image must be content from second image: ';
        $prompt .= $userPrompt;

        // Request parameters
        $multipart = [
            ['name' => 'model', 'contents' => $this->optionsDefaults['MODEL']],
            ['name' => 'n', 'contents' => $this->optionsDefaults['N']],
            ['name' => 'size', 'contents' => $this->optionsDefaults['SIZE']],
            ['name' => 'background', 'contents' => $this->optionsDefaults['BACKGROUND']],
            ['name' => 'prompt', 'contents' => $prompt],
        ];

        $this->logger?->debug('Using model: ' . $this->optionsDefaults['MODEL']);
        $this->logger?->debug('Number of images to generate: ' . $this->optionsDefaults['N']);
        $this->logger?->debug('Image size: ' . $this->optionsDefaults['SIZE']);
        $this->logger?->debug('Background: ' . $this->optionsDefaults['BACKGROUND']);
        $this->logger?->info("Generating image with prompt: {$prompt}");

        // Add reference images
        foreach ($imageUrls as $imageUrl) {
            $basename = basename($imageUrl);
            $contentType = mime_content_type($imageUrl) ?: 'image/png';

            $this->logger?->info('Image URL: ' . $imageUrl);
            $this->logger?->debug('- basename: ' . $basename);
            $this->logger?->debug('- content type: ' . $contentType);

            $multipart[] = [
                'name' => 'image[]',
                'contents' => file_get_contents($imageUrl),
                'filename' => $basename,
                'headers' => ['Content-Type' => $contentType],
            ];
        }

        // OpenAI API request
        $this->logger?->info('Sending request to OpenAI API... (' . self::API_ENDPOINT_EDIT . ')');
        $response = $this->client->request('POST', self::API_ENDPOINT_EDIT, [
            'headers' => [
                'Authorization' => "Bearer {$this->openAiApiKey}",
            ],
            'multipart' => $multipart,
        ]);

        // Reading the response
        try {
            $responseData = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger?->info('Response status code: ' . $response->getStatusCode());
            $this->logger?->debug('Response data: ' . print_r($responseData, true));
        } catch (\JsonException $e) {
            $this->logger?->error('Failed to decode JSON response: ' . $e->getMessage());

            return null;
        }

        $this->logger?->info('Image generation completed successfully.');
        $this->logger?->debug('Generated image data: ' . print_r($responseData, true));

        return $responseData['data'][0]['b64_json'] ?? null;
    }

    private function loadOptions(mixed $options): void
    {
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->optionsDefaults)) {
                $this->optionsDefaults[$key] = $value;
            } else {
                $this->logger?->warning(
                    "Unknown option key: {$key}. Available options: " . implode(
                        ', ',
                        array_keys($this->optionsDefaults)
                    )
                );
            }
        }
    }
}
