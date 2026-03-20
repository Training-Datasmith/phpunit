<?php

declare(strict_types=1);

/**
 * Example 3: Data providers — testing many input/output pairs efficiently.
 *
 * Data providers eliminate copy-paste test methods by separating the test
 * logic from the input/output fixtures.
 *
 * Run with:
 *   vendor/bin/phpunit examples/03_data_providers.php
 */

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// -----------------------------------------------------------------------
// Production code being tested
// -----------------------------------------------------------------------

final class RomanNumeralConverter
{
    private const MAP = [
        1000 => 'M',
        900  => 'CM',
        500  => 'D',
        400  => 'CD',
        100  => 'C',
        90   => 'XC',
        50   => 'L',
        40   => 'XL',
        10   => 'X',
        9    => 'IX',
        5    => 'V',
        4    => 'IV',
        1    => 'I',
    ];

    /**
     * @param positive-int $number Integer in range [1, 3999]
     *
     * @throws \InvalidArgumentException when number is out of the supported range
     */
    public function convert(int $number): string
    {
        if ($number < 1 || $number > 3999) {
            throw new \InvalidArgumentException(
                "Number must be between 1 and 3999, got {$number}.",
            );
        }

        $result = '';
        foreach (self::MAP as $value => $numeral) {
            while ($number >= $value) {
                $result  .= $numeral;
                $number  -= $value;
            }
        }

        return $result;
    }
}

// -----------------------------------------------------------------------
// Tests
// -----------------------------------------------------------------------

final class RomanNumeralConverterTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Attribute-based data provider (PHP 8 style — preferred)
    // -----------------------------------------------------------------------

    /**
     * Provides well-known integer → Roman numeral pairs.
     *
     * Each inner array becomes the argument list for the test method.
     * The array key is used as the test name in output.
     *
     * @return array<string, array{int, string}>
     */
    public static function provideKnownConversions(): array
    {
        return [
            'one'              => [1,    'I'],
            'four'             => [4,    'IV'],
            'five'             => [5,    'V'],
            'nine'             => [9,    'IX'],
            'forty'            => [40,   'XL'],
            'ninety'           => [90,   'XC'],
            'four-hundred'     => [400,  'CD'],
            'nine-hundred'     => [900,  'CM'],
            'year-1994'        => [1994, 'MCMXCIV'],
            'year-2024'        => [2024, 'MMXXIV'],
            'maximum'          => [3999, 'MMMCMXCIX'],
        ];
    }

    #[DataProvider('provideKnownConversions')]
    public function test_converts_integer_to_roman_numeral(int $input, string $expected): void
    {
        $converter = new RomanNumeralConverter();
        $this->assertSame($expected, $converter->convert($input));
    }

    // -----------------------------------------------------------------------
    // Boundary / edge case data provider
    // -----------------------------------------------------------------------

    /**
     * @return array<string, array{int}>
     */
    public static function provideOutOfRangeValues(): array
    {
        return [
            'zero'         => [0],
            'negative-one' => [-1],
            'four-thousand' => [4000],
            'max-int'      => [PHP_INT_MAX],
        ];
    }

    #[DataProvider('provideOutOfRangeValues')]
    public function test_throws_for_out_of_range_values(int $invalid): void
    {
        $converter = new RomanNumeralConverter();

        $this->expectException(\InvalidArgumentException::class);
        $converter->convert($invalid);
    }

    // -----------------------------------------------------------------------
    // Generator-based data provider (lazy — useful for large datasets)
    // -----------------------------------------------------------------------

    /**
     * Yields every integer from 1 to 10 with its expected Roman numeral.
     *
     * Using a generator avoids building the entire array in memory before
     * the first test runs.
     *
     * @return \Generator<string, array{int, string}>
     */
    public static function provideFirstTenIntegers(): \Generator
    {
        $expected = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];

        foreach ($expected as $index => $numeral) {
            yield "integer " . ($index + 1) => [$index + 1, $numeral];
        }
    }

    #[DataProvider('provideFirstTenIntegers')]
    public function test_converts_first_ten_integers(int $input, string $expected): void
    {
        $converter = new RomanNumeralConverter();
        $this->assertSame($expected, $converter->convert($input));
    }
}
