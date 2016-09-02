<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/*
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Event Espresso
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link				http://www.eventespresso.com
 * @ version		$VID:$
 *
 * ------------------------------------------------------------------------
 */
/**
 * Class  EED_Certificates
 * 
 * This is where miscellaneous action and filters callbacks should be setup to
 * do your addon's business logic (that doesn't fit neatly into one of the
 * other classes in the mock addon)
 *
 * @package			Event Espresso
 * @subpackage		eea-certificates
 * @author 				Brent Christensen
 *
 * ------------------------------------------------------------------------
 */
class EED_Certificates extends EED_Module {

	/**
	 * @var 		bool
	 * @access 	public
	 */
	public static $shortcode_active = FALSE;



	/**
	 * @return EED_Certificates
	 */
	public static function instance() {
		return parent::get_instance( __CLASS__ );
	}



	 /**
	  * 	set_hooks - for hooking into EE Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks() {
		 EE_Config::register_route( 'certificates', 'EED_Certificates', 'run' );
	 }

	 /**
	  * 	set_hooks_admin - for hooking into EE Admin Core, other modules, etc
	  *
	  *  @access 	public
	  *  @return 	void
	  */
	 public static function set_hooks_admin() {
		 // ajax hooks
		 add_action( 'wp_ajax_get_certificates', array( 'EED_Certificates', 'get_certificates' ));
		 add_action( 'wp_ajax_nopriv_get_certificates', array( 'EED_Certificates', 'get_certificates' ));
	 }

	 public static function get_certificates(){
		 echo json_encode( array( 'response' => 'ok', 'details' => 'you have made an ajax request!') );
		 die;
	 }



	/**
	 *    config
	 *
	 * @return EE_Certificates_Config
	 */
	public function config(){
		// config settings are setup up individually for EED_Modules via the EE_Configurable class that all modules inherit from, so
		// $this->config();  can be used anywhere to retrieve it's config, and:
		// $this->_update_config( $EE_Config_Base_object ); can be used to supply an updated instance of it's config object
		// to piggy back off of the config setup for the base EE_Certificates class, just use the following (note: updates would have to occur from within that class)
		return EE_Registry::instance()->addons->EE_Certificates->config();
	}






	 /**
	  *    run - initial module setup
	  *
	  * @access    public
	  * @param  WP $WP
	  * @return    void
	  */
	 public function run( $WP ) {
		 add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ));
	 }






	/**
	 * 	enqueue_scripts - Load the scripts and css
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function enqueue_scripts() {
		//Check to see if the certificates css file exists in the '/uploads/espresso/' directory
		if ( is_readable( EVENT_ESPRESSO_UPLOAD_DIR . "css/certificates.css")) {
			//This is the url to the css file if available
			wp_register_style( 'espresso_certificates', EVENT_ESPRESSO_UPLOAD_URL . 'css/espresso_certificates.css' );
		} else {
			// EE certificates style
			wp_register_style( 'espresso_certificates', EE_CERTIFICATES_URL . 'css/espresso_certificates.css' );
		}
		// certificates script
		wp_register_script( 'espresso_certificates', EE_CERTIFICATES_URL . 'scripts/espresso_certificates.js', array( 'jquery' ), EE_CERTIFICATES_VERSION, TRUE );

		// is the shortcode or widget in play?
		if ( EED_Certificates::$shortcode_active ) {
			wp_enqueue_style( 'espresso_certificates' );
			wp_enqueue_script( 'espresso_certificates' );
		}
	}
 }
// End of file EED_Certificates.module.php
// Location: /wp-content/plugins/eea-certificates/EED_Certificates.module.php
