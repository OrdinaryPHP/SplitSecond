<?php

declare(strict_types=1);

namespace Ordinary\SplitSecond;

use DateTimeImmutable;
use DateTimeInterface;

class SplitSecond
{
    public static function Create(SplitSecondUnit $unit, int $splitSeconds): self
    {
        assert($splitSeconds < $unit->perSecond(), new UnexpectedValueException("Split seconds ($splitSeconds) is more than {$unit->name} will allow"));

        return new self($unit, $splitSeconds);
    }

    public static function Milliseconds(int $milliseconds): self
    {
        return self::Create(SplitSecondUnit::Millisecond, $milliseconds);
    }

    public static function Microseconds(int $microseconds): self
    {
        return self::Create(SplitSecondUnit::Microsecond, $microseconds);
    }

    public static function Nanoseconds(int $nanoseconds): self
    {
        return self::Create(SplitSecondUnit::Nanosecond, $nanoseconds);
    }

    public static function extractFromDateTime(DateTimeInterface $dateTime): self
    {
        return self::Microseconds((int) $dateTime->format('u'));
    }

    private function __construct(public readonly SplitSecondUnit $unit, public readonly int $splitSeconds)
    {
    }

    public function applyToDateTime(DateTimeInterface $dateTime): DateTimeImmutable
    {
        $result = DateTimeImmutable::createFromInterface($dateTime);

        return $result->setTime((int) $result->format('H'), (int) $result->format('i'), (int) $result->format('s'), self::toMicroseconds()->splitSeconds);
    }

    public function toMilliseconds(): self
    {
        return match ($this->unit) {
            SplitSecondUnit::Millisecond => $this,
            SplitSecondUnit::Microsecond => self::Microseconds($this->splitSeconds * 1_000),
            SplitSecondUnit::Nanosecond => self::Nanoseconds($this->splitSeconds * 1_000_000),
        };
    }

    public function toMicroseconds(): self
    {
        return match ($this->unit) {
            SplitSecondUnit::Millisecond => self::Milliseconds((int) ($this->splitSeconds / 1_000)),
            SplitSecondUnit::Microsecond => $this,
            SplitSecondUnit::Nanosecond => self::Nanoseconds($this->splitSeconds * 1_000),
        };
    }

    public function toNanoseconds(): self
    {
        return match ($this->unit) {
            SplitSecondUnit::Millisecond => self::Milliseconds((int) ($this->splitSeconds / 1_000_000)),
            SplitSecondUnit::Microsecond => self::Microseconds((int) ($this->splitSeconds / 1_000)),
            SplitSecondUnit::Nanosecond => $this,
        };
    }
}
