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

$ai = new AiGenerateImageByReference($apiKey, $logger);

$generatedImageBase64 = $ai
    ->setStyleImage('/path/to/reference-style.png') // reference image for style
    ->addContentImage('/path/to/content-image.png', 'Add glasses to the person') // content with description
    ->generate('A sunny outdoor scene with the person reading a book.');

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
$ai = new AiGenerateImageByReference($apiKey, $logger, $customClient);
```

---

## 🧰 Options (`$options`)

Pass an options array to `generate()`:

| Key          | Description                                  | Default Value |
| ------------ | -------------------------------------------- | ------------- |
| `MODEL`      | OpenAI model                                 | `gpt-image-1` |
| `SIZE`       | Image size (`1024x1024`, etc.)               | `1024x1024`   |
| `N`          | Number of images                             | `1`           |
| `BACKGROUND` | Background (`transparent`, `opaque`, `auto`) | `transparent` |

Unknown keys will be ignored with a warning if logger is set.

---

## 🧪 Running Tests

```bash
composer install
vendor/bin/phpunit
```

> All tests live in `tests/` and are PSR-4 autoloaded.\
> Uses Guzzle's `MockHandler` for full offline test coverage.

---

## 🧼 Code Style

This project uses **PHP-CS-Fixer** with strict PSR-12 rules.

### ▶️ Run it

```bash
vendor/bin/php-cs-fixer fix
```

> 💡 If you're on PHP 8.4 (or fighting entropy), run it with:
>
> ```bash
> PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix
> ```

---

## 🧠 How It Works

- First image set via `setStyleImage()` defines the visual style.
- Additional images via `addContentImage()` define the content (e.g. characters).
- Prompt describes final composition.
- Uses `multipart/form-data` to call OpenAI API.
- Returns base64-encoded image.

---

## 📦 Requirements

- PHP 8.1+
- OpenAI API key with image generation access
- `guzzlehttp/guzzle`
- (optional) `psr/log` for logging

---

## 📬 Want to contribute?

Sure, fork it. Or open a PR.\
Or just write a poem about AI and post it on your fridge. We approve.

---

## ☕ License

MIT. Hack it, fork it, print it on a mug.

---

## 🧪 Real-World Example

```php
<?php

declare(strict_types=1);

namespace App\OpenAi\Tool;

require_once __DIR__ . '/vendor/autoload.php';

use AiGenerateImageByReference\AiGenerateImageByReference;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$apiKey = 'sk-your-real-key';

$logger = new Logger('ai');
$logger->pushHandler(new StreamHandler('php://stdout'));

$ai = new AiGenerateImageByReference($apiKey, $logger);

$generatedImageBase64 = $ai
    ->setStyleImage('style.png')
    ->addContentImage('tyna.png', 'girl named Tina')
    ->addContentImage('fredy.png', 'boy named Fredy')
    ->generate('Fredy and Tina in a park on a sunny day with blue sky – Fredy is riding a bicycle');

if ($generatedImageBase64) {
    file_put_contents('output.png', base64_decode($generatedImageBase64));
}
```

