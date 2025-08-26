<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly.
}
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use DynamicContentForElementor\Helper;
class AcfFrontend extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    /**
     * @return array<string>
     */
    public function get_script_depends()
    {
        return ['dce-google-maps-api'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_acf_frontend', ['label' => $this->get_title()]);
        $this->add_control('retrieve_from', ['label' => esc_html__('Retrieve the fields from', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'current_post', 'options' => ['current_post' => esc_html__('Current Post', 'dynamic-content-for-elementor'), 'other_post' => esc_html__('Other Post', 'dynamic-content-for-elementor'), 'new_post' => esc_html__('New Post', 'dynamic-content-for-elementor')]]);
        $this->add_control('retrieve_from_id', ['label' => esc_html__('Select Post', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Post Title', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'condition' => ['retrieve_from' => 'other_post']]);
        $this->add_control('new_post_type', ['label' => esc_html__('Post Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_public_post_types(), 'label_block' => \true, 'default' => 'page', 'condition' => ['retrieve_from' => ['new_post']]]);
        $this->add_control('new_post_status', ['label' => esc_html__('Post Status', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => \array_merge(['future' => esc_html__('Future', 'dynamic-content-for-elementor')], get_post_statuses()), 'label_block' => \true, 'default' => 'publish', 'condition' => ['retrieve_from' => ['new_post']]]);
        $this->end_controls_section();
        $this->start_controls_section('section_fields', ['label' => esc_html__('Fields to Show', 'dynamic-content-for-elementor')]);
        $this->add_control('all_fields', ['label' => esc_html__('Show All ACF Fields', 'dynamic-content-for-elementor'), 'default' => 'yes', 'frontend_available' => \true, 'type' => Controls_Manager::SWITCHER, 'condition' => ['retrieve_from!' => 'new_post']]);
        $this->add_control('type', ['label' => esc_html__('Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'fields', 'options' => ['fields' => esc_html__('ACF Fields', 'dynamic-content-for-elementor'), 'field_groups' => esc_html__('ACF Field Groups', 'dynamic-content-for-elementor')], 'conditions' => ['relation' => 'or', 'terms' => [['name' => 'all_fields', 'operator' => '===', 'value' => ''], ['name' => 'retrieve_from', 'operator' => '===', 'value' => 'new_post']]]]);
        $fields = new \Elementor\Repeater();
        $fields->add_control('field', ['label' => esc_html__('ACF Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Select the field...', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'acf', 'dynamic' => ['active' => \false]]);
        $this->add_control('fields_list', ['label' => esc_html__('ACF Fields', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::REPEATER, 'fields' => $fields->get_controls(), 'title_field' => '{{{ field }}}', 'conditions' => ['relation' => 'or', 'terms' => [['relation' => 'and', 'terms' => [['name' => 'all_fields', 'operator' => '===', 'value' => ''], ['name' => 'type', 'operator' => '===', 'value' => 'fields']]], ['relation' => 'and', 'terms' => [['name' => 'retrieve_from', 'operator' => '===', 'value' => 'new_post'], ['name' => 'type', 'operator' => '===', 'value' => 'fields']]]]]]);
        $group = new \Elementor\Repeater();
        $group->add_control('group', ['label' => esc_html__('ACF Field Group', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Select the field group...', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'acf_groups', 'dynamic' => ['active' => \false]]);
        $this->add_control('groups_list', ['label' => esc_html__('ACF Field Groups', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::REPEATER, 'fields' => $group->get_controls(), 'title_field' => '{{{ group }}}', 'conditions' => ['relation' => 'or', 'terms' => [['relation' => 'and', 'terms' => [['name' => 'all_fields', 'operator' => '===', 'value' => ''], ['name' => 'type', 'operator' => '===', 'value' => 'field_groups']]], ['relation' => 'and', 'terms' => [['name' => 'retrieve_from', 'operator' => '===', 'value' => 'new_post'], ['name' => 'type', 'operator' => '===', 'value' => 'field_groups']]]]]]);
        $this->add_control('post_title', ['label' => esc_html__('Post Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_on' => esc_html__('Show', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('Hide', 'dynamic-content-for-elementor'), 'default' => 'yes']);
        $this->add_control('post_content', ['label' => esc_html__('Post Content', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_on' => esc_html__('Show', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('Hide', 'dynamic-content-for-elementor'), 'default' => '', 'condition' => ['retrieve_from' => ['other_post', 'new_post']]]);
        $this->end_controls_section();
        $this->start_controls_section('submit', ['label' => esc_html__('Submit', 'dynamic-content-for-elementor')]);
        $this->add_control('submit_value', ['label' => esc_html__('Submit Value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Update', 'dynamic-content-for-elementor')]);
        $this->add_control('after_submit', ['label' => esc_html__('After Submit', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'message', 'options' => ['message' => esc_html__('Message', 'dynamic-content-for-elementor'), 'redirect' => esc_html__('Redirect', 'dynamic-content-for-elementor')]]);
        $this->add_control('updated_message', ['label' => esc_html__('Updated Message', 'dynamic-content-for-elementor'), 'label_block' => \true, 'type' => Controls_Manager::TEXT, 'condition' => ['after_submit' => 'message']]);
        $this->add_control('url_target', ['label' => esc_html__('Target', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'same', 'options' => ['same' => esc_html__('Same Page', 'dynamic-content-for-elementor'), 'another' => esc_html__('Another Page', 'dynamic-content-for-elementor')], 'condition' => ['after_submit' => 'redirect']]);
        $this->add_control('redirect', ['label' => esc_html__('Redirect To', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::URL, 'placeholder' => esc_html__('https://your-link.com', 'dynamic-content-for-elementor'), 'default' => ['url' => ''], 'condition' => ['after_submit' => 'redirect', 'url_target' => 'another']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style', ['label' => $this->get_title(), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('label_placement', ['label' => esc_html__('Label Placement', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'left', 'options' => ['left' => esc_html__('Left', 'dynamic-content-for-elementor'), 'top' => esc_html__('Top', 'dynamic-content-for-elementor')]]);
        $this->add_control('instruction_placement', ['label' => esc_html__('Instruction Placement', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'label', 'options' => ['label' => esc_html__('Label', 'dynamic-content-for-elementor'), 'field' => esc_html__('Field', 'dynamic-content-for-elementor')]]);
        $this->add_control('uploader', ['label' => esc_html__('Uploader', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'wp', 'options' => ['wp' => 'WP', 'basic' => 'Basic']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_labels', ['label' => esc_html__('Labels', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'labels_typography', 'selector' => '{{WRAPPER}} form.acf-form div.acf-label label']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'labels_text_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-label label']);
        $this->add_control('labels_text_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-label label' => 'fill: {{VALUE}}; color: {{VALUE}};']]);
        $this->add_control('labels_background_color', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-label label' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'labels_border_only', 'selector' => '{{WRAPPER}} form.acf-form div.acf-label label', 'separator' => 'before']);
        $this->add_control('labels_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['labels_border_only!' => ''], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-label label' => 'border-color: {{VALUE}};']]);
        $this->add_control('labels_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-label label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'labels_box_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-label']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_inner_labels', ['label' => esc_html__('Inner Labels', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'inner_labels_typography', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input label']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'inner_labels_text_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input label']);
        $this->add_control('inner_labels_text_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input label' => 'fill: {{VALUE}}; color: {{VALUE}};']]);
        $this->add_control('inner_labels_background_color', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input label' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'inner_labels_border_only', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input label', 'separator' => 'before']);
        $this->add_control('inner_labels_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['inner_labels_border_only!' => ''], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input label' => 'border-color: {{VALUE}};']]);
        $this->add_control('inner_labels_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'inner_labels_box_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_buttons', ['label' => esc_html__('Buttons', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'buttons_typography', 'selector' => '{{WRAPPER}} form.acf-form button']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'buttons_text_shadow', 'selector' => '{{WRAPPER}} form.acf-form button']);
        $this->add_control('buttons_text_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} form.acf-form button' => 'fill: {{VALUE}}; color: {{VALUE}};']]);
        $this->add_control('buttons_background_color', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} form.acf-form button' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'buttons_border_only', 'selector' => '{{WRAPPER}} form.acf-form button', 'separator' => 'before']);
        $this->add_control('buttons_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['buttons_border_only!' => ''], 'selectors' => ['{{WRAPPER}} form.acf-form button' => 'border-color: {{VALUE}};']]);
        $this->add_control('buttons_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} form.acf-form button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'buttons_box_shadow', 'selector' => '{{WRAPPER}} form.acf-form button']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_text_info', ['label' => esc_html__('Text info', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'text_info_typography', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input p']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'text_info_text_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input p']);
        $this->add_control('text_info_text_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input p' => 'fill: {{VALUE}}; color: {{VALUE}};']]);
        $this->add_control('text_info_background_color', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input p' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'text_info_border_only', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input p', 'separator' => 'before']);
        $this->add_control('text_info_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['text_info_border_only!' => ''], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input p' => 'border-color: {{VALUE}};']]);
        $this->add_control('text_info_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-input p' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'text_info_box_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-input p']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_update_button', ['label' => esc_html__('Update Button', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('update_button_align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-form-submit' => 'text-align: {{VALUE}};'], 'default' => is_rtl() ? 'right' : 'left']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'update_button_typography', 'selector' => '{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'update_button_text_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]']);
        $this->add_control('update_button_text_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]' => 'fill: {{VALUE}}; color: {{VALUE}};']]);
        $this->add_control('update_button_background_color', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'update_button_border_only', 'selector' => '{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]', 'separator' => 'before']);
        $this->add_control('update_button_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['update_button_border_only!' => ''], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]' => 'border-color: {{VALUE}};']]);
        $this->add_control('update_button_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'update_button_box_shadow', 'selector' => '{{WRAPPER}} form.acf-form div.acf-form-submit input[type="submit"]']);
        $this->end_controls_section();
    }
    /**
     * Safe Render
     *
     * @return void
     */
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $args = [
            'id' => $this->get_id(),
            // Widget ID
            'post_title' => !empty($settings['post_title']),
            'submit_value' => $settings['submit_value'],
            'label_placement' => $settings['label_placement'],
            'instruction_placement' => $settings['instruction_placement'],
            'uploader' => $settings['uploader'],
            'kses' => \true,
            'updated_message' => 'message' === $settings['after_submit'] ? $settings['updated_message'] : \false,
            'return' => $this->get_return_value($settings),
            'post_content' => \false,
        ];
        if ($settings['all_fields'] && 'new_post' !== $settings['retrieve_from']) {
            $args['fields'] = [];
            // An empty array means all fields
        } else {
            switch ($settings['type']) {
                case 'fields':
                    $args['fields'] = $this->get_fields_to_show($settings);
                    break;
                case 'field_groups':
                    $args['field_groups'] = $this->get_field_groups_to_show($settings);
                    break;
            }
        }
        switch ($settings['retrieve_from']) {
            case 'current_post':
                $args['post_id'] = get_the_ID();
                break;
            case 'other_post':
                $post_id = absint($settings['retrieve_from_id']);
                if (empty($post_id) || !get_post($post_id)) {
                    Helper::notice(\false, esc_html__('Please select the post', 'dynamic-content-for-elementor'), 'danger');
                    return;
                }
                $args['post_id'] = $post_id;
                $args['post_content'] = !empty($settings['post_content']);
                break;
            case 'new_post':
                $args['post_id'] = 'new_post';
                $args += ['new_post' => ['post_type' => $settings['new_post_type'], 'post_status' => $settings['new_post_status']]];
                $args['post_content'] = !empty($settings['post_content']);
                break;
        }
        if (!$this->has_fields_to_show($args)) {
            Helper::notice(\false, esc_html__('You have no fields to show', 'dynamic-content-for-elementor'), 'danger');
            return;
        }
        acf_form_head();
        acf_form($args);
    }
    /**
     * Get Return Value
     *
     * @param array<mixed> $settings
     * @return string|false
     */
    protected function get_return_value($settings)
    {
        if ('redirect' === $settings['after_submit']) {
            switch ($settings['url_target']) {
                case 'same':
                    return get_the_permalink();
                case 'another':
                    $url = $settings['redirect']['url'] ?? '';
                    if (!\filter_var($url, \FILTER_VALIDATE_URL)) {
                        return \false;
                    }
                    return esc_url($url);
            }
        }
        return \false;
    }
    /**
     * Get Fields to Show
     *
     * @param array<mixed> $settings
     * @return array<int,mixed>|array<null>|false
     */
    protected function get_field_groups_to_show($settings)
    {
        if (empty($settings['groups_list'])) {
            return [null];
        }
        // Get selected groups
        $selected_groups = [];
        foreach ($settings['groups_list'] as $value) {
            if (!empty($value['group'])) {
                $selected_groups[] = $value['group'];
            }
        }
        if (empty($selected_groups)) {
            return [null];
        }
        // Prepare visibility check parameters
        $args = [];
        switch ($settings['retrieve_from']) {
            case 'current_post':
                $post_id = get_the_ID();
                if ($post_id) {
                    $args['post_id'] = $post_id;
                }
                break;
            case 'other_post':
                $post_id = absint($settings['retrieve_from_id']);
                if ($post_id) {
                    $args['post_id'] = $post_id;
                }
                break;
            case 'new_post':
                $args['post_type'] = $settings['new_post_type'];
                break;
        }
        // Get visible groups
        $visible_groups = [];
        foreach ($selected_groups as $group_key) {
            $group = acf_get_field_group($group_key);
            if ($group && acf_get_field_group_visibility($group, $args)) {
                $visible_groups[] = $group_key;
            }
        }
        return !empty($visible_groups) ? $visible_groups : [null];
    }
    /**
     * Get Fields to Show
     *
     * @param array<mixed> $settings
     * @return array<int,mixed>|null
     */
    protected function get_fields_to_show($settings)
    {
        if (empty($settings['fields_list'])) {
            return [null];
        }
        $fields = [];
        foreach ($settings['fields_list'] as $value) {
            if (!empty($value['field'])) {
                $fields[] = $value['field'];
            }
        }
        return !empty($fields) ? $fields : [null];
    }
    /**
     * Checks if there are fields or field groups to show
     *
     * @param array<string,mixed> $args Arguments for displaying the form.
     * @return boolean True if there are fields or field groups to show, false otherwise.
     */
    protected function has_fields_to_show($args)
    {
        if ($args['post_title'] || $args['post_content']) {
            return \true;
        }
        if (isset($args['fields']) && [] === $args['fields']) {
            return \true;
        }
        if (isset($args['fields']) && !empty($args['fields']) && $args['fields'][0] !== null) {
            return \true;
        }
        if (isset($args['field_groups']) && \is_array($args['field_groups']) && !empty($args['field_groups'])) {
            return \true;
        }
        return \false;
    }
}
