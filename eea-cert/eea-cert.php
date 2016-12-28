<?php

if ( ! defined( 'ABSPATH' ) )
    exit( 'No direct script access allowed' );
/*
  Plugin Name:     Event Espresso - Certificates (EE4.6+)
  Plugin URI:     http://www.eventespresso.com
  Description:     This adds the certificates integration.
  Version:         2.0.11.p
  Author:         Event Espresso
  Author URI:     http://www.eventespresso.com
  License:         GPLv2
  TextDomain:     event_espresso
  Copyright     (c) 2008-2014 Event Espresso  All Rights Reserved.

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
 */

/**
 * EE Cert add-on for Event Espresso
 * @since         1.0.0
 * @package     EE CERT
 *
 */
define( 'EE_CERT_VERSION', '2.0.11.p' );
define( 'EE_CERT_MIN_CORE_VERSION_REQUIRED', '4.8.21.rc.005' );
define( 'EE_CERT_PLUGIN_FILE', __FILE__ );


function load_ee_core_cert() {
    if ( class_exists( 'EE_Addon' ) ) {
        require_once ( plugin_dir_path( __FILE__ ) . 'EE_Cert.class.php' );
        EE_Cert::register_addon();
    }
}

add_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_cert' );

// This handles all of the actual certificate creation, as the cert is a page unto itself, handled with GET to carry over the selected certificate's ID.
class PageTemplater {
        private static $instance;
        protected $templates;
        // Returns an instance of this class.
        public static function get_instance() {
                if( null == self::$instance ) {
                        self::$instance = new PageTemplater();
                }
                return self::$instance;
        }
        // Initializes the plugin by setting filters and administration functions.
        private function __construct() {
            $this->templates = array();
            // Add a filter to the attributes metabox to inject template into the cache.
            if ( version_compare( floatval($GLOBALS['wp_version']), '4.7', '<' ) ) { // 4.6 and older
                add_filter(
                    'page_attributes_dropdown_pages_args',
                    array( $this, 'register_project_templates' )
                );
            } else { // Add a filter to the wp 4.7 version attributes metabox
                add_filter(
                    'theme_page_templates', array( $this, 'add_new_template' )
                );
            }
                add_filter(
                    'page_attributes_dropdown_pages_args',
                     array( $this, 'register_project_templates' )
                );
                // Add a filter to the save post to inject out template into the page cache
                add_filter(
                    'wp_insert_post_data',
                    array( $this, 'register_project_templates' )
                );
                // Add a filter to the template include to determine if the page has our template assigned and return it's path
                add_filter(
                    'template_include',
                    array( $this, 'view_project_template')
                );
                // Add your templates to this array.
                $this->templates = array(
                        'cert-template.php' => 'Certificate',
                        'leg-cert-template.php' => 'Legacy Certificate'
                );

        }

        // Adds our template to the pages cache in order to trick WordPress into thinking the template file exists where it doesn't really exist.
        public function register_project_templates( $atts ) {
                // Create the key used for the themes cache
                $cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
                // Retrieve the cache list. If it doesn't exist, or it's empty prepare an array
                $templates = wp_get_theme()->get_page_templates();
                if ( empty( $templates ) ) { $templates = array(); }
                // New cache, therefore remove the old one
                wp_cache_delete( $cache_key , 'themes');
                // Now add our template to the list of templates by merging our templates with the existing templates array from the cache.
                $templates = array_merge( $templates, $this->templates );
                // Add the modified cache to allow WordPress to pick it up for listing available templates
                wp_cache_add( $cache_key, $templates, 'themes', 1800 );
                return $atts;
        }
        public function add_new_template( $posts_templates ) {
            $posts_templates = array_merge( $posts_templates, $this->templates );
            return $posts_templates;
        }
        // Checks if the template is assigned to the page
        public function view_project_template( $template ) {
                global $post;
                if (!isset($this->templates[get_post_meta(
                    $post->ID, '_wp_page_template', true
                )] ) ) { return $template; }
                $file = plugin_dir_path(__FILE__). get_post_meta(
                    $post->ID, '_wp_page_template', true
                );
                // Just to be safe, we check if the file exist first
                if( file_exists( $file ) ) { return $file; }
                else { echo $file; }
                return $template;
        }
}
add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );
// add_action( 'the_content', 'leg_credits')

// End of file ee-addon-cert.php
// Location: wp-content/plugins/ee4-cert/ee-addon-cert.php
