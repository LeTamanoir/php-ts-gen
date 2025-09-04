# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

- **Testing**: `composer test` (uses Pest)
- **Test Coverage**: `composer test-coverage`
- **Linting/Formatting**: `composer format` (uses Laravel Pint)
- **Static Analysis**: `vendor/bin/psalm`

## Architecture

This is a PHP library that generates TypeScript types from PHP Data Transfer Objects (DTOs). The core architecture consists of:

### Core Classes (Refactored Architecture)
- `Generator`: Main orchestrator that coordinates the generation process
- `ClassDiscovery`: Finds classes with TypeScript attributes from directories or explicit lists
- `TypeResolver`: Resolves PHP types, handling special cases like `array`, `self`, `parent`, and unions
- `TypeConverter`: Converts resolved PHP types to TypeScript type objects
- `FileWriter`: Handles writing generated TypeScript to files
- `Config`: Configuration object with fluent builder pattern for customizing generation behavior
- `Queue`: Manages the queue of classes to process during generation

The library provides a clean, fluent API through `Generator` with static utility classes for optimal performance.

### Type System
The library models TypeScript types through a hierarchy in `src/Dto/`:
- `TypeScriptType` interface: Root contract for all type representations
- `ScalarType`: Primitive types (string, number, boolean, etc.)
- `ArrayType`: Array types with support for PHPDoc annotations (`list<T>`, `array<K,V>`, etc.)
- `UnionType`: Union types (`A | B`)
- `RecordType`: Object/interface types generated from PHP classes
- `ReferenceType`: References to user-defined classes
- `RawType`: Custom raw TypeScript code from type replacements
- `NamespaceType`/`RootNamespaceType`: Namespace organization

### Key Features
- **Attribute-driven discovery**: Classes marked with `#[TypeScript]` are auto-discovered
- **PHPDoc array parsing**: Complex array types like `list<T>`, `non-empty-list<T>`, `array<K,V>`
- **Namespace mapping**: PHP namespaces become nested TypeScript namespaces
- **Type replacements**: Map PHP types to custom TypeScript types
- **Union type support**: PHP unions and nullable types

### Processing Flow
1. **Discovery**: `ClassDiscovery` finds classes marked with `#[TypeScript]` attribute
2. **Resolution**: `TypeResolver` handles special PHP types (array, self, parent, unions)
3. **Conversion**: `TypeConverter` maps PHP types to TypeScript type objects
4. **Organization**: Build namespace hierarchy using the Dto classes
5. **Rendering**: Generate final TypeScript with proper indentation and namespacing
6. **Output**: `FileWriter` writes the result to the specified file

### Architecture Benefits
- Clean separation of concerns with dedicated utility classes
- Static methods eliminate unnecessary object instantiation overhead
- Fluent interface provides intuitive developer experience
- Type-safe configuration with immutable Config objects

The library requires PHP 8.4+ and uses modern PHP features like attributes, property promotion, and named arguments extensively.