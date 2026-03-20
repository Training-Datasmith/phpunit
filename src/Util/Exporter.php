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
namespace Php_Unit\Util;

use Php_Unit\Text_Ui\Configuration\Registry as ConfigurationRegistry;
use Sebastian_Bergmann\Exporter\Exporter as OriginalExporter;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class Exporter
{
    private static ?Original_Exporter $exporter = null;
    public static function export(mixed $value): string
    {
        return self::exporter()->export($value);
    }
    /**
     * @param array<mixed> $data
     */
    public static function shortened_recursive_export(array $data): string
    {
        return self::exporter()->shortened_recursive_export($data);
    }
    public static function shortened_export(mixed $value): string
    {
        return self::exporter()->shortened_export($value);
    }
    private static function exporter(): Original_Exporter
    {
        if (self::$exporter !== null) {
            return self::$exporter;
        }
        self::$exporter = new Original_Exporter(Configuration_Registry::get()->shorten_arrays_for_export_threshold());
        return self::$exporter;
    }
}