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

final class RecordType implements TypeScriptTypeInterface
{
    /**
     * @use HasPropertiesTrait<TypeScriptTypeInterface>
     */
    use HasPropertiesTrait;

    public function __construct(
        public string $name,
    ) {}

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function from(GenCtx $ctx, string $className): self
    {
        $ref = new ReflectionClass($className);
        $record = new RecordType($ref->getShortName());

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $propName = $prop->getName();

            $type = TypeResolver::resolve($prop);

            $ctx->parentProperty = $prop;
            $ts = TypeConverter::convert($ctx, $type);
            $ctx->parentProperty = null;

            $record->addProperty($propName, $ts);
        }

        return $record;
    }

    #[Override]
    public function render(RenderCtx $ctx): string
    {
        $indent = str_repeat($ctx->indent, $ctx->depth);
        $propIndent = $indent . $ctx->indent;

        $ts = $indent . 'export interface ' . $this->name . " {\n";

        foreach ($this->properties as $name => $type) {
            $ts .= $propIndent . Utils::tsProp($name) . ': ' . $type->render($ctx) . "\n";
        }

        $ts .= $indent . "}\n";

        return $ts;
    }
}
