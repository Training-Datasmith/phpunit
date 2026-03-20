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
namespace Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report;

use Php_Unit\Text_Ui\Configuration\Directory;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Xml
{
    public function __construct(private Directory $target, private bool $include_source)
    {
    }
    public function target(): Directory
    {
        return $this->target;
    }
    public function include_source(): bool
    {
        return $this->include_source;
    }
}