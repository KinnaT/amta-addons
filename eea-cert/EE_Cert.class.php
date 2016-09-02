<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }

// define the plugin directory path and URL
define( 'EE_CERT_BASENAME', plugin_basename( EE_CERT_PLUGIN_FILE ) );
define( 'EE_CERT_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_CERT_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_CERT_ADMIN', EE_CERT_PATH . 'admin' . DS . 'cert' . DS );



/**
 *
 * Class  EE_Cert
 *
 * @package            Event Espresso
 * @subpackage        eea-cert
 * @author                Brent Christensen
 *
 */
Class  EE_Cert extends EE_Addon {

    /**
     * this is not the place to perform any logic or add any other filter or action callbacks
     * this is just to bootstrap your addon; and keep in mind the addon might be DE-registered
     * in which case your callbacks should probably not be executed.
     * EED_Cert is the place for most filter and action callbacks (relating
     * the the primary business logic of your addon) to be placed
     */
    public static function register_addon() {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Cert',
            array(
                'version'                     => EE_CERT_VERSION,
                'plugin_slug'             => 'espresso_cert',
                'min_core_version' => EE_CERT_CORE_VERSION_REQUIRED,
                'main_file_path'         => EE_CERT_PLUGIN_FILE,
                'admin_path'             => EE_CERT_ADMIN,
                'admin_callback'        => '',
                'config_class'             => 'EE_Cert_Config',
                'config_name'         => 'EE_Cert',
                'autoloader_paths' => array(
                    'EE_Cert'                         => EE_CERT_PATH . 'EE_Cert.class.php',
                    'EE_Cert_Config'             => EE_CERT_PATH . 'EE_Cert_Config.php',
                    'Cert_Admin_Page'         => EE_CERT_ADMIN . 'Cert_Admin_Page.core.php',
                    'Cert_Admin_Page_Init' => EE_CERT_ADMIN . 'Cert_Admin_Page_Init.core.php',
                ),
                'dms_paths'             => array( EE_CERT_PATH . 'core' . DS . 'data_migration_scripts' . DS ),
                'module_paths'         => array( EE_CERT_PATH . 'EED_Cert.module.php' ),
                'shortcode_paths'     => array( EE_CERT_PATH . 'EES_Cert.shortcode.php' ),
                'widget_paths'         => array( EE_CERT_PATH . 'EEW_Cert.widget.php' ),
                // if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
                'pue_options'            => array(
                    'pue_plugin_slug'         => 'eea-cert',
                    'plugin_basename'     => EE_CERT_BASENAME,
                    'checkPeriod'                 => '24',
                    'use_wp_update'         => FALSE,
                ),
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
                'default_terms'                     => array()
            )
        );
    }






}
// End of file EE_Cert.class.php
// Location: wp-content/plugins/eea-cert/EE_Cert.class.php
