<?php

declare (strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Php_Unit\Metadata;

use const PHP_EOL;
use Php_Unit\Exception;
use RuntimeException;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Invalid_Attribute_Exception extends RuntimeException implements Exception
{
    /**
     * @param non-empty-string $attributeName
     * @param non-empty-string $target
     * @param non-empty-string $file
     * @param positive-int     $line
     * @param non-empty-string $message
     */
    public function __construct(string $attribute_name, string $target, string $file, int $line, string $message)
    {
        parent::__construct(sprintf('Invalid attribute %s for %s in %s:%d%s%s', $attribute_name, $target, $file, $line, PHP_EOL, $message));
    }
}