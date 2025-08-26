<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions;

use Elementor\Controls_Manager;
use ElementorPro\Modules\Forms\Fields\Upload;
use ElementorPro\Modules\Forms\Widgets\Form;
use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Tokens;
use DynamicContentForElementor\Plugin;
use DynamicOOOS\TelegramBot\Api\BotApi;
if (!\defined('ABSPATH')) {
    exit;
    // Exit if accessed directly
}
class Telegram extends \ElementorPro\Modules\Forms\Classes\Action_Base
{
    public $has_action = \true;
    public function get_name()
    {
        return 'dce_form_telegram';
    }
    public function get_label()
    {
        return esc_html__('Telegram', 'dynamic-content-for-elementor');
    }
    public function get_script_depends()
    {
        return [];
    }
    public function get_style_depends()
    {
        return [];
    }
    public function run_once()
    {
        $save_guard = \DynamicContentForElementor\Plugin::instance()->save_guard;
        $telegram_repeater_settings = ['dce_form_telegram_repeater::dce_form_telegram_enable', 'dce_form_telegram_repeater::dce_form_telegram_condition_field', 'dce_form_telegram_repeater::dce_form_telegram_condition_status', 'dce_form_telegram_repeater::dce_form_telegram_condition_value', 'dce_form_telegram_repeater::dce_form_telegram_token', 'dce_form_telegram_repeater::dce_form_telegram_chat_id', 'dce_form_telegram_repeater::dce_form_telegram_content'];
        foreach ($telegram_repeater_settings as $setting) {
            $save_guard->register_unsafe_control('form', $setting);
        }
    }
    /**
     * @param Form $widget
     * @return void
     */
    public function register_settings_section($widget)
    {
        $widget->start_controls_section('section_dce_form_telegram', ['label' => Helper::dce_logo() . $this->get_label(), 'condition' => ['submit_actions' => $this->get_name()]]);
        if (!\DynamicContentForElementor\Helper::can_register_unsafe_controls()) {
            $widget->add_control('admin_notice', ['name' => 'admin_notice', 'type' => Controls_Manager::RAW_HTML, 'raw' => '<div class="elementor-panel-alert elementor-panel-alert-warning">' . esc_html__('You will need administrator capabilities to edit these settings.', 'dynamic-content-for-elementor') . '</div>']);
            $widget->end_controls_section();
            return;
        }
        Plugin::instance()->text_templates->maybe_add_notice($widget, 'telegram');
        $repeater_fields = new \Elementor\Repeater();
        $repeater_fields->add_control('dce_form_telegram_enable', ['label' => esc_html__('Enable Message', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER, 'default' => 'yes', 'description' => esc_html__('You can temporary disable and reactivate it next time without deleting settings', 'dynamic-content-for-elementor'), 'separator' => 'after']);
        $repeater_fields->add_control('dce_form_telegram_condition_field', ['label' => esc_html__('Condition', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'description' => esc_html__('Type here the form field ID to check, or leave it empty to always execute this action', 'dynamic-content-for-elementor')]);
        $repeater_fields->add_control('dce_form_telegram_condition_status', ['label' => esc_html__('Condition Status', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::CHOOSE, 'options' => ['empty' => ['title' => esc_html__('Empty', 'dynamic-content-for-elementor'), 'icon' => 'eicon-circle-o'], 'valued' => ['title' => esc_html__('Valorized with any value', 'dynamic-content-for-elementor'), 'icon' => 'eicon-dot-circle-o'], 'lt' => ['title' => esc_html__('Less than', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-angle-left'], 'gt' => ['title' => esc_html__('Greater than', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-angle-right'], 'equal' => ['title' => esc_html__('Equal to', 'dynamic-content-for-elementor'), 'icon' => 'fa fa-circle'], 'contain' => ['title' => esc_html__('Contains', 'dynamic-content-for-elementor'), 'icon' => 'eicon-check']], 'default' => 'valued', 'toggle' => \false, 'label_block' => \true, 'condition' => ['dce_form_telegram_condition_field!' => '']]);
        $repeater_fields->add_control('dce_form_telegram_condition_value', ['label' => esc_html__('Condition Value', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'description' => esc_html__('A value to compare the value of the field', 'dynamic-content-for-elementor'), 'condition' => ['dce_form_telegram_condition_field!' => '', 'dce_form_telegram_condition_status' => ['lt', 'gt', 'equal', 'contain']]]);
        $repeater_fields->add_control('dce_form_telegram_token', ['label' => esc_html__('Telegram authorization token', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'render_type' => 'none', 'separator' => 'before']);
        $repeater_fields->add_control('dce_form_telegram_chat_id', ['label' => esc_html__('Chat ID', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'render_type' => 'none']);
        $repeater_fields->add_control('dce_form_telegram_content', ['label' => esc_html__('Message', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXTAREA, 'default' => \DynamicContentForElementor\Plugin::instance()->text_templates->get_default_value(['dynamic-shortcodes' => esc_html__('New message from', 'dynamic-content-for-elementor') . ' {form:name}:{form:message}', 'tokens' => esc_html__('New message from', 'dynamic-content-for-elementor') . ' [form:name]:[form:message]']), 'label_block' => \true, 'render_type' => 'none']);
        $widget->add_control('dce_form_telegram_repeater', ['label' => esc_html__('Messages', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::REPEATER, 'title_field' => '{{{ dce_form_telegram_content }}}', 'fields' => $repeater_fields->get_controls()]);
        $widget->end_controls_section();
    }
    public function run($record, $ajax_handler)
    {
        $fields = Helper::get_form_data($record);
        $post_id = \intval($_POST['post_id']);
        $form_id = sanitize_text_field($_POST['form_id']);
        $document = \Elementor\Plugin::$instance->documents->get($post_id);
        if ($document) {
            $form = \ElementorPro\Modules\Forms\Module::find_element_recursive($document->get_elements_data(), $form_id);
            $widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance($form);
            $settings = $widget->get_settings_for_display();
        } else {
            $settings = $record->get('form_settings');
        }
        if (isset($settings['dce_form_telegram_repeater'])) {
            $message = $settings['dce_form_telegram_repeater']['dce_form_telegram_content'];
            unset($settings['dce_form_telegram_repeater']['dce_form_telegram_content']);
            $settings['dce_form_telegram_repeater']['dce_form_telegram_content'] = Plugin::instance()->text_templates->expand_shortcodes_or_callback($message, ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                return Helper::get_dynamic_value($str, $fields);
            });
        }
        $this->dce_elementor_form_telegram($fields, $settings, $ajax_handler, $record);
    }
    public function on_export($element)
    {
        unset($element['settings']['dce_form_telegram_token']);
        return $element;
    }
    function dce_elementor_form_telegram($fields, $settings = null, $ajax_handler = null, $record = null)
    {
        foreach ($settings['dce_form_telegram_repeater'] as $mkey => $telegram_message) {
            if ($telegram_message['dce_form_telegram_enable'] && $telegram_message['dce_form_telegram_token'] && $telegram_message['dce_form_telegram_chat_id'] && $telegram_message['dce_form_telegram_content']) {
                $condition_satisfy = \true;
                if (!empty($telegram_message['dce_form_telegram_condition_field'])) {
                    $field_value = $fields[$telegram_message['dce_form_telegram_condition_field']];
                    switch ($telegram_message['dce_form_telegram_condition_status']) {
                        case 'empty':
                            if (!empty($field_value)) {
                                $condition_satisfy = \false;
                            }
                            break;
                        case 'valued':
                            if (empty($field_value)) {
                                $condition_satisfy = \false;
                            }
                            break;
                        case 'lt':
                            if (empty($field_value) || $field_value > $telegram_message['dce_form_telegram_condition_value']) {
                                $condition_satisfy = \false;
                            }
                            break;
                        case 'gt':
                            if (empty($field_value) || $field_value < $telegram_message['dce_form_telegram_condition_value']) {
                                $condition_satisfy = \false;
                            }
                            break;
                        case 'equal':
                            if ($field_value != $telegram_message['dce_form_telegram_condition_value']) {
                                $condition_satisfy = \false;
                            }
                        case 'contain':
                            $field_type = Helper::get_field_type($telegram_message['dce_form_telegram_condition_field'], $settings);
                            if ($field_type == 'checkbox') {
                                $field_value = Helper::str_to_array(', ', $field_value);
                            }
                            if (\is_array($fields[$telegram_message['dce_form_telegram_condition_field']])) {
                                if (!\in_array($telegram_message['dce_form_telegram_condition_value'], $field_value)) {
                                    $condition_satisfy = \false;
                                }
                            } elseif (\strpos($field_value, $telegram_message['dce_form_telegram_condition_value']) === \false) {
                                $condition_satisfy = \false;
                            }
                            break;
                    }
                }
                if ($condition_satisfy) {
                    $line_break = "\n";
                    $settings_raw = $record->get('form_settings');
                    $dce_form_telegram_content = $settings_raw['dce_form_telegram_repeater'][$mkey]['dce_form_telegram_content'];
                    $dce_form_telegram_content = $this->replace_content_shortcodes($dce_form_telegram_content, $record, $line_break);
                    $dce_form_telegram_content = Plugin::instance()->text_templates->expand_shortcodes_or_callback($dce_form_telegram_content, ['form-fields' => $record->get('fields')], function ($str) use($fields) {
                        return Helper::get_dynamic_value($str, $fields);
                    });
                    // replace single fields shortcode
                    $dce_form_telegram_content = Helper::replace_setting_shortcodes($dce_form_telegram_content, $fields);
                    // $notification = $settings_raw['dce_form_telegram_repeater'][ $mkey ]['dce_form_telegram_notification'];
                    $telegram = new BotApi($settings_raw['dce_form_telegram_repeater'][$mkey]['dce_form_telegram_token']);
                    $response = $telegram->sendMessage($settings_raw['dce_form_telegram_repeater'][$mkey]['dce_form_telegram_chat_id'], $dce_form_telegram_content);
                }
            }
        }
    }
    public function replace_content_shortcodes($email_content, $record, $line_break)
    {
        $all_fields_shortcode = '[all-fields]';
        $text = $this->get_shortcode_value($all_fields_shortcode, $email_content, $record, $line_break);
        $email_content = \str_replace($all_fields_shortcode, $text, $email_content);
        $all_valued_fields_shortcode = '[all-fields|!empty]';
        $text = $this->get_shortcode_value($all_valued_fields_shortcode, $email_content, $record, $line_break, \false);
        $email_content = \str_replace($all_fields_shortcode, $text, $email_content);
        return $email_content;
    }
    public function get_shortcode_value($shortcode, $email_content, $record, $line_break, $show_empty = \true)
    {
        $text = '';
        if (\false !== \strpos($email_content, $shortcode)) {
            foreach ($record->get('fields') as $field) {
                $formatted = '';
                if (!empty($field['title'])) {
                    $formatted = \sprintf('%s: %s', $field['title'], $field['value']);
                } elseif (!empty($field['value'])) {
                    $formatted = \sprintf('%s', $field['value']);
                }
                if ('textarea' === $field['type'] && '<br>' === $line_break) {
                    $formatted = \str_replace(["\r\n", "\n", "\r"], '<br />', $formatted);
                }
                if (!$show_empty && empty($field['value'])) {
                    continue;
                }
                $text .= $formatted . $line_break;
            }
        }
        return $text;
    }
}
