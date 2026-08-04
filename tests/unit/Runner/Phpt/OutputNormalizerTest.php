<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Runner\Phpt;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutputNormalizer::class)]
#[Small]
final class OutputNormalizerTest extends TestCase
{
    public function testNormalizesLineEndings(): void
    {
        $this->assertSame(
            "line one\nline two\n",
            OutputNormalizer::normalize("line one\r\nline two\r"),
        );
    }

    public function testPreservesWindowsPathSeparators(): void
    {
        $input = "C:\\Users\\Matt\\tests\\end-to-end\\FooTest.php:21";

        $this->assertSame($input, OutputNormalizer::normalize($input));
    }

    public function testPreservesNamespaceSeparators(): void
    {
        $this->assertSame(
            'PHPUnit\TestFixture\NoLogNoCcTest',
            OutputNormalizer::normalize('PHPUnit\TestFixture\NoLogNoCcTest'),
        );
    }
}
