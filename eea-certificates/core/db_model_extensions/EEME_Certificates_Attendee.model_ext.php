<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

class EEME_Certificates_Attendee extends EEME_Base{
    function __construct() {
        $this->_model_name_extended = 'Attendee';
        $this->_extra_tables = array(
            'Certificates_Attendee_Meta' => new EE_Secondary_Table('esp_certificates_attendee_meta', 'NATT_ID', 'ATT_ID', 'DTT_ID', 'ATT_ce')
        );
        $this->_extra_fields = array('Certificates_Attendee_Meta'=>array(
            'NATT_ID'=> new EE_DB_Only_Int_Field('NATT_ID', __('Certificates Attendee Meta Row ID','event_espresso'), false),
            'NATT_ID_fk'=>new EE_DB_Only_Int_Field('ATT_ID', __("Foreign Key to Attendee in Post Table", "event_espresso"), false),
            'NDTT_ID_fk'=>new EE_DB_Only_Int_Field('DTT_ID', __("Foreign Key to Datetime in esp_datetime", "event_espresso"), false),
            'ATT_ce'=>new EE_Foreign_Key_Int_Field('ATT_ce', __("Attendee CEs", 'event_espresso'), true,0,'Certificates_Attendee')));
        $this->_extra_relations = array('Certificates_Attendee'=>new EE_Belongs_To_Relation());
        parent::__construct();
    }
 /*   function ext_get_all_new_things( $arg1 = FALSE ){
        return $this->_->get_all(array(array('Certificates_Thing.NEW_ID'=>$arg1)));
    }
        function ext_get_all_events_for_attendee( $attendee = FALSE ){
        $attendee_ID = EEM_Attendee::instance()->ensure_is_ID($attendee);
        return $this->_->get_all(array(array('Registration.ATT_ID'=>$attendee_ID)));
    }
    function ext_foobar( $arg1 = FALSE ){
        return $this->_->get_all(array(array('Transaction.TXN_ID'=>$arg1)));
    }
*/
}
