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
namespace Php_Unit\Runner\Phpt;

use Php_Unit\Runner\Exception as RunnerException;
use RuntimeException;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Phpt_External_File_Cannot_Be_Loaded_Exception extends RuntimeException implements Runner_Exception
{
    public function __construct(string $section, string $file)
    {
        parent::__construct(sprintf('Could not load --%s-- %s for PHPT file', $section . '_EXTERNAL', $file));
    }
}