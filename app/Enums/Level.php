<?php

namespace App\Enums;

enum Level: string
{
    case Basecamp = 'basecamp';
    case Camp     = 'camp';
    case Summit   = 'summit';

    public function label(): string
    {
        return match ($this) {
            self::Basecamp => 'Basecamp',
            self::Camp     => 'Camp',
            self::Summit   => 'Summit',
        };
    }

    public function subtitle(): string
    {
        return match ($this) {
            self::Basecamp => 'Leading Self',
            self::Camp     => 'Leading Others',
            self::Summit   => 'Leading Leaders',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::Basecamp => 1,
            self::Camp     => 2,
            self::Summit   => 3,
        };
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Basecamp => self::Camp,
            self::Camp     => self::Summit,
            self::Summit   => null,
        };
    }
}