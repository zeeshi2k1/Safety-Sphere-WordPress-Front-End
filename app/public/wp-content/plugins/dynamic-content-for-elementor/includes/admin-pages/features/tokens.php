<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\AdminPages\Features;

use DynamicContentForElementor\AdminPages\Settings;
use DynamicContentForElementor\Tokens;
use DynamicOOO\PluginUtils\AdminPages\Pages\Base;
class TokensSettings extends Settings\SettingsPage
{
    const PAGE_ID = 'dce-settings';
    /**
     * Fix old filter whitelist bug
     *
     * @return void
     */
    public function before_register()
    {
        if (\is_array(get_option('dce_tokens_filters_whitelist'))) {
            Tokens::fix_filters_whitelist();
        }
    }
    /**
     * Get Name
     *
     * @return string
     */
    public function get_name()
    {
        return 'tokens';
    }
    /**
     * Get Label
     *
     * @return string
     */
    public function get_label()
    {
        return 'Tokens ' . esc_html__('(Deprecated)', 'dynamic-content-for-elementor');
    }
    /**
     * Should Display Count
     *
     * @return boolean
     */
    public function should_display_count()
    {
        return \false;
    }
    /**
     * @param string $id
     * @return string
     */
    protected function tokens_filters_whitelist($id)
    {
        $value = esc_textarea(get_option('dce_' . $id, ''));
        $html = "<textarea placeholder='my_function' cols='30' rows='5' id='dce_{$id}' name='dce_{$id}'>{$value}</textarea>";
        $html .= '<p class="description">' . esc_html__('One filter per line', 'dynamic-content-for-elementor') . '</p>';
        return $html;
    }
    /**
     * @return void
     */
    protected function render_tokens_intro()
    {
        $notice = \DynamicContentForElementor\Plugin::instance()->text_templates->get_notice_content();
        if (empty($notice)) {
            return;
        }
        if ('dynamic_shortcodes_only' === $notice['case'] || 'none' === $notice['case']) {
            echo '<p><strong>' . esc_html__('Tokens is now deprecated. Do not activate unless strictly necessary for backward compatibility.', 'dynamic-content-for-elementor') . '</strong></p>';
        } else {
            echo '<p><strong>' . $notice['content'] . '</strong></p>';
        }
    }
    /**
     * @return array<string,mixed>
     */
    public function create_tabs()
    {
        $default_status = Tokens::status_with_unsaved_option();
        $tabs = ['tokens' => ['label' => esc_html__('Tokens', 'dynamic-content-for-elementor'), 'sections' => ['tokens' => ['callback' => [$this, 'render_tokens_intro'], 'fields' => ['tokens_status' => ['label' => esc_html__('Tokens Status', 'dynamic-content-for-elementor'), 'field_args' => ['type' => 'select', 'std' => $default_status, 'options' => ['enable' => esc_html__('Enable', 'dynamic-content-for-elementor'), 'disable' => esc_html__('Disable', 'dynamic-content-for-elementor')]]], 'active_tokens' => ['label' => esc_html__('Active Tokens', 'dynamic-content-for-elementor'), 'field_args' => ['type' => 'checkbox_list', 'std' => \array_keys(Tokens::get_tokens_list()), 'options' => Tokens::get_tokens_options()]], 'tokens_filters_whitelist' => ['label' => esc_html__('Filters Whitelist', 'dynamic-content-for-elementor'), 'field_args' => ['type' => 'raw_html', 'html' => $this->tokens_filters_whitelist('tokens_filters_whitelist')]]]]]]];
        return $tabs;
    }
}
