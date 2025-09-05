<?php

declare(strict_types=1);

namespace Typographos;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use RuntimeException;
use SplFileInfo;
use Typographos\Attributes\TypeScript;

final class ClassDiscovery
{
    /**
     * Scan a directory for classes with TypeScript attribute
     *
     * This method loads all PHP files in the directory and examines
     * all declared classes for the TypeScript attribute. Only classes
     * with this attribute are included in the result.
     *
     * @return class-string[]
     *
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public static function discover(string $dir): array
    {
        if (! $dir || ! is_dir($dir)) {
            throw new RuntimeException('Auto discover directory not found: '.$dir);
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $path = $file->getPathname();

                if (is_file($path)) {
                    require_once $path;
                }
            }
        }

        $classes = [];

        foreach (get_declared_classes() as $class) {
            $ref = new ReflectionClass($class);
            $fileName = $ref->getFileName();
            if ($fileName !== false && ! str_starts_with($fileName, $dir)) {
                continue;
            }
            if ($ref->getAttributes(TypeScript::class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
