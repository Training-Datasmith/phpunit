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
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Move_Attributes_From_Root_To_Coverage implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $map = ['disableCodeCoverageIgnore' => 'disableCodeCoverageIgnore', 'ignoreDeprecatedCodeUnitsFromCodeCoverage' => 'ignoreDeprecatedCodeUnits'];
        $root = $document->document_element;
        assert($root instanceof Dom_Element);
        $coverage = $document->get_elements_by_tag_name('coverage')->item(0);
        if (!$coverage instanceof Dom_Element) {
            throw new Migration_Exception('Unexpected state - No coverage element');
        }
        foreach ($map as $old => $new) {
            if (!$root->has_attribute($old)) {
                continue;
            }
            $coverage->set_attribute($new, $root->get_attribute($old));
            $root->remove_attribute($old);
        }
    }
}