<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use DynamicContentForElementor\Helper;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use ElementorPro\Modules\Forms\Fields;
use ElementorPro\Modules\Forms\Classes;
use ElementorPro\Modules\Forms\Widgets\Form;
use ElementorPro\Plugin;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class Signature extends \ElementorPro\Modules\Forms\Fields\Field_Base
{
    private $is_common = \false;
    public $has_action = \false;
    public $depended_scripts = ['dce-signature-lib', 'dce-signature'];
    public function __construct()
    {
        add_action('elementor/element/form/section_form_style/after_section_end', [$this, 'add_control_section_to_form'], 10, 2);
        add_action('elementor/widget/print_template', function ($template, $widget) {
            if ('form' === $widget->get_name()) {
                $template = \false;
            }
            return $template;
        }, 10, 2);
        parent::__construct();
    }
    /**
     * Update the repeater "form_fields" with custom controls for the signature field
     */
    public function update_controls($widget)
    {
        $elementor = Plugin::elementor();
        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
        if (is_wp_error($control_data)) {
            return;
        }
        $field_controls = ['signature_save_to_file' => ['name' => 'signature_save_to_file', 'label' => esc_html__('Save to file', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'inner_tab' => 'form_fields_content_tab', 'tabs_wrapper' => 'form_fields_tabs', 'condition' => ['field_type' => $this->get_type()]], 'signature_jpeg' => ['name' => 'signature_jpeg', 'label' => esc_html__('Transmit using JPEG', 'dynamic-content-for-elementor'), 'description' => esc_html__('Use this if the signature fails to appear in PDFs.', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'no', 'inner_tab' => 'form_fields_content_tab', 'tabs_wrapper' => 'form_fields_tabs', 'condition' => ['field_type' => $this->get_type()]]];
        $control_data['fields'] = $this->inject_field_controls($control_data['fields'], $field_controls);
        $widget->update_control('form_fields', $control_data);
    }
    /**
     * Add signature-specific style controls to the Form widget
     */
    public function add_control_section_to_form($element, $args)
    {
        $element->start_controls_section('dce_section_signature_buttons_style', ['label' => '<span class="color-dce icon-dce-logo-dce pull-right ml-1"></span> ' . esc_html__('Signature', 'dynamic-content-for-elementor'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $element->add_control('signature_alignment', ['label' => esc_html__('Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['left' => ['title' => esc_html__('Left', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-left'], 'center' => ['title' => esc_html__('Center', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-center'], 'right' => ['title' => esc_html__('Right', 'dynamic-content-for-elementor'), 'icon' => 'eicon-text-align-right']], 'default' => is_rtl() ? 'right' : 'left', 'selectors' => ['{{WRAPPER}} .dce-signature-wrapper' => 'text-align: {{VALUE}};']]);
        $element->add_responsive_control('signature_canvas_width', ['label' => esc_html__('Signature Pad Width', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px', '%'], 'default' => ['unit' => 'px', 'size' => 400], 'range' => ['px' => ['min' => 1, 'max' => 1200, 'step' => 10], '%' => ['min' => 1, 'max' => 100]], 'selectors' => ['{{WRAPPER}} .dce-signature-wrapper' => '--canvas-width: {{SIZE}}{{UNIT}};']]);
        $element->add_control('signature_canvas_aspect_ratio', ['label' => esc_html__('Aspect Ratio (Width ÷ Height)', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'default' => ['size' => 2], 'range' => ['px' => ['min' => 0.1, 'max' => 5, 'step' => 0.1]], 'description' => esc_html__('For example, 2 means the height is half the width.', 'dynamic-content-for-elementor')]);
        $element->add_control('signature_canvas_border_radius', ['label' => esc_html__('Pad Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => '3', 'right' => '3', 'bottom' => '3', 'left' => '3', 'unit' => 'px'], 'size_units' => ['px', '%'], 'selectors' => ['{{WRAPPER}} .dce-signature-canvas' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'], 'separator' => 'before']);
        $element->add_control('signature_canvas_border_width', ['label' => esc_html__('Pad Border Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'default' => ['top' => '1', 'right' => '1', 'bottom' => '1', 'left' => '1', 'unit' => 'px'], 'size_units' => ['px'], 'selectors' => ['{{WRAPPER}} .dce-signature-canvas' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $element->add_control('signature_canvas_background_color', ['label' => esc_html__('Pad Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => ['{{WRAPPER}} .dce-signature-canvas' => 'background-color: {{VALUE}};']]);
        $element->add_control('signature_canvas_pen_color', ['label' => esc_html__('Pen Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#000000']);
        $element->add_control('signature_clear_heading', ['label' => esc_html__('Clear Button', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING, 'separator' => 'before']);
        $element->add_control('signature_clear_icon', ['label' => esc_html__('Icon', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::ICONS, 'default' => ['value' => 'eicon-close', 'library' => 'eicons']]);
        $element->add_control('signature_clear_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '#e62626', 'selectors' => ['{{WRAPPER}} .dce-signature-button-clear' => 'color: {{VALUE}};', '{{WRAPPER}} .dce-signature-button-clear i' => 'color: {{VALUE}};', '{{WRAPPER}} .dce-signature-button-clear svg' => 'fill: {{VALUE}};']]);
        $element->add_control('signature_clear_background', ['label' => esc_html__('Background Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-signature-button-clear' => 'background-color: {{VALUE}};']]);
        $element->add_control('signature_clear_background_hover', ['label' => esc_html__('Background Hover Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'default' => '', 'selectors' => ['{{WRAPPER}} .dce-signature-button-clear:hover' => 'background-color: {{VALUE}};']]);
        $element->add_group_control(Group_Control_Border::get_type(), ['name' => 'signature_clear_border', 'label' => esc_html__('Border', 'dynamic-content-for-elementor'), 'selector' => '{{WRAPPER}} .dce-signature-button-clear']);
        $element->add_control('signature_clear_border_radius', ['label' => esc_html__('Border Radius', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => ['px', '%', 'em'], 'selectors' => ['{{WRAPPER}} .dce-signature-button-clear' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};']]);
        $element->add_responsive_control('signature_clear_icon_size', ['label' => esc_html__('Icon Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'size_units' => ['px', 'em', 'rem'], 'range' => ['px' => ['min' => 5, 'max' => 100], 'em' => ['min' => 0.1, 'max' => 10], 'rem' => ['min' => 0.1, 'max' => 10]], 'default' => ['unit' => 'px', 'size' => 16], 'selectors' => ['{{WRAPPER}} .dce-signature-button-clear i' => 'font-size: {{SIZE}}{{UNIT}};', '{{WRAPPER}} .dce-signature-button-clear svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};']]);
        $element->end_controls_section();
    }
    public static function get_satisfy_dependencies()
    {
        return \true;
    }
    /**
     * @return array<string>
     */
    public function get_script_depends() : array
    {
        return $this->depended_scripts;
    }
    public function get_name()
    {
        return 'Signature';
    }
    public function get_label()
    {
        return esc_html__('Form Signature', 'dynamic-content-for-elementor');
    }
    public function get_type()
    {
        return 'dce_form_signature';
    }
    /**
     * @return array<string>
     */
    public function get_style_depends() : array
    {
        return $this->depended_styles;
    }
    public function render($item, $item_index, $form)
    {
        $settings = $form->get_settings_for_display();
        // We do not use type hidden so the browser will honor required:
        $hidden_css = 'width: 0; height: 0; opacity: 0; position: absolute; pointer-events: none;';
        $form->add_render_attribute('input' . $item_index, 'style', $hidden_css, \true);
        $form->add_render_attribute('signature-canvas' . $item_index, 'class', 'dce-signature-canvas');
        if (isset($settings['signature_canvas_pen_color'])) {
            $form->add_render_attribute('signature-canvas' . $item_index, 'data-pen-color', $settings['signature_canvas_pen_color']);
        }
        $form->add_render_attribute('signature-canvas' . $item_index, 'data-background-color', $settings['signature_canvas_background_color'] ?? '#ffffff');
        $form->add_render_attribute('signature-canvas' . $item_index, 'data-jpeg', $item['signature_jpeg']);
        // Aspect ratio
        $aspect_ratio = !empty($settings['signature_canvas_aspect_ratio']['size']) ? \floatval($settings['signature_canvas_aspect_ratio']['size']) : 2;
        $form->add_render_attribute('signature-canvas' . $item_index, 'data-aspect-ratio', $aspect_ratio);
        // Fallback: HTML <canvas> size = 1x1, so JS can set the true size internally
        $form->add_render_attribute('signature-canvas' . $item_index, 'width', '1');
        $form->add_render_attribute('signature-canvas' . $item_index, 'height', '1');
        // CSS: expand to 100% of the container, height calculated from aspect ratio
        $form->add_render_attribute('signature-canvas' . $item_index, 'style', \sprintf('width: 100%%; height: calc(100%% / %s); border-style: solid; touch-action: none; user-select: none;', $aspect_ratio));
        // Outer wrapper for full width
        $form->add_render_attribute('signature-outer-wrapper' . $item_index, 'style', 'width: 100%; display: block;');
        // Signature wrapper
        $form->add_render_attribute('signature-wrapper' . $item_index, 'class', 'dce-signature-wrapper');
        $form->add_render_attribute('signature-wrapper' . $item_index, 'style', 'width: var(--canvas-width); min-width: 200px;');
        ?>
		<div <?php 
        echo $form->get_render_attribute_string('signature-outer-wrapper' . $item_index);
        ?>>
			<div <?php 
        echo $form->get_render_attribute_string('signature-wrapper' . $item_index);
        ?>>
				<div style="position: relative; display: inline-block; width: 100%;">
					<button type="button"
							class="dce-signature-button-clear"
							data-action="clear"
							style="position: absolute; top: 0; right: 0; z-index: 10;">
						<?php 
        $icon = $settings['signature_clear_icon'] ?? ['value' => 'eicon-close', 'library' => 'eicons'];
        \Elementor\Icons_Manager::render_icon($icon);
        ?>
					</button>
					<input <?php 
        echo $form->get_render_attribute_string('input' . $item_index);
        ?>>
					<canvas <?php 
        echo $form->get_render_attribute_string('signature-canvas' . $item_index);
        ?>></canvas>
				</div>
			</div>
		</div>
		<?php 
    }
    /**
     * @param array<string,mixed> $field
     * @param Classes\Form_Record $record
     * @param Classes\Ajax_Handler $ajax_handler
     * @return void
     */
    public function validation($field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler)
    {
        $id = $field['id'];
        if ($field['required'] && '' === $field['raw_value']) {
            $ajax_handler->add_error($id, esc_html__('This signature field is required.', 'dynamic-content-for-elementor'));
        }
        if ('' === $field['raw_value']) {
            return;
        }
        if (!\preg_match('&^data:image/(jpeg|png);base64,[\\w\\d/+]+=*$&', $field['raw_value'])) {
            $ajax_handler->add_error($id, esc_html__('Invalid signature.', 'dynamic-content-for-elementor'));
        }
    }
    /**
     * @param string $data
     * @param string $dir_name
     * @param string $extension
     * @param Classes\Ajax_Handler $ajax_handler
     * @return array<string,string>|null
     */
    public function save_to_file($data, $dir_name, $extension, $ajax_handler)
    {
        $upload_dir = wp_upload_dir();
        $dir_abs_path = trailingslashit($upload_dir['basedir']) . 'dynamic/signatures/' . $dir_name;
        Helper::ensure_dir($dir_abs_path);
        /**
         * SPDX-SnippetBegin
         * SPDX-FileCopyrightText: Elementor
         * SPDX-License-Identifier: GPL-3.0-or-later
         */
        $filename = \uniqid() . '.' . $extension;
        $filename = wp_unique_filename($dir_abs_path, $filename);
        $new_file = trailingslashit($dir_abs_path) . $filename;
        if (\is_dir($dir_abs_path) && \is_writable($dir_abs_path)) {
            $res = \file_put_contents($new_file, $data);
            if ($res) {
                @\chmod($new_file, 0644);
                $url = trailingslashit($upload_dir['baseurl']) . 'dynamic/signatures/' . trailingslashit($dir_name) . $filename;
                return ['url' => $url, 'loc' => $new_file];
            } else {
                $ajax_handler->add_error_message(esc_html__('There was an error while trying to save your signature.', 'dynamic-content-for-elementor'));
                return null;
            }
        } else {
            $ajax_handler->add_admin_error_message(esc_html__('Signature save directory is not writable or does not exist.', 'dynamic-content-for-elementor'));
            return null;
        }
        /**
         * SPDX-SnippetEnd
         */
    }
    /**
     * @param array<string, mixed> $field
     * @param Classes\Form_Record $record
     * @param Classes\Ajax_Handler $ajax_handler
     * @return void
     */
    public function process_field($field, Classes\Form_Record $record, Classes\Ajax_Handler $ajax_handler)
    {
        $value = $field['value'];
        if ('' === $value) {
            return;
        }
        $settings = Helper::get_form_field_settings($field['id'], $record);
        if (($settings['signature_save_to_file'] ?? '') !== 'yes') {
            return;
        }
        \preg_match('&^data:image/(jpeg|png);base64,([\\w\\d/+]+=*)$&', $value, $matches);
        $extension = $matches[1];
        $encoded_image = $matches[2];
        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $decoded_image = \base64_decode($encoded_image);
        $dir_name = $settings['_id'];
        if (!\preg_match('/[\\w\\d_]+/', $dir_name)) {
            $ajax_handler->add_admin_error_message(esc_html__('Invalid field ID', 'dynamic-content-for-elementor'));
            return;
        }
        $saved = $this->save_to_file($decoded_image, $dir_name, $extension, $ajax_handler);
        if (!empty($saved['url']) && !empty($saved['loc'])) {
            $record->update_field($field['id'], 'value', $saved['url']);
            $record->update_field($field['id'], 'raw_value', $saved['loc']);
        }
    }
}
