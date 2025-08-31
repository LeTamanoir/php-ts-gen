<?php

declare(strict_types=1);

namespace PhpTs\Data;

final class TsIntersection implements TsType
{
    /**
     * @param  TsType[]  $types
     */
    private function __construct(private array $types) {}

    /**
     * @param  TsType[]  $types
     */
    public static function from(array $types): TsType
    {
        // flatten nested intersections
        /** @var TsType[] */
        $flat = [];
        foreach ($types as $t) {
            if ($t instanceof self) {
                $flat = [...$flat, ...$t->types];
            } else {
                $flat[] = $t;
            }
        }

        // remove duplicates
        $uniq = [];
        $seen = [];
        foreach ($flat as $t) {
            $sig = $t->render(RenderCtx::root());
            if (! isset($seen[$sig])) {
                $uniq[] = $t;
                $seen[$sig] = true;
            }
        }

        if (count($uniq) === 0) {
            return TsScalar::never;
        }
        if (count($uniq) === 1) {
            return $uniq[0];
        }

        return new self($uniq);
    }

    public function render(RenderCtx $ctx): string
    {
        return implode(' & ', array_map(fn (TsType $t) => $t->render($ctx), $this->types));
    }
}
