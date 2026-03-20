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
namespace Php_Unit\Framework\Mock_Object\Generator;

use Sebastian_Bergmann\Template\Template;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This trait is not covered by the backward compatibility promise for PHPUnit
 */
trait Template_Loader
{
    /**
     * @var array<string,Template>
     */
    private static array $templates = [];
    private function load_template(string $template): Template
    {
        $filename = __DIR__ . '/templates/' . $template;
        if (!isset(self::$templates[$filename])) {
            self::$templates[$filename] = new Template($filename);
        }
        return self::$templates[$filename];
    }
}