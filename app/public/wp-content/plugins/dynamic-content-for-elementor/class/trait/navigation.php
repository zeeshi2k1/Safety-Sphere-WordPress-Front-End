<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor;

trait Navigation
{
    public static function numeric_posts_nav()
    {
        // TODO: Used only on archive.php and search.php pages. To be deprecated
        if (is_singular()) {
            return;
        }
        global $wp_query;
        /** Stop execution if there's only 1 page */
        if ($wp_query->max_num_pages <= 1) {
            return;
        }
        $paged = get_query_var('paged') ? absint(get_query_var('paged')) : 1;
        $max = \intval($wp_query->max_num_pages);
        $links = [];
        //phpstan
        $prev_arrow = is_rtl() ? 'fa fa-angle-right' : 'fa fa-angle-left';
        $next_arrow = is_rtl() ? 'fa fa-angle-left' : 'fa fa-angle-right';
        /** Add current page to the array */
        if ($paged >= 1) {
            $links[] = $paged;
        }
        /** Add the pages around the current page to the array */
        if ($paged >= 3) {
            $links[] = $paged - 1;
            $links[] = $paged - 2;
        }
        if ($paged + 2 <= $max) {
            $links[] = $paged + 2;
            $links[] = $paged + 1;
        }
        echo '<div class="navigation posts-navigation"><ul class="page-numbers">' . "\n";
        /** Previous Post Link */
        if (get_previous_posts_link()) {
            \printf('<li>%s</li>' . "\n", get_previous_posts_link());
        }
        /** Link to first page, plus ellipses if necessary */
        if (!\in_array(1, $links)) {
            $class = 1 == $paged ? ' class="current"' : '';
            \printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link(1)), '1');
            if (!\in_array(2, $links)) {
                echo '<li>…</li>';
            }
        }
        /** Link to current page, plus 2 pages in either direction if necessary */
        \sort($links);
        foreach ((array) $links as $link) {
            $class = $paged == $link ? ' class="current"' : '';
            \printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($link)), $link);
        }
        /** Link to last page, plus ellipses if necessary */
        if (!\in_array($max, $links)) {
            if (!\in_array($max - 1, $links)) {
                echo '<li>…</li>' . "\n";
            }
            $class = $paged == $max ? ' class="current"' : '';
            \printf('<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url(get_pagenum_link($max)), $max);
        }
        /** Next Post Link */
        if (get_next_posts_link()) {
            \printf('<li>%s</li>' . "\n", get_next_posts_link());
        }
        echo '</ul></div>' . "\n";
    }
    // Search and Filter Pro - Navigation
    public static function get_wp_link_page_sf($i)
    {
        return get_pagenum_link($i);
    }
    public static function get_wp_link_page($i)
    {
        if (!is_singular() || is_front_page()) {
            return get_pagenum_link($i);
        }
        // Based on wp-includes/post-template.php:957 `_wp_link_page`.
        global $wp_rewrite;
        $id_page = \DynamicContentForElementor\Helper::get_the_id();
        $post = get_post();
        $query_args = [];
        $url = get_permalink($id_page);
        if ($i > 1) {
            if ('' === get_option('permalink_structure') || \in_array($post->post_status, ['draft', 'pending'])) {
                $url = add_query_arg('page', $i, $url);
            } elseif (get_option('show_on_front') === 'page' && (int) get_option('page_on_front') === $post->ID) {
                $url = trailingslashit($url) . user_trailingslashit("{$wp_rewrite->pagination_base}/" . $i, 'single_paged');
            } else {
                $url = trailingslashit($url) . user_trailingslashit($i, 'single_paged');
            }
        }
        if (is_preview()) {
            if ('draft' !== $post->post_status && isset($_GET['preview_id'], $_GET['preview_nonce'])) {
                $query_args['preview_id'] = sanitize_text_field(wp_unslash($_GET['preview_id']));
                $query_args['preview_nonce'] = sanitize_text_field(wp_unslash($_GET['preview_nonce']));
            }
            $url = get_preview_post_link($post, $query_args, $url);
        }
        return $url;
    }
    public static function get_next_pagination()
    {
        $paged = \max(1, get_query_var('paged'), get_query_var('page'));
        if (empty($paged)) {
            $paged = 1;
        }
        $link_next = self::get_wp_link_page($paged + 1);
        return $link_next;
    }
    // Next Pagination for Search&Filter Pro
    public static function get_next_pagination_sf()
    {
        $paged = \max(1, get_query_var('paged'));
        if (empty($paged)) {
            $paged = 1;
        }
        $link_next = self::get_wp_link_page_sf($paged + 1);
        return $link_next;
    }
}
