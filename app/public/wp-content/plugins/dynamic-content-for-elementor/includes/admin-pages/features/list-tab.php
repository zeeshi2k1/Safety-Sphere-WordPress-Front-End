<?php

// SPDX-FileCopyrightText: 2018-2025 Ovation S.r.l. <help@dynamic.ooo>
// SPDX-License-Identifier: GPL-3.0-or-later
namespace DynamicContentForElementor\AdminPages\Features;

use DynamicContentForElementor\Helper;
use DynamicContentForElementor\Plugin;
abstract class ListTab
{
    private $name;
    private $label;
    // Contains all features for which this tab is responsible for:
    protected $features;
    public function __construct($name)
    {
        $this->name = $name;
        $this->features = $this->get_all_tab_features();
    }
    public function get_name()
    {
        return $this->name;
    }
    public abstract function get_label();
    public abstract function get_all_tab_features();
    public function get_count()
    {
        return \count($this->features);
    }
    public function should_display_count()
    {
        return \true;
    }
    public function save_form()
    {
        $features = $this->get_all_tab_features();
        // form submit will return only active features, so set them all as inactive as a base:
        $features = \array_map(function ($f) {
            return 'inactive';
        }, $features);
        foreach ($_POST['dce-feature'] ?? [] as $fn => $_) {
            if (!isset($features[$fn])) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions
                \error_log('Trying to save an unknown feature');
                continue;
            }
            $features[$fn] = 'active';
        }
        \DynamicContentForElementor\Plugin::instance()->features->db_update_features_status($features);
        $this->features = $this->get_all_tab_features();
        // refresh internal features status.
        \DynamicContentForElementor\Plugin::instance()->plugin_utils->admin_pages->admin_notices->success(esc_html__('Your preferences have been saved.', 'dynamic-content-for-elementor'));
    }
    public function are_all_active()
    {
        return !empty(wp_list_filter($this->features, ['status' => 'inactive']));
    }
    public function render_toggle_all_button()
    {
        $checked = checked($this->are_all_active(), \true, \false);
        $label = $this->get_label();
        $activate_label = esc_html__('Activate all', 'dynamic-content-for-elementor') . ' ' . esc_html($this->get_label());
        $deactivate_label = esc_html__('Deactivate all', 'dynamic-content-for-elementor') . ' ' . esc_html($this->get_label());
        echo <<<END
\t<h1>{$label}</h1>
\t<p id="dce-feature-all"><a href="#" id="dce-feature-activate-all"><span class='dot green'></span>{$activate_label}</a>&nbsp;/&nbsp;<a href="#" id="dce-feature-deactivate-all"><span class='dot red'></span>{$deactivate_label}</a></p>
END;
    }
    public function show_feature($feature_name, $feature_info)
    {
        $is_active = $feature_info['status'] === 'active';
        $plugin_dependencies_not_satisfied = Helper::check_plugin_dependencies(\true, $feature_info['plugin_depends']);
        $php_version_not_satisfied = isset($feature_info['minimum_php']) && \version_compare(\phpversion(), $feature_info['minimum_php'], '<');
        $is_bundled_feature = isset($feature_info['activated_by']);
        ?>

		<div class="dce-feature dce-feature-group-<?php 
        echo \strtolower($feature_name);
        ?> dce-feature-<?php 
        echo \urlencode(\strtolower($feature_info['title']));
        ?>
			<?php 
        if (!empty($plugin_dependencies_not_satisfied)) {
            echo ' required-plugin';
        }
        if ($php_version_not_satisfied) {
            echo ' required-php';
        }
        if ($is_bundled_feature) {
            echo ' bundled-feature';
        }
        if ($is_active || $is_bundled_feature && Plugin::instance()->features->is_feature_active($feature_info['activated_by'])) {
            echo ' widget-activated';
        }
        ?>
			">

		<?php 
        if ($php_version_not_satisfied) {
            ?>
			<div class="dce-check">
				<div class="deactivated"></div>
			</div>
			<small class="warning"><span class="dashicons dashicons-warning"></span> 
				<?php 
            /* translators: %1$s: minimum PHP version required */
            \printf(esc_html__('Requires PHP v%1$s+', 'dynamic-content-for-elementor'), $feature_info['minimum_php']);
            ?>
			</small>
		<?php 
        } elseif ($is_bundled_feature) {
            ?>
			<small class="warning">
				<span class="dashicons dashicons-warning"></span>
				<?php 
            if (Plugin::instance()->features->is_feature_active($feature_info['activated_by'])) {
                $status = esc_html__('active', 'dynamic-content-for-elementor');
            } else {
                $status = esc_html__('deactivated', 'dynamic-content-for-elementor');
            }
            $activated_by = Plugin::instance()->features->get_feature_title($feature_info['activated_by']);
            /* translators: 1: feature status, 2: activation dependency */
            \printf(esc_html__('This feature is %1$s. Its activation depends on %2$s', 'dynamic-content-for-elementor'), '<strong>' . $status . '</strong>', '<strong>' . $activated_by . '</strong>');
            ?>
			</small>
		<?php 
        } else {
            if (empty($plugin_dependencies_not_satisfied) && !$is_bundled_feature) {
                ?>
				<div class="dce-check">
					<input type="checkbox" name="dce-feature[<?php 
                echo $feature_name;
                ?>]" value="true" id="dce-feature-<?php 
                echo $feature_name;
                ?>" class="dce-checkbox" <?php 
                if ($is_active) {
                    ?> checked="checked"<?php 
                }
                ?>>
					<label for="dce-feature-<?php 
                echo $feature_name;
                ?>"><div id="tick_mark"></div></label>
				</div>
			<?php 
            } else {
                ?>
				<div class="dce-check">
					<div class="deactivated"></div>
				</div>
			<?php 
            }
            ?>

			<?php 
            if (!empty($plugin_dependencies_not_satisfied)) {
                ?>
				<small class="warning">
					<span class="dashicons dashicons-warning"></span>
					<?php 
                _e('Requires', 'dynamic-content-for-elementor');
                ?> <?php 
                echo \implode(', ', $plugin_dependencies_not_satisfied);
                ?>
				</small>
				<?php 
            }
        }
        if (isset($feature_info['icon'])) {
            ?>
			<p><i class="icon <?php 
            echo $feature_info['icon'];
            ?>" aria-hidden="true"></i></p>
		<?php 
        }
        ?>
		<h4><?php 
        echo esc_html($feature_info['title']);
        ?></h4>

		<?php 
        if (isset($feature_info['description'])) {
            ?>
			<p><?php 
            echo $feature_info['description'];
            ?></p>
			<?php 
        }
        ?>

		<?php 
        if (!empty($feature_info['doc_url'])) {
            ?>
			<p style="margin-top: -10px"><a href="<?php 
            echo DCE_FEATURES_URL . $feature_info['doc_url'];
            ?>" target="_blank"><?php 
            _e('Details', 'dynamic-content-for-elementor');
            ?></a></p>
		<?php 
        }
        if ($this->should_calculate_usage()) {
            $this->show_calculate_usage($feature_info['name']);
        }
        if (isset($feature_info['legacy'])) {
            if (isset($feature_info['replaced_by_custom_message'])) {
                ?>
				<p class="legacy"><?php 
                echo $feature_info['replaced_by_custom_message'];
                ?></p>
				<?php 
            } elseif (isset($feature_info['replaced_by'])) {
                $new_version_name = \DynamicContentForElementor\Plugin::instance()->features->get_feature_info($feature_info['replaced_by'], 'title');
                ?>
				<p class="legacy">
					<?php 
                /* translators: %1$s: name of the new version */
                \printf(esc_html__('This feature is deprecated. We recommend to use the new version called %1$s, but we will not remove this version.', 'dynamic-content-for-elementor'), '<strong>' . esc_html($new_version_name) . '</strong>');
                ?>
				</p>
			<?php 
            } else {
                ?>
				<p class="legacy"><?php 
                _e('This feature is deprecated. At the moment we don\'t have a new version and we will not remove this version.', 'dynamic-content-for-elementor');
                ?></p>
			<?php 
            }
        }
        ?>
		</div>
		<?php 
    }
    public function should_calculate_usage()
    {
        return \false;
    }
    public function show_calculate_usage($feature_name)
    {
        $elementor_controls_usage = get_option('elementor_controls_usage');
        $feature_used = \false;
        if ($elementor_controls_usage) {
            $feature_used = $this->calculate_usage($feature_name, $elementor_controls_usage);
        }
        if ($feature_used) {
            echo '<p class="used">';
            /* translators: %s: number of times the feature is used */
            \printf(_n('Used %s time', 'Used %s times', $feature_used, 'dynamic-content-for-elementor'), esc_html($feature_used));
            echo '</p>';
        }
    }
    public function render_list()
    {
        echo '<div class="dce-modules">';
        foreach ($this->features as $fname => $finfo) {
            $this->show_feature($fname, $finfo);
        }
        echo '</div>';
    }
    public function render()
    {
        echo '<form action="" method="post">';
        wp_nonce_field('dce-settings-page', 'dce-settings-page');
        if (isset($_POST['save-dce-feature'])) {
            $this->save_form();
        }
        $this->render_toggle_all_button();
        if ($this->get_count() > 6) {
            submit_button(esc_html__('Save', 'dynamic-content-for-elementor') . ' ' . $this->get_label());
        }
        $this->render_list();
        submit_button(esc_html__('Save', 'dynamic-content-for-elementor') . ' ' . $this->get_label());
        echo '<input type="hidden" name="save-dce-feature" value="1" />';
        echo '</form>';
    }
}
