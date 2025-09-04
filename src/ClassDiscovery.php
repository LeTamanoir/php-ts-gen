<?php

declare(strict_types=1);

namespace Typographos;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Typographos\Attributes\TypeScript;

final class ClassDiscovery
{
    /**
     * Find classes to generate TypeScript for
     *
     * @param  class-string[]  $explicitClasses
     * @return class-string[]
     */
    public static function findClasses(array $explicitClasses, null|string $autoDiscoverDirectory): array
    {
        $classes = $explicitClasses;

        if ($autoDiscoverDirectory !== null) {
            $classes = [...$classes, ...self::scanDirectoryForClasses($autoDiscoverDirectory)];
        }

        return $classes;
    }

    /**
     * Scan a directory for classes with TypeScript attribute
     *
     * This method loads all PHP files in the directory and examines
     * all declared classes for the TypeScript attribute. Only classes
     * with this attribute are included in the result.
     *
     * @return class-string[]
     */
    private static function scanDirectoryForClasses(string $dir): array
    {
        if (!$dir || !is_dir($dir)) {
            throw new RuntimeException('Auto discover directory not found: ' . $dir);
        }

        // Get a snapshot of classes before loading files
        $existingClasses = array_flip(get_declared_classes());

        // Load all PHP files to register their classes
        self::loadPhpFilesFromDirectory($dir);

        // Find newly loaded classes with TypeScript attribute
        $classes = [];
        foreach (get_declared_classes() as $class) {
            // Skip classes that existed before we started loading
            if (isset($existingClasses[$class])) {
                continue;
            }

            $ref = new ReflectionClass($class);
            if ($ref->getAttributes(TypeScript::class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }

    /**
     * Recursively load all PHP files from directory
     */
    private static function loadPhpFilesFromDirectory(string $dir): void
    {
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
    }
}
