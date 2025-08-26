<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Utils;
use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Group_Control_Outline;
use DynamicContentForElementor\Controls\Group_Control_Filters_CSS;
use DynamicContentForElementor\Controls\Group_Control_Transform_Element;
// Exit if accessed directly
if (!\defined('ABSPATH')) {
    exit;
}
class PodsGallery extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public function run_once()
    {
        parent::run_once();
        $save_guard = \DynamicContentForElementor\Plugin::instance()->save_guard;
        $save_guard->register_unsafe_control($this->get_type(), 'other_post_source');
    }
    public function get_script_depends()
    {
        return ['jquery-masonry', 'dce-wow', 'photoswipe', 'photoswipe-ui', 'dce-diamonds', 'dce-homeycombs', 'dce-acfgallery'];
    }
    public function get_style_depends()
    {
        return ['dce-photoSwipe_skin', 'dce-justifiedGallery', 'animatecss', 'dce-pods-gallery'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_content', ['label' => esc_html__('PODS', 'dynamic-content-for-elementor')]);
        $this->add_control('gallery_field_list', ['label' => esc_html__('PODS Field', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_pods_fields('file'), 'default' => esc_html__('Select the field...', 'dynamic-content-for-elementor')]);
        $this->end_controls_section();
        // ********************************************************************************* Section GALLERY
        $this->start_controls_section('section_settings_gallery', ['label' => 'Gallery', 'dynamic-content-for-elementor', 'condition' => []]);
        $this->add_control('gallery_type', ['label' => esc_html__('Gallery Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['row' => esc_html__('Row', 'dynamic-content-for-elementor'), 'grid' => esc_html__('Grid', 'dynamic-content-for-elementor'), 'single_image' => esc_html__('Single image', 'dynamic-content-for-elementor')], 'default' => 'grid', 'frontend_available' => \true]);
        $this->add_responsive_control('columns_grid', ['label' => esc_html__('Columns', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => '2', 'tablet_default' => '3', 'mobile_default' => '1', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'render_type' => 'template', 'selectors' => ['{{WRAPPER}} .gallery-item' => 'width: calc( 100% / {{VALUE}} );'], 'condition' => ['gallery_type' => ['grid', 'masonry']]]);
        $this->add_control('column_diamond', ['label' => esc_html__('Min Diamond per Row', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => '4', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'], 'frontend_available' => \true, 'condition' => ['gallery_type' => 'diamond']]);
        $this->add_responsive_control('size_diamond', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'range' => ['px' => ['min' => 20, 'max' => 800, 'step' => 1]], 'frontend_available' => \true, 'selectors' => ['{{WRAPPER}} .diamonds .diamond-box-wrap' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};'], 'condition' => ['gallery_type' => 'diamond']]);
        $this->add_control('gap_diamond', ['label' => esc_html__('Gap Diamond', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 0, 'min' => 0, 'max' => 100, 'step' => 1, 'frontend_available' => \true, 'condition' => ['gallery_type' => 'diamond']]);
        $this->add_control('hideIncompleteRow', ['label' => esc_html__('Hide Incomplete Row', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'frontend_available' => \true, 'condition' => ['gallery_type' => 'diamond']]);
        $this->add_responsive_control('size_honeycombs', ['label' => esc_html__('Size Hexagon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 250, 'tablet_default' => 150, 'mobile_default' => 100, 'min' => 20, 'max' => 800, 'step' => 1, 'frontend_available' => \true, 'condition' => ['gallery_type' => 'hexagon']]);
        $this->add_control('gap_honeycombs', ['label' => esc_html__('Gap Hexagon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 10, 'min' => 0, 'max' => 100, 'step' => 1, 'frontend_available' => \true, 'condition' => ['gallery_type' => 'hexagon']]);
        $this->add_control('enabled_wow', ['label' => esc_html__('WOW Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'frontend_available' => \true]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_gallery', ['label' => esc_html__('Gallery', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'default' => '', 'prefix_class' => 'align-', 'selectors' => ['{{WRAPPER}} .dynamic_gallery' => 'text-align: {{VALUE}};']]);
        $this->add_responsive_control('v_align', ['label' => esc_html__('Vertical Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['top' => ['title' => esc_html__('Top', 'dynamic-content-for-elementor'), 'icon' => 'eicon-v-align-top'], 'middle' => ['title' => esc_html__('Middle', 'dynamic-content-for-elementor'), 'icon' => 'eicon-v-align-middle'], 'down' => ['title' => esc_html__('Down', 'dynamic-content-for-elementor'), 'icon' => 'eicon-v-align-bottom']], 'default' => 'top', 'selectors' => ['{{WRAPPER}} .dynamic_gallery  .gallery-item' => 'vertical-align: {{VALUE}};'], 'condition' => ['gallery_type' => ['grid']]]);
        $this->add_responsive_control('items_padding', ['label' => esc_html__('Paddings Items', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .gallery-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['gallery_type!' => ['hexagon']]]);
        $this->add_control('image_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .wrap-item-gallery' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['gallery_type!' => ['hexagon']]]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'image_border', 'label' => esc_html__('Image Border', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .wrap-item-gallery', 'condition' => ['gallery_type!' => ['diamond', 'hexagon']]]);
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'image_box_shadow', 'selector' => '{{WRAPPER}} .dynamic_gallery-masonry .wrap-item-gallery, {{WRAPPER}} .dynamic_gallery-diamond .diamond-box', 'condition' => ['gallery_type!' => ['hexagon']]]);
        $this->end_controls_section();
        $this->start_controls_section('section_settings', ['label' => 'Images', 'condition' => []]);
        $this->add_group_control(Group_Control_Image_Size::get_type(), ['name' => 'size', 'label' => esc_html__('Image Size', 'dynamic-content-for-elementor'), 'default' => 'large', 'condition' => []]);
        $this->add_control('use_desc', ['label' => esc_html__('Description', 'dynamic-content-for-elementor'), 'description' => esc_html__('Select what to use in the description below the image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'multiple' => \true, 'options' => ['title' => 'Title', 'caption' => 'Caption', 'description' => 'Description'], 'default' => '', 'condition' => ['gallery_type!' => ['diamond', 'hexagon']]]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_images', ['label' => 'Images', 'dynamic-content-for-elementor', 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_control('force_width', ['label' => esc_html__('Force Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'prefix_class' => 'forcewidth-']);
        $this->add_responsive_control('size_img', ['label' => esc_html__('Size (%)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 100, 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => 1, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .wrap-item-gallery' => 'width: {{SIZE}}{{UNIT}};'], 'condition' => ['force_width' => 'yes']]);
        $this->add_control('popover-toggle', ['label' => esc_html__('Transform image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes']);
        $this->start_popover();
        $this->add_group_control(Group_Control_Transform_Element::get_type(), ['name' => 'transform_image', 'label' => 'Transform image', 'selector' => '{{WRAPPER}} .dynamic_gallery', 'separator' => 'before']);
        $this->end_popover();
        $this->add_group_control(Group_Control_Filters_CSS::get_type(), ['name' => 'filters_image', 'label' => 'Filters image', 'selector' => '{{WRAPPER}} .gallery-item img']);
        $this->add_responsive_control('desc_margin', ['label' => esc_html__('space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} figcaption' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['use_desc!' => '']]);
        $this->add_control('figure_title_heading', ['label' => esc_html__('Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['use_desc' => 'title']]);
        $this->add_control('acf_space', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['max' => 100, 'min' => 0, 'step' => 1]], 'selectors' => ['{{WRAPPER}} figcaption .title' => 'margin-bottom: {{SIZE}}{{UNIT}};'], 'condition' => ['use_desc' => 'title']]);
        $this->add_control('desc_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} figcaption .title' => 'color: {{VALUE}};'], 'condition' => ['use_desc' => 'title']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'desc_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} figcaption .title', 'condition' => ['use_desc' => 'title']]);
        $this->add_control('figure_caption_heading', ['label' => esc_html__('Caption', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['use_desc' => 'caption']]);
        $this->add_control('space_caption', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['max' => 100, 'min' => 0, 'step' => 1]], 'selectors' => ['{{WRAPPER}} figcaption .caption' => 'margin-bottom: {{SIZE}}{{UNIT}};'], 'condition' => ['use_desc' => 'caption']]);
        $this->add_control('caption_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} figcaption .caption' => 'color: {{VALUE}};'], 'condition' => ['use_desc' => 'caption']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'caption_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} figcaption .caption', 'condition' => ['use_desc' => 'caption']]);
        $this->add_control('figure_description_heading', ['label' => esc_html__('Description', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['use_desc' => 'description']]);
        $this->add_control('space_description', ['label' => esc_html__('Space', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0], 'range' => ['px' => ['max' => 100, 'min' => 0, 'step' => 1]], 'selectors' => ['{{WRAPPER}} figcaption .description' => 'margin-bottom: {{SIZE}}{{UNIT}};'], 'condition' => ['use_desc' => 'description']]);
        $this->add_control('description_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} figcaption .description' => 'color: {{VALUE}};'], 'condition' => ['use_desc' => 'description']]);
        $this->add_group_control(Group_Control_Typography::get_type(), ['name' => 'description_typography', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} figcaption .description', 'condition' => ['use_desc' => 'description']]);
        $this->end_controls_section();
        $this->start_controls_section('section_wow', ['label' => 'Animation', 'condition' => ['enabled_wow' => 'yes']]);
        $this->add_control('wow_coef', ['label' => esc_html__('Delay', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 0, 'min' => 0.05, 'max' => 1, 'step' => 0.05, 'condition' => ['enabled_wow' => 'yes']]);
        $this->add_control('wow_animations', ['label' => esc_html__('Wow Animation Effect', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['fadeIn' => 'Fade In', 'fadeInDown' => 'Fade In Down', 'fadeInLeft' => 'Fade In Left', 'fadeInRight' => 'Fade In Right', 'fadeInUp' => 'Fade In Up', 'zoomIn' => 'Zoom In', 'zoomInDown' => 'Zoom In Down', 'zoomInLeft' => 'Zoom In Left', 'zoomInRight' => 'Zoom In Right', 'zoomInUp' => 'Zoom In Up', 'bounceIn' => 'Bounce In', 'bounceInDown' => 'Bounce In Down', 'bounceInLeft' => 'Bounce In Left', 'bounceInRight' => 'Bounce In Right', 'bounceInUp' => 'Bounce In Up', 'slideInDown' => 'Slide In Down', 'slideInLeft' => 'Slide In Left', 'slideInRight' => 'Slide In Right', 'slideInUp' => 'Slide In Up', 'rotateIn' => 'Rotate In', 'rotateInDownLeft' => 'Rotate In Down Left', 'rotateInDownRight' => 'Rotate In Down Right', 'rotateInUpLeft' => 'Rotate In Up Left', 'rotateInUpRight' => 'Rotate In Up Right', 'bounce' => 'Bounce', 'flash' => 'Flash', 'pulse' => 'Pulse', 'rubberBand' => 'Rubber Band', 'shake' => 'Shake', 'headShake' => 'Head Shake', 'swing' => 'Swing', 'tada' => 'Tada', 'wobble' => 'Wobble', 'jello' => 'Jello', 'lightSpeedIn' => 'Light Speed In', 'rollIn' => 'Roll In'], 'default' => 'fadeInUp', 'condition' => ['enabled_wow' => 'yes']]);
        $this->end_controls_section();
        $this->start_controls_section('section_lightbox_effects', ['label' => 'Lightbox', 'dynamic-content-for-elementor', 'condition' => []]);
        $this->add_control('enable_lightbox', ['label' => esc_html__('LightBox', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'frontend_available' => \true]);
        $this->add_control('enable_lightbox_link', ['label' => esc_html__('Image link', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['enable_lightbox' => '']]);
        $this->add_control('lightbox_type', ['label' => esc_html__('Lightbox Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['' => esc_html__('Default', 'dynamic-content-for-elementor'), 'photoswipe' => 'Photoswipe'], 'default' => '', 'condition' => ['enable_lightbox!' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_image_link', ['label' => 'Image Link', 'dynamic-content-for-elementor', 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_group_control(Group_Control_Background::get_type(), ['name' => 'background', 'types' => ['classic', 'gradient'], 'selector' => '{{WRAPPER}} .gallery-overlay_hover, {{WRAPPER}} .inner_span', 'popover' => \true]);
        $this->add_control('hover_effects', ['label' => esc_html__('Hover Effects', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['' => esc_html__('None', 'dynamic-content-for-elementor'), 'zoom' => esc_html__('Zoom', 'dynamic-content-for-elementor')], 'default' => '', 'separator' => 'before', 'prefix_class' => 'hovereffect-', 'condition' => ['enable_lightbox!' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_source', ['label' => esc_html__('Source', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_CONTENT]);
        $this->add_control('data_source', ['label' => esc_html__('Source', 'dynamic-content-for-elementor'), 'description' => esc_html__('Select the data source', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'label_on' => esc_html__('Same', 'dynamic-content-for-elementor'), 'label_off' => esc_html__('Other', 'dynamic-content-for-elementor'), 'return_value' => 'yes']);
        $this->add_control('other_post_source', ['label' => esc_html__('Select from other source post', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Post Title', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'condition' => ['data_source' => '']]);
        $this->end_controls_section();
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        $id_page = Helper::get_the_id($settings['other_post_source'] ?? \false);
        $idFields = $settings['gallery_field_list'];
        $gallery = pods_field_raw($idFields, $id_page);
        if (!$gallery) {
            return;
        }
        $counter = 0;
        $title = '';
        $image_size = $settings['size_size'];
        $enable_lightbox = '';
        $lightbox_type = '';
        $elementor_lightbox = '';
        $data_elementor_open_lightbox = '';
        $data_elementor_slideshow = '';
        if ($settings['enable_lightbox']) {
            $enable_lightbox = ' is-lightbox';
        }
        if ($settings['lightbox_type'] == 'photoswipe') {
            $lightbox_type = ' ' . $settings['lightbox_type'];
            $data_elementor_open_lightbox = 'data-elementor-open-lightbox="no"';
        } else {
            $lightbox_type = ' gallery';
            $data_elementor_slideshow = ' data-elementor-lightbox-slideshow="' . $this->get_id() . '"';
            $elementor_lightbox = ' gallery-lightbox';
            $data_elementor_open_lightbox = 'data-elementor-open-lightbox="yes"';
        }
        $type_gallery = '';
        if (!empty($settings['gallery_type'])) {
            $type_gallery = $settings['gallery_type'];
        }
        $type_gallery_item = '';
        $type_gallery_item_a = '';
        if ($settings['gallery_type'] == 'hexagon') {
            $type_gallery = 'honeycombs';
            if ($enable_lightbox != '') {
                $type_gallery_item_a = 'comb';
            } else {
                $type_gallery_item = ' comb';
            }
        }
        $overlay_hover_block = '';
        $overlay_hover_class = '';
        if ($settings['gallery_type'] == 'hexagon') {
            $overlay_hover_block = '<span><span>';
        } else {
            $overlay_hover_block = '<span class="gallery-overlay_hover"></span>';
        }
        $overlay_hover_class = ' is-overlay ';
        ?>
		<div class="<?php 
        echo $type_gallery;
        ?>-grid dynamic_gallery dynamic_gallery-<?php 
        echo $type_gallery . $enable_lightbox . $elementor_lightbox . $lightbox_type . $overlay_hover_class;
        ?> column-<?php 
        echo $settings['columns_grid'];
        ?>">
			<?php 
        foreach ($gallery as $image) {
            $single_image = '';
            if ($settings['gallery_type'] == 'single_image' && $counter >= 1) {
                $single_image = ' hidden';
            }
            if (!isset($image['ID'])) {
                $img_id = $image;
                $image = Helper::get_image_attachment($img_id);
            }
            $image_url = Group_Control_Image_Size::get_attachment_image_src($image['ID'], 'size', $settings);
            $image_meta = wp_get_attachment_metadata($image['ID']);
            $wow_enable = $settings['enabled_wow'];
            if ($wow_enable == 'yes') {
                $wow_coeff = $settings['wow_coef'] ? $settings['wow_coef'] : 0;
                $wow_delay = ' data-wow-delay="' . $counter * $wow_coeff . 's"';
                $wow_animations = $settings['wow_animations'];
                $wow_string = ' wow ' . $wow_animations;
            } else {
                $wow_string = '';
                $wow_delay = '';
            }
            if ($settings['gallery_type'] != 'hexagon') {
                echo '<figure itemprop="associatedMedia" class="gallery-item grid-item' . $type_gallery_item . $single_image . $wow_string . '"' . $wow_delay . '>';
                echo '<div class="wrap-item-gallery">';
            }
            if ($enable_lightbox) {
                echo '<a class="' . $type_gallery_item_a . $enable_lightbox . $elementor_lightbox . '" href="' . $image['guid'] . '" itemprop="contentUrl" data-size="' . $image_meta['width'] . 'x' . $image_meta['height'] . '"' . $data_elementor_open_lightbox . $data_elementor_slideshow . '>';
            } elseif ($settings['enable_lightbox_link']) {
                echo '<a class="' . $type_gallery_item_a . '" href="' . $image['guid'] . '" itemprop="contentUrl" >';
            }
            echo '<img src="' . esc_url($image_url) . '" itemprop="thumbnail" alt="' . esc_attr(wp_strip_all_tags(get_post_meta($image['ID'], '_wp_attachment_image_alt', \true), \true)) . '" />';
            echo $overlay_hover_block;
            if ($enable_lightbox || $settings['enable_lightbox_link']) {
                echo '</a>';
            }
            if ($settings['gallery_type'] != 'hexagon') {
                echo '</div>';
                if (!empty($settings['use_desc']) && ($settings['gallery_type'] != 'diamond' && $settings['gallery_type'] != 'hexagon')) {
                    echo '<figcaption itemprop="description caption">';
                    foreach ($settings['use_desc'] as $value) {
                        switch ($value) {
                            case 'caption':
                                echo '<p class="' . $value . '" >' . $image['post_excerpt'] . '</p>';
                                break;
                            case 'description':
                                echo '<p class="' . $value . '">' . $image['post_content'] . '</p>';
                                break;
                            case 'title':
                            default:
                                echo '<h3 class="' . $value . '">' . $image['post_title'] . '</h3>';
                        }
                    }
                    echo '</figcaption>';
                }
                echo '</figure>';
            }
            ++$counter;
        }
        ?>
		</div>
		<!-- Root element of PhotoSwipe. Must have class pswp. -->
		<?php 
    }
}
