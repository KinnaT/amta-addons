<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * This file contains the certificates message class for the EE Certificates addon
 *
 * @since 1.0.0
 * @package  EE Certificates
 * @subpackage modules, admin
 * @author         Kinna Thompson
 */


class EE_Certificate_message_type extends EE_message_type {

    public function __construct() {
        $this->name = 'cert';
        $this->description = __( 'Certificate message types are triggered by the certificates addon when attached to an applicable ticket.', 'event_espresso' );
        $this->label = array(
            'singular' => __( 'certificate', 'event_espresso' ),
            'plural' => __( 'certificates', 'event_espresso' )
            );
        $this->_master_templates = array( 'html' => 'receipt' );

        parent::__construct();
    }


    protected function _set_admin_pages() {
        $this->admin_registered_pages = array(
        'events_edit' => TRUE
        );
    }



    protected function _set_data_handler() {
        $this->_data_handler = 'Registration';
       // $this->_single_message = $this->_data instanceof EE_Registration ? true : false;
    }

    protected function _get_data_for_context( $context, EE_Registration $registration, $id ) {
        return $registration;
    }



    protected function _set_admin_settings_fields() {
        $this->_admin_settings_fields = array();
    }

    protected function _set_contexts() {
    $this->_context_label = array(
        'label' => __('recipient', 'event_espresso'),
        'plural' => __('recipients', 'event_espresso'),
        'description' => __('Recipient\'s are who will receive the message.', 'event_espresso')
    );

    $this->_contexts = array(
        'attendee' => array(
            'label' => __('Registrant', 'event_espresso'),
            'description' => __('This template goes to selected registrants.', 'event_espresso')
        )
    );
    }

    protected function _set_valid_shortcodes() {
        parent::_set_valid_shortcodes();

        $included_shortcodes = array(
            'recipient_details', 'organization', 'event', 'ticket', 'venue', 'primary_registration_details', 'event_author', 'email','event_meta', 'recipient_list', 'datetime_list', 'question_list', 'datetime', 'question'
            );

        //add shortcodes to the single 'registrant' context we have for the cert message type
        $this->_valid_shortcodes['registrant'] = $included_shortcodes;

    }


    protected function _set_with_messengers() {
        $this->_with_messengers = array('html' => 'html');
    }

}
