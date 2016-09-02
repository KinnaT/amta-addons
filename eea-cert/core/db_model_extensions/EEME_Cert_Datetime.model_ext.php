<?php

if (!defined('EVENT_ESPRESSO_VERSION')) {
    exit('No direct script access allowed');
}

class EEME_Cert_Datetime extends EEME_Base{
function __construct() {
        $this->_model_name_extended = 'Datetime';
        $this->_extra_tables = array(
            'Cert_Datetime_Meta' => new EE_Secondary_Table('esp_cert_datetime_meta', 'CE_ID', 'DTT_ID', 'DTT_ce')
        );
        $this->_extra_fields = array('Cert_Datetime_Meta'=>array(
            'CE_ID'=> new EE_DB_Only_Int_Field('CE_ID', __('Cert Datetime Meta Row ID','event_espresso'), false),
            'DTT_ID_fk'=>new EE_DB_Only_Int_Field('DTT_ID', __("Foreign Key to Datetime in Post Table", "event_espresso"), false),
            'DTT_ce'=>new EE_Integer_Field('DTT_ce', __("CE Credits", 'event_espresso'), true, 0, 'Datetime') ) );
        $this->_extra_relations = array('Datetime'=>new EE_Belongs_To_Relation());
        parent::__construct();
    }
    function ext_get_ce_for_datetime( $datetime = FALSE ){
        $datetime_ID = EEM_Datetime::instance()->get_all(array(array('Datetime.DTT_ID' => $datetime) ) );
        return $this->_->get_all(array(array('Datetime.DTT_ID'=>$datetime)));
    }
}
