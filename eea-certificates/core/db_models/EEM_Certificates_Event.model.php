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
class EEM_Certificates_Event extends EEM_Base{
    // private instance of the EEM_Certificates_Thing object
    protected static $_instance = null;

    protected function __construct($timezone = null) {
        $this->_tables = array(
            'Certificates_Event'=>new EE_Primary_Table('esp_certificates_event_meta', 'CE_ID')
        );
        $this->_fields = array(
            'Certificates_Event'=>array(
                'NEW_CE_ID'=>new EE_Primary_Key_Int_Field('CE_ID', __('Certificates Datetime Meta Row ID', 'event_espresso'), false),
                'NEW_DTT_ID_fk'=>new EE_DB_Only_Int_Field('DTT_ID', __("Foreign Key to Datetime in Post Table", "event_espresso"), false),
                'NEW_DTT_ce'=>new EE_Integer_Field('DTT_ce', __("CE Credits", 'event_espresso'), false ),
                'NEW_ATT_ID' => new EE_WP_User_Field( 'ATT_ID', __( 'Foreign Key to Attendee', 'event_espresso' ), false ),
            )
        );
        $this->_model_relations = array(
            'Attendee' => new EE_Has_Many_Relation(),
            'Event' => new EE_Belongs_To_Relation()
        );
        parent::__construct($timezone);
    }
}

// End of file EEM_Certificates_Thing.model.php
