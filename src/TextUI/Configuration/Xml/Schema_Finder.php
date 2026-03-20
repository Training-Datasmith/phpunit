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

use function assert;
use function defined;
use Directory_Iterator;
use function is_file;
use Php_Unit\Runner\Version;
use function rsort;
use function sprintf;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Schema_Finder
{
    /**
     * @return non-empty-list<non-empty-string>
     */
    public function available(): array
    {
        $result = [Version::series()];
        foreach (new Directory_Iterator($this->path() . 'schema') as $file) {
            if ($file->is_dot()) {
                continue;
            }
            $version = $file->get_basename('.xsd');
            assert($version !== '');
            $result[] = $version;
        }
        rsort($result);
        return $result;
    }
    /**
     * @throws CannotFindSchemaException
     */
    public function find(string $version): string
    {
        if ($version === Version::series()) {
            $filename = $this->path() . 'phpunit.xsd';
        } else {
            $filename = $this->path() . 'schema/' . $version . '.xsd';
        }
        if (!is_file($filename)) {
            throw new Cannot_Find_Schema_Exception(sprintf('Schema for PHPUnit %s is not available', $version));
        }
        return $filename;
    }
    private function path(): string
    {
        if (defined('__PHPUNIT_PHAR_ROOT__')) {
            return __PHPUNIT_PHAR_ROOT__ . '/';
        }
        return __DIR__ . '/../../../../';
    }
}