<?php

declare(strict_types=1);

namespace Pcd\Support;

use DateTimeImmutable;
use DateTimeZone;

final class Clock
{
    public static function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public static function sql(?DateTimeImmutable $moment = null): string
    {
        return ($moment ?? self::now())->format('Y-m-d H:i:s.u');
    }

    public static function iso(?string $sql): string
    {
        if ($sql === null || $sql === '') {
            return '';
        }

        return (new DateTimeImmutable($sql, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s\Z');
    }

    public static function fromIso(string $iso): DateTimeImmutable
    {
        return (new DateTimeImmutable($iso, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('UTC'));
    }

    public static function timestamp(?string $sql): int
    {
        if ($sql === null || $sql === '') {
            return 0;
        }

        return (new DateTimeImmutable($sql, new DateTimeZone('UTC')))->getTimestamp();
    }
}
