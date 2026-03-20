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
namespace Php_Unit\Text_Ui\Xml_Configuration;

use function str_replace;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Generator
{
    private const string TEMPLATE = <<<'EOT'
    <?xml version="1.0" encoding="UTF-8"?>
    <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:noNamespaceSchemaLocation="{schema_location}"
             bootstrap="{bootstrap_script}"
             cacheDirectory="{cache_directory}"
             executionOrder="depends,defects"
             requireCoverageMetadata="true"
             beStrictAboutCoverageMetadata="true"
             beStrictAboutOutputDuringTests="true"
             displayDetailsOnPhpunitDeprecations="true"
             failOnPhpunitDeprecation="true"
             failOnRisky="true"
             failOnWarning="true">
        <testsuites>
            <testsuite name="default">
                <directory>{tests_directory}</directory>
            </testsuite>
        </testsuites>
    
        <source ignoreIndirectDeprecations="true" restrictNotices="true" restrictWarnings="true">
            <include>
                <directory>{src_directory}</directory>
            </include>
        </source>
    </phpunit>
    
    EOT;
    public function generate_default_configuration(string $schema_location, string $bootstrap_script, string $tests_directory, string $src_directory, string $cache_directory): string
    {
        return str_replace(['{schema_location}', '{bootstrap_script}', '{tests_directory}', '{src_directory}', '{cache_directory}'], [$schema_location, $bootstrap_script, $tests_directory, $src_directory, $cache_directory], self::TEMPLATE);
    }
}