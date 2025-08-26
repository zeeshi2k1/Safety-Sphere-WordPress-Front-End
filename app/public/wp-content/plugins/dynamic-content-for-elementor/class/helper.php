<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
#phpcs:ignoreFile
namespace DynamicContentForElementor;

use Elementor\Icons_Manager;
if (!\defined('ABSPATH')) {
    exit;
}
class Helper
{
    use \DynamicContentForElementor\Plugins;
    use \DynamicContentForElementor\Filesystem;
    use \DynamicContentForElementor\Wp;
    use \DynamicContentForElementor\Meta;
    use \DynamicContentForElementor\Elementor;
    use \DynamicContentForElementor\Form;
    use \DynamicContentForElementor\Strings;
    use \DynamicContentForElementor\Image;
    use \DynamicContentForElementor\Navigation;
    use \DynamicContentForElementor\Notices;
    use \DynamicContentForElementor\Options;
    use \DynamicContentForElementor\Date;
    use \DynamicContentForElementor\Pagination;
    use \DynamicContentForElementor\I18n;
    use \DynamicContentForElementor\Validation;
    /**
     * @var array<string>
     */
    const ALLOWED_HTML_WRAPPER_TAGS = ['article', 'aside', 'div', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'main', 'nav', 'p', 'section', 'span', 'code'];
    /**
     * @var array<string>
     */
    const NOT_ALLOWED_USER_FIELDS = ['user_login', 'login', 'user_pass', 'pass', 'user_email', 'email', 'user_registered', 'registered', 'user_activation_key', 'activation_key', 'user_status', 'status'];
    public static function can_register_unsafe_controls()
    {
        if (current_user_can('administrator')) {
            return \true;
        }
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            return \false;
        }
        if (($_REQUEST['action'] ?? '') === 'elementor_ajax') {
            return \false;
        }
        return \true;
    }
    public static function update_elementor_control($widget, $control_name, $callback)
    {
        $elementor = \ElementorPro\Plugin::elementor();
        $control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), $control_name);
        if (is_wp_error($control_data)) {
            return;
        }
        $control_data = $callback($control_data);
        $widget->update_control($control_name, $control_data);
    }
    /** Make sure the given dir is created and has protection files. */
    public static function ensure_dir($path)
    {
        if (\file_exists($path . '/index.php')) {
            return $path;
        }
        wp_mkdir_p($path);
        $files = [['file' => 'index.php', 'content' => ['<?php', '// Silence is golden.']], ['file' => '.htaccess', 'content' => ['Options -Indexes', '<ifModule mod_headers.c>', '	<Files *.*>', '       Header set Content-Disposition attachment', '	</Files>', '</IfModule>']]];
        foreach ($files as $file) {
            if (!\file_exists(trailingslashit($path) . $file['file'])) {
                $content = \implode(\PHP_EOL, $file['content']);
                @\file_put_contents(trailingslashit($path) . $file['file'], $content);
            }
        }
    }
    /**
     * @param array<string,mixed> $settings
     * @param string $key
     * @param string $old_default
     * @return string
     */
    public static function get_migrated_icon($settings, $key, $old_default)
    {
        $old_key = $key;
        $new_key = 'selected_' . $key;
        $migration_allowed = Icons_Manager::is_migration_allowed();
        // old default
        if (!isset($settings[$old_key]) && !$migration_allowed) {
            $settings[$old_key] = $old_default;
        }
        $migrated = isset($settings['__fa4_migrated'][$new_key]);
        $is_new = empty($settings[$old_key]) && $migration_allowed;
        if ($migrated || $is_new) {
            \ob_start();
            Icons_Manager::render_icon($settings[$new_key] ?? '', ['aria-hidden' => 'true']);
            $s = \ob_get_clean();
            return $s ? $s : '';
        } else {
            $class = $settings[$old_key];
            return "<i class='{$class}'></i>";
        }
    }
    /**
     * Is Condition Satisfied
     *
     * @param mixed $field
     * @param string $status
     * @param mixed $value
     * @return boolean
     */
    public static function is_condition_satisfied($field, $status, $value)
    {
        switch ($status) {
            case 'isset':
                if (!empty($field)) {
                    return \true;
                }
                break;
            case 'not':
                if (empty($field)) {
                    return \true;
                }
                break;
            case 'lt':
                if (\is_numeric($field)) {
                    $field = \floatval($field);
                }
                if (\is_numeric($value)) {
                    $value = \floatval($value);
                }
                if (\is_array($field) && \count($field) < $value) {
                    return \true;
                }
                return $field < $value;
            case 'gt':
                if (\is_numeric($field)) {
                    $field = \floatval($field);
                }
                if (\is_numeric($value)) {
                    $value = \floatval($value);
                }
                if (\is_array($field) && \count($field) > $value) {
                    return \true;
                }
                return $field > $value;
            case 'contain':
                if (\is_array($field) && \in_array($value, $field)) {
                    return \true;
                }
                if (\is_string($field) && $value !== '' && \strpos($field, $value) !== \false) {
                    return \true;
                }
                break;
            case 'not_contain':
                if (empty($field)) {
                    return \true;
                }
                if (\is_array($field) && !\in_array($value, $field)) {
                    return \true;
                }
                if (\is_string($field) && $value !== '' && \strpos($field, $value) === \false) {
                    return \true;
                }
                break;
            case 'in_array':
                if (!\is_array($value)) {
                    $value = \DynamicContentForElementor\Helper::to_string($value);
                    $value = \DynamicContentForElementor\Helper::str_to_array(',', $value);
                }
                if (\in_array($field, $value)) {
                    return \true;
                }
                break;
            case 'not_value':
                return $field != $value;
            case 'value':
                return $field == $value;
        }
        return \false;
    }
}
