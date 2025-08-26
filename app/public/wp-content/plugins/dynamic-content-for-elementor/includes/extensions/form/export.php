<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions;

use Elementor\Controls_Manager;
use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Tokens;
use DynamicContentForElementor\Plugin;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class Export extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
    public $has_action = \true;
    public function run_once()
    {
        $save_guard = \DynamicContentForElementor\Plugin::instance()->save_guard;
        $save_guard->register_unsafe_control('form', 'dce_form_export_url');
        $save_guard->register_unsafe_control('form', 'dce_form_export_port');
        $save_guard->register_unsafe_control('form', 'dce_form_export_method');
        $save_guard->register_unsafe_control('form', 'dce_form_export_ssl');
        $save_guard->register_unsafe_control('form', 'dce_form_export_empty');
        $save_guard->register_unsafe_control('form', 'dce_form_export_json');
        $save_guard->register_unsafe_control('form', 'dce_form_export_fields::dce_form_export_field_key');
        $save_guard->register_unsafe_control('form', 'dce_form_export_fields::dce_form_export_field_value');
        $save_guard->register_unsafe_control('form', 'dce_form_export_headers::dce_form_export_header_key');
        $save_guard->register_unsafe_control('form', 'dce_form_export_headers::dce_form_export_header_value');
        $save_guard->register_unsafe_control('form', 'dce_form_export_timeout');
        $save_guard->register_unsafe_control('form', 'dce_form_pdf_log');
        $save_guard->register_unsafe_control('form', 'dce_form_pdf_log_path');
        $save_guard->register_unsafe_control('form', 'dce_form_pdf_error');
    }
    /**
     * Get Name
     *
     * Return the action name
     *
     * @access public
     * @return string
     */
    public function get_name()
    {
        return 'dce_form_export';
    }
    public function get_script_depends()
    {
        return [];
    }
    public function get_style_depends()
    {
        return [];
    }
    /**
     * Get Label
     *
     * Returns the action label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return esc_html__('Export', 'dynamic-content-for-elementor');
    }
    /**
     * Register Settings Section
     *
     * Registers the Action controls
     *
     * @access public
     * @param \Elementor\Widget_Base $widget
     */
    public function register_settings_section($widget)
    {
        $widget->start_controls_section('section_dce_form_export', ['label' => Helper::dce_logo() . $this->get_label(), 'condition' => ['submit_actions' => $this->get_name()]]);
        if (!\DynamicContentForElementor\Helper::can_register_unsafe_controls()) {
            $widget->add_control('admin_notice', ['name' => 'admin_notice', 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('You will need administrator capabilities to edit these settings.', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning']);
            $widget->end_controls_section();
            return;
        }
        Plugin::instance()->text_templates->maybe_add_notice($widget, 'export');
        $widget->add_control('dce_form_export_url', ['label' => esc_html__('Endpoint URL', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'https://www.external.ext/save_data.php', 'label_block' => \true]);
        $widget->add_control('dce_form_export_port', ['label' => esc_html__('Port', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::NUMBER, 'placeholder' => '80']);
        $widget->add_control('dce_form_export_method', ['label' => esc_html__('Method', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::SELECT, 'options' => ['get' => 'GET', 'post' => 'POST', 'head' => 'HEAD'], 'default' => 'get', 'toggle' => \false, 'label_block' => 'true']);
        $widget->add_control('dce_form_export_ssl', ['label' => esc_html__('Enable SSL Certificate verify', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $widget->add_control('dce_form_export_empty', ['label' => esc_html__('Ignore fields with empty value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes']);
        $widget->add_control('dce_form_export_json', ['label' => esc_html__('Encode Post Data in JSON', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'condition' => ['dce_form_export_method' => 'post']]);
        $repeater_fields = new \Elementor\Repeater();
        $repeater_fields->add_control('dce_form_export_field_key', ['label' => esc_html__('Field Key', 'dynamic-content-for-elementor'), 'description' => esc_html__('It\'s the key of the parameter in the request', 'dynamic-content-for-elementor') . '<br>?<b>field_key</b>=FieldValue&<b>page</b>=2&<b>txt</b>=Test<br>', 'type' => Controls_Manager::TEXT]);
        $repeater_fields->add_control('dce_form_export_field_value', ['label' => esc_html__('Field Value', 'dynamic-content-for-elementor'), 'description' => esc_html__('It\'s the value of the parameter in the request', 'dynamic-content-for-elementor') . '<br>?field_key=<b>FieldValue</b>&page=<b>2</b>&txt=<b>Test</b><br>', 'type' => Controls_Manager::TEXT]);
        $widget->add_control('dce_form_export_admin_notice', ['name' => 'admin_notice', 'type' => Controls_Manager::RAW_HTML, 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning', 'raw' => esc_html__("If you don't set at least one list argument nothing will be exported", 'dynamic-content-for-elementor')]);
        $widget->add_control('dce_form_export_fields', ['label' => esc_html__('Exported Arguments list', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater_fields->get_controls(), 'title_field' => '{{{ dce_form_export_field_key }}} = {{{ dce_form_export_field_value }}}', 'prevent_empty' => \false]);
        $repeater_headers = new \Elementor\Repeater();
        $repeater_headers->add_control('dce_form_export_header_key', ['label' => esc_html__('Header Key', 'dynamic-content-for-elementor'), 'placeholder' => 'Content-Type', 'type' => Controls_Manager::TEXT]);
        $repeater_headers->add_control('dce_form_export_header_value', ['label' => esc_html__('Header Value', 'dynamic-content-for-elementor'), 'placeholder' => 'application/json', 'type' => Controls_Manager::TEXT]);
        $widget->add_control('dce_form_export_headers', ['label' => esc_html__('Add Headers', 'dynamic-content-for-elementor'), 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater_headers->get_controls(), 'title_field' => '{{{ dce_form_export_header_key }}}: {{{ dce_form_export_header_value }}}', 'default' => [['dce_form_export_header_key' => 'Connection', 'dce_form_export_header_value' => 'keep-alive']], 'prevent_empty' => \false]);
        $widget->add_control('dce_form_export_timeout', ['label' => esc_html__('Request Timeout in seconds', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::NUMBER, 'default' => '']);
        $widget->add_control('dce_form_pdf_log', ['label' => esc_html__('Enable log', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('Create a log for Export result', 'dynamic-content-for-elementor'), 'default' => 'yes']);
        $widget->add_control('dce_form_pdf_log_path', ['label' => esc_html__('Log Path', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'default' => \DynamicContentForElementor\Plugin::instance()->text_templates->get_default_value(['dynamic-shortcodes' => 'elementor/export/log_' . $widget->get_id() . "_{date:now @format='Ymd'}.txt", 'tokens' => 'elementor/export/log_' . $widget->get_id() . '_[date|Ymd].txt']), 'label_block' => \true, 'condition' => ['dce_form_pdf_log!' => '']]);
        $widget->add_control('dce_form_pdf_error', ['label' => esc_html__('Show error on failure', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'description' => esc_html__('If the remote request fails (not response code 200) then an error is going to be displayed', 'dynamic-content-for-elementor'), 'default' => 'yes']);
        $widget->end_controls_section();
    }
    /**
     * Run
     *
     * Runs the action after submit
     *
     * @access public
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
     */
    public function run($record, $ajax_handler)
    {
        $fields = Helper::get_form_data($record);
        $settings = $record->get('form_settings');
        $simple_keys_to_expand = ['dce_form_export_url', 'dce_form_export_port', 'dce_form_export_timeout', 'dce_form_pdf_log', 'dce_form_pdf_log_path'];
        foreach ($simple_keys_to_expand as $key) {
            if (isset($settings[$key])) {
                $settings[$key] = Plugin::instance()->text_templates->expand_shortcodes_or_callback($settings[$key], ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                    return Helper::get_dynamic_value($str, $fields);
                });
            }
        }
        $repeater_keys_to_expand = ['dce_form_export_fields', 'dce_form_export_headers'];
        foreach ($repeater_keys_to_expand as $key) {
            foreach ($settings[$key] as $i => $s) {
                $settings[$key][$i] = \array_map(function ($item) use($fields, $record) {
                    return Plugin::instance()->text_templates->expand_shortcodes_or_callback($item, ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                        return Helper::get_dynamic_value($str, $fields);
                    });
                }, $s);
            }
        }
        $this->export($fields, $settings, $ajax_handler, $record);
    }
    protected function export($fields, $settings, $ajax_handler, $record)
    {
        $export_data = [];
        if (!empty($settings['dce_form_export_fields'])) {
            foreach ($settings['dce_form_export_fields'] as $akey => $adata) {
                $pvalue = $adata['dce_form_export_field_value'];
                if ($pvalue == '' && $settings['dce_form_export_empty']) {
                    continue;
                }
                if (\substr(\trim($pvalue), 0, 1) == '{' && \substr(\trim($pvalue), -1, 1) == '}' || \substr(\trim($pvalue), 0, 1) == '[' && \substr(\trim($pvalue), -1, 1) == ']') {
                    $pvalue = \json_decode($pvalue);
                }
                $export_data[$adata['dce_form_export_field_key']] = $pvalue;
            }
        }
        $args = [];
        $exp_url = $settings['dce_form_export_url'];
        if ($exp_url) {
            $pieces = \explode('/', $exp_url);
            if (\count($pieces) >= 3) {
                if ($settings['dce_form_export_port']) {
                    $pieces[2] = $pieces[2] . ':' . $settings['dce_form_export_port'];
                    $exp_url = \implode('/', $pieces);
                }
                if ($settings['dce_form_export_method'] == 'get') {
                    if (!empty($export_data)) {
                        foreach ($export_data as $akey => $avalue) {
                            $exp_url = add_query_arg($akey, $avalue, $exp_url);
                        }
                    }
                } elseif ($settings['dce_form_export_json']) {
                    $args['body'] = wp_json_encode($export_data);
                    $args['headers'] = ['Content-Type' => 'application/json; charset=utf-8'];
                    $args['data_format'] = 'body';
                } else {
                    $args['body'] = $export_data;
                }
                $timeout = $settings['dce_form_export_timeout'];
                if (\is_numeric($timeout) && $timeout > 0) {
                    $args['timeout'] = (int) $timeout;
                }
                if (!empty($settings['dce_form_export_headers'])) {
                    foreach ($settings['dce_form_export_headers'] as $akey => $adata) {
                        $pvalue = Plugin::instance()->text_templates->expand_shortcodes_or_callback($adata['dce_form_export_header_value'], ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                            return Helper::get_dynamic_value($str, $fields);
                        });
                        $args['headers'][$adata['dce_form_export_header_key']] = $pvalue;
                    }
                }
                if (!$settings['dce_form_export_ssl']) {
                    add_filter('https_ssl_verify', '__return_false');
                }
                $args['follow_redirects'] = \true;
                $req = 'wp_remote_' . $settings['dce_form_export_method'];
                // Send the request
                switch ($settings['dce_form_export_method']) {
                    case 'get':
                        $ret = wp_remote_get($exp_url, $args);
                        break;
                    case 'post':
                        $ret = wp_remote_post($exp_url, $args);
                        break;
                    case 'head':
                        $ret = wp_remote_head($exp_url, $args);
                        break;
                    default:
                        // this should never happen:
                        $ajax_handler->add_admin_error_message('DCE Error: AHPH6P');
                        return;
                }
                if (!is_wp_error($ret)) {
                    $log = 'Form Export: OK';
                } else {
                    $ret_code = wp_remote_retrieve_response_code($ret);
                    $log = 'Form Export: ERROR ' . $ret_code;
                    if ($settings['dce_form_pdf_error']) {
                        $ajax_handler->add_admin_error_message($ret->get_error_message());
                        $ajax_handler->add_error_message(\ElementorPro\Modules\Forms\Classes\Ajax_Handler::get_default_message(\ElementorPro\Modules\Forms\Classes\Ajax_Handler::SERVER_ERROR, $settings));
                    }
                }
                if ($settings['dce_form_pdf_log']) {
                    $ret_body = $ret;
                    $log = $log . ' - ' . $req . \PHP_EOL;
                    $log .= 'request_url: ' . $exp_url . \PHP_EOL;
                    if ($settings['dce_form_export_method'] == 'post') {
                        $log .= 'request_data: ' . \var_export($args['body'] ?? '', \true) . \PHP_EOL;
                    }
                    $log .= 'return_body: ' . \var_export($ret_body, \true);
                    $log = \PHP_EOL . '[' . \date('Y-m-d H:i:s') . '] ' . $log;
                    $upload = wp_upload_dir();
                    $upload_dir = $upload['basedir'];
                    $log_dir = $upload_dir . '/' . Plugin::instance()->text_templates->expand_shortcodes_or_callback(\dirname($settings['dce_form_pdf_log_path']), ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                        return Helper::get_dynamic_value($str, $fields);
                    });
                    $log_filename = Plugin::instance()->text_templates->expand_shortcodes_or_callback(\basename($settings['dce_form_pdf_log_path']), ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                        return Helper::get_dynamic_value($str, $fields);
                    });
                    if (!\is_dir($log_dir) && !\mkdir($log_dir, 0755, \true) || !\file_put_contents($log_dir . '/' . $log_filename, $log, \FILE_APPEND)) {
                        $ajax_handler->add_error_message('Error on LOG creation in ' . $log_dir . '/' . $log_filename);
                    }
                }
            }
        }
    }
    public function on_export($element)
    {
        unset($element['settings']['dce_form_export_url'], $element['settings']['dce_form_export_port'], $element['settings']['dce_form_export_headers'], $element['settings']['dce_form_pdf_log_path']);
        return $element;
    }
}
