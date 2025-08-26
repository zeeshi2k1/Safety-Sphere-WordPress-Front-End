<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Modules\DynamicTags\Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Modules\DynamicTags\Module;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class Php extends Tag
{
    public function get_name()
    {
        return 'dce-dynamic-tag-php';
    }
    public function get_title()
    {
        return esc_html__('PHP', 'dynamic-content-for-elementor');
    }
    public function get_group()
    {
        return 'dce';
    }
    public function get_categories()
    {
        return \DynamicContentForElementor\Helper::get_dynamic_tags_categories();
    }
    public function get_docs()
    {
        return 'https://www.dynamic.ooo/dynamic-content-for-elementor/features/dynamic-tag-php/';
    }
    protected function register_controls()
    {
        if (\DynamicContentForElementor\Helper::can_register_unsafe_controls()) {
            $this->register_controls_settings();
        } else {
            $this->register_controls_non_admin_notice();
        }
    }
    protected function register_controls_non_admin_notice()
    {
        $this->add_control('html_notice', ['type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('You will need administrator capabilities to edit this Dynamic Tag.', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning']);
    }
    protected function register_controls_settings()
    {
        $this->add_control('data', ['label' => esc_html__('Return as Data', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::SWITCHER, 'description' => esc_html__('Instead of echo you use return and the result can be any php value instead of only strings. The advanced section does not apply in this case.', 'dynamic-content-for-elementor')]);
        $this->add_control('custom_php', ['label' => esc_html__('Custom PHP', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CODE, 'language' => 'php', 'default' => 'echo "Hello World!";']);
    }
    /**
     * @param string $code
     * @param bool $echo_error
     * @return string
     */
    public function eval_php($code, $echo_error = \false)
    {
        if (!\DynamicContentForElementor\Helper::can_register_unsafe_controls()) {
            return '';
        }
        $error = \false;
        $result = null;
        try {
            $result = @eval($code);
        } catch (\ParseError $e) {
            $error = $e->getMessage();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
        if ($error && current_user_can('administrator')) {
            $msg = '<strong>';
            $msg .= esc_html__('Please check your PHP code', 'dynamic-content-for-elementor');
            $msg .= '</strong><br />';
            $msg .= esc_html__('ERROR', 'dynamic-content-for-elementor') . ': ' . $error . "\n";
            if ($echo_error) {
                echo $msg;
                return '';
            } else {
                return $msg;
            }
        } else {
            return $result;
        }
    }
    public function render()
    {
        $settings = $this->get_settings_for_display();
        $this->eval_php($settings['custom_php'] ?? '', \true);
    }
    /**
     * @param array<mixed> $options
     * @return string
     */
    public function get_content(array $options = [])
    {
        $settings = $this->get_settings_for_display();
        if ($settings['data'] === 'yes') {
            return $this->eval_php($settings['custom_php'] ?? '');
        } else {
            return parent::get_content();
        }
    }
}
