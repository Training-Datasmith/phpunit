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

use Dom_Document;
use Dom_Element;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Move_Attributes_From_Filter_Whitelist_To_Coverage implements Migration
{
    /**
     * @throws MigrationException
     */
    public function migrate(Dom_Document $document): void
    {
        $whitelist = $document->get_elements_by_tag_name('whitelist')->item(0);
        if ($whitelist === null) {
            return;
        }
        $coverage = $document->get_elements_by_tag_name('coverage')->item(0);
        if (!$coverage instanceof Dom_Element) {
            throw new Migration_Exception('Unexpected state - No coverage element');
        }
        $map = ['addUncoveredFilesFromWhitelist' => 'includeUncoveredFiles', 'processUncoveredFilesFromWhitelist' => 'processUncoveredFiles'];
        foreach ($map as $old => $new) {
            if (!$whitelist->has_attribute($old)) {
                continue;
            }
            $coverage->set_attribute($new, $whitelist->get_attribute($old));
            $whitelist->remove_attribute($old);
        }
    }
}