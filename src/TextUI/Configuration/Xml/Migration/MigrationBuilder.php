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

use function version_compare;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Migration_Builder
{
    /**
     * @var non-empty-array<non-empty-string, non-empty-list<class-string>>
     */
    private const array AVAILABLE_MIGRATIONS = ['8.5' => [Remove_Log_Types::class], '9.2' => [Remove_Cache_Tokens_Attribute::class, Introduce_Coverage_Element::class, Move_Attributes_From_Root_To_Coverage::class, Move_Attributes_From_Filter_Whitelist_To_Coverage::class, Move_Whitelist_Includes_To_Coverage::class, Move_Whitelist_Excludes_To_Coverage::class, Remove_Empty_Filter::class, Coverage_Clover_To_Report::class, Coverage_Crap4j_To_Report::class, Coverage_Html_To_Report::class, Coverage_Php_To_Report::class, Coverage_Text_To_Report::class, Coverage_Xml_To_Report::class, Convert_Log_Types::class], '9.5' => [Remove_Listeners::class, Remove_Test_Suite_Loader_Attributes::class, Remove_Cache_Result_File_Attribute::class, Remove_Coverage_Element_Cache_Directory_Attribute::class, Remove_Coverage_Element_Process_Uncovered_Files_Attribute::class, Introduce_Cache_Directory_Attribute::class, Rename_Backup_Static_Attributes_Attribute::class, Remove_Be_Strict_About_Resource_Usage_During_Small_Tests_Attribute::class, Remove_Be_Strict_About_Todo_Annotated_Tests_Attribute::class, Remove_Printer_Attributes::class, Remove_Verbose_Attribute::class, Rename_Force_Covers_Annotation_Attribute::class, Rename_Be_Strict_About_Covers_Annotation_Attribute::class, Remove_Conversion_To_Exceptions_Attributes::class, Remove_No_Interaction_Attribute::class, Remove_Logging_Elements::class, Remove_Test_Dox_Groups_Element::class], '10.0' => [Move_Coverage_Directories_To_Source::class], '10.4' => [Remove_Be_Strict_About_Todo_Annotated_Tests_Attribute::class], '10.5' => [Remove_Register_Mock_Objects_From_Test_Arguments_Recursively_Attribute::class], '11.0' => [Replace_Restrict_Deprecations_With_Ignore_Deprecations::class], '11.1' => [Remove_Cache_Result_File_Attribute::class, Remove_Coverage_Element_Cache_Directory_Attribute::class], '11.2' => [Remove_Be_Strict_About_Todo_Annotated_Tests_Attribute::class]];
    /**
     * @return non-empty-list<Migration>
     */
    public function build(string $from_version): array
    {
        $stack = [new Update_Schema_Location()];
        foreach (self::AVAILABLE_MIGRATIONS as $version => $migrations) {
            if (version_compare($version, $from_version, '<')) {
                continue;
            }
            foreach ($migrations as $migration) {
                $stack[] = new $migration();
            }
        }
        return $stack;
    }
}