<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\Extensions\DynamicVisibility\Triggers;

use Elementor\Controls_Manager;
class DateTime extends \DynamicContentForElementor\Extensions\DynamicVisibility\Triggers\Base
{
    /**
     * @param \Elementor\Element_Base $element
     * @return void
     */
    public function register_controls($element)
    {
        $element->add_control('dce_visibility_datetime_important_note', ['type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__('The time will be interpreted in the Time Zone as configured in the WordPress settings.', 'dynamic-content-for-elementor'), 'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning']);
        $element->add_control('dce_visibility_date_dynamic', ['label' => esc_html__('Use Dynamic Dates', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SWITCHER]);
        $element->add_control('dce_visibility_date_dynamic_from', ['label' => esc_html__('Date From', 'dynamic-content-for-elementor'), 'label_block' => \true, 'type' => Controls_Manager::TEXT, 'placeholder' => 'Y-m-d H:i:s', 'description' => esc_html__('If set the element will appear after this date', 'dynamic-content-for-elementor'), 'condition' => ['dce_visibility_date_dynamic!' => ''], 'dynamic' => ['active' => \true]]);
        $element->add_control('dce_visibility_date_dynamic_to', ['label' => esc_html__('Date To', 'dynamic-content-for-elementor'), 'label_block' => \true, 'type' => Controls_Manager::TEXT, 'placeholder' => 'Y-m-d H:i:s', 'description' => esc_html__('If set the element will be visible until this date', 'dynamic-content-for-elementor'), 'condition' => ['dce_visibility_date_dynamic!' => ''], 'dynamic' => ['active' => \true]]);
        $element->add_control('dce_visibility_date_from', ['label' => esc_html__('Date From', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DATE_TIME, 'description' => esc_html__('If set the element will appear after this date', 'dynamic-content-for-elementor'), 'condition' => ['dce_visibility_date_dynamic' => '']]);
        $element->add_control('dce_visibility_date_to', ['label' => esc_html__('Date To', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::DATE_TIME, 'description' => esc_html__('If set the element will be visible until this date', 'dynamic-content-for-elementor'), 'condition' => ['dce_visibility_date_dynamic' => '']]);
        $element->add_control('dce_visibility_period_from', ['label' => esc_html__('Period From', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'description' => esc_html__('If set the element will appear after this period', 'dynamic-content-for-elementor'), 'placeholder' => 'mm/dd', 'separator' => 'before', 'dynamic' => ['active' => \true]]);
        $element->add_control('dce_visibility_period_to', ['label' => esc_html__('Period To', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => 'mm/dd', 'description' => esc_html__('If set the element will be visible until this period', 'dynamic-content-for-elementor'), 'dynamic' => ['active' => \true]]);
        global $wp_locale;
        $week = [];
        for ($day_index = 0; $day_index <= 6; $day_index++) {
            $week[$day_index] = $wp_locale->get_weekday($day_index);
        }
        $element->add_control('dce_visibility_time_week', ['label' => esc_html__('Days of the week', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::SELECT2, 'options' => $week, 'multiple' => \true, 'separator' => 'before']);
        $element->add_control('dce_visibility_time_from', ['label' => esc_html__('Time From', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => 'H:m', 'description' => esc_html__('If set (in H:m format) the element will appear after this time.', 'dynamic-content-for-elementor'), 'separator' => 'before']);
        $element->add_control('dce_visibility_time_to', ['label' => esc_html__('Time To', 'dynamic-content-for-elementor'), 'type' => Controls_Manager::TEXT, 'placeholder' => 'H:m', 'description' => esc_html__('If set (in H:m format) the element will be visible until this time', 'dynamic-content-for-elementor')]);
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
        if ($settings['dce_visibility_date_dynamic']) {
            if ($settings['dce_visibility_date_dynamic_from'] && $settings['dce_visibility_date_dynamic_to']) {
                $triggers['date'] = esc_html__('Date Dynamic', 'dynamic-content-for-elementor');
                $triggers['dce_visibility_date_dynamic_from'] = esc_html__('Date Dynamic From', 'dynamic-content-for-elementor');
                $triggers['dce_visibility_date_dynamic_to'] = esc_html__('Date Dynamic To', 'dynamic-content-for-elementor');
                // between
                $dateTo = \strtotime($settings['dce_visibility_date_dynamic_to']);
                $dateFrom = \strtotime($settings['dce_visibility_date_dynamic_from']);
                ++$triggers_n;
                if (current_time('timestamp') >= $dateFrom && current_time('timestamp') <= $dateTo) {
                    $conditions['date'] = esc_html__('Date Dynamic', 'dynamic-content-for-elementor');
                }
            } else {
                if ($settings['dce_visibility_date_dynamic_from']) {
                    $triggers['dce_visibility_date_dynamic_from'] = esc_html__('Date Dynamic From', 'dynamic-content-for-elementor');
                    $dateFrom = \strtotime($settings['dce_visibility_date_dynamic_from']);
                    ++$triggers_n;
                    if (current_time('timestamp') >= $dateFrom) {
                        $conditions['dce_visibility_date_dynamic_from'] = esc_html__('Date Dynamic From', 'dynamic-content-for-elementor');
                    }
                }
                if ($settings['dce_visibility_date_dynamic_to']) {
                    $triggers['dce_visibility_date_dynamic_to'] = esc_html__('Date Dynamic To', 'dynamic-content-for-elementor');
                    $dateTo = \strtotime($settings['dce_visibility_date_dynamic_to']);
                    ++$triggers_n;
                    if (current_time('timestamp') <= $dateTo) {
                        $conditions['dce_visibility_date_dynamic_to'] = esc_html__('Date Dynamic To', 'dynamic-content-for-elementor');
                    }
                }
            }
        } elseif ($settings['dce_visibility_date_from'] && $settings['dce_visibility_date_to']) {
            $triggers['date'] = esc_html__('Date', 'dynamic-content-for-elementor');
            $triggers['dce_visibility_date_from'] = esc_html__('Date From', 'dynamic-content-for-elementor');
            $triggers['dce_visibility_date_to'] = esc_html__('Date To', 'dynamic-content-for-elementor');
            // between
            $dateTo = \strtotime($settings['dce_visibility_date_to']);
            $dateFrom = \strtotime($settings['dce_visibility_date_from']);
            ++$triggers_n;
            if (current_time('timestamp') >= $dateFrom && current_time('timestamp') <= $dateTo) {
                $conditions['date'] = esc_html__('Date', 'dynamic-content-for-elementor');
            }
        } else {
            if ($settings['dce_visibility_date_from']) {
                $triggers['dce_visibility_date_from'] = esc_html__('Date From', 'dynamic-content-for-elementor');
                $dateFrom = \strtotime($settings['dce_visibility_date_from']);
                ++$triggers_n;
                if (current_time('timestamp') >= $dateFrom) {
                    $conditions['dce_visibility_date_from'] = esc_html__('Date From', 'dynamic-content-for-elementor');
                }
            }
            if ($settings['dce_visibility_date_to']) {
                $triggers['dce_visibility_date_to'] = esc_html__('Date To', 'dynamic-content-for-elementor');
                $dateTo = \strtotime($settings['dce_visibility_date_to']);
                ++$triggers_n;
                if (current_time('timestamp') <= $dateTo) {
                    $conditions['dce_visibility_date_to'] = esc_html__('Date To', 'dynamic-content-for-elementor');
                }
            }
        }
        if ($settings['dce_visibility_period_from'] && $settings['dce_visibility_period_to']) {
            $triggers['period'] = esc_html__('Period', 'dynamic-content-for-elementor');
            $triggers['dce_visibility_period_from'] = esc_html__('Period From', 'dynamic-content-for-elementor');
            $triggers['dce_visibility_period_to'] = esc_html__('Period To', 'dynamic-content-for-elementor');
            ++$triggers_n;
            $period_from = \DateTime::createFromFormat('d/m H:i:s', $settings['dce_visibility_period_from'] . ' 00:00:00');
            $period_to = \DateTime::createFromFormat('d/m H:i:s', $settings['dce_visibility_period_to'] . ' 23:59:59');
            if (\false !== $period_from && \false !== $period_to && $period_from->getTimestamp() <= $period_to->getTimestamp()) {
                if (current_time('U') >= $period_from->getTimestamp() && current_time('U') <= $period_to->getTimestamp()) {
                    $conditions['period'] = esc_html__('Period', 'dynamic-content-for-elementor');
                }
            } elseif (\false !== $period_from && \false !== $period_to) {
                // Period From > Period To. For example between 20 Dec - 11 Jan
                if (current_time('U') >= $period_from->getTimestamp() || current_time('U') <= $period_to->getTimestamp()) {
                    $conditions['period'] = esc_html__('Period', 'dynamic-content-for-elementor');
                }
            }
        } else {
            if ($settings['dce_visibility_period_from']) {
                $triggers['dce_visibility_period_from'] = esc_html__('Period From', 'dynamic-content-for-elementor');
                ++$triggers_n;
                if (date_i18n('m/d') >= $settings['dce_visibility_period_from']) {
                    $conditions['dce_visibility_period_from'] = esc_html__('Period From', 'dynamic-content-for-elementor');
                }
            }
            if ($settings['dce_visibility_period_to']) {
                $triggers['dce_visibility_period_to'] = esc_html__('Period To', 'dynamic-content-for-elementor');
                ++$triggers_n;
                if (date_i18n('m/d') <= $settings['dce_visibility_period_to']) {
                    $conditions['dce_visibility_period_to'] = esc_html__('Period To', 'dynamic-content-for-elementor');
                }
            }
        }
        if (!empty($settings['dce_visibility_time_week'])) {
            $triggers['dce_visibility_time_week'] = esc_html__('Day of Week', 'dynamic-content-for-elementor');
            ++$triggers_n;
            if (\in_array(current_time('w'), $settings['dce_visibility_time_week'])) {
                $conditions['dce_visibility_time_week'] = esc_html__('Day of Week', 'dynamic-content-for-elementor');
            }
        }
        if ($settings['dce_visibility_time_from'] && $settings['dce_visibility_time_to']) {
            $triggers['time'] = esc_html__('Time', 'dynamic-content-for-elementor');
            $triggers['dce_visibility_time_from'] = esc_html__('Time From', 'dynamic-content-for-elementor');
            $triggers['dce_visibility_time_to'] = esc_html__('Time To', 'dynamic-content-for-elementor');
            $time_from = $settings['dce_visibility_time_from'];
            $time_to = $settings['dce_visibility_time_to'];
            ++$triggers_n;
            if ($time_from <= $time_to) {
                if (current_time('H:i') >= $time_from && current_time('H:i') <= $time_to) {
                    $conditions['time'] = esc_html__('Time', 'dynamic-content-for-elementor');
                }
            } else {
                // Time From > Time To. For example between 18:00 - 07:00
                if (current_time('H:i') >= $time_from || current_time('H:i') <= $time_to) {
                    $conditions['time'] = esc_html__('Time', 'dynamic-content-for-elementor');
                }
            }
        } else {
            if ($settings['dce_visibility_time_from']) {
                $triggers['dce_visibility_time_from'] = esc_html__('Time From', 'dynamic-content-for-elementor');
                $time_from = $settings['dce_visibility_time_from'];
                ++$triggers_n;
                if (current_time('H:i') >= $time_from) {
                    $conditions['dce_visibility_time_from'] = esc_html__('Time From', 'dynamic-content-for-elementor');
                }
            }
            if ($settings['dce_visibility_time_to']) {
                $triggers['dce_visibility_time_to'] = esc_html__('Time To', 'dynamic-content-for-elementor');
                $time_to = $settings['dce_visibility_time_to'] == '00:00' ? '24:00' : $settings['dce_visibility_time_to'];
                ++$triggers_n;
                if (current_time('H:i') <= $time_to) {
                    $conditions['dce_visibility_time_to'] = esc_html__('Time To', 'dynamic-content-for-elementor');
                }
            }
        }
    }
}
