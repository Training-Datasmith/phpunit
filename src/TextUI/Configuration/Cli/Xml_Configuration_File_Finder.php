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
namespace Php_Unit\Text_Ui\Cli_Arguments;

use function getcwd;
use function is_dir;
use function is_file;
use function realpath;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Xml_Configuration_File_Finder
{
    public function find(Configuration $configuration): false|string
    {
        $use_default_configuration = $configuration->use_default_configuration();
        if ($configuration->has_configuration_file()) {
            if (is_dir($configuration->configuration_file())) {
                $candidate = $this->configuration_file_in_directory($configuration->configuration_file());
                if ($candidate !== false) {
                    return $candidate;
                }
                return false;
            }
            return $configuration->configuration_file();
        }
        if ($use_default_configuration) {
            $directory = getcwd();
            if ($directory !== false) {
                $candidate = $this->configuration_file_in_directory($directory);
                if ($candidate !== false) {
                    return $candidate;
                }
            }
        }
        return false;
    }
    private function configuration_file_in_directory(string $directory): false|string
    {
        $candidates = [$directory . '/phpunit.xml', $directory . '/phpunit.dist.xml', $directory . '/phpunit.xml.dist'];
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return realpath($candidate);
            }
        }
        return false;
    }
}