<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
* Event Espresso
*
* Event Registration and Management Plugin for WordPress
*
* @ package         Event Espresso
* @ author            Seth Shoultes
* @ copyright     (c) 2008-2011 Event Espresso  All Rights Reserved.
* @ license         {@link http://eventespresso.com/support/terms-conditions/}   * see Plugin Licensing *
* @ link                 {@link http://www.eventespresso.com}
* @ since             $VID:$
*
* ------------------------------------------------------------------------
*
* Cert_Admin_Page_Init class
*
* This is the init for the Cert Addon Admin Pages.  See EE_Admin_Page_Init for method inline docs.
*
* @package            Event Espresso (cert addon)
* @subpackage        admin/Cert_Admin_Page_Init.core.php
* @author                Darren Ethier
*
* ------------------------------------------------------------------------
*/
class Cert_Admin_Page_Init extends EE_Admin_Page_Init  {

    /**
     *     constructor
     *
     * @access public
     * @return \Cert_Admin_Page_Init
     */
    public function __construct() {

        do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );

        define( 'CERT_PG_SLUG', 'espresso_cert' );
        define( 'CERT_LABEL', __( 'Cert', 'event_espresso' ));
        define( 'EE_CERT_ADMIN_URL', admin_url( 'admin.php?page=' . CERT_PG_SLUG ));
        define( 'EE_CERT_ADMIN_ASSETS_PATH', EE_CERT_ADMIN . 'assets' . DS );
        define( 'EE_CERT_ADMIN_ASSETS_URL', EE_CERT_URL . 'admin' . DS . 'cert' . DS . 'assets' . DS );
        define( 'EE_CERT_ADMIN_TEMPLATE_PATH', EE_CERT_ADMIN . 'templates' . DS );
        define( 'EE_CERT_ADMIN_TEMPLATE_URL', EE_CERT_URL . 'admin' . DS . 'cert' . DS . 'templates' . DS );

        parent::__construct();
        $this->_folder_path = EE_CERT_ADMIN;

    }





    protected function _set_init_properties() {
        $this->label = CERT_LABEL;
    }



    /**
    *        _set_menu_map
    *
    *        @access         protected
    *        @return         void
    */
    protected function _set_menu_map() {
        $this->_menu_map = new EE_Admin_Page_Sub_Menu( array(
            'menu_group' => 'addons',
            'menu_order' => 25,
            'show_on_menu' => EE_Admin_Page_Menu_Map::BLOG_ADMIN_ONLY,
            'parent_slug' => 'espresso_events',
            'menu_slug' => CERT_PG_SLUG,
            'menu_label' => CERT_LABEL,
            'capability' => 'administrator',
            'admin_init_page' => $this
        ));
    }



}
// End of file Cert_Admin_Page_Init.core.php
// Location: /wp-content/plugins/eea-cert/admin/cert/Cert_Admin_Page_Init.core.php
