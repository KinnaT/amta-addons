<?php
/*
  Plugin Name: Event Espresso - Certificates (EE4.x+)
  Plugin URI: http://www.eventespresso.com
  Description: Event Espresso addon to allow creation of certificates based on earned credits.
  Version: 1.0.0
  Author: Kinna Thompson
  Author URI: https://www.twitter.com/real_kinna
  Copyright 2016 Kinna Thompson (email : kinna3@outlook.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package        Event Espresso
 * @ author            Event Espresso
 * @ copyright    (c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license        http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link                http://www.eventespresso.com
 * @ version         EE4
 *
 * ------------------------------------------------------------------------
 */
// define versions and this file
define( 'EE_CERTIFICATES_CORE_VERSION_REQUIRED', '4.8.0.rc.0000' );
define( 'EE_CERTIFICATES_VERSION', '1.0.0' );
define( 'EE_CERTIFICATES_PLUGIN_FILE',  __FILE__ );




/**
 *    captures plugin activation errors for debugging
 */
function espresso_certificates_plugin_activation_errors() {

    if ( WP_DEBUG ) {
        $activation_errors = ob_get_contents();
        file_put_contents( EVENT_ESPRESSO_UPLOAD_DIR . 'logs' . DS . 'espresso_certificates_plugin_activation_errors.html', $activation_errors );
    }
}
add_action( 'activated_plugin', 'espresso_certificates_plugin_activation_errors' );



/**
 *    registers addon with EE core
 */
function load_espresso_certificates() {
  if ( class_exists( 'EE_Addon' )) {
      // certificates version
      require_once ( plugin_dir_path( __FILE__ ) . 'EE_Certificates.class.php' );
      EE_Certificates::register_addon();
  } else {
    add_action( 'admin_notices', 'espresso_certificates_activation_error' );
  }
}
add_action( 'AHEE__EE_System__load_espresso_addons', 'load_espresso_certificates' );



/**
 *    verifies that addon was activated
 */
function espresso_certificates_activation_check() {
  if ( ! did_action( 'AHEE__EE_System__load_espresso_addons' ) ) {
    add_action( 'admin_notices', 'espresso_certificates_activation_error' );
  }
}
add_action( 'init', 'espresso_certificates_activation_check', 1 );



/**
 *    displays activation error admin notice
 */
function espresso_certificates_activation_error() {
  unset( $_GET[ 'activate' ] );
  unset( $_REQUEST[ 'activate' ] );
  if ( ! function_exists( 'deactivate_plugins' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  }
  deactivate_plugins( plugin_basename( EE_CERTIFICATES_PLUGIN_FILE ) );
  ?>
  <div class="error">
    <p><?php printf( __( 'Event Espresso Certificates could not be activated. Please ensure that Event Espresso version %1$s or higher is running', 'event_espresso' ), EE_CERTIFICATES_CORE_VERSION_REQUIRED ); ?></p>
  </div>
<?php
}



// End of file espresso_certificates.php
// Location: wp-content/plugins/eea-certificates/espresso_certificates.php
