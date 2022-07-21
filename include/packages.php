<?php
/***********************************************************************************************************************
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
    /**
     * Trivial class that tracks information about a source code package.
     */
    class Package {
        /**
         * Constructor
         *
         * \param[in] $projects       An array of projects using this package.
         *
         * \param[in] $package_name   The package name.
         *
         * \param[in] $description    The package description.
         *
         * \param[in] $repository_url The repository URL.
         */
        public function __construct(array $projects, string $package_name, string $description, $repository_url) {
            $this->current_projects = $projects;
            $this->current_package_name = $package_name;
            $this->current_description = $description;
            $this->current_repository_url = $repository_url;
        }

        /**
         * Method that returns a list of projects this package is associated with.
         *
         * \return Returns a list of projects this package is associated with.
         */
        public function projects() {
            return $this->current_projects;
        }

        /**
         * Method you can use to obtain the package name.
         *
         * \return Returns the package name.
         */
        public function package_name() {
            return $this->current_package_name;
        }

        /**
         * Method you can use to obtain the package description.
         *
         * \return Returns the package description.
         */
        public function description() {
            return $this->current_description;
        }

        /**
         * Method you can use to obtain the repository URL.
         *
         * \return Returns the repository URL.
         */
        public function repository_url() {
            return $this->current_repository_url;
        }
    };

    /**
     * Class that tracks information about all packages.
     */
    class Packages {
        /**
         * Static method that is triggered when the plug-in is activated.
         */
        static public function plugin_activated() {
            global $wpdb;
            $wpdb->query(
                'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'inesonic_source_packages' . ' (' .
                    'idx INTEGER UNSIGNED NOT NULL,' .
                    'projects VARCHAR(255) NOT NULL,' .
                    'package_name VARCHAR(64) NOT NULL,' .
                    'description VARCHAR(128) NOT NULL,' .
                    'repository_url VARCHAR(2048) NOT NULL,' .
                    'PRIMARY KEY (idx)' .
                ')'
            );
        }

        /**
         * Static method that is triggered when the plug-in is deactivated.
         */
        static public function plugin_deactivated() {}

        /**
         * Static method that is triggered when the plug-in is uninstalled.
         */
        static public function plugin_uninstalled() {
            global $wpdb;
            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'inesonic_source_packages');
        }

        /**
         * Constructor
         */
        public function __construct() {
            $this->package_data = null;
        }

        /**
         * Method that returns the current list of source packages.
         *
         * \return Returns an array of Package instances holding all the known source packages.
         */
        public function packages() {
            if ($this->package_data === null) {
                $this->load_package_data();
            }

            return $this->package_data;
        }

        /**
         * Method that updates the current list of source packages.
         *
         * \param[in] new_packages The new list of source packages.
         */
        public function set_packages(array $new_packages) {
            $this->package_data = $new_packages;
            $this->save_package_data();
        }

        /**
         * Function that loads the package data from the database.
         */
        private function load_package_data() {
            global $wpdb;
            $query_results = $wpdb->get_results(
                'SELECT idx,projects,package_name,description,repository_url FROM ' .
                    $wpdb->prefix . 'inesonic_source_packages' . ' ' .
                    'ORDER BY idx ASC'
            );

            $package_data = array();
            foreach($query_results as $query_result) {
                $package_data[] = new \Inesonic\RepositoryTracker\Package(
                    json_decode($query_result->projects),
                    $query_result->package_name,
                    $query_result->description,
                    $query_result->repository_url
                );
            }

            $this->package_data = $package_data;
        }

        /**
         * Function that saves the package data from the database.
         */
        private function save_package_data() {
            global $wpdb;

            $wpdb->query('DELETE FROM ' . $wpdb->prefix . 'inesonic_source_packages');

            foreach($this->package_data as $index => $package_data) {
                $wpdb->insert(
                    $wpdb->prefix . 'inesonic_source_packages',
                    array(
                        'idx' => intval($index),
                        'projects' => json_encode($package_data->projects()),
                        'package_name' => $package_data->package_name(),
                        'description' => $package_data->description(),
                        'repository_url' => $package_data->repository_url()
                    ),
                    array(
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s'
                    )
                );
            }
        }
    }
