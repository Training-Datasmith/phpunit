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
namespace Php_Unit\Text_Ui\Configuration;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @immutable
 */
final readonly class Php
{
    public function __construct(private Directory_Collection $include_paths, private Ini_Setting_Collection $ini_settings, private Constant_Collection $constants, private Variable_Collection $global_variables, private Variable_Collection $env_variables, private Variable_Collection $post_variables, private Variable_Collection $get_variables, private Variable_Collection $cookie_variables, private Variable_Collection $server_variables, private Variable_Collection $files_variables, private Variable_Collection $request_variables)
    {
    }
    public function include_paths(): Directory_Collection
    {
        return $this->include_paths;
    }
    public function ini_settings(): Ini_Setting_Collection
    {
        return $this->ini_settings;
    }
    public function constants(): Constant_Collection
    {
        return $this->constants;
    }
    public function global_variables(): Variable_Collection
    {
        return $this->global_variables;
    }
    public function env_variables(): Variable_Collection
    {
        return $this->env_variables;
    }
    public function post_variables(): Variable_Collection
    {
        return $this->post_variables;
    }
    public function get_variables(): Variable_Collection
    {
        return $this->get_variables;
    }
    public function cookie_variables(): Variable_Collection
    {
        return $this->cookie_variables;
    }
    public function server_variables(): Variable_Collection
    {
        return $this->server_variables;
    }
    public function files_variables(): Variable_Collection
    {
        return $this->files_variables;
    }
    public function request_variables(): Variable_Collection
    {
        return $this->request_variables;
    }
}