<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
use DynamicContentForElementor\Helper;
class Custom extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    const CUSTOM_PHP_CONTROL_NAME = 'dce_visibility_custom_condition_php';
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        if (!\defined('DCE_PATH')) {
            //  Feature not available in FREE version
            $element->add_control('dce_visibility_custom_hide', ['raw' => esc_html__('Feature available only in Dynamic.ooo - Dynamic Content for Elementor, paid version.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::RAW_HTML, 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning']);
        } elseif (Helper::can_register_unsafe_controls()) {
            $element->add_control(self::CUSTOM_PHP_CONTROL_NAME, ['label' => esc_html__('Custom PHP condition', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CODE, 'language' => 'php', 'default' => '', 'description' => esc_html__('Type here a function that returns a boolean value. You can use all WP variables and functions.', 'dynamic-content-for-elementor')]);
        }
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
        if (!isset($settings['dce_visibility_custom_condition']) || !$settings['dce_visibility_custom_condition']) {
            if (isset($settings[self::CUSTOM_PHP_CONTROL_NAME]) && \preg_match('/\\S/', $settings[self::CUSTOM_PHP_CONTROL_NAME])) {
                $triggers['custom'] = esc_html__('Custom Condition', 'dynamic-content-for-elementor');
                $customhidden = $this->check_custom_condition($settings);
                ++$triggers_n;
                if ($customhidden) {
                    $conditions['custom'] = esc_html__('Custom Condition', 'dynamic-content-for-elementor');
                }
            }
        }
    }
    /**
     * @param array<string,mixed> $settings
     * @return boolean
     */
    protected function check_custom_condition($settings)
    {
        if (!Helper::can_register_unsafe_controls()) {
            return \false;
        }
        $php_code = $settings[self::CUSTOM_PHP_CONTROL_NAME];
        if (current_user_can('manage_options') && !empty($_GET['dce_disable_visibility_custom_conditions'])) {
            echo esc_html__('Dynamic Visibility: Custom Condition found, but custom conditions are disabled.', 'dynamic-content-for-elementor');
            return \false;
        }
        if (\is_string($php_code)) {
            try {
                return eval($php_code);
            } catch (\ParseError $e) {
                echo '<pre>Dynamic Visibility - Custom Condition: ', $e->getMessage(), '</pre>';
            } catch (\Throwable $e) {
                if (current_user_can('administrator')) {
                    Helper::notice(\false, esc_html__('This message is visible only for Administrators', 'dynamic-content-for-elementor'));
                    echo '<pre>Dynamic Visibility - Custom Condition: ', $e->getMessage(), '</pre>';
                }
            }
        }
        return \false;
    }
}
