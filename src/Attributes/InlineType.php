<?php

declare(strict_types=1);

namespace Typographos\Attributes;

use Attribute;

/**
 * InlineType attribute to mark a property that references a class
 * to be inlined instead of added to the generation queue.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class InlineType
{
}
