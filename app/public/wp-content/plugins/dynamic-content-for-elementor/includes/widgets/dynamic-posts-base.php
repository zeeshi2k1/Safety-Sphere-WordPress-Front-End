<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use DynamicContentForElementor\Plugin;
use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Controls\Group_Control_Transform_Element;
use DynamicContentForElementor\Controls\Group_Control_Filters_CSS;
use DynamicContentForElementor\Favorites;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class DynamicPostsBase extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    protected $query = null;
    protected $query_args = null;
    protected $_has_template_content = \false;
    public function get_name()
    {
        return 'dce-dynamicposts-base';
    }
    public function run_once()
    {
        parent::run_once();
        $save_guard = \DynamicContentForElementor\Plugin::instance()->save_guard;
        $save_guard->register_unsafe_control('any', 'custom_query_code');
        $save_guard->register_unsafe_control('any', 'post_status');
        $save_guard->register_unsafe_control('any', 'favorites_user_id');
    }
    public $depended_scripts = ['dce-dynamicPosts-base'];
    public $depended_styles = ['dce-dynamic-posts'];
    /**
     * @param array<string>|string $handler
     * @return void
     */
    public function add_script_depends($handler)
    {
        if (!empty($handler)) {
            if (\is_array($handler)) {
                $this->depended_scripts = \array_merge($this->depended_scripts, $handler);
            } else {
                $this->depended_scripts[] = $handler;
            }
        }
    }
    /**
     * @param array<string>|string $handler
     * @return void
     */
    public function add_style_depends($handler)
    {
        if (!empty($handler)) {
            if (\is_array($handler)) {
                $this->depended_styles = \array_merge($this->depended_styles, $handler);
            } else {
                $this->depended_styles[] = $handler;
            }
        }
    }
    public function get_script_depends()
    {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
            $all_scripts = [];
            foreach ($this->get_skins() as $skin => $value) {
                $all_scripts = \array_merge($all_scripts, $this->get_skin($skin)->get_script_depends());
            }
            return \array_merge($this->depended_scripts, $all_scripts);
        }
        return \array_merge($this->depended_scripts, $this->get_current_skin()->get_script_depends());
    }
    public function get_style_depends()
    {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode()) {
            $all_styles = [];
            foreach ($this->get_skins() as $skin => $value) {
                $all_styles = \array_merge($all_styles, $this->get_skin($skin)->get_style_depends());
            }
            return \array_merge($this->depended_styles, $all_styles);
        }
        return \array_merge($this->depended_styles, $this->get_current_skin()->get_style_depends());
    }
    protected function _enqueue_scripts()
    {
        $scripts = $this->get_script_depends();
        if (!empty($scripts)) {
            foreach ($scripts as $script) {
                wp_enqueue_script($script);
            }
        }
    }
    protected function _enqueue_styles()
    {
        $styles = $this->get_style_depends();
        if (!empty($styles)) {
            foreach ($styles as $style) {
                wp_enqueue_style($style);
            }
        }
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->register_base_controls();
        $this->register_pagination_controls();
        $this->register_infinitescroll_controls();
        $this->register_query_controls();
        $this->register_style_direction_controls();
    }
    /**
     * Register Widget Specific Controls
     *
     * @return void
     */
    protected function register_widget_specific_controls()
    {
    }
    protected function register_base_controls()
    {
        $taxonomies = Helper::get_taxonomies();
        $this->start_controls_section('section_dynamicposts', ['label' => $this->get_title(), 'tab' => Controls_Manager::TAB_CONTENT]);
        $skin_control = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack($this->get_unique_name(), '_skin');
        if (\is_array($skin_control) && isset($skin_control['options']) && \is_array($skin_control['options'])) {
            // Deprecated skins: they will no longer appear as an option, but will still work if already selected.
            unset($skin_control['options']['gridtofullscreen3d']);
            unset($skin_control['options']['3d']);
            $this->update_control('_skin', $skin_control);
        }
        $this->register_skins_images();
        // +********************* Pagination
        $this->add_control('pagination_enable', ['label' => esc_html__('Pagination', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']]]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']]]]]]]);
        $this->add_control('infiniteScroll_enable', ['label' => esc_html__('Infinite Scroll', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'frontend_available' => \true, 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']], ['name' => 'pagination_enable', 'operator' => '!=', 'value' => '']]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']], ['name' => 'pagination_enable', 'operator' => '!=', 'value' => '']]]]]]);
        $this->add_control('style_items', ['label' => esc_html__('Items Style', 'dynamic-content-for-elementor'), 'type' => 'images_selector', 'type_selector' => 'image', 'columns_grid' => 5, 'separator' => 'before', 'options' => ['default' => ['title' => esc_html__('Default', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/top.png'], 'left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/left.png'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/right.png'], 'alternate' => ['title' => esc_html__('Alternate', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/alternate.png'], 'textzone' => ['title' => esc_html__('Text Zone', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/textzone.png'], 'overlay' => ['title' => esc_html__('Overlay', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/overlay.png'], 'float' => ['title' => esc_html__('Float', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/float.png'], 'html_tokens' => ['title' => esc_html__('Dynamic HTML', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/html_tokens.png'], 'template' => ['title' => esc_html__('Elementor Template', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/img/layout/template.png']], 'toggle' => \false, 'render_type' => 'template', 'prefix_class' => 'dce-posts-layout-', 'default' => 'default', 'frontend_available' => \true, 'condition' => ['_skin' => ['', 'grid', 'grid-filters', 'carousel', 'filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion']]]);
        // +********************* Style: Left, Right, Alternate
        $this->add_responsive_control('image_rate', ['label' => esc_html__('Distribution (%)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => 1, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-image-area' => 'width: {{SIZE}}%;', '{{WRAPPER}} .dce-content-area' => 'width: calc( 100% - {{SIZE}}% );'], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['left', 'right', 'alternate']]]);
        // +********************* Float Hover style descripton:
        $this->add_control('float_hoverstyle_description', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('The Float style allows you to create animations between the content and the image. From the Hover Effect panel you can choose the settings', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['float']]]);
        Plugin::instance()->text_templates->maybe_add_notice($this, '', ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'html_tokens']);
        $read_more = esc_html__('Read more', 'dynamic-content-for-elementor');
        $this->add_control('html_tokens_editor', ['label' => esc_html__('Dynamic HTML', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CODE, 'default' => \DynamicContentForElementor\Plugin::instance()->text_templates->get_default_value(['dynamic-shortcodes' => <<<EOF
{if:{post:featured-image-id} [<a href="{post:permalink}">{media:image @ID={post:featured-image-id}}</a>]}
<h4><a href="{post:permalink}">{post:title}</a></h4>
<p>{post:excerpt}</p>
<a class="btn btn-primary" href="{post:permalink}">{$read_more}</a>
EOF
, 'tokens' => '<a href="[post:permalink]">[post:thumb]</a><h4><a href="[post:permalink]">[post:title|esc_html]</a></h4><p>[post:excerpt]</p><a class="btn btn-primary" href="[post:permalink]">' . esc_html__('Read more', 'dynamic-content-for-elementor') . '</a>']), 'ai' => ['active' => \false], 'dynamic' => ['active' => \false], 'description' => \DynamicContentForElementor\Plugin::instance()->text_templates->get_default_value(['dynamic-shortcodes' => esc_html__('You can use Dynamic Shortcodes and HTML.', 'dynamic-content-for-elementor'), 'tokens' => esc_html__('You can use HTML and Tokens.', 'dynamic-content-for-elementor')]), 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'html_tokens']]);
        // +********************* Image Zone Style:
        $this->add_control('heading_imagezone', ['label' => esc_html__('Image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']]]);
        // +********************* Image Zone: Mask
        $this->add_control('imagemask_popover', ['label' => esc_html__('Mask', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Custom', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']]]);
        $this->start_popover();
        $this->add_control('mask_heading', ['label' => esc_html__('Mask', 'dynamic-content-for-elementor'), 'description' => esc_html__('Shape parameters', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'imagemask_popover' => 'yes']]);
        $this->add_control('mask_shape_type', ['label' => esc_html__('Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['image' => esc_html__('PNG Image', 'dynamic-content-for-elementor'), 'clippath' => esc_html__('Clip Path', 'dynamic-content-for-elementor')], 'default' => 'image', 'render_type' => 'template', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'imagemask_popover' => 'yes']]);
        $this->add_control('images_mask', ['label' => esc_html__('Select PNG mask', 'dynamic-content-for-elementor'), 'type' => 'images_selector', 'toggle' => \false, 'type_selector' => 'image', 'columns_grid' => 4, 'default' => DCE_URL . 'assets/img/mask/flower.png', 'options' => ['mask1' => ['title' => esc_html__('Flower', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/flower.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/flower.jpg'], 'mask2' => ['title' => esc_html__('Blob', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/blob.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/blob.jpg'], 'mask3' => ['title' => esc_html__('Diagonals', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/diagonal.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/diagonal.jpg'], 'mask4' => ['title' => esc_html__('Rhombus', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/rombs.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/rombs.jpg'], 'mask5' => ['title' => esc_html__('Waves', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/waves.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/waves.jpg'], 'mask6' => ['title' => esc_html__('Drawing', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/draw.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/draw.jpg'], 'mask7' => ['title' => esc_html__('Sketch', 'dynamic-content-for-elementor'), 'image' => DCE_URL . 'assets/img/mask/sketch.png', 'image_preview' => DCE_URL . 'assets/img/mask/low/sketch.jpg'], 'custom_mask' => ['title' => esc_html__('Custom Mask', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'image' => DCE_URL . 'assets/displacement/custom.jpg', 'image_preview' => DCE_URL . 'assets/displacement/custom.jpg']], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'imagemask_popover' => 'yes', 'mask_shape_type' => 'image'], 'selectors' => ['{{WRAPPER}} .dce-posts-container .dce-post-image img' => '-webkit-mask-image: url({{VALUE}}); mask-image: url({{VALUE}}); -webkit-mask-position: 50% 50%; mask-position: 50% 50%; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-size: contain; mask-size: contain;']]);
        $this->add_control('custom_image_mask', ['label' => esc_html__('Select a PNG file', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::MEDIA, 'dynamic' => ['active' => \true], 'default' => ['url' => \Elementor\Utils::get_placeholder_image_src()], 'selectors' => ['{{WRAPPER}} .dce-posts-container .dce-post-image img' => '-webkit-mask-image: url({{URL}}); mask-image: url({{URL}}); -webkit-mask-position: 50% 50%; mask-position: 50% 50%; -webkit-mask-repeat: no-repeat; mask-repeat: no-repeat; -webkit-mask-size: contain; mask-size: contain;'], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'imagemask_popover' => 'yes', 'images_mask' => 'custom_mask', 'mask_shape_type' => 'image']]);
        $this->add_control('clippath_mask', ['label' => esc_html__('Predefined Clip-Path', 'dynamic-content-for-elementor'), 'type' => 'images_selector', 'toggle' => \false, 'type_selector' => 'image', 'columns_grid' => 5, 'options' => ['polygon(50% 0%, 0% 100%, 100% 100%)' => ['title' => 'Triangle', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/triangle.png'], 'polygon(20% 0%, 80% 0%, 100% 100%, 0% 100%)' => ['title' => 'Trapezoid', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/trapezoid.png'], 'polygon(25% 0%, 100% 0%, 75% 100%, 0% 100%)' => ['title' => 'Parallelogram', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/parallelogram.png'], 'polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%)' => ['title' => 'Rombus', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/rombus.png'], 'polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%)' => ['title' => 'Pentagon', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/pentagon.png'], 'polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%)' => ['title' => 'Hexagon', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/hexagon.png'], 'polygon(50% 0%, 90% 20%, 100% 60%, 75% 100%, 25% 100%, 0% 60%, 10% 20%)' => ['title' => 'Heptagon', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/heptagon.png'], 'polygon(30% 0%, 70% 0%, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0% 70%, 0% 30%)' => ['title' => 'Octagon', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/octagon.png'], 'polygon(50% 0%, 83% 12%, 100% 43%, 94% 78%, 68% 100%, 32% 100%, 6% 78%, 0% 43%, 17% 12%)' => ['title' => 'Nonagon', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/nonagon.png'], 'polygon(50% 0%, 80% 10%, 100% 35%, 100% 70%, 80% 90%, 50% 100%, 20% 90%, 0% 70%, 0% 35%, 20% 10%)' => ['title' => 'Decagon', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/decagon.png'], 'polygon(20% 0%, 80% 0%, 100% 20%, 100% 80%, 80% 100%, 20% 100%, 0% 80%, 0% 20%)' => ['title' => 'Bevel', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/bevel.png'], 'polygon(0% 15%, 15% 15%, 15% 0%, 85% 0%, 85% 15%, 100% 15%, 100% 85%, 85% 85%, 85% 100%, 15% 100%, 15% 85%, 0% 85%)' => ['title' => 'Rabbet', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/rabbet.png'], 'polygon(40% 0%, 40% 20%, 100% 20%, 100% 80%, 40% 80%, 40% 100%, 0% 50%)' => ['title' => 'Left arrow', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/leftarrow.png'], 'polygon(0% 20%, 60% 20%, 60% 0%, 100% 50%, 60% 100%, 60% 80%, 0% 80%)' => ['title' => 'Right arrow', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/rightarrow.png'], 'polygon(25% 0%, 100% 1%, 100% 100%, 25% 100%, 0% 50%)' => ['title' => 'Left point', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/leftpoint.png'], 'polygon(0% 0%, 75% 0%, 100% 50%, 75% 100%, 0% 100%)' => ['title' => 'Right point', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/rightpoint.png'], 'polygon(100% 0%, 75% 50%, 100% 100%, 25% 100%, 0% 50%, 25% 0%)' => ['title' => 'Left chevron', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/leftchevron.png'], 'polygon(75% 0%, 100% 50%, 75% 100%, 0% 100%, 25% 50%, 0% 0%)' => ['title' => 'Right Chevron', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/rightchevron.png'], 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)' => ['title' => 'Star', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/star.png'], 'polygon(10% 25%, 35% 25%, 35% 0%, 65% 0%, 65% 25%, 90% 25%, 90% 50%, 65% 50%, 65% 100%, 35% 100%, 35% 50%, 10% 50%)' => ['title' => 'Cross', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/cross.png'], 'polygon(0% 0%, 100% 0%, 100% 75%, 75% 75%, 75% 100%, 50% 75%, 0% 75%)' => ['title' => 'Message', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/message.png'], 'polygon(0% 0%, 0% 100%, 25% 100%, 25% 25%, 75% 25%, 75% 75%, 25% 75%, 25% 100%, 100% 100%, 100% 0%)' => ['title' => 'Frame', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/frame.png'], 'circle(50% at 50% 50%)' => ['title' => 'Circle', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/circle.png'], 'ellipse(25% 40% at 50% 50%)' => ['title' => 'Ellipse', 'return_val' => 'val', 'image_preview' => DCE_URL . 'assets/img/shapes/ellipse.png']], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'imagemask_popover' => 'yes', 'mask_shape_type' => 'clippath'], 'selectors' => ['{{WRAPPER}} .dce-posts-container .dce-post-image img' => '-webkit-clip-path: {{VALUE}}; clip-path: {{VALUE}};']]);
        $this->end_popover();
        // +********************* Image Zone: Transforms
        $this->add_control('imagetransforms_popover', ['label' => esc_html__('Transforms', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'return_value' => 'yes', 'render_type' => 'ui', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']]]);
        $this->start_popover();
        $this->add_group_control(Group_Control_Transform_Element::get_type(), ['name' => 'transform_image', 'label' => esc_html__('Transform image', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-post-item .dce-image-area', 'separator' => 'before', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'imagetransforms_popover' => 'yes']]);
        $this->end_popover();
        // +********************* Image Zone: Filters
        $this->add_group_control(Group_Control_Filters_CSS::get_type(), ['name' => 'imagezone_filters', 'label' => esc_html__('Filters', 'dynamic-content-for-elementor'), 'render_type' => 'ui', 'selector' => '{{WRAPPER}} .dce-post-block .dce-post-image img', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']]]);
        // +********************* Content Zone Style:
        $this->add_control('heading_contentzone', ['label' => esc_html__('Content Area', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']]]);
        // +********************* Content Zone: Style
        $this->add_control('contentstyle_popover', ['label' => esc_html__('Style', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Custom', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'render_type' => 'ui', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']]]);
        $this->start_popover();
        $this->add_control('contentzone_bgcolor', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'separator' => 'before', 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-content-area' => 'background-color: {{VALUE}};'], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'contentstyle_popover' => 'yes']]);
        $this->add_group_control(Group_Control_Border::get_type(), ['name' => 'contentzone_border', 'selector' => '{{WRAPPER}} .dce-post-item .dce-content-area', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'contentstyle_popover' => 'yes']]);
        $this->add_responsive_control('contentzone_padding', ['label' => esc_html__('Padding', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-content-area' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'contentstyle_popover' => 'yes']]);
        $this->add_responsive_control('contentzone_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-content-area' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens'], 'contentstyle_popover' => 'yes']]);
        $this->end_popover();
        // +********************* Content Zone Transform: Overlay, TextZone, Float
        $this->add_control('contenttransform_popover', ['label' => esc_html__('Transform', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE, 'label_off' => esc_html__('Default', 'dynamic-content-for-elementor'), 'label_on' => esc_html__('Custom', 'dynamic-content-for-elementor'), 'return_value' => 'yes', 'render_type' => 'ui', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['overlay', 'textzone', 'float']]]);
        $this->start_popover();
        $this->add_responsive_control('contentzone_x', ['label' => 'X', 'type' => Controls_Manager::SLIDER, 'size_units' => ['%'], 'default' => ['size' => '', 'unit' => '%'], 'range' => ['%' => ['min' => -100, 'max' => 100, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .dce-content-area' => 'margin-left: {{SIZE}}%;'], 'condition' => ['contenttransform_popover' => 'yes', '_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['overlay', 'textzone', 'float']]]);
        $this->add_responsive_control('contentzone_y', ['label' => 'Y', 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => -100, 'max' => 100, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .dce-content-area' => 'margin-top: {{SIZE}}%;'], 'condition' => ['contenttransform_popover' => 'yes', '_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['overlay', 'textzone', 'float']]]);
        $this->add_responsive_control('contentzone_width', ['label' => esc_html__('Width (%)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => 1, 'max' => 100, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .dce-content-area' => 'width: {{SIZE}}%;'], 'condition' => ['contenttransform_popover' => 'yes', '_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['overlay', 'textzone', 'float']]]);
        $this->add_responsive_control('contentzone_height', ['label' => esc_html__('Height (%)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => '%'], 'size_units' => ['%'], 'range' => ['%' => ['min' => 1, 'max' => 100, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} .dce-content-area' => 'height: {{SIZE}}%;'], 'condition' => ['contenttransform_popover' => 'yes', '_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['float']]]);
        $this->end_popover();
        // +********************* Content Zone: BoxShadow
        $this->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'contentzone_box_shadow', 'selector' => '{{WRAPPER}} .dce-post-item .dce-content-area', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items!' => ['default', 'template', 'html_tokens']], 'popover' => \true]);
        /* Responsive --------------- */
        $this->add_control('force_layout_default', ['label' => esc_html__('Force default layout on mobile', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'prefix_class' => 'force-default-mobile-', 'condition' => ['_skin' => ['', 'grid', 'grid-filters', 'carousel', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => ['left', 'right', 'alternate']]]);
        // +********************* Style: Elementor TEMPLATE
        if (\DynamicContentForElementor\Plugin::instance()->template_system->is_active()) {
            $template_conditions = ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'template', 'native_templatemode_enable' => ''];
            $template_2_conditions = ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'template', 'templatemode_enable_2!' => '', 'native_templatemode_enable' => ''];
        } else {
            $template_conditions = ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'template'];
            $template_2_conditions = ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'template', 'templatemode_enable_2!' => ''];
        }
        $this->add_control('template_id', ['label' => esc_html__('Template', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Template Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'render_type' => 'template', 'object_type' => 'elementor_library', 'condition' => $template_conditions]);
        $this->add_control('templatemode_enable_2', ['label' => esc_html__('Template for even posts', 'dynamic-content-for-elementor'), 'description' => esc_html__('Enable a template to manage the appearance of the even elements', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'render_type' => 'template', 'condition' => $template_conditions]);
        $this->add_control('template_2_id', ['label' => esc_html__('Template for even posts', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Select Template', 'dynamic-content-for-elementor'), 'label_block' => \true, 'show_label' => \false, 'query_type' => 'posts', 'object_type' => 'elementor_library', 'render_type' => 'template', 'condition' => $template_2_conditions]);
        if (\DynamicContentForElementor\Plugin::instance()->template_system->is_active()) {
            $this->add_control('native_templatemode_enable', ['label' => esc_html__('Template System Block', 'dynamic-content-for-elementor'), 'description' => esc_html__('Use the template associated with the type (Menu: Dynamic Content for Elementor > Features > Template System) to manage the appearance of the individual elements of the grid ', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'render_type' => 'template', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion'], 'style_items' => 'template', 'templatemode_enable_2' => '']]);
        }
        $this->add_control('templatemode_linkable', ['label' => esc_html__('Linkable', 'dynamic-content-for-elementor'), 'description' => esc_html__('Apply the extended link to the entire block', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'frontend_available' => \true, 'render_type' => 'template', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel', 'smoothscroll', '3d']]]);
        $this->end_controls_section();
        $this->register_widget_specific_controls();
        // ------------------------------------------------------------------ [SECTION ITEMS]
        $this->start_controls_section('section_items', ['label' => esc_html__('Items', 'dynamic-content-for-elementor'), 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => 'style_items', 'operator' => '!in', 'value' => ['template', 'html_tokens']], ['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'carousel', 'filters', 'dualcarousel', 'smoothscroll', '3d', 'accordion']]]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['list', 'table', 'timeline']]]]]]]);
        $repeater = new Repeater();
        // Items for WooCommerce
        $woocommerce_items = [];
        if (Helper::is_woocommerce_active()) {
            $woocommerce_items = ['item_addtocart' => esc_html__('Add to Cart', 'dynamic-content-for-elementor'), 'item_productprice' => esc_html__('Product Price', 'dynamic-content-for-elementor'), 'item_sku' => esc_html__('Product SKU', 'dynamic-content-for-elementor')];
        }
        // JetEngine Item
        $jetengine_item = [];
        if (Helper::is_jetengine_active()) {
            $jetengine_item = ['item_jetengine' => esc_html__('JetEngine Field', 'dynamic-content-for-elementor')];
        }
        // Metabox Item
        $metabox_item = [];
        if (Helper::is_metabox_active()) {
            $metabox_item = ['item_metabox' => esc_html__('Meta Box Field', 'dynamic-content-for-elementor')];
        }
        $repeater->add_control('item_id', ['label' => esc_html__('Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'label_block' => \false, 'options' => \array_merge(['item_title' => esc_html__('Title', 'dynamic-content-for-elementor'), 'item_image' => esc_html__('Featured Image', 'dynamic-content-for-elementor'), 'item_date' => esc_html__('Date', 'dynamic-content-for-elementor'), 'item_termstaxonomy' => esc_html__('Terms', 'dynamic-content-for-elementor'), 'item_content' => esc_html__('Content', 'dynamic-content-for-elementor'), 'item_author' => esc_html__('Author', 'dynamic-content-for-elementor'), 'item_custommeta' => esc_html__('Custom Meta Field', 'dynamic-content-for-elementor'), 'item_token' => esc_html__('Token', 'dynamic-content-for-elementor')], $jetengine_item, $metabox_item, ['item_readmore' => esc_html__('Read More', 'dynamic-content-for-elementor'), 'item_posttype' => esc_html__('Post Type', 'dynamic-content-for-elementor')], $woocommerce_items), 'default' => 'item_title']);
        // TABS ----------
        $repeater->start_controls_tabs('items_repeater_tab');
        $repeater->start_controls_tab('tab_content', ['label' => esc_html__('Content', 'dynamic-content-for-elementor')]);
        // CONTENT - TAB
        //
        // +********************* Image
        $repeater->add_group_control(Group_Control_Image_Size::get_type(), ['name' => 'thumbnail_size', 'label' => esc_html__('Image Format', 'dynamic-content-for-elementor'), 'default' => 'large', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image']]]]);
        $repeater->add_responsive_control('ratio_image', ['label' => esc_html__('Image Ratio', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['min' => 0.1, 'max' => 2, 'step' => 0.1]], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-img' => 'padding-bottom: calc( {{SIZE}} * 100% );'], 'render_type' => 'template', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image'], ['name' => 'use_bgimage', 'value' => '']]]]);
        $repeater->add_responsive_control('width_image', ['label' => esc_html__('Image Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['%', 'px', 'vw'], 'range' => ['%' => ['min' => 1, 'max' => 100, 'step' => 1], 'vw' => ['min' => 1, 'max' => 100, 'step' => 1], 'px' => ['min' => 1, 'max' => 800, 'step' => 1]], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-image' => 'width: {{SIZE}}{{UNIT}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image'], ['name' => 'use_bgimage', 'value' => '']]]]);
        $repeater->add_control('use_bgimage', ['label' => esc_html__('Background Image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'render_type' => 'template', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image']]], 'selectors' => ['{{WRAPPER}} .dce-image-area, {{WRAPPER}}.dce-posts-layout-default .dce-post-bgimage' => 'position: relative;']]);
        $repeater->add_responsive_control('height_bgimage', ['label' => esc_html__('Height', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px', '%'], 'range' => ['px' => ['min' => 1, 'max' => 800, 'step' => 1]], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-image.dce-post-bgimage' => 'height: {{SIZE}}{{UNIT}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image'], ['name' => 'use_bgimage', 'operator' => '!=', 'value' => '']]]]);
        $repeater->add_responsive_control('position_bgimage', ['label' => esc_html__('Background Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'label_block' => \true, 'default' => '', 'responsive' => \true, 'options' => ['' => esc_html__('Default (Center Center)', 'dynamic-content-for-elementor'), 'top center' => _x('Top Center', 'Background Control', 'dynamic-content-for-elementor'), 'bottom center' => _x('Bottom Center', 'Background Control', 'dynamic-content-for-elementor')], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-image.dce-post-bgimage .dce-bgimage' => 'background-position: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image'], ['name' => 'use_bgimage', 'operator' => '!=', 'value' => '']]]]);
        $repeater->add_control('use_overlay', ['label' => esc_html__('Overlay', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'prefix_class' => 'overlayimage-', 'render_type' => 'template', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image']]]]);
        $repeater->add_group_control(Group_Control_Background::get_type(), ['name' => 'overlay_color', 'label' => esc_html__('Background', 'dynamic-content-for-elementor'), 'types' => ['classic', 'gradient'], 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-image.dce-post-overlayimage:after', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image'], ['name' => 'use_overlay', 'operator' => '!==', 'value' => '']]]]);
        $repeater->add_responsive_control('overlay_opacity', ['label' => esc_html__('Opacity (%)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0.7], 'range' => ['px' => ['max' => 1, 'min' => 0.1, 'step' => 0.01]], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-image.dce-post-overlayimage:after' => 'opacity: {{SIZE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image'], ['name' => 'use_overlay', 'operator' => '!==', 'value' => '']]]]);
        $repeater->add_control('featured_image_fallback', ['label' => esc_html__('Fallback', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::MEDIA, 'separator' => 'before', 'render_type' => 'template', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_image']]]]);
        // Custom Meta Fields
        $repeater->add_control('metafield_key', ['label' => esc_html__('Meta Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'default' => '', 'dynamic' => ['active' => \false], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_custommeta']]]]);
        // Jet Engine
        $repeater->add_control('jetengine_key', ['label' => esc_html__('JetEngine Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Field name or key', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'jet', 'dynamic' => ['active' => \false], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_jetengine']]]]);
        // Metabox
        $repeater->add_control('metabox_key', ['label' => esc_html__('Meta Box Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Field name or key', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metabox', 'dynamic' => ['active' => \false], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_metabox']]]]);
        $repeater->add_control('metafield_type', ['label' => esc_html__('Field type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'text', 'options' => ['image' => esc_html__('Image', 'dynamic-content-for-elementor'), 'date' => esc_html__('Date', 'dynamic-content-for-elementor'), 'text' => esc_html__('Text', 'dynamic-content-for-elementor'), 'textarea' => esc_html__('Textarea', 'dynamic-content-for-elementor'), 'button' => esc_html__('Button (URL)', 'dynamic-content-for-elementor'), 'url' => esc_html__('URL', 'dynamic-content-for-elementor')], 'condition' => ['item_id' => ['item_custommeta', 'item_jetengine', 'item_metabox']]]);
        $repeater->add_control('metafield_url_target', ['label' => esc_html__('Open in a new window', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['item_id' => ['item_custommeta'], 'metafield_type' => ['button', 'url']]]);
        $repeater->add_control('html_tag_item', ['label' => esc_html__('Wrapper HTML Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'div', 'options' => Helper::get_html_tags([], \true), 'condition' => ['item_id' => ['item_custommeta', 'item_jetengine', 'item_metabox'], 'metafield_type' => 'text']]);
        $repeater->add_group_control(Group_Control_Image_Size::get_type(), ['name' => 'image_size', 'label' => esc_html__('Image Format', 'dynamic-content-for-elementor'), 'default' => 'large', 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'IN', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox']], ['name' => 'metafield_type', 'value' => 'image']]]]);
        $repeater->add_control('metafield_date_format_source', ['label' => esc_html__('Date Format - Source', 'dynamic-content-for-elementor'), 'description' => '<a target="_blank" href="https://www.php.net/manual/en/function.date.php">' . esc_html__('Use standard PHP format characters', 'dynamic-content-for-elementor') . '</a>', 'type' => Controls_Manager::TEXT, 'default' => 'F j, Y, g:i a', 'placeholder' => 'YmdHis, d/m/Y, m-d-y', 'label_block' => \true, 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox']], ['name' => 'metafield_type', 'value' => 'date']]]]);
        $repeater->add_control('metafield_date_format_display', ['label' => esc_html__('Date Format - Display', 'dynamic-content-for-elementor'), 'placeholder' => 'YmdHis, d/m/Y, m-d-y', 'type' => Controls_Manager::TEXT, 'default' => 'F j, Y, g:i a', 'label_block' => \true, 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox']], ['name' => 'metafield_type', 'value' => 'date']]]]);
        $repeater->add_control('metafield_button_label', ['label' => esc_html__('Button Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Click me', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox']], ['name' => 'metafield_type', 'value' => 'button']]]]);
        $repeater->add_control('metafield_button_size', ['label' => esc_html__('Button Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'sm', 'options' => Helper::get_button_sizes(), 'style_transfer' => \true, 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox']], ['name' => 'metafield_type', 'value' => 'button']]]]);
        $repeater->add_control('price_format', ['label' => esc_html__('Price Format', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'both', 'options' => ['both' => esc_html__('Both', 'dynamic-content-for-elementor'), 'regular' => esc_html__('Regular Price', 'dynamic-content-for-elementor'), 'sale' => esc_html__('Sale Price', 'dynamic-content-for-elementor')], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_productprice']]]]);
        $repeater->add_control('add_to_cart_action', ['label' => esc_html__('Action', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'cart_page', 'options' => ['cart_page' => esc_html__('Add to Cart and redirect to Cart Page', 'dynamic-content-for-elementor'), 'ajax' => esc_html__('Add to Cart via Ajax', 'dynamic-content-for-elementor')], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_addtocart']]]]);
        $repeater->add_control('add_to_cart_ajax_forward', ['label' => esc_html__('Hide "View Cart" message after it is added', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'none', 'return_value' => 'none', 'render_type' => 'template', 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .wc-forward' => 'display: {{VALUE}}'], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_addtocart'], ['name' => 'add_to_cart_action', 'operator' => '==', 'value' => 'ajax']]]]);
        $repeater->add_control('add_to_cart_text', ['label' => esc_html__('Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Add to Cart', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_addtocart']]]]);
        // +********************* Title
        $repeater->add_control('html_tag', ['label' => esc_html__('HTML Tag', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_html_tags(), 'default' => 'h3', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_title']]]]);
        // +********************* Date
        $repeater->add_control('date_type', ['label' => esc_html__('Date Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['publish' => esc_html__('Publish Date', 'dynamic-content-for-elementor'), 'modified' => esc_html__('Last Modified Date', 'dynamic-content-for-elementor')], 'default' => 'publish', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_date']]]]);
        // added block_enable
        $repeater->add_control('date_format', ['label' => esc_html__('Date Format', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => 'd/<b>m</b>/y', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_date']]]]);
        // +********************* Terms of Taxonomy [metadata] (Category, Tag, CustomTax)
        $repeater->add_control('separator_chart', ['label' => esc_html__('Separator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => '/', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_termstaxonomy']]]]);
        $repeater->add_control('only_parent_terms', ['label' => esc_html__('Show only', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['both' => ['title' => esc_html__('Both', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-sitemap'], 'yes' => ['title' => esc_html__('Parents', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-female'], 'children' => ['title' => esc_html__('Children', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-child']], 'toggle' => \false, 'default' => 'both', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_termstaxonomy']]]]);
        $repeater->add_control('block_enable', ['label' => esc_html__('Block', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'block', 'render_type' => 'template', 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-term-item' => 'display: {{VALUE}}'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_termstaxonomy']]]]]);
        $repeater->add_control('icon_enable', ['label' => esc_html__('Icon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_termstaxonomy', 'item_date']]]]]);
        $repeater->add_control('taxonomy_filter', ['label' => esc_html__('Filter Taxonomy', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'separator' => 'before', 'label_block' => \true, 'multiple' => \true, 'options' => $taxonomies, 'placeholder' => esc_html__('Auto', 'dynamic-content-for-elementor'), 'description' => esc_html__('Use only terms in selected taxonomies. If empty all terms will be used.', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_termstaxonomy']]]]);
        // +********************* Content/Excerpt
        $repeater->add_control('content_type', ['label' => esc_html__('Content type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'toggle' => \false, 'label_block' => \false, 'options' => ['0' => esc_html__('Manual Excerpt', 'dynamic-content-for-elementor'), 'auto-excerpt' => esc_html__('Automatic Excerpt', 'dynamic-content-for-elementor'), '1' => esc_html__('Content', 'dynamic-content-for-elementor')], 'default' => '0', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_content']]]]);
        $repeater->add_control('textcontent_limit', ['label' => esc_html__('Content Character Limit', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => '', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_content'], ['name' => 'content_type', 'value' => '1']]]]);
        // +********************* ReadMore
        $repeater->add_control('readmore_text', ['label' => esc_html__('Text', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Read More', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_readmore']]]]);
        $repeater->add_control('readmore_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'sm', 'options' => Helper::get_button_sizes(), 'style_transfer' => \true, 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_readmore', 'item_addtocart']]]]]);
        // +********************* Item Token
        $repeater->add_control('item_token_code', ['label' => esc_html__('Token Code', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXTAREA, 'label_block' => \true, 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_token']]]]);
        // +********************* Author user
        $repeater->add_control('author_user_key', ['label' => esc_html__('User Key', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Field key or Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'multiple' => \true, 'query_type' => 'fields', 'object_type' => 'user', 'default' => ['avatar', 'display_name'], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_author']]]]);
        $repeater->add_control('author_image_size', ['label' => esc_html__('Avatar size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => '50', 'render_type' => 'template', 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_author'], ['name' => 'author_user_key', 'operator' => 'contains', 'value' => 'avatar'], ['name' => 'author_user_key', 'operator' => '!=', 'value' => ''], ['name' => 'author_user_key', 'operator' => '!=', 'value' => []]]]]);
        // +********************* Post Type
        $repeater->add_control('posttype_label', ['label' => esc_html__('Post Type Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'plural', 'options' => ['plural' => esc_html__('Plural', 'dynamic-content-for-elementor'), 'singular' => esc_html__('Singular', 'dynamic-content-for-elementor')], 'conditions' => ['terms' => [['name' => 'item_id', 'value' => 'item_posttype']]]]);
        $repeater->end_controls_tab();
        $repeater->start_controls_tab('tab_style', ['label' => esc_html__('Style', 'dynamic-content-for-elementor')]);
        $repeater->add_responsive_control('item_align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-right']], 'default' => '', 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}}' => 'text-align: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image']]]]]);
        $repeater->add_responsive_control('image_align', ['label' => esc_html__('Image Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['flex-start' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-center'], 'flex-end' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-h-align-right']], 'default' => 'top', 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}}.dce-item_image' => 'justify-content: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_image']]]]]);
        // -------- TYPOGRAPHY
        $repeater->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_item', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'render_type' => 'ui', 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} > *', 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image', 'item_readmore', 'item_addtocart']]]]]);
        // Read More
        $repeater->add_group_control(Group_Control_Typography::get_type(), ['name' => 'typography_item_readmore', 'label' => esc_html__('Typography', 'dynamic-content-for-elementor'), 'render_type' => 'ui', 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-button > *', 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_readmore', 'item_addtocart']]]]]);
        // -------- COLORS
        $repeater->add_control('color_item', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} > *' => 'color: {{VALUE}};', '{{WRAPPER}} {{CURRENT_ITEM}} a' => 'color: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image']]]]]);
        $repeater->add_control('color_item_separator', ['label' => esc_html__('Separator Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-term-item .dce-separator' => 'color: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_termstaxonomy']], ['name' => 'block_enable', 'value' => '']]]]);
        $repeater->add_control('color_item_icon', ['label' => esc_html__('Icon Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-icon' => 'color: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_termstaxonomy', 'item_date']], ['name' => 'icon_enable', 'value' => 'yes']]]]);
        $repeater->add_control('bgcolor_item', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} *:not(.dce-post-button) > *' => 'background-color: {{VALUE}};', '{{WRAPPER}} {{CURRENT_ITEM}} .dce-post-content' => 'background-color: {{VALUE}};', '{{WRAPPER}} {{CURRENT_ITEM}} a.dce-button' => 'background-color: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image', 'item_author']]]]]);
        $repeater->add_control('hover_color_item', ['label' => esc_html__('Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} a:hover' => 'color: {{VALUE}};'], 'conditions' => ['relation' => 'or', 'terms' => [['name' => 'metafield_type', 'operator' => '!=', 'value' => 'image']]]]);
        $repeater->add_control('hover_bgcolor_item', ['label' => esc_html__('Hover Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} a:hover' => 'background-color: {{VALUE}};'], 'conditions' => ['relation' => 'or', 'terms' => [['name' => 'metafield_type', 'operator' => '==', 'value' => 'button'], ['name' => 'item_id', 'operator' => '==', 'value' => 'item_addtocart']]]]);
        $repeater->add_control('title_added', ['label' => esc_html__('Added', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'conditions' => ['relation' => 'and', 'terms' => [['name' => 'item_id', 'operator' => '==', 'value' => 'item_addtocart'], ['name' => 'add_to_cart_action', 'operator' => '==', 'value' => 'ajax']]]]);
        $repeater->add_control('added_color_item', ['label' => esc_html__('Text Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} a.added' => 'color: {{VALUE}};'], 'conditions' => ['relation' => 'and', 'terms' => [['name' => 'item_id', 'operator' => '==', 'value' => 'item_addtocart'], ['name' => 'add_to_cart_action', 'operator' => '==', 'value' => 'ajax']]]]);
        $repeater->add_control('added_bgcolor_item', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} a.added' => 'background-color: {{VALUE}};'], 'conditions' => ['relation' => 'and', 'terms' => [['name' => 'item_id', 'operator' => '==', 'value' => 'item_addtocart'], ['name' => 'add_to_cart_action', 'operator' => '==', 'value' => 'ajax']]]]);
        $repeater->add_control('padding_item', ['label' => esc_html__('Padding Normal', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_readmore', 'item_addtocart', 'item_jetengine']]]]]);
        $repeater->add_control('heading_item_button', ['label' => esc_html__('Button', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['metafield_type' => 'button']]);
        $repeater->add_control('heading_item_image', ['label' => esc_html__('Image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['metafield_type' => 'image']]);
        $repeater->add_responsive_control('border_radius_item', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-button, {{WRAPPER}} {{CURRENT_ITEM}} .dce-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'condition' => ['metafield_type' => ['button', 'image']]]);
        $repeater->add_group_control(Group_Control_Border::get_type(), ['name' => 'border_item', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .dce-button', 'condition' => ['metafield_type' => ['button', 'image']]]);
        // ------------ SPACES
        $repeater->add_responsive_control('item_padding', ['label' => esc_html__('Padding Special', 'dynamic-content-for-elementor'), 'description' => esc_html__('Will also apply to some of the elements inside the item.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'rem'], 'separator' => 'before', 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}}:not(.dce-item_readmore) > *, {{WRAPPER}} {{CURRENT_ITEM}} a.dce-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $repeater->add_responsive_control('item_margin', ['label' => esc_html__('Margin', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'rem'], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $repeater->add_responsive_control('item_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image', 'item_readmore', 'item_custommeta', 'item_jetengine', 'item_metabox', 'item_addtocart']]]], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} > *' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $repeater->add_group_control(Group_Control_Border::get_type(), ['name' => 'item_border', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image', 'item_readmore', 'item_author', 'item_custommeta', 'item_jetengine', 'item_metabox', 'item_addtocart']]]], 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} > *']);
        $repeater->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'box_shadow', 'label' => esc_html__('Box Shadow', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_image', 'item_readmore', 'item_author', 'item_custommeta', 'item_jetengine', 'item_metabox', 'item_addtocart']]]], 'selector' => '{{WsRAPPER}} {{CURRENT_ITEM}} > *']);
        $repeater->add_responsive_control('item_in_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_image', 'item_readmore', 'item_addtocart']]]], 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} .dce-button, {{WRAPPER}} {{CURRENT_ITEM}} .dce-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $repeater->add_group_control(Group_Control_Border::get_type(), ['name' => 'item_in_border', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_image', 'item_readmore', 'item_author', 'item_addtocart']]]], 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .dce-button, {{WRAPPER}} {{CURRENT_ITEM}} .dce-img']);
        $repeater->add_group_control(Group_Control_Box_Shadow::get_type(), ['name' => 'box_in_shadow', 'label' => esc_html__('Box Shadow', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_image', 'item_readmore', 'item_author', 'item_addtocart']]]], 'selector' => '{{WRAPPER}} {{CURRENT_ITEM}} .dce-button, {{WRAPPER}} {{CURRENT_ITEM}} .dce-img']);
        $repeater->add_control('display_inline', ['label' => esc_html__('Display', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'inline-block', 'label_on' => 'Inline', 'label_off' => 'Block', 'return_value' => 'inline-block', 'selectors' => ['{{WRAPPER}} {{CURRENT_ITEM}} > *' => 'display: {{VALUE}};'], 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => 'in', 'value' => ['item_title', 'item_posttype', 'item_date', 'item_content', 'item_termstaxonomy']]]]]);
        $repeater->end_controls_tab();
        $repeater->start_controls_tab('tab_advanced', ['label' => esc_html__('Advanced', 'dynamic-content-for-elementor'), 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_custommeta', 'item_author', 'item_custommeta', 'item_jetengine', 'item_metabox', 'item_addtocart']]]]]);
        // ADVANCED - TAB
        $repeater->add_control('use_link', ['label' => esc_html__('Use link', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'conditions' => ['terms' => [['name' => 'item_id', 'operator' => '!in', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox', 'item_author', 'item_date', 'item_readmore', 'item_addtocart', 'item_content', 'item_posttype']]]]]);
        $repeater->add_control('open_target_blank', ['label' => esc_html__('Open link in a new window', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'conditions' => ['terms' => [['name' => 'use_link', 'value' => 'yes'], ['name' => 'item_id', 'operator' => '!in', 'value' => ['item_custommeta', 'item_jetengine', 'item_metabox', 'item_author', 'item_date', 'item_content', 'item_posttype', 'item_sku', 'item_addtocart', 'item_productprice']]]]]);
        $repeater->end_controls_tab();
        $repeater->start_controls_tab('tab_responsive', ['label' => esc_html__('Responsive', 'dynamic-content-for-elementor')]);
        /**
         * Responsive
         *
         * SPDX-SnippetBegin
         * SPDX-FileCopyrightText: Elementor
         * SPDX-License-Identifier: GPL-3.0-or-later
         */
        $repeater->add_control('responsive_description', ['raw' => esc_html__('Responsive visibility will take effect only on preview or live page, and not while editing in Elementor.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::RAW_HTML, 'content_classes' => 'elementor-descriptor']);
        $active_devices = Helper::get_active_devices_list();
        foreach ($active_devices as $breakpoint_key) {
            /**
             * @var \Elementor\Core\Breakpoints\Breakpoint $breakpoint
             */
            $breakpoint = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints($breakpoint_key);
            $label = 'desktop' === $breakpoint_key ? esc_html__('Desktop', 'dynamic-content-for-elementor') : $breakpoint->get_label();
            $repeater->add_control('hide_' . $breakpoint_key, [
                /* translators: %s: Device name. */
                'label' => \sprintf(esc_html__('Hide On %s', 'dynamic-content-for-elementor'), $label),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Hide', 'dynamic-content-for-elementor'),
                'label_off' => esc_html__('Show', 'dynamic-content-for-elementor'),
                'render_type' => 'template',
            ]);
        }
        /**
         * SPDX-SnippetEnd
         */
        $repeater->end_controls_tab();
        $repeater->end_controls_tabs();
        $this->add_control('list_items', ['type' => Controls_Manager::REPEATER, 'label' => esc_html__('Show these items', 'dynamic-content-for-elementor'), 'fields' => $repeater->get_controls(), 'item_actions' => ['add' => \true, 'duplicate' => \false, 'remove' => \true, 'sort' => \true], 'default' => [['item_id' => 'item_title'], ['item_id' => 'item_image']], 'title_field' => '{{{ posts_v2_item_id_to_label(item_id) }}}']);
        $this->end_controls_section();
        // ------------------------------------------------------------ [SECTION Hover Effects]
        $this->start_controls_section('section_hover_effect', ['label' => esc_html__('Hover Effects', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_CONTENT, 'condition' => ['_skin' => ['', 'grid', 'grid-filters', 'carousel', 'dualcarousel'], 'style_items!' => 'template']]);
        $this->start_controls_tabs('items_this_tab');
        $this->start_controls_tab('tab_hover_block', ['label' => esc_html__('Block', 'dynamic-content-for-elementor')]);
        $this->add_control('hover_animation', ['label' => esc_html__('Hover Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HOVER_ANIMATION]);
        $this->end_controls_tab();
        $this->start_controls_tab('tab_hover_image', ['label' => esc_html__('Image', 'dynamic-content-for-elementor')]);
        $this->add_responsive_control('hover_image_opacity', ['label' => esc_html__('Opacity', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'range' => ['px' => ['max' => 1, 'min' => 0.1, 'step' => 0.01]], 'selectors' => ['{{WRAPPER}} .dce-post-block:not(.dce-hover-effects) a.dce-post-image:hover, {{WRAPPER}} .dce-post-block.dce-hover-effects:hover a.dce-post-image' => 'opacity: {{SIZE}};']]);
        $this->add_group_control(Group_Control_Filters_CSS::get_type(), ['name' => 'hover_filters_image', 'label' => 'Filters image', 'selector' => '{{WRAPPER}} .dce-post-block:not(.dce-hover-effects) a.dce-post-image:hover img, {{WRAPPER}} .dce-post-block.dce-hover-effects:hover a.dce-post-image img']);
        $this->add_control('use_overlay_hover', ['label' => esc_html__('Overlay', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \false, 'label_block' => \false, 'separator' => 'before', 'options' => ['1' => ['title' => esc_html__('Yes', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-check'], '0' => ['title' => esc_html__('No', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-ban']], 'default' => '0']);
        $this->add_group_control(Group_Control_Background::get_type(), ['name' => 'overlay_color_hover', 'label' => esc_html__('Background', 'dynamic-content-for-elementor'), 'types' => ['classic', 'gradient'], 'selector' => '{{WRAPPER}} a.dce-post-image.dce-post-overlayhover:before', 'condition' => ['use_overlay_hover' => '1']]);
        $this->end_controls_tab();
        $this->start_controls_tab('tab_hover_content', ['label' => esc_html__('Content', 'dynamic-content-for-elementor'), 'condition' => ['style_items!' => 'default']]);
        $this->add_control('hover_content_animation', ['label' => esc_html__('Hover Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HOVER_ANIMATION, 'condition' => ['style_items!' => 'float']]);
        $this->add_control('hover_text_heading_float', ['label' => esc_html__('Float Style', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['style_items' => 'float']]);
        $this->add_control('hover_text_effect', ['label' => esc_html__('TextZone Effect', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => '', 'options' => ['' => esc_html__('None', 'dynamic-content-for-elementor'), 'fade' => 'Fade', 'slidebottom' => 'Slide bottom', 'slidetop' => 'Slide top', 'slideleft' => 'Slide left', 'slideright' => 'Slide right', 'cssanimations' => 'CSS Animations'], 'render_type' => 'template', 'prefix_class' => 'dce-hovertexteffect-', 'condition' => ['style_items' => 'float']]);
        $this->add_control('hover_text_effect_timingFunction', ['label' => esc_html__('Effect Timing function', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_anim_timing_functions(), 'default' => 'ease-in-out', 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-hover-effect-content' => 'transition-timing-function: {{VALUE}}; -webkit-transition-timing-function: {{VALUE}};'], 'condition' => ['hover_text_effect!' => ['', 'cssanimations'], 'style_items' => 'float']]);
        $this->add_control('heading_hover_text_effect_in', ['label' => esc_html__('Animation IN', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float']]);
        $this->add_control('hover_text_effect_animation_in', ['label' => esc_html__('Animation effect', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_anim_in(), 'default' => 'fadeIn', 'frontend_available' => \true, 'render_type' => 'template', 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float'], 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-hover-effect-content.dce-open' => 'animation-name: {{VALUE}}; -webkit-animation-name: {{VALUE}};']]);
        $this->add_control('hover_text_effect_timingFunction_in', ['label' => esc_html__('Effect Timing function', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_anim_timing_functions(), 'default' => 'ease-in-out', 'selectors' => ['{{WRAPPER}} .dce-post-item:hover .dce-hover-effect-content.dce-open' => 'animation-timing-function: {{VALUE}}; -webkit-animation-timing-function: {{VALUE}};'], 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float']]);
        $this->add_control('hover_text_effect_speed_in', ['label' => esc_html__('Speed (sec.)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 0.5, 'min' => 0.1, 'max' => 2, 'step' => 0.1, 'dynamic' => ['active' => \false], 'selectors' => ['{{WRAPPER}} .dce-post-item:hover .dce-hover-effect-content.dce-open' => 'animation-duration: {{VALUE}}s; -webkit-animation-duration: {{VALUE}}s;'], 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float']]);
        $this->add_control('heading_hover_text_effect_out', ['label' => esc_html__('Animation OUT', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float']]);
        $this->add_control('hover_text_effect_animation_out', ['label' => esc_html__('Animation effect', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_anim_out(), 'default' => 'fadeOut', 'frontend_available' => \true, 'render_type' => 'template', 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float'], 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-hover-effect-content.dce-close' => 'animation-name: {{VALUE}}; -webkit-animation-name: {{VALUE}};']]);
        $this->add_control('hover_text_effect_timingFunction_out', ['label' => esc_html__('Effect Timing function', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'groups' => Helper::get_anim_timing_functions(), 'default' => 'ease-in-out', 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-hover-effect-content.dce-close' => 'animation-timing-function: {{VALUE}}; -webkit-animation-timing-function: {{VALUE}};'], 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float']]);
        $this->add_control('hover_text_effect_speed_out', ['label' => esc_html__('Speed (sec.)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 0.5, 'min' => 0.1, 'max' => 2, 'step' => 0.1, 'dynamic' => ['active' => \false], 'selectors' => ['{{WRAPPER}} .dce-post-item .dce-hover-effect-content.dce-close' => 'animation-duration: {{VALUE}}s; -webkit-animation-duration: {{VALUE}}s;'], 'condition' => ['hover_text_effect' => 'cssanimations', 'style_items' => 'float']]);
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
    }
    /**
     * Register Skins Images
     *
     * @return void
     */
    protected function register_skins_images()
    {
        // skin: Template
        $this->add_control('skin_dis_customtemplate', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/template.png" />', 'content_classes' => 'dce-skin-dis dce-ect-dis', 'condition' => ['_skin' => ['', 'grid', 'carousel', 'grid-filters', 'dualcarousel'], 'style_items' => 'template']]);
        // Skin - Pagination Top
        $this->add_control('skin_pagination_top', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/pagination.png" />', 'content_classes' => 'dce-skin-dis dce-pagination-top', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['top', 'both']], ['name' => 'rtl', 'operator' => '==', 'value' => '']]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['top', 'both']], ['name' => 'rtl', 'operator' => '==', 'value' => '']]]]]]);
        // Skin - Pagination Top
        $this->add_control('skin_pagination_top_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/pagination_rtl.png" />', 'content_classes' => 'dce-skin-dis dce-pagination-top', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['top', 'both']], ['name' => 'rtl', 'operator' => '!=', 'value' => '']]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['top', 'both']], ['name' => 'rtl', 'operator' => '!=', 'value' => '']]]]]]);
        // skin: Carousel
        $this->add_control('skin_dis_default', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/default.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'row']]);
        // skin: Grid
        $this->add_control('skin_dis_grid', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/grid.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'grid', 'rtl' => '']]);
        $this->add_control('skin_dis_grid_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/grid_rtl.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'grid', 'rtl!' => '']]);
        // skin: Carousel
        $this->add_control('skin_dis_carousel', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/carousel.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'carousel']]);
        // skin: Filters
        $this->add_control('skin_dis_filters', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/filters.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'grid-filters', 'rtl' => '']]);
        $this->add_control('skin_dis_filters_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/filters_rtl.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'grid-filters', 'rtl!' => '']]);
        // skin: Dual Carousel
        $this->add_control('skin_dis_dualcarousel', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/dualcarousel.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'dualcarousel']]);
        // skin: Accordion
        $this->add_control('skin_dis_accordion', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/accordion.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'accordion', 'rtl' => '']]);
        $this->add_control('skin_dis_accordion_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/accordion_rtl.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'accordion', 'rtl!' => '']]);
        // skin: List
        $this->add_control('skin_dis_list', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/list.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'list', 'rtl' => '']]);
        $this->add_control('skin_dis_list_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/list_rtl.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'list', 'rtl!' => '']]);
        // skin: Table
        $this->add_control('skin_dis_table', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/table.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'table']]);
        // skin: Timeline
        $this->add_control('skin_dis_timeline', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/timeline.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'timeline', 'rtl' => '']]);
        $this->add_control('skin_dis_timeline_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/timeline_rtl.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'timeline', 'rtl!' => '']]);
        // skin: gridtofullscreen3d
        $this->add_control('skin_dis_gridtofullscreen3d', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/gridtofullscreen3d.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'gridtofullscreen3d']]);
        // skin: crossroadsslideshow
        $this->add_control('skin_dis_crossroadsslideshow', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/crossroadsslideshow.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => 'crossroadsslideshow']]);
        // skin: 3d
        $this->add_control('skin_dis_3d', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/3d.png" />', 'content_classes' => 'dce-skin-dis', 'condition' => ['_skin' => '3d']]);
        // Skin - Pagination Bottom
        $this->add_control('skin_pagination_bottom', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/pagination.png" />', 'content_classes' => 'dce-skin-dis dce-pagination-bottom', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['bottom', 'both']], ['name' => 'rtl', 'operator' => '==', 'value' => '']]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['bottom', 'both']], ['name' => 'rtl', 'operator' => '==', 'value' => '']]]]]]);
        $this->add_control('skin_pagination_bottom_rtl', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/pagination_rtl.png" />', 'content_classes' => 'dce-skin-dis dce-pagination-bottom', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['bottom', 'both']], ['name' => 'rtl', 'operator' => '!=', 'value' => '']]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => ''], ['name' => 'pagination_position', 'operator' => 'in', 'value' => ['bottom', 'both']], ['name' => 'rtl', 'operator' => '!=', 'value' => '']]]]]]);
        // skin: infinitescroll
        $this->add_control('skin_dis_infinitescroll', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => '<img src="' . DCE_URL . 'assets/img/skins/infinitescroll.png" />', 'content_classes' => 'dce-skin-dis dce-pagination-dis', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list']], ['name' => 'post_offset', 'operator' => 'in', 'value' => [0, '']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => 'yes']]], ['terms' => [['name' => '_skin', 'operator' => 'in', 'value' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion']], ['name' => 'query_type', 'operator' => '!in', 'value' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites']], ['name' => 'pagination_enable', 'operator' => '==', 'value' => 'yes'], ['name' => 'infiniteScroll_enable', 'operator' => '==', 'value' => 'yes']]]]]]);
    }
    protected function register_pagination_controls()
    {
        $this->start_controls_section('section_pagination', ['label' => esc_html__('Pagination', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_CONTENT, 'condition' => ['pagination_enable!' => '', 'infiniteScroll_enable' => '', 'post_offset' => [0, ''], '_skin' => ['', 'grid', 'grid-filters', 'gridtofullscreen3d', 'accordion', 'list', 'table']]]);
        $this->add_control('pagination_position', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['top' => esc_html__('Top', 'dynamic-content-for-elementor'), 'bottom' => esc_html__('Bottom', 'dynamic-content-for-elementor'), 'both' => esc_html__('Both', 'dynamic-content-for-elementor')], 'default' => 'bottom']);
        $this->add_control('pagination_show_numbers', ['label' => esc_html__('Show Numbers', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $this->add_control('pagination_range', ['label' => esc_html__('Range of numbers', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => 4, 'condition' => ['pagination_show_numbers' => 'yes']]);
        $this->add_control('pagination_show_first_last_pages', ['label' => esc_html__('Always Show First and Last Pages', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['pagination_show_numbers' => 'yes']]);
        $this->add_control('pagination_show_prevnext', ['label' => esc_html__('Show Prev/Next', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before']);
        $this->add_control('prevnext_notice', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('These arrows only appear when the number of pages that result from the query exceed the value in the setting "Range of numbers"', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'condition' => ['pagination_show_prevnext' => 'yes']]);
        $this->add_control('pagination_icon_prev_mirror_next', ['label' => esc_html__("Apply the 'Next' icon style to the 'Prev' icon with a mirrored direction", 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['pagination_show_prevnext' => 'yes']]);
        $this->add_control('selected_pagination_icon_prevnext_previous', ['label' => esc_html__('Icon Previous', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-long-arrow-alt-left', 'library' => 'fa-solid'], 'recommended' => ['fa-solid' => ['arrow-left', 'angle-left', 'long-arrow-alt-left', 'arrow-alt-circle-left', 'arrow-circle-left', 'caret-left', 'caret-square-left', 'chevron-circle-left', 'chevron-left', 'hand-point-left']], 'condition' => ['pagination_show_prevnext' => 'yes', 'pagination_icon_prev_mirror_next' => '']]);
        $this->add_control('selected_pagination_icon_prevnext', ['label' => esc_html__('Icon Next', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-long-arrow-alt-right', 'library' => 'fa-solid'], 'recommended' => ['fa-solid' => ['arrow-right', 'angle-right', 'long-arrow-alt-right', 'arrow-alt-circle-right', 'arrow-circle-right', 'caret-right', 'caret-square-right', 'chevron-circle-right', 'chevron-right', 'hand-point-right']], 'condition' => ['pagination_show_prevnext' => 'yes']]);
        $this->add_control('pagination_prev_label', ['label' => esc_html__('Previous Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Previous', 'dynamic-content-for-elementor'), 'condition' => ['pagination_show_prevnext' => 'yes']]);
        $this->add_control('pagination_next_label', ['label' => esc_html__('Next Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Next', 'dynamic-content-for-elementor'), 'condition' => ['pagination_show_prevnext' => 'yes']]);
        $this->add_control('pagination_show_firstlast', ['label' => esc_html__('Show First/Last', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before']);
        $this->add_control('pagination_icon_first_mirror_last', ['label' => esc_html__("Apply the 'Last' icon style to the 'First' icon with a mirrored direction", 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['pagination_show_prevnext' => 'yes']]);
        $this->add_control('selected_pagination_icon_firstlast_first', ['label' => esc_html__('Icon First', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-long-arrow-alt-left', 'library' => 'fa-solid'], 'recommended' => ['fa-solid' => ['arrow-left', 'angle-left', 'long-arrow-alt-left', 'arrow-alt-circle-left', 'arrow-circle-left', 'caret-left', 'caret-square-left', 'chevron-circle-left', 'chevron-left', 'hand-point-left']], 'condition' => ['pagination_show_firstlast' => 'yes', 'pagination_icon_first_mirror_last' => '']]);
        $this->add_control('selected_pagination_icon_firstlast', ['label' => esc_html__('Icon Last', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-long-arrow-alt-right', 'library' => 'fa-solid'], 'recommended' => ['fa-solid' => ['arrow-right', 'angle-right', 'long-arrow-alt-right', 'arrow-alt-circle-right', 'arrow-circle-right', 'caret-right', 'caret-square-right', 'chevron-circle-right', 'chevron-right', 'hand-point-right']], 'condition' => ['pagination_show_firstlast' => 'yes']]);
        $this->add_control('pagination_first_label', ['label' => esc_html__('Previous Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('First', 'dynamic-content-for-elementor'), 'condition' => ['pagination_show_firstlast' => 'yes']]);
        $this->add_control('pagination_last_label', ['label' => esc_html__('Next Label', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Last', 'dynamic-content-for-elementor'), 'condition' => ['pagination_show_firstlast' => 'yes']]);
        $this->add_control('pagination_show_progression', ['label' => esc_html__('Show Progression', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before']);
        $this->end_controls_section();
    }
    protected function register_infinitescroll_controls()
    {
        $this->start_controls_section('section_infinitescroll', ['label' => esc_html__('Infinite Scroll', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_CONTENT, 'condition' => ['pagination_enable' => 'yes', 'infiniteScroll_enable' => 'yes']]);
        $this->add_control('infiniteScroll_trigger', ['label' => esc_html__('Trigger', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'scroll', 'frontend_available' => \true, 'options' => ['scroll' => esc_html__('On Scroll Page', 'dynamic-content-for-elementor'), 'button' => esc_html__('On Click Button', 'dynamic-content-for-elementor')]]);
        $this->add_control('infiniteScroll_label_button', ['label' => esc_html__('Label Button', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('View more', 'dynamic-content-for-elementor'), 'condition' => ['infiniteScroll_trigger' => 'button']]);
        $this->add_control('infiniteScroll_enable_status', ['label' => esc_html__('Enable Status', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'separator' => 'before']);
        $this->add_control('infiniteScroll_loading_type', ['label' => esc_html__('Loading Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'toggle' => \false, 'options' => ['ellips' => ['title' => esc_html__('Ellips', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-ellipsis-h'], 'text' => ['title' => esc_html__('Label Text', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-font']], 'default' => 'ellips', 'separator' => 'before', 'condition' => ['infiniteScroll_enable_status' => 'yes']]);
        $this->add_control('infiniteScroll_label_loading', ['label' => esc_html__('Label Loading', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('Loading...', 'dynamic-content-for-elementor'), 'condition' => ['infiniteScroll_enable_status' => 'yes', 'infiniteScroll_loading_type' => 'text']]);
        $this->add_control('infiniteScroll_label_last', ['label' => esc_html__('Label Last', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('End of content', 'dynamic-content-for-elementor'), 'condition' => ['infiniteScroll_enable_status' => 'yes']]);
        $this->add_control('infiniteScroll_label_error', ['label' => esc_html__('Label Error', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => esc_html__('No more articles to load', 'dynamic-content-for-elementor'), 'condition' => ['infiniteScroll_enable_status' => 'yes']]);
        $this->add_control('infiniteScroll_enable_history', ['label' => esc_html__('Enable History', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'separator' => 'before', 'frontend_available' => \true]);
        $this->end_controls_section();
    }
    protected function register_query_controls()
    {
        $taxonomies = Helper::get_taxonomies();
        $this->start_controls_section('section_query', ['label' => esc_html__('Query', 'dynamic-content-for-elementor')]);
        $this->add_control('dynamic_archives_active', ['type' => Controls_Manager::RAW_HTML, 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'raw' => esc_html__('The query is automatically set to current object', 'dynamic-content-for-elementor'), 'condition' => ['query_type' => 'dynamic_archives']]);
        $this->add_control('query_type', ['label' => esc_html__('Query Type', 'dynamic-content-for-elementor'), 'type' => 'images_selector', 'toggle' => \false, 'type_selector' => 'icon', 'columns_grid' => 5, 'separator' => 'before', 'options' => [
            'get_cpt' => ['title' => esc_html__('From Post Type', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'eicon-post-content'],
            // dynamic_mode is deprecated, use dynamic_archives instead
            'relationship' => ['title' => esc_html__('ACF Relationship', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'fa fa-american-sign-language-interpreting'],
            'pods_relationship' => ['title' => esc_html__('Pods Relationship', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-relation'],
            'search_filter' => ['title' => 'Search & Filter Pro', 'return_val' => 'val', 'icon' => 'dce-logo-search-and-filter-pro'],
            'post_parent' => ['title' => esc_html__('From Post Parent', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'fa fa-sitemap'],
            'search_page' => ['title' => esc_html__('Search Results', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'fa fa-search'],
            'specific_posts' => ['title' => esc_html__('From Specific Posts', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'fa fa-list-ul'],
            'id_list' => ['title' => esc_html__('ID List', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'fa fa-clipboard-list'],
            'sticky_posts' => ['title' => esc_html__('Sticky Posts', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'eicon-star'],
            'custom_query' => ['title' => esc_html__('Custom Query Code', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'eicon-editor-code'],
        ], 'default' => 'get_cpt']);
        $this->add_control('specific_page_parent', ['label' => esc_html__('Show children from this parent-page', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Page Title', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'condition' => ['query_type' => 'post_parent', 'parent_source' => '', 'child_source' => '']]);
        $this->add_control('dynamic_parent_heading', ['label' => esc_html__('Dynamic', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['query_type' => 'post_parent']]);
        $this->add_control('parent_source', ['label' => esc_html__('Sibling Posts', 'dynamic-content-for-elementor'), 'description' => esc_html__('Get posts that share the same parent as the current post.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['query_type' => 'post_parent']]);
        $this->add_control('child_source', ['label' => esc_html__('Child Posts', 'dynamic-content-for-elementor'), 'description' => esc_html__('Get posts that have the current post as their parent.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['query_type' => 'post_parent', 'parent_source' => '']]);
        // --------------------------------- [ Specific Posts-Pages ]
        $repeater = new Repeater();
        $repeater->add_control('repeater_specific_posts', ['label' => esc_html__('Select Post', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'show_label' => \false, 'placeholder' => esc_html__('Select post', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts']);
        $this->add_control('specific_posts', ['label' => esc_html__('Specific Posts', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::REPEATER, 'prevent_empty' => \false, 'default' => [], 'separator' => 'after', 'fields' => $repeater->get_controls(), 'title_field' => 'ID: {{{ repeater_specific_posts }}}', 'condition' => ['query_type' => 'specific_posts']]);
        if (Helper::is_searchandfilterpro_active()) {
            if (Helper::is_search_filter_pro_version(2)) {
                $this->add_control('search_filter_id', ['label' => esc_html__('Filter', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'label_block' => \true, 'placeholder' => esc_html__('Select the filter', 'dynamic-content-for-elementor'), 'query_type' => 'posts', 'object_type' => 'search-filter-widget', 'condition' => ['query_type' => 'search_filter']]);
            } elseif (Helper::is_search_filter_pro_version(3)) {
                $this->add_control('search_filter_v3_notice', ['label' => esc_html__('Query', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NOTICE, 'heading' => esc_html__('Warning', 'dynamic-content-for-elementor'), 'content' => esc_html__('Search & Filter Pro version 3.0 is not supported. Please upgrade to version 3.1 or later.', 'dynamic-content-for-elementor'), 'notice_type' => 'warning', 'condition' => ['query_type' => 'search_filter']]);
            } elseif (Helper::is_search_filter_pro_version(3.1)) {
                $this->add_control('search_filter_v3_id', ['label' => esc_html__('Query', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'label_block' => \true, 'placeholder' => esc_html__('Select the query', 'dynamic-content-for-elementor'), 'query_type' => 'search_and_filter_v3_query_ids', 'dynamic' => ['active' => \false], 'condition' => ['query_type' => 'search_filter']]);
            }
        } else {
            $this->add_control('search_filter_notice', ['type' => Controls_Manager::RAW_HTML, 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'raw' => \sprintf(
                /* translators: %1$s: opening tag for the link, %2$s: closing tag for the link */
                esc_html__('Combine the power of Search & Filter Pro front end filters with Dynamic Posts! Create front end search forms and filter layouts using the advanced query and filter builder of Search & Filter Pro. Note: In order to use this feature you need install Search & Filter Pro. Search & Filter Pro is a premium product - you can %1$sget it here%2$s.', 'dynamic-content-for-elementor'),
                '<a href="https://searchandfilter.com">',
                '</a>'
            ), 'condition' => ['query_type' => 'search_filter']]);
        }
        $this->add_control('id_list', ['label' => esc_html__('ID List', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'label_block' => \true, 'description' => esc_html__('Type a comma-separated list of ids (e.g. 1, 100, 250)', 'dynamic-content-for-elementor'), 'dynamic' => ['active' => \true], 'condition' => ['query_type' => 'id_list']]);
        $this->add_control('favorites_scope', ['label' => esc_html__('Favorites Scope', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['user' => esc_html__('User', 'dynamic-content-for-elementor'), 'cookie' => esc_html__('Cookie', 'dynamic-content-for-elementor')], 'default' => 'user', 'condition' => ['query_type' => 'favorites']]);
        $this->add_control('favorites_user_source', ['label' => esc_html__('User', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['current' => esc_html__('Current User', 'dynamic-content-for-elementor'), 'author' => esc_html__('Current Author', 'dynamic-content-for-elementor'), 'other' => esc_html__('Another User', 'dynamic-content-for-elementor')], 'default' => 'current', 'condition' => ['query_type' => 'favorites', 'favorites_scope' => 'user']]);
        $this->add_control('favorites_user_id', ['label' => esc_html__('Select User', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('User Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'users', 'condition' => ['query_type' => 'favorites', 'favorites_scope' => 'user', 'favorites_user_source' => 'other']]);
        $this->add_control('favorites_key', ['label' => esc_html__('Favorites Key', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'label_block' => \true, 'default' => 'my_favorites', 'description' => esc_html__('Set here the key you used in the widget "Add to Favorites"', 'dynamic-content-for-elementor'), 'dynamic' => ['active' => \true], 'condition' => ['query_type' => 'favorites']]);
        if (\DynamicContentForElementor\Helper::can_register_unsafe_controls()) {
            $this->add_control('custom_query_code', ['label' => esc_html__('Custom Query Code', 'dynamic-content-for-elementor'), 'description' => esc_html__('Here you should return a valid list of arguments for the WP_Query', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CODE, 'language' => 'php', 'rows' => 10, 'placeholder' => "return array ( 'post_type' => 'any' );", 'label_block' => \true, 'condition' => ['query_type' => 'custom_query']]);
        } else {
            $this->add_control('custom_query_notice', ['type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('You will need administrator capabilities to edit these settings.', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'condition' => ['query_type' => 'custom_query']]);
        }
        if (!Helper::is_woocommerce_active()) {
            $this->add_control('products_notice', ['type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('In order to use this feature you need install WooCommerce.', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'condition' => ['query_type' => ['products_cart', 'product_upsells', 'product_crosssells', 'product_variations']]]);
        }
        // On ACF Relationship widget we need to move these controls on another section
        if ('DynamicContentForElementor\\Widgets\\AcfRelationship' !== \get_called_class()) {
            $this->add_acf_relationship_controls();
        }
        if (Helper::is_pods_active()) {
            $this->add_control('pods_relationship_field', ['label' => esc_html__('PODS Relationship field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Select the field...', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'pods', 'object_type' => 'relationship', 'default' => '0', 'condition' => ['query_type' => 'pods_relationship']]);
        } else {
            $this->add_control('pods_notice', ['label' => esc_html__('Important Note', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::RAW_HTML, 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'raw' => \sprintf(
                /* translators: %1$s: opening tag for the link, %2$s: closing tag for the link */
                esc_html__('In order to use this feature you need install PODS. You can %1$sdownload it free here%2$s.', 'dynamic-content-for-elementor'),
                '<a href="https://pods.io">',
                '</a>'
            ), 'condition' => ['query_type' => 'pods_relationship']]);
        }
        $this->add_control('post_type', ['label' => esc_html__('Post Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => Helper::get_public_post_types(), 'multiple' => \true, 'label_block' => \true, 'default' => [], 'condition' => ['query_type' => ['get_cpt', 'search_page', 'sticky_posts']]]);
        $this->add_control('include_variations_product', ['label' => esc_html__('Include Variations', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'conditions' => ['relation' => 'or', 'terms' => [['relation' => 'and', 'terms' => [['name' => 'query_type', 'operator' => '===', 'value' => 'get_cpt'], ['name' => 'post_type', 'operator' => 'contains', 'value' => 'product']]], ['relation' => 'or', 'terms' => [['name' => 'query_type', 'operator' => 'in', 'value' => ['products_cart', 'product_upsells', 'product_crosssells']]]]]]]);
        $this->add_control('post_status', ['label' => esc_html__('Post Status', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => \array_merge(['future' => esc_html__('Future', 'dynamic-content-for-elementor')], get_post_statuses()), 'multiple' => \true, 'label_block' => \true, 'default' => 'publish', 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'favorites']]]);
        $this->add_control('ignore_sticky_posts', ['label' => esc_html__('Ignore Sticky Posts', 'dynamic-content-for-elementor'), 'description' => esc_html__('Ignores that a post is sticky and shows the posts in the normal order. Your sticky posts will appear in the loop, however they will not be placed on the top', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'favorites'], 'remove_sticky_posts' => '']]);
        $this->add_control('force_sticky_posts_at_the_top', ['label' => esc_html__('Force Sticky Posts at the top', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'conditions' => ['relation' => 'or', 'terms' => [['terms' => [['name' => 'query_type', 'operator' => 'in', 'value' => ['get_cpt', 'dynamic_mode', 'favorites']], ['name' => 'remove_sticky_posts', 'operator' => '==', 'value' => ''], ['name' => 'ignore_sticky_posts', 'operator' => '==', 'value' => '']]], ['terms' => [['name' => 'query_type', 'operator' => 'in', 'value' => ['specific_posts']]]]]]]);
        $this->add_control('remove_sticky_posts', ['label' => esc_html__('Remove Sticky Posts from the loop', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'specific_posts', 'id_list']]]);
        $this->add_control('num_posts', ['label' => esc_html__('Results per page', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'separator' => 'before', 'default' => '10', 'condition' => ['query_type' => ['get_cpt', 'relationship', 'dynamic_mode', 'pods_relationship', 'search_page', 'post_parent', 'sticky_posts', 'favorites', 'products_cart', 'woo_products_on_sale', 'product_upsells', 'product_variations', 'product_crosssells', 'id_list']]]);
        $this->add_control('num_posts_notice', ['type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('If you use pagination in Query Type "Dynamic - Current Query" the number of results per page should match the value you set in "Settings > Reading > Blog pages show at most". You have set the value', 'dynamic-content-for-elementor') . ' ' . get_option('posts_per_page'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'separator' => 'before', 'condition' => ['query_type' => ['dynamic_mode']]]);
        $this->add_control('post_offset', ['label' => esc_html__('Posts Offset', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'description' => esc_html__('Warning: posts offset doesn\'t support pagination', 'dynamic-content-for-elementor'), 'default' => 0, 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'favorites', 'relationship'], 'num_posts!' => '-1']]);
        $this->add_control('orderby', ['label' => esc_html__('Order By', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_post_orderby_options(), 'default' => 'date', 'condition' => ['query_type!' => ['search_filter', 'custom_query', 'dynamic_archives']]]);
        $this->add_control('meta_type', ['label' => esc_html__('Meta Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_post_orderby_meta_value_types(), 'default' => 'CHAR', 'condition' => ['orderby' => 'meta_value', 'query_type!' => ['search_filter']]]);
        $this->add_control('metakey', ['label' => esc_html__('Meta Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'separator' => 'after', 'dynamic' => ['active' => \false], 'condition' => ['orderby' => ['meta_value_date', 'meta_value_num', 'meta_value'], 'query_type!' => ['search_filter']]]);
        $this->add_control('order', ['label' => esc_html__('Order', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['ASC' => esc_html__('Ascending', 'dynamic-content-for-elementor'), 'DESC' => esc_html__('Descending', 'dynamic-content-for-elementor')], 'default' => 'DESC', 'condition' => ['query_type!' => ['search_filter', 'custom_query', 'dynamic_archives'], 'orderby!' => ['rand', 'none', 'post__in']]]);
        $this->add_control('reverse_order_posts', ['label' => esc_html__('Reverse Order of Posts', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'condition' => ['query_type' => ['specific_posts', 'favorites'], 'orderby' => ['post__in']]]);
        // --------------------------------- [ Posts Exclusion ]
        $this->add_control('heading_query_options', ['label' => esc_html__('Exclude', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'search_page', 'sticky_posts', 'post_parent']]]);
        $this->add_control('exclude_io', ['label' => esc_html__('Current Post', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'sticky_posts', 'post_parent']]]);
        $this->add_control('exclude_page_parent', ['label' => esc_html__('Parent Pages', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'sticky_posts']]]);
        $this->add_control('exclude_page_child', ['label' => esc_html__('Child Pages', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'sticky_posts']]]);
        $this->add_control('exclude_posts', ['label' => esc_html__('Specific Posts', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Post Title', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'multiple' => \true, 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'search_page', 'sticky_posts', 'post_parent', 'product_variations']]]);
        $this->end_controls_section();
        // Query Filter
        $this->start_controls_section('section_query_filter', ['label' => esc_html__('Query Filter', 'dynamic-content-for-elementor'), 'condition' => ['query_type' => ['get_cpt', 'dynamic_mode', 'search_page', 'relationship', 'id_list', 'sticky_posts', 'favorites']]]);
        $this->add_control('query_filter', ['label' => esc_html__('By', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => ['date' => esc_html__('Date', 'dynamic-content-for-elementor'), 'term' => esc_html__('Terms and Taxonomies', 'dynamic-content-for-elementor'), 'author' => esc_html__('Author', 'dynamic-content-for-elementor'), 'metakey' => esc_html__('Metakey', 'dynamic-content-for-elementor'), 'query_id' => esc_html__('Query ID', 'dynamic-content-for-elementor')], 'multiple' => \true, 'label_block' => \true, 'default' => []]);
        // +********************* Date
        $this->add_control('heading_query_filter_date', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('Date Filters', 'dynamic-content-for-elementor'), 'label_block' => \false, 'separator' => 'before', 'content_classes' => 'dce-icon-heading', 'condition' => ['query_filter' => 'date']]);
        $this->add_control('querydate_mode', ['label' => esc_html__('Date Filter', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => '', 'label_block' => \true, 'options' => ['' => esc_html__('No Filter', 'dynamic-content-for-elementor'), 'past' => esc_html__('Past', 'dynamic-content-for-elementor'), 'future' => esc_html__('Future', 'dynamic-content-for-elementor'), 'today' => esc_html__('Today', 'dynamic-content-for-elementor'), 'yesterday' => esc_html__('Yesterday', 'dynamic-content-for-elementor'), 'days' => esc_html__('Past Days', 'dynamic-content-for-elementor'), 'weeks' => esc_html__('Past Weeks', 'dynamic-content-for-elementor'), 'months' => esc_html__('Past Months', 'dynamic-content-for-elementor'), 'years' => esc_html__('Past Years', 'dynamic-content-for-elementor'), 'period' => esc_html__('Period', 'dynamic-content-for-elementor')], 'condition' => ['query_filter' => 'date']]);
        $this->add_control('querydate_field', ['label' => esc_html__('Date Field', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'label_block' => \false, 'options' => ['post_date' => ['title' => esc_html__('Publish Date', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-calendar'], 'post_modified' => ['title' => esc_html__('Modified Date', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-edit'], 'post_meta' => ['title' => esc_html__('Post Meta', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-square']], 'default' => 'post_date', 'toggle' => \false, 'condition' => ['query_filter' => 'date', 'querydate_mode!' => ['', 'future']]]);
        $this->add_control('querydate_use_utc', ['label' => esc_html__('Stored as UTC', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('Date is Saved in the database as UTC. This is usually not the case for ACF Fields.', 'dynamic-content-for-elementor'), 'conditions' => ['relation' => 'or', 'terms' => [['name' => 'querydate_field', 'operator' => '==', 'value' => 'post_meta'], ['name' => 'querydate_mode', 'operator' => '==', 'value' => 'future']]]]);
        $this->add_control('querydate_field_meta', ['label' => esc_html__('Meta Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'description' => esc_html__('Selected Post Meta value must be stored in the "Ymd" format, like ACF Date', 'dynamic-content-for-elementor'), 'separator' => 'before', 'dynamic' => ['active' => \false], 'condition' => ['query_filter' => 'date', 'querydate_mode!' => 'future', 'querydate_field' => 'post_meta']]);
        $this->add_control('querydate_field_meta_format', ['label' => esc_html__('Meta Date Format', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => esc_html__('Ymd', 'dynamic-content-for-elementor'), 'label_block' => \true, 'default' => esc_html__('Ymd', 'dynamic-content-for-elementor'), 'condition' => ['query_filter' => 'date', 'querydate_mode!' => 'future', 'querydate_field' => 'post_meta']]);
        $this->add_control('querydate_field_meta_future', ['label' => esc_html__('Meta Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'separator' => 'before', 'dynamic' => ['active' => \false], 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'future']]);
        $this->add_control('querydate_field_meta_future_format', ['label' => esc_html__('Meta Date Format', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => esc_html__('Y-m-d', 'dynamic-content-for-elementor'), 'label_block' => \false, 'default' => esc_html__('Ymd', 'dynamic-content-for-elementor'), 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'future']]);
        $this->add_control('querydate_field_meta_future_contains_today', ['label' => esc_html__('Future dates contains today\'s date', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'future']]);
        $this->add_control('querydate_range', ['label' => esc_html__('Number of (days/months/years) elapsed', 'dynamic-content-for-elementor'), 'label_block' => \false, 'type' => Controls_Manager::NUMBER, 'default' => 1, 'condition' => ['query_filter' => 'date', 'querydate_mode' => ['days', 'weeks', 'months', 'years']]]);
        $this->add_control('querydate_date_type', ['label' => esc_html__('Date Input Mode', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'label_block' => \true, 'options' => ['static' => ['title' => esc_html__('Static', 'dynamic-content-for-elementor'), 'icon' => 'eicon-pencil'], 'dynamicstring' => ['title' => esc_html__('Dynamic', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-cogs']], 'default' => '_dynamic', 'toggle' => \false, 'separator' => 'before', 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'period']]);
        $this->add_control('querydate_date_from', ['label' => esc_html__('Date from', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DATE_TIME, 'label_block' => \false, 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'period', 'querydate_date_type' => 'static']]);
        $this->add_control('querydate_date_to', ['label' => esc_html__('Date to', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DATE_TIME, 'label_block' => \false, 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'period', 'querydate_date_type' => 'static']]);
        $this->add_control('querydate_date_from_dynamic', ['label' => esc_html__('Date from', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'period', 'querydate_date_type' => 'dynamicstring']]);
        $this->add_control('querydate_date_to_dynamic', ['label' => esc_html__('Date to', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'condition' => ['query_filter' => 'date', 'querydate_mode' => 'period', 'querydate_date_type' => 'dynamicstring']]);
        // +********************* Term Taxonomy
        $this->add_control('heading_query_filter_term', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('Terms and Taxonomies Filters', 'dynamic-content-for-elementor'), 'separator' => 'before', 'content_classes' => 'dce-icon-heading', 'condition' => ['query_filter' => 'term']]);
        // From Post or Meta
        $this->add_control('term_from', ['label' => esc_html__('Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'label_block' => \false, 'options' => ['post_term' => ['title' => esc_html__('Select Term', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-tag'], 'post_meta' => ['title' => esc_html__('Post Meta Term', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-square'], 'dynamicstring' => ['title' => esc_html__('Dynamic String', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-cogs']], 'default' => 'post_term', 'toggle' => \false, 'condition' => ['query_filter' => 'term']]);
        $this->add_control('heading_taxonomies', ['label' => esc_html__('Taxonomies', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['query_filter' => 'term']]);
        $this->add_control('taxonomy', ['label' => esc_html__('Select Taxonomies', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => $taxonomies, 'multiple' => \true, 'label_block' => \true, 'condition' => ['query_filter' => 'term']]);
        $this->add_control('taxonomies_operator', ['label' => esc_html__('Logical Relationship', 'dynamic-content-for-elementor'), 'description' => esc_html__('The logical relationship between each inner taxonomy when there is more than one', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['AND' => 'AND', 'OR' => 'OR'], 'toggle' => \false, 'default' => 'OR', 'condition' => ['query_filter' => 'term', 'taxonomy!' => '']]);
        // [Post Meta]
        $this->add_control('term_field_meta', ['label' => esc_html__('Post Term custom meta field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'dynamic' => ['active' => \false], 'description' => esc_html__('Selected post meta value. The meta must return an element of type array or comma separated string that contains the term type IDs. (e.g.: array [5,27,88] or 5,27,88)', 'dynamic-content-for-elementor'), 'condition' => ['term_from' => 'post_meta', 'query_filter' => 'term']]);
        // [Post Meta String]
        $this->add_control('term_field_meta_string', ['label' => esc_html__('Post Term string field', 'dynamic-content-for-elementor'), 'description' => esc_html__('Type the Term slug', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'label_block' => \true, 'render_type' => 'template', 'default' => '', 'condition' => ['term_from' => 'dynamicstring', 'query_filter' => 'term']]);
        $this->add_control('heading_terms', ['label' => esc_html__('Terms', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => ['query_filter' => 'term', 'term_from' => 'post_term', 'taxonomy!' => '']]);
        // [Post Term]
        foreach ($taxonomies as $tax_key => $a_tax) {
            if ($tax_key) {
                $this->add_control('include_term_' . $tax_key, ['label' => esc_html__('Terms Included for', 'dynamic-content-for-elementor') . ' ' . $a_tax, 'type' => 'ooo_query', 'placeholder' => esc_html__('All Terms', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'terms', 'object_type' => $tax_key, 'render_type' => 'template', 'multiple' => \true, 'condition' => ['taxonomy' => $tax_key, 'query_filter' => 'term', 'term_from' => 'post_term', 'terms_current_post' => ''], 'dynamic' => ['active' => \true]]);
            }
        }
        $this->add_control('terms_include_children', ['label' => esc_html__('Include Children Terms', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['taxonomy!' => '', 'query_filter' => 'term', 'term_from' => 'post_term']]);
        $this->add_control('include_term_operator', ['label' => esc_html__('Operator for Included Terms', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['AND' => 'AND', 'IN' => 'IN'], 'toggle' => \false, 'default' => 'IN', 'condition' => ['taxonomy!' => '', 'query_filter' => 'term', 'term_from' => 'post_term', 'terms_current_post' => '']]);
        foreach ($taxonomies as $tax_key => $a_tax) {
            if ($tax_key) {
                $this->add_control('exclude_term_' . $tax_key, ['label' => esc_html__('Terms Excluded for', 'dynamic-content-for-elementor') . ' ' . $a_tax, 'type' => 'ooo_query', 'placeholder' => esc_html__('All terms', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'terms', 'object_type' => $tax_key, 'render_type' => 'template', 'multiple' => \true, 'condition' => ['taxonomy' => $tax_key, 'query_filter' => 'term', 'term_from' => 'post_term', 'terms_current_post' => '']]);
            }
        }
        $this->add_control('terms_current_post', ['label' => esc_html__('Dynamic Current Post Terms', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Filter results by taxonomy terms associated to the current post', 'dynamic-content-for-elementor'), 'condition' => ['taxonomy!' => '', 'query_filter' => 'term', 'term_from' => 'post_term']]);
        // Author
        $this->add_control('heading_query_filter_author', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__(' Author Filters', 'dynamic-content-for-elementor'), 'separator' => 'before', 'content_classes' => 'dce-icon-heading', 'condition' => ['query_filter' => 'author']]);
        // From, Post, Meta or Current
        $this->add_control('author_from', ['label' => esc_html__('Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'label_block' => \false, 'options' => ['post_author' => ['title' => esc_html__('Select Author', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-users'], 'current_author' => ['title' => esc_html__('Current author', 'dynamic-content-for-elementor'), 'icon' => 'eicon-edit'], 'current_user' => ['title' => esc_html__('Current user', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-user']], 'default' => 'post_author', 'toggle' => \false, 'condition' => ['query_filter' => 'author']]);
        // Post Meta
        $this->add_control('author_field_meta', ['label' => esc_html__('Post author custom meta field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'default' => 'nickname', 'dynamic' => ['active' => \false], 'description' => esc_html__('Selected Post Meta value. The meta must return an element of type array or comma separated string containing author IDs. (eg: array [5,27,88] or 5,27,88)', 'dynamic-content-for-elementor'), 'condition' => ['author_from' => 'post_meta', 'query_filter' => 'author']]);
        // Post Meta String
        $this->add_control('author_field_meta_string', ['label' => esc_html__('Post Author string field', 'dynamic-content-for-elementor'), 'description' => esc_html__('Type the Post Meta value. Type a sequence of author IDs separated by commas. (eg: 5,27,88)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'label_block' => \true, 'render_type' => 'template', 'default' => '', 'condition' => ['author_from' => 'dynamicstring', 'query_filter' => 'author']]);
        // Select Authors
        $this->add_control('include_author', ['label' => esc_html__('Include Author', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Select author', 'dynamic-content-for-elementor'), 'label_block' => \true, 'multiple' => \true, 'query_type' => 'users', 'description' => esc_html__('Filter posts by selected Authors', 'dynamic-content-for-elementor'), 'condition' => ['query_filter' => 'author', 'author_from' => 'post_author']]);
        $this->add_control('exclude_author', ['label' => esc_html__('Exclude Author', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('No', 'dynamic-content-for-elementor'), 'label_block' => \true, 'multiple' => \true, 'query_type' => 'users', 'description' => esc_html__('Filter posts by selected Authors', 'dynamic-content-for-elementor'), 'separator' => 'after', 'condition' => ['query_filter' => 'author', 'author_from' => 'post_author']]);
        // Meta key
        $this->add_control('heading_query_filter_metakey', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('Metakey Filters', 'dynamic-content-for-elementor'), 'separator' => 'before', 'content_classes' => 'dce-icon-heading', 'condition' => ['query_filter' => 'metakey']]);
        // Post Meta
        $this->add_control('metakey_field_meta', ['label' => esc_html__('Meta Field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Meta key or name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'metas', 'object_type' => 'post', 'dynamic' => ['active' => \false], 'condition' => ['query_filter' => 'metakey']]);
        $this->add_control('metakey_field_meta_operator', ['label' => esc_html__('Operator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_sql_operators(), 'default' => '=', 'condition' => ['query_filter' => 'metakey']]);
        $this->add_control('metakey_field_meta_value', ['label' => esc_html__('Value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'dynamic' => ['active' => \true], 'condition' => ['query_filter' => 'metakey']]);
        // Query ID
        $this->add_control('heading_query_id', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('Query ID Filter', 'dynamic-content-for-elementor'), 'separator' => 'before', 'content_classes' => 'dce-icon-heading', 'condition' => ['query_filter' => 'query_id']]);
        /**
         * SPDX-FileCopyrightText: Elementor
         * SPDX-License-Identifier: GPL-3.0-or-later
         */
        $this->add_control('query_id', ['label' => esc_html__('Query ID', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'description' => esc_html__('Give your Query a custom unique id to allow server side filtering', 'dynamic-content-for-elementor'), 'condition' => ['query_filter' => 'query_id']]);
        $this->end_controls_section();
        // FALLBACK for NO RESULTS
        $this->start_controls_section('section_fallback', ['label' => esc_html__('No Results Behaviour', 'dynamic-content-for-elementor')]);
        $this->add_control('fallback', ['label' => esc_html__('Fallback Content', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('If you want to show something when no elements are found.', 'dynamic-content-for-elementor')]);
        $this->add_control('fallback_type', ['label' => esc_html__('Content type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['text' => ['title' => esc_html__('Text', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'template' => ['title' => esc_html__('Template', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-th-large']], 'toggle' => \false, 'default' => 'text', 'condition' => ['fallback!' => '']]);
        $this->add_control('fallback_template', ['label' => esc_html__('Render Template', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Template Name', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'posts', 'object_type' => 'elementor_library', 'condition' => ['fallback!' => '', 'fallback_type' => 'template']]);
        $this->add_control('fallback_text', ['label' => esc_html__('Text Fallback', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::WYSIWYG, 'default' => esc_html__('No results found.', 'dynamic-content-for-elementor'), 'condition' => ['fallback!' => '', 'fallback_type' => 'text']]);
        $this->end_controls_section();
    }
    public function add_acf_relationship_controls()
    {
        $this->add_control('relationship_meta', ['label' => esc_html__('ACF Relationship field', 'dynamic-content-for-elementor'), 'type' => 'ooo_query', 'placeholder' => esc_html__('Select the field...', 'dynamic-content-for-elementor'), 'label_block' => \true, 'query_type' => 'acf', 'dynamic' => ['active' => \false], 'object_type' => ['post_object', 'relationship'], 'condition' => ['query_type' => 'relationship']]);
        $this->add_control('acf_relationship_from', ['label' => esc_html__('Retrieve the field from', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'current_post', 'options' => ['current_post' => esc_html__('Current Post', 'dynamic-content-for-elementor'), 'current_user' => esc_html__('Current User', 'dynamic-content-for-elementor'), 'current_author' => esc_html__('Current Author', 'dynamic-content-for-elementor'), 'current_term' => esc_html__('Current Term', 'dynamic-content-for-elementor'), 'options_page' => esc_html__('Options Page', 'dynamic-content-for-elementor')], 'condition' => ['query_type' => 'relationship']]);
        $this->add_control('relationship_invert', ['label' => esc_html__('Invert direction (deprecated)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Retrieve all posts that are associated with the current post. (Deprecated: Using ACF Bidirectional option is recommended as a replacement) ', 'dynamic-content-for-elementor'), 'condition' => ['query_type' => 'relationship', 'acf_relationship_from' => 'current_post']]);
    }
    /**
     * Register Direction Controls
     *
     * @return void
     */
    protected function register_style_direction_controls()
    {
        $this->start_controls_section('section_direction_style', ['label' => esc_html__('Direction', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['_skin' => ['', 'accordion', 'grid', 'carousel', 'grid-filters', 'gridtofullscreen3d', 'list', 'dualcarousel', 'table', 'timeline']]]);
        $this->add_control('rtl', ['label' => esc_html__('RTL', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => is_rtl() ? 'yes' : '', 'frontend_available' => \true]);
        $this->end_controls_section();
    }
    public function safe_render()
    {
    }
    /**
     * Helper method similar to wp_date bu simplified timezone.
     *
     * @param string $format
     * @param int $timestamp
     * @param bool $use_utc
     * @return string|false
     */
    private function date($format = null, $timestamp = null, $use_utc = \true)
    {
        if (empty($format)) {
            $format = 'Y-m-d H:i:s';
        }
        $tz = null;
        if ($use_utc) {
            $tz = new \DateTimeZone('UTC');
        }
        return wp_date($format, $timestamp, $tz);
    }
    /**
     * Set Empty Query
     *
     * @return void
     */
    protected function set_empty_query()
    {
        $this->query = '';
    }
    public function query_posts()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        /**
         * @var array<string>|null $post_type
         */
        $post_type = Helper::validate_post_types($settings['post_type']);
        $post_status = $settings['post_status'];
        $id_page = Helper::get_the_id();
        if (\false !== get_post_type()) {
            // phpstan
            $post_type_of_the_current_post = Helper::validate_post_types(get_post_type());
        } else {
            $post_type_of_the_current_post = '';
        }
        $args = [];
        $wishlist = [];
        $taxquery = [];
        $exclude_current_post = [];
        $sticky_posts_to_remove = [];
        $posts_excluded = [];
        $use_parent_page = [];
        $terms_query = 'all';
        $terms_query_excluded = [];
        $query_id = '';
        if (is_singular()) {
            if ($settings['exclude_io']) {
                $exclude_current_post = [$id_page];
            }
        } elseif (is_home() || is_archive()) {
            $exclude_current_post = [];
        }
        if ($settings['exclude_posts'] && \is_array($settings['exclude_posts'])) {
            $posts_excluded = $settings['exclude_posts'];
        }
        if ($settings['exclude_page_parent']) {
            $use_parent_page = [0];
        } else {
            $use_parent_page = [];
        }
        if ($settings['exclude_page_child']) {
            $use_child_page = 0;
        } else {
            $use_child_page = [];
        }
        // Ignore Sticky Posts
        if ($settings['ignore_sticky_posts']) {
            $args['ignore_sticky_posts'] = '1';
        }
        // Remove Sticky Posts
        if ($settings['remove_sticky_posts']) {
            $sticky_posts_to_remove = get_option('sticky_posts');
        }
        // Query Type - Search Page
        if ($settings['query_type'] == 'search_page') {
            if (empty($_GET['s'])) {
                $this->set_empty_query();
                return;
            }
            if (empty($post_type)) {
                $post_type[] = 'any';
            }
            $args = \array_merge($args, ['s' => sanitize_text_field($_GET['s']), 'post_type' => $post_type, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'posts_per_page' => $settings['num_posts'] ?? get_option('posts_per_page'), 'post__not_in' => $posts_excluded, 'post_parent__not_in' => $use_parent_page]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ($settings['query_type'] == 'dynamic_archives') {
            global $wp_query;
            $args = $wp_query->query_vars;
        } elseif ($settings['query_type'] == 'dynamic_mode') {
            // Query Type - Dynamic
            $array_taxquery = [];
            $taxonomy_list = [];
            if (is_archive()) {
                $queried_object = get_queried_object();
                if (is_tax() || is_category() || is_tag()) {
                    $taxonomy_list[0] = $queried_object->taxonomy;
                }
            } elseif (is_single()) {
                $taxonomy_list = get_post_taxonomies($id_page);
            }
            if (!empty($taxonomy_list)) {
                // Convert taxonomy setting to array
                if (!empty($settings['taxonomy']) && \is_string($settings['taxonomy'])) {
                    $settings['taxonomy'] = array($settings['taxonomy']);
                }
                foreach ($taxonomy_list as $tax) {
                    $terms_list = [];
                    $lista_dei_termini = [];
                    if (is_single()) {
                        if (\in_array($tax, $settings['taxonomy'] ?? [], \true)) {
                            $terms_list = wp_get_post_terms($id_page, $tax, ['orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'hide_empty' => \true]);
                        }
                        foreach ($terms_list as $term) {
                            $lista_dei_termini[] = $term->term_id;
                        }
                    } elseif (is_archive()) {
                        $lista_dei_termini[0] = $queried_object->term_id;
                    }
                    if (\count($lista_dei_termini) > 0) {
                        $array_taxquery = [];
                        foreach ($lista_dei_termini as $termine) {
                            $array_taxquery[] = ['taxonomy' => $tax, 'field' => 'id', 'terms' => $termine];
                        }
                    }
                    /* EXCLUDED */
                    if (isset($settings['terms_' . $tax . '_excluse'])) {
                        $terms_query_excluded = $settings['terms_' . $tax . '_excluse'];
                    }
                    if (!empty($terms_query_excluded)) {
                        $array_taxquery_excluded = [];
                        if (\count($terms_query_excluded) > 1) {
                            $array_taxquery_excluded['relation'] = $settings['combination_taxonomy_excluse'];
                        }
                        foreach ($terms_query_excluded as $term_query) {
                            $array_taxquery_excluded[] = ['taxonomy' => $tax, 'field' => 'term_id', 'terms' => $term_query, 'operator' => 'NOT IN'];
                        }
                        if (empty($array_taxquery)) {
                            $array_taxquery = $array_taxquery_excluded;
                        } else {
                            $array_taxquery = ['relation' => 'AND', $array_taxquery, $array_taxquery_excluded];
                        }
                    }
                }
            }
            // Se la taxQuery dinamica non da risultati uso quella statica.
            if (!$array_taxquery) {
                $array_taxquery = $taxquery;
            }
            if ('elementor_library' == $post_type_of_the_current_post) {
                $post_type_of_the_current_post = 'post';
            }
            if (!is_search()) {
                $args = \array_merge($args, ['post_type' => $post_type_of_the_current_post, 'posts_per_page' => $settings['num_posts'], 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'post__not_in' => \array_merge($posts_excluded, $exclude_current_post, $sticky_posts_to_remove), 'post_parent__not_in' => $use_parent_page, 'tax_query' => $array_taxquery, 'post_status' => $post_status, 'post_parent' => $use_child_page]);
                if ('meta_value' === $settings['orderby']) {
                    $args['meta_type'] = $settings['meta_type'];
                }
            }
            if (is_date()) {
                global $wp_query;
                $args['year'] = $wp_query->query_vars['year'];
                $args['monthnum'] = $wp_query->query_vars['monthnum'];
                $args['day'] = $wp_query->query_vars['day'];
            }
            if (!empty($settings['post_offset'])) {
                $args['offset'] = $settings['post_offset'];
            }
        } elseif ('get_cpt' === $settings['query_type']) {
            // Query Type - From Post Type
            if (null === $post_type) {
                // phpstan
                $this->set_empty_query();
                return;
            }
            $post_type = $this->include_product_variations($post_type, $settings);
            $args = \array_merge($args, ['post_type' => $post_type, 'posts_per_page' => $settings['num_posts'], 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'post_status' => $post_status, 'post_parent' => $use_child_page]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
            if ($settings['metakey']) {
                $args['meta_key'] = $settings['metakey'];
            }
            $post__not_in = \array_merge($posts_excluded, $exclude_current_post, $sticky_posts_to_remove);
            if (!empty($post__not_in)) {
                $args['post__not_in'] = $post__not_in;
            }
            if (!empty($use_parent_page)) {
                $args['post_parent__not_in'] = $use_parent_page;
            }
            if (!empty($settings['post_offset'])) {
                $args['offset'] = $settings['post_offset'];
            }
        } elseif ('post_parent' === $settings['query_type']) {
            // Query Type - From Post Parent
            if (!empty($settings['specific_page_parent'])) {
                $post_type_specific_page_parent = get_post_type($settings['specific_page_parent']);
                if (\false === $post_type_specific_page_parent) {
                    // phpstan
                    $this->set_empty_query();
                    return;
                }
                $args = \array_merge($args, ['post_type' => \DynamicContentForElementor\Helper::validate_post_types($post_type_specific_page_parent), 'post_parent' => $settings['specific_page_parent']]);
            }
            if ($settings['parent_source']) {
                $args = \array_merge($args, ['post_type' => $post_type_of_the_current_post, 'post_parent' => wp_get_post_parent_id($id_page)]);
            }
            if ($settings['child_source']) {
                $args = \array_merge($args, ['post_type' => $post_type_of_the_current_post, 'post_parent' => $id_page]);
            }
            $args = \array_merge($args, ['post__not_in' => \array_merge($posts_excluded, $exclude_current_post), 'posts_per_page' => $settings['num_posts'], 'order' => $settings['order'], 'orderby' => $settings['orderby']]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ('relationship' === $settings['query_type']) {
            // Query Type - ACF Relationship
            if (empty($settings['relationship_meta'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('Select an ACF Relationship Field', 'dynamic-content-for-elementor'));
                }
                $this->set_empty_query();
                return;
            }
            if (!Helper::is_acf_active()) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('ACF is not active', 'dynamic-content-for-elementor'));
                }
                $this->set_empty_query();
                return;
            }
            $relations_ids = null;
            if ($settings['relationship_invert']) {
                $relations_ids = Helper::get_acf_field_value_relationship_invert($settings['relationship_meta'], $id_page);
            } else {
                $acf_source = Helper::get_acf_source_id($settings['acf_relationship_from']);
                if ($acf_source) {
                    $relations_ids = \get_field($settings['relationship_meta'], $acf_source, \false);
                }
            }
            // check if is a subfield
            if (!$relations_ids) {
                $relations_ids = get_sub_field($settings['relationship_meta'], \false);
            }
            // Don't execute WP_Query if the ACF Relationship field is empty
            if (empty($relations_ids)) {
                $this->set_empty_query();
                return;
            }
            if (!\is_array($relations_ids)) {
                $relations_ids = [$relations_ids];
            }
            if (Helper::is_wpml_active()) {
                // WPML Translation
                $relations_ids = Helper::wpml_translate_object_id($relations_ids);
            }
            if ($settings['metakey']) {
                $args['meta_key'] = $settings['metakey'];
            }
            // Remove Sticky Posts but not posts on the ACF Relationship field
            $sticky_posts = get_option('sticky_posts');
            if (!empty($sticky_posts)) {
                $args['post__not_in'] = \array_diff($sticky_posts, $relations_ids);
            }
            if (!empty($settings['post_offset'])) {
                $args['offset'] = $settings['post_offset'];
            }
            $args = \array_merge($args, ['post_type' => 'any', 'posts_per_page' => $settings['num_posts'], 'post_status' => 'publish', 'post__in' => $relations_ids, 'orderby' => $settings['orderby'], 'order' => $settings['order']]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ('metabox_relationship' === $settings['query_type']) {
            // Query Type - Meta Box Relationship
            if (empty($settings['metabox_relationship_id'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('Type the Meta Box Relationship ID', 'dynamic-content-for-elementor'));
                }
                $this->set_empty_query();
                return;
            }
            $metabox_relationship['id'] = $settings['metabox_relationship_id'];
            // Check if the Meta Box Relationship ID exists
            if (!\array_key_exists($metabox_relationship['id'], \MB_Relationships_API::get_all_relationships())) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__("The Meta Box Relationship ID doesn't exist", 'dynamic-content-for-elementor'));
                }
                $this->set_empty_query();
                return;
            }
            if ('from' === $settings['metabox_relationship_relation']) {
                $metabox_relationship['from'] = get_the_ID();
            } else {
                $metabox_relationship['to'] = get_the_ID();
            }
            $args = \array_merge($args, ['relationship' => $metabox_relationship, 'post_status' => 'publish', 'orderby' => $settings['orderby'], 'order' => $settings['order']]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ('pods_relationship' === $settings['query_type'] && Helper::is_pods_active()) {
            // Query Type - PODS Relationship
            if (pods(get_post_type(), get_the_ID())) {
                $related_posts = pods_field_raw($settings['pods_relationship_field']);
            }
            $related_posts_id = \false;
            if (\is_numeric($related_posts)) {
                $related_posts_id = array($related_posts);
            } elseif (isset($related_posts['ID'])) {
                $related_posts_id = array($related_posts['ID']);
            } elseif (\is_array($related_posts)) {
                $related_posts_id = wp_list_pluck($related_posts, 'ID');
            }
            // Don't execute WP_Query if the Pods Relationship field is empty
            if (empty($related_posts_id)) {
                $this->set_empty_query();
                return;
            }
            if ($settings['metakey']) {
                $args['meta_key'] = $settings['metakey'];
            }
            if (Helper::is_wpml_active()) {
                // WPML Translation
                $related_posts_id = Helper::wpml_translate_object_id($related_posts_id);
            }
            $args = \array_merge($args, ['post_type' => 'any', 'posts_per_page' => $settings['num_posts'], 'post_status' => 'publish', 'post__in' => $related_posts_id, 'orderby' => $settings['orderby'], 'order' => $settings['order']]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ('specific_posts' === $settings['query_type']) {
            // Query Type - From Specific Posts
            $post__in = [];
            $specific_posts = $settings['specific_posts'];
            // Remove Sticky Posts
            if (!empty($sticky_posts_to_remove)) {
                $args['post__not_in'] = $sticky_posts_to_remove;
            }
            if (\is_array($specific_posts) && !empty($specific_posts)) {
                foreach ($specific_posts as $post) {
                    if (!empty($post['repeater_specific_posts'])) {
                        $post__in[] = $post['repeater_specific_posts'];
                    }
                }
            } else {
                $this->set_empty_query();
                return;
            }
            if (Helper::is_wpml_active()) {
                // WPML Translation
                $post__in = Helper::wpml_translate_object_id($post__in);
            }
            $args = \array_merge($args, ['post_type' => 'any', 'post__in' => $post__in, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'post_status' => 'publish', 'posts_per_page' => -1]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
            if ('post__in' === $args['orderby'] && 'yes' === $settings['reverse_order_posts']) {
                if (\is_array($args['post__in'])) {
                    //@phpstan-ignore-line
                    $args['post__in'] = \array_reverse($args['post__in']);
                }
            }
        } elseif ('id_list' === $settings['query_type']) {
            // Query Type - ID List
            $args = \array_merge($args, ['post_type' => 'any', 'post__in' => \explode(',', sanitize_text_field($settings['id_list'])), 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'post_status' => 'publish', 'posts_per_page' => $settings['num_posts']]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
            // Remove Sticky Posts
            if (!empty($sticky_posts_to_remove)) {
                $args['post__not_in'] = $sticky_posts_to_remove;
            }
        } elseif ('woo_products_on_sale' === $settings['query_type']) {
            // Query Type - Dynamic Woo Products On Sale
            $products_on_sale = \wc_get_product_ids_on_sale();
            if (empty($products_on_sale)) {
                $this->set_empty_query();
                return;
            }
            if (Helper::is_wpml_active()) {
                // WPML Translation
                $products_on_sale = Helper::wpml_translate_object_id($products_on_sale);
            }
            $args = \array_merge($args, ['post_type' => 'product', 'post__in' => $products_on_sale, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'post_status' => $post_status, 'posts_per_page' => $settings['num_posts']]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ('favorites' === $settings['query_type']) {
            // Query Type - Favorites
            if (empty($settings['favorites_key'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('Select the Favorites key', 'dynamic-content-for-elementor'));
                }
                return;
            }
            $user_id = null;
            if ($settings['favorites_scope'] === 'user') {
                switch ($settings['favorites_user_source']) {
                    case 'current':
                        $user_id = (int) get_current_user_id();
                        break;
                    case 'author':
                        $user_id = (int) get_the_author_meta('ID');
                        break;
                    case 'other':
                        $user_id = !empty($settings['favorites_user_id']) ? (int) $settings['favorites_user_id'] : null;
                        break;
                }
            }
            $favorites_post_in = Favorites::get($settings['favorites_key'], $settings['favorites_scope'], $user_id);
            if (empty($favorites_post_in)) {
                $this->set_empty_query();
                return;
            }
            // Base arguments for both favorites and wishlist
            $base_args = ['order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'post_status' => $post_status, 'posts_per_page' => $settings['num_posts']];
            if ('meta_value' === $settings['orderby']) {
                $base_args['meta_type'] = $settings['meta_type'];
            }
            if ('dce_wishlist' === $settings['favorites_key']) {
                if (!is_user_logged_in()) {
                    $this->set_empty_query();
                    return;
                }
                $wishlist = [];
                foreach ($favorites_post_in as $product) {
                    if ('product' === get_post_type((int) $product) && !wc_customer_bought_product('', get_current_user_id(), get_the_ID($product))) {
                        $wishlist[] = $product;
                    }
                }
                $args = \array_merge($args, $base_args, ['post_type' => 'product', 'post__in' => $wishlist]);
            } else {
                $args = \array_merge($args, $base_args, ['post_type' => 'any', 'post__in' => $favorites_post_in]);
                if ('post__in' === $args['orderby'] && 'yes' === $settings['reverse_order_posts'] && \is_array($args['post__in'])) {
                    //@phpstan-ignore-line
                    $args['post__in'] = \array_reverse($args['post__in']);
                }
            }
        } elseif ('custom_query' === $settings['query_type']) {
            // Query Type - Custom Query
            $custom_query = $this->check_custom_query($settings);
            if ($custom_query) {
                $args = $custom_query;
            }
        } elseif ('products_cart' === $settings['query_type']) {
            // Query Type - Products in the Cart
            if (Helper::is_woocommerce_active()) {
                global $woocommerce;
                $items = $woocommerce->cart->get_cart();
                if (empty($items)) {
                    $this->set_empty_query();
                    return;
                }
                $products = [];
                foreach ($items as $item => $values) {
                    $products[] = $values['data']->get_id();
                }
                if (Helper::is_wpml_active()) {
                    // WPML Translation
                    $products = Helper::wpml_translate_object_id($products);
                }
                $post_type = ['product'];
                $args = \array_merge($args, ['post_type' => $this->include_product_variations($post_type, $settings), 'post__in' => $products, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey']]);
                if ('meta_value' === $settings['orderby']) {
                    $args['meta_type'] = $settings['meta_type'];
                }
            }
        } elseif ('product_upsells' === $settings['query_type']) {
            // Query Type - Up-Sells Products
            if (Helper::is_woocommerce_active()) {
                $upsell_ids = get_post_meta(get_the_ID(), '_upsell_ids', \true);
                if (Helper::is_wpml_active()) {
                    // WPML Translation
                    $upsell_ids = Helper::wpml_translate_object_id($upsell_ids);
                }
                if ($upsell_ids) {
                    $post_type = ['product'];
                    $args = \array_merge($args, ['post_type' => $this->include_product_variations($post_type, $settings), 'post__in' => $upsell_ids, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey']]);
                    if ('meta_value' === $settings['orderby']) {
                        $args['meta_type'] = $settings['meta_type'];
                    }
                } else {
                    $this->set_empty_query();
                    return;
                }
            }
        } elseif ('product_variations' === $settings['query_type']) {
            // Query Type - Variations Products
            if (!Helper::is_woocommerce_active()) {
                $this->set_empty_query();
                return;
            }
            if (\false === get_the_ID() || 'product' !== get_post_type(get_the_ID())) {
                // phpstan
                $this->set_empty_query();
                return;
            }
            $product_obj = \wc_get_product(get_the_ID());
            if (!$product_obj || !$product_obj->is_type('variable')) {
                $this->set_empty_query();
                return;
            }
            $children = $product_obj->get_children();
            if (empty($children)) {
                $this->set_empty_query();
                return;
            }
            if (Helper::is_wpml_active()) {
                // WPML Translation
                $children = Helper::wpml_translate_object_id($children);
            }
            $args = \array_merge($args, ['post_type' => 'product_variation', 'posts_per_page' => $settings['num_posts'], 'post__in' => $children, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'post_status' => 'publish']);
        } elseif ('product_crosssells' === $settings['query_type']) {
            // Query Type - Cross-Sells Products
            if (Helper::is_woocommerce_active()) {
                $crosssell_ids = get_post_meta(get_the_ID(), '_crosssell_ids', \true);
                if (Helper::is_wpml_active()) {
                    // WPML Translation
                    $crosssell_ids = Helper::wpml_translate_object_id($crosssell_ids);
                }
                if ($crosssell_ids) {
                    $post_type = ['product'];
                    $args = \array_merge($args, ['post_type' => $this->include_product_variations($post_type, $settings), 'post__in' => $crosssell_ids, 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey']]);
                    if ('meta_value' === $settings['orderby']) {
                        $args['meta_type'] = $settings['meta_type'];
                    }
                } else {
                    $this->set_empty_query();
                    return;
                }
            }
        } elseif ('sticky_posts' === $settings['query_type']) {
            // Query Type - Sticky Posts
            if (empty(get_option('sticky_posts'))) {
                // No Sticky Posts
                $this->set_empty_query();
                return;
            }
            $args = \array_merge($args, ['ignore_sticky_posts' => '1', 'post__in' => get_option('sticky_posts'), 'post_type' => $post_type, 'posts_per_page' => $settings['num_posts'], 'order' => $settings['order'], 'orderby' => $settings['orderby'], 'meta_key' => $settings['metakey'], 'post__not_in' => \array_merge($posts_excluded, $exclude_current_post), 'post_parent__not_in' => $use_parent_page, 'post_status' => $post_status, 'offset' => $settings['post_offset'] ?? 0, 'post_parent' => $use_child_page]);
            if ('meta_value' === $settings['orderby']) {
                $args['meta_type'] = $settings['meta_type'];
            }
        } elseif ('search_filter' === $settings['query_type']) {
            // Query Type - Search and Filter
            if (!Helper::is_searchandfilterpro_active()) {
                $this->set_empty_query();
                return;
            }
            $sfid = null;
            if (Helper::is_search_filter_pro_version(2)) {
                $sfid = \intval($settings['search_filter_id']);
                $args = ['search_filter_id' => $sfid];
            } elseif (Helper::is_search_filter_pro_version(3.1)) {
                $sfid = \intval($settings['search_filter_v3_id']);
                $args = ['search_filter_query_id' => $sfid, 'integration' => 'dynamicooo/dynamic-content-for-elementor'];
            }
            if (empty($sfid)) {
                $this->set_empty_query();
                return;
            }
        }
        // Pagination
        if ('search_filter' !== $settings['query_type'] || Helper::is_search_filter_pro_version(3.1)) {
            global $paged;
            $page = $this->get_current_page();
            $args['paged'] = $page;
        } elseif (isset($_GET['sf_paged'])) {
            $args['paged'] = \intval($_GET['sf_paged']);
        } else {
            $args['paged'] = 1;
        }
        // Query Filter
        if (\is_array($settings['query_filter']) && !empty($settings['query_filter'])) {
            // Date query filter
            if (\in_array('date', $settings['query_filter'], \true)) {
                $datetime_stored_as_utc = \true;
                $querydate_field_meta_format = 'Ymd';
                $date_field = $settings['querydate_field'];
                if ($settings['querydate_field'] === 'post_meta') {
                    if ($settings['querydate_use_utc'] !== 'yes') {
                        $datetime_stored_as_utc = \false;
                    }
                }
                if ($settings['querydate_mode'] != 'future' && $settings['querydate_field'] == 'post_meta') {
                    $date_field_meta = sanitize_key($settings['querydate_field_meta']);
                    $querydate_field_meta_format = sanitize_text_field($settings['querydate_field_meta_format']);
                }
                if ($settings['querydate_mode'] == 'future') {
                    if ($settings['querydate_use_utc'] !== 'yes') {
                        $datetime_stored_as_utc = \false;
                    }
                    $date_field_meta = sanitize_key($settings['querydate_field_meta_future']);
                    $querydate_field_meta_format = sanitize_text_field($settings['querydate_field_meta_future_format']);
                    if ($settings['querydate_field_meta_future_contains_today']) {
                        $future_compare = '>=';
                    } else {
                        $future_compare = '>';
                    }
                    $args['meta_query'] = [['key' => $date_field_meta, 'value' => $this->date($querydate_field_meta_format, null, $datetime_stored_as_utc), 'meta_type' => 'DATETIME', 'compare' => $future_compare]];
                }
                if ($date_field) {
                    $date_after = \false;
                    $date_before = \false;
                    switch ($settings['querydate_mode']) {
                        case 'past':
                            $date_before = $date_field !== 'post_meta' ? \time() : $this->date(null, null, $datetime_stored_as_utc);
                            break;
                        case 'today':
                            $date_after = $this->date(null, \strtotime('today 00:00'), $datetime_stored_as_utc);
                            $date_before = $this->date(null, \strtotime('today 23:59'), $datetime_stored_as_utc);
                            break;
                        case 'yesterday':
                            $date_after = $this->date(null, \strtotime('yesterday 00:00'), $datetime_stored_as_utc);
                            $date_before = $this->date(null, \strtotime('yesterday 23:59'), $datetime_stored_as_utc);
                            break;
                        case 'days':
                        case 'weeks':
                        case 'months':
                        case 'years':
                            $date_after = '-' . $settings['querydate_range'] . ' ' . $settings['querydate_mode'];
                            $date_before = 'now';
                            break;
                        case 'period':
                            $date_after = $settings['querydate_date_type'] === 'static' ? $settings['querydate_date_from'] : $settings['querydate_date_from_dynamic'];
                            $date_before = $settings['querydate_date_type'] === 'static' ? $settings['querydate_date_to'] : $settings['querydate_date_to_dynamic'];
                            break;
                    }
                    // compare by post publish date
                    if ($settings['querydate_field'] == 'post_date') {
                        $args['date_query'] = [['after' => $date_after, 'before' => $date_before, 'inclusive' => \true]];
                    } elseif ($settings['querydate_field'] == 'post_modified') {
                        // compare by post modified date
                        $args['date_query'] = [['column' => 'post_modified', 'after' => $date_after, 'before' => $date_before, 'inclusive' => \true]];
                    } elseif ($settings['querydate_field'] == 'post_meta') {
                        // compare by post meta
                        if ($date_after) {
                            $date_after = $this->date($querydate_field_meta_format, \strtotime($date_after), $datetime_stored_as_utc);
                        }
                        if ($date_before) {
                            $date_before = $this->date($querydate_field_meta_format, \strtotime($date_before), $datetime_stored_as_utc);
                        }
                        if ($date_before && $date_after) {
                            $args['meta_query'] = [['key' => $date_field_meta, 'value' => [$date_after, $date_before], 'meta_type' => 'DATETIME', 'compare' => 'BETWEEN']];
                        } elseif ($date_after) {
                            $args['meta_query'] = [['key' => $date_field_meta, 'value' => $date_after, 'meta_type' => 'DATETIME', 'compare' => '>=']];
                        } else {
                            $args['meta_query'] = [['key' => $date_field_meta, 'value' => $date_before, 'meta_type' => 'DATETIME', 'compare' => '<=']];
                        }
                    }
                }
            }
            // Term query filter
            if (\in_array('term', $settings['query_filter'], \true)) {
                if ('post_term' === $settings['term_from']) {
                    // Convert taxonomy setting to array
                    if (!empty($settings['taxonomy']) && \is_string($settings['taxonomy'])) {
                        $settings['taxonomy'] = array($settings['taxonomy']);
                    }
                    // Select Term
                    if (empty($settings['terms_current_post']) && !empty($settings['taxonomy'])) {
                        foreach ($settings['taxonomy'] as $taxonomy) {
                            if (!empty($settings['include_term_' . $taxonomy]) || !empty($settings['exclude_term_' . $taxonomy])) {
                                if (!empty($settings['include_term_' . $taxonomy])) {
                                    // Include Terms
                                    $args['tax_query'][] = ['include_children' => $settings['terms_include_children'], 'operator' => $settings['include_term_operator'], 'taxonomy' => $taxonomy, 'terms' => $settings['include_term_' . $taxonomy]];
                                }
                                if (!empty($settings['exclude_term_' . $taxonomy])) {
                                    // Exclude Terms
                                    $args['tax_query'][] = ['operator' => 'NOT IN', 'taxonomy' => $taxonomy, 'terms' => $settings['exclude_term_' . $taxonomy]];
                                }
                                if (!empty($settings['include_term_' . $taxonomy]) && !empty($settings['exclude_term_' . $taxonomy])) {
                                    // Relation when used include and exclude together
                                    $args['tax_query']['relation'] = 'AND';
                                }
                            }
                        }
                        if (\count($settings['taxonomy']) > 1 && !empty($settings['taxonomies_operator'])) {
                            // The logical relationship between each inner taxonomy array when there is more than one. Possible values are 'AND', 'OR'. Do not use with a single inner taxonomy array
                            $args['tax_query']['relation'] = $settings['taxonomies_operator'];
                        }
                    }
                    // Dynamic Current Post Terms
                    if ($settings['terms_current_post']) {
                        foreach ($settings['taxonomy'] as $taxonomy) {
                            $terms_query = $this->get_terms_query($taxonomy, $settings, $id_page);
                            if (!empty($terms_query) && \is_array($terms_query)) {
                                $args['tax_query'][] = ['taxonomy' => $taxonomy, 'terms' => $terms_query];
                            }
                            if (!empty($terms_query) && \is_array($terms_query) && \count($terms_query) > 1) {
                                $args['tax_query']['relation'] = $settings['taxonomies_operator'];
                            }
                        }
                    }
                } elseif ('post_meta' === $settings['term_from']) {
                    // Post Meta Term
                    $post_id_for_meta = get_the_ID();
                    if ($post_id_for_meta !== \false && $settings['term_field_meta']) {
                        foreach ($settings['taxonomy'] as $taxonomy) {
                            $args['tax_query'][] = ['operator' => 'IN', 'taxonomy' => $taxonomy, 'terms' => get_post_meta($post_id_for_meta, $settings['term_field_meta'])];
                        }
                    }
                } elseif ('dynamicstring' === $settings['term_from']) {
                    // Dynamic String
                    if (empty($settings['taxonomy'])) {
                        $this->set_empty_query();
                        return;
                    }
                    if ($settings['term_field_meta_string']) {
                        $args['tax_query'] = array('relation' => $settings['taxonomies_operator']);
                        foreach ($settings['taxonomy'] as $taxonomy) {
                            $args['tax_query'][] = array('operator' => 'IN', 'taxonomy' => $taxonomy, 'field' => 'slug', 'terms' => sanitize_text_field($settings['term_field_meta_string']));
                        }
                    }
                }
            }
            // Author query filter
            if (\in_array('author', $settings['query_filter'], \true)) {
                $author_id = get_the_author_meta('ID');
                if (!is_singular()) {
                    $queried_object = get_queried_object();
                    if ($queried_object) {
                        if (\get_class($queried_object) == 'WP_User') {
                            $author_id = get_queried_object_id();
                            $args['author__in'] = $author_id;
                        }
                    }
                }
                if ($settings['author_from'] == 'post_author') {
                    if ($settings['include_author']) {
                        $args['author__in'] = $settings['include_author'];
                    }
                    if ($settings['exclude_author']) {
                        $args['author__not_in'] = $settings['exclude_author'];
                    }
                } elseif ($settings['author_from'] == 'current_author') {
                    $args['author__in'] = $author_id;
                } elseif ($settings['author_from'] == 'current_user') {
                    $args['author__in'] = get_current_user_id();
                }
            }
            // Meta Key query filter
            if (\in_array('metakey', $settings['query_filter'], \true)) {
                if ($settings['metakey_field_meta']) {
                    $args['meta_query'][] = ['key' => $settings['metakey_field_meta'], 'value' => sanitize_text_field($settings['metakey_field_meta_value']), 'compare' => $settings['metakey_field_meta_operator']];
                }
            }
            // Query ID filter
            if (\in_array('query_id', $settings['query_filter'], \true)) {
                $query_id = $this->get_settings_for_display('query_id');
            }
        }
        if (!empty($query_id)) {
            add_action('pre_get_posts', [$this, 'pre_get_posts_query_filter']);
        }
        $relevanssi_active = \function_exists('relevanssi_query');
        if ($relevanssi_active) {
            // Temporarily remove Relevanssi filters to prevent interference during WP Query searches
            remove_filter('posts_request', 'relevanssi_prevent_default_request');
            //phpstan-ignore
            remove_filter('posts_pre_query', 'relevanssi_query', 99);
            //phpstan-ignore
        }
        $query_p = new \WP_Query($args);
        if ($relevanssi_active) {
            // Re-add Relevanssi filters to restore normal behavior for subsequent queries
            add_filter('posts_request', 'relevanssi_prevent_default_request', 10, 2);
            //phpstan-ignore
            add_filter('posts_pre_query', 'relevanssi_query', 99, 2);
            //phpstan-ignore
        }
        if (!empty($query_id)) {
            remove_action('pre_get_posts', [$this, 'pre_get_posts_query_filter']);
        }
        // Force Sticky Posts at the Top
        if ('yes' !== $settings['ignore_sticky_posts'] && 'yes' === $settings['force_sticky_posts_at_the_top']) {
            $query_p = $this->force_sticky_posts_at_the_top($query_p);
        }
        do_action('dynamicooo/posts/query_results', $query_p);
        $this->query = $query_p;
        $this->query_args = $args;
    }
    /**
     * SPDX-FileCopyrightText: Elementor
     * SPDX-License-Identifier: GPL-3.0-or-later
     *
     * @param \WP_Query $wp_query
     * @return void
     */
    public function pre_get_posts_query_filter($wp_query)
    {
        $query_id = $this->get_settings_for_display('query_id');
        do_action("dynamicooo/query/{$query_id}", $wp_query, $this);
    }
    /**
     * Force Sticky Posts at the Top
     *
     * @param \WP_Query $query_p
     * @return \WP_Query $query_p
     */
    public function force_sticky_posts_at_the_top(\WP_Query $query_p)
    {
        $sticky_posts = [];
        foreach ($query_p->posts as $key => $post) {
            if ($post instanceof \WP_Post && is_sticky($post->ID) || \is_int($post) && is_sticky($post)) {
                $sticky_posts[] = $post;
                unset($query_p->posts[$key]);
            }
        }
        $query_p->posts = \array_values(\array_filter($query_p->posts));
        \array_unshift($query_p->posts, ...$sticky_posts);
        return $query_p;
    }
    /**
     * Get Terms for the current post, useful for Dynamic Current Post Terms
     *
     * @param string $taxonomy
     * @param array<mixed> $settings
     * @param integer $id_page
     * @return array<mixed>|string
     */
    public function get_terms_query(string $taxonomy, array $settings, int $id_page)
    {
        if (empty($settings)) {
            $settings = $this->get_settings_for_display();
        }
        if (empty($id_page)) {
            $id_page = get_the_ID();
        }
        if (empty($taxonomy)) {
            return [];
        }
        $terms_query = [];
        // Da implementare oR & AND tems ...
        if (is_singular()) {
            if (\false === $id_page) {
                throw new \Exception('ID not valid', 1);
            }
            $terms_list = wp_get_post_terms($id_page, $taxonomy, ['orderby' => 'name', 'order' => 'ASC', 'fields' => 'all', 'hide_empty' => \true]);
            if (!empty($terms_list)) {
                foreach ($terms_list as $akey => $aterm) {
                    if (!\in_array($aterm->term_id, $terms_query, \true)) {
                        $terms_query[] = $aterm->term_id;
                    }
                }
            }
        } elseif (is_archive() && (is_tax() || is_category() || is_tag())) {
            $queried_object = get_queried_object();
            $terms_query = [$queried_object->term_id];
        }
        return !empty($terms_query) ? $terms_query : 'all';
    }
    protected function check_custom_query($settings)
    {
        //+exclude_start
        if (!\DynamicContentForElementor\Helper::can_register_unsafe_controls()) {
            return \false;
        }
        $custom_query = $settings['custom_query_code'];
        if (\is_string($custom_query)) {
            try {
                return @eval($custom_query);
            } catch (\ParseError $e) {
                $evalError = \true;
            } catch (\Throwable $e) {
                $evalError = \true;
            }
            if ($evalError && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<strong>';
                echo esc_html__('Please check your Dynamic Posts - Custom Query Code', 'dynamic-content-for-elementor');
                echo '</strong><br />';
                echo esc_html__('ERROR', 'dynamic-content-for-elementor') . ': ' . $e->getMessage(), "\n";
            }
        }
        return \false;
        //+exclude_end
    }
    public function get_query()
    {
        return $this->query;
    }
    public function get_query_args()
    {
        return $this->query_args;
    }
    public function get_current_page()
    {
        if ('' === $this->get_settings('pagination_enable') && '' === $this->get_settings('infiniteScroll_enable')) {
            return 1;
        }
        return \max(1, get_query_var('paged'), get_query_var('page'));
    }
    /**
     * Include Woo Product Variations
     *
     * @param array<string> $post_types
     * @param array<mixed> $settings
     * @return array<string>
     */
    public static function include_product_variations(array $post_types, $settings)
    {
        if (\in_array('product', $post_types) && 'yes' === $settings['include_variations_product']) {
            return \array_merge($post_types, ['product_variation']);
        }
        return $post_types;
    }
}
