<?php

declare(strict_types=1);

namespace Typographos;

use RuntimeException;

final class FileWriter
{
    /**
     * Write content to a file
     */
    public static function writeToFile(string $content, string $filePath): void
    {
        if (file_exists($filePath) && !is_writable($filePath) || !file_put_contents($filePath, $content)) {
            throw new RuntimeException('Failed to write generated types to file ' . $filePath);
        }
    }
}
