<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('No direct script access allowed'); }
// define the plugin directory path and URL
define( 'EE_CERT_BASENAME', plugin_basename( EE_CERT_PLUGIN_FILE ) );
define( 'EE_CERT_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_CERT_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_CERT_TEMPLATE_PATH', EE_CERT_PATH . 'templates/' );
define( 'EE_CERT_ADMIN', EE_CERT_PATH . 'admin' . DS . 'cert' . DS );
define( 'EE_CERT_CORE', EE_CERT_PATH . 'core' . DS);

/**
 *
 * Class  EE_Cert
 *
 * @package            Event Espresso
 * @subpackage        eea-cert
 * @author             Kinna Thompson
 *
 */

class EE_Cert extends EE_Addon {

    /**
     * register_addon
     */
    public static function register_addon() {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Cert',
            array(
                'version'                     => EE_CERT_VERSION,
                'min_core_version' => EE_CERT_CORE_VERSION_REQUIRED,
                'main_file_path'         => EE_CERT_PLUGIN_FILE,
                'plugin_slug'             => 'espresso_cert',
                'admin_path'             => EE_CERT_ADMIN,
                'admin_callback'        => '',
                'config_class'             => 'EE_Cert_Config',
                'config_name'         => 'EE_Cert',
                'autoloader_paths' => array(
                    'EE_Cert' => EE_CERT_PATH . 'EE_Cert.class.php',
                    'EE_Cert_Config' => EE_CERT_PATH . 'EE_Cert_Config.php',
                    'Cert_Admin_Page' => EE_CERT_ADMIN . 'Cert_Admin_Page.core.php',
                    'Cert_Admin_Page_Init' => EE_CERT_ADMIN . 'Cert_Admin_Page_Init.core.php',
                ),
                // 'dms_paths'             => array( EE_CERT_PATH . 'core' . DS . 'data_migration_scripts' . DS ),
                'module_paths'         => array(
                    EE_CERT_PATH . 'EED_Cert.module.php',
                    EE_CERT_PATH . 'EED_Cert_Admin.module.php'
                ),
                'shortcode_paths'     => array( EE_CERT_PATH . 'EES_Cert.shortcode.php' ),
                'widget_paths'         => array( EE_CERT_PATH . 'EEW_Cert.widget.php' ),
                'capabilities' => array(
                    'administrator' => array(
                        'edit_thing', 'edit_things', 'edit_others_things', 'edit_private_things'
                    ),
                ),
                'class_paths'                         => EE_CERT_PATH . 'core' . DS . 'db_classes',
                'model_paths'                     => EE_CERT_PATH . 'core' . DS . 'db_models',
                'class_extension_paths'         => EE_CERT_PATH . 'core' . DS . 'db_class_extensions',
                'model_extension_paths'     => EE_CERT_PATH . 'core' . DS . 'db_model_extensions',
                //note for the mock we're not actually adding any custom cpt stuff yet.
                'custom_post_types'             => array(),
                'custom_taxonomies'         => array(),
                'default_terms'                     => array(),
                // if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
                'pue_options'            => array(
                    'pue_plugin_slug'         => 'eea-cert',
                    'plugin_basename'     => EE_CERT_BASENAME,
                    'checkPeriod'                 => '24',
                    'use_wp_update'         => FALSE,
                ),
            )
        );
    }

    /**
     * Used to get a user id for a given EE_Attendee id.
     * If none found then null is returned.
     *
     * @param int      $att_id The attendee id to find a user match with.
     *
     * @return int|null     $user_id if found otherwise null.
     */
    public static function get_attendee_user( $att_id ) {
        global $wpdb;
        $key = $wpdb->get_blog_prefix() . 'EE_Attendee_ID';
        $query = "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '$key' AND meta_value = '%d'";
        $user_id = $wpdb->get_var( $wpdb->prepare( $query, (int) $att_id ) );
        return $user_id ? (int) $user_id : NULL;
    }


    /**
     * used to determine if credits are turned on for the event or not.
     *
     * @param int|EE_Event $event Either event_id or EE_Event object.
     *
     * @return bool   true YES has_credits turned on false NO forced login turned off.
     */
    public static function is_datetime_has_credits_on( $datetime ) {
        return self::_get_cert_datetime_setting( 'has_credits', $datetime );
    }



    public static function has_ce_credits( $datetime ) {
        return self::_get_cert_datetime_setting( 'ce_credits', $datetime );
    }


    /**
     * This retrieves the specific cert setting for an event as indicated by key.
     *
     * @param string $key   What setting are we retrieving
     * @param int|EE_Event EE_Event  or event id
     *
     * @return mixed Whatever the value for the key is or what is set as the global default if it doesn't
     * exist.
     */
    protected static function _get_cert_datetime_setting( $key, $datetime ) {
        //any global defaults?
        $config = isset( EE_Registry::instance()->CFG->addons->cert ) ? EE_Registry::instance()->CFG->addons->cert : false;
        $global_default = array(
            'has_credits' => $config && isset( $config->has_credits ) ? $config->has_credits : false,
            'ce_credits' => $config && isset( $config->ce_credits ) ? $config->ce_credits : null
            );

        $datetime = $datetime instanceof EE_Datetime ? $datetime : EE_Registry::instance()->load_model( 'Datetime' )->get_one_by_ID( (int) $datetime );
        $settings = $datetime instanceof EE_Event ? $datetime->get_post_meta( 'ee_cert_settings', true ) : array();
        if ( ! empty( $settings ) ) {
            $value =  isset( $settings[$key] ) ? $settings[$key] : $global_default[$key];

            //since post_meta *might* return an empty string.  If the default global value is boolean, then let's make sure we cast the value returned from the post_meta as boolean in case its an empty string.
            return is_bool( $global_default[$key] ) ? (bool) $value : $value;
        }
        return $global_default[$key];
    }

    /**
     * used to update the has_credits setting for an datetime.
     *
     * @param int|EE_Event $event Either the EE_Event object or int.
     * @param bool $force_login value.  If turning off you can just not send.
     *
     * @throws EE_Error (via downstream activity)
     * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success
     * and false on failure. NOTE: If the meta_value passed to this function is the
     * same as the value that is already in the database, this function returns false.
     */
    public static function update_datetime_has_credits( $datetime, $has_credits = false ) {
        return self::_update_has_credits_datetime_setting( 'has_credits', $datetime, $has_credits );
    }

    /**
     * used to update the CE credits setting for an datetime.
     *
     * @param int|EE_Event $event Either the EE_Event object or int.
     * @param bool $auto_create value.  If turning off you can just not send.
     */
    public static function update_ce_credits( $datetime, $ce_credits = null ) {
        return self::_update_ce_credits_setting( 'ce_credits', $datetime, $ce_credits );
    }


    /**
     * used to update the certificates datetime specific settings.
     *
     * @param string $key     What setting is being updated.
     * @param int|EE_Event $event Either the EE_Event object or id.
     * @param mixed $value The value being updated.
     *
     * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success
     * and false on failure. NOTE: If the meta_value passed to this function is the same as the value that is already in the database, this function returns false.
     */
    protected static function _update_cert_datetime_setting( $key, $datetime, $value ) {
        $datetime = $datetime instanceof EE_Event ? $datetime : EE_Registry::instance()->load_model( 'datetime' )->get_one_by_ID( (int) $datetime );

        if ( ! $datetime instanceof EE_datetime ) {
            return false;
        }
        $settings = $datetime->get_post_meta( 'ee_cert_settings', true );
        $settings = empty( $settings ) ? array() : $settings;
        $settings[$key] = $value;
        return $datetime->update_post_meta( 'ee_cert_settings', $settings );
    }
}
// End of file EE_Cert.class.php
// Location: wp-content/plugins/eea-cert/EE_Cert.class.php
