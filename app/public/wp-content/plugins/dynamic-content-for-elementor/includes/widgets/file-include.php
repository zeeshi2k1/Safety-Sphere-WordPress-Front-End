<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use DynamicContentForElementor\Helper;
use Elementor\Controls_Manager;
if (!\defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
class FileInclude extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_includefile', ['label' => esc_html__('File Include', 'dynamic-content-for-elementor')]);
        $this->add_control('file', ['label' => esc_html__('File path', 'dynamic-content-for-elementor'), 'description' => esc_html__('The path of the file to include (e.g.:folder/file.html) ', 'dynamic-content-for-elementor'), 'placeholder' => 'wp-content/themes/my-theme/my-custom-file.php', 'type' => Controls_Manager::TEXT, 'label_block' => \true, 'frontend_available' => \true, 'default' => '']);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $file = ABSPATH . $settings['file'];
        if ('' !== $settings['file'] && \file_exists($file)) {
            include $file;
        } elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            if ('' === $settings['file']) {
                _e('Select the file', 'dynamic-content-for-elementor');
            } elseif (!\file_exists($file)) {
                _e('The file doesn\'t exists', 'dynamic-content-for-elementor');
            }
        }
    }
}
