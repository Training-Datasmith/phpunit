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

use Php_Unit\Text_Ui\Cli_Arguments\Builder as CliConfigurationBuilder;
use Php_Unit\Text_Ui\Cli_Arguments\Exception as CliConfigurationException;
use Php_Unit\Text_Ui\Cli_Arguments\Xml_Configuration_File_Finder;
use Php_Unit\Text_Ui\Xml_Configuration\Default_Configuration;
use Php_Unit\Text_Ui\Xml_Configuration\Exception as XmlConfigurationException;
use Php_Unit\Text_Ui\Xml_Configuration\Loader;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @codeCoverageIgnore
 */
final readonly class Builder
{
    /**
     * @param list<string> $argv
     *
     * @throws ConfigurationCannotBeBuiltException
     */
    public function build(array $argv): Configuration
    {
        try {
            $cli_configuration = (new Cli_Configuration_Builder())->from_parameters($argv);
            $configuration_file = (new Xml_Configuration_File_Finder())->find($cli_configuration);
            $xml_configuration = Default_Configuration::create();
            if ($configuration_file !== false) {
                $xml_configuration = (new Loader())->load($configuration_file);
            }
            return Registry::init($cli_configuration, $xml_configuration);
        } catch (Cli_Configuration_Exception|Xml_Configuration_Exception $e) {
            throw new Configuration_Cannot_Be_Built_Exception($e->get_message(), $e->get_code(), $e);
        }
    }
}