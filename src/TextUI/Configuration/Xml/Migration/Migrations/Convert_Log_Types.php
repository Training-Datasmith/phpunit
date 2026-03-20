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
final readonly class Convert_Log_Types implements Migration
{
    public function migrate(Dom_Document $document): void
    {
        $logging = $document->get_elements_by_tag_name('logging')->item(0);
        if (!$logging instanceof Dom_Element) {
            return;
        }
        $types = ['junit' => 'junit', 'teamcity' => 'teamcity', 'testdox-html' => 'testdoxHtml', 'testdox-text' => 'testdoxText', 'testdox-xml' => 'testdoxXml', 'plain' => 'text'];
        $log_nodes = [];
        foreach ($logging->get_elements_by_tag_name('log') as $log_node) {
            if (!isset($types[$log_node->get_attribute('type')])) {
                continue;
            }
            $log_nodes[] = $log_node;
        }
        foreach ($log_nodes as $old_node) {
            $new_log_node = $document->create_element($types[$old_node->get_attribute('type')]);
            $new_log_node->set_attribute('outputFile', $old_node->get_attribute('target'));
            $logging->replace_child($new_log_node, $old_node);
        }
    }
}