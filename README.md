# Flaphl Reissue

A modern PHP 8.2+ serialization/deserialization library with metadata-driven normalization, context builders, and PSR compliance.

## Installation

```bash
composer require flaphl/reissue
```

## Basic Usage

```php
use Flaphl\Element\Reissue\Reissue;

$reissue = new Reissue();

// Serialize
$json = $reissue->reissue($object, 'json');

// Deserialize
$object = $reissue->deissue($json, MyClass::class, 'json');
```

## Features

- **Multiple Formats**: JSON, XML, and array support
- **Metadata System**: PHP 8 attributes for serialization control
- **Context Builders**: Fluent API for encoder/decoder configuration
- **Chain Support**: Chain multiple encoders/normalizers
- **Magic Method Protection**: Automatic `__sleep`, `__wakeup`, `__serialize`, `__unserialize` handling
- **Performance Tracking**: Built-in data collector for monitoring
- **PSR Compliant**: PSR-6 cache integration

## Attributes

```php
use Flaphl\Element\Reissue\Attribute\{Groups, Ignore, MaxDepth, SerializedName};

class User
{
    #[Groups(['public'])]
    public string $name;
    
    #[SerializedName('email_address')]
    public string $email;
    
    #[Ignore]
    public string $password;
    
    #[MaxDepth(2)]
    public array $posts;
}
```

## Context Builders

```php
use Flaphl\Element\Reissue\Context\Encoder\JsonEncoderContextBuilder;

$context = (new JsonEncoderContextBuilder())
    ->withPrettyPrint()
    ->withUnescapedUnicode()
    ->withGroups(['public'])
    ->build();

$json = $reissue->reissue($user, 'json', $context);
```

## Requirements

- PHP 8.2 or higher
- symfony/cache (optional, for cached metadata)

## License

MIT License. See [LICENSE](LICENSE) file.