<?php

if (!defined('ABSPATH'))
    exit('No direct script access allowed');

//define constants
define( 'EE_CERT_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_CERT_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_CERT_ADMIN', EE_CERT_PATH . 'admin' . DS );
define( 'EE_CERT_TEMPLATE_PATH', EE_CERT_PATH . 'templates/' );
define( 'EE_CERT_BASENAME', plugin_basename( EE_CERT_PLUGIN_FILE ) );

/**
 * Class definition for the EE_CERT object
 *
 * @since         1.0.0
 * @package     EE CERT
 */
class EE_CERT extends EE_Addon {

    /**
     * Set up
     */
    public static function register_addon() {
        EE_Register_Addon::register(
                'EE_CERT', array(
                'version'            => EE_CERT_VERSION,
                'min_core_version'    => EE_CERT_MIN_CORE_VERSION_REQUIRED,
                'main_file_path'    => EE_CERT_PLUGIN_FILE,
                'config_class'        => 'EE_Cert_Config',
                'config_name'        => 'cert',
                'admin_path'        => EE_CERT_ADMIN . 'cert' . DS,
                'admin_callback'    => 'additional_admin_hooks',
                'autoloader_paths'    => array(
                    'EE_Cert'                => EE_CERT_PATH . 'EE_Cert.class.php',
                    'EE_Cert_Config'         => EE_CERT_PATH . 'EE_Cert_Config.php',
                    'Cert_Admin_Page'        => EE_CERT_ADMIN . 'cert' . DS . 'Cert_Admin_Page.core.php',
                    'Cert_Admin_Page_Init'   => EE_CERT_ADMIN . 'cert' . DS . 'Ee_Cert_Admin_Page_Init.core.php',
                ),
                'module_paths' => array(
                    EE_CERT_PATH . 'EED_Cert_Admin.module.php'
                 ),
                // if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
                'pue_options' => array(
                    'pue_plugin_slug' => 'eea-cert-integration',
                    'checkPeriod' => '24',
                    'use_wp_update' => FALSE
                ),
                )
        );
    }

    public function additional_admin_hooks() {
        if ( is_admin() && ! EE_Maintenance_Mode::instance()->level() ) {
            add_filter( 'plugin_action_links', array( $this, 'plugin_actions' ), 10, 2 );
        }
    }

        public function plugin_actions( $links, $file ) {
        if ( $file === EE_CERT_BASENAME ) {
            array_unshift( $links, '<a href="admin.php?page=espresso_cert&action=settings">' . __('Settings') . '</a>' );
        }
        return $links;
    }

    public static function get_attendee_user( $att_id ) {
        global $wpdb;
        $key = $wpdb->get_blog_prefix() . 'EE_Attendee_ID';
        $query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '$key' AND meta_value = '%d'";
        $user_id = $wpdb->get_var( $wpdb->prepare( $query, (int) $att_id ) );
        return $user_id ? (int) $user_id : NULL;
    }

    /**
     * used to determine if CE credits are turned on for the event or not.
     * @param int|EE_Event $event Either event_id or EE_Event object.
     *
     * @return bool
     */
    public static function is_event_has_credits( $event ) {
        return self::_get_cert_event_setting( 'has_credits', $event );
    }

    /**
     * This retrieves the specific cert setting for an event as indicated by key.
     *
     * @return mixed Whatever the value for the key is or what is set as the global default if it doesn't
     * exist.
     */
    protected static function _get_cert_event_setting( $key, $event ) {
        $config = isset( EE_Registry::instance()->CFG->addons->cert ) ? EE_Registry::instance()->CFG->addons->cert : false;
        $global_default = array(
            'has_credits' => $config && isset( $config->has_credits ) ? $config->has_credits : false,
            );


        $event = $event instanceof EE_Event ? $event : EE_Registry::instance()->load_model( 'Event' )->get_one_by_ID( (int) $event );
        $settings = $event instanceof EE_Event ? $event->get_post_meta( 'ee_cert_settings', true ) : array();
        if ( ! empty( $settings ) ) {
            $value =  isset( $settings[$key] ) ? $settings[$key] : $global_default[$key];

            return is_bool( $global_default[$key] ) ? (bool) $value : $value;
        }
        return $global_default[$key];
    }


    public static function update_event_has_credits( $event, $has_credits = false ) {
        return self::_update_cert_event_setting( 'has_credits', $event, $has_credits );
    }

    protected static function _update_cert_event_setting( $key, $event, $value ) {
        $event = $event instanceof EE_Event ? $event : EE_Registry::instance()->load_model( 'Event' )->get_one_by_ID( (int) $event );

        if ( ! $event instanceof EE_Event ) {
            return false;
        }
        $settings = $event->get_post_meta( 'ee_cert_settings', true );
        $settings = empty( $settings ) ? array() : $settings;
        $settings[$key] = $value;
        return $event->update_post_meta( 'ee_cert_settings', $settings );
    }

}

// end of class EE_CERT
