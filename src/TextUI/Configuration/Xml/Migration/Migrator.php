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
use Php_Unit\Runner\Version;
use Php_Unit\Util\Xml\Loader as XmlLoader;
use Php_Unit\Util\Xml\Xml_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Migrator
{
    /**
     * @throws Exception
     * @throws MigrationException
     * @throws XmlException
     */
    public function migrate(string $filename): string
    {
        $origin = (new Schema_Detector())->detect($filename);
        if (!$origin->detected()) {
            throw new Exception('The file does not validate against any known schema');
        }
        if ($origin->version() === Version::series()) {
            throw new Exception('The file does not need to be migrated');
        }
        $configuration_document = (new Xml_Loader())->load_file($filename);
        foreach ((new Migration_Builder())->build($origin->version()) as $migration) {
            $migration->migrate($configuration_document);
        }
        $configuration_document->format_output = true;
        $configuration_document->preserve_white_space = false;
        $xml = $configuration_document->save_xml();
        assert($xml !== false);
        return $xml;
    }
}