<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Typography;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class ParentChildMenu extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public function get_style_depends()
    {
        return ['dce-list'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_content', ['label' => $this->get_title()]);
        $this->add_control('parentpage_select', ['label' => esc_html__('Parent Page', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Post Title', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'condition' => ['dynamic_parentchild' => '']]);
        $this->add_control('menu_style', ['label' => esc_html__('Style', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['horizontal' => esc_html__('Horizontal', 'dynamic-content-for-elementor'), 'vertical' => esc_html__('Vertical', 'dynamic-content-for-elementor')], 'default' => 'vertical', 'separator' => 'after']);
        $this->add_control('dynamic_parentchild', ['label' => esc_html__('Dynamic page parent/child', 'dynamic-content-for-elementor'), 'description' => esc_html__('Change depending on the page that displays it', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '']);
        $this->add_control('orderby', ['label' => esc_html__('Order By', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_post_orderby_options(), 'default' => 'menu_order']);
        $this->add_control('order', ['label' => esc_html__('Order', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['ASC' => esc_html__('Ascending', 'dynamic-content-for-elementor'), 'DESC' => esc_html__('Descending', 'dynamic-content-for-elementor')], 'default' => 'ASC']);
        $this->add_control('use_second_level', ['label' => esc_html__('Use second level', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('exclude_io', ['label' => esc_html__('Exclude myself', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('no_siblings', ['label' => esc_html__('Hide Siblings', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['dynamic_parentchild!' => '']]);
        $this->add_control('only_children', ['label' => esc_html__('Only children', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['no_siblings!' => '']]);
        $this->add_control('show_parent', ['label' => esc_html__('Parent Page Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_on' => esc_html__('Show', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('Hide', 'dynamic-content-for-elementor'), 'default' => 'yes']);
        $this->add_control('title_html_tag', ['label' => esc_html__('Title HTML Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_html_tags(), 'default' => 'h4', 'condition' => ['show_parent!' => '']]);
        $this->add_control('show_childlist', ['label' => esc_html__('Child List', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_on' => esc_html__('Show', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('Hide', 'dynamic-content-for-elementor'), 'default' => 'yes']);
        $this->add_control('blockwidth_enable', ['label' => esc_html__('Force Block width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['show_border' => '2']]);
        $this->add_control('menu_width', ['label' => esc_html__('Box Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'size_units' => ['px', '%'], 'range' => ['px' => ['min' => 0, 'max' => 800], '%' => ['min' => 0, 'max' => 100]], 'condition' => ['blockwidth_enable' => 'yes'], 'selectors' => ['{{WRAPPER}} .dce-menu .box' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('show_separators', ['label' => esc_html__('Show Separator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['solid' => ['title' => esc_html__('Yes', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-check'], 'none' => ['title' => esc_html__('No', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-ban']], 'toggle' => \true, 'default' => 'solid', 'selectors' => ['{{WRAPPER}} .dce-menu.horizontal li' => 'border-left-style: {{VALUE}};'], 'condition' => ['menu_style' => 'horizontal']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style', ['label' => esc_html__('Style', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('menu_align', ['label' => esc_html__('Text Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'default' => 'left', 'selectors' => ['{{WRAPPER}}' => 'text-align: {{VALUE}};']]);
        $this->add_control('heading_colors', ['label' => esc_html__('List items', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['show_childlist!' => '']]);
        $this->add_control('menu_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['show_childlist!' => ''], 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu a' => 'color: {{VALUE}};']]);
        $this->add_control('menu_color_hover', ['label' => esc_html__('Text Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['show_childlist!' => ''], 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu a:hover' => 'color: {{VALUE}};']]);
        $this->add_control('menu_color_active', ['label' => esc_html__('Text Active Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['show_childlist!' => ''], 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu ul li a.active' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_list', 'selector' => '{{WRAPPER}} .dce-menu ul.first-level li', 'condition' => ['show_childlist!' => '']]);
        $this->add_control('heading_level_2', ['label' => esc_html__('Level 2', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes']]);
        $this->add_control('menu_color_2', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu ul.second-level a' => 'color: {{VALUE}};'], 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes']]);
        $this->add_control('menu_color_hover_2', ['label' => esc_html__('Text Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes'], 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu ul.second-level a:hover' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_list_2', 'selector' => '{{WRAPPER}} .dce-menu ul.second-level li', 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes']]);
        $this->add_control('heading_spaces_menu', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['show_childlist!' => '']]);
        $this->add_control('menu_space', ['label' => esc_html__('Header Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-menu .dce-parent-title' => 'margin-bottom: calc( {{SIZE}}{{UNIT}} / 2);', '{{WRAPPER}} .dce-menu hr' => 'margin-bottom: calc( {{SIZE}}{{UNIT}} / 2);', '{{WRAPPER}} .dce-menu div.box' => 'padding: {{SIZE}}{{UNIT}};'], 'condition' => ['show_childlist!' => '']]);
        $this->add_control('menu_list_space', ['label' => esc_html__('List Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-menu ul.first-level > li' => 'margin-bottom: {{SIZE}}{{UNIT}};'], 'condition' => ['show_childlist!' => '']]);
        $this->add_control('menu_indent', ['label' => esc_html__('Indent', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}}.border-1 .dce-menu ul.first-level > li, {{WRAPPER}} .dce-menu.horizontal li' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};', '{{WRAPPER}}.border-2 .dce-menu ul.first-level' => 'padding: {{SIZE}}{{UNIT}};'], 'condition' => ['show_childlist!' => '']]);
        $this->add_control('heading_spaces_menu_2', ['label' => esc_html__('Space of level 2', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes']]);
        $this->add_control('menu_2_list_space', ['label' => esc_html__('List Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-menu ul.second-level > li' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};'], 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes']]);
        $this->add_control('menu_2_indent', ['label' => esc_html__('Indent', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-menu ul.second-level > li' => 'padding-left: {{SIZE}}{{UNIT}}; padding-right: {{SIZE}}{{UNIT}};'], 'condition' => ['show_childlist!' => '', 'use_second_level' => 'yes']]);
        $this->add_control('heading_title', ['label' => esc_html__('Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['show_parent!' => '']]);
        $this->add_control('menu_title_color', ['label' => esc_html__('Title Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['show_parent!' => ''], 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu .dce-parent-title a' => 'color: {{VALUE}};']]);
        $this->add_control('menu_title_color_hover', ['label' => esc_html__('Title Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-menu .dce-parent-title a:hover' => 'color: {{VALUE}};'], 'condition' => ['show_parent!' => '']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_tit', 'selector' => '{{WRAPPER}} .dce-menu .dce-parent-title', 'condition' => ['show_parent!' => '']]);
        $this->add_control('heading_border', ['label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('show_border', ['label' => esc_html__('Show Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'toggle' => \false, 'label_block' => \false, 'options' => ['1' => esc_html__('Yes', 'dynamic-content-for-elementor'), '0' => esc_html__('No', 'dynamic-content-for-elementor'), '2' => esc_html__('Any', 'dynamic-content-for-elementor')], 'prefix_class' => 'border-', 'render_type' => 'template', 'default' => '1']);
        $this->add_control('menu_border_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'condition' => ['show_border!' => '0'], 'selectors' => ['{{WRAPPER}} .dce-menu hr' => 'border-color: {{VALUE}};', '{{WRAPPER}} .dce-menu.horizontal li' => 'border-left-color: {{VALUE}};', '{{WRAPPER}} .dce-menu .box' => 'border-color: {{VALUE}};']]);
        $this->add_control('menu_border_size', ['label' => esc_html__('Weight', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 20]], 'selectors' => ['{{WRAPPER}} .dce-menu hr, {{WRAPPER}} .dce-menu .box' => 'border-width: {{SIZE}}{{UNIT}};'], 'condition' => ['show_border' => ['1', '2']]]);
        $this->add_control('menu_border_width', ['label' => esc_html__('Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px', '%'], 'range' => ['px' => ['min' => 1, 'max' => 1000], '%' => ['min' => 1, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-menu hr, {{WRAPPER}} .dce-menu .box' => 'width: {{SIZE}}{{UNIT}};'], 'condition' => ['show_border' => ['1']]]);
        $this->add_control('heading_separator', ['label' => esc_html__('Separator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['show_separators' => 'solid', 'menu_style' => 'horizontal']]);
        $this->add_control('menu_color_separator', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'condition' => ['show_separators' => 'solid', 'menu_style' => 'horizontal'], 'selectors' => ['{{WRAPPER}} .dce-menu.horizontal li' => 'border-left-color: {{VALUE}};']]);
        $this->add_responsive_control('menu_size_separator', ['label' => esc_html__('Weight', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0, 'max' => 50]], 'selectors' => ['{{WRAPPER}} .dce-menu.horizontal li' => 'border-left-width: {{SIZE}}{{UNIT}};'], 'condition' => ['show_separators' => 'solid', 'menu_style' => 'horizontal']]);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $id_page = get_queried_object_id();
        if (!$settings['dynamic_parentchild']) {
            $id_page = $settings['parentpage_select'];
        } else {
            $parent_page = wp_get_post_parent_id($id_page);
            if ($parent_page) {
                if ($settings['use_second_level']) {
                    $ancestors = get_post_ancestors($id_page);
                    $root = \count($ancestors) - 1;
                    $parentroot = $ancestors[$root];
                    if ($parent_page == $parentroot) {
                        if ($this->has_children($id_page)) {
                            $id_page = $id_page;
                        } else {
                            $id_page = $parent_page;
                        }
                    } else {
                        $id_page = $parent_page;
                    }
                } else {
                    $id_page = wp_get_post_parent_id($id_page);
                }
            }
        }
        $exclude_io = array();
        if (is_singular()) {
            if ($settings['exclude_io']) {
                $exclude_io = array($id_page);
            }
        } elseif (is_home() || is_archive()) {
            $exclude_io = array();
        }
        $args = array('posts_per_page' => -1, 'orderby' => $settings['orderby'], 'order' => $settings['order'], 'exclude' => $exclude_io, 'post_type' => 'any', 'post_parent' => $id_page, 'post_status' => 'publish', 'suppress_filters' => \false);
        $children = get_posts($args);
        $styleMenu = $settings['menu_style'];
        $clssStyleMenu = $styleMenu;
        echo '<nav class="dce-menu ' . $clssStyleMenu . '">';
        if ($settings['show_border'] == 2) {
            echo '<div class="box">';
        }
        if ($settings['show_parent']) {
            $html_tag = Helper::validate_html_tag($settings['title_html_tag']);
            echo '<' . $html_tag . ' class="dce-parent-title"><a href="' . get_permalink($id_page) . '">' . wp_kses_post(get_the_title($id_page)) . '</a></' . $html_tag . '>';
            if ($settings['show_border']) {
                echo '<hr />';
            }
        }
        if ($settings['show_childlist']) {
            echo '<ul class="first-level">';
            foreach ($children as $page) {
                if (get_the_ID() == $page->ID) {
                    $linkActive = ' class="active"';
                } else {
                    $linkActive = '';
                }
                if (!$settings['exclude_io'] || $page->ID != $id_page) {
                    if ($linkActive || !$settings['no_siblings']) {
                        echo '<li class="item-' . $page->ID . '">';
                        if (!$settings['only_children']) {
                            echo '<a href="' . get_permalink($page->ID) . '"' . $linkActive . '>' . wp_kses_post($page->post_title) . '</a>';
                        }
                        if ($settings['use_second_level']) {
                            $args2 = array('posts_per_page' => -1, 'orderby' => $settings['orderby'], 'order' => $settings['order'], 'exclude' => $exclude_io, 'post_type' => 'any', 'post_parent' => $page->ID, 'post_status' => 'publish', 'suppress_filters' => \false);
                            $children2 = get_posts($args2);
                            if (\count($children2) > 0) {
                                echo '<ul class="second-level">';
                                foreach ($children2 as $page2) {
                                    if (get_the_ID() == $page2->ID) {
                                        $linkActive = ' class="active"';
                                    } else {
                                        $linkActive = '';
                                    }
                                    echo '<li class="item-' . $page2->ID . '"><a href="' . get_permalink($page2->ID) . '"' . $linkActive . '>' . wp_kses_post($page2->post_title) . '</a></li>';
                                }
                                echo '</ul>';
                            }
                        }
                        if ($linkActive || !$settings['no_siblings']) {
                            echo '</li>';
                        }
                    }
                }
            }
            echo '</ul>';
        }
        if ($settings['show_border'] == 2) {
            echo '</div>';
        }
        echo '</nav>';
    }
    protected function has_children($post_id)
    {
        $children = get_pages("child_of={$post_id}");
        if (\count($children) != 0) {
            return \true;
        } else {
            return \false;
        }
        // No children
    }
}
