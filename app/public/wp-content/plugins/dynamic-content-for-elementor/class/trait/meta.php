<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor;

trait Meta
{
    public static $meta_fields = [];
    public static function get_acf_field_locations($aacf)
    {
        if (\is_string($aacf)) {
            $aacf = self::get_acf_field_post($aacf);
        }
        if ($aacf) {
            $aacf_group = get_post($aacf->post_parent);
            if ($aacf_group) {
                if ($aacf_group->post_parent == 'acf-field') {
                    // may be in repeater or block or tab
                    $aacf_group = get_post($aacf->post_parent);
                }
                if ($aacf_group->post_parent == 'acf-field-group') {
                    return self::get_acf_group_locations($aacf_group);
                }
            }
        }
        return array();
    }
    /**
     * @param string $source_type
     * @param string $other_post_source
     * @return string
     */
    public static function get_acf_source_id($source_type, $other_post_source = \false)
    {
        switch ($source_type) {
            case 'current_post':
                return \DynamicContentForElementor\Helper::get_the_id($other_post_source);
            case 'current_user':
                $user_id = get_current_user_id();
                return 'user_' . $user_id;
            case 'current_author':
                $user_id = get_the_author_meta('ID');
                return 'user_' . $user_id;
            case 'current_term':
                $queried_object = get_queried_object();
                if ($queried_object instanceof \WP_Term) {
                    $taxonomy = $queried_object->taxonomy;
                    $term_id = $queried_object->term_id;
                    return $taxonomy . '_' . $term_id;
                }
                return '';
            case 'options_page':
                return 'option';
        }
        return '';
    }
    public static function get_acf_group_locations($aacf_group)
    {
        $locations = array();
        if (\is_string($aacf_group)) {
            $acf_groups = get_posts(array('post_type' => 'acf-field-group', 'post_excerpt' => $aacf_group, 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
            if (!empty($acf_groups)) {
                $aacf_group = \reset($acf_groups);
            } else {
                return \false;
            }
        }
        $aacf_meta = maybe_unserialize($aacf_group->post_content);
        if (!empty($aacf_meta['location'])) {
            foreach ($aacf_meta['location'] as $gkey => $gvalue) {
                foreach ($gvalue as $rkey => $rvalue) {
                    $pieces = \explode('_', $rvalue['param']);
                    $location = \reset($pieces);
                    $locations[$location] = $location;
                    if ($location == 'page') {
                        $locations['post'] = 'post';
                    }
                    if ($location == 'current') {
                        $locations['user'] = 'user';
                    }
                }
            }
        }
        return $locations;
    }
    public static function get_user_metas($grouped = \false, $like = '', $info = \true)
    {
        $userMetasGrouped = array();
        $userMetas = $userMetasGrouped;
        // ACF
        $acf_groups = get_posts(array('post_type' => 'acf-field-group', 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
        if (!empty($acf_groups)) {
            foreach ($acf_groups as $aacf_group) {
                $is_user_group = \in_array('user', self::get_acf_group_locations($aacf_group));
                $aacf_meta = maybe_unserialize($aacf_group->post_content);
                if ($is_user_group) {
                    $acf = get_posts(array('post_type' => 'acf-field', 'numberposts' => -1, 'post_status' => 'publish', 'post_parent' => $aacf_group->ID, 'suppress_filters' => \false));
                    if (!empty($acf)) {
                        foreach ($acf as $aacf) {
                            $aacf_meta = maybe_unserialize($aacf->post_content);
                            if ($like) {
                                $pos_key = \stripos($aacf->post_excerpt, $like);
                                $pos_name = \stripos($aacf->post_title, $like);
                                if ($pos_key === \false && $pos_name === \false) {
                                    continue;
                                }
                            }
                            $field_name = $aacf->post_title;
                            if ($info) {
                                $field_name .= ' [' . $aacf_meta['type'] . ']';
                            }
                            $userMetas[$aacf->post_excerpt] = $field_name;
                            $userMetasGrouped['ACF'][$aacf->post_excerpt] = $userMetas[$aacf->post_excerpt];
                        }
                    }
                }
            }
        }
        // MANUAL
        global $wpdb;
        if (!is_multisite()) {
            $table = $wpdb->prefix . 'usermeta';
        } else {
            $table = $wpdb->get_blog_prefix(get_main_site_id()) . 'usermeta';
        }
        if (\defined('CUSTOM_USER_META_TABLE')) {
            $table = CUSTOM_USER_META_TABLE;
        }
        $query = 'SELECT DISTINCT meta_key FROM ' . esc_sql($table);
        if ($like) {
            $query .= $wpdb->prepare(' WHERE meta_key LIKE %s', '%' . $wpdb->esc_like($like) . '%');
        }
        $results = $wpdb->get_results($query);
        if (!empty($results)) {
            $metas = array();
            foreach ($results as $key => $auser) {
                $metas[$auser->meta_key] = $auser->meta_key;
            }
            \ksort($metas);
            $manual_metas = $metas;
            foreach ($manual_metas as $ameta) {
                $userMetas[$ameta] = $ameta;
                $userMetasGrouped['META'][$ameta] = $ameta;
            }
        }
        if ($grouped) {
            return $userMetasGrouped;
        }
        return $userMetas;
    }
    public static function get_term_metas($grouped = \false, $like = '')
    {
        $termMetasGrouped = array();
        $termMetas = $termMetasGrouped;
        // ACF
        $acf_groups = get_posts(array('post_type' => 'acf-field-group', 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
        if (!empty($acf_groups)) {
            foreach ($acf_groups as $aacf_group) {
                $is_term_group = \in_array('taxonomy', self::get_acf_group_locations($aacf_group));
                $aacf_meta = maybe_unserialize($aacf_group->post_content);
                if ($is_term_group) {
                    $acf = get_posts(array('post_type' => 'acf-field', 'numberposts' => -1, 'post_status' => 'publish', 'post_parent' => $aacf_group->ID, 'suppress_filters' => \false));
                    if (!empty($acf)) {
                        foreach ($acf as $aacf) {
                            $aacf_meta = maybe_unserialize($aacf->post_content);
                            if ($like) {
                                $pos_key = \stripos($aacf->post_excerpt, $like);
                                $pos_name = \stripos($aacf->post_title, $like);
                                if ($pos_key === \false && $pos_name === \false) {
                                    continue;
                                }
                            }
                            $field_name = $aacf->post_title;
                            $termMetas[$aacf->post_excerpt] = $field_name;
                            $termMetasGrouped['ACF'][$aacf->post_excerpt] = $termMetas[$aacf->post_excerpt];
                        }
                    }
                }
            }
        }
        // MANUAL
        global $wpdb;
        $query = $wpdb->prepare("SELECT DISTINCT meta_key FROM {$wpdb->termmeta} WHERE meta_key LIKE %s", '%' . $wpdb->esc_like($like) . '%');
        $results = $wpdb->get_results($query);
        if (!empty($results)) {
            $metas = array();
            foreach ($results as $key => $aterm) {
                $metas[$aterm->meta_key] = $aterm->meta_key;
            }
            \ksort($metas);
            $manual_metas = $metas;
            foreach ($manual_metas as $ameta) {
                $termMetas[$ameta] = $ameta;
                $termMetasGrouped['META'][$ameta] = $ameta;
            }
        }
        if ($grouped) {
            return $termMetasGrouped;
        }
        return $termMetas;
    }
    public static function get_post_meta($post_id, $meta_key, $single = \false, $plugin = \true, $fallback = \true)
    {
        $meta_value = \false;
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        if (!$plugin || $fallback) {
            $meta_value = get_post_meta($post_id, $meta_key, $single);
        }
        if ($plugin) {
            // https://docs.elementor.com/article/381-elementor-integration-with-acf
            if (\DynamicContentForElementor\Helper::is_acf_active()) {
                $acf_fields = self::get_acf_fields();
                if (!empty($acf_fields) && \array_key_exists($meta_key, $acf_fields)) {
                    // https://www.advancedcustomfields.com/resources/
                    $meta_value = self::get_acf_field_value($meta_key, $post_id);
                }
            }
            // https://docs.elementor.com/article/385-elementor-integration-with-pods
            if (\DynamicContentForElementor\Helper::is_plugin_active('pods')) {
                $pods_fields = \array_keys(self::get_pods_fields());
                if (!empty($pods_fields) && \in_array($meta_key, $pods_fields, \true)) {
                    $meta_value = wp_kses_post(pods_field_display($meta_key, $post_id));
                }
            }
        }
        return $meta_value;
    }
    public static function get_post_meta_name($meta_key)
    {
        // ACF
        if (self::is_acf_active()) {
            $acf = get_field_object($meta_key);
            if ($acf) {
                return $acf['label'];
            }
        }
        // PODS
        if (self::is_plugin_active('pods')) {
            $pods = get_page_by_path($meta_key, OBJECT, '_pods_field');
            if ($pods) {
                return $pods->post_title;
            }
        }
        return $meta_key;
    }
    public static function get_meta_type($meta_key, $meta_value = null)
    {
        $meta_type = 'text';
        // ACF
        if (self::is_acf_active()) {
            global $wpdb;
            $sql = 'SELECT post_content FROM ' . $wpdb->prefix . 'posts WHERE post_excerpt = %s AND post_type = "acf-field";';
            $prepared_sql = $wpdb->prepare($sql, $meta_key);
            $acf_result = $wpdb->get_col($prepared_sql);
            if (!empty($acf_result)) {
                $acf_content = \reset($acf_result);
                $acf_field_object = maybe_unserialize($acf_content);
                if ($acf_field_object && \is_array($acf_field_object) && isset($acf_field_object['type'])) {
                    $meta_type = $acf_field_object['type'];
                }
            }
        }
        // PODS
        if (self::is_plugin_active('pods')) {
            $pods = get_page_by_path($meta_key, OBJECT, '_pods_field');
            if ($pods instanceof \WP_Post) {
                $meta_type = get_post_meta($pods->ID, 'type', \true);
            }
        }
        if ($meta_value) {
            if ($meta_type != 'text') {
                switch ($meta_type) {
                    case 'gallery':
                        return 'image';
                    case 'embed':
                        if (\strpos($meta_value, 'https://www.youtube.com/') !== \false || \strpos($meta_value, 'https://youtu.be/') !== \false) {
                            return 'youtube';
                        } else {
                            return $meta_type;
                        }
                    default:
                        return $meta_type;
                }
            } else {
                if ($meta_key == 'avatar') {
                    return 'image';
                }
                if (\is_numeric($meta_value)) {
                    return 'number';
                }
                // Validate e-mail
                if (\filter_var($meta_value, \FILTER_VALIDATE_EMAIL) !== \false) {
                    return 'email';
                }
                // Youtube url
                if (\is_string($meta_value)) {
                    if (\strpos($meta_value, 'https://www.youtube.com/') !== \false || \strpos($meta_value, 'https://youtu.be/') !== \false) {
                        return 'youtube';
                    }
                    $ext = \pathinfo($meta_value, \PATHINFO_EXTENSION);
                    if (\in_array($ext, array('mp3', 'm4a', 'ogg', 'wav', 'wma')) === \true) {
                        return 'audio';
                    }
                    if (\in_array($ext, array('mp4', 'm4v', 'webm', 'ogv', 'wmv', 'flv')) === \true) {
                        return 'video';
                    }
                    if (\substr($meta_value, 0, 7) == 'http://' || \substr($meta_value, 0, 8) == 'https://') {
                        return 'url';
                    }
                }
            }
        }
        return $meta_type;
    }
    public static function get_post_metas($grouped = \false, $like = '', $info = \true)
    {
        $postMetasGrouped = array();
        $postMetas = $postMetasGrouped;
        // REGISTERED in FUNCTION
        $cpts = self::get_public_post_types();
        foreach ($cpts as $ckey => $cvalue) {
            $cpt_metas = get_registered_meta_keys($ckey);
            if (!empty($cpt_metas)) {
                foreach ($cpt_metas as $fkey => $actpmeta) {
                    if ($like) {
                        $pos_key = \stripos($fkey, $like);
                        if ($pos_key === \false) {
                            continue;
                        }
                    }
                    $field_name = $fkey;
                    if ($info) {
                        $field_name .= ' [' . $actpmeta['type'] . ']';
                    }
                    $postMetas[$fkey] = $field_name;
                    $postMetasGrouped['CPT_' . $ckey][$fkey] = $field_name;
                }
            }
        }
        // ACF
        if (self::is_acf_active()) {
            // ACF
            $acf_groups = get_posts(array('post_type' => 'acf-field-group', 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
            if (!empty($acf_groups)) {
                foreach ($acf_groups as $aacf_group) {
                    $is_post_group = \in_array('post', self::get_acf_group_locations($aacf_group));
                    $aacf_meta = maybe_unserialize($aacf_group->post_content);
                    if ($is_post_group) {
                        $acf = get_posts(array('post_type' => 'acf-field', 'numberposts' => -1, 'post_status' => 'publish', 'post_parent' => $aacf_group->ID, 'suppress_filters' => \false));
                        if (!empty($acf)) {
                            foreach ($acf as $aacf) {
                                $aacf_meta = maybe_unserialize($aacf->post_content);
                                if ($like) {
                                    $pos_key = \stripos($aacf->post_excerpt, $like);
                                    $pos_name = \stripos($aacf->post_title, $like);
                                    if ($pos_key === \false && $pos_name === \false) {
                                        continue;
                                    }
                                }
                                $field_name = $aacf->post_title;
                                if ($info) {
                                    $field_name .= ' [' . $aacf_meta['type'] . ']';
                                }
                                $postMetas[$aacf->post_excerpt] = $field_name;
                                $postMetasGrouped['ACF'][$aacf->post_excerpt] = $postMetas[$aacf->post_excerpt];
                            }
                        }
                    }
                }
            }
        }
        // PODS
        if (self::is_plugin_active('pods')) {
            $pods = get_posts(array('post_type' => '_pods_field', 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
            if (!empty($pods)) {
                foreach ($pods as $apod) {
                    $type = get_post_meta($apod->ID, 'type', \true);
                    $field_name = $apod->post_title;
                    if ($info) {
                        $field_name .= ' [' . $type . ']';
                    }
                    $postMetas[$apod->post_name] = $field_name;
                    $postMetasGrouped['PODS'][$apod->post_name] = $postMetas[$apod->post_name];
                }
            }
        }
        // TOOLSET
        if (self::is_plugin_active('wpcf')) {
            $toolset = get_option('wpcf-fields', \false);
            if ($toolset) {
                $toolfields = maybe_unserialize($toolset);
                if (!empty($toolfields)) {
                    foreach ($toolfields as $atool) {
                        $field_name = $atool['name'];
                        if ($info) {
                            $field_name .= ' [' . $atool['type'] . ']';
                        }
                        $postMetas[$atool['meta_key']] = $field_name;
                        $postMetasGrouped['TOOLSET'][$atool['meta_key']] = $postMetas[$atool['meta_key']];
                    }
                }
            }
        }
        // MANUAL
        global $wpdb;
        $query = 'SELECT DISTINCT meta_key FROM ' . $wpdb->prefix . 'postmeta';
        if ($like) {
            $query .= ' WHERE meta_key LIKE %s';
            $prepared_query = $wpdb->prepare($query, '%' . $wpdb->esc_like($like) . '%');
        } else {
            $prepared_query = $query;
        }
        $results = $wpdb->get_results($prepared_query);
        if (!empty($results)) {
            $metas = array();
            foreach ($results as $key => $apost) {
                $metas[$apost->meta_key] = $apost->meta_key;
            }
            \ksort($metas);
            $manual_metas = \array_diff_key($metas, $postMetas);
            foreach ($manual_metas as $ameta) {
                if (\substr($ameta, 0, 8) == '_oembed_') {
                    continue;
                }
                if (!isset($postMetas[$ameta])) {
                    $postMetas[$ameta] = $ameta;
                    $postMetasGrouped['NATIVE'][$ameta] = $ameta;
                }
            }
        }
        if ($grouped) {
            return $postMetasGrouped;
        }
        return $postMetas;
    }
    public static function get_relationship_pods($grouped = \false, $like = '', $info = \true)
    {
        $postMetasGrouped = [];
        $postMetas = [];
        $pods = get_posts(array('post_type' => '_pods_field', 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
        if (!empty($pods)) {
            foreach ($pods as $apod) {
                $type = get_post_meta($apod->ID, 'type', \true);
                if ('pick' === $type) {
                    $field_name = $apod->post_title;
                    if ($info) {
                        $field_name .= ' [' . $type . ']';
                    }
                    $postMetas[$apod->post_name] = $field_name;
                    $postMetasGrouped['PODS'][$apod->post_name] = $postMetas[$apod->post_name];
                }
            }
        }
        if ($grouped) {
            return $postMetasGrouped;
        }
        return $postMetas;
    }
    /**
     * Check if a field name is not in the list of standard WordPress post fields
     *
     * @param string|null $meta_name The field name to check
     * @return boolean True if not a standard field, false if it is
     */
    public static function is_post_meta($meta_name = null)
    {
        if (!$meta_name) {
            return \true;
        }
        static $post_fields = null;
        if ($post_fields === null) {
            $post_fields = \array_flip(['ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping', 'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count']);
        }
        return !isset($post_fields[$meta_name]);
    }
    /**
     * Check if a field name is a user data
     *
     * @param string|null $field_name
     * @return boolean
     */
    public static function is_user_data($field_name = null)
    {
        if (!$field_name) {
            return \false;
        }
        static $user_fields = null;
        if ($user_fields === null) {
            $user_fields = \array_flip(['locale', 'syntax_highlighting', 'avatar', 'nickname', 'first_name', 'last_name', 'description', 'rich_editing', 'role', 'jabber', 'aim', 'yim', 'show_admin_bar_front']);
        }
        return isset($user_fields[$field_name]) || !self::is_validated_user_meta($field_name);
    }
    public static function is_validated_user_meta($meta_name = null)
    {
        if (!$meta_name) {
            return \true;
        }
        $not_allowed = array('ID', 'user_login', 'user_pass', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'user_activation_key', 'user_status', 'display_name');
        if (\in_array($meta_name, $not_allowed, \true)) {
            return \false;
        }
        return \true;
    }
    /**
     * @param string|null $meta_name
     * @return boolean
     */
    public static function is_user_meta($meta_name = null)
    {
        return self::is_validated_user_meta($meta_name);
    }
    public static function is_term_meta($meta_name = null)
    {
        $term_fields = array('term_id', 'name', 'slug', 'term_group', 'term_order');
        if ($meta_name) {
            if (\in_array($meta_name, $term_fields)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Check if a field exists in ACF
     *
     * @param string $key
     * @return boolean
     */
    public static function is_acf($key = '')
    {
        if (!$key || !\function_exists('DynamicOOOS\\acf_get_field')) {
            return \false;
        }
        return acf_get_field($key) !== \false;
    }
    /**
     * Retrieve all ACF fields, filtered by types if provided
     *
     * @param string|array<string>|null $types
     * @return array<int|string,mixed>
     */
    public static function get_acf_fields($types = null)
    {
        if (!\DynamicContentForElementor\Helper::is_acf_active()) {
            return [];
        }
        $field_groups = acf_get_field_groups();
        $all_fields = [];
        $type_lookup = null;
        if (!empty($types)) {
            $type_lookup = \is_string($types) ? [$types => \true] : \array_fill_keys($types, \true);
        }
        foreach ($field_groups as $group) {
            $fields = acf_get_fields($group['key']);
            if ($fields) {
                self::process_acf_fields($fields, $all_fields, $type_lookup);
            }
        }
        return $all_fields;
    }
    /**
     * Process ACF fields and add them to the all_fields array
     *
     * @param array<string,mixed> $fields
     * @param array<int|string,mixed> $all_fields
     * @param array<string,bool>|null $type_lookup
     * @return void
     */
    private static function process_acf_fields($fields, &$all_fields, $type_lookup)
    {
        foreach ($fields as $field) {
            if ($type_lookup === null || isset($type_lookup[$field['type']])) {
                $display_value = $field['label'] . ' [' . $field['name'] . '] (' . $field['type'] . ')';
                $all_fields[$field['name']] = $display_value;
            }
            // Process sub fields
            if (!empty($field['sub_fields'])) {
                self::process_acf_fields($field['sub_fields'], $all_fields, $type_lookup);
            }
            // Process flexible content layouts
            if ($field['type'] === 'flexible_content' && !empty($field['layouts'])) {
                foreach ($field['layouts'] as $layout) {
                    if (!empty($layout['sub_fields'])) {
                        self::process_acf_fields($layout['sub_fields'], $all_fields, $type_lookup);
                    }
                }
            }
        }
    }
    public static function get_acf_field_value_relationship_invert($acf_relation_field, $post_id = 0)
    {
        if (!$post_id) {
            $post_id = get_the_ID();
        }
        $posts_related = get_posts(array('post_type' => 'any', 'numberposts' => '-1', 'fields' => 'ids', 'meta_query' => array(array(
            'key' => $acf_relation_field,
            'value' => '"' . $post_id . '"',
            // matches exactly "123", not just 123. This prevents a match for "1234"
            'compare' => 'LIKE',
        ))));
        return $posts_related;
    }
    public static function get_acf_flexible_content_sub_fields_by_row($key, $row)
    {
        if (!self::is_acf_active()) {
            return [];
        }
        $fields = \get_field($key);
        $sub_fields = [];
        $row_counter = 0;
        if ($fields) {
            foreach ($fields as $field_key => $field_value) {
                ++$row_counter;
                if ($row_counter === $row) {
                    $sub_fields[] = $field_value;
                    break;
                }
            }
        }
        unset($sub_fields[0]['acf_fc_layout']);
        return $sub_fields[0] ?? [];
    }
    public static function get_acf_field_id($key, $multi = \false)
    {
        if (isset(self::$meta_fields[$key]['ID'])) {
            return self::$meta_fields[$key]['ID'];
        }
        global $wpdb;
        $query = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "acf-field" AND post_excerpt LIKE %s';
        $prepared_query = $wpdb->prepare($query, $wpdb->esc_like($key));
        $results = $wpdb->get_results($prepared_query);
        if (\count($results) > 1) {
            // bad acf configuration
            $field_ids = array();
            foreach ($results as $afields) {
                $field_ids[] = $afields->ID;
            }
            if ($multi) {
                return $field_ids;
            }
            return \reset($field_ids);
        }
        $result = $wpdb->get_var($prepared_query);
        if ($result) {
            self::$meta_fields[$key]['ID'] = $result;
            return $result;
        }
        return \false;
    }
    public static function get_acf_field_settings($key)
    {
        if (isset(self::$meta_fields[$key]['settings'])) {
            return self::$meta_fields[$key]['settings'];
        }
        $field = self::get_acf_field_post($key);
        if ($field) {
            $settings = maybe_unserialize($field->post_content);
            self::$meta_fields[$key]['settings'] = $settings;
            return $settings;
        }
        return \false;
    }
    public static function get_acf_field_post($key, $multi = \false)
    {
        if (isset(self::$meta_fields[$key]['post'])) {
            return self::$meta_fields[$key]['post'];
        }
        if (\is_numeric($key)) {
            $post = get_post($key);
            self::$meta_fields[$key]['post'] = $post;
            return $post;
        }
        $field_id = self::get_acf_field_id($key, $multi);
        if ($field_id) {
            if (\is_array($field_id)) {
                if ($multi) {
                    $posts = get_posts(array('post__in' => $field_id, 'posts_per_page' => -1));
                    self::$meta_fields[$key]['post'] = $posts;
                    return $posts;
                } else {
                    $field_id = \reset($field_id);
                }
            }
            if ($field_id) {
                $post = get_post($field_id);
                self::$meta_fields[$key]['post'] = $post;
                return $post;
            }
        }
        self::$meta_fields[$key]['post'] = \false;
        return \false;
    }
    public static function get_acf_field_value($idField, $id_page = null, $format = \true)
    {
        // Apply filter to allow bypassing loop check for specific use cases
        $bypass_loop_check = apply_filters('dynamicooo/acf/bypass_loop_check', \false, $idField, $id_page);
        if (!$id_page) {
            $id_page = acf_get_valid_post_id();
        }
        // Check if the ACF loop is already active, unless bypassing is enabled
        if (!$bypass_loop_check) {
            if (acf_get_loop('active')) {
                $sub_field_value = get_sub_field($idField);
                if ($sub_field_value !== \false) {
                    return $sub_field_value;
                }
            }
        }
        // Get the ACF field post data
        $dataACFieldPost = self::get_acf_field_post($idField);
        // Handle fields within a Repeater or Flexible content
        if ($dataACFieldPost) {
            $parentID = $dataACFieldPost->post_parent;
            $parent_settings = self::get_acf_field_settings($parentID);
            $custom_in_loop = apply_filters('dynamicooo/acf/in-loop', \false, $parent_settings);
            // Check if the parent field is a repeater, flexible content, or a custom loop
            if (isset($parent_settings['type']) && ($parent_settings['type'] == 'repeater' || $parent_settings['type'] == 'flexible_content') || $custom_in_loop) {
                $parent_post = get_post($parentID);
                $row = acf_get_loop('active');
                // If not already in a loop, initiate the loop
                if (!$row) {
                    if (have_rows($parent_post->post_excerpt, $id_page)) {
                        the_row();
                    }
                }
                $sub_field_value = get_sub_field($idField);
                if ($sub_field_value !== \false) {
                    return $sub_field_value;
                }
            }
        }
        // Retrieve the main field
        $theField = \get_field($idField, $id_page, $format);
        if (!$theField) {
            $locations = self::get_acf_field_locations($dataACFieldPost);
            // Check if taxonomy or other locations apply
            if (is_tax() || is_category() || is_tag() || \in_array('taxonomy', $locations)) {
                $term = get_queried_object();
                $theField = \get_field($idField, $term, $format);
            }
            // Check if author field is applicable
            if (!$theField && is_author()) {
                $author_id = get_the_author_meta('ID');
                $theField = \get_field($idField, 'user_' . $author_id, $format);
            }
            // Check if the user or options fields are applicable
            if (!$theField && \in_array('user', $locations)) {
                $user_id = get_current_user_id();
                $theField = \get_field($idField, 'user_' . $user_id, $format);
            }
            if (!$theField && \in_array('options', $locations)) {
                $theField = \get_field($idField, 'options', $format);
            }
            if (!$theField && \in_array('nav', $locations)) {
                $menu = wp_get_nav_menu_object($id_page);
                $theField = \get_field($idField, $menu, $format);
            }
        }
        return $theField;
    }
    public static function get_pods_fields($t = null)
    {
        $podsList = [];
        $podsList[0] = esc_html__('Select the field...', 'dynamic-content-for-elementor');
        $pods = get_posts(array('post_type' => '_pods_field', 'numberposts' => -1, 'post_status' => 'publish', 'suppress_filters' => \false));
        if (!empty($pods)) {
            foreach ($pods as $apod) {
                $type = get_post_meta($apod->ID, 'type', \true);
                if (!$t || $type == $t) {
                    $title = $apod->post_title;
                    if (!$t) {
                        $title .= ' [' . $type . ']';
                    }
                    $podsList[$apod->post_name] = $title;
                }
            }
        }
        return $podsList;
    }
    public static function get_toolset_fields($t = null)
    {
        $toolset_list = [];
        $toolset_list[0] = esc_html__('Select the field...', 'dynamic-content-for-elementor');
        $toolset = get_option('wpcf-fields', \false);
        if ($toolset) {
            $toolfields = maybe_unserialize($toolset);
            if (!empty($toolfields)) {
                foreach ($toolfields as $atool) {
                    $type = $atool['type'];
                    if (!$t || $type == $t) {
                        $title = $atool['name'];
                        if (!$t) {
                            $title .= ' [' . $type . ']';
                        }
                        $toolset_list[$atool['meta_key']] = $title;
                    }
                }
            }
        }
        return $toolset_list;
    }
    /**
     * Get Toolset Relationship Fields
     *
     * @return array<string, string>
     */
    public static function get_toolset_relationship_fields()
    {
        $toolset_list = [];
        $toolset_list[0] = esc_html__('Select the field...', 'dynamic-content-for-elementor');
        $relationships = toolset_get_relationships([]);
        if (!empty($relationships)) {
            foreach ($relationships as $relationship) {
                $relationship_slug = $relationship['slug'];
                $toolset_list[$relationship_slug] = $relationship['labels']['plural'];
            }
        }
        return $toolset_list;
    }
}
