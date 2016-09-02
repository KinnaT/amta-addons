<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

class EEME_Certificates_Event extends EEME_Base{
function __construct() {
        $this->_model_name_extended = 'Event';
        $this->_extra_tables = array(
            'Certificates_Event_Meta' => new EE_Secondary_Table('esp_certificates_event_meta', 'CE_ID', 'DTT_ID', 'DTT_ce')
        );
        $this->_extra_fields = array('Certificates_Event_Meta'=>array(
            'CE_ID'=> new EE_DB_Only_Int_Field('CE_ID', __('Certificates Datetime Meta Row ID','event_espresso'), false),
            'DTT_ID_fk'=>new EE_DB_Only_Int_Field('DTT_ID', __("Foreign Key to Datetime in Post Table", "event_espresso"), false),
            'DTT_ce'=>new EE_Foreign_Key_Int_Field('DTT_ce', __("CE Credits", 'event_espresso'), true,0,'Certificates_Event_Meta')));
        $this->_extra_relations = array('Event'=>new EE_Belongs_To_Relation());
        parent::__construct();
    }
    function ext_get_all_new_things( $arg1 = FALSE ){
        return $this->_->get_all(array(array('Event.DTT_ID'=>$datetime_ID)));
    }
    function ext_get_all_events_for_attendee( $attendee = FALSE ){
        $attendee_ID = EEM_Attendee::instance()->ensure_is_ID($attendee);
        return $this->_->get_all(array(array('Registration.ATT_ID'=>$attendee_ID)));
    }
    function ext_get_ce_for_datetime( $datetime = FALSE ){
        $datetime_ID = EEM_Datetime::instance()->get_one(array(array('Event.DTT_ID' => $datetime) ) );
        return $this->_->get_all(array('Event.DTT_ID'));
    }
}
