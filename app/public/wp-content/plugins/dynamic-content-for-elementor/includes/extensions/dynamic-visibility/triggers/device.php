<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
class Device extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        $element->add_control('dce_visibility_responsive', ['label' => esc_html__('Responsive', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['desktop' => ['title' => esc_html__('Desktop and Tv', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-desktop'], 'mobile' => ['title' => esc_html__('Mobile and Tablet', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-mobile']], 'description' => esc_html__('Not really responsive, remove the element from the code based on the user\'s device. This trigger uses native WP device detection.', 'dynamic-content-for-elementor') . ' <a href="https://codex.wordpress.org/Function_Reference/wp_is_mobile" target="_blank">' . esc_html__('Read more.', 'dynamic-content-for-elementor') . '</a>']);
        $element->add_control('dce_visibility_browser', ['label' => esc_html__('Browser', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => ['is_chrome' => 'Google Chrome', 'is_gecko' => 'FireFox', 'is_safari' => 'Safari', 'is_IE' => 'Internet Explorer', 'is_edge' => 'Microsoft Edge', 'is_NS4' => 'Netscape', 'is_opera' => 'Opera', 'is_lynx' => 'Lynx', 'is_iphone' => 'iPhone'], 'description' => esc_html__('Trigger visibility for a specific browser.', 'dynamic-content-for-elementor'), 'multiple' => \true, 'separator' => 'before']);
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
        if (!isset($settings['dce_visibility_device']) || !$settings['dce_visibility_device']) {
            $ahidden = \false;
            // responsive
            if (isset($settings['dce_visibility_responsive']) && $settings['dce_visibility_responsive']) {
                $triggers['dce_visibility_responsive'] = esc_html__('Responsive', 'dynamic-content-for-elementor');
                if (wp_is_mobile()) {
                    ++$triggers_n;
                    if ($settings['dce_visibility_responsive'] == 'mobile') {
                        $conditions['dce_visibility_responsive'] = esc_html__('Responsive: is Mobile', 'dynamic-content-for-elementor');
                        $ahidden = \true;
                    }
                } else {
                    ++$triggers_n;
                    if ($settings['dce_visibility_responsive'] == 'desktop') {
                        $conditions['dce_visibility_responsive'] = esc_html__('Responsive: is Desktop', 'dynamic-content-for-elementor');
                        $ahidden = \true;
                    }
                }
            }
            // browser
            if (isset($settings['dce_visibility_browser']) && \is_array($settings['dce_visibility_browser']) && !empty($settings['dce_visibility_browser'])) {
                $triggers['dce_visibility_browser'] = esc_html__('Browser', 'dynamic-content-for-elementor');
                $is_browser = \false;
                foreach ($settings['dce_visibility_browser'] as $browser) {
                    global ${$browser};
                    if (isset(${$browser}) && ${$browser}) {
                        $is_browser = \true;
                    }
                }
                ++$triggers_n;
                if ($is_browser) {
                    $conditions['dce_visibility_browser'] = esc_html__('Browser', 'dynamic-content-for-elementor');
                    $ahidden = \true;
                }
            }
        }
    }
}
