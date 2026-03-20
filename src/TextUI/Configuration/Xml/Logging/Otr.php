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
namespace Php_Unit\Text_Ui\Xml_Configuration\Logging;

use Php_Unit\Text_Ui\Configuration\File;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Otr
{
    public function __construct(private File $target, private bool $include_git_information)
    {
    }
    public function target(): File
    {
        return $this->target;
    }
    public function include_git_information(): bool
    {
        return $this->include_git_information;
    }
}