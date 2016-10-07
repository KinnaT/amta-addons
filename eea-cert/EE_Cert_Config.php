<?php
if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit('NO direct script access allowed'); }
/**
 * This file contains the definition of the EE_Certs Config
 *
 * @since 1.0.0
 * @package EE Certs
 * @subpackage config
 */

/**
 * Class defining the Certs Config object stored on EE_Registry::instance->CFG
 *
 * @since 1.0.0
 *
 * @package EE Certs
 * @subpackage config
 * @author Kinna Thompson
 */
class EE_Cert_Config extends EE_Config_Base {

    /**
     * Global default setting for whether login is required to register for an event.
     *
     * @since 1.0.0
     * @var bool
     */
    public $has_credits;


    /**
     * Global setting for what gets used for the registration page url.
     *
     * @since 1.1.3
     * @var
     */
    public $ce_credits;



    /**
     * This global option is used to indicate behaviour when a logged in user registers for an event, and what happens
     * to that user’s related contact, which in turn is related to the primary registration.
     *
     * When true (default):
     * - If the logged in user has never had a relationship set between the user and the contact record, the relationship
     * will be created on the initial registration between the contact for the primary registration and this user.
     * - On subsequent registrations by this user, the contact record from previous registrations for that user will be used
     * for the primary registration and ANY changes to that contact record will sync both with the contact record AND related
     * wp user details for that account.
     *
     * When false:
     * - If the logged in user has never had a relationship set between the user and the contact record, the relationship will
     * be created on the initial registration between the contact for the primary registration and this user.
     * - On subsequent registrations by this user, if the contact details for the primary registrant are changed (personal
     * question group), then a NEW contact record is created and there is NO relationship setup between this user and this new contact.
     * The existing contact relationship is preserved.
     *
     * The main difference between the two options is in the former (true) - EVERY registration by a logged in user is
     * attached to the same contact for the primary registration, and the user has a record of every event they've
     * registered for.
     * Whereas with the second option (false) - a record of events the user has registered for ONLY applies when the
     * personal questions for the primary registration have not been changed.
     *
     * @type bool
     */
    // public $sync_user_with_contact;


    /**
     * constructor
     * @since 1.0.0
     */
    public function __construct() {
        $this->has_credits = false;
        $this->ce_credits = '';
    }

} //end EE_Certs_Config
