<?php
 /**********************************************************************************************************************
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
 */

namespace Inesonic\RepositoryTracker;
    require_once dirname(__FILE__) . '/helpers.php';
    require_once dirname(__FILE__) . '/packages.php';

    /**
     * Class that manages the plug-in admin panel menus.
     */
    class Menus {
        /**
         * Static method that is triggered when the plug-in is activated.
         *
         * \param $options The plug-in options instance.
         */
        public static function plugin_activated(Options $options) {}

        /**
         * Static method that is triggered when the plug-in is deactivated.
         *
         * \param $options The plug-in options instance.
         */
        public static function plugin_deactivated(Options $options) {}

        /**
         * Constructor
         *
         * \param $packages          The packages database.
         *
         * \param $short_plugin_name A short version of the plug-in name to be used in the menus.
         *
         * \param $plugin_name       The user visible name for this plug-in.
         *
         * \param $plugin_slug       The slug used for the plug-in.  We use this slug as a prefix for slugs this class
         *                           may also require.
         */
        public function __construct(
                Packages $packages,
                string   $short_plugin_name,
                string   $plugin_name,
                string   $plugin_slug
            ) {
            $this->short_plugin_name = $short_plugin_name;
            $this->plugin_name = $plugin_name;
            $this->plugin_slug = $plugin_slug;
            $this->plugin_prefix = str_replace('-', '_', $plugin_slug);

            $this->packages = $packages;

            add_action('init', array($this, 'on_initialization'));
        }

        /**
         * Method you can use to indicate if we have an API key.
         *
         * \param $have_api_key If true, then we have an API key.  If false, then we do not have an API key.
         */
        public function set_have_api_key(Boolean $have_api_key) {
            $this->have_api_key = $have_api_key;
        }

        /**
         * Method that is triggered during initialization to bolt the plug-in settings UI into WordPress.
         */
        public function on_initialization() {
            add_action('admin_menu', array($this, 'add_menu'));
            add_action('wp_ajax_inesonic_repository_tracker_update' , array($this, 'update'));
        }

        /**
         * Method that adds the menu to the dashboard.
         */
        public function add_menu() {
            add_menu_page(
                $this->plugin_name,
                $this->short_plugin_name,
                'manage_options',
                $this->plugin_prefix,
                array($this, 'build_page'),
                plugin_dir_url(__FILE__) . 'assets/img/menu_icon.png',
                30
            );
        }

        /**
         * Method that adds scripts and styles to the admin page.
         */
        public function enqueue_scripts() {
            wp_enqueue_script('jquery');
            wp_enqueue_script(
                'inesonic-repository-tracker-settings-page',
                \Inesonic\RepositoryTracker\javascript_url('settings-page'),
                array('jquery'),
                null,
                true
            );
            wp_localize_script(
                'inesonic-repository-tracker-settings-page',
                'ajax_object',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );

            wp_enqueue_style(
                'inesonic-repository-tracker-styles',
                \Inesonic\RepositoryTracker\css_url('inesonic-repository-tracker-styles'),
                array(),
                null
            );
        }

        /**
         * Method that renders the site monitoring page.
         */
        public function build_page() {
            $this->enqueue_scripts();

            echo '<div class="inesonic-repository-tracker-page-title"><h1 class="inesonic-repository-tracker-header">' .
                   __("Source Code Repositories", 'inesonic-repository-tracker') .
                 '</h1></div>' .
                 '<div class="inesonic-repository-tracker-section">' .
                   '<div class="inesonic-repository-tracker-section-title">' .
                     '<h2 class="inesonic-repository-tracker-subheader">' .
                       __('Repository Data', 'inesonic-repository-tracker') .
                     '</h2>' .
                   '</div>' .
                   '<div class="inesonic-repository-tracker-repository-table-area">' .
                     '<table class="inesonic-repository-tracker-repository-table">' .
                       '<thead class="inesonic-repository-tracker-repository-table-header">' .
                         '<tr class="inesonic-repository-tracker-repository-table-header-row">' .
                           '<td class="inesonic-repository-tracker-repository-table-header-package-name">' .
                             __('Package Name', 'inesonic-repository-tracker') .
                           '</td>' .
                           '<td class="inesonic-repository-tracker-repository-table-header-projects">' .
                             __('Projects (comma separated)', 'inesonic-repository-tracker') .
                           '</td>' .
                           '<td class="inesonic-repository-tracker-repository-table-header-repository-url">' .
                             __('Repository URL', 'inesonic-repository-tracker') .
                           '</td>' .
                           '<td class="inesonic-repository-tracker-repository-table-header-description">' .
                             __('Description', 'inesonic-repository-tracker') .
                           '</td>' .
                         '</tr>' .
                       '</thead>' .
                       '<tbody id="inesonic-repository-tracker-repository-table-body" ' .
                              'class="inesonic-repository-tracker-repository-table-body"' .
                       '>';

            $packages = $this->packages->packages();
            $row_index = 0;
            foreach($packages as $package) {
                echo self::repository_table_row(
                    $row_index,
                    $package->package_name(),
                    $package->projects(),
                    $package->repository_url(),
                    $package->description()
                );

                ++$row_index;
            }

            echo self::repository_table_row($row_index, '', array(), '', '');

            echo       '</tbody>' .
                     '</table>' .
                   '</div>' .
                   '<div class="inesonic-repository-tracker-button-area">' .
                     '<a id="inesonic-repository-tracker-update-repository-table-button" class="button">' .
                       __('Update Source Code Repository Data', 'inesonic-repository-tracker') .
                     '</a>' .
                   '</div>' .
                 '</div>';
        }

        /**
         * Method that creates a version table row.
         *
         * \param[in] $row_index      The row index used to access fields by ID.
         *
         * \param[in] $package_name   The package name.
         *
         * \param[in] $projects       A list of projects of interest.
         *
         * \param[in] $repository_url The URL for the repository.
         *
         * \param[in] $description    The package description.
         *
         * \return Returns the row data for the table row.
         */
        static private function repository_table_row(
                int    $row_index,
                string $package_name,
                array  $projects,
                string $repository_url,
                string $description
            ) {
            return '<tr class="inesonic-repository-tracker-repository-table-row">' .
                     '<td class="inesonic-repository-tracker-repository-table-package-name-data">' .
                       '<input type="text" ' .
                              'id="inesonic-repository-tracker-package-name-' . $row_index . '" ' .
                              'class="inesonic-repository-tracker-package-name-input" ' .
                              'value="' . esc_html($package_name) . '"' .
                       '/>' .
                     '</td>' .
                     '<td class="inesonic-repository-tracker-repository-table-projects-data">' .
                       '<input type="text" ' .
                              'id="inesonic-repository-tracker-projects-' . $row_index . '" ' .
                              'class="inesonic-repository-tracker-projects-input" ' .
                              'value="' . esc_html(implode(', ', $projects)) . '"' .
                       '/>' .
                     '</td>' .
                     '<td class="inesonic-repository-tracker-repository-table-repository-url-data">' .
                       '<input type="text" ' .
                              'id="inesonic-repository-tracker-repository-url-' . $row_index . '" ' .
                              'class="inesonic-repository-tracker-repository-url-input" ' .
                              'value="' . esc_html($repository_url) . '"' .
                       '/>' .
                     '</td>' .
                     '<td class="inesonic-repository-tracker-repository-table-description-data">' .
                       '<input type="text" ' .
                              'id="inesonic-repository-tracker-description-' . $row_index . '" ' .
                              'class="inesonic-repository-tracker-description-input" ' .
                              'value="' . esc_html($description) . '"' .
                       '/>' .
                     '</td>' .
                   '</tr>';
        }

        /**
         * Method that is triggered by AJAX to update version data.
         */
        public function update() {
            if (current_user_can('activate_plugins')) {
                if (array_key_exists('data', $_POST)) {
                    $repository_data = $_POST['data'];

                    $package_data = array();
                    $valid_data = true;
                    foreach ($repository_data as $data) {
                        $package_name = $data['name'];
                        $projects = $data['projects'];
                        $repository_url = $data['url'];
                        $description = $data['description'];

                        $package_data[] = new \Inesonic\RepositoryTracker\Package(
                            $projects,
                            $package_name,
                            $description,
                            $repository_url
                        );
                    }

                    $this->packages->set_packages($package_data);
                    $status = 'OK';
                } else {
                    $status = 'invalid message';
                }
            } else {
                $status = 'insufficient permissions';
            }

            echo json_encode(array('status' => $status));
            wp_die();
        }
    };
