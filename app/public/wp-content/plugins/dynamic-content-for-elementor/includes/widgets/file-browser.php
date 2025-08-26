<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use DynamicContentForElementor\Controls\Group_Control_Filters_HSB;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class FileBrowser extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public $file_metadata = [];
    // save it in a hidden field in json, values only for this post
    /**
     * @return array<string>
     */
    public function get_style_depends()
    {
        return ['dce-filebrowser', 'dce-file-icon'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_filebrowser', ['label' => esc_html__('FileBrowser', 'dynamic-content-for-elementor')]);
        $this->add_control('path_selection', ['label' => esc_html__('Select path', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['uploads' => ['title' => esc_html__('Uploads', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-upload'], 'custom' => ['title' => esc_html__('Custom', 'dynamic-content-for-elementor'), 'icon' => 'eicon-custom'], 'media' => ['title' => esc_html__('Media Library', 'dynamic-content-for-elementor'), 'icon' => 'eicon-image'], 'taxonomy' => ['title' => esc_html__('Taxonomy', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-tags'], 'post' => ['title' => esc_html__('Post Medias', 'dynamic-content-for-elementor'), 'icon' => 'eicon-post']], 'default' => 'uploads', 'toggle' => \false]);
        $this->add_control('folder_custom', ['label' => esc_html__('Custom Path', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => 'myfolder/docs', 'description' => esc_html__('A custom path from site root', 'dynamic-content-for-elementor'), 'default' => 'wp-content/uploads', 'condition' => ['path_selection' => ['custom']]]);
        $this->add_control('medias_field', ['label' => esc_html__('Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or Field Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'fields', 'object_type' => ['post', 'user', 'term'], 'condition' => ['path_selection' => 'media']]);
        $this->add_control('medias', ['label' => esc_html__('Choose Files', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::WYSIWYG, 'condition' => ['path_selection' => 'media', 'medias_field' => '']]);
        $this->add_control('remove_media', ['label' => esc_html__('Remove All Files', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::BUTTON, 'event' => 'dceFileBrowser:removeMedia', 'text' => esc_html__('Remove', 'dynamic-content-for-elementor'), 'description' => '', 'condition' => ['path_selection' => 'media', 'medias_field' => '']]);
        $this->add_control('folder', ['label' => esc_html__('Root Folder', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => $this->getFolders(), 'default' => \date('Y'), 'description' => esc_html__('You can add more files through the MediaLibrary or via FTP', 'dynamic-content-for-elementor'), 'condition' => ['path_selection' => 'uploads']]);
        foreach ($this->getFolders() as $key => $value) {
            $subfolders = $this->getFoldersRic(self::getRootDir($value), \false, $value);
            $subfolders = \array_reverse($subfolders, \true);
            $subfolders['/'] = '/';
            $subfolders = \array_reverse($subfolders, \true);
            $this->add_control('subfolder_' . $value, ['label' => esc_html__('SubFolder', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => $subfolders, 'default' => '/', 'description' => esc_html__('Select specific subfolder or root', 'dynamic-content-for-elementor'), 'condition' => ['path_selection' => 'uploads', 'folder' => $value]]);
        }
        $taxonomies = Helper::get_taxonomies(\false, 'attachment');
        $this->add_control('taxonomy', ['label' => esc_html__('Select Taxonomy', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['' => esc_html__('None', 'dynamic-content-for-elementor')] + $taxonomies, 'description' => esc_html__('Use selected taxonomy as folder', 'dynamic-content-for-elementor'), 'label_block' => \true, 'condition' => ['path_selection' => 'taxonomy']]);
        if (!empty($taxonomies)) {
            foreach ($taxonomies as $tkey => $atax) {
                if ($tkey) {
                    $terms = Helper::get_taxonomy_terms($tkey, \true);
                    $this->add_control('terms_' . $tkey, ['label' => esc_html__('Terms', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => ['' => esc_html__('All', 'dynamic-content-for-elementor')] + $terms, 'description' => esc_html__('Filter results by selected taxonomy terms', 'dynamic-content-for-elementor'), 'label_block' => \true, 'condition' => ['taxonomy' => $tkey, 'path_selection' => 'taxonomy']]);
                }
            }
        }
        $this->add_control('private_access', ['label' => esc_html__('Set Private access', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'description' => esc_html__('WARNING: direct access will be disabled for ALL files in folder and subfolder', 'dynamic-content-for-elementor'), 'condition' => ['path_selection' => ['custom', 'uploads']]]);
        $this->add_control('user_role', ['label' => esc_html__('Roles', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => wp_roles()->get_names() + ['visitor' => 'Visitor (User not logged in)'], 'placeholder' => esc_html__('Roles', 'dynamic-content-for-elementor'), 'label_block' => \true, 'multiple' => \true, 'description' => esc_html__('Limit visualization to specific user roles', 'dynamic-content-for-elementor'), 'condition' => ['private_access!' => '', 'path_selection' => ['custom', 'uploads']]]);
        $this->add_control('user_redirect', ['label' => esc_html__('Redirect', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::URL, 'placeholder' => esc_html__('/subscribe-plan-page', 'dynamic-content-for-elementor'), 'description' => esc_html__('Redirect Unauthorized users', 'dynamic-content-for-elementor'), 'condition' => ['private_access!' => '', 'path_selection' => ['custom', 'uploads']]]);
        $this->add_control('title', ['label' => esc_html__('Show folder title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'condition' => ['path_selection!' => 'media']]);
        $this->add_control('title_size', ['label' => esc_html__('Title HTML Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_html_tags(), 'default' => 'h4', 'condition' => ['path_selection!' => 'media', 'title!' => '']]);
        $this->add_control('empty', ['label' => esc_html__('Show empty folders', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['path_selection' => ['uploads', 'custom']]]);
        $this->add_control('resized', ['label' => esc_html__('Show resized images', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('WordPress automatically create for every uploaded image many resized version (e.g.:my-image-150x150.png, another-img-310x250.jpg), if you want to view them, enable this setting', 'dynamic-content-for-elementor'), 'condition' => ['path_selection' => ['uploads', 'custom']]]);
        $this->add_control('order', ['label' => esc_html__('Order', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => array(\SCANDIR_SORT_NONE => esc_html__('None', 'dynamic-content-for-elementor'), 0 => esc_html__('Ascending', 'dynamic-content-for-elementor'), 1 => esc_html__('Descending', 'dynamic-content-for-elementor')), 'default' => '0', 'description' => esc_html__('Select file order', 'dynamic-content-for-elementor'), 'condition' => ['path_selection' => ['uploads', 'custom']]]);
        $this->add_control('file_type', ['label' => esc_html__('Filter by file extension', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => 'gif, jpg, png', 'description' => esc_html__('Show only specific file types. Separate each extension by comma', 'dynamic-content-for-elementor'), 'condition' => ['path_selection!' => 'media']]);
        $this->add_control('file_type_show', ['label' => esc_html__('Show/Hide specified file types', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'label_off' => esc_html__('Hide', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Show', 'dynamic-content-for-elementor'), 'default' => 'yes', 'condition' => ['file_type!' => '']]);
        $this->add_control('img_icon', ['label' => esc_html__('Use thumbnail for images', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('If the file is an image then use it\'s thumb as icon', 'dynamic-content-for-elementor'), 'condition' => ['path_selection' => 'media']]);
        $this->add_control('search', ['label' => esc_html__('Quick search form', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before']);
        $this->add_control('enable_metadata', ['separator' => 'before', 'label' => esc_html__('Metadata info', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $this->end_controls_section();
        $this->start_controls_section('section_file_form', ['label' => esc_html__('Search Form', 'dynamic-content-for-elementor'), 'condition' => ['search!' => '']]);
        $this->add_control('search_text', ['label' => esc_html__('Search Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Quick Search', 'dynamic-content-for-elementor')]);
        $this->add_control('search_text_size', ['label' => esc_html__('Form Title HTML Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_html_tags(), 'default' => 'h4', 'condition' => ['search_text!' => '']]);
        $this->add_control('search_notice', ['label' => esc_html__('Search Notice', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('* at least 3 characters', 'dynamic-content-for-elementor'), 'placeholder' => esc_html__('* at least 3 characters', 'dynamic-content-for-elementor')]);
        $this->add_control('search_quick', ['label' => esc_html__('Quick Search', 'dynamic-content-for-elementor'), 'description' => esc_html__('Search on input change, no buttons needed', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $this->add_control('search_find_text', ['label' => esc_html__('Find Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Find', 'dynamic-content-for-elementor'), 'condition' => ['search_quick' => '']]);
        $this->add_control('search_reset', ['label' => esc_html__('Reset Button', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['search_quick' => '']]);
        $this->add_control('search_reset_text', ['label' => esc_html__('Reset Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Reset', 'dynamic-content-for-elementor'), 'condition' => ['search_quick' => '', 'search_reset!' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_file_metadata', ['label' => esc_html__('Metadata', 'dynamic-content-for-elementor'), 'condition' => ['enable_metadata!' => '']]);
        $this->add_control('extension', ['label' => esc_html__('Show file extension', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_metadata' => 'yes']]);
        $this->add_control('enable_metadata_size', ['label' => esc_html__('Show file size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_metadata' => 'yes']]);
        $this->add_control('enable_metadata_hits', ['label' => esc_html__('Add a download counter for statistics', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_metadata' => 'yes']]);
        $this->add_control('enable_metadata_description', ['label' => esc_html__('Add description to files', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_metadata' => 'yes']]);
        $this->add_control('enable_metadata_wp_description', ['label' => esc_html__('Use WP Caption', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('Use Media Caption as description if file is managed by native WP interface', 'dynamic-content-for-elementor'), 'condition' => ['enable_metadata' => 'yes', 'enable_metadata_description' => 'yes']]);
        $this->add_control('enable_metadata_custom_title', ['label' => esc_html__('Set custom title to files and folders', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_metadata' => 'yes']]);
        $this->add_control('enable_metadata_wp_title', ['label' => esc_html__('Use WP Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('Use Media Title if file is managed by native WP interface', 'dynamic-content-for-elementor'), 'condition' => ['enable_metadata' => 'yes', 'enable_metadata_custom_title' => 'yes']]);
        $this->add_control('enable_metadata_hide', ['label' => esc_html__('Hide some files and folders', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_metadata' => 'yes']]);
        $this->add_control('enable_metadata_hide_reverse', ['label' => esc_html__('Invert: show only selected files and folders', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('If enabled you select the exact file and folder to show, all other file and folders will be hidden', 'dynamic-content-for-elementor'), 'condition' => ['enable_metadata' => 'yes', 'enable_metadata_hide' => 'yes']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_title', ['label' => esc_html__('Title', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['title' => 'yes']]);
        $this->add_responsive_control('title_align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-right'], 'justify' => ['title' => esc_html__('Justified', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-justify']], 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-filebrowser-title' => 'text-align: {{VALUE}};']]);
        $this->add_control('title_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-filebrowser-title' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'title_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-filebrowser-title']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'title_text_shadow', 'selector' => '{{WRAPPER}} .dce-filebrowser-title']);
        $this->add_control('title_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-filebrowser-title' => 'padding-bottom: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_folders', ['label' => esc_html__('Folders', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('foldername_color', ['label' => esc_html__('Name Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} a .dce-dir-title' => 'color: {{VALUE}};']]);
        $this->add_control('foldername_color_hover', ['label' => esc_html__('Name Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} a:hover .dce-dir-title' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'foldername_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} a .dce-dir-title']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'foldername_text_shadow', 'selector' => '{{WRAPPER}} a .dce-dir-title']);
        $this->add_control('heading_folders_border', ['label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('folder_border_type', ['label' => esc_html__('Border type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'solid', 'options' => ['solid' => esc_html__('Solid', 'dynamic-content-for-elementor'), 'dashed' => esc_html__('Dashed', 'dynamic-content-for-elementor'), 'dotted' => esc_html__('Dotted', 'dynamic-content-for-elementor'), 'double' => esc_html__('Double', 'dynamic-content-for-elementor'), 'none' => esc_html__('None', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} .dce-list li.dir' => 'border-bottom-style: {{VALUE}}']]);
        $this->add_control('folder_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['folder_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-list li.dir' => 'border-bottom-color: {{VALUE}};']]);
        $this->add_control('folder_border_stroke', ['label' => esc_html__('Border weight', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 10]], 'condition' => ['folder_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-list li.dir' => 'border-bottom-width: {{SIZE}}{{UNIT}};']]);
        $this->add_control('folder_list_space', ['label' => esc_html__('Row Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => 3, 'max' => 100]], 'separator' => 'before', 'selectors' => ['{{WRAPPER}} .dce-list li.dir' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('folder_list_padding', ['label' => esc_html__('Space around', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'separator' => 'after', 'selectors' => ['{{WRAPPER}} .dce-list-root' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        // Icons
        $this->add_control('heading_folders_icon', ['label' => esc_html__('Icons', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_responsive_control('folder_icon_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 40], 'range' => ['px' => ['min' => 0, 'max' => 180]], 'selectors' => ['{{WRAPPER}} .dce-list .fiv-icon-folder' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('folder_icon_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => -50, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-list .fiv-icon-folder' => 'margin-right: {{SIZE}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Filters_HSB::get_type(), ['name' => 'icon_hue_filters', 'label' => esc_html__('Color (HSB)', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-list .fiv-icon-folder']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_subfolders', ['label' => esc_html__('Sub Folders', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('subfoldername_color', ['label' => esc_html__('Name Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} li.dir li.dir a .dce-dir-title' => 'color: {{VALUE}};']]);
        $this->add_control('subfoldername_color_hover', ['label' => esc_html__('Name Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} li.dir li.dir a:hover .dce-dir-title' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'subfoldername_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} li.dir li.dir a .dce-dir-title']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'subfoldername_text_shadow', 'selector' => '{{WRAPPER}} li.dir li.dir a .dce-dir-title']);
        $this->add_control('heading_subfolders_border', ['label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('subfolder_border_type', ['label' => esc_html__('Border type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'solid', 'options' => ['solid' => esc_html__('Solid', 'dynamic-content-for-elementor'), 'dashed' => esc_html__('Dashed', 'dynamic-content-for-elementor'), 'dotted' => esc_html__('Dotted', 'dynamic-content-for-elementor'), 'double' => esc_html__('Double', 'dynamic-content-for-elementor'), 'none' => esc_html__('None', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} .dce-list li.dir li.dir' => 'border-bottom-style: {{VALUE}}']]);
        $this->add_control('subfolder_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['subfolder_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-list li.dir li.dir' => 'border-bottom-color: {{VALUE}};']]);
        $this->add_control('subfolder_border_stroke', ['label' => esc_html__('Border weight', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 10]], 'condition' => ['subfolder_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-list li.dir li.dir' => 'border-bottom-width: {{SIZE}}{{UNIT}};']]);
        $this->add_control('subfolder_list_space', ['label' => esc_html__('Row Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => 3, 'max' => 100]], 'separator' => 'before', 'selectors' => ['{{WRAPPER}} .dce-list li.dir li.dir' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_control('heading_subfolders_icon', ['label' => esc_html__('Icons', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('subfolder_icon_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 40], 'range' => ['px' => ['min' => 0, 'max' => 180]], 'selectors' => ['{{WRAPPER}} .dce-list li.dir li.dir .fiv-icon-folder' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_control('subfolder_icon_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => -50, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-list li.dir li.dir .fiv-icon-subfolder' => 'margin-right: {{SIZE}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Filters_HSB::get_type(), ['name' => 'subf_icon_hue_filters', 'label' => esc_html__('Color (HSB)', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-list li.dir li.dir .fiv-icon-folder']);
        $this->end_controls_section();
        $this->start_controls_section('section_style_files', ['label' => esc_html__('Files', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('filename_color', ['label' => esc_html__('Name Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} a.dce-file-download' => 'color: {{VALUE}};']]);
        $this->add_control('filename_color_hover', ['label' => esc_html__('Name Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} a.dce-file-download:hover' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'filename_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} a .dce-file-title']);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'filename_text_shadow', 'selector' => '{{WRAPPER}} a .dce-file-title']);
        $this->add_control('heading_files_border', ['label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('file_border_type', ['label' => esc_html__('Border type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'solid', 'options' => ['solid' => esc_html__('Solid', 'dynamic-content-for-elementor'), 'dashed' => esc_html__('Dashed', 'dynamic-content-for-elementor'), 'dotted' => esc_html__('Dotted', 'dynamic-content-for-elementor'), 'double' => esc_html__('Double', 'dynamic-content-for-elementor'), 'none' => esc_html__('None', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} .dce-list li.file' => 'border-bottom-style: {{VALUE}}']]);
        $this->add_control('file_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['file_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-list li.file' => 'border-bottom-color: {{VALUE}};']]);
        $this->add_control('file_border_stroke', ['label' => esc_html__('Border weight', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 10]], 'condition' => ['file_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-list li.file' => 'border-bottom-width: {{SIZE}}{{UNIT}};']]);
        $this->add_control('file_list_space', ['label' => esc_html__('Row Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => 3, 'max' => 100]], 'separator' => 'before', 'selectors' => ['{{WRAPPER}} .dce-list li.file' => 'padding-top: {{SIZE}}{{UNIT}}; padding-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_control('heading_files_icon', ['label' => esc_html__('Icons', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('file_icon_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 40], 'range' => ['px' => ['min' => 10, 'max' => 180]], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-download .fiv-viv' => 'font-size: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .dce-list .dce-file-download .dce-img-icon' => 'width: {{SIZE}}{{UNIT}}; height: auto;', '{{WRAPPER}} .dce-list .dce-file-description' => 'margin-left: {{SIZE}}{{UNIT}};']]);
        $this->add_control('file_icon_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-download .fiv-viv' => 'margin-right: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .dce-list .dce-file-download .dce-img-icon' => 'margin-right: {{SIZE}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Filters_HSB::get_type(), ['name' => 'fileicon_hue_filters', 'label' => esc_html__('Color (HSB)', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-list .dce-file-download .fiv-viv']);
        $this->add_control('heading_files_size', ['label' => esc_html__('Label Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['enable_metadata_size' => 'yes']]);
        $this->add_control('filesizes_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['enable_metadata_size' => 'yes'], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-download .dce-file-size-label' => 'color: {{VALUE}};']]);
        $this->add_control('filesize_icon_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'range' => ['px' => ['min' => 10, 'max' => 180]], 'condition' => ['enable_metadata_size' => 'yes'], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-download .dce-file-size-label' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_control('heading_files_hits', ['label' => esc_html__('Label Hits', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['enable_metadata_hits' => 'yes']]);
        $this->add_control('filehits_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['enable_metadata_hits' => 'yes'], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-hits-label' => 'color: {{VALUE}};']]);
        $this->add_control('filehits_icon_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'range' => ['px' => ['min' => 10, 'max' => 180]], 'condition' => ['enable_metadata_hits' => 'yes'], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-hits-label' => 'font-size: {{SIZE}}{{UNIT}};']]);
        $this->add_control('filehits_icon_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'range' => ['px' => ['min' => 10, 'max' => 180]], 'condition' => ['enable_metadata_hits' => 'yes'], 'selectors' => ['{{WRAPPER}} .dce-list .dce-file-hits-label' => 'margin-left: {{SIZE}}{{UNIT}};']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_search', ['label' => esc_html__('Search', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['search' => 'yes']]);
        $this->add_control('align_search', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'selectors' => ['{{WRAPPER}} .dce-file-search-form' => 'text-align: {{VALUE}};']]);
        $this->add_control('heading_search_border', ['label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('search_border_type', ['label' => esc_html__('Border type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'solid', 'options' => ['solid' => esc_html__('Solid', 'dynamic-content-for-elementor'), 'dashed' => esc_html__('Dashed', 'dynamic-content-for-elementor'), 'dotted' => esc_html__('Dotted', 'dynamic-content-for-elementor'), 'double' => esc_html__('Double', 'dynamic-content-for-elementor'), 'none' => esc_html__('None', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} .dce-file-search-form' => 'border-style: {{VALUE}}']]);
        $this->add_control('search_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-file-search-form' => 'border-color: {{VALUE}};']]);
        $this->add_control('search_border_stroke', ['label' => esc_html__('Border weight', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 10]], 'condition' => ['search_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} .dce-file-search-form' => 'border-width: {{SIZE}}{{UNIT}};']]);
        $this->add_control('search_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 50]], 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .dce-file-search-form' => 'border-radius: {{SIZE}}{{UNIT}};']]);
        $this->add_control('heading_search_background', ['label' => esc_html__('Background', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(Group_Control_Background::get_type(), ['name' => 'background_search', 'types' => ['classic', 'gradient'], 'selector' => '{{WRAPPER}} .dce-file-search-form']);
        $this->add_control('heading_search_title', ['label' => esc_html__('Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['search_text!' => '']]);
        $this->add_control('search_title_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-file-search-form-title' => 'color: {{VALUE}};'], 'condition' => ['search_text!' => '']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'search_title_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-file-search-form-title', 'condition' => ['search_text!' => '']]);
        $this->add_control('search_title_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => -50, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-file-search-form-title' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Text_Shadow::get_type(), ['name' => 'search_title_text_shadow', 'selector' => '{{WRAPPER}} .dce-file-search-form-title', 'condition' => ['search_text!' => '']]);
        $this->add_control('heading_search_field', ['label' => esc_html__('Field search', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('search_field_txcolor', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} input.filetxt' => 'color: {{VALUE}};']]);
        $this->add_control('search_field_bgcolor', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} input.filetxt' => 'background-color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'search_field_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} input.filetxt']);
        $this->add_control('search_field_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 50]], 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} input.filetxt' => 'border-radius: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('search_field_padding', ['label' => esc_html__('Padding', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} input.filetxt' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_control('search_field_border_type', ['label' => esc_html__('Border type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'solid', 'options' => ['solid' => esc_html__('Solid', 'dynamic-content-for-elementor'), 'dashed' => esc_html__('Dashed', 'dynamic-content-for-elementor'), 'dotted' => esc_html__('Dotted', 'dynamic-content-for-elementor'), 'double' => esc_html__('Double', 'dynamic-content-for-elementor'), 'none' => esc_html__('None', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} input.filetxt' => 'border-style: {{VALUE}}']]);
        $this->add_control('search_field_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_field_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} input.filetxt' => 'border-color: {{VALUE}};']]);
        $this->add_responsive_control('search_field_border_stroke', ['label' => esc_html__('Borders', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'isLinked' => \true], 'condition' => ['search_field_border_type!' => 'none'], 'selectors' => ['{{WRAPPER}} input.filetxt' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_control('search_field_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 10], 'range' => ['px' => ['min' => -50, 'max' => 100]], 'selectors' => ['{{WRAPPER}} input.filetxt' => 'margin-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'search_field_box_shadow', 'exclude' => ['box_shadow_position'], 'selector' => '{{WRAPPER}} input.filetxt']);
        $this->add_control('heading_desc_field', ['label' => esc_html__('Small description', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('search_desc_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-desc small' => 'color: {{VALUE}};']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'search_desc_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-search-desc small']);
        $this->add_control('heading_search_buttons', ['label' => esc_html__('Buttons', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'buttons_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-search-buttons input']);
        $this->add_control('buttons_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 50]], 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input' => 'border-radius: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('buttons_padding', ['label' => esc_html__('Padding', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_responsive_control('buttons_border_stroke', ['label' => esc_html__('Borders', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '', 'isLinked' => \true], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $this->add_control('buttons_v_space', ['label' => esc_html__('Vertical Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input' => 'margin-top: {{SIZE}}{{UNIT}}; margin-bottom: {{SIZE}}{{UNIT}};']]);
        $this->add_control('buttons_h_space', ['label' => esc_html__('Horizontal Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['min' => 0, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};']]);
        $this->add_control('heading_search_buttonReset', ['label' => esc_html__('Button Reset', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['search_reset!' => '']]);
        $this->add_control('buttonreset_txcolor', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_reset!' => ''], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset' => 'color: {{VALUE}};']]);
        $this->add_control('buttonreset_bgcolor', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_reset!' => ''], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset' => 'background-color: {{VALUE}};']]);
        $this->add_control('buttonreset_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_reset!' => ''], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset' => 'border-color: {{VALUE}};']]);
        $this->add_control('buttonreset_txcolor_hover', ['label' => esc_html__('Hover Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_reset!' => ''], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset:hover' => 'color: {{VALUE}};']]);
        $this->add_control('buttonreset_bgcolor_hover', ['label' => esc_html__('Hover Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_reset!' => ''], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset:hover' => 'background-color: {{VALUE}};']]);
        $this->add_control('buttonreset_border_color_hover', ['label' => esc_html__('Hover Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'condition' => ['search_reset!' => ''], 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset:hover' => 'border-color: {{VALUE}};']]);
        $this->add_control('heading_search_buttonFind', ['label' => esc_html__('Button Find', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('buttonfind_txcolor', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.find' => 'color: {{VALUE}};']]);
        $this->add_control('buttonfind_bgcolor', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.find' => 'background-color: {{VALUE}};']]);
        $this->add_control('buttonfind_border_color', ['label' => esc_html__('Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.find' => 'border-color: {{VALUE}};']]);
        $this->add_control('buttonfind_txcolor_hover', ['label' => esc_html__('Hover Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.find:hover' => 'color: {{VALUE}};']]);
        $this->add_control('buttonfind_bgcolor_hover', ['label' => esc_html__('Hover Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.find:hover' => 'background-color: {{VALUE}};']]);
        $this->add_control('buttonfind_border_color_hover', ['label' => esc_html__('Hover Border color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .dce-search-buttons input.reset:hover' => 'border-color: {{VALUE}};']]);
        $this->end_controls_section();
    }
    /**
     * @return void
     */
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $baseDir = \false;
        $files = $dirs = array();
        switch ($settings['path_selection']) {
            case 'custom':
                $baseDir = $settings['folder_custom'];
                $tmpTit = \explode('/', $baseDir);
                $baseTitle = \end($tmpTit);
                break;
            case 'uploads':
                $baseDir = $settings['folder'];
                $baseTitle = $settings['folder'];
                if ($settings['subfolder_' . $settings['folder']]) {
                    $baseDir .= $settings['subfolder_' . $settings['folder']];
                    if ($settings['subfolder_' . $settings['folder']] != '/') {
                        $tmpTit = \explode('/', $settings['subfolder_' . $settings['folder']]);
                        $baseTitle = \end($tmpTit);
                    }
                }
                break;
            case 'media':
                $baseTitle = \false;
                $baseDir = 'wp-content/uploads';
                if ($settings['medias_field']) {
                    $medias = get_post_meta(get_the_ID(), $settings['medias_field'], \true);
                } else {
                    $medias = $settings['medias'];
                }
                $src_identifier = 'http';
                $tmp = \explode($src_identifier, $medias);
                foreach ($tmp as $fkey => $afile) {
                    if ($fkey) {
                        list($furl, $other) = \explode('"', $afile, 2);
                        $resized = Helper::is_resized_image($furl);
                        if ($resized) {
                            $furl = $resized;
                        }
                        list($other, $fpath) = \explode($baseDir, $furl, 2);
                        $files[] = \substr($fpath, 1);
                    }
                }
                \array_filter($files);
                $files = \array_unique($files);
                if (empty($files)) {
                    $baseDir = \false;
                }
                break;
            case 'taxonomy':
                $baseTitle = \false;
                $baseDir = 'wp-content/uploads';
                if ($settings['taxonomy']) {
                    $term_id = \intval($settings['terms_' . $settings['taxonomy']]);
                    if ($term_id) {
                        $taxonomy = get_taxonomy($settings['taxonomy']);
                        if ($taxonomy) {
                            $baseTitle = $taxonomy->label;
                            $term = get_term_by('term_taxonomy_id', $term_id);
                            $baseTitle = $term->name;
                        }
                        $medias = Helper::get_term_posts($term_id, 'attachment');
                        if (!empty($medias)) {
                            foreach ($medias as $amedia) {
                                list($other, $fpath) = \explode($baseDir, $amedia->guid, 2);
                                $files[] = \substr($fpath, 1);
                            }
                        }
                    }
                }
                // TODO - subfolder
                \array_filter($files);
                if (empty($files)) {
                    $baseDir = \false;
                }
                break;
            case 'post':
                $baseTitle = wp_kses_post(get_the_title());
                $baseDir = 'wp-content/uploads';
                $medias = get_attached_media('', get_the_ID());
                if (!empty($medias)) {
                    foreach ($medias as $amedia) {
                        list($other, $fpath) = \explode($baseDir, $amedia->guid, 2);
                        $files[] = \substr($fpath, 1);
                    }
                }
                \array_filter($files);
                if (empty($files)) {
                    $baseDir = \false;
                }
        }
        if ($baseDir) {
            if (\is_dir(self::getRootDir($baseDir, $settings))) {
                if ($settings['path_selection'] == 'uploads' || $settings['path_selection'] == 'custom') {
                    $private_folder = self::getRootDir($baseDir, $settings);
                    $htaccess = $private_folder . '.htaccess';
                    $htblock = 'Options -Indexes' . \PHP_EOL . '<files "*">' . \PHP_EOL . 'order allow,deny' . \PHP_EOL . 'deny from all' . \PHP_EOL . '</files>';
                    if (empty($settings['private_access'])) {
                        if (\is_file($htaccess)) {
                            $htfile = wp_remote_retrieve_body(wp_remote_get($htaccess));
                            if ($htfile == $htblock) {
                                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                                    Helper::notice(esc_html__('Warning', 'dynamic-content-for-elementor'), esc_html__('The folder is secured. The HTACCESS file will be removed.', 'dynamic-content-for-elementor'), 'danger');
                                } else {
                                    \unlink($htaccess);
                                }
                            }
                        }
                    } elseif (!\is_file($htaccess)) {
                        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                            Helper::notice(esc_html__('Warning', 'dynamic-content-for-elementor'), esc_html__('The folder is not secured. An HTACCESS file will be generated.', 'dynamic-content-for-elementor'), 'danger');
                        } else {
                            \file_put_contents($htaccess, $htblock);
                        }
                    }
                }
                if (isset($settings['enable_metadata']) && $settings['enable_metadata']) {
                    $this->file_metadata = get_option('dce-file-browser', array());
                }
                if (isset($settings['search']) && $settings['search']) {
                    $this->displayFileSearch($settings);
                }
                if (isset($settings['title']) && $settings['title'] && $baseTitle) {
                    $title_size = Helper::validate_html_tag($settings['title_size']);
                    echo '<' . $title_size . ' class="dce-filebrowser-title">' . esc_html($baseTitle) . '</' . $title_size . '>';
                }
                echo '<ul class="list-unstyled dce-list dce-list-root"';
                if (isset($settings['enable_metadata']) && $settings['enable_metadata'] && isset($settings['enable_metadata_hide']) && $settings['enable_metadata_hide'] && $settings['enable_metadata_hide_reverse']) {
                    echo ' data-hide-reverse="1"';
                }
                echo '>';
                $this->dirToHtml(self::getRootDir($baseDir, $settings), null, $files, $dirs);
                echo '</ul>';
                $this->editorJavascript();
            } elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                Helper::notice(\false, esc_html__('Root folder not found', 'dynamic-content-for-elementor'));
            }
        } elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            Helper::notice(\false, esc_html__('Select root folder or files', 'dynamic-content-for-elementor'));
        }
    }
    public static function getRootDir($folder = \false, $settings = [])
    {
        if (!isset($settings['path_selection']) || $settings['path_selection'] == 'uploads') {
            $dir = wp_upload_dir();
            $dir = $dir['basedir'];
            if ($folder) {
                $dir = $dir . \DIRECTORY_SEPARATOR . $folder;
            }
        } else {
            $dir = ABSPATH;
            if ($folder && $folder != \DIRECTORY_SEPARATOR) {
                $dir .= $folder;
            }
        }
        return $dir;
    }
    protected function getFolders($dir = null, $settings = [])
    {
        if (!$dir) {
            $dir = self::getRootDir($dir, $settings);
        }
        $scanned_directory = \array_diff(\scandir($dir), array('..', '.'));
        $ret = [];
        foreach ($scanned_directory as $key => $value) {
            if (\is_dir($dir . \DIRECTORY_SEPARATOR . $value)) {
                $ret[$value] = \basename($value);
            }
        }
        return $ret;
    }
    protected function getFoldersRic($dir, $hidden = \false, $base = \false)
    {
        $result = [];
        $cdir = \scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!\in_array($value, array('.', '..'))) {
                if (\is_dir($dir . \DIRECTORY_SEPARATOR . $value)) {
                    $plainName = \str_replace(self::getRootDir(), '', $dir) . \DIRECTORY_SEPARATOR . $value;
                    if ($base) {
                        $plainName = \str_replace(\DIRECTORY_SEPARATOR . $base, '', $plainName);
                    }
                    $result[$plainName] = $plainName;
                    $result = \array_merge($result, $this->getFoldersRic($dir . \DIRECTORY_SEPARATOR . $value, $hidden, $base));
                }
            }
        }
        return $result;
    }
    protected function dirToHtml($dir, $hidden = \false, $files = array(), $dirs = array())
    {
        $image_exts = array('jpg', 'jpeg', 'jpe', 'gif', 'png');
        $settings = $this->get_settings_for_display();
        if (isset($settings['file_type']) && $settings['file_type']) {
            $file_type = \strtolower($settings['file_type']);
            $file_type = \str_replace(array('.', ','), ' ', $file_type);
            $extensions = \explode(' ', $file_type);
            $extensions = \array_filter($extensions);
        }
        if (!empty($dirs) && \is_array($dirs)) {
            $cdir = $dirs;
        }
        if (!empty($files) && \is_array($files)) {
            $cdir = $files;
        }
        if (empty($cdir)) {
            $cdir = \scandir($dir, isset($settings['order']) ? $settings['order'] : null);
        }
        $rootDir = \realpath(self::getRootDir(null, $settings));
        foreach ($cdir as $key => $value) {
            if (!\is_array($value) && \substr($value, 0, 1) == '.') {
                // hidden file
                continue;
            }
            $title = \false;
            if (\is_array($value)) {
                $fulldir = $dir . \DIRECTORY_SEPARATOR . $key;
            } elseif (\substr($dir, -1, 1) == '/') {
                $fulldir = $dir . $value;
            } else {
                $fulldir = $dir . \DIRECTORY_SEPARATOR . $value;
            }
            $fulldir = \realpath($fulldir);
            if ($fulldir === \false || !\is_string($rootDir)) {
                continue;
            }
            if (\strpos($fulldir, $rootDir) !== 0) {
                continue;
            }
            $rdir = \str_replace($rootDir, '', $fulldir);
            if (\is_array($value) || \is_dir($fulldir)) {
                $hide = \false;
                $kdir = sanitize_file_name($rdir);
                if (isset($settings['enable_metadata']) && $settings['enable_metadata'] && isset($settings['enable_metadata_hide']) && $settings['enable_metadata_hide']) {
                    $hide = $this->get_dir_meta($kdir, 'hidden');
                }
                if (isset($settings['enable_metadata']) && $settings['enable_metadata'] && isset($settings['enable_metadata_hide']) && $settings['enable_metadata_hide'] && isset($settings['enable_metadata_hide_reverse']) && $settings['enable_metadata_hide_reverse']) {
                    $hide = !$hide;
                }
                if (!Helper::is_empty_dir($fulldir) || $settings['empty']) {
                    if (!$hide || \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                        $hideHtml = '';
                        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                            if (isset($settings['enable_metadata']) && $settings['enable_metadata'] && isset($settings['enable_metadata_hide']) && $settings['enable_metadata_hide']) {
                                $hid = 'dce-dir-hide-' . $kdir;
                                if ($hide) {
                                    $hideHtml = '<a id="' . esc_html($hid) . '" class="btn btn-xs btn-secondary pull-left dce-dir-hide" href="#" data-dir="' . esc_html($kdir) . '"><span class="dashicons dashicons-hidden"></span></a>';
                                } else {
                                    $hideHtml = '<a id="' . esc_html($hid) . '" class="btn btn-xs btn-secondary pull-left dce-dir-hide" href="#" data-dir="' . esc_html($kdir) . '"><span class="dashicons dashicons-visibility"></span></a>';
                                }
                            }
                        }
                        $customTitle = 0;
                        if (!$title) {
                            if (\is_array($value)) {
                                $title = $key;
                            } else {
                                if (\Elementor\Plugin::$instance->editor->is_edit_mode() && isset($settings['enable_metadata']) && $settings['enable_metadata'] && isset($settings['enable_metadata_title']) && $settings['enable_metadata_custom_title']) {
                                    $customTitle = 1;
                                }
                                if (isset($settings['enable_metadata_custom_title']) && $settings['enable_metadata_custom_title']) {
                                    $title = $this->get_dir_meta($kdir, 'title', $value);
                                } else {
                                    $title = $value;
                                }
                            }
                        }
                        ?>
							<li class="dir">
						<?php 
                        echo $hideHtml;
                        ?>
								<a class="<?php 
                        echo $customTitle ? 'inline-' : '';
                        ?>block folder-dir" data-toggle="collapse" id="<?php 
                        echo $kdir;
                        ?>" data-target="#<?php 
                        echo $kdir;
                        ?>-ul" href="#<?php 
                        echo $kdir;
                        ?>" onClick="jQuery(this).siblings('ul').slideToggle(); return false;">
									<span class="middle fiv-viv fiv-icon-folder"></span>
							<?php 
                        if ($customTitle) {
                            ?>
									</a> <input type="text" class="dce-dir-title" data-dir="<?php 
                            echo $kdir;
                            ?>" name="dce-dir-browser[<?php 
                            echo $kdir;
                            ?>][title]" value="<?php 
                            echo $title;
                            ?>" /> <a class="inline-block" href="<?php 
                            echo $this->path_to_url($fulldir);
                            ?>" target="_blank">
							<?php 
                        } else {
                            ?>
										<strong class="dce-dir-title"><?php 
                            echo esc_html($title);
                            ?></strong>
							<?php 
                        }
                        ?>
								</a>
								<ul class="dce-hidden collapse list-unstyled dce-list" id="#<?php 
                        echo $kdir;
                        ?>-ul">
								<?php 
                        echo $this->dirToHtml($fulldir, null, $files, $value);
                        ?>
								</ul>
							</li>
								<?php 
                    }
                }
            } else {
                $filename = \basename($value);
                $pezzi = \explode('.', $filename);
                if (\count($pezzi) > 1) {
                    $ext = \strtolower(\end($pezzi));
                } else {
                    $ext = 'blank';
                }
                if (!empty($extensions)) {
                    if (isset($settings['file_type_show'])) {
                        if ($settings['file_type_show']) {
                            if (!\in_array($ext, $extensions)) {
                                continue;
                            }
                        } elseif (\in_array($ext, $extensions)) {
                            continue;
                        }
                    }
                }
                if (\in_array($ext, $image_exts)) {
                    $is_resized = Helper::is_resized_image($value);
                    if ($is_resized) {
                        if ($settings['path_selection'] == 'media') {
                            $value = $is_resized;
                            $fulldir = $dir . \DIRECTORY_SEPARATOR . $value;
                            $rdir = \str_replace(self::getRootDir(null, $settings), '', $fulldir);
                        } elseif (!$settings['resized']) {
                            continue;
                        }
                    }
                }
                $md5 = \md5($fulldir);
                $post_id = 0;
                if ($settings['enable_metadata']) {
                    $post_id = Helper::get_image_id($rdir);
                }
                $hide = \false;
                if ($settings['enable_metadata'] && $settings['enable_metadata_hide']) {
                    $hide = $this->get_file_meta($post_id ? $post_id : $md5, 'hidden');
                }
                if ($settings['enable_metadata'] && $settings['enable_metadata_hide'] && $settings['enable_metadata_hide_reverse']) {
                    $hide = !$hide;
                }
                if (\Elementor\Plugin::$instance->editor->is_edit_mode() || !$hide) {
                    if (!\file_exists(DCE_PATH . '/assets/node/file-icon-vectors/icons/vivid/' . $ext . '.svg')) {
                        $ext = 'blank';
                    }
                    echo '<li class="file ext-' . $ext . '">';
                    if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                        if (isset($settings['enable_metadata']) && $settings['enable_metadata'] && isset($settings['enable_metadata_hide']) && $settings['enable_metadata_hide']) {
                            $hid = 'dce-file-hide-' . $md5;
                            if ($hide) {
                                echo '<a id="' . esc_html($hid) . '" class="btn btn-xs btn-secondary pull-left dce-file-hide" href="#" data-md5="' . esc_html($md5) . '"' . ($post_id ? ' data-post-id="' . esc_html($post_id) . '"' : '') . '><span class="dashicons dashicons-hidden"></span></a>';
                            } else {
                                echo '<a id="' . esc_html($hid) . '" class="btn btn-xs btn-secondary pull-left dce-file-hide" href="#" data-md5="' . esc_html($md5) . '"' . ($post_id ? ' data-post-id="' . esc_html($post_id) . '"' : '') . '><span class="dashicons dashicons-visibility"></span></a>';
                            }
                        }
                    }
                    $customTitle = 0;
                    if (\Elementor\Plugin::$instance->editor->is_edit_mode() && $settings['enable_metadata'] && $settings['enable_metadata_custom_title']) {
                        $customTitle = 1;
                    }
                    $direct_link = $this->path_to_url($fulldir);
                    if ($settings['path_selection'] == 'uploads' || $settings['path_selection'] == 'custom') {
                        if (!empty($settings['private_access'])) {
                            $direct_link = DCE_URL . 'assets/file.php?element_id=' . $this->get_id() . '&md5=' . $md5;
                        }
                    }
                    echo '<a class="' . ($customTitle ? 'inline-' : '') . 'block btn-block dce-file-download" href="' . esc_url($direct_link) . '"  data-md5="' . esc_html($md5) . '"' . ($post_id ? ' data-post-id="' . esc_html($post_id) . '"' : '') . ' target="_blank">';
                    if (!empty($settings['img_icon']) && \in_array($ext, $image_exts) && $post_id) {
                        echo wp_get_attachment_image($post_id, 'thumbnail', \true, array('class' => 'middle dce-img-icon'));
                    } else {
                        echo '<span class="middle fiv-viv fiv-icon-' . $ext . '"></span>';
                    }
                    echo '<span class="dce-file-text">';
                    if (!$settings['extension'] && $ext != 'blank') {
                        $value = \substr($value, 0, -(\strlen($ext) + 1));
                    }
                    if ($settings['enable_metadata_custom_title']) {
                        if ($settings['enable_metadata_wp_title'] && $post_id) {
                            $title = esc_html(get_the_title($post_id));
                        } else {
                            $title = esc_html($this->get_file_meta($post_id ? $post_id : $md5, 'title', $value));
                        }
                    } elseif (!$title) {
                        $title = esc_html(\basename($value));
                    }
                    if ($customTitle) {
                        echo '</a>';
                        if (!empty($settings['enable_metadata']) && $settings['enable_metadata_wp_title'] && $post_id) {
                            echo '<strong class="dce-file-title"><a target="_blank" onclick="window.open(jQuery(this).attr(\'href\'));" href="' . get_site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit"><span class="dashicons dashicons-edit" style="vertical-align: middle;"></span> ' . $title . '</a></strong>';
                        } else {
                            echo '<input type="text" class="dce-file-title" data-md5="' . $md5 . '"' . ($post_id ? ' data-post-id="' . $post_id . '"' : '') . ' name="dce-file-browser[' . $md5 . '][title]" value="' . $title . '" />';
                        }
                        echo '<a class="inline-block" href="' . $this->path_to_url($fulldir) . '" target="_blank">';
                    } else {
                        echo '<strong class="dce-file-title">' . $title . '</strong>';
                    }
                    if (!empty($settings['enable_metadata']) && $settings['enable_metadata_size']) {
                        echo ' <small class="label label-default dce-file-size-label">(' . size_format(\filesize($fulldir), 0) . ')</small>';
                    }
                    if (!empty($settings['enable_metadata']) && $settings['enable_metadata_hits']) {
                        echo ' <small class="label label-default dce-file-hits-label"><i class="fa fa-download" aria-hidden="true"></i> <b>' . $this->get_file_meta($post_id ? $post_id : $md5, 'hits', 0) . '</b></small>';
                    }
                    echo '</span>';
                    echo '</a>';
                    if (!empty($settings['enable_metadata'])) {
                        if (isset($settings['enable_metadata_description']) && $settings['enable_metadata_description']) {
                            if ($settings['enable_metadata_wp_description'] && $post_id) {
                                $description = wp_get_attachment_caption($post_id);
                            } else {
                                $description = $this->get_file_meta($post_id ? $post_id : $md5, 'description');
                            }
                            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                                if ($settings['enable_metadata_wp_description'] && $post_id) {
                                    echo '<div class="dce-file-description block"><a target="_blank" onclick="window.open(jQuery(this).attr(\'href\'));" href="' . get_site_url() . '/wp-admin/post.php?post=' . $post_id . '&action=edit"><span class="dashicons dashicons-edit"></span> ' . ($description ? $description : '<span class="dce-empty-capton">' . esc_html__('Edit caption', 'dynamic-content-for-elementor') . '</span>') . '</a></div>';
                                } else {
                                    echo '<textarea class="dce-file-description block" data-md5="' . $md5 . '"' . ($post_id ? ' data-post-id="' . $post_id . '"' : '') . ' name="dce-file-browser[' . $md5 . '][description]">' . $description . '</textarea>';
                                }
                            } elseif (\trim($description)) {
                                echo '<div class="dce-file-description block">' . $description . '</div>';
                            }
                        }
                    }
                    echo '</li>';
                }
            }
        }
        return '';
    }
    protected function get_file_meta($file_id, $meta = '', $fallback = '')
    {
        if (\is_numeric($file_id)) {
            $ret = get_post_meta($file_id, 'dce-file', \true);
        } else {
            $ret = get_option('dce-file-' . $file_id);
        }
        if ($ret) {
            if (isset($ret[$meta])) {
                return $ret[$meta];
            }
        }
        if (isset($this->file_metadata[$file_id])) {
            return $this->file_metadata[$file_id][$meta];
        }
        return $fallback;
    }
    /**
     * Path to URL
     *
     * @param string $dir
     * @return string
     */
    protected function path_to_url($dir)
    {
        $dir = wp_normalize_path($dir);
        $upload_dirs = wp_upload_dir();
        $basedir = wp_normalize_path($upload_dirs['basedir']);
        $baseurl = $upload_dirs['baseurl'];
        $abspath = wp_normalize_path(ABSPATH);
        $site_url = site_url();
        if (\strpos($dir, $basedir) === 0) {
            $relative_path = \ltrim(\substr($dir, \strlen($basedir)), '/');
            $url = trailingslashit($baseurl) . $relative_path;
        } elseif (\strpos($dir, $abspath) === 0) {
            $relative_path = \ltrim(\substr($dir, \strlen($abspath)), '/');
            $url = trailingslashit($site_url) . $relative_path;
        } else {
            $url = esc_url_raw($dir);
        }
        return esc_url($url);
    }
    protected function get_dir_meta($dir_id, $meta = '', $fallback = '')
    {
        $ret = get_option('dce-dir-' . $dir_id);
        if ($ret && isset($ret[$meta])) {
            return $ret[$meta];
        }
        if (isset($this->file_metadata[$dir_id])) {
            return $this->file_metadata[$dir_id][$meta];
        }
        return $fallback;
    }
    protected function displayFileSearch($settings)
    {
        $this->add_render_attribute('form', 'class', 'dce-file-search-form');
        $this->add_render_attribute('form', 'action', '');
        ?>
		<form <?php 
        echo $this->get_render_attribute_string('form');
        ?>>
			<?php 
        if (!empty($settings['search_text'])) {
            $search_text_size = Helper::validate_html_tag($settings['search_text_size']);
            $this->add_render_attribute('search_text', 'class', 'dce-file-search-form-title');
            ?>
				<<?php 
            echo esc_attr($search_text_size);
            ?> <?php 
            echo $this->get_render_attribute_string('search_text');
            ?>>
					<?php 
            echo esc_html($settings['search_text']);
            ?>
				</<?php 
            echo esc_attr($search_text_size);
            ?>>
				<?php 
        }
        $this->add_render_attribute('input_container', 'class', 'form-control');
        $this->add_render_attribute('input_text', ['type' => 'text', 'class' => 'filetxt', 'name' => 'filetxt', 'value' => '']);
        ?>
			<div <?php 
        echo $this->get_render_attribute_string('input_container');
        ?>>
				<input <?php 
        echo $this->get_render_attribute_string('input_text');
        ?>>
			</div>
			<?php 
        if (!empty($settings['search_notice'])) {
            ?>
				<div class="dce-search-desc"><small>
					<?php 
            echo esc_html($settings['search_notice']);
            ?>
				</small></div>
				<?php 
        }
        if (empty($settings['search_quick'])) {
            $this->add_render_attribute('buttons_container', 'class', 'text-right dce-search-buttons');
            ?>
				<div <?php 
            echo $this->get_render_attribute_string('buttons_container');
            ?>>
					<?php 
            if (!empty($settings['search_reset'])) {
                $this->add_render_attribute('reset_button', ['class' => 'reset', 'type' => 'reset', 'value' => $settings['search_reset_text']]);
                ?>
						<input <?php 
                echo $this->get_render_attribute_string('reset_button');
                ?>>
						<?php 
            }
            $this->add_render_attribute('find_button', ['class' => 'find', 'type' => 'submit', 'value' => $settings['search_find_text']]);
            ?>
					<input <?php 
            echo $this->get_render_attribute_string('find_button');
            ?>>
				</div>
				<?php 
        }
        ?>
		</form>
		<br />
		<?php 
    }
    protected function editorJavascript()
    {
        $settings = $this->get_settings_for_display();
        ?>
		<script type="text/javascript" >
			var updateOptionsNonce = "<?php 
        echo esc_js(wp_create_nonce('wpa_update_options'));
        ?>";
			var updatePostMetasNonce = "<?php 
        echo wp_create_nonce('wpa_update_postmetas');
        ?>";
			var lastHide = '';
			jQuery(function () {
				if (typeof ajaxurl === 'undefined') {
					var ajaxurl = "<?php 
        echo esc_url(admin_url('admin-ajax.php', 'relative'));
        ?>";
				}

			<?php 
        if (\Elementor\Plugin::$instance->editor->is_edit_mode() && $settings['enable_metadata']) {
            if ($settings['enable_metadata_description']) {
                ?>
						jQuery(document).on("change", ".dce-file-description", function () {
							var data = {}
							if (jQuery(this).attr("data-post-id")) {
								data['action'] = "wpa_update_postmetas";
								data['nonce'] = updatePostMetasNonce;
								data['post_id'] = jQuery(this).attr("data-post-id");
								data['dce-file'] = {'description': jQuery(this).val()};
							} else {
								data['action'] = "wpa_update_options";
								data['nonce'] = updateOptionsNonce;
								data["dce-file-" + jQuery(this).attr("data-md5")] = {'description': jQuery(this).val()};
							}
							jQuery.post(ajaxurl, data, function (response) {
							});
						});
			<?php 
            }
            if ($settings['enable_metadata_custom_title']) {
                ?>
						jQuery(document).on("change", ".dce-file-title, .dce-dir-title", function () {
							var data = {};
							if (jQuery(this).hasClass('dce-file-title')) {
								if (jQuery(this).attr("data-post-id")) {
									data['action'] = "wpa_update_postmetas";
									data['nonce'] = updatePostMetasNonce;
									data['post_id'] = jQuery(this).attr("data-post-id");
									data['dce-file'] = {'title': jQuery(this).val()};
								} else {
									data['nonce'] = updateOptionsNonce;
									data['action'] = "wpa_update_options";
									data["dce-file-" + jQuery(this).attr("data-md5")] = {'title': jQuery(this).val()};
								}
							}
							if (jQuery(this).hasClass('dce-dir-title')) {
								data['nonce'] = updateOptionsNonce;
								data['action'] = "wpa_update_options";
								data["dce-dir-" + jQuery(this).attr("data-dir")] = {'title': jQuery(this).val()};
							}
							jQuery.post(ajaxurl, data, function (response) {
							});
						});
				<?php 
            }
            if ($settings['enable_metadata_hide']) {
                ?>
						jQuery(document).on("click", ".dce-file-hide, .dce-dir-hide", function (event) {
							if (!jQuery(this).attr('data-stop')) {
								var data = {};
								var visible = '';
								if (jQuery(this).children(".dashicons").hasClass('dashicons-hidden')) {
									jQuery(this).children(".dashicons").removeClass('dashicons-hidden').addClass('dashicons-visibility');
									visible = '';
								} else {
									jQuery(this).children(".dashicons").removeClass('dashicons-visibility').addClass('dashicons-hidden');
									visible = 'hidden';
								}
								if (jQuery(this).closest('.dce-list-root').attr('data-hide-reverse')) {
									if (visible != '') {
										visible = '';
									} else {
										visible = 'hidden';
									}
								}
								if (jQuery(this).hasClass('dce-file-hide')) {
									if (jQuery(this).attr("data-post-id")) {
										data['nonce'] = updatePostMetasNonce;
										data['action'] = "wpa_update_postmetas";
										data['post_id'] = jQuery(this).attr("data-post-id");
										data['dce-file'] = {'hidden': visible};
									} else {
										data['nonce'] = updateOptionsNonce;
										data['action'] = "wpa_update_options";
										data["dce-file-" + jQuery(this).attr("data-md5")] = {'hidden': visible};
									}
								}
								if (jQuery(this).hasClass('dce-dir-hide')) {
									data['nonce'] = updateOptionsNonce;
									data['action'] = "wpa_update_options";
									data["dce-dir-" + jQuery(this).attr("data-dir")] = {'hidden': visible};
								}

								lastHide = jQuery(this).attr('id');
								jQuery.post(ajaxurl, data, function (response) {
									jQuery('#' + lastHide).removeAttr('data-stop');

								});
								jQuery(this).attr('data-stop', 1);
								return false;
							}
						});
				<?php 
            }
        }
        ?>

		<?php 
        if ($settings['search']) {
            if ($settings['search_quick']) {
                ?>
					jQuery(".dce-file-search-form .filetxt").keyup(function (event) {
						var dce_form = jQuery(this).closest(".dce-file-search-form");
						if (jQuery(this).val().length > 2) {
							dce_form.siblings('.dce-list').find('ul.dce-list').show();
							dce_form.siblings('.dce-list').find('li.file').each(function () {
								if (jQuery(this).text().toLowerCase().indexOf(jQuery(".dce-file-search-form .filetxt").val().toLowerCase()) >= 0) {
									jQuery(this).show();
								} else {
									jQuery(this).hide();
								}
							});
						} else {
							dce_form.siblings('.dce-list').find('li.file').show();
						}
					});
				<?php 
            }
            ?>
					jQuery(".dce-file-search-form").submit(function (event) {
						if (jQuery(this).find('.filetxt').val().length > 2) {
							jQuery(this).siblings('.dce-list').find('ul.dce-list').show();
							jQuery(this).siblings('.dce-list').find('li.file').each(function () {
								if (jQuery(this).text().toLowerCase().indexOf(jQuery(".dce-file-search-form .filetxt").val().toLowerCase()) >= 0) {
									jQuery(this).show();
								} else {
									jQuery(this).hide();
								}
							});
						}
						return false;
					});
					jQuery(".dce-file-search-form .reset").click(function (event) {
						jQuery(this).closest('.dce-file-search-form').siblings('.dce-list').find('ul.dce-list').hide();
						jQuery(this).closest('.dce-file-search-form').siblings('.dce-list').find('li.file').show();
					});
		<?php 
        }
        if ($settings['enable_metadata_hits']) {
            ?>
					jQuery(document).on("click", ".dce-list > .file a.dce-file-download", function (event) {
						var data = {};
						data['action'] = "dce_file_browser_hits";
						if (jQuery(this).attr("data-post-id")) {
							data['post_id'] = jQuery(this).attr("data-post-id");
						}
						data["md5"] = jQuery(this).attr("data-md5");
						jQuery.post(ajaxurl, data, function (response) {
						});
					});
		<?php 
        }
        ?>
			});
		</script>
		<?php 
    }
}
