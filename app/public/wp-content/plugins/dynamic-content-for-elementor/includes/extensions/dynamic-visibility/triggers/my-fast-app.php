<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
use DynamicContentForElementor\Helper;
class MyFastApp extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    /**
     * @return boolean
     */
    public function is_available()
    {
        return \defined('DCE_PATH') && Helper::is_myfastapp_active();
    }
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        $element->add_control('dce_visibility_myfastapp', ['label' => esc_html__('The visitor is', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['all' => esc_html__('on the site or in the app', 'dynamic-content-for-elementor'), 'site' => esc_html__('on the site', 'dynamic-content-for-elementor'), 'app' => esc_html__('in the app', 'dynamic-content-for-elementor')], 'default' => 'all']);
    }
    /**
     * @param array<string,mixed> $settings
     * @param array<string,mixed> &$triggers
     * @param array<string,mixed> &$conditions
     * @param int &$triggers_n
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function check_conditions($settings, &$triggers, &$conditions, &$triggers_n, $element)
    {
        if (isset($settings['dce_visibility_myfastapp']) && 'all' !== $settings['dce_visibility_myfastapp']) {
            $triggers['dce_visibility_myfastapp'] = 'My FastAPP';
            $headers = \getallheaders();
            $is_on_myfastapp = isset($headers['X-Appid']) || isset($_COOKIE['myfastapp-cli']);
            if ('app' === $settings['dce_visibility_myfastapp'] && $is_on_myfastapp || 'site' === $settings['dce_visibility_myfastapp'] && !$is_on_myfastapp) {
                $conditions['dce_visibility_myfastapp'] = 'My FastAPP';
            }
        }
    }
}
