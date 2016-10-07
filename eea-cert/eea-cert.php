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

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
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
        // new_addon version
        require_once ( plugin_dir_path( __FILE__ ) . 'EE_Cert.class.php' );
        EE_Cert::register_addon();
    }
}

add_action( 'AHEE__EE_System__load_espresso_addons', 'load_ee_core_cert' );

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

function leg_credits() {
    if ( is_page( 5212 ) && is_user_logged_in() ) {
        function get_legacy_credits($output_type=OBJECT) {
            global $wpdb;
            global $current_user;
            get_currentuserinfo();
            $user = $current_user->user_login;
            $leg_table  = $wpdb->prefix . 'ce_credits';
            return $wpdb->get_results("SELECT * FROM {$leg_table} WHERE `username` = '$user'", OBJECT);
        }
        $legacy_credits = get_legacy_credits();
        if (!empty($legacy_credits)){
            ?>
            <div class="legacy-credits">
                <h3>Legacy CE Credits Earned</h3>
                <?php // print_r($legacy_credits); ?>
                <table id="legacy-credits-table" class="espresso-table footable table footable-loaded" data-filter="#filter">
                    <thead class="espresso-table-header-row">
                        <tr>
                            <th class="th-group footable-sortable legacy-credits-date">Date<span class="footable-sort-indicator"></span></th>
                            <th class="th-group footable-sortable">Event<span class="footable-sort-indicator"></span></th>
                            <th class="th-group footable-sortable">Credits<span class="footable-sort-indicator"></span></th>
                            <th class="th-group" data-sort-ignore="true"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($legacy_credits as $legacy_credit) {
                        $credit = $legacy_credit->credits_id;
                         ?>
                        <tr class="espresso-table-row unit legacy-credits-body" style="display: table-row;">
                            <td class="event-<?php echo $credit ?> legacy-credits-date"><?php echo $legacy_credit->start_date ?></td>
                            <td class="event-<?php echo $credit ?> legacy-credits-event"><?php echo $legacy_credit->details ?></td>
                            <td class="event-<?php echo $credit ?> legacy-credits-credits"><?php echo $legacy_credit->credits ?></td>
                            <td class="legacy-credits-cert"><a id="a_leg_cert_link-<?php echo $credit ?>" class="a_cert_link" href="http://127.0.0.1/wordpress/?page_id=5958&credits_id=<?php echo $credit ?>" target="_blank">View Certificate</a></td>
                        </tr>
                        <?php    } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>

            </div>
        <?php }
    }
}

// End of file ee-addon-cert.php
// Location: wp-content/plugins/ee4-cert/ee-addon-cert.php
