# 🧠🎨 AiGenerateImageByReference

A PHP library to generate images in the style of a reference image using the OpenAI Image API.

---

## ⚙️ Installation

```bash
composer require kdosiodjinud/ai-generate-image-by-reference
```

> Requires PHP 8.1+, `guzzlehttp/guzzle`, and `psr/log`.

---

## 🚀 Usage

```php
use AiGenerateImageByReference\AiGenerateImageByReference;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$apiKey = 'sk-...';

$logger = new Logger('ai');
$logger->pushHandler(new StreamHandler('php://stdout'));

$ai = new AiGenerateImageByReference($apiKey, [], $logger);

$imageUrls = [
    '/path/to/reference-style.png',  // first image defines the style
    '/path/to/content-image.png',    // content to be rendered
];

$generatedImageBase64 = $ai->generate($imageUrls, 'Add glasses to the person');

if ($generatedImageBase64) {
    file_put_contents('output.png', base64_decode($generatedImageBase64));
}
```

---

## 🔧 Advanced Usage (Custom HTTP Client)

You can pass a custom Guzzle client (e.g. with a mock handler for testing):

```php
use GuzzleHttp\Client;

$customClient = new Client([...]);
$ai = new AiGenerateImageByReference($apiKey, [], $logger, $customClient);
```

---

## 🧰 Options (`$options`)

You can pass options to the constructor or to `generate()`:

| Key           | Description                | Default Value        |
|---------------|----------------------------|----------------------|
| `MODEL`       | OpenAI model               | `gpt-image-1`        |
| `SIZE`        | Image size                 | `1024x1024`          |
| `N`           | Number of images           | `1`                  |
| `BACKGROUND`  | Background (`transparent`, `opaque`, `auto`) | `transparent` |

---

## 🧪 Running Tests

```bash
composer install
vendor/bin/phpunit
```

> Place your tests in the `tests/` directory, autoloaded via PSR-4.

The class supports dependency injection of the HTTP client for testing:

- Use Guzzle's `MockHandler` for fake responses
- No real API calls needed in unit tests

---

## 🧼 Code Style

This project uses **PHP-CS-Fixer** with PSR-12 and strict rules.  
No need for extra installation — it's included in `composer install`.

### ⚙️ Config

The configuration is defined in `.php-cs-fixer.dist.php`:

```php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
        'ordered_imports' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'phpdoc_align' => ['align' => 'left'],
    ])
    ->setFinder($finder);
```

### ▶️ Run it

```bash
vendor/bin/php-cs-fixer fix
```

> 💡 On PHP 8.4, you may need to prefix the command to suppress a version warning:
>
> ```bash
> PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix
> ```

---

## 🧠 How It Works

- The first image in `$imageUrls` is used as the style reference.
- All other images define the content.
- Uses `multipart/form-data` to communicate with the API.
- Returns a base64-encoded image.

---

## 📦 Requirements

- PHP 8.1+
- OpenAI API key with image generation access
- `guzzlehttp/guzzle`
- (optional) `psr/log` for logging

---

## 📬 Want to contribute?

Feel free to open an issue or PR. Or send a handwritten letter with a pencil – just for the vibe.

---

## ☕ License

MIT. Modify, throw away, or launch it to Mars.
