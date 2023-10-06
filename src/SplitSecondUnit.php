<?php
namespace Ordinary\SplitSecond;
enum SplitSecondUnit
{
    case Millisecond;
    case Microsecond;
    case Nanosecond;

    public function perSecond(): int
    {
        return match ($this) {
            self::Millisecond => 1_000,
            self::Microsecond => 1_000_000,
            self::Nanosecond => 1_000_000_000,
        };
    }

    public function maxInSecond(): int
    {
        return $this->perSecond() - 1;
    }

    public function decimalPrecision(): int
    {
        return match ($this) {
            self::Millisecond => 3,
            self::Microsecond => 6,
            self::Nanosecond => 9,
        };
    }

    public function abbr(): string
    {
        return match ($this) {
            self::Millisecond => 'ms',
            self::Microsecond => 'μs',
            self::Nanosecond => 'ns',
        };
    }
}