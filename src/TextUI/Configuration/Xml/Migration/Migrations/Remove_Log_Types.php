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
final readonly class Remove_Log_Types implements Migration
{
    public function migrate(Dom_Document $document): void
    {
        $logging = $document->get_elements_by_tag_name('logging')->item(0);
        if (!$logging instanceof Dom_Element) {
            return;
        }
        foreach (Snapshot_Node_List::from_node_list($logging->get_elements_by_tag_name('log')) as $log_node) {
            assert($log_node instanceof Dom_Element);
            switch ($log_node->get_attribute('type')) {
                case 'json':
                case 'tap':
                    $logging->remove_child($log_node);
            }
        }
    }
}