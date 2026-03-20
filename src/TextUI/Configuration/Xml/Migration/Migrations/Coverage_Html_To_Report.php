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

use Dom_Element;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Coverage_Html_To_Report extends Log_To_Report_Migration
{
    protected function for_type(): string
    {
        return 'coverage-html';
    }
    protected function to_report_format(Dom_Element $log_node): Dom_Element
    {
        $html = $log_node->owner_document->create_element('html');
        $html->set_attribute('outputDirectory', $log_node->get_attribute('target'));
        $this->migrate_attributes($log_node, $html, ['lowUpperBound', 'highLowerBound']);
        return $html;
    }
}