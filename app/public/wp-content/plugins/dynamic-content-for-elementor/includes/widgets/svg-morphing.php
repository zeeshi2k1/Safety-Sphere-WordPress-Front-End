<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Core\Schemes\Color as Scheme_Color;
use Elementor\Group_Control_Image_Size;
use Elementor\Repeater;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class SvgMorphing extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public function get_script_depends()
    {
        return ['dce-anime-lib', 'dce-flubber-lib', 'dce-svgmorph'];
    }
    public function get_style_depends()
    {
        return ['dce-svg'];
    }
    protected $svg_shapes = array('path' => 'path', 'polyline' => 'polyline');
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_svg_controls', ['label' => esc_html__('Controls', 'dynamic-content-for-elementor')]);
        $this->add_control('svg_trigger', ['label' => esc_html__('Trigger', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['animation' => esc_html__('Animation', 'dynamic-content-for-elementor'), 'rollover' => esc_html__('Rollover', 'dynamic-content-for-elementor'), 'scroll' => esc_html__('Scroll', 'dynamic-content-for-elementor')], 'frontend_available' => \true, 'default' => 'animation', 'prefix_class' => 'svg-trigger-', 'separator' => 'after', 'render_type' => 'template']);
        $this->add_control('link_to', ['label' => esc_html__('Link to', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'default' => 'none', 'options' => ['none' => esc_html__('None', 'dynamic-content-for-elementor'), 'home' => esc_html__('Home URL', 'dynamic-content-for-elementor'), 'custom' => esc_html__('Custom URL', 'dynamic-content-for-elementor')], 'condition' => ['svg_trigger' => 'rollover']]);
        $this->add_control('link', ['label' => esc_html__('Link', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::URL, 'placeholder' => esc_html__('https://your-link.com', 'dynamic-content-for-elementor'), 'dynamic' => ['active' => \true], 'condition' => ['link_to' => 'custom', 'svg_trigger' => 'rollover'], 'default' => ['url' => ''], 'show_label' => \false]);
        $this->add_control('one_by_one', ['label' => esc_html__('One by one', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes', 'frontend_available' => \true, 'separator' => 'before', 'condition' => ['svg_trigger' => 'scroll']]);
        $this->add_control('playpause_control', ['label' => esc_html__('Animation Controls', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'default' => 'running', 'description' => esc_html__('In pause mode, it is possible to shape the shapes. You can manage the animation between one scene and another in play mode.', 'dynamic-content-for-elementor'), 'toggle' => \false, 'options' => ['running' => ['title' => esc_html__('Play', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-play'], 'paused' => ['title' => esc_html__('Pause', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-pause']], 'frontend_available' => \true, 'separator' => 'before', 'render_type' => 'ui', 'condition' => ['svg_trigger!' => 'rollover']]);
        $this->add_control('yoyo', ['label' => esc_html__('Yoyo', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'frontend_available' => \true, 'separator' => 'before', 'condition' => ['svg_trigger' => 'animation']]);
        $this->add_control('repeat_morph', ['label' => esc_html__('Repeat', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'label_block' => \false, 'frontend_available' => \true, 'description' => esc_html__('Infinite: -1 or do not repeat: 0', 'dynamic-content-for-elementor'), 'default' => -1, 'min' => -1, 'max' => 25, 'step' => 1, 'condition' => ['svg_trigger!' => 'rollover', 'one_by_one' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_creative_svg', ['label' => esc_html__('SVG & Viewbox', 'dynamic-content-for-elementor')]);
        // Deprecated Polyline, but don't remove settings
        $this->add_control('type_of_shape', ['label' => esc_html__('Shape Type', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HIDDEN, 'options' => $this->svg_shapes, 'default' => 'path', 'description' => esc_html__('Type of SVG sequence', 'dynamic-content-for-elementor'), 'frontend_available' => \true, 'label_block' => \true]);
        $this->add_control('enable_image', ['label' => esc_html__('Pattern image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'frontend_available' => \true, 'separator' => 'before']);
        $this->add_control('viewBox_heading', ['label' => esc_html__('SVG ViewBox', 'dynamic-content-for-elementor'), 'description' => esc_html__('The pixel size of the document you drew the shapes on', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $this->add_control('viewbox_width', ['label' => esc_html__('Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'label_block' => \false, 'default' => 600, 'min' => 100, 'max' => 2000, 'step' => 1]);
        $this->add_control('viewbox_height', ['label' => esc_html__('Height', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'label_block' => \false, 'default' => 600, 'min' => 100, 'max' => 2000, 'step' => 1]);
        $this->add_responsive_control('svg_width', ['label' => esc_html__('Content Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'separator' => 'before', 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px', '%'], 'range' => ['%' => ['min' => 1, 'max' => 100, 'step' => 0.1], 'px' => ['min' => 1, 'max' => 3500]], 'selectors' => ['{{WRAPPER}} svg.dce-svg-morph' => 'width: {{SIZE}}{{UNIT}};']]);
        $this->add_responsive_control('svg_height', ['label' => esc_html__('Content Height', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '', 'unit' => 'px'], 'size_units' => ['px', '%'], 'range' => ['%' => ['min' => 1, 'max' => 100, 'step' => 0.1], 'px' => ['min' => 1, 'max' => 2000]], 'selectors' => ['{{WRAPPER}} svg.dce-svg-morph' => 'height: {{SIZE}}{{UNIT}};']]);
        $repeater = new \Elementor\Repeater();
        $repeater->add_control('id_shape', ['label' => 'ID', 'type' => Controls_Manager::TEXT, 'default' => 'shape-']);
        $repeater->add_control('shape_numbers', ['label' => esc_html__('Numbers', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXTAREA, 'default' => '']);
        $repeater->add_control('transform_heading', ['label' => esc_html__('Transform', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $repeater->add_control('shape_rotation', ['label' => esc_html__('Rotation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '0'], 'render_type' => 'ui', 'range' => ['px' => ['min' => -180, 'max' => 180, 'step' => 1]], 'label_block' => \true]);
        $repeater->add_control('position_heading', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $repeater->add_control('shape_x', ['label' => esc_html__('X', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'render_type' => 'ui', 'range' => ['px' => ['min' => -500, 'max' => 500, 'step' => 1]], 'label_block' => \false]);
        $repeater->add_control('shape_y', ['label' => esc_html__('Y', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => ''], 'render_type' => 'ui', 'range' => ['px' => ['min' => -500, 'max' => 500, 'step' => 1]], 'label_block' => \false]);
        $repeater->add_control('style_heading', ['label' => esc_html__('Style', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $repeater->add_control('fill_image', ['label' => esc_html__('Fill Image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::MEDIA, 'default' => ['url' => ''], 'dynamic' => ['active' => \true]]);
        $repeater->add_control('fill_color', ['label' => esc_html__('Fill Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#FF0000']);
        $repeater->add_control('stroke_color', ['label' => esc_html__('Stroke Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#000000']);
        $repeater->add_control('stroke_width', ['label' => esc_html__('Stroke Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 60, 'step' => 1]], 'label_block' => \false]);
        $repeater->add_control('animation_heading', ['label' => esc_html__('Animation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $repeater->add_control('speed_morph', ['label' => esc_html__('Speed', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'label_block' => \false, 'default' => ['size' => ''], 'range' => ['px' => ['min' => 0.2, 'max' => 5, 'step' => 0.1]], 'frontend_available' => \true]);
        $repeater->add_control('duration_morph', ['label' => esc_html__('Step Duration', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'label_block' => \false, 'default' => ['size' => ''], 'range' => ['px' => ['min' => 0.1, 'max' => 10, 'step' => 0.1]], 'frontend_available' => \true]);
        $repeater->add_control('easing_morph', ['label' => esc_html__('Easing', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['' => esc_html__('Default', 'dynamic-content-for-elementor')] + Helper::get_ease(), 'default' => '', 'frontend_available' => \true, 'label_block' => \false]);
        $repeater->add_control('easing_morph_ease', ['label' => esc_html__('Equation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['' => esc_html__('Default', 'dynamic-content-for-elementor')] + Helper::get_timing_functions(), 'default' => '', 'frontend_available' => \true, 'label_block' => \false]);
        $this->end_controls_section();
        $this->start_controls_section('section_svg_animations', ['label' => esc_html__('Animations', 'dynamic-content-for-elementor')]);
        $this->add_control('playpause_info_animation', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('You\'re on pause mode. It would be better to be in play mode. If you\'re watching the scene in pause mode, you won\'t see the changes to the parameters of the animations', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'separator' => 'after', 'condition' => ['playpause_control' => 'paused']]);
        $this->add_control('speed_morph', ['label' => esc_html__('Speed Transition', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 0.7], 'range' => ['px' => ['min' => 0.2, 'max' => 5, 'step' => 0.1]], 'frontend_available' => \true]);
        $this->add_control('duration_morph', ['label' => esc_html__('Step Duration', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 1], 'range' => ['px' => ['min' => 0, 'max' => 12, 'step' => 0.1]], 'frontend_available' => \true]);
        $this->add_control('easing_morph', ['label' => esc_html__('Easing', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_ease(), 'default' => 'easeInOut', 'frontend_available' => \true, 'label_block' => \false]);
        $this->add_control('easing_morph_ease', ['label' => esc_html__('Equation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => Helper::get_timing_functions(), 'default' => 'Power3', 'frontend_available' => \true, 'label_block' => \false]);
        $this->end_controls_section();
        $count = 0;
        foreach ($this->svg_shapes as $svgs) {
            if ($svgs == 'path') {
                $default_shape = [['id_shape' => $svgs . '_1', 'shape_numbers' => 'M438.7,254.2L587,508.4H293.5H0l148.3-254.2L293.5,0L438.7,254.2z'], ['id_shape' => $svgs . '_2', 'shape_numbers' => 'M600,259.8L450,519.6H150L0,259.8L150,0h300L600,259.8z'], ['id_shape' => $svgs . '_3', 'shape_numbers' => 'M568,568H0l172.5-284L0,0h568L395.5,287L568,568z'], ['id_shape' => $svgs . '_4', 'shape_numbers' => 'M568,568H0l1.7-284L0,0h568l-1.7,287L568,568z']];
            } elseif ($svgs == 'polyline') {
                $default_shape = [['id_shape' => $svgs . '_1', 'shape_numbers' => '0.3,131.7 142.3,42.7 210.3,239.7 265.3,8.7 307.3,220.7 378.3,1.7 443.3,232.7 554.3,175.7 '], ['id_shape' => $svgs . '_2', 'shape_numbers' => '0.2,103.2 157.2,190.2 211.2,65.2 269.2,160.2 361.2,1.2 438.2,227.2 488.2,30.2 554.2,147.2 ']];
            }
            $this->start_controls_section('section_svg_' . $svgs, ['label' => $svgs, 'condition' => ['type_of_shape' => $svgs]]);
            $this->add_control('playpause_info_' . $svgs, ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('You are in play mode. It would be better to be in Pause Mode. If you are watching the scene in play mode, it is difficult to change the parameters of the shapes. Pause and switch between shapes by clicking on the block', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'separator' => 'after', 'condition' => ['playpause_control' => 'running']]);
            $this->add_control('repeater_shape_' . $svgs, ['label' => 'Shape ' . $svgs, 'type' => Controls_Manager::REPEATER, 'default' => $default_shape ?? '', 'fields' => $repeater->get_controls(), 'title_field' => '{{{ id_shape }}}', 'frontend_available' => \true]);
            $this->end_controls_section();
            ++$count;
        }
        // Section for pattern image
        $this->start_controls_section('section_svg_bgimage', ['label' => esc_html__('Pattern Image', 'dynamic-content-for-elementor'), 'condition' => ['enable_image' => 'yes']]);
        $this->add_control('playpause_info_image', ['type' => Controls_Manager::RAW_HTML, 'show_label' => \false, 'raw' => esc_html__('You are in play mode. It would be better to be in Pause Mode. If you are watching the scene in play mode, it is difficult to change the parameters of the shapes. Pause and switch between shapes by clicking on the block', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'separator' => 'after', 'condition' => ['playpause_control' => 'running']]);
        $this->add_control('svg_image', ['label' => esc_html__('Image', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::MEDIA, 'default' => ['url' => ''], 'frontend_available' => \true, 'show_label' => \false, 'dynamic' => ['active' => \true]]);
        $this->add_group_control(Group_Control_Image_Size::get_type(), ['name' => 'image', 'default' => 'thumbnail']);
        $this->add_responsive_control('svg_size', ['label' => esc_html__('Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '100', 'unit' => '%'], 'size_units' => ['%', 'px'], 'range' => ['%' => ['min' => 1, 'max' => 200], 'px' => ['min' => 1, 'max' => 2000]]]);
        $this->add_control('svgimage_x', ['label' => esc_html__('X', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '0'], 'size_units' => ['%', 'px'], 'range' => ['%' => ['min' => -100, 'max' => 100], 'px' => ['min' => -500, 'max' => 500, 'step' => 1]], 'label_block' => \false]);
        $this->add_control('svgimage_y', ['label' => esc_html__('Y', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => '0'], 'size_units' => ['%', 'px'], 'range' => ['%' => ['min' => -100, 'max' => 100], 'px' => ['min' => -500, 'max' => 500, 'step' => 1]], 'label_block' => \false]);
        $this->end_controls_section();
        $this->start_controls_section('section_style', ['label' => esc_html__('Style', 'dynamic-content-for-elementor')]);
        $this->add_responsive_control('svg_align', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-align-right']], 'prefix_class' => 'align-', 'default' => 'left', 'selectors' => ['{{WRAPPER}}' => 'text-align: {{VALUE}};']]);
        $this->end_controls_section();
    }
    protected function realHeight($imgid, $imgsize, $imgformat)
    {
        $imageData = wp_get_attachment_image_src($imgid, $imgformat);
        $h = $imageData[2];
        $w = $imageData[1];
        $imageProportion = $h / $w;
        $realHeight = $imgsize * $imageProportion;
        return $realHeight;
    }
    protected function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        // Temporary force path shape
        $settings['type_of_shape'] = 'path';
        $id_page = Helper::get_the_id();
        $widget_id = $this->get_id();
        $runAnimation = $settings['playpause_control'];
        if ($settings['svg_trigger'] == 'rollover' || $settings['svg_trigger'] == 'scroll') {
            $runAnimation = 'paused';
        }
        $keyVector = 'd';
        //'d' -> path, 'points' -> polyline
        // if ( $settings['type_of_shape'] === 'polygon' || $settings['type_of_shape'] === 'polyline' ) {
        //  $keyVector = 'points'; // -> Polygon
        // }
        $coeff = '0.5';
        $this->add_render_attribute('_wrapper', 'data-coeff', $coeff);
        $image_url = '';
        $viewBoxW = $settings['viewbox_width'];
        $viewBoxH = $settings['viewbox_height'];
        switch ($settings['link_to']) {
            case 'custom':
                if (!empty($settings['link']['url'])) {
                    $link = esc_url($settings['link']['url']);
                } else {
                    $link = \false;
                }
                break;
            case 'home':
                $link = esc_url(get_home_url());
                break;
            case 'none':
            default:
                $link = \false;
                break;
        }
        $allowed_type = isset($this->svg_shapes[$settings['type_of_shape']]) ? $this->svg_shapes[$settings['type_of_shape']] : 'path';
        ?>
		<div class="dce-svg-morph-wrap">
			<?php 
        $target = !empty($settings['link']['is_external']) ? 'target="_blank"' : '';
        if ($link) {
            echo '<a href="' . $link . '" ' . $target . '>';
        }
        ?>
			<svg id="dce-svg-<?php 
        echo esc_attr($widget_id);
        ?>" class="dce-svg-morph" data-morphid="0" data-run="<?php 
        echo esc_attr($runAnimation);
        ?>" version="1.1" xmlns="http://www.w3.org/2000/svg"  width="100%" height="100%" viewBox="0 0 <?php 
        echo esc_attr($viewBoxW);
        ?> <?php 
        echo esc_attr($viewBoxH);
        ?>" preserveAspectRatio="xMidYMid meet" xml:space="preserve" style="transform: rotate(<?php 
        echo esc_attr($settings['repeater_shape_' . $settings['type_of_shape']][0]['shape_rotation']['size']);
        ?>deg) translate(<?php 
        echo esc_attr($settings['repeater_shape_' . $settings['type_of_shape']][0]['shape_x']['size']);
        ?>px,<?php 
        echo esc_attr($settings['repeater_shape_' . $settings['type_of_shape']][0]['shape_y']['size']);
        ?>px);">

				<?php 
        if ($settings['enable_image']) {
            $posX = $settings['svgimage_x']['size'] ?? 0;
            $posY = $settings['svgimage_y']['size'] ?? 0;
            $image_id = $settings['svg_image']['id'];
            $image_url = Group_Control_Image_Size::get_attachment_image_src($image_id, 'image', $settings);
            ?>
					<defs>
						<?php 
            $heightPattern = $settings['svg_size']['size'] . $settings['svg_size']['unit'];
            if ($settings['svg_image']['url'] != '') {
                $heightPattern = $this->realHeight($image_id, $settings['svg_size']['size'], $settings['image_size']) . $settings['svg_size']['unit'];
            }
            ?>
						<pattern id="pattern-<?php 
            echo esc_attr($widget_id);
            ?>" patternUnits="userSpaceOnUse" patternContentUnits="userSpaceOnUse" width="<?php 
            echo esc_attr($settings['svg_size']['size'] . $settings['svg_size']['unit']);
            ?>" height="<?php 
            echo esc_attr($heightPattern);
            ?>" x="<?php 
            echo esc_attr($posX . $settings['svgimage_x']['unit']);
            ?>" y="<?php 
            echo esc_attr($posY . $settings['svgimage_y']['unit']);
            ?>">
							<?php 
            if ($settings['svg_image']['url'] != '') {
                ?>
								<image id="img-patt-base" xlink:href="<?php 
                echo esc_url($image_url);
                ?>" width="<?php 
                echo esc_attr($settings['svg_size']['size'] . $settings['svg_size']['unit']);
                ?>" height="<?php 
                echo esc_attr($this->realHeight($image_id, $settings['svg_size']['size'], $settings['image_size']) . $settings['svg_size']['unit']);
                ?>"> </image>
							<?php 
            }
            if ($settings['repeater_shape_' . $settings['type_of_shape']]) {
                $count = 0;
                $repeater_shape = $settings['repeater_shape_' . $settings['type_of_shape']];
                foreach ($repeater_shape as $item) {
                    if ($item['fill_image']['url'] != '') {
                        $image_id_pattern = $item['fill_image']['id'];
                        $image_url_pattern = Group_Control_Image_Size::get_attachment_image_src($image_id_pattern, 'image', $settings);
                        $visible = ' style="opacity:1"';
                        if ($count > 0) {
                            $visible = ' style="opacity:0"';
                        }
                        ?>

										<image id="img-patt-<?php 
                        echo $count;
                        ?>" class="dce-shape-image dce-shape-image-repeater-item-<?php 
                        echo esc_attr($item['_id']);
                        ?>" xlink:href="<?php 
                        echo esc_url($image_url_pattern);
                        ?>" width="<?php 
                        echo esc_attr($settings['svg_size']['size'] . $settings['svg_size']['unit']);
                        ?>" height="<?php 
                        echo esc_attr($this->realHeight($image_id_pattern, $settings['svg_size']['size'], $settings['image_size']) . $settings['svg_size']['unit']);
                        ?>"<?php 
                        echo $visible;
                        ?>> </image>
										<?php 
                    }
                    ++$count;
                }
            }
            ?>
						</pattern>
					</defs>
				<?php 
        }
        ?>

				<?php 
        $fill_color = $settings['repeater_shape_' . $settings['type_of_shape']][0]['fill_color'];
        $fill_image = $settings['repeater_shape_' . $settings['type_of_shape']][0]['fill_image']['id'];
        $fill_element = $fill_color;
        if ($fill_image || $image_url) {
            $fill_element = 'url(#pattern-' . $this->get_id() . ')';
        }
        ?>
				<<?php 
        echo $allowed_type;
        ?> id="shape-<?php 
        echo esc_attr($widget_id);
        ?>" fill="<?php 
        echo esc_attr($fill_element);
        ?>" stroke-width="<?php 
        echo esc_attr($settings['repeater_shape_' . $settings['type_of_shape']][0]['stroke_width']['size']);
        ?>" stroke="<?php 
        echo esc_attr($settings['repeater_shape_' . $settings['type_of_shape']][0]['stroke_color']);
        ?>" stroke-miterlimit="10" <?php 
        echo esc_attr($keyVector);
        ?>="<?php 
        echo esc_attr($settings['repeater_shape_' . $settings['type_of_shape']][0]['shape_numbers']);
        ?>"/>
			</svg>
			<?php 
        if ($link) {
            echo '</a>';
        }
        ?>
		</div>

		<?php 
    }
    protected function content_template()
    {
        ?>
		<#
		var currentItem = (editSettings.activeItemIndex >= 0) ? editSettings.activeItemIndex : false;
		var morphid = (currentItem) ? currentItem-1 : 0;
		var idWidget = id;
		
		var viewBoxW = settings.viewbox_width;
		var viewBoxH = settings.viewbox_height;
		
		var allowed_shapes = {'path': 'path', 'polyline': 'polyline'};
		var typeShape = allowed_shapes[settings.type_of_shape] || 'path';

		// PATTERN Image
		var image = {
			id: settings.svg_image.id,
			url: settings.svg_image.url,
			size: settings.image_size,
			dimension: settings.image_custom_dimension,
			model: view.getEditModel()
		};
		var bgImage = elementor.imagesManager.getImageUrl(image);

		var sizeImage = settings.svg_size.size;
		var sizeUnitImage = settings.svg_size.unit;
		var enable_image = settings.enable_image;

		var image_x = settings.svgimage_x.size;
		var image_y = settings.svgimage_y.size;
		if(image_x == '') image_x = '0';
		if(image_y == '') image_y = '0';

		var sizeUnitXImage = settings.svgimage_x.unit;
		var sizeUnitYImage = settings.svgimage_y.unit;

		var runAnimation = settings.playpause_control;
		if(settings.svg_trigger == 'rollover' || settings.svg_trigger == 'scroll') {
			runAnimation = 'paused';
		}

		var shapeNumbers = settings['repeater_shape_' + typeShape] || [];
		var indexShape = 0;
		if(morphid) {
			indexShape = morphid;
		}

		if(shapeNumbers[indexShape] != undefined && shapeNumbers.length) {
			var firstShape = shapeNumbers[indexShape]['shape_numbers'] || '';
			if(firstShape == '') firstShape = shapeNumbers[indexShape-1]['shape_numbers'];

			var firstFill = shapeNumbers[indexShape]['fill_color'] || '#ccc';
			var firstStrokeColor = shapeNumbers[indexShape]['stroke_color'] || '#000';
			var firstStrokeWidth = shapeNumbers[indexShape]['stroke_width']['size'] || 0;

			// -- Fill --
			var fill_element = firstFill;

			var firstPosX = shapeNumbers[indexShape]['shape_x']['size'] || 0;
			var firstPosY = shapeNumbers[indexShape]['shape_y']['size'] || 0;
			var firstRotation = shapeNumbers[indexShape]['shape_rotation']['size'] || 0;

			var keyVector = 'd';
			if(typeShape == 'polygon' || typeShape == 'polyline') keyVector = 'points';

			var iFrameDOM = jQuery("iframe#elementor-preview-iframe").contents();

			dce_getimageSizes(bgImage, function(data) {
				if(jQuery("iframe#elementor-preview-iframe").length) {
					var pattern = iFrameDOM.find('pattern#pattern-'+idWidget);
					var patternImage = iFrameDOM.find('pattern#pattern-'+idWidget+' image');

					if(patternImage.length) {
						var realHeight = data.coef * settings.svg_size.size;
						pattern.attr('height', realHeight+settings.svg_size.unit);
						patternImage.attr('height', realHeight+settings.svg_size.unit);
					}
				}
			});

			var link_url;
			if('custom' === settings.link_to) {
				link_url = settings.link.url;
			}
			#>
			<div class="dce-svg-morph-wrap">
				<# if(link_url) { #>
					<a href="{{ link_url }}">
				<# } #>

				<svg id="dce-svg-{{idWidget}}" 
					class="dce-svg-morph" 
					data-run="{{runAnimation}}" 
					data-morphid="{{morphid}}" 
					version="1.1" 
					xmlns="http://www.w3.org/2000/svg" 
					stroke-miterlimit="10" 
					width="100%" 
					height="100%" 
					viewBox="0 0 {{viewBoxW}} {{viewBoxH}}" 
					preserveAspectRatio="xMidYMid meet" 
					xml:space="preserve" 
					style="transform: rotate({{firstRotation}}deg) translate({{firstPosX}}px,{{firstPosY}}px);">

					<# if(enable_image) { #>
						<defs>
							<pattern id="pattern-{{idWidget}}" 
								patternUnits="userSpaceOnUse" 
								patternContentUnits="userSpaceOnUse" 
								width="{{sizeImage}}{{sizeUnitImage}}" 
								height="{{sizeImage}}{{sizeUnitImage}}" 
								x="{{image_x}}{{sizeUnitXImage}}" 
								y="{{image_y}}{{sizeUnitYImage}}">

								<# if(bgImage) { #>
									<image id="img-patt-base" 
										xlink:href="{{bgImage}}" 
										width="{{sizeImage}}{{sizeUnitImage}}" 
										height="{{sizeImage}}{{sizeUnitImage}}">
									</image>
								<# } #>

								<# if(shapeNumbers.length) {
									var count = 0;
									var image_url_pattern = '';
									_.each(shapeNumbers, function(item) {
										var image_pattern = {
											id: item.fill_image.id,
											url: item.fill_image.url,
											size: settings.image_size,
											dimension: settings.image_custom_dimension,
											model: view.getEditModel()
										};
										image_url_pattern = elementor.imagesManager.getImageUrl(image_pattern);

										if(image_url_pattern) {
											var visible = ' style=\"opacity:1\"';
											if(count > 0) visible = ' style=\"opacity:0\"';
											#>
											<image id="img-patt-{{count}}" 
												class="dce-shape-image elementor-repeater-item-{{item._id}}"  
												xlink:href="{{image_url_pattern}}" 
												width="{{sizeImage}}{{sizeUnitImage}}" 
												height="{{sizeImage}}{{sizeUnitImage}}"{{visible}}>
											</image>
											<#
											count++;
										}
									});
								} #>
							</pattern>
						</defs>
					<# }
						if(bgImage || image_url_pattern) {
							fill_element = 'url(#pattern-'+idWidget+')';
						}

					#>

					<{{typeShape}} id="shape-{{idWidget}}" 
						fill="{{fill_element}}" 
						stroke-width="{{firstStrokeWidth}}" 
						stroke="{{firstStrokeColor}}" 
						{{keyVector}}="{{firstShape}}"/>
				</svg>

				<# if(link_url) { #>
					</a>
				<# } #>
			</div>

		<# } #>
		<?php 
    }
}
