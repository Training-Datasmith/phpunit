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
use Dom_Document;
use Dom_Element;
use Php_Unit\Runner\Version;
use function str_contains;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Update_Schema_Location implements Migration
{
    private const string NAMESPACE_URI = 'http://www.w3.org/2001/XMLSchema-instance';
    private const string LOCAL_NAME_SCHEMA_LOCATION = 'noNamespaceSchemaLocation';
    public function migrate(Dom_Document $document): void
    {
        $root = $document->document_element;
        assert($root instanceof Dom_Element);
        $existing_schema_location = $root->get_attribute_ns(self::NAMESPACE_URI, self::LOCAL_NAME_SCHEMA_LOCATION);
        if (str_contains($existing_schema_location, '://') === false) {
            // If the current schema location is a relative path, don't update it
            return;
        }
        $root->set_attribute_ns(self::NAMESPACE_URI, 'xsi:' . self::LOCAL_NAME_SCHEMA_LOCATION, 'https://schema.phpunit.de/' . Version::series() . '/phpunit.xsd');
    }
}