<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }

// define the plugin directory path and URL
define( 'EE_CERTIFICATES_BASENAME', plugin_basename( EE_CERTIFICATES_PLUGIN_FILE ) );
define( 'EE_CERTIFICATES_PATH', plugin_dir_path( __FILE__ ) );
define( 'EE_CERTIFICATES_URL', plugin_dir_url( __FILE__ ) );
define( 'EE_CERTIFICATES_ADMIN', EE_CERTIFICATES_PATH . 'admin' . DS . 'certificates' . DS );



/**
 *
 * Class  EE_Certificates
 *
 * @package            Event Espresso
 * @subpackage        eea-certificates
 * @author                Brent Christensen
 *
 */
Class  EE_Certificates extends EE_Addon {

    /**
     * this is not the place to perform any logic or add any other filter or action callbacks
     * this is just to bootstrap your addon; and keep in mind the addon might be DE-registered
     * in which case your callbacks should probably not be executed.
     * EED_Certificates is the place for most filter and action callbacks (relating
     * the the primary business logic of your addon) to be placed
     */
    public static function register_addon() {
        // register addon via Plugin API
        EE_Register_Addon::register(
            'Certificates',
            array(
                'version'                     => EE_CERTIFICATES_VERSION,
                'plugin_slug'             => 'espresso_certificates',
                'min_core_version' => EE_CERTIFICATES_CORE_VERSION_REQUIRED,
                'main_file_path'         => EE_CERTIFICATES_PLUGIN_FILE,
                'admin_path'             => EE_CERTIFICATES_ADMIN,
                'admin_callback'        => '',
                'config_class'             => 'EE_Certificates_Config',
                'config_name'         => 'EE_Certificates',
                'autoloader_paths' => array(
                    'EE_Certificates'                         => EE_CERTIFICATES_PATH . 'EE_Certificates.class.php',
                    'EE_Certificates_Config'             => EE_CERTIFICATES_PATH . 'EE_Certificates_Config.php',
                    'Certificates_Admin_Page'         => EE_CERTIFICATES_ADMIN . 'Certificates_Admin_Page.core.php',
                    'Certificates_Admin_Page_Init' => EE_CERTIFICATES_ADMIN . 'Certificates_Admin_Page_Init.core.php',
                ),
                'dms_paths'             => array( EE_CERTIFICATES_PATH . 'core' . DS . 'data_migration_scripts' . DS ),
                'module_paths'         => array( EE_CERTIFICATES_PATH . 'EED_Certificates.module.php' ),
                'shortcode_paths'     => array( EE_CERTIFICATES_PATH . 'EES_Certificates.shortcode.php' ),
                'widget_paths'         => array( EE_CERTIFICATES_PATH . 'EEW_Certificates.widget.php' ),
                // if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
                'pue_options'            => array(
                    'pue_plugin_slug'         => 'eea-certificates',
                    'plugin_basename'     => EE_CERTIFICATES_BASENAME,
                    'checkPeriod'                 => '24',
                    'use_wp_update'         => FALSE,
                ),
                'capabilities' => array(
                    'administrator' => array(
                        'edit_thing', 'edit_things', 'edit_others_things', 'edit_private_things'
                    ),
                ),
                'capability_maps' => array(
                    'EE_Meta_Capability_Map_Edit' => array(
                        'edit_thing',
                        array( 'Certificates_Event', 'edit_things', 'edit_others_things', 'edit_private_things' )
                    )
                ),
                'class_paths'                         => EE_CERTIFICATES_PATH . 'core' . DS . 'db_classes',
                'model_paths'                     => EE_CERTIFICATES_PATH . 'core' . DS . 'db_models',
                'class_extension_paths'         => EE_CERTIFICATES_PATH . 'core' . DS . 'db_class_extensions',
                'model_extension_paths'     => EE_CERTIFICATES_PATH . 'core' . DS . 'db_model_extensions',
                //note for the mock we're not actually adding any custom cpt stuff yet.
                'custom_post_types'             => array(),
                'custom_taxonomies'         => array(),
                'default_terms'                     => array()
            )
        );
    }






}
// End of file EE_Certificates.class.php
// Location: wp-content/plugins/eea-certificates/EE_Certificates.class.php
