<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class InlineAlign extends \DynamicContentForElementor\Extensions\ExtensionPrototype
{
    private $is_common = \false;
    public $has_action = \false;
    /**
     * Get Name
     *
     * Return the action name
     *
     * @access public
     * @return string
     */
    public function get_name()
    {
        return 'dce_form_inline_align';
    }
    /**
     * Get Label
     *
     * Returns the action label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return esc_html__('Inline align', 'dynamic-content-for-elementor');
    }
    /**
     * Add Actions
     *
     * @since 0.5.5
     *
     * @access private
     */
    protected function add_actions()
    {
        add_action('elementor/widget/render_content', array($this, '_render_form'), 10, 2);
        add_action('elementor/element/form/section_form_fields/before_section_end', [$this, 'update_fields_controls']);
        add_action('elementor/widget/print_template', function ($template, $widget) {
            if ('form' === $widget->get_name()) {
                $template = \false;
            }
            return $template;
        }, 10, 2);
    }
    public function _render_form($content, $widget)
    {
        if ($widget->get_name() == 'form') {
            $settings = $widget->get_settings_for_display();
            $add_css = $add_js = '';
            $has_js = \false;
            foreach ($settings['form_fields'] as $key => $afield) {
                if ($afield['field_type'] == 'radio' || $afield['field_type'] == 'checkbox') {
                    if (!empty($afield['inline_align'])) {
                        $has_js = \true;
                        $add_js .= "jQuery('.elementor-field-group-" . $afield['custom_id'] . "').addClass('elementor-repeater-item-" . $afield['_id'] . "');";
                        $add_css .= '.elementor-field-group-' . $afield['custom_id'] . '.elementor-repeater-item-' . $afield['_id'] . ' .elementor-subgroup-inline{width: 100%; justify-content: ' . $afield['inline_align'] . ';}';
                    }
                }
            }
            if ($has_js) {
                $add_js = '<script>jQuery(function(){' . $add_js . '});</script>';
                $add_css = '<style>' . $add_css . '</style>';
                $add_js = \DynamicContentForElementor\Assets::dce_enqueue_script($this->get_name() . '-' . $widget->get_id() . '-inline', $add_js);
                $add_css = \DynamicContentForElementor\Assets::dce_enqueue_style($this->get_name() . '-' . $widget->get_id() . '-inline', $add_css);
                return $content . $add_js . $add_css;
            }
        }
        return $content;
    }
    public function update_fields_controls($widget)
    {
        $elementor = \ElementorPro\Plugin::elementor();
        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
        if (is_wp_error($control_data)) {
            return;
        }
        $field_controls = ['inline_align' => ['name' => 'inline_align', 'label' => esc_html__('Inline align', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'separator' => 'before', 'options' => ['flex-start' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-center'], 'flex-end' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-right'], 'space-around' => ['title' => esc_html__('Around', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-justify'], 'space-evenly' => ['title' => esc_html__('Evenly', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-justify'], 'space-between' => ['title' => esc_html__('Between', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-justify']], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .elementor-subgroup-inline' => 'width: 100%; justify-content: {{VALUE}};'], 'render_type' => 'ui', 'condition' => ['field_type' => ['checkbox', 'radio'], 'inline_list!' => ''], 'tabs_wrapper' => 'form_fields_tabs', 'inner_tab' => 'form_fields_enchanted_tab', 'tab' => 'enchanted']];
        $control_data['fields'] = \array_merge($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }
}
