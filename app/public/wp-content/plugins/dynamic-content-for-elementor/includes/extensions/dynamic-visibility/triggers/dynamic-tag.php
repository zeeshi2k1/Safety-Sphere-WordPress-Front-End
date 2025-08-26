<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
use DynamicContentForElementor\Helper;
class DynamicTag extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        $element->add_control('dce_visibility_dynamic_tag', ['label' => esc_html__('Dynamic Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'label_block' => \true, 'dynamic' => ['active' => \true, 'categories' => [
            // only categories that return strings or we'll
            // get Elementor warnings.
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::DATETIME_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY,
            \Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY,
        ]], 'placeholder' => esc_html__('Choose a Dynamic Tag', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_dynamic_tag_status', ['label' => esc_html__('Status', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'label_block' => \true, 'options' => Helper::compare_options(), 'default' => 'isset', 'toggle' => \false]);
        $element->add_control('dce_visibility_dynamic_tag_value', ['type' => Controls_Manager::TEXT, 'label' => esc_html__('Value', 'dynamic-content-for-elementor'), 'condition' => ['dce_visibility_dynamic_tag_status!' => ['not', 'isset']]]);
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
        if (!empty($settings['__dynamic__']) && !empty($settings['__dynamic__']['dce_visibility_dynamic_tag'])) {
            $triggers['dce_visibility_dynamic_tag'] = esc_html__('Dynamic Tag', 'dynamic-content-for-elementor');
            $my_val = $settings['dce_visibility_dynamic_tag'];
            $condition_result = Helper::is_condition_satisfied($my_val, $settings['dce_visibility_dynamic_tag_status'], $settings['dce_visibility_dynamic_tag_value']);
            ++$triggers_n;
            if ($condition_result) {
                $conditions['dce_visibility_dynamic_tag'] = esc_html__('Dynamic Tag', 'dynamic-content-for-elementor');
            }
        }
    }
}
