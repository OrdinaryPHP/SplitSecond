<?php

declare(strict_types=1);

namespace Ordinary\SplitSecond;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use PHPUnit\Framework\TestCase;

class SplitSecondTest extends TestCase
{
    public static function splitSecondUnitEnumProvider(): Generator
    {
        foreach (SplitSecondUnit::cases() as $case) {
            yield [$case];
        }
    }

    public static function dateProvider(): Generator
    {
        yield [new DateTimeImmutable()];
        yield [new DateTimeImmutable('99999-12-31T23:59:59.919191')];
        yield [new DateTimeImmutable('0000-01-01T00:00:00.919191')];
        yield [new DateTimeImmutable('-9999-01-01T00:00:00.919191')];
    }

    public static function toMillisecondsProvider(): Generator
    {
        yield [SplitSecond::milliseconds(999), 999];

        yield [SplitSecond::microseconds(0), 0];
        yield [SplitSecond::microseconds(999), 0];
        yield [SplitSecond::microseconds(1_000), 1];
        yield [SplitSecond::microseconds(100_000), 100];
        yield [SplitSecond::microseconds(999_999), 999];

        yield [SplitSecond::nanoseconds(0), 0];
        yield [SplitSecond::nanoseconds(999_999), 0];
        yield [SplitSecond::nanoseconds(1_000_000), 1];
        yield [SplitSecond::nanoseconds(100_000_000), 100];
        yield [SplitSecond::nanoseconds(999_999_999), 999];
    }

    public static function applyToDateTimeProvider(): Generator
    {
        yield [SplitSecond::milliseconds(999)];
        yield [SplitSecond::microseconds(999_999)];
        yield [SplitSecond::nanoseconds(999_999_999)];
    }

    public static function toMicrosecondsProvider(): Generator
    {
        yield [SplitSecond::milliseconds(0), 0];
        yield [SplitSecond::milliseconds(1), 1_000];
        yield [SplitSecond::milliseconds(999), 999_000];

        yield [SplitSecond::microseconds(999_999), 999_999];

        yield [SplitSecond::nanoseconds(0), 0];
        yield [SplitSecond::nanoseconds(999), 0];
        yield [SplitSecond::nanoseconds(1_000), 1];
        yield [SplitSecond::nanoseconds(1_000_000), 1_000];
        yield [SplitSecond::nanoseconds(100_000_000), 100_000];
        yield [SplitSecond::nanoseconds(999_999_999), 999_999];
    }

    public static function toNanosecondsProvider(): Generator
    {
        yield [SplitSecond::milliseconds(0), 0];
        yield [SplitSecond::milliseconds(1), 1_000_000];
        yield [SplitSecond::milliseconds(999), 999_000_000];

        yield [SplitSecond::microseconds(0), 0];
        yield [SplitSecond::microseconds(1), 1_000];
        yield [SplitSecond::microseconds(100), 100_000];
        yield [SplitSecond::microseconds(999_999), 999_999_000];

        yield [SplitSecond::nanoseconds(999_999_999), 999_999_999];
    }

    public function testMicroseconds(): void
    {
        $split = SplitSecond::microseconds(SplitSecondUnit::Microsecond->maxInSecond());

        self::assertInstanceOf(SplitSecond::class, $split);
        self::assertSame(SplitSecondUnit::Microsecond, $split->unit);
        self::assertSame(SplitSecondUnit::Microsecond->maxInSecond(), $split->splitSeconds);
    }

    /** @dataProvider splitSecondUnitEnumProvider */
    public function testCreate(SplitSecondUnit $unit): void
    {
        $maxValue = $unit->maxInSecond();
        $max = SplitSecond::create($unit, $maxValue);

        self::assertInstanceOf(SplitSecond::class, $max);
        self::assertSame($unit, $max->unit);
        self::assertSame($maxValue, $max->splitSeconds);

        $belowMaxValue = $unit->maxInSecond();
        $belowMax = SplitSecond::create($unit, $belowMaxValue);

        self::assertInstanceOf(SplitSecond::class, $belowMax);
        self::assertSame($unit, $belowMax->unit);
        self::assertSame($belowMaxValue, $belowMax->splitSeconds);

        $oneValue = 1;
        $one = SplitSecond::create($unit, $oneValue);

        self::assertInstanceOf(SplitSecond::class, $one);
        self::assertSame($unit, $one->unit);
        self::assertSame($oneValue, $one->splitSeconds);

        $zeroValue = 0;
        $zero = SplitSecond::create($unit, $zeroValue);

        self::assertInstanceOf(SplitSecond::class, $zero);
        self::assertSame($unit, $zero->unit);
        self::assertSame($zeroValue, $zero->splitSeconds);

        // test above max value
        self::expectException(UnexpectedValueException::class);
        SplitSecond::create($unit, $unit->maxInSecond() + 1);
    }

