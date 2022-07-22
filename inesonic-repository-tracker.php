<?php
/**
 * Plugin Name:       Inesonic Source Code Repository Tracker
 * Description:       A small plugin that provides support for tracking source code repositories.
 * Version:           1.0.0
 * Author:            Inesonic,  LLC
 * Author URI:        https://inesonic.com
 * License:           GPLv3
 * License URI:
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Text Domain:       inesonic-repository-tracker
 * Domain Path:       /locale
 ***********************************************************************************************************************
 * Copyright 2021 - 2022, Inesonic, LLC
 *
 * GNU Public License, Version 3:
 *   This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 *   License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any
 *   later version.
 *
 *   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 *   warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 *   details.
 *
 *   You should have received a copy of the GNU General Public License along with this program.  If not, see
 *   <https://www.gnu.org/licenses/>.
 ***********************************************************************************************************************
 * \file inesonic-version-tracker.php
 *
 * Main plug-in file.
 */

require_once __DIR__ . "/include/packages.php";
require_once __DIR__ . "/include/menus.php";

/**
 * Inesonic WordPress plug-in that manages a list of source code repositories.
 */
class InesonicRepositoryTracker {
    /**
     * The plugin version number.
     */
    const VERSION = '1.0.0';

    /**
     * The plugin slug.
     */
    const SLUG = 'inesonic-repository-tracker';

    /**
     * A pretty name for the plugin.
     */
    const NAME = 'Inesonic Source Code Repository Tracker';

    /**
     * The plugin author.
     */
    const AUTHOR = 'Inesonic, LLC';

    /**
     * The plugin prefix.
     */
    const PREFIX = 'InesonicRepositoryTracker';

    /**
     * Shorter plug-in descriptive name.
     */
    const SHORT_NAME = 'Source Code';

    /**
     * The singleton class instance.
     */
    private static $instance;  /* Plug-in instance */

    /**
     * Method that is called to initialize a single instance of the plug-in
     */
    public static function instance() {
        if (!isset(self::$instance) && !(self::$instance instanceof InesonicRepositoryTracker)) {
            self::$instance = new InesonicRepositoryTracker();
        }
    }

    /**
     * Static method that is triggered when the plug-in is activated.
     */
    public static function plugin_activated() {
        if (defined('ABSPATH') && current_user_can('activate_plugins')) {
            $plugin = isset($_REQUEST['plugin']) ? sanitize_text_field($_REQUEST['plugin']) : '';
            if (check_admin_referer('activate-plugin_' . $plugin)) {
                \Inesonic\RepositoryTracker\Packages::plugin_activated();
            }
        }
    }

    /**
     * Static method that is triggered when the plug-in is deactivated.
     */
    public static function plugin_uninstalled() {
        if (defined('ABSPATH') && current_user_can('activate_plugins')) {
            $plugin = isset($_REQUEST['plugin']) ? sanitize_text_field($_REQUEST['plugin']) : '';
            if (check_admin_referer('deactivate-plugin_' . $plugin)) {
                \Inesonic\RepositoryTracker\Packages::plugin_uninstalled();
            }
        }
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->packages = new \Inesonic\RepositoryTracker\Packages();
        $this->admin_menus = new \Inesonic\RepositoryTracker\Menus(
            $this->packages,
            self::SHORT_NAME,
            self::NAME,
            self::SLUG
        );

        add_action('init', array($this, 'customize_on_initialization'));
    }

    /**
     * Method that performs various initialization tasks during WordPress init phase.
     */
    public function customize_on_initialization() {
        add_shortcode('inesonic_source_code_table', array($this, 'inesonic_source_code_table'));
    }

    /**
     * Method that provides a shortcode that generates a table of source code repositories.
     *
     * \param[in] $attributes The shortcode attributes.
     *
     * \return Returns an HTML table rendering a list of source code repositiroes.
     */
    public function inesonic_source_code_table($attributes) {
        if (is_array($attributes) && array_key_exists('project', $attributes)) {
            $project = $attributes['project'];
        } else {
            $project = null;
        }

        if (is_array($attributes) && array_key_exists('package_name', $attributes)) {
            $package_name_header = $attributes['package_name'];
        } else {
            $package_name_header = __('Package', 'inesonic-repository-tracker');
        }

        if (is_array($attributes) && array_key_exists('repository_url', $attributes)) {
            $repository_url_header = $attributes['repository_url'];
        } else {
            $repository_url_header = __('Repository URL', 'inesonic-repository-tracker');
        }

        if (is_array($attributes) && array_key_exists('description', $attributes)) {
            $description_header = $attributes['description'];
        } else {
            $description_header = __('Description', 'inesonic-repository-tracker');
        }

        $result = '<table class="inesonic-repository-tracker-table">' .
                    '<thead class="inesonic-repository-tracker-table-header">' .
                      '<tr class="inesonic-repository-tracker-table-header-row">' .
                        '<td class="inesonic-repository-tracker-table-header-package-name">' .
                          esc_html($package_name_header) .
                        '</td>' .
                        '<td class="inesonic-repository-tracker-table-header-repository-url">' .
                          esc_html($repository_url_header) .
                        '</td>' .
                        '<td class="inesonic-repository-tracker-table-header-description">' .
                          esc_html($description_header) .
                        '</td>' .
                      '</tr>' .
                    '</thead>' .
                    '<tbody class="inesonic-repository-tracker-table-body">';

        $packages = $this->packages->packages();
        foreach ($packages as $package) {
            if ($project === null || in_array($project, $package->projects())) {
                $result .=     '<tr class="inesonic-repository-tracker-table-row">' .
                                 '<td class="inesonic-repository-tracker-package-name">' .
                                   esc_html($package->package_name()) .
                                 '</td>' .
                                 '<td class="inesonic-repository-tracker-repository-url">' .
                                   '<a href="' . $package->repository_url() . '" '.
                                      'class="inesonic-repository-tracker-link"' .
                                   '>' .
                                     esc_html($package->repository_url()) .
                                   '</a>' .
                                 '</td>' .
                                 '<td class="inesonic-repository-tracker-description">' .
                                   esc_html($package->description()) .
                                 '</td>' .
                               '</tr>';
            }
        }

        $result .=   '</tbody>' .
                   '</table>';

        return $result;
    }
}

/* Instantiate our plug-in. */
InesonicRepositoryTracker::instance();

/* Define critical global hooks. */
register_activation_hook(__FILE__, array('InesonicRepositoryTracker', 'plugin_activated'));
register_uninstall_hook(__FILE__, array('InesonicRepositoryTracker', 'plugin_uninstalled'));
