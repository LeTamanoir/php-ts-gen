<?php

declare(strict_types=1);

namespace Typographos\Attributes;

use Attribute;

/**
 * TypeScript attribute to mark a class for type generation.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class TypeScript {}
