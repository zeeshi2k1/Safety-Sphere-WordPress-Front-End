<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\TextTemplates;

use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Tokens;
class Manager
{
    /**
     * @var array<string,mixed>
     */
    public $expand_data = [];
    const URL_MIGRATE_TOKENS_TO_DSH = 'https://dnmc.ooo/dce-tokens-migration';
    const URL_DSH_INSTALLATION = 'https://dnmc.ooo/dsh-dce-install';
    /**
     * @var \DynamicContentForElementor\TextTemplates\DynamicShortcodes\Manager
     */
    public $dce_shortcodes;
    /**
     * @var \DynamicContentForElementor\TextTemplates\Timber\Manager
     */
    public $timber;
    public function __construct()
    {
        $this->dce_shortcodes = new \DynamicContentForElementor\TextTemplates\DynamicShortcodes\Manager();
        $this->timber = new \DynamicContentForElementor\TextTemplates\Timber\Manager();
    }
    /**
     * @return array<string,mixed>
     */
    public function get_notice_content()
    {
        $is_tokens_active = \DynamicContentForElementor\Tokens::is_active();
        $is_dynamic_shortcodes_installed = $this->dce_shortcodes->is_dsh_active();
        if ($is_tokens_active && $is_dynamic_shortcodes_installed) {
            return ['case' => 'both', 'notice_type' => 'warning', 'heading' => esc_html__('Use Dynamic Shortcodes Only!', 'dynamic-content-for-elementor'), 'content' => \sprintf(esc_html__('Tokens is now deprecated. Enhance your site by using only Dynamic Shortcodes, which you already have active and deactivate Tokens. %1$sLearn how to migrate%2$s.', 'dynamic-content-for-elementor'), '<a href="' . self::URL_MIGRATE_TOKENS_TO_DSH . '">', '</a>')];
        } elseif ($is_tokens_active) {
            return ['case' => 'tokens_only', 'notice_type' => 'danger', 'heading' => esc_html__('Recommended Update', 'dynamic-content-for-elementor'), 'content' => \sprintf(esc_html__('Tokens is deprecated. Dynamic Shortcodes, included in your Dynamic Content for Elementor license, offers more advanced features and better efficiency. %1$sInstall Dynamic Shortcodes now%2$s.', 'dynamic-content-for-elementor') . $this->maybe_get_install_dsh(), '<a href="' . self::URL_DSH_INSTALLATION . '">', '</a>')];
        } elseif ($is_dynamic_shortcodes_installed) {
            return [];
        } else {
            return ['case' => 'none', 'notice_type' => 'danger', 'heading' => esc_html__('Installation Required', 'dynamic-content-for-elementor'), 'content' => \sprintf(esc_html__('Dynamic Shortcodes, which is included in your Dynamic Content for Elementor license, is not currently installed. %1$sInstall Dynamic Shortcodes%2$s to enhance your experience with Elementor.', 'dynamic-content-for-elementor') . $this->maybe_get_install_dsh(), '<a href="' . self::URL_DSH_INSTALLATION . '">', '</a>')];
        }
    }
    /**
     * @return void|array<string,mixed>
     */
    public function get_notice_html_templates()
    {
        $is_timber_installed = Helper::check_plugin_dependency('timber');
        $is_dynamic_shortcodes_installed = $this->dce_shortcodes->is_dsh_active();
        if ($is_timber_installed && $is_dynamic_shortcodes_installed) {
            return ['case' => 'both', 'notice_type' => 'warning', 'heading' => esc_html__('Use Dynamic Shortcodes Only!', 'dynamic-content-for-elementor'), 'content' => esc_html__('PDF creation with Timber plugin is deprecated, but we will not remove the integration. Please use only Dynamic Shortcodes, which you already have active and deactivate Timber.', 'dynamic-content-for-elementor'), 'required' => \false];
        } elseif ($is_timber_installed) {
            return ['case' => 'timber_only', 'notice_type' => 'danger', 'heading' => esc_html__('Recommended Update', 'dynamic-content-for-elementor'), 'content' => \sprintf(esc_html__('PDF creation with Timber plugin is deprecated, but we will not remove the integration. Please use Dynamic Shortcodes, included in your Dynamic Content for Elementor license. %1$sInstall Dynamic Shortcodes now%2$s.', 'dynamic-content-for-elementor'), '<a href="' . self::URL_DSH_INSTALLATION . '">', '</a>'), 'required' => \false];
        } elseif ($is_dynamic_shortcodes_installed) {
            return;
        } else {
            return ['case' => 'none', 'notice_type' => 'danger', 'heading' => esc_html__('Installation Required', 'dynamic-content-for-elementor'), 'content' => \sprintf(esc_html__('Dynamic Shortcodes, which is included in your Dynamic Content for Elementor license, is not currently installed. %1$sInstall Dynamic Shortcodes%2$s to create your PDF.', 'dynamic-content-for-elementor'), '<a href="' . self::URL_DSH_INSTALLATION . '">', '</a>'), 'required' => \true];
        }
    }
    /**
     * @return string
     */
    protected function maybe_get_install_dsh()
    {
        if (!\function_exists('DynamicOOOS\\get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (isset(get_plugins()['dynamic-shortcodes/dynamic-shortcodes.php'])) {
            return '';
        }
        $admin_post_url = admin_url('admin-post.php');
        $install_dsh = esc_html__('Install Dynamic Shortcodes', 'dynamic-content-for-elementor');
        return <<<HTML
<form target="_blank" action="{$admin_post_url}" method="POST">
\t<input type="hidden" name="action" value="dce_install_dsh">
\t<button class="e-btn e-info e-btn-1" type="submit">{$install_dsh}</button>
\t</form>
HTML;
    }
    /**
     * @param \Elementor\Controls_Stack $widget
     * @param string $prefix
     * @param array<string,mixed> $condition
     * @return void
     */
    public function maybe_add_notice($widget, $prefix = '', $condition = [])
    {
        $notice = $this->get_notice_content();
        if (!empty($notice)) {
            $widget->add_control($prefix . 'dsh_notice', ['type' => \Elementor\Controls_Manager::NOTICE, 'notice_type' => $notice['notice_type'], 'dismissible' => $notice['dismissible'] ?? \false, 'heading' => $notice['heading'], 'content' => $notice['content'], 'condition' => $condition]);
        }
    }
    /**
     * @param array<string,string> $values
     * @return string
     */
    public function get_default_value($values)
    {
        if (Helper::check_plugin_dependency('dynamic-shortcodes')) {
            return $values['dynamic-shortcodes'] ?? '';
        }
        if (Tokens::is_active()) {
            return $values['tokens'] ?? '';
        }
        return '';
    }
    /**
     * @param array<string,mixed> $atts
     * @return string
     */
    public function field_shortcode($atts)
    {
        if (!isset($atts['id'])) {
            return '';
        }
        if (!isset($this->expand_data['form-fields'])) {
            return '';
        }
        $fields = $this->expand_data['form-fields'];
        if (!isset($fields[$atts['id']])) {
            return '';
        }
        return $fields[$atts['id']]['value'];
    }
    /**
     * @return void
     */
    public function ensure_wp_shortcodes()
    {
        if (shortcode_exists('field')) {
            return;
        }
        add_shortcode('field', [$this, 'field_shortcode']);
    }
    /**
     * @param string $str
     * @param array<mixed> $data
     * @param Callable|null $callback
     * @return mixed
     */
    public function expand_shortcodes_or_callback($str, $data, $callback)
    {
        $original_str = $str;
        if (\is_string($str)) {
            $modified_str = $this->dce_shortcodes->expand_with_data($str, $data);
            if ($modified_str !== $original_str) {
                return $modified_str;
            }
        }
        $this->ensure_wp_shortcodes();
        $this->expand_data = $data;
        if (!\is_callable($callback)) {
            if (\is_string($str)) {
                $str = do_shortcode($str);
            }
        } else {
            $str = $callback($str, $data);
        }
        return $str;
    }
}
