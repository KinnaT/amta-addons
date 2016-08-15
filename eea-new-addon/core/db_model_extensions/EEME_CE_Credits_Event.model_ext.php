<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

/**
 *
 * EEME_Mock_Attendee extends EEM_Attendee and adds a function named 'new_func' onto it
 *
 * @package            Event Espresso
 * @subpackage
 * @author                Mike Nelson
 *
 */
class EEME_CE_Credits_Event extends EEME_Base{
    function __construct() {
        $this->_model_name_extended = 'Event';
        $this->_extra_tables = array(
            'CE_Credit_Event_Meta' => new EE_Secondary_Table('esp_ce_credit_event_meta', 'NATT_ID', 'ATT_ID')
        );
        $this->_extra_fields = array('New_Addon_Attendee_Meta'=>array(
            'NATT_ID'=> new EE_DB_Only_Int_Field('NATT_ID', __('CE Credit Event Meta Row ID','event_espresso'), false),
            'NATT_ID_fk'=>new EE_DB_Only_Int_Field('EVT_ID', __("Foreign Key to Event in Post Table", "event_espresso"), false),
            'EVT_'=>new EE_Foreign_Key_Int_Field('EVT_foobar', __("CE Credit", 'event_espresso'), true,0,'CE_credits')));
        $this->_extra_relations = array('CE_credits'=>new EE_Belongs_To_Relation());
        parent::__construct();
    }
    function ext_get_all_new_things( $arg1 = FALSE ){
        return $this->_->get_all(array(array('CE_credits.NEW_ID'=>$arg1)));
    }
}

class EEME_My_Addon_Event extends EEME_Base{
    function __construct() {
        $this->_model_name_extended = 'Event';
        parent::__construct();
    }
    /**
     * Gets all events attended by an attendee
     * @param int|EE_Attendee $attendee
     * @return EE_Event[]
     */
    function ext_get_all_events_for_attendee( $attendee = FALSE ){
        $attendee_ID = EEM_Attendee::instance()->ensure_is_ID($attendee);
        return $this->_->get_all(array(array('Registration.ATT_ID'=>$attendee_ID)));
    }
}
