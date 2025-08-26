<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Icons_Manager;
use ElementorPro\Plugin;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class Icons extends \DynamicContentForElementor\Extensions\ExtensionPrototype
{
    private $is_common = \false;
    public $has_action = \false;
    public $depended_scripts = ['dce-icons-form'];
    public $depended_styles = ['dce-icons-form-style'];
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
        return 'dce_form_icons';
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
        return esc_html__('icons', 'dynamic-content-for-elementor');
    }
    public function _render_form($content, $widget)
    {
        if ($widget->get_name() == 'form') {
            $settings = $widget->get_settings_for_display();
        }
        return $content;
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
        add_action('elementor/element/form/section_form_fields/before_section_end', [$this, 'update_fields_controls']);
        add_action('elementor/element/form/section_steps_style/after_section_end', [$this, 'add_background_section']);
        add_filter('elementor_pro/forms/render/item', array($this, 'render_field'), 10, 3);
        add_action('elementor-pro/forms/pre_render', [$this, 'add_assets_depends'], 10, 2);
        add_action('elementor/preview/enqueue_scripts', [$this, 'add_preview_depends']);
        add_action('elementor/widget/print_template', function ($template, $widget) {
            if ('form' === $widget->get_name()) {
                $template = \false;
            }
            return $template;
        }, 10, 2);
    }
    public function add_preview_depends()
    {
        foreach ($this->depended_scripts as $script) {
            wp_enqueue_script($script);
        }
        foreach ($this->depended_styles as $style) {
            wp_enqueue_style($style);
        }
    }
    public function add_assets_depends($instance, $form)
    {
        foreach ($instance['form_fields'] as $field) {
            $position = $field['field_icon_position'] ?? '';
            if ('elementor-field' === $position || 'elementor-field-label' === $position) {
                foreach ($this->depended_scripts as $script) {
                    wp_enqueue_script($script);
                }
                foreach ($this->depended_styles as $style) {
                    wp_enqueue_style($style);
                }
                return;
            }
        }
    }
    public function render_field($item, $item_index, $form)
    {
        if (!empty($item['field_icon'])) {
            if ('elementor-field-label' === $item['field_icon_position']) {
                $label = Helper::get_icon($item['field_icon'], ['aria-hidden' => 'true', 'class' => 'label-icons dce-form-icon']) . ' ' . $item['field_label'];
                $form->add_render_attribute('label' . $item_index, 'dce-icon-render', $label);
            } elseif ('elementor-field' === $item['field_icon_position']) {
                $icon = Helper::get_icon($item['field_icon'], ['aria-hidden' => 'true', 'class' => 'input-icons dce-form-icon']);
                $form->add_render_attribute('input' . $item_index, 'dce-icon-render', $icon);
                $form->add_render_attribute('textarea' . $item_index, 'dce-icon-render', $icon);
                $form->add_render_attribute('select' . $item_index, 'dce-icon-render', $icon);
            }
        }
        return $item;
    }
    public function update_fields_controls($widget)
    {
        $elementor = Plugin::elementor();
        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
        if (is_wp_error($control_data)) {
            return;
        }
        $icons_activated = ['date', 'time', 'tel', 'text', 'email', 'textarea', 'number', 'url', 'password', 'dce_js_field', 'amount', 'select'];
        $field_controls = ['field_icon_position' => ['name' => 'field_icon_position', 'label' => esc_html__('Icon', 'dynamic-content-for-elementor'), 'separator' => 'before', 'type' => Controls_Manager::CHOOSE, 'options' => ['no-icon' => ['title' => esc_html__('No Icon', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-times'], 'elementor-field-label' => ['title' => esc_html__('On Label', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-tag'], 'elementor-field' => ['title' => esc_html__('On Input', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-field']], 'toggle' => \false, 'default' => 'no-icon', 'tabs_wrapper' => 'form_fields_tabs', 'inner_tab' => 'form_fields_enchanted_tab', 'tab' => 'enchanted', 'conditions' => ['terms' => [['name' => 'field_type', 'operator' => 'in', 'value' => $icons_activated]]]], 'field_icon' => ['name' => 'field_icon', 'label' => esc_html__('Select Icon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'tabs_wrapper' => 'form_fields_tabs', 'inner_tab' => 'form_fields_enchanted_tab', 'tab' => 'enchanted', 'label_block' => \true, 'conditions' => ['relation' => 'and', 'terms' => [['name' => 'field_type', 'operator' => 'in', 'value' => $icons_activated]], 'terms' => [['name' => 'field_icon_position', 'operator' => '!=', 'value' => ['no-icon']]]]]];
        $control_data['fields'] = \array_merge($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }
    public function add_background_section($widget)
    {
        $widget->start_controls_section('section_dce_field_icons_style', ['label' => '<span class="color-dce icon-dce-logo-dce pull-right ml-1"></span> ' . esc_html__('Icons', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $widget->add_control('label_icon_color', ['label' => esc_html__('Icon Color on Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .label-icons' => 'color: {{VALUE}}; fill: {{VALUE}}']]);
        $widget->add_control('field_icon_color', ['label' => esc_html__('Icon Color on Input', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-field-input-wrapper .input-icons' => 'color: {{VALUE}}; fill: {{VALUE}}']]);
        $widget->add_control('label_icon_size', ['label' => esc_html__('Size on Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['unit' => 'px', 'size' => ''], 'range' => ['px' => ['min' => 10, 'max' => 50, 'step' => 1]], 'frontend_available' => \true, 'render_type' => 'template', 'selectors' => ['{{WRAPPER}} .label-icons' => 'font-size: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}']]);
        $widget->add_control('field_icon_size', ['label' => esc_html__('Size on Input', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['unit' => 'px', 'size' => ''], 'range' => ['px' => ['min' => 10, 'max' => 50, 'step' => 1]], 'frontend_available' => \true, 'render_type' => 'template', 'selectors' => ['{{WRAPPER}} i.input-icons.dce-form-icon' => 'font-size: {{SIZE}}{{UNIT}};', '{{WRAPPER}} svg.input-icons.dce-form-icon' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};']]);
        $widget->end_controls_section();
    }
}
