<?php

if (!defined('EVENT_ESPRESSO_VERSION'))
    exit('No direct script access allowed');

/**
 *
 * EEM_Certificates_Thing
 *
 * @package            Event Espresso
 * @subpackage
 * @author                Mike Nelson
 *
 */
class EEM_Certificates_Thing extends EEM_Base{
    // private instance of the EEM_Certificates_Thing object
    protected static $_instance = null;

    protected function __construct($timezone = null) {
        $this->_tables = array(
            'CE_credits'=>new EE_Primary_Table('esp_ce_credits', 'NEW_ID')
        );
        $this->_fields = array(
            'Certificates_Thing'=>array(
                'NEW_ID'=>new EE_Primary_Key_Int_Field('CREDITS_ID', __("CE Credits ID", 'event_espresso')),
                'NEW_name' => new EE_Plain_Text_Field('CREDITS_name', __('CE Credits', 'event_espresso'), false),
                'NEW_wp_user' => new EE_WP_User_Field( 'NEW_wp_user', __( 'Things Creator', 'event_espresso' ), false )
            )
        );
        $this->_model_relations = array(
            'Attendee' => new EE_Has_Many_Relation(),
            'WP_User' => new EE_Belongs_To_Relation()
        );
        parent::__construct($timezone);
    }
}

// End of file EEM_Certificates_Thing.model.php
