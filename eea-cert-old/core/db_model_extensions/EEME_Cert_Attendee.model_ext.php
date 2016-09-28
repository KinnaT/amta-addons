<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

class EEME_Cert_Attendee extends EEME_Base{
    function __construct() {
        $this->_model_name_extended = 'Attendee';
        $this->_extra_tables = array(
            'Cert_Attendee_Meta' => new EE_Secondary_Table('esp_cert_attendee_meta', 'CATT_ID', 'ATT_ID', 'DTT_ID', 'ATT_ce')
        );
        $this->_extra_fields = array('Cert_Attendee_Meta'=>array(
            'CATT_ID'=> new EE_DB_Only_Int_Field('CATT_ID', __('Certificates Attendee Meta ID','event_espresso'), false),
            'ATT_ID_fk'=>new EE_DB_Only_Int_Field('ATT_ID', __("Foreign Key to Attendee in Post Table", "event_espresso"), false),
            'DTT_ID_fk'=>new EE_DB_Only_Int_Field('DTT_ID', __("Foreign Key to Datetime in esp_datetime", "event_espresso"), false),
            'ATT_ce'=>new EE_Foreign_Key_Int_Field('ATT_ce', __("Attendee CEs", 'event_espresso'), true, '', 'Attendee') ) );
        $this->_extra_relations = array('Attendee'=>new EE_Belongs_To_Relation());
        parent::__construct();
    }
/*        function ext_get_all_events_for_attendee( $attendee = FALSE ){
        $attendee_ID = EEM_Attendee::instance()->ensure_is_ID($attendee);
        return $this->_->get_all(array(array('Registration.ATT_ID'=>$attendee_ID)));
    }*/
}
