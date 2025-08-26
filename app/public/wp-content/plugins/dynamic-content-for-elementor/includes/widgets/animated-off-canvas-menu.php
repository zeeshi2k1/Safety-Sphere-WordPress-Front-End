<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Repeater;
use Elementor\Controls_Manager;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Icons_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class AnimatedOffCanvasMenu extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    /**
     * @return array<string>
     */
    public function get_script_depends()
    {
        return ['dce-anime-lib', 'dce-animatedoffcanvasmenu-js'];
    }
    /**
     * @return array<string>
     */
    public function get_style_depends()
    {
        return ['elementor-icons', 'dce-animatedOffcanvasMenu'];
    }
    /**
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_animatedoffcanvasmenu_settings', ['label' => $this->get_title()]);
        $this->add_control('menu_animatedoffcanvasmenu', ['label' => esc_html__('Select menu', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_taxonomy_terms('nav_menu'), 'default' => '', 'label_block' => \true, 'render_type' => 'template']);
        $this->add_control('animatedoffcanvasmenu_depth', ['label' => esc_html__('Depth', 'dynamic-content-for-elementor'), 'description' => esc_html__('How many levels of the hierarchy are to be included. 0 means all.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 0, 'min' => 0, 'max' => 3, 'step' => 1, 'dynamic' => ['active' => \false]]);
        $this->add_responsive_control('hamburger_align', ['label' => esc_html__('Menu Icon Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'separator' => 'before', 'selectors' => ['{{WRAPPER}} .dce-button-wrapper' => 'text-align: {{VALUE}};'], 'default' => 'right']);
        $this->add_responsive_control('aocm_position', ['label' => esc_html__('Menu Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-left'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-right']], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-nav .dce-menu-aocm' => '{{VALUE}}: 0;', '#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close' => '{{VALUE}}: 0;'], 'default' => 'right', 'frontend_available' => \true]);
        $this->add_control('side_background', ['label' => esc_html__('Side Background', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_on' => esc_html__('Show', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('Hide', 'dynamic-content-for-elementor'), 'return_value' => 'show', 'default' => 'show', 'separator' => 'before', 'frontend_available' => 'true']);
        $this->add_control('dynamic_template_before_choice', ['label' => esc_html__('Template before menu', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '']);
        $this->add_control('dynamic_template_before', ['label' => esc_html__('Template', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Template Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'object_type' => 'elementor_library', 'condition' => ['dynamic_template_before_choice!' => '']]);
        $this->add_control('dynamic_template_after_choice', ['label' => esc_html__('Template after menu', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '']);
        $this->add_control('dynamic_template_after', ['label' => esc_html__('Template', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Template Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'object_type' => 'elementor_library', 'condition' => ['dynamic_template_after_choice!' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_animatedoffcanvasmenu_animations_time', ['label' => esc_html__('Animations Time', 'dynamic-content-for-elementor')]);
        $this->add_control('time_side_background_opening', ['label' => esc_html__('Side Background Opening (ms)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 400, 'unit' => 'ms'], 'size_units' => ['ms'], 'range' => ['ms' => ['min' => 0, 'max' => 3000, 'step' => 100]], 'render_type' => 'template', 'frontend_available' => \true, 'condition' => ['side_background!' => '']]);
        $this->add_control('time_menu_pane_opening', ['label' => esc_html__('Menu Pane Opening (ms)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 400, 'unit' => 'ms'], 'size_units' => ['ms'], 'range' => ['ms' => ['min' => 0, 'max' => 3000, 'step' => 100]], 'render_type' => 'template', 'frontend_available' => \true]);
        $this->add_control('time_menu_list_opening', ['label' => esc_html__('Menu List Opening (ms)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1000, 'unit' => 'ms'], 'size_units' => ['ms'], 'range' => ['ms' => ['min' => 0, 'max' => 3000, 'step' => 100]], 'render_type' => 'template', 'frontend_available' => \true]);
        $this->add_control('time_menu_list_stagger', ['label' => esc_html__('Delay between menu items (ms)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 500, 'unit' => 'ms'], 'size_units' => ['ms'], 'range' => ['ms' => ['min' => 0, 'max' => 3000, 'step' => 100]], 'render_type' => 'template', 'frontend_available' => \true]);
        $this->end_controls_section();
        $this->start_controls_section('section_animatedoffcanvasmenu_style', ['label' => $this->get_title(), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('animatedoffcanvasmenu_rate', ['label' => esc_html__('Menu Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 45, 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => 1, 'max' => 100, 'step' => 1]], 'render_type' => 'template', 'frontend_available' => \true]);
        $this->add_control('title_hamburger_Style', ['label' => esc_html__('Menu', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_responsive_control('animatedoffcanvasmenu_align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} #dce-ul-menu' => 'text-align: {{VALUE}};'], 'default' => '']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'animatedoffcanvasmenu_typography', 'label' => esc_html__('Typography items', 'dynamic-content-for-elementor'), 'selector' => '#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li a', 'separator' => 'before']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'animatedoffcanvasmenu_typography_subitems', 'label' => esc_html__('Typography sub-items', 'dynamic-content-for-elementor'), 'selector' => '#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li ul.sub-menu li a', 'separator' => 'before']);
        $this->add_responsive_control('animatedoffcanvasmenu_size_childindicator', ['label' => esc_html__('Children-indicator size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 100, 'step' => 1]], 'selectors' => ['#animatedoffcanvasmenu-{{ID}}  ul#dce-ul-menu li span.indicator-child' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_control('title_menu_colors', ['label' => esc_html__('Colors', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(Group_Control_Background::get_type(), ['name' => 'animatedoffcanvasmenu_background', 'label' => esc_html__('Background', 'dynamic-content-for-elementor'), 'types' => ['classic', 'gradient'], 'selector' => '#animatedoffcanvasmenu-{{ID}} .dce-nav .dce-menu-aocm']);
        $this->start_controls_tabs('menu_colors');
        $this->start_controls_tab('menu_colors_normal', ['label' => esc_html__('Normal', 'dynamic-content-for-elementor')]);
        $this->add_control('menu_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li a' => 'color: {{VALUE}};']]);
        $this->add_control('menu_indicator_color', ['label' => esc_html__('Indicator Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li .indicator-child' => 'color: {{VALUE}};']]);
        $this->add_control('menu_indicator_bgcolor', ['label' => esc_html__('Indicator Background', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li .indicator-child' => 'background-color: {{VALUE}};']]);
        $this->end_controls_tab();
        $this->start_controls_tab('menu_colors_hover', ['label' => esc_html__('Hover', 'dynamic-content-for-elementor')]);
        $this->add_control('menu_hover_color', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li a:hover' => 'color: {{VALUE}};']]);
        $this->add_control('menu_indicator_hover_color', ['label' => esc_html__('Indicator Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li .indicator-child:hover' => 'color: {{VALUE}};']]);
        $this->add_control('menu_indicatorbg_hover_color', ['label' => esc_html__('Indicator Background', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li .indicator-child:hover' => 'background-color: {{VALUE}};']]);
        $this->add_control('menu_hover_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['hamburger_border_border!' => ''], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-button-hamburger:hover' => 'border-color: {{VALUE}};']]);
        $this->end_controls_tab();
        $this->start_controls_tab('menu_colors_active', ['label' => esc_html__('Active', 'dynamic-content-for-elementor')]);
        $this->add_control('menu_active_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li.current-menu-item a' => 'color: {{VALUE}};']]);
        $this->add_control('menu_indicator_active_color', ['label' => esc_html__('Indicator Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li.current-menu-item .indicator-child' => 'color: {{VALUE}};']]);
        $this->add_control('menu_indicatorbg_active_color', ['label' => esc_html__('Indicator Background', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li.current-menu-item .indicator-child' => 'background-color: {{VALUE}};']]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control('title_hamburger_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_responsive_control('animatedoffcanvasmenu_padding', ['label' => esc_html__('Padding', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => ''], 'size_units' => ['px', 'em', '%'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-nav-menu' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('animatedoffcanvasmenu_itemspace', ['label' => esc_html__('Menu Items', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 100, 'step' => 1]], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('animatedoffcanvasmenu_subitemspace', ['label' => esc_html__('Menu Sub-Items', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 100, 'step' => 1]], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li ul.sub-menu li' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('animatedoffcanvasmenu_indicatorpace', ['label' => esc_html__('Children Indicator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 100, 'step' => 1]], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} ul#dce-ul-menu li .indicator-child' => 'margin-left: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        $this->start_controls_section('section_animatedoffcanvasmenu_hamburger', ['label' => esc_html__('Menu Icon', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('hamburger_style', ['label' => esc_html__('Menu Icon Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'bars', 'options' => ['bars' => esc_html__('Bars', 'dynamic-content-for-elementor'), 'custom_icon' => esc_html__('Custom Icon', 'dynamic-content-for-elementor'), 'barsround' => esc_html__('Bars Round', 'dynamic-content-for-elementor'), 'dots' => esc_html__('Dots', 'dynamic-content-for-elementor'), 'grid9' => esc_html__('Grid 9', 'dynamic-content-for-elementor'), 'grid4' => esc_html__('Grid 4', 'dynamic-content-for-elementor'), 'plus' => esc_html__('Plus', 'dynamic-content-for-elementor'), 'arrow' => esc_html__('Arrow', 'dynamic-content-for-elementor'), 'wave' => esc_html__('Wave', 'dynamic-content-for-elementor'), 'circlebar' => esc_html__('Circle2Bar', 'dynamic-content-for-elementor')]]);
        $this->add_control('custom_icon_image', ['label' => esc_html__('Custom icon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'condition' => ['hamburger_style' => ['custom_icon']], 'default' => ['value' => 'fas fa-arrow-right', 'library' => 'solid']]);
        $this->add_control('hamburger_wave_style', ['label' => esc_html__('Wave style', 'dynamic-content-for-elementor'), 'separator' => 'after', 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 5, 'step' => 1, 'condition' => ['hamburger_style' => ['wave']]]);
        $this->add_responsive_control('hamburger_svg_size', ['label' => esc_html__('Menu Icon Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'separator' => 'before', 'default' => ['size' => '40', 'unit' => 'px'], 'size_units' => ['px', '%'], 'range' => ['px' => ['min' => 1, 'max' => 180, 'step' => 1], '%' => ['min' => 1, 'max' => 100, 'step' => 1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger' => 'width: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}};']]);
        $svgSelector = '{{WRAPPER}} #dce_hamburger g line, {{WRAPPER}} #dce_hamburger g circle, {{WRAPPER}} #dce_hamburger g path, {{WRAPPER}} #dce_hamburger g polygon, {{WRAPPER}} #dce_hamburger g rect';
        $svgSelectorHover = '{{WRAPPER}} #dce_hamburger:hover g line, {{WRAPPER}} #dce_hamburger:hover g circle, {{WRAPPER}} #dce_hamburger:hover g path, {{WRAPPER}} #dce_hamburger:hover g polygon, {{WRAPPER}} #dce_hamburger:hover g rect';
        $this->add_control('title_hamburger_rolloverstyle', ['label' => esc_html__('Style', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->start_controls_tabs('hamburger_colors');
        $this->start_controls_tab('hamburger_style_normal', ['label' => esc_html__('Normal', 'dynamic-content-for-elementor')]);
        $this->add_control('hamburger_force_fill', ['label' => esc_html__('Force Fill Color', 'dynamic-content-for-elementor'), 'description' => esc_html__('When switchen on this will fill try to fill all parts of the icon. Altro ty this if the above setting has no effect', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $this->add_control('hamburger_svg_fill', ['label' => esc_html__('Fill Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#000000', 'selectors' => [$svgSelector => 'fill: {{VALUE}};', '{{WRAPPER}} #dce_hamburger' => 'color: {{VALUE}}'], 'condition' => ['hamburger_force_fill' => '']]);
        $this->add_control('hamburger_svg_fill_forced', ['label' => esc_html__('Fill Color (Force)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#000000', 'selectors' => ['#dce_hamburger' => 'fill: {{VALUE}};', '#dce_hamburger' => 'color: {{VALUE}};'], 'condition' => ['hamburger_force_fill' => 'yes']]);
        $this->add_control('hamburger_svg_strokelines', ['label' => esc_html__('Stroke Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#000000', 'selectors' => ['{{WRAPPER}} #dce_hamburger g line, {{WRAPPER}} #dce_hamburger g path, {{WRAPPER}} #dce_hamburger g polygon' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'wave', 'arrow', 'plus', 'circlebar']]]);
        $this->add_responsive_control('hamburger_svg_strokewidth', ['label' => esc_html__('Stroke Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 3, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 20, 'step' => 0.1]], 'selectors' => [$svgSelector => 'stroke-width: {{SIZE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'wave', 'arrow', 'plus', 'circlebar']]]);
        $this->add_control('hamburger_item2', ['label' => esc_html__('Item 2', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Item 2', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']]]);
        $this->start_popover();
        $this->add_control('hamburger_svg_strokeitem2', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} #dce_hamburger g:nth-child(2) line' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->add_responsive_control('hamburger_svg_item2size', ['label' => esc_html__('Scale', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 2, 'step' => 0.01]], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']], 'selectors' => ['{{WRAPPER}} #dce_hamburger.bars g:nth-child(2)' => 'transform: scaleX({{SIZE}});', '{{WRAPPER}} #dce_hamburger.dots g:nth-child(2)' => 'transform: scale({{SIZE}});']]);
        $this->add_responsive_control('hamburger_svg_item2position', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \false, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-right']], 'default' => 'center', 'selectors' => ['{{WRAPPER}} #dce_hamburger g:nth-child(2)' => 'transform-origin: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->end_popover();
        $this->add_control('hamburger_item3', ['label' => esc_html__('Item 3', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Item 3', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']]]);
        $this->start_popover();
        $this->add_control('hamburger_svg_strokeitem3', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} #dce_hamburger g:nth-child(3) line' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->add_responsive_control('hamburger_svg_item3size', ['label' => esc_html__('Scale', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 2, 'step' => 0.01]], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']], 'selectors' => ['{{WRAPPER}} #dce_hamburger.bars g:nth-child(3)' => 'transform: scaleX({{SIZE}});', '{{WRAPPER}} #dce_hamburger.dots g:nth-child(3)' => 'transform: scale({{SIZE}});']]);
        $this->add_responsive_control('hamburger_svg_item3position', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \false, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-right']], 'default' => 'center', 'selectors' => ['{{WRAPPER}} #dce_hamburger g:nth-child(3)' => 'transform-origin: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->end_popover();
        $this->add_control('hamburger_svg_stroke_circle', ['label' => esc_html__('Circle Stroke Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#000000', 'separator' => 'before', 'selectors' => ['{{WRAPPER}} #dce_hamburger g circle, {{WRAPPER}} #dce_hamburger g rect, {{WRAPPER}} #dce_hamburger g path' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['dots', 'circlebar', 'grid9', 'grid4']]]);
        $this->add_responsive_control('hamburger_svg_strokewidth_circle', ['label' => esc_html__('Circle Stroke-Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0.5, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 20, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger g circle' => 'stroke-width: {{SIZE}};'], 'condition' => ['hamburger_style' => ['dots', 'circlebar', 'grid9', 'grid4']]]);
        $this->add_responsive_control('hamburger_svg_dotsize', ['label' => esc_html__('Dots Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 5, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 10, 'step' => 1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger g circle' => 'r: {{SIZE}}{{UNIT}};'], 'condition' => ['hamburger_style' => ['dots', 'grid9', 'grid4']]]);
        $this->add_responsive_control('hamburger_svg_circlesize', ['label' => esc_html__('Circle Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 37, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 10, 'max' => 38, 'step' => 1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger g circle' => 'r: {{SIZE}}{{UNIT}};'], 'condition' => ['hamburger_style' => ['circlebar']]]);
        $this->end_controls_tab();
        $this->start_controls_tab('hamburger_style_hover', ['label' => esc_html__('Hover', 'dynamic-content-for-elementor')]);
        $this->add_control('hamburger_svg_fill_hover', ['label' => esc_html__('Fill Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => [$svgSelectorHover => 'fill: {{VALUE}};', '{{WRAPPER}} #dce_hamburger:hover' => 'color: {{VALUE}}']]);
        $this->add_control('hamburger_svg_strokelines_hover', ['label' => esc_html__('Stroke Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g line, {{WRAPPER}} #dce_hamburger:hover g path, {{WRAPPER}} #dce_hamburger:hover g polygon' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'wave', 'arrow', 'plus', 'circlebar']]]);
        $this->add_responsive_control('hamburger_svg_strokewidth_hover', ['label' => esc_html__('Stroke-Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 20, 'step' => 0.1]], 'selectors' => [$svgSelectorHover => 'stroke-width: {{SIZE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'wave', 'arrow', 'plus', 'circlebar']]]);
        $this->add_control('hamburger_item2_hover', ['label' => esc_html__('Item 2', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Item 2', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']]]);
        $this->start_popover();
        $this->add_control('hamburger_svg_strokeitem2_hover', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g:nth-child(2) line' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars']]]);
        $this->add_responsive_control('hamburger_svg_item2size_hover', ['label' => esc_html__('Scale', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 1, 'step' => 0.01]], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']], 'selectors' => ['{{WRAPPER}} #dce_hamburger.bars:hover g:nth-child(2)' => 'transform: scaleX({{SIZE}});', '{{WRAPPER}} #dce_hamburger.dots:hover g:nth-child(2)' => 'transform: scale({{SIZE}});']]);
        $this->add_responsive_control('hamburger_svg_item2position_hover', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \false, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-right']], 'default' => 'center', 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g:nth-child(2)' => 'transform-origin: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->end_popover();
        $this->add_control('hamburger_item3_hover', ['label' => esc_html__('Item 3', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Item 3', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']]]);
        $this->start_popover();
        $this->add_control('hamburger_svg_strokeitem3_hover', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g:nth-child(3) line' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->add_responsive_control('hamburger_svg_item3size_hover', ['label' => esc_html__('Scale', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 1, 'step' => 0.01]], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'dots']], 'selectors' => ['{{WRAPPER}} #dce_hamburger.bars:hover g:nth-child(3)' => 'transform: scaleX({{SIZE}});', '{{WRAPPER}} #dce_hamburger.dots:hover g:nth-child(3)' => 'transform: scale({{SIZE}});']]);
        $this->add_responsive_control('hamburger_svg_item3position_hover', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \false, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-right']], 'default' => 'center', 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g:nth-child(3)' => 'transform-origin: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround']]]);
        $this->end_popover();
        $this->add_control('hamburger_svg_stroke_circle_hover', ['label' => esc_html__('Circle Stroke Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'separator' => 'before', 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g circle, {{WRAPPER}} #dce_hamburger:hover g rect, {{WRAPPER}} #dce_hamburger:hover g path' => 'stroke: {{VALUE}};'], 'condition' => ['hamburger_style' => ['dots', 'circlebar', 'grid9', 'grid4']]]);
        $this->add_responsive_control('hamburger_svg_strokewidth_circle_hover', ['label' => esc_html__('Circle Stroke-Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0.1, 'max' => 20, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g circle' => 'stroke-width: {{SIZE}};'], 'condition' => ['hamburger_style' => ['dots', 'circlebar', 'grid9', 'grid4']]]);
        $this->add_responsive_control('hamburger_svg_dotsize_hover', ['label' => esc_html__('Dots Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 10, 'step' => 1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g circle' => 'r: {{SIZE}}{{UNIT}};'], 'condition' => ['hamburger_style' => ['dots', 'grid9', 'grid4']]]);
        $this->add_responsive_control('hamburger_svg_circlesize_hover', ['label' => esc_html__('Circle Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 10, 'max' => 38, 'step' => 1]], 'selectors' => ['{{WRAPPER}} #dce_hamburger:hover g circle' => 'r: {{SIZE}}{{UNIT}};'], 'condition' => ['hamburger_style' => ['circlebar']]]);
        $this->add_control('hamburger_hover_border_color', ['label' => esc_html__('Border Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['hamburger_border_border!' => ''], 'selectors' => ['{{WRAPPER}} .dce-button-hamburger:hover' => 'border-color: {{VALUE}};']]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_control('hover_timingFunction', ['label' => esc_html__('Hover Animation Timing function', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_anim_timing_functions(), 'default' => 'ease-in-out', 'separator' => 'before', 'label_block' => \true, 'frontend_available' => \true, 'selectors' => ['{{WRAPPER}} #dce_hamburger g, {{WRAPPER}} #dce_hamburger g line, {{WRAPPER}} #dce_hamburger g circle, {{WRAPPER}} #dce_hamburger g path, {{WRAPPER}} #dce_hamburger g polygon, {{WRAPPER}} #dce_hamburger g rect' => 'animation-timing-function: {{VALUE}}; -webkit-animation-timing-function: {{VALUE}};']]);
        $this->add_control('hamburger_svg_strokelinecap', ['label' => esc_html__('Stroke Linecap', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'butt', 'separator' => 'before', 'options' => ['butt' => esc_html__('Butt', 'dynamic-content-for-elementor'), 'round' => esc_html__('Round', 'dynamic-content-for-elementor'), 'square' => esc_html__('Square', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} #dce_hamburger g line, {{WRAPPER}} #dce_hamburger g path, {{WRAPPER}} #dce_hamburger g polyline' => 'stroke-linecap: {{VALUE}};'], 'condition' => ['hamburger_style' => ['bars', 'barsround', 'wave', 'arrow', 'plus', 'circlebar']]]);
        $this->add_control('title_hamburger_background', ['label' => esc_html__('Menu Icon Wrapper Background', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_responsive_control('hamburger_padding', ['label' => esc_html__('Padding', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['{{WRAPPER}} .dce-button-hamburger' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Background::get_type(), ['name' => 'hamburger_background', 'label' => esc_html__('Background Overlay Color', 'dynamic-content-for-elementor'), 'types' => ['classic', 'gradient'], 'selector' => '{{WRAPPER}} .dce-button-hamburger']);
        $this->add_control('title_hamburger_border', ['label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'hamburger_border', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'placeholder' => '1px', 'default' => '1px', 'selector' => '{{WRAPPER}} .dce-button-hamburger']);
        $this->add_control('hamburger_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .dce-button-hamburger' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'hamburger_box_shadow', 'selector' => '{{WRAPPER}} .dce-button-hamburger']);
        $this->end_controls_section();
        $this->start_controls_section('section_animatedoffcanvasmenu_sideof', ['label' => esc_html__('Side Background', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['side_background!' => '']]);
        $this->add_group_control(Group_Control_Background::get_type(), ['name' => 'background_overlay', 'label' => esc_html__('Background Overlay Color', 'dynamic-content-for-elementor'), 'types' => ['classic', 'gradient'], 'selector' => '#animatedoffcanvasmenu-{{ID}} .dce-bg']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_close', ['label' => esc_html__('Close Button', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('enable_close_button', ['label' => esc_html__('Close Button', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('close_type', ['label' => esc_html__('Close type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['x' => ['title' => esc_html__('X', 'dynamic-content-for-elementor'), 'icon' => 'eicon-close'], 'icon' => ['title' => esc_html__('Icon', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-asterisk'], 'image' => ['title' => esc_html__('Image', 'dynamic-content-for-elementor'), 'icon' => 'eicon-image'], 'text' => ['title' => esc_html__('Text', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-italic']], 'toggle' => \false, 'default' => 'x', 'condition' => []]);
        $this->add_control('close_icon', ['label' => esc_html__('Close Icon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'label_block' => \true, 'default' => ['value' => 'fas fa-times', 'library' => 'solid'], 'condition' => ['close_type' => 'icon']]);
        $this->add_control('close_image', ['label' => esc_html__('Close Image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::MEDIA, 'default' => ['url' => ''], 'condition' => ['close_type' => 'image']]);
        $this->add_control('close_text', ['label' => esc_html__('Close Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Close', 'dynamic-content-for-elementor'), 'condition' => ['close_type' => 'text']]);
        $this->start_controls_tabs('close_colors');
        $this->start_controls_tab('close_colors_normal', ['label' => esc_html__('Normal', 'dynamic-content-for-elementor'), 'condition' => ['close_type!' => 'image']]);
        $this->add_control('close_icon_color', ['label' => esc_html__('Icon color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close' => 'color: {{VALUE}};'], 'condition' => ['close_type' => 'icon']]);
        $this->add_control('close_text_color', ['label' => esc_html__('Text color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close' => 'color: {{VALUE}};'], 'condition' => ['close_type' => 'text', 'close_text!' => '']]);
        $this->add_control('x_close_text_color', ['label' => esc_html__('Close color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:after, #animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:before' => 'background-color: {{VALUE}};'], 'condition' => ['close_type' => 'x']]);
        $this->add_control('close_bg_color', ['label' => esc_html__('Background color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close' => 'background-color: {{VALUE}};'], 'condition' => ['close_type!' => ['image', 'x']]]);
        $this->add_control('x_close_bg_color', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'condition' => ['close_type' => 'x'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'x_close_bg_border', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'selector' => '#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics', 'condition' => ['close_type' => 'x']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'close_bg_border', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'selector' => '#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close', 'condition' => ['close_type!' => 'x']]);
        $this->end_controls_tab();
        $this->start_controls_tab('close_colors_hover', ['label' => esc_html__('Hover', 'dynamic-content-for-elementor'), 'condition' => ['close_type!' => 'image']]);
        $this->add_control('close_icon_color_hover', ['label' => esc_html__('Icon color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close:hover' => 'color: {{VALUE}};'], 'condition' => ['close_type' => 'icon']]);
        $this->add_control('close_text_color_hover', ['label' => esc_html__('Text color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close:hover' => 'color: {{VALUE}};'], 'condition' => ['close_type' => 'text', 'close_text!' => '']]);
        $this->add_control('x_close_text_color_hover', ['label' => esc_html__('X color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close:hover .dce-quit-ics:after, #animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close:hover .dce-quit-ics:before' => 'background-color: {{VALUE}};'], 'condition' => ['close_type' => 'x']]);
        $this->add_control('close_background_color_hover', ['label' => esc_html__('Background color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close:hover' => 'background-color: {{VALUE}};'], 'condition' => ['close_type!' => ['image', 'x']]]);
        $this->add_control('x_close_background_color_hover', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:hover' => 'background-color: {{VALUE}};'], 'condition' => ['close_type' => 'x']]);
        $this->add_control('close_bg_color_hover', ['label' => esc_html__('Background color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close:hover' => 'border-color: {{VALUE}};'], 'condition' => ['close_bg_border_border!' => '']]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->add_responsive_control('x_buttonsize_closemodal', ['label' => esc_html__('Button Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'separator' => 'before', 'default' => ['size' => 50, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 20, 'max' => 100, 'step' => 1]], 'condition' => ['close_type' => 'x'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};']]);
        $this->add_control('x_weight_closemodal', ['label' => esc_html__('Close Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 1, 'max' => 20, 'step' => 1]], 'condition' => ['close_type' => 'x'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:after, #animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:before' => 'height: {{SIZE}}{{UNIT}}; top: calc(50% - ({{SIZE}}{{UNIT}}/2));']]);
        $this->add_control('x_size_closemodal', ['label' => esc_html__('Close Size (%)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 60, 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => 20, 'max' => 200, 'step' => 1]], 'condition' => ['close_type' => 'x'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:after, #animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics:before' => 'width: {{SIZE}}{{UNIT}}; left: calc(50% - ({{SIZE}}{{UNIT}}/2));']]);
        $this->add_responsive_control('x_vertical_close', ['label' => esc_html__('Y Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0, 'unit' => 'px'], 'size_units' => ['px', 'em', '%'], 'range' => ['px' => ['min' => 0, 'max' => 200, 'step' => 1], 'em' => ['min' => 0, 'max' => 10, 'step' => 1], '%' => ['min' => 0, 'max' => 100, 'step' => 1]], 'condition' => ['close_type' => 'x'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics' => 'top: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('x_horizontal_close', ['label' => esc_html__('X Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0, 'unit' => 'px'], 'size_units' => ['px'], 'range' => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]], 'condition' => ['close_type' => 'x'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .dce-quit-ics' => 'right: {{SIZE}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'close_typography', 'label' => esc_html__('Close Typography', 'dynamic-content-for-elementor'), 'selector' => '#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close:not(i)', 'condition' => ['close_type' => 'text', 'close_text!' => '']]);
        $this->add_responsive_control('close_size', ['label' => esc_html__('Icon Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['min' => 6, 'max' => 300]], 'default' => ['size' => 20, 'unit' => 'px'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; height: auto;', '#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm .dce-close .close-img' => 'width: {{SIZE}}{{UNIT}}; height: auto;'], 'condition' => ['close_type' => ['icon', 'image']]]);
        $this->add_control('close_bg_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['close_type!' => 'x']]);
        $this->add_control('close_margin', ['label' => esc_html__('Close Margin', 'dynamic-content-for-elementor'), 'description' => esc_html__('Helpful insert close button external from modal by insert negative values', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before', 'condition' => ['close_type!' => 'x']]);
        $this->add_control('close_padding', ['label' => esc_html__('Close Padding', 'dynamic-content-for-elementor'), 'description' => esc_html__('Please note that padding bottom has no effect. Left or right padding will depend on the button position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', 'em', '%'], 'selectors' => ['#animatedoffcanvasmenu-{{ID}} .dce-menu-aocm button.dce-close' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before', 'condition' => ['close_type!' => 'x']]);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        ?>
		<div class="dce-menu-aocm-strip">

			<div class="dce-button-wrapper">
				<div class="dce-button-hamburger">
					<?php 
        if ($settings['hamburger_style'] != 'custom_icon') {
            ?>
					<svg version="2" id="dce_hamburger" class="<?php 
            echo $settings['hamburger_style'];
            ?>" xmlns="http://www.w3.org/2000/svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" preserveAspectRatio="xMidYMin slice">

						<?php 
            if ($settings['hamburger_style'] == 'bars') {
                ?>
							<g><line x1="0" y1="20" x2="80" y2="20"/></g>
							<g><line x1="0" y1="40" x2="80" y2="40"/></g>
							<g><line x1="0" y1="60" x2="80" y2="60"/></g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'barsround') {
                ?>
							<g><line x1="10" y1="20" x2="70" y2="20" stroke-linecap="round"/></g>
							<g><line x1="10" y1="40" x2="70" y2="40" stroke-linecap="round"/></g>
							<g><line x1="10" y1="60" x2="70" y2="60" stroke-linecap="round"/></g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'dots') {
                ?>
							<g><circle cx="40" cy="20" r="5"/></g>
							<g><circle cx="40" cy="40" r="5"/></g>
							<g><circle cx="40" cy="60" r="5"/></g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'grid9') {
                ?>
							<g><circle cx="40" cy="15" r="5"/>
								<circle cx="15" cy="15" r="5"/>
								<circle cx="65" cy="15" r="5"/></g>
							<g><circle cx="40" cy="40" r="5"/>
								<circle cx="15" cy="40" r="5"/>
								<circle cx="65" cy="40" r="5"/></g>
							<g><circle cx="40" cy="65" r="5"/>
								<circle cx="15" cy="65" r="5"/>
								<circle cx="65" cy="65" r="5"/></g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'grid4') {
                ?>
							<g>
								<circle cx="20" cy="20" r="5"/>
								<circle cx="60" cy="20" r="5"/>
							</g>
							<g>
								<circle cx="20" cy="60" r="5"/>
								<circle cx="60" cy="60" r="5"/>
							</g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'plus') {
                ?>
							<g><line x1="40" y1="5" x2="40" y2="75"/></g>
							<g><line x1="5" y1="40" x2="75" y2="40"/></g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'arrow') {
                ?>
							<g><path d="M15,40h57 M42.607,12.393L15,40l27.607,27.608"/></g>
						<?php 
            } elseif ($settings['hamburger_style'] == 'wave') {
                ?>

							<?php 
                if ($settings['hamburger_wave_style'] == 1) {
                    ?>
								<g id="curve1">
									<path d="M6.212,30c17.197,0,17.197,20,34.394,20C57.803,50,57.803,30,75,30"/>
								</g>
							<?php 
                } elseif ($settings['hamburger_wave_style'] == 2) {
                    ?>
								<g id="curve2">
									<path d="M5,33c11.666,0,11.666,14,23.331,14
										c11.667,0,11.667-14,23.335-14C63.333,33,63.333,47,75,47"/>
								</g>
							<?php 
                } elseif ($settings['hamburger_wave_style'] == 3) {
                    ?>
								<g id="curve3">
									<path d="M6.212,33.121c8.598,0,8.598,13.758,17.195,13.758
										c8.598,0,8.598-13.758,17.196-13.758c8.6,0,8.6,13.758,17.198,13.758S66.4,33.121,75,33.121"/>
								</g>
							<?php 
                } elseif ($settings['hamburger_wave_style'] == 4) {
                    ?>
								<g id="curve4">
									<path d="M6.212,33.121c5.729,0,5.729,13.758,11.459,13.758
										c5.73,0,5.73-13.758,11.461-13.758c5.733,0,5.733,13.758,11.465,13.758c5.731,0,5.731-13.758,11.463-13.758
										c5.734,0,5.734,13.758,11.47,13.758S69.266,33.121,75,33.121"/>
								</g>
							<?php 
                } elseif ($settings['hamburger_wave_style'] == 5) {
                    ?>
								<g id="curve5">
									<path d="M6.212,33.121c4.298,0,4.298,13.758,8.595,13.758
										s4.297-13.758,8.595-13.758s4.297,13.758,8.595,13.758c4.299,0,4.299-13.758,8.598-13.758c4.3,0,4.3,13.758,8.601,13.758
										c4.299,0,4.299-13.758,8.598-13.758c4.302,0,4.302,13.758,8.604,13.758S70.698,33.121,75,33.121"/>
								</g>
							<?php 
                }
                ?>

						<?php 
            } elseif ($settings['hamburger_style'] == 'circlebar') {
                ?>
							<g><circle cx="40" cy="40" r="37"/></g>
							<g>
								<line x1="29" y1="35" x2="51" y2="35"/>
								<line x1="29" y1="45" x2="51" y2="45"/>
							</g>
						<?php 
            }
            ?>
					</svg>
					<?php 
        } else {
            // custom_icon
            ?>
						<div id="dce_hamburger">
							<?php 
            Icons_Manager::render_icon($settings['custom_icon_image'], ['aria-hidden' => 'true']);
            ?>
						</div>
					<?php 
        }
        ?>
				</div>
			</div>
		</div>

		<div id="animatedoffcanvasmenu-<?php 
        echo $this->get_id();
        ?>" class="dce-menu-aocm-wrap animatedoffcanvasmenu">
			<?php 
        if ($settings['side_background'] == 'show') {
            ?>
				<div class="dce-bg"></div>
			<?php 
        }
        ?>
			<div class="dce-nav">
				<div class="dce-menu-aocm">
					<div class="dce-close close-hidden close-<?php 
        echo $settings['close_type'];
        ?>" aria-label="Close">
						<?php 
        if ($settings['close_type'] == 'text') {
            ?>
							<span class="dce-button-text"><?php 
            echo wp_kses_post($settings['close_text']);
            ?></span>
						<?php 
        }
        ?>

						<?php 
        if ($settings['close_type'] == 'icon' && $settings['close_icon']) {
            Icons_Manager::render_icon($settings['close_icon'], ['aria-hidden' => 'true']);
        }
        ?>

						<?php 
        if ($settings['close_type'] == 'image') {
            ?>
							<?php 
            if ($settings['close_image']['id']) {
                ?>
								<img class="close-img" aria-hidden="true" src="<?php 
                echo $settings['close_image']['url'];
                ?>" />
							<?php 
            }
            ?>
						<?php 
        }
        ?>

						<?php 
        if ($settings['close_type'] == 'x') {
            ?>
							<span class="dce-quit-ics"></span>
						<?php 
        }
        ?>
					</div>

					<div class="dce-nav-menu">
						<?php 
        if ($settings['dynamic_template_before_choice'] && $settings['dynamic_template_before']) {
            ?>
							<div class="dce-template-before">
								<?php 
            $atts = ['id' => $settings['dynamic_template_before'], 'inlinecss' => \Elementor\Plugin::$instance->editor->is_edit_mode()];
            $template_system = \DynamicContentForElementor\Plugin::instance()->template_system;
            echo $template_system->build_elementor_template_special($atts);
            ?>
							</div>
						<?php 
        }
        ?>

						<?php 
        if ($settings['menu_animatedoffcanvasmenu']) {
            wp_nav_menu(['menu' => $settings['menu_animatedoffcanvasmenu'], 'menu_id' => 'dce-ul-menu', 'depth' => $settings['animatedoffcanvasmenu_depth'], 'before' => '<span class="menu-item-wrap">', 'after' => '</span>']);
        }
        ?>

						<?php 
        if ($settings['dynamic_template_after_choice'] && $settings['dynamic_template_after']) {
            ?>
							<div class="dce-template-after">
								<?php 
            $atts = ['id' => $settings['dynamic_template_after'], 'inlinecss' => \Elementor\Plugin::$instance->editor->is_edit_mode()];
            $template_system = \DynamicContentForElementor\Plugin::instance()->template_system;
            echo $template_system->build_elementor_template_special($atts);
            ?>
							</div>
						<?php 
        }
        ?>
					</div>
				</div>
			</div>
		</div>
		<?php 
    }
}
