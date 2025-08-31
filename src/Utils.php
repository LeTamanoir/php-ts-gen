<?php

declare(strict_types=1);

namespace PhpTs;

class Utils
{
    /**
     * Property names can be quoted—so we only quote when necessary.
     */
    public static function tsProp(string $raw): string
    {
        if (preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $raw)) {
            return $raw;
        }
        // Quote and escape inner quotes/backslashes
        $escaped = addcslashes($raw, '\\"');

        return "\"{$escaped}\"";
    }

    /**
     * Make sure namespace segments are valid TS identifiers.
     * TS namespaces cannot be quoted, so we conservatively normalize.
     */
    public static function tsIdent(string $raw): string
    {
        // Replace invalid chars with underscores; prefix if it starts with a digit.
        $id = preg_replace('/[^A-Za-z0-9_$]/', '_', $raw) ?? '_';
        if (preg_match('/^[0-9]/', $id)) {
            $id = '_'.$id;
        }

        return $id;
    }
}
