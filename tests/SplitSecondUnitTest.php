<?php

declare(strict_types=1);

namespace Ordinary\SplitSecond;

use Generator;
use PHPUnit\Framework\TestCase;

class SplitSecondUnitTest extends TestCase
{
    public static function splitSecondEnumProvider(): Generator
    {
        foreach (SplitSecondUnit::cases() as $case) {
            yield [$case];
        }
    }

    /** @dataProvider splitSecondEnumProvider */
    public function testPerSecond(SplitSecondUnit $case): void
    {
        $expected = match ($case) {
            SplitSecondUnit::Millisecond => 1_000,
            SplitSecondUnit::Microsecond => 1_000_000,
            SplitSecondUnit::Nanosecond => 1_000_000_000,
        };

        self::assertSame($expected, $case->perSecond());
    }

    /** @dataProvider splitSecondEnumProvider */
    public function testMaxInSecond(SplitSecondUnit $case): void
    {
        $expected = match ($case) {
            SplitSecondUnit::Millisecond => 999,
            SplitSecondUnit::Microsecond => 999_999,
            SplitSecondUnit::Nanosecond => 999_999_999,
        };

        self::assertSame($expected, $case->maxInSecond());
    }

    /** @dataProvider splitSecondEnumProvider */
    public function testAbbr(SplitSecondUnit $case): void
    {
        $expected = match ($case) {
            SplitSecondUnit::Millisecond => 'ms',
            SplitSecondUnit::Microsecond => 'μs',
            SplitSecondUnit::Nanosecond => 'ns',
        };

        self::assertSame($expected, $case->abbr());
    }

    /** @dataProvider splitSecondEnumProvider */
    public function testDecimalPrecision(SplitSecondUnit $case): void
    {
        $expected = match ($case) {
            SplitSecondUnit::Millisecond => 3,
            SplitSecondUnit::Microsecond => 6,
            SplitSecondUnit::Nanosecond => 9,
        };

        self::assertSame($expected, $case->decimalPrecision());
    }
}
