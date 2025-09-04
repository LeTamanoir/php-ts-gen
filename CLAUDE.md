# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

- **Testing**: `composer test` (uses Pest)
- **Test Coverage**: `composer test-coverage`
- **Linting/Formatting**: `composer format` (uses Laravel Pint)
- **Static Analysis**: `vendor/bin/psalm`

## Architecture

This is a PHP library that generates TypeScript types from PHP Data Transfer Objects (DTOs). The core architecture consists of:

### Core Classes
- `Codegen`: Main code generation orchestrator that discovers classes, parses them, and writes TypeScript output
- `Config`: Configuration object with fluent builder pattern for customizing generation behavior
- `Queue`: Manages the queue of classes to process during generation

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
1. Auto-discover classes in specified directory or use explicit class list
2. Parse each class using reflection, extracting public properties
3. Build type hierarchy using the Dto classes
4. Render final TypeScript output with proper indentation and namespacing
5. Write to specified output file

The library requires PHP 8.4+ and uses modern PHP features like attributes, property promotion, and named arguments extensively.