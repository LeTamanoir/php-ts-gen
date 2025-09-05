<?php

declare(strict_types=1);

namespace Typographos\Dto;

use InvalidArgumentException;
use Override;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Typographos\Interfaces\TypeScriptTypeInterface;
use Typographos\Traits\HasPropertiesTrait;
use Typographos\TypeConverter;
use Typographos\TypeResolver;
use Typographos\Utils;

final class InlineRecordType implements TypeScriptTypeInterface
{
    /**
     * @use HasPropertiesTrait<TypeScriptTypeInterface>
     */
    use HasPropertiesTrait;

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public static function from(GenCtx $ctx, string $className): self
    {
        $ref = new ReflectionClass($className);
        $record = new InlineRecordType();

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propName = $prop->getName();

            $type = TypeResolver::resolve($prop);

            $ts = TypeConverter::convert($ctx, $type);

            $record->addProperty($propName, $ts);
        }

        return $record;
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth + 1);
        $propIndent = $indent . $ctx->indent;

        $ts = "{\n";

        foreach ($this->properties as $name => $type) {
            $ts .= $propIndent . Utils::tsProp($name) . ': ' . $type->render($ctx) . "\n";
        }

        $ts .= $indent . '}';

        return $ts;
    }
}
