<?php

declare(strict_types=1);

namespace PhpTs\Data;

final class TsUnion implements TsType
{
    /**
     * @param  TsType[]  $types
     */
    public function __construct(public array $types) {}

    public function render(RenderCtx $ctx): string
    {
        // remove duplicates
        $uniq = [];
        $seen = [];
        foreach ($this->types as $t) {
            $sig = $t->render($ctx);
            if (! isset($seen[$sig])) {
                $uniq[] = $t;
                $seen[$sig] = true;
            }
        }

        return implode(' | ', array_keys($seen));
    }
}