    public function testNanoseconds(): void
    {
        $split = SplitSecond::nanoseconds(SplitSecondUnit::Nanosecond->maxInSecond());

        self::assertInstanceOf(SplitSecond::class, $split);
        self::assertSame(SplitSecondUnit::Nanosecond, $split->unit);
        self::assertSame(SplitSecondUnit::Nanosecond->maxInSecond(), $split->splitSeconds);
    }

    /** @dataProvider dateProvider */
    public function testExtractFromDateTime(DateTimeInterface $date): void
    {
        $expected = (int) $date->format('u');
        $split = SplitSecond::extractFromDateTime($date);

        self::assertSame(SplitSecondUnit::Microsecond, $split->unit);
        self::assertSame($expected, $split->splitSeconds);
    }

    public function testMilliseconds(): void
    {
        $split = SplitSecond::milliseconds(SplitSecondUnit::Millisecond->maxInSecond());

        self::assertInstanceOf(SplitSecond::class, $split);
        self::assertSame(SplitSecondUnit::Millisecond, $split->unit);
        self::assertSame(SplitSecondUnit::Millisecond->maxInSecond(), $split->splitSeconds);
    }

    /** @dataProvider toMillisecondsProvider */
    public function testToMilliseconds(SplitSecond $split, int $expected): void
    {
        $milli = $split->toMilliseconds();

        if ($split->unit === SplitSecondUnit::Millisecond) {
            self::assertSame($split, $milli);
        } else {
            self::assertNotSame($split, $milli);
            self::assertInstanceOf(SplitSecond::class, $milli);
            self::assertSame(SplitSecondUnit::Millisecond, $milli->unit);
        }

        self::assertSame($expected, $milli->splitSeconds);
    }

    /** @dataProvider applyToDateTimeProvider */
    public function testApplyToDateTime(SplitSecond $splitSecond): void
    {
        $date = (new DateTimeImmutable())->setTime(0, 0, 0, 0);
        $applied = $splitSecond->applyToDateTime($date);

        self::assertNotSame($date, $applied);
        self::assertSame($date->format('c'), $applied->format('c'));
        self::assertSame($splitSecond->toMicroseconds()->splitSeconds, (int) $applied->format('u'));
    }

    /** @dataProvider applyToDateTimeProvider */
    public function testApplyToDateTimeMsMatch(SplitSecond $splitSecond): void
    {
        $dateMutable = (new DateTime())->setTime(0, 0, 0, $splitSecond->toMicroseconds()->splitSeconds);
        $dateImmutable = (new DateTimeImmutable())->setTime(0, 0, 0, $splitSecond->toMicroseconds()->splitSeconds);
        $appliedImmutable = $splitSecond->applyToDateTime($dateImmutable);
        $appliedMutable = $splitSecond->applyToDateTime($dateMutable);

        self::assertSame($dateImmutable, $appliedImmutable);
        self::assertNotSame($dateMutable, $appliedMutable);
    }

    /** @dataProvider toMicrosecondsProvider */
    public function testToMicroseconds(SplitSecond $split, int $expected): void
    {
        $micro = $split->toMicroseconds();

        if ($split->unit === SplitSecondUnit::Microsecond) {
            self::assertSame($split, $micro);
        } else {
            self::assertNotSame($split, $micro);
            self::assertInstanceOf(SplitSecond::class, $micro);
            self::assertSame(SplitSecondUnit::Microsecond, $micro->unit);
        }

        self::assertSame($expected, $micro->splitSeconds);
    }

    /** @dataProvider toNanosecondsProvider */
    public function testToNanoseconds(SplitSecond $split, int $expected): void
    {
        $nano = $split->toNanoseconds();

        if ($split->unit === SplitSecondUnit::Nanosecond) {
            self::assertSame($split, $nano);
        } else {
            self::assertNotSame($split, $nano);
            self::assertInstanceOf(SplitSecond::class, $nano);
            self::assertSame(SplitSecondUnit::Nanosecond, $nano->unit);
        }

        self::assertSame($expected, $nano->splitSeconds);
    }
}
