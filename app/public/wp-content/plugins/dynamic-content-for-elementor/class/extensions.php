<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor;

use Elementor\Controls_Manager;
if (!\defined('ABSPATH')) {
    exit;
}
class Extensions
{
    public function __construct()
    {
        $this->load_extensions();
        add_action('elementor_pro/init', function () {
            do_action('dce/register_form_actions');
        });
    }
    public function add_form_action($extension)
    {
        $priority = 10;
        if (isset($extension->action_priority)) {
            $priority = $extension->action_priority;
        }
        add_action('dce/register_form_actions', function () use($extension) {
            $module = \ElementorPro\Plugin::instance()->modules_manager->get_modules('forms');
            $module->actions_registrar->register($extension);
        }, $priority);
    }
    public function load_extensions()
    {
        $features = \DynamicContentForElementor\Plugin::instance()->features;
        $active_extensions = $features->filter(['type' => 'extension', 'status' => 'active']);
        foreach ($active_extensions as $extension_info) {
            $class = '\\DynamicContentForElementor\\' . $extension_info['class'];
            if (\DynamicContentForElementor\Helper::check_plugin_dependencies(\false, $extension_info['plugin_depends']) && (!isset($extension_info['minimum_php']) || \version_compare(\phpversion(), $extension_info['minimum_php'], '>='))) {
                $extension = new $class($extension_info);
                if (\method_exists($extension, 'run_once')) {
                    $extension->run_once();
                }
                if (isset($extension->has_action) && $extension->has_action) {
                    $this->add_form_action($extension);
                }
                \DynamicContentForElementor\Assets::add_depends($extension);
            }
        }
    }
}
