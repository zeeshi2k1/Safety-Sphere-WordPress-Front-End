<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Icons_Manager;
use Elementor\Utils;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
class Content extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public function run_once()
    {
        parent::run_once();
        $save_guard = \DynamicContentForElementor\Plugin::instance()->save_guard;
        $save_guard->register_unsafe_control($this->get_type(), 'other_post_source');
    }
    public static $remove_recursion_loop = [];
    public function get_script_depends()
    {
        return ['imagesloaded', 'dce-content-js'];
    }
    public function get_style_depends()
    {
        return ['dce-content'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $post_type_object = get_post_type_object(get_post_type());
        $this->start_controls_section('section_content', ['label' => esc_html__('Content', 'dynamic-content-for-elementor')]);
        $this->add_control('use_filters_content', ['label' => esc_html__('Use the content-filters', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes']);
        $this->add_control('use_content_limit', ['label' => esc_html__('Limit Content', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => ['use_filters_content' => '']]);
        $this->add_control('count_content_limit', ['label' => esc_html__('Number of characters', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => '15', 'condition' => ['use_content_limit' => 'yes', 'use_filters_content' => '']]);
        $this->add_control('use_content_autop', ['label' => esc_html__('Auto paragraph', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes']);
        $this->add_control('html_tag', ['label' => esc_html__('HTML Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_html_tags(), 'default' => 'div']);
        $this->add_responsive_control('align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right'], 'justify' => ['title' => esc_html__('Justified', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-justify']], 'default' => '', 'selectors' => ['{{WRAPPER}}' => 'text-align: {{VALUE}};']]);
        $this->add_control('link_to', ['label' => esc_html__('Link to', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'none', 'options' => ['none' => esc_html__('None', 'dynamic-content-for-elementor'), 'home' => esc_html__('Home URL', 'dynamic-content-for-elementor'), 'post' => esc_html__('Post URL', 'dynamic-content-for-elementor'), 'custom' => esc_html__('Custom URL', 'dynamic-content-for-elementor')]]);
        $this->add_control('link', ['label' => esc_html__('Link', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::URL, 'placeholder' => esc_html__('https://your-link.com', 'dynamic-content-for-elementor'), 'condition' => ['link_to' => 'custom'], 'default' => ['url' => ''], 'show_label' => \false]);
        $this->add_control('no_shortcode', ['label' => esc_html__('Remove Shortcodes', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $this->add_control('enable_unfold', ['label' => esc_html__('Unfold', 'dynamic-content-for-elementor'), 'description' => esc_html__('Limit the display of the content', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'frontend_available' => \true, 'separator' => 'before']);
        $this->add_responsive_control('height_content', ['label' => esc_html__('Height', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'frontend_available' => \true, 'default' => ['size' => 280], 'range' => ['px' => ['max' => 600, 'min' => 0, 'step' => 1]], 'selectors' => ['{{WRAPPER}} .dce-content.unfolded' => 'height: {{SIZE}}{{UNIT}};'], 'render_type' => 'template', 'condition' => ['enable_unfold' => 'yes']]);
        $this->add_control('unfold_icon', ['label' => esc_html__('Icon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'skin' => 'inline', 'default' => ['value' => 'fas fa-plus-circle', 'library' => 'solid'], 'label_block' => \false]);
        $this->end_controls_section();
        $this->start_controls_section('section_source', ['label' => esc_html__('Source', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('data_source', ['label' => esc_html__('Source', 'dynamic-content-for-elementor'), 'description' => esc_html__('Select the data source', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'label_on' => esc_html__('Same', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('other', 'dynamic-content-for-elementor'), 'return_value' => 'yes']);
        $this->add_control('other_post_source', ['label' => esc_html__('Select from other source post', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Post Title', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'condition' => ['data_source' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style', ['label' => esc_html__('Content', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-content, {{WRAPPER}} .dce-content a.dce-content-link' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography', 'selector' => '{{WRAPPER}} .dce-content']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'text_shadow', 'selector' => '{{WRAPPER}} .dce-content']);
        $this->add_responsive_control('space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['max' => 100, 'min' => 0, 'step' => 1]], 'selectors' => ['{{WRAPPER}} .dce-content' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_control('rollhover_heading', ['label' => esc_html__('Rollover', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['link_to!' => 'none']]);
        $this->add_control('hover_color', ['label' => esc_html__('Hover Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-content:hover' => 'color: {{VALUE}};'], 'condition' => ['link_to!' => 'none']]);
        $this->add_control('hover_animation', ['label' => esc_html__('Hover Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HOVER_ANIMATION, 'condition' => ['link_to!' => 'none']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_unfold', ['label' => esc_html__('Unfold', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['enable_unfold' => 'yes']]);
        $this->add_control('unfold_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .unfold-btn' => 'color: {{VALUE}};']]);
        $this->add_control('unfold_color_hover', ['label' => esc_html__('Rollover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .unfold-btn:hover' => 'color: {{VALUE}};']]);
        $this->add_responsive_control('unfold_size', ['label' => esc_html__('Icon Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'frontend_available' => \true, 'size_units' => ['px', 'em', 'rem', 'vw', 'custom'], 'default' => ['size' => 50, 'unit' => 'px'], 'range' => ['px' => ['max' => 600, 'min' => 0, 'step' => 1], 'em' => ['min' => 0, 'max' => 10], 'rem' => ['min' => 0, 'max' => 10]], 'selectors' => ['{{WRAPPER}} .unfold-btn' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('unfold_space', ['label' => esc_html__('Icon Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 15], 'range' => ['px' => ['max' => 100, 'min' => 0, 'step' => 1]], 'selectors' => ['{{WRAPPER}} .unfold-btn' => 'margin-top: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('unfold_alignment', ['label' => esc_html__('Icon Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \true, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'default' => 'center', 'selectors' => ['{{WRAPPER}} .unfold-btn' => 'text-align: {{VALUE}}; display: block;']]);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $demoPage = get_post_meta(get_the_ID(), 'demo_id', \true);
        $id_page = Helper::get_the_id($settings['other_post_source'] ?? \false);
        $type_page = get_post_type($id_page);
        $default_content = esc_html__('This is the text place holder for post content.', 'dynamic-content-for-elementor') . ' Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur ut risus id lectus hendrerit mattis. Nunc augue risus, dignissim vel nibh quis, gravida ultrices tortor. Nam volutpat nec est sed molestie. Mauris pellentesque diam in arcu bibendum convallis. Aenean non nisi et velit eleifend lobortis. Fusce lobortis tortor enim, eget elementum urna varius mollis. Vivamus imperdiet dignissim tincidunt. Praesent sit amet nulla lobortis, tempor ipsum id, feugiat felisss.';
        $target = !empty($settings['link']) && $settings['link']['is_external'] ? 'target="_blank"' : '';
        $animation_class = '';
        if ($type_page == 'elementor_library' && empty($demoPage)) {
            // The template page does not have a content so when it is displayed by the template it shows a fake text for clutter.
            $content = $default_content;
            if ($settings['use_content_limit']) {
                $charset = get_bloginfo('charset');
                $content = \mb_substr(wp_strip_all_tags($content), 0, $settings['count_content_limit'], $charset) . '&hellip;';
            }
            $html = \sprintf('<%1$s class="dce-content %2$s"><div class="dce-content-wrapper">', \DynamicContentForElementor\Helper::validate_html_tag($settings['html_tag']), $animation_class);
            $html .= $content;
            $html .= \sprintf('</div></%s>', \DynamicContentForElementor\Helper::validate_html_tag($settings['html_tag']));
        } else {
            // All other Taxonomies
            if (is_author() || is_post_type_archive()) {
                $content = get_the_archive_description();
            } elseif ($settings['use_filters_content']) {
                if (!empty(self::$remove_recursion_loop[$id_page])) {
                    return;
                }
                if (empty(self::$remove_recursion_loop[$id_page])) {
                    self::$remove_recursion_loop[$id_page] = 1;
                } else {
                    ++self::$remove_recursion_loop[$id_page];
                }
                $is_elementor = get_post_meta($id_page, '_elementor_edit_mode', \true);
                if ($is_elementor) {
                    $atts = ['id' => $id_page, 'post_id' => $id_page, 'inlinecss' => \Elementor\Plugin::$instance->editor->is_edit_mode()];
                    $template_system = \DynamicContentForElementor\Plugin::instance()->template_system;
                    $content = $template_system->build_elementor_template_special($atts);
                } else {
                    $post_wp = get_post($id_page);
                    $content = wp_kses_post($post_wp->post_content);
                    if ($type_page == 'elementor_library' && !$content) {
                        $content = $default_content;
                    }
                    $content = wpautop($content);
                    $template_system = \DynamicContentForElementor\TemplateSystem::$instance;
                    if ($template_system !== null) {
                        $template_system->remove_content_filter();
                    }
                    $content = apply_filters('the_content', $content);
                    if ($template_system !== null) {
                        $template_system->add_content_filter();
                    }
                }
            } else {
                $post = get_post($id_page);
                $content = wp_kses_post($post->post_content);
                if ($settings['use_content_autop']) {
                    $content = wpautop($content);
                }
                if ($settings['use_content_limit'] && '' !== $content) {
                    $charset = get_bloginfo('charset');
                    $content = \mb_substr(wp_strip_all_tags($content), 0, $settings['count_content_limit'], $charset) . '&hellip;';
                }
            }
            if (empty($content)) {
                return;
            }
            switch ($settings['link_to']) {
                case 'custom':
                    if (!empty($settings['link']['url'])) {
                        $link = esc_url($settings['link']['url']);
                    } else {
                        $link = \false;
                    }
                    break;
                case 'post':
                    $link = esc_url(get_the_permalink());
                    break;
                case 'home':
                    $link = esc_url(get_home_url());
                    break;
                case 'none':
                default:
                    $link = \false;
                    break;
            }
            $html = \sprintf('<%1$s class="dce-content %2$s"><div class="dce-content-wrapper">', \DynamicContentForElementor\Helper::validate_html_tag($settings['html_tag']), $animation_class);
            if ($link) {
                $html .= \sprintf('<a class="dce-content-link" href="%1$s" %2$s>%3$s</a>', $link, $target, $content);
            } else {
                $html .= $content;
            }
            $html .= \sprintf('</div></%s>', \DynamicContentForElementor\Helper::validate_html_tag($settings['html_tag']));
        }
        if ($settings['no_shortcode']) {
            $html = strip_shortcodes($html);
            $html = Helper::vc_strip_shortcodes($html);
        }
        echo do_shortcode($html);
        if ($settings['enable_unfold']) {
            Icons_Manager::render_icon($settings['unfold_icon'], ['class' => 'unfold-btn']);
        }
    }
}
