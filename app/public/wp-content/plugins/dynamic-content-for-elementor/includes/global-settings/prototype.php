<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Includes\Settings;

use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly.
}
class DCE_Settings_Prototype
{
    public static $name = 'Global Settings Prototype';
    public function get_name()
    {
        return 'dce_settings_prototype';
    }
    public static function get_satisfy_dependencies()
    {
        return \true;
    }
    public function get_css_wrapper_selector()
    {
        return 'body';
    }
    public static function get_controls()
    {
        return [];
    }
}
