<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use DynamicContentForElementor\Helper;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class DynamicCharts extends \DynamicContentForElementor\Widgets\WidgetPrototype
{
    public function get_script_depends()
    {
        return ['dce-jquery-color', 'dce-chart-js', 'dce-dynamic-charts'];
    }
    /**
     * Register controls after check if this feature is only for admin
     *
     * @return void
     */
    protected function safe_register_controls()
    {
        $this->start_controls_section('section_dynamic_charts', ['label' => $this->get_title()]);
        $this->add_control('type', ['label' => esc_html__('Type', 'dynamic-content-for-elementor'), 'type' => 'images_selector', 'type_selector' => 'icon', 'toggle' => \false, 'options' => ['bar' => ['title' => esc_html__('Bar', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-dynamic-charts'], 'line' => ['title' => esc_html__('Line', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-chart-line'], 'radar' => ['title' => esc_html__('Radar', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-chart-radar'], 'doughnut' => ['title' => esc_html__('Doughnut', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-chart-doughnut'], 'pie' => ['title' => esc_html__('Pie', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-chart-pie']], 'columns_grid' => 5, 'frontend_available' => \true, 'default' => 'bar']);
        $this->add_control('input', ['label' => esc_html__('Input Type', 'dynamic-content-for-elementor'), 'type' => 'images_selector', 'options' => ['simple' => ['title' => esc_html__('Simple', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-simple'], 'csv' => ['title' => esc_html__('CSV', 'dynamic-content-for-elementor'), 'return_val' => 'val', 'icon' => 'icon-dce-csv']], 'type_selector' => 'icon', 'columns_grid' => 5, 'toggle' => \false, 'default' => 'simple', 'frontend_available' => \true]);
        $this->add_control('csv_from', ['label' => esc_html__('CSV from', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['url' => esc_html__('URL', 'dynamic-content-for-elementor'), 'textarea' => esc_html__('Textarea', 'dynamic-content-for-elementor')], 'default' => 'url', 'condition' => ['input' => 'csv']]);
        $this->add_control('csv_url', ['label' => esc_html__('CSV Url', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'condition' => ['input' => 'csv', 'csv_from' => 'url']]);
        $this->add_control('csv_textarea', ['label' => esc_html__('CSV Textarea', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXTAREA, 'condition' => ['input' => 'csv', 'csv_from' => 'textarea']]);
        $this->add_control('csv_separator', ['label' => esc_html__('Separator', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => ',', 'condition' => ['input' => 'csv']]);
        $this->end_controls_section();
        $this->start_controls_section('section_input', ['label' => esc_html__('Input', 'dynamic-content-for-elementor')]);
        $this->add_control('labels', ['label' => esc_html__('Labels', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXTAREA, 'frontend_available' => \true, 'default' => esc_html__('America,Africa,Asia,Europe,Oceania', 'dynamic-content-for-elementor'), 'description' => esc_html__('Type values separated by comma', 'dynamic-content-for-elementor'), 'condition' => ['input' => 'simple']]);
        $this->add_control('data', ['label' => esc_html__('Data', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXTAREA, 'frontend_available' => \true, 'default' => '982,1277,4519,739,41', 'description' => esc_html__('Type values separated by comma', 'dynamic-content-for-elementor'), 'condition' => ['input' => 'simple']]);
        $this->add_control('csv_has_header', ['label' => esc_html__('CSV has Header', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'condition' => ['input' => 'csv']]);
        $this->add_control('csv_index_labels', ['label' => esc_html__('Column Index for Labels', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'default' => 2, 'condition' => ['input' => 'csv']]);
        $this->add_control('csv_index_data', ['label' => esc_html__('Column Index for Data', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'default' => 1, 'condition' => ['input' => 'csv']]);
        $this->add_control('show_legend', ['label' => esc_html__('Show Legend', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'frontend_available' => \true, 'condition' => ['type!' => ['pie', 'doughnut']]]);
        $this->add_control('legend', ['label' => esc_html__('Legend', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'frontend_available' => \true, 'default' => esc_html__('Population in milion', 'dynamic-content-for-elementor'), 'condition' => ['type!' => ['pie', 'doughnut'], 'show_legend!' => '']]);
        $this->add_control('show_title', ['label' => esc_html__('Show Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'frontend_available' => \true, 'condition' => ['type!' => ['pie', 'doughnut']]]);
        $this->add_control('title', ['label' => esc_html__('Title', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'frontend_available' => \true, 'default' => esc_html__('Population in milion', 'dynamic-content-for-elementor'), 'condition' => ['type!' => ['pie', 'doughnut'], 'show_title!' => '']]);
        $this->add_control('csv_limit', ['label' => esc_html__('Limit Records', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => '', 'condition' => ['input' => 'csv']]);
        $this->add_control('csv_limit_records', ['label' => esc_html__('Limit', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'min' => 1, 'condition' => ['input' => 'csv', 'csv_limit!' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_manipulation', ['label' => esc_html__('Manipulation', 'dynamic-content-for-elementor'), 'condition' => ['input' => 'csv']]);
        $this->add_control('labels_manipulation', ['label' => esc_html__('Labels Manipulation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $this->add_control('labels_manipulation_function', ['label' => esc_html__('Manipulation', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['unix_to_date' => esc_html__('Timestamp to Date', 'dynamic-content-for-elementor'), 'unix_to_datetime' => esc_html__('Timestamp to DateTime', 'dynamic-content-for-elementor'), 'unix_to_time' => esc_html__('Timestamp to Time', 'dynamic-content-for-elementor'), 'uppercase' => esc_html__('Uppercase', 'dynamic-content-for-elementor'), 'lowercase' => esc_html__('Lowercase', 'dynamic-content-for-elementor')], 'default' => 'unix_to_date', 'condition' => ['labels_manipulation!' => '']]);
        $this->add_control('add_text_to_value', ['label' => esc_html__('Add Text To Value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'frontend_available' => \true, 'default' => '', 'condition' => ['input' => 'csv']]);
        $this->add_control('text_to_value', ['label' => esc_html__('Text To Value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'frontend_available' => \true, 'default' => esc_html__('%', 'dynamic-content-for-elementor'), 'condition' => ['add_text_to_value!' => '']]);
        $this->end_controls_section();
        $this->start_controls_section('section_options', ['label' => esc_html__('Options', 'dynamic-content-for-elementor'), 'condition' => ['type' => ['line', 'bar', 'radar']]]);
        $this->add_control('begin_at_zero', ['label' => esc_html__('Begin at Zero', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'frontend_available' => \true, 'default' => 'yes', 'condition' => ['type' => ['line', 'bar']]]);
        $this->add_control('grace', ['label' => esc_html__('Grace', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'frontend_available' => \true, 'description' => esc_html__('Percentage (string ending with %) or amount (number) for added room in the scale range above and below data', 'dynamic-content-for-elementor'), 'condition' => ['begin_at_zero' => '']]);
        $this->add_control('stepsize', ['label' => esc_html__('Step Size', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'frontend_available' => \true, 'default' => esc_html__('0.5', 'dynamic-content-for-elementor'), 'description' => esc_html__('Select the Step Size for y axis', 'dynamic-content-for-elementor'), 'condition' => ['input' => 'simple', 'type' => ['line', 'bar', 'radar']]]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_legend', ['label' => esc_html__('Legend', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['type!' => ['pie', 'doughnut'], 'show_legend!' => '']]);
        $this->add_control('legend_position', ['label' => esc_html__('Position', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['top' => esc_html__('Top', 'dynamic-content-for-elementor'), 'left' => esc_html__('Left', 'dynamic-content-for-elementor'), 'bottom' => esc_html__('Bottom', 'dynamic-content-for-elementor'), 'right' => esc_html__('Right', 'dynamic-content-for-elementor')], 'default' => 'top', 'frontend_available' => \true]);
        $this->add_control('legend_align', ['label' => esc_html__('Horizontal Alignment', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT, 'options' => ['start' => esc_html__('Start', 'dynamic-content-for-elementor'), 'center' => esc_html__('Center', 'dynamic-content-for-elementor'), 'end' => esc_html__('End', 'dynamic-content-for-elementor')], 'default' => 'center', 'frontend_available' => \true]);
        $this->add_control('legend_color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'frontend_available' => \true]);
        $this->end_controls_section();
        $this->start_controls_section('section_style_axes', ['label' => esc_html__('Axes', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE, 'condition' => ['type' => ['bar', 'line']]]);
        $this->add_control('axis_x_heading', ['label' => esc_html__('X - Axis', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING]);
        $this->add_control('axis_x_grid_color', ['label' => esc_html__('Grid Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'frontend_available' => \true]);
        $this->add_control('axis_x_labels_color', ['label' => esc_html__('Labels Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'frontend_available' => \true]);
        $this->add_control('axis_y_heading', ['label' => esc_html__('Y - Axis', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::HEADING]);
        $this->add_control('axis_y_grid_color', ['label' => esc_html__('Grid Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'frontend_available' => \true]);
        $this->add_control('axis_y_labels_color', ['label' => esc_html__('Labels Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'frontend_available' => \true]);
        $this->end_controls_section();
        $this->start_controls_section('section_toggle_style_content', ['label' => esc_html__('Content', 'dynamic-content-for-elementor'), 'tab' => Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('height', ['label' => esc_html__('Height', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SLIDER, 'render_type' => 'template', 'range' => ['px' => ['min' => 200, 'max' => 1440], 'vh' => ['min' => 10, 'max' => 100], '%' => ['min' => 10, 'max' => 100]], 'default' => ['size' => 400], 'size_units' => ['px', '%', 'vh'], 'selectors' => ['{{WRAPPER}} .chart-container' => 'height: {{SIZE}}{{UNIT}};']]);
        $this->add_control('background_random_colors', ['label' => esc_html__('Random Colors for Background and Border', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'frontend_available' => \true, 'default' => 'yes']);
        $colors = new \Elementor\Repeater();
        $colors->add_control('color', ['label' => esc_html__('Color', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::COLOR, 'frontend_available' => \true, 'default' => '#E52600']);
        $this->add_control('background_data', ['label' => esc_html__('Colors', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::REPEATER, 'title_field' => '{{{ color }}}', 'fields' => $colors->get_controls(), 'frontend_available' => \true, 'condition' => ['background_random_colors' => '']]);
        $this->add_control('border_width_data', ['label' => esc_html__('Border Width', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'frontend_available' => \true, 'default' => 1, 'min' => 0]);
        $this->end_controls_section();
    }
    public function safe_render()
    {
        $settings = $this->get_settings_for_display();
        if (empty($settings)) {
            return;
        }
        if ('csv' === $settings['input']) {
            if ('url' === $settings['csv_from'] && empty($settings['csv_url'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('Please insert the CSV Url', 'dynamic-content-for-elementor'));
                }
                return;
            }
            if ('textarea' === $settings['csv_from'] && empty($settings['csv_textarea'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('Please insert the CSV values', 'dynamic-content-for-elementor'));
                }
                return;
            }
            if (empty($settings['csv_separator'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('Please insert the CSV separator', 'dynamic-content-for-elementor'));
                }
                return;
            }
            if (!empty($settings['csv_limit']) && empty($settings['csv_limit_records'])) {
                if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                    Helper::notice(\false, esc_html__('You have chosen to limit the records of the CSV. Please insert the CSV limit records', 'dynamic-content-for-elementor'));
                }
                return;
            }
            // CSV content
            if ('url' === $settings['csv_from']) {
                $csv = wp_remote_retrieve_body(wp_remote_get($settings['csv_url']));
            } elseif ('textarea' === $settings['csv_from']) {
                $csv = $settings['csv_textarea'];
            }
            if (!isset($csv)) {
                return;
            }
            $records = \explode("\n", $csv);
            // Remove the first row if the CSV has header and set an array $header
            if (!empty($settings['csv_has_header'])) {
                $header = \array_shift($records);
            }
            $records_separated = [];
            $i = 1;
            foreach ($records as $record) {
                if (!empty($settings['csv_limit']) && $i > $settings['csv_limit_records']) {
                    break;
                }
                if ('' === $record) {
                    continue;
                }
                $records_separated[] = \str_getcsv($record, $settings['csv_separator']);
                ++$i;
            }
            // Array with labels
            $labels = \array_column($records_separated, $settings['csv_index_labels'] - 1);
            // Array with data
            $data = \array_column($records_separated, $settings['csv_index_data'] - 1);
            // Labels Manipulation
            if (!empty($settings['labels_manipulation'])) {
                $manipulation = ['unix_to_date' => function ($value) {
                    return \gmdate(get_option('date_format'), $value);
                }, 'unix_to_datetime' => function ($value) {
                    return \gmdate(get_option('date_format') . ' ' . get_option('time_format'), $value);
                }, 'unix_to_time' => function ($value) {
                    return \gmdate(get_option('time_format'), $value);
                }, 'uppercase' => 'mb_strtoupper', 'lowercase' => 'mb_strtolower'];
                $labels = \array_map($manipulation[$settings['labels_manipulation_function']], $labels);
            }
            $data = \array_map('floatval', $data);
            // Set labels on a data attribute
            $this->add_render_attribute('canvas', 'data-chart-labels', wp_json_encode($labels));
            // Set data on a data attribute
            $this->add_render_attribute('canvas', 'data-chart-data', wp_json_encode($data));
        }
        $this->add_render_attribute('wrapper', 'class', 'chart-container');
        $this->add_render_attribute('wrapper', 'style', 'position: relative;');
        $this->add_render_attribute('canvas', 'id', 'myChart');
        ?>

		<div <?php 
        echo $this->get_render_attribute_string('wrapper');
        ?>>
			<canvas <?php 
        echo $this->get_render_attribute_string('canvas');
        ?>></canvas>
		</div>
		<?php 
    }
}
