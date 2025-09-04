<?php

declare(strict_types=1);

namespace Typographos\Extensions;

use Typographos\Config;
use Typographos\Core\Contracts\ExtensionInterface;

final readonly class TypeReplacementExtension implements ExtensionInterface
{
    public function __construct(
        private array $replacements = [],
    ) {}

    public function configure(Config $config): Config
    {
        foreach ($this->replacements as $from => $to) {
            $config = $config->withTypeReplacement($from, $to);
        }

        return $config;
    }
}
