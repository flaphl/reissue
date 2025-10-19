# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-10-19

### Added
- Initial release of Flaphl Reissue serialization system
- Core serialization/deserialization interfaces (`ReissueInterface`, `Reissue`)
- Multiple format support: JSON, XML, Array
- Encoder/Decoder system with `ChainEncoder` and `ChainDecoder`
- Context builder pattern for encoder configuration
  - `JsonEncoderContextBuilder` with JSON-specific options
  - `XmlEncoderContextBuilder` with XML-specific options
- Normalizer system for object transformation
  - `ObjectNormalizer` for basic object serialization
  - `MetadataAwareObjectNormalizer` with attribute support
  - `ArrayNormalizer` for array handling
  - `DateTimeNormalizer` for DateTime objects
- PHP 8 attribute-based metadata system
  - `#[Groups]` - Group-based serialization
  - `#[Ignore]` - Property exclusion
  - `#[MaxDepth]` - Circular reference protection
  - `#[SerializedName]` - Property name transformation
- Metadata loading system
  - `AttributeLoader` for PHP 8 attributes
  - `XmlFileLoader` for XML configuration files
  - `LoaderChain` for multiple loader sources
- Metadata factory pattern (Atelier)
  - `ClassMetadataAtelier` - Basic metadata factory
  - `CacheClassMetaDataAtelier` - PSR-6 cached metadata
  - `CompilerClassMetadataAtelier` - Compiled metadata for production
- Magic method protection (`MagicProtectionHandler`)
  - Automatic `__sleep`, `__wakeup` detection and handling
  - Support for `__serialize`, `__unserialize`
- Name converters
  - `CamelCaseToSnakeCaseNameConverter` for property name transformation
- Performance tracking
  - `ReissueDataCollector` for monitoring serialization operations
- Dependency injection integration
  - `ReissuePass` for Symfony DI container
  - `AttributeMetadataPass` for metadata loader registration
- JSON helper classes (`JsonEncode`, `JsonDecode`)
- XML encoding with schema validation (`reissue.schema.json`)
- Comprehensive test suite: 212 tests, 509 assertions
- PSR-4 autoloading support
- Full documentation and examples

### Requirements
- PHP 8.2 or higher
- psr/cache ^1.0|^2.0|^3.0 (optional)
- symfony/cache ^6.0|^7.0 (optional)

[1.0.0]: https://github.com/flaphl/reissue/releases/tag/v1.0.0
