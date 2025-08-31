<?php

declare(strict_types=1);

namespace Typographos\Tests\Fixtures;

class Arrays
{
    /**
     * @param  list<string>  $withDocCommentInConstructor
     */
    public function __construct(
        /** @var list<string> */
        public array $stringList,

        /** @var non-empty-list<non-empty-list<string>> */
        public array $nonEmptyNestedStringList,

        /** @var array<string,int> */
        public array $stringToIntObject,

        /** @var array<array-key,int> */
        public array $arrayKeyToIntObject,

        /** @var array<string|int,int> */
        public array $arrayKeyToIntObject_2,

        /** @var array<int,\Typographos\Tests\Fixtures\Scalars> */
        public array $scalars,

        public array $withDocCommentInConstructor,
    ) {}
}
