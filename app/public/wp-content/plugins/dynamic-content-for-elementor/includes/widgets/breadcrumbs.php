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
class Breadcrumbs extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public function get_style_depends()
    {
        return ['dce-breadcrumbs'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_options', ['label' => esc_html__('Options', 'dynamic-content-for-elementor')]);
        if (!\function_exists('yoast_breadcrumb') || !$this->is_yoast_breadcrumbs()) {
            $this->add_control('enable_home_text', ['label' => esc_html__('Show Home', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
            $this->add_control('home-text', ['label' => esc_html__('Custom Home Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Homepage', 'dynamic-content-for-elementor'), 'condition' => ['enable_home_text' => 'yes']]);
            $this->add_control('separator', ['label' => esc_html__('Separator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => ' > ']);
        } else {
            $this->add_control('yoast_bc_alert', ['raw' => esc_html__('Breadcrumbs Yoast SEO', 'dynamic-content-for-elementor') . ' ' . \sprintf('<a href="%s" target="_blank">%s</a>', admin_url('admin.php?page=wpseo_titles#top#breadcrumbs'), esc_html__('Go settings Panel', 'dynamic-content-for-elementor')), 'type' => Controls_Manager::RAW_HTML, 'content_classes' => '']);
        }
        $this->add_responsive_control('align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right'], 'justify' => ['title' => esc_html__('Justified', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-justify']], 'default' => '', 'selectors' => ['{{WRAPPER}}' => 'text-align: {{VALUE}};']]);
        $this->add_responsive_control('space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 5, 'unit' => 'px'], 'tablet_default' => ['unit' => 'px'], 'mobile_default' => ['unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-separator, {{WRAPPER}} a, {{WRAPPER}} a + span' => 'padding: 0 {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_items', ['label' => esc_html__('Items', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} li, {{WRAPPER}} a' => 'color: {{VALUE}};']]);
        $this->add_control('color_hover', ['label' => esc_html__('Text Color Hover', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} a:hover' => 'color: {{VALUE}};']]);
        $this->add_control('final_color', ['label' => esc_html__('Current Item Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} li.item-current' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-breadcrumbs']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_final', 'label' => esc_html__('Current Item Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .item-current']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_home', ['label' => esc_html__('Home', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['enable_home_text' => 'yes']]);
        $this->add_control('home_color', ['label' => esc_html__('Home Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} li.item-home' => 'color: {{VALUE}};', '{{WRAPPER}} li.item-home a' => 'color: {{VALUE}};'], 'condition' => ['enable_home_text' => 'yes']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_hometext', 'label' => esc_html__('Home Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} li.item-home']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_separator', ['label' => esc_html__('Separator', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('separator_color', ['label' => esc_html__('Separator Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-separator span' => 'color: {{VALUE}};']]);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $separator = isset($settings['separator']) ? '<span class="dce-separator">' . wp_kses_post($settings['separator']) . '</span>' : '';
        $home_title = isset($settings['home-text']) ? wp_kses_post($settings['home-text']) : '';
        $id_page = Helper::get_the_id();
        if (\function_exists('yoast_breadcrumb') && $this->is_yoast_breadcrumbs()) {
            \yoast_breadcrumb('<div>', '</div>');
        } else {
            // Get the query & post information
            global $post, $wp_query;
            $category = get_the_category($id_page);
            // Build the breadcrumbs
            echo '<ul>';
            // Do not display on the homepage
            if (!is_front_page()) {
                if ($settings['enable_home_text']) {
                    // Home
                    echo '<li class="item-home"><a class="bread-link bread-home" href="' . get_home_url() . '" title="' . $home_title . '">' . $home_title . '</a></li>';
                    echo '<li class="separator separator-home"> ' . $separator . ' </li>';
                }
                if (is_single()) {
                    // Single post (Only display the first category)
                    if (\count($category) > 0) {
                        echo '<li class="item-cat item-cat-' . \intval($category[0]->term_id) . ' item-cat-' . esc_attr($category[0]->category_nicename) . '"><a class="bread-cat bread-cat-' . \intval($category[0]->term_id) . ' bread-cat-' . esc_attr($category[0]->category_nicename) . '" href="' . get_category_link(\intval($category[0]->term_id)) . '" title="' . $category[0]->cat_name . '">' . $category[0]->cat_name . '</a></li>';
                    }
                    if (\count($category) > 0) {
                        echo '<li class="separator separator-' . \intval($category[0]->term_id) . '"> ' . $separator . ' </li>';
                    }
                    echo '<li class="item-current item-' . $id_page . '"><span class="bread-current bread-' . $id_page . '" title="' . wp_kses_post(get_the_title()) . '">' . wp_kses_post(get_the_title()) . '</span></li>';
                } elseif (is_category()) {
                    // Category page
                    echo '<li class="item-current item-cat-' . $category[0]->term_id . ' item-cat-' . $category[0]->category_nicename . '"><span class="bread-current bread-cat-' . $category[0]->term_id . ' bread-cat-' . $category[0]->category_nicename . '">' . $category[0]->cat_name . '</span></li>';
                } elseif (is_tax()) {
                    // Custom Taxonomy
                    echo '<li class="item-current item-cat-' . get_queried_object()->term_id . ' item-cat-' . get_queried_object()->slug . '"><span class="bread-current bread-cat-' . get_queried_object()->term_id . ' bread-cat-' . get_queried_object()->slug . '">' . get_queried_object()->name . '</span></li>';
                } elseif (is_page() || get_post_type($id_page) === 'page') {
                    // Standard page
                    if ($post->post_parent) {
                        // If child page, get parents
                        $anc = get_post_ancestors($id_page);
                        // Get parents in the right order
                        $anc = \array_reverse($anc);
                        $parents = '';
                        // Parent page loop
                        foreach ($anc as $ancestor) {
                            $parents .= '<li class="item-parent item-parent-' . $ancestor . '"><a class="bread-parent bread-parent-' . $ancestor . '" href="' . get_permalink($ancestor) . '" title="' . wp_kses_post(get_the_title($ancestor)) . '">' . wp_kses_post(get_the_title($ancestor)) . '</a></li>';
                            $parents .= '<li class="separator separator-' . $ancestor . '"> ' . $separator . ' </li>';
                        }
                        // Display parent pages
                        echo $parents;
                        // Current page
                        echo '<li class="item-current item-' . $id_page . '"><span title="' . wp_kses_post(get_the_title()) . '"> ' . wp_kses_post(get_the_title()) . '</span></li>';
                    } else {
                        // Just display current page if not parents
                        echo '<li class="item-current item-' . $id_page . '"><span class="bread-current bread-' . $id_page . '"> ' . wp_kses_post(get_the_title()) . '</span></li>';
                    }
                } elseif (is_tag()) {
                    // Tag page
                    // Get tag information
                    $term_id = get_query_var('tag_id');
                    $taxonomy = 'post_tag';
                    $args = 'include=' . $term_id;
                    $terms = get_terms($taxonomy, $args);
                    // Display the tag name
                    echo '<li class="item-current item-tag-' . $terms[0]->term_id . ' item-tag-' . $terms[0]->slug . '"><span class="bread-current bread-tag-' . $terms[0]->term_id . ' bread-tag-' . $terms[0]->slug . '">' . $terms[0]->name . '</span></li>';
                } elseif (is_day()) {
                    // Day archive
                    // Year link
                    echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link(get_the_time('Y')) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
                    echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
                    // Month link
                    echo '<li class="item-month item-month-' . get_the_time('m') . '"><a class="bread-month bread-month-' . get_the_time('m') . '" href="' . get_month_link(get_the_time('Y'), get_the_time('m')) . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</a></li>';
                    echo '<li class="separator separator-' . get_the_time('m') . '"> ' . $separator . ' </li>';
                    // Day display
                    echo '<li class="item-current item-' . get_the_time('j') . '"><span class="bread-current bread-' . get_the_time('j') . '"> ' . get_the_time('jS') . ' ' . get_the_time('M') . ' Archives</span></li>';
                } elseif (is_month()) {
                    // Month Archive
                    // Year link
                    echo '<li class="item-year item-year-' . get_the_time('Y') . '"><a class="bread-year bread-year-' . get_the_time('Y') . '" href="' . get_year_link(get_the_time('Y')) . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</a></li>';
                    echo '<li class="separator separator-' . get_the_time('Y') . '"> ' . $separator . ' </li>';
                    // Month display
                    echo '<li class="item-month item-month-' . get_the_time('m') . '"><span class="bread-month bread-month-' . get_the_time('m') . '" title="' . get_the_time('M') . '">' . get_the_time('M') . ' Archives</span></li>';
                } elseif (is_year()) {
                    // Display year archive
                    echo '<li class="item-current item-current-' . get_the_time('Y') . '"><span class="bread-current bread-current-' . get_the_time('Y') . '" title="' . get_the_time('Y') . '">' . get_the_time('Y') . ' Archives</span></li>';
                } elseif (is_author()) {
                    // Auhor archive
                    // Get the author information
                    global $author;
                    $userdata = get_userdata($author);
                    // Display author name
                    echo '<li class="item-current item-current-' . esc_attr($userdata->user_nicename) . '"><span class="bread-current bread-current-' . esc_attr($userdata->user_nicename) . '" title="' . esc_attr($userdata->display_name) . '">' . esc_html__('Author', 'dynamic-content-for-elementor') . esc_html($userdata->display_name) . '</span></li>';
                } elseif (get_query_var('paged')) {
                    // Paginated archives
                    echo '<li class="item-current item-current-' . get_query_var('paged') . '"><span class="bread-current bread-current-' . get_query_var('paged') . '" title="' . esc_html__('Page', 'dynamic-content-for-elementor') . ' ' . get_query_var('paged') . '">' . esc_html__('Page', 'dynamic-content-for-elementor') . ' ' . get_query_var('paged') . '</span></li>';
                } elseif (is_search()) {
                    // Search results page
                    echo '<li class="item-current item-current-' . esc_attr(get_search_query()) . '"><span class="bread-current bread-current-' . esc_attr(get_search_query()) . '" title="' . esc_attr__('Search results for:', 'dynamic-content-for-elementor') . ' ' . esc_attr(get_search_query()) . '">' . esc_html__('Search results for:', 'dynamic-content-for-elementor') . ' ' . esc_html(get_search_query()) . '</span></li>';
                } elseif (is_404()) {
                    // 404 page
                    echo '<li>' . esc_html__('Error 404', 'dynamic-content-for-elementor') . '/li>';
                }
            }
            echo '</ul>';
        }
    }
    private function is_yoast_breadcrumbs()
    {
        $breadcrumbs_yoast = current_theme_supports('yoast-seo-breadcrumbs');
        if (!$breadcrumbs_yoast) {
            $options_yoast = get_option('wpseo_titles');
            if (empty($options_yoast)) {
                $options_yoast = get_option('wpseo_internallinks');
            }
            $breadcrumbs_yoast = \true === !empty($options_yoast['breadcrumbs-enable']);
        }
        return $breadcrumbs_yoast;
    }
}
