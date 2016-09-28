<?php if ( ! defined('EVENT_ESPRESSO_VERSION')) { exit('No direct script access allowed'); }
/**
 * Event Espresso
 *
 * Event Registration and Ticketing Management Plugin for WordPress
 *
 * @ package            Event Espresso
 * @ author                Event Espresso
 * @ copyright        (c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license            http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link                    http://www.eventespresso.com
 * @ version             $VID:$
 *
 * ------------------------------------------------------------------------
 */
 /**
 *
 * Class EE_Cert_Config
 *
 * Description
 *
 * @package         Event Espresso
 * @subpackage    core
 * @author                Brent Christensen
 * @since                $VID:$
 *
 */

class EE_Cert_Config extends EE_Config_Base {

    public $has_credits;

    /**
     * Global setting for what gets used for the registration page url.
     *
     * @since 1.1.3
     * @var
     */
    public $registration_page;


    public $ce_credits;

    /**
     * constructor
     * @since 1.0.0
     */
    public function __construct() {
        $this->has_credits = false;
        $this->ce_credits = '';
    }
}

// End of file EE_Cert_Config.php
// Location: /wp-content/plugins/eea-cert/EE_Cert_Config.php
