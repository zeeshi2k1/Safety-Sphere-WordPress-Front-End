<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
use DynamicContentForElementor\Helper;
class Archive extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        $element->add_control('dce_visibility_archive', ['label' => esc_html__('Archive Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => static::get_whitelist_archive_functions(), 'separator' => 'before']);
        $taxonomies = Helper::get_taxonomies();
        // TODO: specify what Category, Tag or CustomTax
        $element->add_control('dce_visibility_archive_tax', ['label' => esc_html__('Taxonomy', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => $taxonomies, 'multiple' => \false, 'separator' => 'before', 'condition' => ['dce_visibility_archive' => 'is_tax']]);
        foreach ($taxonomies as $tkey => $atax) {
            if ($tkey) {
                switch ($tkey) {
                    case 'post_tag':
                        $condition = ['dce_visibility_archive' => 'is_tag'];
                        break;
                    case 'category':
                        $condition = ['dce_visibility_archive' => 'is_category'];
                        break;
                    default:
                        $condition = ['dce_visibility_archive' => 'is_tax', 'dce_visibility_archive_tax' => $tkey];
                }
                $element->add_control('dce_visibility_archive_term_' . $tkey, ['label' => $atax . ' ' . esc_html__('Terms', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Term Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'terms', 'object_type' => $tkey, 'description' => esc_html__('Visible if current post is related to these terms', 'dynamic-content-for-elementor'), 'multiple' => \true, 'condition' => $condition]);
            }
        }
        $element->add_control('dce_visibility_term', ['label' => esc_html__('Taxonomy Term', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $element->add_control('dce_visibility_term_parent', ['label' => esc_html__('Is Parent', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for term with children.', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_term_root', ['label' => esc_html__('Is Root', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for term of first level (without parent).', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_term_leaf', ['label' => esc_html__('Is Leaf', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for terms in last level (without children).', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_term_node', ['label' => esc_html__('Is Node', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for terms in intermedial level (with parent and children).', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_term_child', ['label' => esc_html__('Has Parent', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for terms which are children (with a parent).', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_term_sibling', ['label' => esc_html__('Has Siblings', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for terms with siblings.', 'dynamic-content-for-elementor')]);
        $element->add_control('dce_visibility_term_count', ['label' => esc_html__('Has Posts', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Triggered for terms has related Posts count.', 'dynamic-content-for-elementor')]);
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
        if (!empty($settings['dce_visibility_archive'])) {
            $context_archive = \false;
            $archive = $settings['dce_visibility_archive'];
            switch ($archive) {
                case 'is_post_type_archive':
                case 'is_tax':
                case 'is_category':
                case 'is_tag':
                case 'is_author':
                case 'is_date':
                case 'is_year':
                case 'is_month':
                case 'is_day':
                case 'is_search':
                    if (\in_array($archive, \array_keys(self::get_whitelist_archive_functions()), \true) && \is_callable($archive)) {
                        $context_archive = \call_user_func($archive);
                    }
                    break;
                default:
                    $context_archive = is_archive();
            }
            if ($context_archive) {
                $context_archive_advanced = \false;
                $queried_object = get_queried_object();
                $is_wpml_active = Helper::is_wpml_active();
                $archive_type = '';
                $term_ids = [];
                if ($queried_object instanceof \WP_Term) {
                    switch ($archive) {
                        case 'is_tax':
                            if ($settings['dce_visibility_archive_tax'] && $queried_object->taxonomy == $settings['dce_visibility_archive_tax']) {
                                $archive_type = $settings['dce_visibility_archive_tax'];
                                $term_ids = $settings['dce_visibility_archive_term_' . $archive_type];
                            }
                            break;
                        case 'is_category':
                            if ($queried_object->taxonomy == 'category') {
                                $archive_type = 'category';
                                $term_ids = $settings['dce_visibility_archive_term_category'];
                            }
                            break;
                        case 'is_tag':
                            if ($queried_object->taxonomy == 'post_tag') {
                                $archive_type = 'post_tag';
                                $term_ids = $settings['dce_visibility_archive_term_post_tag'];
                            }
                            break;
                    }
                }
                if ($is_wpml_active && !empty($archive_type)) {
                    $term_ids = Helper::wpml_translate_object_id_by_type($term_ids, $archive_type);
                }
                if (empty($term_ids) || $queried_object instanceof \WP_Term && \in_array($queried_object->term_id, $term_ids)) {
                    $context_archive_advanced = \true;
                }
                ++$triggers_n;
                if ($context_archive_advanced) {
                    $conditions['dce_visibility_archive'] = esc_html__('Archive', 'dynamic-content-for-elementor');
                }
            }
        }
        // TERMS
        $term = get_queried_object();
        if ($term instanceof \WP_Term) {
            // is parent
            if (!empty($settings['dce_visibility_term_root'])) {
                $triggers['dce_visibility_term_root'] = esc_html__('Term is Root', 'dynamic-content-for-elementor');
                ++$triggers_n;
                if (!$term->parent) {
                    $conditions['dce_visibility_term_root'] = esc_html__('Term is Root', 'dynamic-content-for-elementor');
                }
            }
            if (!empty($settings['dce_visibility_term_parent'])) {
                $triggers['dce_visibility_term_parent'] = esc_html__('Term is Parent', 'dynamic-content-for-elementor');
                $children = get_term_children($term->term_id, $term->taxonomy);
                ++$triggers_n;
                if (!empty($children) && \is_array($children)) {
                    $conditions['dce_visibility_term_parent'] = esc_html__('Term is Parent', 'dynamic-content-for-elementor');
                }
            }
            if (!empty($settings['dce_visibility_term_leaf'])) {
                $triggers['dce_visibility_term_leaf'] = esc_html__('Term is Leaf', 'dynamic-content-for-elementor');
                $children = get_term_children($term->term_id, $term->taxonomy);
                ++$triggers_n;
                if (empty($children)) {
                    $conditions['dce_visibility_term_leaf'] = esc_html__('Term is Leaf', 'dynamic-content-for-elementor');
                }
            }
            if (!empty($settings['dce_visibility_term_node'])) {
                $triggers['dce_visibility_term_node'] = esc_html__('Term is Node', 'dynamic-content-for-elementor');
                if ($term->parent) {
                    $children = get_term_children($term->term_id, $term->taxonomy);
                    ++$triggers_n;
                    if (!empty($children)) {
                        $conditions['dce_visibility_term_node'] = esc_html__('Term is Node', 'dynamic-content-for-elementor');
                    }
                }
            }
            if (!empty($settings['dce_visibility_term_child'])) {
                $triggers['dce_visibility_term_child'] = esc_html__('Term has Parent', 'dynamic-content-for-elementor');
                ++$triggers_n;
                if ($term->parent) {
                    $conditions['dce_visibility_term_child'] = esc_html__('Term has Parent', 'dynamic-content-for-elementor');
                }
            }
            if (!empty($settings['dce_visibility_term_sibling'])) {
                $triggers['dce_visibility_term_sibling'] = esc_html__('Term has Siblings', 'dynamic-content-for-elementor');
                $siblings = \false;
                if ($term->parent) {
                    $siblings = get_term_children($term->parent, $term->taxonomy);
                } else {
                    $args = ['taxonomy' => $term->taxonomy, 'parent' => 0, 'hide_empty' => \false];
                    $siblings = get_terms($args);
                }
                ++$triggers_n;
                if (!empty($siblings) && \is_array($siblings) && \count($siblings) > 1) {
                    $conditions['dce_visibility_term_sibling'] = esc_html__('Term has Siblings', 'dynamic-content-for-elementor');
                }
            }
            if (!empty($settings['dce_visibility_term_count'])) {
                $triggers['dce_visibility_term_count'] = esc_html__('Term Posts', 'dynamic-content-for-elementor');
                ++$triggers_n;
                if ($term->count) {
                    $conditions['dce_visibility_term_count'] = esc_html__('Term Posts', 'dynamic-content-for-elementor');
                }
            }
        }
    }
    /**
     * Archive functions
     *
     * @return array<string,string>
     */
    public static function get_whitelist_archive_functions()
    {
        return ['is_blog' => esc_html__('Home blog (latest posts)', 'dynamic-content-for-elementor'), 'posts_page' => esc_html__('Posts page', 'dynamic-content-for-elementor'), 'is_tax' => esc_html__('Taxonomy', 'dynamic-content-for-elementor'), 'is_category' => esc_html__('Category', 'dynamic-content-for-elementor'), 'is_tag' => esc_html__('Tag', 'dynamic-content-for-elementor'), 'is_author' => esc_html__('Author', 'dynamic-content-for-elementor'), 'is_date' => esc_html__('Date', 'dynamic-content-for-elementor'), 'is_year' => esc_html__('Year', 'dynamic-content-for-elementor'), 'is_month' => esc_html__('Month', 'dynamic-content-for-elementor'), 'is_day' => esc_html__('Day', 'dynamic-content-for-elementor'), 'is_time' => esc_html__('Time', 'dynamic-content-for-elementor'), 'is_new_day' => esc_html__('New Day', 'dynamic-content-for-elementor'), 'is_search' => esc_html__('Search', 'dynamic-content-for-elementor'), 'is_paged' => esc_html__('Paged', 'dynamic-content-for-elementor'), 'is_main_query' => esc_html__('Main Query', 'dynamic-content-for-elementor'), 'in_the_loop' => esc_html__('In the Loop', 'dynamic-content-for-elementor')];
    }
}
