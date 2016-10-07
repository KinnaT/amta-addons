<?php

if (!defined('ABSPATH'))
    exit('No direct script access allowed');

//define constants
define( 'EE_CERT_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_CERT_URL', plugin_dir_url( __FILE__ ) );
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
        // register addon via Plugin API
        EE_Register_Addon::register(
                'EE_CERT', array(
                'version' => EE_CERT_VERSION,
                'min_core_version' => EE_CERT_MIN_CORE_VERSION_REQUIRED,
                'main_file_path' => EE_CERT_PLUGIN_FILE,
                'config_class' => 'EE_Cert_Config',
                'config_name' => 'cert',
                'admin_callback' => 'additional_admin_hooks',
                'module_paths' => array(
                    // EE_CERT_PATH . 'EED_Cert_SPCO.module.php',
                    EE_CERT_PATH . 'EED_Cert_Admin.module.php',
                    EE_CERT_PATH . 'EED_Cert_Ticket_Selector.module.php',
                 ),
                'shortcode_paths' => array(
                    EE_CERT_PATH . 'EES_Espresso_Cert.shortcode.php'
                ),
               // 'dms_paths' => array( EE_CERT_PATH . 'core/data_migration_scripts' ),
                'autoloader_paths' => array(
                    'EE_Cert_Config' => EE_CERT_PATH . 'EE_Cert_Config.php',
                    // 'EE_SPCO_Reg_Step_Cert_Login' => EE_CERT_PATH . 'EE_SPCO_Reg_Step_cert_Login.class.php',
                    // 'EE_DMS_2_0_0_user_option' => EE_CERT_PATH . 'core/data_migration_scripts/2_0_0_stages/EE_DMS_2_0_0_user_option.dmsstage.php'
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
    protected static function _add_message_type() {
        add_action('EE_Brewing_Regular___messages_caf', array( $this, 'register_cert_message_type' ) );
    }

    public function register_cert_message_type() {
            $setup_args = array(
            mtfilename => 'EE_Cert_message_type.class.php',
            'autoloadpaths' => array(
            EE_CERT_PATH . 'messages/'
            ),
            'messengers_to_activate_with' => array( 'html' ),
            'messengers_to_validate_with' => array( 'html' ),
            'force_activation' => true
        );
    EE_Register_Message_Type::register( 'cert', $setup_args );
    }

    /**
    * Takes care of adding all filters for template packs this message type connects with.
    *
    * @since 1.0.0
    *
    * @return void.
    */
    protected static function _add_template_pack_filters() {
        add_filter( 'FHEE__EE_Messages_Template_Pack_Default__get_supports', array( 'EE_Cert', 'register_supports_for_default_template_pack' ), 10  );
        add_filter( 'FHEE__EE_Template_Pack___get_specific_template__filtered_base_path', array( 'EE_Cert', 'register_base_path_for_cert_template' ), 10, 6 );
        add_filter( 'FHEE__EE_Messages_Template_Pack__get_variation__base_path_or_url', array( 'EE_Cert', 'get_cert_css_path_or_url' ), 10, 8 );
        add_filter( 'FHEE__EE_Messages_Template_Pack__get_variation__base_path', array( 'EE_Cert', 'get_cert_css_path_or_url' ), 10, 8 );
    }


        public static function register_base_path_for_cert_templates( $base_path, $messenger, $message_type, $field, $context, $template_pack ) {
        if ( ! $template_pack instanceof EE_Messages_Template_Pack_Default || ! $message_type instanceof EE_Certificate_message_type ) {
            return $base_path; //we're only setting up default templates for the default pack or for ticketing message type or ticket notice message type.
        }

        return EE_CERT_PATH . 'messages/templates/';
    }




    public static function get_cert_css_path_or_url( $base_path_or_url, $messenger, $message_type, $type, $variation, $url, $file_extension, $template_pack ) {
        if ( ! $template_pack instanceof EE_Messages_Template_Pack_Default || $messenger != 'html' || $message_type != 'cert' ) {
            return $base_path_or_url;
        }

        return  self::_get_cert_path_or_url( $url );
    }
        private static function _get_cert_path_or_url( $url = FALSE ) {
        return $url ? EE_CERT_PATH . 'messages/templates/' : EE_CERT_PATH . 'messages/templates/';
    }

        /**
     * Adds the cert message type to the supports array for the default template pack.
     *
     * @since %VER%
     *
     * @param array  $supports Original "supports" value for default template pack.
     * @return array  new supports value.
     */
    public static function register_supports_for_default_template_pack( $supports ) {
        $supports['html'][] = 'cert';
        return $supports;
    }




    /**
     *  additional admin hooks
     */
    public function additional_admin_hooks() {
        if ( is_admin() && ! EE_Maintenance_Mode::instance()->level() ) {
            add_filter( 'plugin_action_links', array( $this, 'plugin_actions' ), 10, 2 );
        }
    }




    /**
     * plugin_actions
     *
     * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
     * @param $links
     * @param $file
     * @return array
     */
    public function plugin_actions( $links, $file ) {
        if ( $file === EE_CERT_BASENAME ) {
            array_unshift( $links, '<a href="admin.php?page=espresso_registration_form&action=cert_settings">' . __('Settings') . '</a>' );
        }
        return $links;
    }




    /**
     * other helper methods
     */


    /**
     * Used to get a user id for a given EE_Attendee id.
     * If none found then null is returned.
     *
     * @param int     $att_id The attendee id to find a user match with.
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
     * used to determine if forced login is turned on for the event or not.
     *
     * @param int|EE_Event $event Either event_id or EE_Event object.
     *
     * @return bool   true YES forced login turned on false NO forced login turned off.
     */
    public static function is_event_has_credits( $event ) {
        return self::_get_cert_event_setting( 'has_credits', $event );
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
    protected static function _get_cert_event_setting( $key, $event ) {
        //any global defaults?
        $config = isset( EE_Registry::instance()->CFG->addons->cert ) ? EE_Registry::instance()->CFG->addons->cert : false;
        $global_default = array(
            'has_credits' => $config && isset( $config->has_credits ) ? $config->has_credits : false,
            );


        $event = $event instanceof EE_Event ? $event : EE_Registry::instance()->load_model( 'Event' )->get_one_by_ID( (int) $event );
        $settings = $event instanceof EE_Event ? $event->get_post_meta( 'ee_cert_settings', true ) : array();
        if ( ! empty( $settings ) ) {
            $value =  isset( $settings[$key] ) ? $settings[$key] : $global_default[$key];

            //since post_meta *might* return an empty string.  If the default global value is boolean, then let's make sure we cast the value returned from the post_meta as boolean in case its an empty string.
            return is_bool( $global_default[$key] ) ? (bool) $value : $value;
        }
        return $global_default[$key];
    }


    /**
     * used to update the "has credits" setting for an event.
     *
     * @param int|EE_Event $event Either the EE_Event object or int.
     * @param bool $has_credits value.  If turning off you can just not send.
     *
     * @throws EE_Error (via downstream activity)
     * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success
     *                          and false on failure. NOTE: If the meta_value passed to this function is the
     *                          same as the value that is already in the database, this function returns false.
     */
    public static function update_event_has_credits( $event, $has_credits = false ) {
        return self::_update_cert_event_setting( 'has_credits', $event, $has_credits );
    }


    /**
     * used to update the cert event specific settings.
     *
     * @param string $key     What setting is being updated.
     * @param int|EE_Event $event Either the EE_Event object or id.
     * @param mixed $value The value being updated.
     *
     * @return mixed Returns meta_id if the meta doesn't exist, otherwise returns true on success
     *                          and false on failure. NOTE: If the meta_value passed to this function is the
     *                          same as the value that is already in the database, this function returns false.
     */
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
