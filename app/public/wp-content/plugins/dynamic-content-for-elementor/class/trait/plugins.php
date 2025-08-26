<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor;

trait Plugins
{
    protected static $plugin_dependency_names = ['acf' => 'Advanced Custom Fields', 'advanced-custom-fields-pro' => 'Advanced Custom Fields Pro', 'elementor-pro' => 'Elementor Pro', 'jet-engine' => 'JetEngine', 'metabox' => 'Meta Box', 'pods' => 'Pods', 'search-filter-pro' => 'Search & Filter Pro', 'timber' => 'Timber', 'types' => 'Toolset', 'woocommerce' => 'WooCommerce'];
    /**
     * @var array<string,mixed>
     */
    private static $plugin_depends = [];
    public static $checked_plugins = [];
    private static function set_plugin_dependency_status($plugin)
    {
        switch ($plugin) {
            case 'acf':
                self::$plugin_depends['acf'] = \class_exists('ACF');
                break;
            case 'acf-pro':
                self::$plugin_depends['acf-pro'] = \class_exists('ACF') && \defined('ACF_PRO');
                break;
            case 'breakdance':
                self::$plugin_depends['breakdance'] = \true;
                break;
            case 'dynamic-shortcodes':
                self::$plugin_depends['dynamic-shortcodes'] = \class_exists('DynamicShortcodes\\Plugin');
                break;
            case 'elementor':
                self::$plugin_depends['elementor'] = \class_exists('Elementor\\Plugin');
                break;
            case 'elementor-pro':
                self::$plugin_depends['elementor-pro'] = \class_exists('ElementorPro\\Plugin');
                break;
            case 'jet-engine':
                self::$plugin_depends['jet-engine'] = \class_exists('Jet_Engine');
                break;
            case 'metabox':
                self::$plugin_depends['metabox'] = \class_exists('RWMB_Core');
                break;
            case 'oxygen':
                self::$plugin_depends['oxygen'] = \true;
                break;
            case 'pods':
                self::$plugin_depends['pods'] = \class_exists('DynamicOOOS\\Pods');
                break;
            case 'timber':
                self::$plugin_depends['timber'] = \class_exists('\\Timber\\Timber');
                break;
            case 'toolset':
                self::$plugin_depends['toolset'] = \defined('TYPES_VERSION');
                break;
            case 'woocommerce':
                self::$plugin_depends['woocommerce'] = \class_exists('woocommerce');
                break;
            case 'wpml':
                self::$plugin_depends['wpml'] = \class_exists('SitePress');
                break;
            default:
                throw new \Error('bad plugin name');
        }
    }
    public static function check_plugin_dependency($plugin)
    {
        if (!isset(self::$plugin_depends[$plugin])) {
            self::set_plugin_dependency_status($plugin);
        }
        return self::$plugin_depends[$plugin];
    }
    public static function get_plugin_dependency_names($plugin)
    {
        if (isset(self::$plugin_dependency_names[$plugin])) {
            return self::$plugin_dependency_names[$plugin];
        }
        return $plugin;
    }
    public static function is_plugin_active($plugin)
    {
        if (isset(self::$checked_plugins[$plugin])) {
            return self::$checked_plugins[$plugin];
        }
        if ($plugin === 'elementor-pro') {
            $is_active = self::is_elementorpro_active();
        } else {
            $is_active = self::is_acf_pro($plugin) || self::is_plugin_must_use($plugin) || self::is_plugin_active_for_local($plugin) || self::is_plugin_active_for_network($plugin);
        }
        self::$checked_plugins[$plugin] = $is_active;
        return $is_active;
    }
    public static function is_acf_pro($plugin)
    {
        if ($plugin == 'acf') {
            if (\defined('ACF')) {
                return ACF;
            }
        }
        if ($plugin == 'advanced-custom-fields-pro') {
            if (\defined('ACF_PRO')) {
                return ACF_PRO;
            }
        }
        return \false;
    }
    public static function is_plugin_must_use($plugin)
    {
        $mu_plugins = wp_get_mu_plugins();
        // Must Use
        if (\is_dir(WPMU_PLUGIN_DIR)) {
            $mu_dir_plugins = \glob(WPMU_PLUGIN_DIR . '/*/*.php');
            // Must Use
            if (!empty($mu_dir_plugins)) {
                foreach ($mu_dir_plugins as $aplugin) {
                    $mu_plugins[] = $aplugin;
                }
            }
        }
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        if (!empty($mu_plugins)) {
            foreach ($mu_plugins as $aplugin) {
                $plugin_data = get_plugin_data($aplugin);
                if (!empty($plugin_data['Name']) && $plugin_data['Name'] == 'Advanced Custom Fields PRO') {
                    $mu_plugins[] = \str_replace('acf.php', 'advanced-custom-fields-pro.php', $aplugin);
                    break;
                }
            }
        }
        return self::check_plugin($plugin, $mu_plugins);
    }
    public static function is_plugin_active_for_local($plugin)
    {
        $active_plugins = get_option('active_plugins', array());
        return self::check_plugin($plugin, $active_plugins);
    }
    public static function is_plugin_active_for_network($plugin)
    {
        $active_plugins = get_site_option('active_sitewide_plugins');
        if (!empty($active_plugins)) {
            $active_plugins = \array_keys($active_plugins);
            return self::check_plugin($plugin, $active_plugins);
        }
        return \false;
    }
    public static function check_plugin($plugin, $active_plugins = array())
    {
        if (\in_array($plugin, (array) $active_plugins)) {
            return \true;
        }
        if (!empty($active_plugins)) {
            foreach ($active_plugins as $aplugin) {
                $tmp = \basename($aplugin);
                $tmp = \pathinfo($tmp, \PATHINFO_FILENAME);
                if ($plugin == $tmp) {
                    return \true;
                }
            }
        }
        if (!empty($active_plugins)) {
            foreach ($active_plugins as $aplugin) {
                $pezzi = \explode('/', $aplugin);
                $tmp = \reset($pezzi);
                if ($plugin == $tmp) {
                    return \true;
                }
            }
        }
        return \false;
    }
    public static function is_woocommerce_active()
    {
        if (\class_exists('woocommerce')) {
            return \true;
        }
        return \false;
    }
    public static function is_memberpress_active()
    {
        if (\defined('MEPR_PLUGIN_NAME')) {
            return \true;
        }
        return \false;
    }
    public static function is_myfastapp_active()
    {
        if (\defined('TOA_MYFASTAPP_VERSION')) {
            return \true;
        }
        return \false;
    }
    /**
     * Check if Geolocation IP Detection is active
     * https://wordpress.org/plugins/geoip-detect/
     *
     * @return boolean
     */
    public static function is_geoipdetect_active()
    {
        return \DynamicContentForElementor\Helper::is_plugin_active('geoip-detect') && \function_exists('geoip_detect2_get_info_from_current_ip');
    }
    /**
     * Check if WPML is active
     *
     * @return boolean
     */
    public static function is_wpml_active()
    {
        if (\class_exists('SitePress')) {
            return \true;
        }
        return \false;
    }
    public static function is_acf_active()
    {
        if (\class_exists('ACF') && \defined('ACF')) {
            return \true;
        }
        return \false;
    }
    /**
     * @return boolean
     */
    public static function is_dynamic_shortcodes_active()
    {
        return \class_exists('DynamicShortcodes\\Plugin');
    }
    public static function is_acfpro_active()
    {
        if (\class_exists('ACF') && \defined('ACF_PRO')) {
            return \true;
        }
        return \false;
    }
    /**
     * Check if Jet Engine is active
     *
     * @return boolean
     */
    public static function is_jetengine_active()
    {
        if (\class_exists('Jet_Engine')) {
            return \true;
        }
        return \false;
    }
    /**
     * Check if Meta Box is active
     *
     * @return boolean
     */
    public static function is_metabox_active()
    {
        if (\class_exists('RWMB_Core')) {
            return \true;
        }
        return \false;
    }
    public static function is_pods_active()
    {
        if (self::is_plugin_active('pods')) {
            return \true;
        }
        return \false;
    }
    /**
     * @return boolean
     */
    public static function is_searchandfilterpro_active()
    {
        // Check if either SFPro2 or SFPro3 constants are defined
        return \defined('SEARCH_FILTER_PRO_BASE_PATH') || \defined('SEARCH_FILTER_PRO_BASE_FILE');
    }
    public static function is_elementorpro_active()
    {
        if (\class_exists('ElementorPro\\Plugin')) {
            return \true;
        }
        return \false;
    }
    public static function is_polylang_active()
    {
        if (\class_exists('Polylang') && \function_exists('pll_languages_list')) {
            return \true;
        }
        return \false;
    }
    public static function check_plugin_dependencies($response = \false, $dependencies = [])
    {
        $plugin_disabled = [];
        if (!empty($dependencies)) {
            $is_active = \true;
            foreach ($dependencies as $key => $plugin) {
                if (!\is_numeric($key)) {
                    if (!\DynamicContentForElementor\Helper::is_plugin_active($key)) {
                        $is_active = \false;
                    }
                } elseif (!\DynamicContentForElementor\Helper::is_plugin_active($plugin)) {
                    $is_active = \false;
                }
                if (!$is_active) {
                    if (!$response) {
                        return \false;
                    }
                    if (\is_numeric($key)) {
                        $plugin_disabled[] = self::get_plugin_dependency_names($plugin);
                    } else {
                        $plugin_disabled[] = $key;
                    }
                }
            }
        }
        if ($response) {
            return $plugin_disabled;
        }
        return \true;
    }
    /**
     * @param int|float $version
     * @return bool
     */
    public static function is_search_filter_pro_version($version)
    {
        if (!\in_array($version, [2, 3, 3.1], \true)) {
            return \false;
        }
        if (2 === $version) {
            return \defined('SEARCH_FILTER_VERSION') && \version_compare(SEARCH_FILTER_VERSION, '2.0.0', '>=') && \version_compare(SEARCH_FILTER_VERSION, '3.0.0', '<');
        }
        $is_pro_defined = \defined('SEARCH_FILTER_PRO_VERSION');
        if (!$is_pro_defined) {
            return \false;
        }
        if (3 === $version) {
            return \version_compare(SEARCH_FILTER_PRO_VERSION, '3.0.0', '>=') && \version_compare(SEARCH_FILTER_PRO_VERSION, '3.1.0', '<');
        }
        // Version 3.1 or greater
        return \version_compare(SEARCH_FILTER_PRO_VERSION, '3.1.0', '>=');
    }
}
