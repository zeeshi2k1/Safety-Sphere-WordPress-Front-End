<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Integrations;

use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Plugin;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly.
}
class AdvancedCustomFields
{
    /**
     * @var string
     */
    private $google_maps_api;
    public function __construct()
    {
        $this->google_maps_api = get_option(Plugin::instance()->prefix . '_google_maps_api');
        $google_maps_api_acf = get_option(Plugin::instance()->prefix . '_google_maps_api_acf');
        if (empty($this->google_maps_api) || empty($google_maps_api_acf)) {
            return;
        }
        if (Helper::is_acf_active() || Helper::is_acfpro_active()) {
            add_filter('acf/fields/google_map/api', [$this, 'set_google_maps_api']);
        }
    }
    /**
     * Set Google Maps API key for ACF fields
     *
     * @param array<string,mixed> $api
     * @return array<string,mixed>
     */
    public function set_google_maps_api($api)
    {
        $api['key'] = $this->google_maps_api;
        return $api;
    }
}
