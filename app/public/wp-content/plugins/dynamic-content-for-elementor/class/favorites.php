<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor;

if (!\defined('ABSPATH')) {
    exit;
}
class Favorites
{
    public const SCOPE_USER = 'user';
    public const SCOPE_COOKIE = 'cookie';
    public const DEFAULT_CACHE_TIME = 43200;
    // 12 hours
    public const CACHE_PREFIX = 'ooo';
    /**
     * Public interface to add a favorite.
     *
     * @param string $scope      The scope (e.g. 'cookie' or 'user').
     * @param string $key        The key to store favorites.
     * @param int    $post_id    The post ID to add.
     * @param int    $expiration (cookie only) Cookie expiration time, default 86400.
     * @return void
     */
    public static function add_favorite($scope, $key, $post_id, $expiration = 86400)
    {
        if (!self::is_valid_scope($scope)) {
            return;
        }
        $key = sanitize_key($key);
        switch ($scope) {
            case self::SCOPE_COOKIE:
                self::add_action_cookie($key, $post_id, $expiration);
                break;
            case self::SCOPE_USER:
                self::add_action_user($key, $post_id);
                break;
        }
    }
    /**
     * Public interface to remove a favorite.
     *
     * @param string $scope      The scope (e.g. 'cookie' or 'user').
     * @param string $key        The key to store favorites.
     * @param int    $post_id    The post ID to remove.
     * @param int    $expiration (cookie only) Cookie expiration time, default 86400.
     * @return void
     */
    public static function remove_favorite($scope, $key, $post_id, $expiration = 86400)
    {
        if (!self::is_valid_scope($scope)) {
            return;
        }
        $key = sanitize_key($key);
        switch ($scope) {
            case self::SCOPE_COOKIE:
                self::remove_action_cookie($key, $post_id, $expiration);
                break;
            case self::SCOPE_USER:
                self::remove_action_user($key, $post_id);
                break;
        }
    }
    /**
     * Check if a post has been added to favorites.
     *
     * @param string $key     The key used to store favorites.
     * @param string $scope   The scope ('user' or 'cookie').
     * @param int    $post_ID The post ID.
     * @return bool
     */
    public static function is_favorited($key, $scope, $post_ID)
    {
        if (!self::is_valid_scope($scope)) {
            return \false;
        }
        $favorites = self::get($key, $scope);
        return $favorites && \in_array($post_ID, $favorites, \false);
    }
    /**
     * Get the favorites counter for a specific post.
     *
     * @param string $key     The key used to store favorites.
     * @param string $scope   The scope ('user' or 'cookie').
     * @param int    $post_ID The post ID.
     * @return int
     */
    public static function get_counter($key, $scope, $post_ID)
    {
        if (!self::is_valid_scope($scope)) {
            return 0;
        }
        switch ($scope) {
            case self::SCOPE_USER:
                $cached_value = self::get_counter_cache($key, $post_ID);
                if ($cached_value !== \false) {
                    return $cached_value;
                }
                $count = self::get_user_counter($key);
                self::set_counter_cache($key, $post_ID, $count);
                return $count;
            case self::SCOPE_COOKIE:
                return self::get_cookie_counter($key, $post_ID);
        }
        return 0;
    }
    /**
     * Get cached counter value
     *
     * @param string $key     The key used to store favorites.
     * @param int    $post_ID The post ID.
     * @return int|false
     */
    protected static function get_counter_cache($key, $post_ID)
    {
        return get_transient(self::CACHE_PREFIX . "_{$key}_{$post_ID}");
    }
    /**
     * Set cached counter value
     *
     * @param string $key     The key used to store favorites.
     * @param int    $post_ID The post ID.
     * @param int    $count   The count to cache.
     * @return void
     */
    protected static function set_counter_cache($key, $post_ID, $count)
    {
        set_transient(self::CACHE_PREFIX . "_{$key}_{$post_ID}", $count, self::DEFAULT_CACHE_TIME);
    }
    /**
     * Delete cached counter value
     *
     * @param string $key     The key used to store favorites.
     * @param int    $post_ID The post ID.
     * @return void
     */
    protected static function delete_counter_cache($key, $post_ID)
    {
        delete_transient(self::CACHE_PREFIX . "_{$key}_{$post_ID}");
    }
    /**
     * Check if the provided scope is valid.
     *
     * @param string $scope
     * @return bool
     */
    protected static function is_valid_scope($scope)
    {
        return \in_array($scope, [self::SCOPE_USER, self::SCOPE_COOKIE], \true);
    }
    /**
     * Add an item to cookie favorites.
     *
     * @param string $key
     * @param int    $id
     * @param int    $expiration
     * @return void
     */
    protected static function add_action_cookie($key, $id, $expiration)
    {
        $current = self::get($key, self::SCOPE_COOKIE);
        $current = self::add($id, $current);
        self::save_cookie($key, $id, $current, $expiration);
    }
    /**
     * Remove an item from cookie favorites.
     * This function will remove all occurrences of the specified ID from the favorites array.
     *
     * @param string $key        The key used to store the cookie
     * @param int    $id         The post ID to remove from favorites
     * @param int    $expiration Cookie expiration time in seconds
     * @return void
     */
    protected static function remove_action_cookie($key, $id, $expiration)
    {
        $current = self::get($key, self::SCOPE_COOKIE);
        if (empty($current)) {
            return;
        }
        // Ensure consistent integer type for comparison
        $id = \intval($id);
        $current = \array_map('intval', $current);
        // Remove all occurrences of the ID from the array
        $current = \array_filter($current, function ($value) use($id) {
            return $value !== $id;
        });
        self::save_cookie($key, $id, $current, $expiration);
    }
    /**
     * Add an item to user meta favorites.
     *
     * @param string $key
     * @param int|string $id
     * @return void
     */
    protected static function add_action_user($key, $id)
    {
        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            return;
        }
        if (\in_array($key, \DynamicContentForElementor\Helper::NOT_ALLOWED_USER_FIELDS, \true)) {
            wp_send_json_error('Invalid meta key');
        }
        $current = self::get($key, self::SCOPE_USER);
        $current = self::add($id, $current);
        update_user_meta($current_user_id, $key, $current);
        $post_id = get_the_ID();
        if ($post_id !== \false) {
            self::delete_counter_cache($key, $post_id);
        }
    }
    /**
     * Remove an item from user meta favorites.
     *
     * @param string $key
     * @param int    $id
     * @return void
     */
    protected static function remove_action_user($key, $id)
    {
        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            return;
        }
        if (\in_array($key, \DynamicContentForElementor\Helper::NOT_ALLOWED_USER_FIELDS, \true)) {
            wp_send_json_error('Invalid meta key');
        }
        $current = self::get($key, self::SCOPE_USER);
        if (empty($current)) {
            return;
        }
        $id_position = \array_search($id, $current, \true);
        if (\false === $id_position) {
            return;
        }
        $current = self::remove((int) $id_position, $current);
        update_user_meta($current_user_id, $key, $current);
        $post_id = get_the_ID();
        if ($post_id !== \false) {
            self::delete_counter_cache($key, $post_id);
        }
    }
    /**
     * Get favorites based on scope.
     *
     * @param string $key
     * @param string $scope
     * @param int|null $user_id Optional user ID for user scope
     * @return array<int|string>|null
     */
    public static function get($key, $scope, $user_id = null)
    {
        if (!self::is_valid_scope($scope)) {
            return null;
        }
        $current = [];
        switch ($scope) {
            case self::SCOPE_USER:
                if ($user_id === null) {
                    $user_id = get_current_user_id();
                }
                $key = \DynamicContentForElementor\Helper::validate_user_fields($key);
                if (empty($key)) {
                    return null;
                }
                $current = get_user_meta($user_id, $key, \true);
                if (!\is_array($current)) {
                    $current = [];
                }
                break;
            case self::SCOPE_COOKIE:
                if (isset($_COOKIE[$key])) {
                    $cookie_value = \urldecode($_COOKIE[$key]);
                    $cookie_value = sanitize_text_field($cookie_value);
                    $current = \DynamicContentForElementor\Helper::str_to_array(',', $cookie_value);
                    $current = \DynamicContentForElementor\Helper::validate_post_id($current);
                }
                break;
        }
        if (\DynamicContentForElementor\Helper::is_wpml_active() && !empty($current)) {
            $translated = \DynamicContentForElementor\Helper::wpml_translate_object_id($current);
            return \is_array($translated) ? $translated : [$translated];
        }
        return \is_array($current) ? $current : null;
    }
    /**
     * Add an ID to the favorites list.
     *
     * @param int|string $id
     * @param array<mixed>|null $current
     * @return array<mixed>
     */
    protected static function add($id, $current)
    {
        $id = \intval($id);
        if (empty($current)) {
            $current = [$id];
        } else {
            $current[] = $id;
        }
        return $current;
    }
    /**
     * Remove an ID from the favorites list.
     *
     * @param int $position
     * @param array<mixed> $current
     * @return array<mixed>
     */
    protected static function remove($position, $current)
    {
        unset($current[$position]);
        return $current;
    }
    /**
     * Save favorites in cookies.
     *
     * @param string $key
     * @param int $id
     * @param array<mixed> $current
     * @param int $expiration
     * @return void
     */
    protected static function save_cookie($key, $id, $current, $expiration)
    {
        $http_host = 'localhost' === $_SERVER['HTTP_HOST'] ? '' : sanitize_text_field($_SERVER['HTTP_HOST']);
        $current_string = \implode(',', $current);
        @\setcookie($key, $current_string, $expiration, '/', $http_host);
        self::update_cookie_counter($key, $id, \in_array($id, $current));
    }
    /**
     * Get the counter for user favorites.
     *
     * @param string $key
     * @return int
     */
    protected static function get_user_counter($key = '')
    {
        $post_id = get_the_ID();
        if (!$post_id) {
            return 0;
        }
        $users_with_favorite = get_users(['meta_key' => $key, 'meta_value' => (string) $post_id, 'meta_compare' => 'LIKE', 'fields' => 'ID']);
        return \count($users_with_favorite);
    }
    /**
     * Get the counter for cookie favorites
     *
     * @param string $key     The key used to store favorites
     * @param int    $post_ID The post ID to check
     * @return int
     */
    protected static function get_cookie_counter($key, $post_ID)
    {
        $cookies = get_option('dce_favorite_cookies', []);
        if (isset($cookies[$key][$post_ID])) {
            return \intval($cookies[$key][$post_ID]);
        }
        return 0;
    }
    /**
     * Update the cookie counter
     *
     * @param string $key     The key used to store favorites
     * @param int    $post_ID The post ID to update
     * @param bool   $increment True to increment, false to decrement
     * @return void
     */
    protected static function update_cookie_counter($key, $post_ID, $increment = \true)
    {
        $cookies = get_option('dce_favorite_cookies', []);
        if (!isset($cookies[$key])) {
            $cookies[$key] = [];
        }
        if (!isset($cookies[$key][$post_ID])) {
            $cookies[$key][$post_ID] = 0;
        }
        if ($increment) {
            ++$cookies[$key][$post_ID];
        } else {
            --$cookies[$key][$post_ID];
            // Remove the counter if it reaches zero
            if ($cookies[$key][$post_ID] <= 0) {
                unset($cookies[$key][$post_ID]);
                if (empty($cookies[$key])) {
                    unset($cookies[$key]);
                }
            }
        }
        update_option('dce_favorite_cookies', $cookies);
    }
    /**
     * Validates the content based on the specified scope.
     *
     * @param mixed $content The content to validate
     * @param string $scope The scope (user or cookie)
     * @return bool True if the content is valid for the specified scope
     */
    protected static function is_valid_favorites_content($content, $scope)
    {
        if (!self::is_valid_scope($scope)) {
            return \false;
        }
        switch ($scope) {
            case self::SCOPE_USER:
                return self::is_valid_user_meta_content($content);
            case self::SCOPE_COOKIE:
                return self::is_valid_cookie_content($content);
            default:
                return \false;
        }
    }
    /**
     * Validates if the user meta content is a valid array of favorite IDs.
     *
     * @param mixed $content The content to validate
     * @return bool True if the content is a valid array of favorite IDs
     */
    protected static function is_valid_user_meta_content($content)
    {
        if (empty($content)) {
            return \false;
        }
        if (!\is_array($content)) {
            return \false;
        }
        foreach ($content as $post_id) {
            if (!\is_numeric($post_id)) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Validates if the cookie content is a valid string of favorite IDs.
     *
     * @param mixed $content The content to validate
     * @return bool True if the content is a valid string of favorite IDs
     */
    protected static function is_valid_cookie_content($content)
    {
        if (empty($content) || !\is_string($content)) {
            return \false;
        }
        $ids = \explode(',', $content);
        foreach ($ids as $id) {
            if (!\is_numeric(\trim($id))) {
                return \false;
            }
        }
        return \true;
    }
    /**
     * Remove all favorites for a specific key and scope.
     *
     * @param string $scope      The scope (e.g. 'cookie' or 'user').
     * @param string $key        The key to store favorites.
     * @return void
     */
    public static function clear_favorites($scope, $key)
    {
        if (!self::is_valid_scope($scope)) {
            return;
        }
        $key = sanitize_key($key);
        switch ($scope) {
            case self::SCOPE_COOKIE:
                if (isset($_COOKIE[$key]) && !self::is_valid_favorites_content($_COOKIE[$key], $scope)) {
                    return;
                }
                $http_host = 'localhost' === $_SERVER['HTTP_HOST'] ? '' : sanitize_text_field($_SERVER['HTTP_HOST']);
                @\setcookie($key, '', \time() - 3600, '/', $http_host);
                break;
            case self::SCOPE_USER:
                $current_user_id = get_current_user_id();
                if (!$current_user_id) {
                    return;
                }
                if (\in_array($key, \DynamicContentForElementor\Helper::NOT_ALLOWED_USER_FIELDS, \true)) {
                    wp_send_json_error('Invalid meta key');
                }
                $current_meta = get_user_meta($current_user_id, $key, \true);
                if (self::is_valid_favorites_content($current_meta, $scope)) {
                    delete_user_meta($current_user_id, $key);
                    $post_id = get_the_ID();
                    if ($post_id !== \false) {
                        self::delete_counter_cache($key, $post_id);
                    }
                }
                break;
        }
    }
}
