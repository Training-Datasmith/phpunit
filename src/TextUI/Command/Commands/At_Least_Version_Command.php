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
namespace Php_Unit\Text_Ui\Command;

use Php_Unit\Runner\Version;
use function version_compare;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class At_Least_Version_Command implements Command
{
    public function __construct(private string $version)
    {
    }
    public function execute(): Result
    {
        if (version_compare(Version::id(), $this->version, '>=')) {
            return Result::from();
        }
        return Result::from('', Result::FAILURE);
    }
}