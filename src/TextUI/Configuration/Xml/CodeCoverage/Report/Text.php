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

use Php_Unit\Text_Ui\Configuration\File;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Text
{
    public function __construct(private File $target, private bool $show_uncovered_files, private bool $show_only_summary)
    {
    }
    public function target(): File
    {
        return $this->target;
    }
    public function show_uncovered_files(): bool
    {
        return $this->show_uncovered_files;
    }
    public function show_only_summary(): bool
    {
        return $this->show_only_summary;
    }
}