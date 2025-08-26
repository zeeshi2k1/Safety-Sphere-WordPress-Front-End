<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
use DynamicContentForElementor\Helper;
class Events extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        $element->add_control('dce_visibility_events_note', ['type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('Using an Event trigger is necessary to activate "Keep HTML" from settings', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'condition' => ['dce_visibility_dom' => '']]);
        $element->add_control('dce_visibility_event', ['frontend_available' => \true, 'label' => esc_html__('Event', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'click', 'options' => ['click' => 'click', 'mouseover' => 'mouseover', 'dblclick' => 'dblclick', 'touchstart' => 'touchstart', 'touchmove' => 'touchmove'], 'condition' => ['dce_visibility_dom!' => '']]);
        $element->add_control('dce_visibility_click', ['frontend_available' => \true, 'label' => esc_html__('Trigger on this element', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'description' => esc_html__('Type here the Selector in jQuery format. For example #name', 'dynamic-content-for-elementor'), 'dynamic' => ['active' => \true], 'condition' => ['dce_visibility_dom!' => '']]);
        $element->add_control('dce_visibility_click_show', ['frontend_available' => \true, 'label' => esc_html__('Show Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_jquery_display_mode(), 'condition' => ['dce_visibility_dom!' => '', 'dce_visibility_click!' => '']]);
        $element->add_control('dce_visibility_event_transition_delay', ['frontend_available' => \true, 'label' => esc_html__('Transition Delay', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 400, 'condition' => ['dce_visibility_dom!' => '', 'dce_visibility_click!' => '', 'dce_visibility_click_show!' => '']]);
        $element->add_control('dce_visibility_click_other', ['frontend_available' => \true, 'label' => esc_html__('Hide other elements', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'description' => esc_html__('Type here the Selector in jQuery format. For example .elements', 'dynamic-content-for-elementor'), 'condition' => ['dce_visibility_dom!' => '', 'dce_visibility_click!' => '']]);
        $element->add_control('dce_visibility_click_toggle', ['frontend_available' => \true, 'label' => esc_html__('Toggle', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['dce_visibility_dom!' => '', 'dce_visibility_click!' => '']]);
        $element->add_control('dce_visibility_load', ['frontend_available' => \true, 'label' => esc_html__('On Page Load', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['dce_visibility_dom!' => ''], 'separator' => 'before']);
        $element->add_control('dce_visibility_load_delay', ['frontend_available' => \true, 'label' => esc_html__('Delay time', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'min' => 0, 'default' => 0, 'condition' => ['dce_visibility_dom!' => '', 'dce_visibility_load!' => '']]);
        $element->add_control('dce_visibility_load_show', ['frontend_available' => \true, 'label' => esc_html__('Show Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_jquery_display_mode(), 'condition' => ['dce_visibility_dom!' => '', 'dce_visibility_load!' => '']]);
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
        // This method is intentionally left empty for the "events" trigger.
        // Because event-based triggers (such as click or load events) are handled entirely
        // on the client side via JavaScript, no server-side condition evaluation is necessary.
        // All relevant settings for events (for example, the click selector) are processed
        // separately in the is_hidden() method
    }
}
