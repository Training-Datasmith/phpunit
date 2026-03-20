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
namespace Php_Unit\Text_Ui\Configuration;

use function assert;
use function file_get_contents;
use function file_put_contents;
use Php_Unit\Event\Facade as EventFacade;
use Php_Unit\Text_Ui\Cli_Arguments\Configuration as CliConfiguration;
use Php_Unit\Text_Ui\Cli_Arguments\Exception;
use Php_Unit\Text_Ui\Xml_Configuration\Configuration as XmlConfiguration;
use Php_Unit\Util\Version_Comparison_Operator;
use function serialize;
use function unserialize;
/**
 * CLI options and XML configuration are static within a single PHPUnit process.
 * It is therefore okay to use a Singleton registry here.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Registry
{
    private static ?Configuration $instance = null;
    public static function save_to(string $path): bool
    {
        $result = file_put_contents($path, serialize(self::get()));
        if ($result) {
            return true;
        }
        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }
    /**
     * This method is used by the "run test(s) in separate process" templates.
     *
     * @noinspection PhpUnused
     *
     * @codeCoverageIgnore
     */
    public static function load_from(string $path): void
    {
        $buffer = file_get_contents($path);
        assert($buffer !== false);
        self::$instance = unserialize($buffer, ['allowed_classes' => [Configuration::class, Php::class, Constant_Collection::class, Constant::class, Ini_Setting_Collection::class, Ini_Setting::class, Variable_Collection::class, Variable::class, Directory_Collection::class, Directory::class, File_Collection::class, File::class, Filter_Directory_Collection::class, Filter_Directory::class, Filter_File_Collection::class, Filter_File::class, Test_Directory_Collection::class, Test_Directory::class, Test_File_Collection::class, Test_File::class, Test_Suite_Collection::class, Test_Suite::class, Version_Comparison_Operator::class, Source::class]]);
    }
    public static function get(): Configuration
    {
        assert(self::$instance instanceof Configuration);
        return self::$instance;
    }
    /**
     * @throws \PHPUnit\TextUI\XmlConfiguration\Exception
     * @throws Exception
     * @throws NoCustomCssFileException
     */
    public static function init(Cli_Configuration $cli_configuration, Xml_Configuration $xml_configuration): Configuration
    {
        self::$instance = (new Merger())->merge($cli_configuration, $xml_configuration);
        Event_Facade::emitter()->test_runner_configured(self::$instance);
        return self::$instance;
    }
}