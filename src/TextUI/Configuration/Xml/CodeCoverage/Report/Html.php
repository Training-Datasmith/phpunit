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
namespace Php_Unit\Text_Ui\Xml_Configuration\Code_Coverage\Report;

use Php_Unit\Text_Ui\Configuration\Directory;
use Php_Unit\Text_Ui\Configuration\No_Custom_Css_File_Exception;
use Php_Unit\Text_Ui\Configuration\No_Html_Coverage_Target_Exception;
/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Html
{
    public function __construct(private ?Directory $target, private int $low_upper_bound, private int $high_lower_bound, private string $color_success_low, private string $color_success_low_dark, private string $color_success_medium, private string $color_success_medium_dark, private string $color_success_high, private string $color_success_high_dark, private string $color_success_bar, private string $color_success_bar_dark, private string $color_warning, private string $color_warning_dark, private string $color_warning_bar, private string $color_warning_bar_dark, private string $color_danger, private string $color_danger_dark, private string $color_danger_bar, private string $color_danger_bar_dark, private string $color_breadcrumbs, private string $color_breadcrumbs_dark, private ?string $custom_css_file)
    {
    }
    /**
     * @phpstan-assert-if-true !null $this->target
     */
    public function has_target(): bool
    {
        return $this->target !== null;
    }
    /**
     * @throws NoHtmlCoverageTargetException
     */
    public function target(): Directory
    {
        if (!$this->has_target()) {
            throw new No_Html_Coverage_Target_Exception();
        }
        return $this->target;
    }
    public function low_upper_bound(): int
    {
        return $this->low_upper_bound;
    }
    public function high_lower_bound(): int
    {
        return $this->high_lower_bound;
    }
    public function color_success_low(): string
    {
        return $this->color_success_low;
    }
    public function color_success_low_dark(): string
    {
        return $this->color_success_low_dark;
    }
    public function color_success_medium(): string
    {
        return $this->color_success_medium;
    }
    public function color_success_medium_dark(): string
    {
        return $this->color_success_medium_dark;
    }
    public function color_success_high(): string
    {
        return $this->color_success_high;
    }
    public function color_success_high_dark(): string
    {
        return $this->color_success_high_dark;
    }
    public function color_success_bar(): string
    {
        return $this->color_success_bar;
    }
    public function color_success_bar_dark(): string
    {
        return $this->color_success_bar_dark;
    }
    public function color_warning(): string
    {
        return $this->color_warning;
    }
    public function color_warning_dark(): string
    {
        return $this->color_warning_dark;
    }
    public function color_warning_bar(): string
    {
        return $this->color_warning_bar;
    }
    public function color_warning_bar_dark(): string
    {
        return $this->color_warning_bar_dark;
    }
    public function color_danger(): string
    {
        return $this->color_danger;
    }
    public function color_danger_dark(): string
    {
        return $this->color_danger_dark;
    }
    public function color_danger_bar(): string
    {
        return $this->color_danger_bar;
    }
    public function color_danger_bar_dark(): string
    {
        return $this->color_danger_bar_dark;
    }
    public function color_breadcrumbs(): string
    {
        return $this->color_breadcrumbs;
    }
    public function color_breadcrumbs_dark(): string
    {
        return $this->color_breadcrumbs_dark;
    }
    /**
     * @phpstan-assert-if-true !null $this->customCssFile
     */
    public function has_custom_css_file(): bool
    {
        return $this->custom_css_file !== null;
    }
    /**
     * @throws NoCustomCssFileException
     */
    public function custom_css_file(): string
    {
        if (!$this->has_custom_css_file()) {
            throw new No_Custom_Css_File_Exception();
        }
        return $this->custom_css_file;
    }
}