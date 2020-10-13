<?php

namespace KFOSOFT\Cron\Value;

use RuntimeException;

class CommandType
{
    const INTERNAL = 'internal';
    const EXTERNAL = 'external';

    private const CLASSES = [
        self::INTERNAL => InternalCronJob::class,
        self::EXTERNAL => ExternalCronJob::class,
    ];

    /**
     * @param string $type
     *
     * @return string
     */
    public static function getClassNameType(string $type): string
    {
        if (!\array_key_exists($type, self::CLASSES)) {
            throw new RuntimeException(sprintf('Undefined command type %s. Use "internal" or "external".', $type));
        }

        return self::CLASSES[$type];
    }
}
