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

use Php_Unit\Text_Ui\Help;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Show_Help_Command implements Command
{
    public function __construct(private int $shell_exit_code)
    {
    }
    public function execute(): Result
    {
        return Result::from((new Help())->generate(), $this->shell_exit_code);
    }
}