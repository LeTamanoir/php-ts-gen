# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

- **Testing**: `composer test` (uses Pest)
- **Test Coverage**: `composer test-coverage`
- **Linting/Formatting**: Uses Mago
- **Static Analysis**: Uses Mago

## Architecture

This is a PHP library that generates TypeScript types from PHP Data Transfer Objects (DTOs). The core architecture consists of:

### Core Classes (Refactored Architecture)
- `Generator`: Main orchestrator with fluent interface that coordinates the entire generation process and handles file writing
- `ClassDiscovery`: Static utility for finding classes with TypeScript attributes from directories
- `TypeResolver`: Resolves PHP types, handling special cases like `array`, `self`, `parent`, and unions
- `TypeConverter`: Static utility that converts resolved PHP types to TypeScript type objects
- `Queue`: Manages the processing queue of classes during generation
- `GenCtx`: Context object that carries generation state (queue, type replacements, parent property)

The library uses static utility classes for optimal performance while maintaining an intuitive fluent API through the main `Generator` class.

### Type System
The library models TypeScript types through a hierarchy in `src/Dto/`:
- `TypeScriptTypeInterface`: Root contract for all type representations
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
2. **Context Creation**: `GenCtx` is created with the queue, type replacements, and parent property
3. **Namespace Building**: `RootNamespaceType::from()` processes the queue and builds the namespace hierarchy
4. **Type Resolution**: `TypeResolver` handles special PHP types (array, self, parent, unions) 
5. **Type Conversion**: `TypeConverter::convert()` maps resolved PHP types to TypeScript type objects
6. **Rendering**: Generate final TypeScript with proper indentation and namespacing
7. **Output**: `Generator` writes the result directly to the specified file

### Architecture Benefits
- Clean separation of concerns with dedicated utility classes
- Static methods eliminate unnecessary object instantiation overhead
- Fluent interface provides intuitive developer experience
- Simple, direct API without complex configuration objects

The library requires PHP 8.4+ and uses modern PHP features like attributes, property promotion, and named arguments extensively.