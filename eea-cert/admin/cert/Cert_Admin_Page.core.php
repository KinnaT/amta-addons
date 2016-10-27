<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }
/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @package        Event Espresso
 * @author        Event Espresso
 * @copyright    (c)    2009-2014 Event Espresso All Rights Reserved.
 * @license        http://eventespresso.com/support/terms-conditions/  ** see Plugin Licensing **
 * @link            http://www.eventespresso.com
 * @version        EE4
 *
 * ------------------------------------------------------------------------
 *
 * Cert_Admin_Page
 *
 * This contains the logic for setting up the Cert Addon Admin related pages.  Any methods without PHP doc comments have inline docs with parent class.
 *
 *
 * @package        Cert_Admin_Page (cert addon)
 * @subpackage    admin/Cert_Admin_Page.core.php
 * @author        Kinna Thompson
 *
 * ------------------------------------------------------------------------
 */
class Cert_Admin_Page extends EE_Admin_Page {


    protected function _init_page_props() {
        $this->page_slug = CERT_PG_SLUG;
        $this->page_label = CERT_LABEL;
        $this->_admin_base_url = EE_CERT_ADMIN_URL;
        $this->_admin_base_path = EE_CERT_ADMIN;
    }

    protected function _ajax_hooks() {}

    protected function _define_page_props() {
        $this->_admin_page_title = CERT_LABEL;
        $this->_labels = array(
            'publishbox' => __('Update Settings', 'event_espresso')
            );
    }

    protected function _set_page_routes() {
        $this->_page_routes = array(
            'default' => '_legacy',
            'settings' => '_settings',
            'update_settings' => array(
                'func' => '_update_settings',
                'noheader' => TRUE
                ),
            );
    }

    protected function _set_page_config() {

        $this->_page_config = array(
            'default' => array(
                'nav' => array(
                    'label' => __('View Legacy Certificates', 'event_espresso'),
                    'order' => 10
                    ),
                // 'metaboxes' => array_merge( $this->_default_espresso_metaboxes, array( '_publish_post_box') ),
                'require_nonce' => FALSE
                ),
            'settings' => array(
                'nav' => array(
                    'label' => __('Settings', 'event_espresso'),
                    'order' => 20
                    ),
                'metaboxes' => array_merge( $this->_default_espresso_metaboxes, array( '_publish_post_box' ) ),
                'require_nonce' => FALSE
                ),
            );
    }

    protected function _add_screen_options() {}
    protected function _add_screen_options_default() {}
    protected function _add_feature_pointers() {}
    public function load_scripts_styles() {} // Probably remove?

    public function admin_init() {}
    public function admin_notices() {}
    public function admin_footer_scripts() {}


    protected function _legacy() {
        $this->_settings_page( 'cert_legacy_certs.template.php' );
    }

    protected function _settings() {
        $this->_settings_page( 'cert_settings.template.php' );
    }

    /**
     * _settings_page
     * @param $template
     */
    protected function _settings_page( $template ) {
        $this->_template_args['cert_config'] = EE_Config::instance()->get_config( 'addons', 'EE_Cert', 'EE_Cert_Config' );
        $this->_template_args['values'] = array(
                array('id' => false, 'text' => __('No', 'event_espresso')),
                array('id' => true, 'text' => __('Yes', 'event_espresso'))
        );
        $this->_template_args['return_action'] = $this->_req_action;
        $this->_template_args['reset_url'] = EE_Admin_Page::add_query_args_and_nonce(array('action'=> 'reset_settings','return_action'=>$this->_req_action), EE_CERT_ADMIN_URL);
        $this->_set_add_edit_form_tags( 'update_settings' );
        $this->_set_publish_post_box_vars( NULL, FALSE, FALSE, NULL, FALSE);
        $this->_template_args['admin_page_content'] = EEH_Template::display_template( EE_CERT_ADMIN_TEMPLATE_PATH . $template, $this->_template_args, TRUE );
        $this->display_admin_page_with_sidebar();
    }

    protected function _update_settings(){
        if(isset($_POST['reset']) && $_POST['reset'] == '1'){
            $config = new EE_Cert_Config();
            $count = 1;
        }else{
            $config = EE_Config::instance()->get_config( 'addons', 'EE_Cert', 'EE_Cert_Config' );
            $count=0;
                        $cert_req_data = stripslashes_deep( $this->_req_data[ 'cert'] );
            foreach( $cert_req_data as $top_level_key => $top_level_value){
                if(is_array($top_level_value)){
                    foreach($top_level_value as $second_level_key => $second_level_value){
                        if(property_exists($config,$top_level_key) && property_exists($config->$top_level_key, $second_level_key)
                            && $second_level_value != $config->$top_level_key->$second_level_key){
                            $config->$top_level_key->$second_level_key = $this->_sanitize_config_input($top_level_key,$second_level_key,$second_level_value);
                            $count++;
                        }
                    }
                }else{
                    if(property_exists($config, $top_level_key) && $top_level_value != $config->$top_level_key){
                        $config->$top_level_key = $this->_sanitize_config_input($top_level_key, NULL, $top_level_value);
                        $count++;
                    }
                }
            }
        }
        EE_Config::instance()->update_config( 'addons', 'EE_Cert', $config );
        $this->_redirect_after_action($count, 'Settings', 'updated', array('action' => $this->_req_data['return_action']));
    }

    /**
     * resets the cert data and redirects to where they came from
     */
//    protected function _reset_settings(){
//        EE_Config::instance()->addons['cert'] = new EE_Cert_Config();
//        EE_Config::instance()->update_espresso_config();
//        $this->_redirect_after_action(1, 'Settings', 'reset', array('action' => $this->_req_data['return_action']));
//    }
    private function _sanitize_config_input($top_level_key,$second_level_key,$value){
        $sanitization_methods = array(
            'basic'=>array(
                'enable_certs'=>'bool',
                'default_credits'=>'int' ),
            'display'=>array(
                'cert_company'=>'plaintext',
                'company_phone'=>'int' )
            );
        $sanitization_method = NULL;
        if(isset($sanitization_methods[$top_level_key]) &&
                $second_level_key === NULL &&
                ! is_array($sanitization_methods[$top_level_key]) ){
            $sanitization_method = $sanitization_methods[$top_level_key];
        }elseif(is_array($sanitization_methods[$top_level_key]) && isset($sanitization_methods[$top_level_key][$second_level_key])){
            $sanitization_method = $sanitization_methods[$top_level_key][$second_level_key];
        }
//        echo "$top_level_key [$second_level_key] with value $value will be sanitized as a $sanitization_method<br>";
        switch($sanitization_method){
            case 'bool':
                return (boolean)intval($value);
            case 'plaintext':
                return wp_strip_all_tags($value);
            case 'int':
                return intval($value);
            case 'html':
                return $value;
            default:
                $input_name = $second_level_key == NULL ? $top_level_key : $top_level_key."[".$second_level_key."]";
                EE_Error::add_error(sprintf(__("Could not sanitize input '%s' because it has no entry in our sanitization methods array", "event_espresso"),$input_name), __FILE__, __FUNCTION__, __LINE__ );
                return NULL;

        }
    }





} //ends Forms_Admin_Page class
