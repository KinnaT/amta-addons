<?php
if (!defined('EVENT_ESPRESSO_VERSION') )
    exit('NO direct script access allowed');

/**
 * Event Espresso
 *
 * Event Registration and Management Plugin for Wordpress
 *
 * @package        Event Espresso
 * @author        Seth Shoultes
 * @copyright    (c)2009-2012 Event Espresso All Rights Reserved.
 * @license        http://eventespresso.com/support/terms-conditions/  ** see Plugin Licensing **
 * @link        http://www.eventespresso.com
 * @version        4.0
 *
 * ------------------------------------------------------------------------
 *
 * espresso_events_Datetime_Hooks
 * Hooks various messages logic so that it runs on indicated Events Admin Pages.
 * Commenting/docs common to all children classes is found in the EE_Admin_Hooks parent.
 *
 * Definitely not finished or functional.
 *
 * @package        espresso_events_Datetime_Hooks
 * @subpackage    caffeinated/admin/new/pricing/espresso_events_Datetime_Hooks.class.php
 * @author        Kinna Thompson
 *
 * ------------------------------------------------------------------------
 */
class espresso_events_Datetime_Hooks extends EE_Admin_Hooks {

    /**
     * Holds the status of whether an event is currently being created (true) or edited (false)
     * @access protected
     * @var bool
     */
    protected $_is_creating_event;

    /**
     * Used to contain the format strings for date and time that will be used for php date and
     * time.
     * Is set in the _set_hooks_properties() method.
     * @var array
     */
    protected $_date_format_strings;



    protected function _set_hooks_properties() {
        $this->_name = 'datetime';

        //capability check
        if ( ! EE_Registry::instance()->CAP->current_user_can( 'ee_read_edit_event', 'advanced_ticket_datetime_metabox' ) ) {
            return;
        }

        /**
         * Format strings for date and time.  Defaults are existing behaviour from 4.1.
         * Note, that if you return null as the value for 'date', and 'time' in the array, then
         * EE will automatically use the set wp_options, 'date_format', and 'time_format'.
         *
         * @since 4.6.7
         *
         * @var array  Expected an array returned with 'date' and 'time' keys.
         */
        $this->_date_format_strings = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___set_hooks_properties__date_format_strings', array(
                'date' => 'Y-m-d',
                'time' => 'h:i a'
            ));

        //validate
        $this->_date_format_strings['date'] = isset( $this->_date_format_strings['date'] ) ? $this->_date_format_strings['date'] : null;
        $this->_date_format_strings['time'] = isset( $this->_date_format_strings['time'] ) ? $this->_date_format_strings['time'] : null;

        //validate format strings
        $format_validation = EEH_DTT_Helper::validate_format_string( $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] );
        if ( is_array( $format_validation ) ) {
            $msg = '<p>' . sprintf( __( 'The format "%s" was likely added via a filter and is invalid for the following reasons:', 'event_espresso' ), $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] ) . '</p><ul>';
            foreach ( $format_validation as $error ) {
                $msg .= '<li>' . $error . '</li>';
            }
            $msg .= '</ul></p><p>' . sprintf( __( '%sPlease note that your date and time formats have been reset to "Y-m-d" and "h:i a" respectively.%s', 'event_espresso' ), '<span style="color:#D54E21;">', '</span>' ) . '</p>';
            EE_Error::add_attention( $msg, __FILE__, __FUNCTION__, __LINE__ );
            $this->_date_format_strings = array(
                'date' => 'Y-m-d',
                'time' => 'h:i a'
                );
        }


        $this->_scripts_styles = array(
            'registers' => array(
                'ee-tickets-datetimes-css' => array(
                    'url' => PRICING_ASSETS_URL . 'event-tickets-datetimes.css',
                    'type' => 'css'
                    ),
                'ee-dtt-ticket-metabox' => array(
                    'url' => PRICING_ASSETS_URL . 'ee-datetime-ticket-metabox.js',
                    'depends' => array('ee-datepicker', 'ee-dialog', 'underscore')
                    )
                ),
            'deregisters' => array(
                'event-editor-css' => array('type' => 'css' ),
                'event-datetime-metabox' => array('type' => 'js')
                ),
            'enqueues' => array(
                'ee-tickets-datetimes-css' => array( 'edit', 'create_new' ),
                'ee-dtt-ticket-metabox' => array( 'edit', 'create_new' )
                ),
            'localize' => array(
                'ee-dtt-ticket-metabox' => array(
                    'DTT_CONVERTED_FORMATS' => EEH_DTT_Helper::convert_php_to_js_and_moment_date_formats( $this->_date_format_strings['date'], $this->_date_format_strings['time'] ),
                    'DTT_START_OF_WEEK' => array( 'dayValue' => (int) get_option( 'start_of_week' ) )
                    )
                )
            );
        add_action('AHEE__EE_Admin_Page_CPT__do_extra_autosave_stuff__after_Extend_Events_Admin_Page', array( $this, 'autosave_handling' ), 10 );
        add_filter('FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks', array( $this, 'caf_updates' ), 10 );
    }

    public function caf_updates( $update_callbacks ) {
        foreach ( $update_callbacks as $key => $callback ) {
            if ( $callback[1] == '_default_tickets_update' )
                unset( $update_callbacks[$key] );
        }

        $update_callbacks[] = array( $this, 'dtt_and_tickets_caf_update' );
        return $update_callbacks;
    }



    /**
     * Handles saving everything related to Tickets (datetimes, tickets, prices)
     * @param  EE_Event $evtobj The Event object we're attaching data to
     * @param  array    $data   The request data from the form
     * @return bool             success or fail
     */
    public function dtt_and_tickets_caf_update( $evtobj, $data ) {
        //first we need to start with datetimes cause they are the "root" items attached to events.
        $saved_dtts = $this->_update_dtts( $evtobj, $data );
        //next tackle the tickets (and prices?)
        $this->_update_tkts( $evtobj, $saved_dtts, $data );
    }



    /**
     * update event_datetimes
     * @param  EE_Event     $evt_obj Event being updated
     * @param  array        $data    the request data from the form
     * @return EE_Datetime[]
     */
    protected function _update_dtts( $evt_obj, $data ) {
        $timezone = isset( $data['timezone_string'] ) ? $data['timezone_string'] : NULL;
        $saved_dtt_ids = array();
        $saved_dtt_objs = array();

        foreach ( $data['edit_event_datetimes'] as $row => $dtt ) {
            //trim all values to ensure any excess whitespace is removed.
            $dtt = array_map(
                function( $datetime_data ) {
                    return is_array( $datetime_data ) ? $datetime_data : trim( $datetime_data );
                },
                $dtt
            );
            $dtt['DTT_EVT_end'] = isset($dtt['DTT_EVT_end']) && ! empty( $dtt['DTT_EVT_end'] ) ? $dtt['DTT_EVT_end'] : $dtt['DTT_EVT_start'];
            $datetime_values = array(
                'DTT_ID'             => ! empty( $dtt['DTT_ID'] ) ? $dtt['DTT_ID'] : NULL,
                'DTT_name'             => ! empty( $dtt['DTT_name'] ) ? $dtt['DTT_name'] : '',
                'DTT_description'     => ! empty( $dtt['DTT_description'] ) ? $dtt['DTT_description'] : '',
                'DTT_EVT_start'     => $dtt['DTT_EVT_start'],
                'DTT_EVT_end'         => $dtt['DTT_EVT_end'],
                'DTT_reg_limit'     => empty( $dtt['DTT_reg_limit'] ) ? EE_INF : $dtt[ 'DTT_reg_limit' ],
                'DTT_order'         => ! isset( $dtt['DTT_order'] ) ? $row : $dtt['DTT_order'],
                // new values
                'DTT_has_credits'    => ! empty( $dtt['DTT_has_credits'] ) ? $dtt['DTT_has_credits'] : FALSE,
                'DTT_ce_credits'     => ! empty( $dtt['DTT_ce_credits'] ) ? $dtt['DTT_ce_credits'] : NULL,
            );

            //if we have an id then let's get existing object first and then set the new values.  Otherwise we instantiate a new object for save.

            if ( !empty( $dtt['DTT_ID'] ) ) {
                $DTM = EE_Registry::instance()->load_model('Datetime', array($timezone) )->get_one_by_ID($dtt['DTT_ID'] );

                //set date and time format according to what is set in this class.
                $DTM->set_date_format( $this->_date_format_strings['date'] );
                $DTM->set_time_format( $this->_date_format_strings['time'] );

                foreach ( $datetime_values as $field => $value ) {
                    $DTM->set( $field, $value );
                }

                // make sure the $dtt_id here is saved just in case after the add_relation_to() the autosave replaces it.
                // We need to do this so we dont' TRASH the parent DTT.(save the ID for both key and value to avoid duplications)
                $saved_dtt_ids[$DTM->ID()] = $DTM->ID();

            } else {
                $DTM = EE_Registry::instance()->load_class('Datetime', array( $datetime_values, $timezone ), FALSE, FALSE );

                //reset date and times to match the format
                $DTM->set_date_format( $this->_date_format_strings['date'] );
                $DTM->set_time_format( $this->_date_format_strings['time'] );
                foreach( $datetime_values as $field => $value ) {
                    $DTM->set( $field, $value );
                }
            }


            $DTM->save();
            $DTM = $evt_obj->_add_relation_to( $DTM, 'Datetime' );
            $evt_obj->save();

            //before going any further make sure our dates are setup correctly so that the end date is always equal or greater than the start date.
            if( $DTM->get_raw('DTT_EVT_start') > $DTM->get_raw('DTT_EVT_end') ) {
                $DTM->set('DTT_EVT_end', $DTM->get('DTT_EVT_start') );
                $DTM = EEH_DTT_Helper::date_time_add($DTM, 'DTT_EVT_end', 'days');
                $DTM->save();
            }

            //    now we have to make sure we add the new DTT_ID to the $saved_dtt_ids array
            // because it is possible there was a new one created for the autosave.
            // (save the ID for both key and value to avoid duplications)
            $saved_dtt_ids[$DTM->ID()] = $DTM->ID();
            $saved_dtt_objs[$row] = $DTM;

            //todo if ANY of these updates fail then we want the appropriate global error message.
        }

        //now we need to REMOVE any dtts that got deleted.  Keep in mind that this process will only kick in for DTT's that don't have any DTT_sold on them. So its safe to permanently delete at this point.
        $old_datetimes = explode(',', $data['datetime_IDs'] );
        $old_datetimes = $old_datetimes[0] == '' ? array() : $old_datetimes;

        if ( is_array( $old_datetimes ) ) {
            $dtts_to_delete = array_diff( $old_datetimes, $saved_dtt_ids );
            foreach ( $dtts_to_delete as $id ) {
                $id = absint( $id );
                if ( empty( $id ) )
                    continue;

                $dtt_to_remove = EE_Registry::instance()->load_model('Datetime')->get_one_by_ID($id);

                //remove tkt relationships.
                $related_tickets = $dtt_to_remove->get_many_related('Ticket');
                foreach ( $related_tickets as $tkt ) {
                    $dtt_to_remove->_remove_relation_to($tkt, 'Ticket');
                }

                $evt_obj->_remove_relation_to( $id, 'Datetime' );
                $dtt_to_remove->refresh_cache_of_related_objects();

            }
        }

        return $saved_dtt_objs;
    }

    /**
     * update datetimes
     * @param  EE_Event         $evtobj     Event object being updated
     * @param  EE_Datetime[]    $saved_dtts an array of datetime ids being updated
     * @param  array            $data       incoming request data
     * @return EE_Ticket[]
     */
    protected function _update_dtts( $evtobj, $saved_dtts, $data ) {

        $new_dtt = null;
        $new_default = null;
        //stripslashes because WP filtered the $_POST ($data) array to add slashes
        $data = stripslashes_deep($data);
        $timezone = isset( $data['timezone_string'] ) ? $data['timezone_string'] : NULL;
        $saved_tickets = $dtts_on_existing = array();
        $old_datetimes = isset( $data['datetime_IDs'] ) ? explode(',', $data['datetime_IDs'] ) : array();

        //load money helper

        foreach ( $data['edit_datetimes'] as $row => $dtt ) {

            $price_rows = is_array($data['edit_prices']) && isset($data['edit_prices'][$row]) ? $data['edit_prices'][$row] : array();

            $now = null;
            if ( empty( $dtt['DTT_start_date'] ) ) {
                //lets' use now in the set timezone.
                $now = new DateTime( 'now', new DateTimeZone( $evtobj->get_timezone() ) );
                $dtt['DTT_start_date'] = $now->format( $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] );
            }

            if ( empty( $tkt['DTT_end_date'] ) ) {
                /**
                 * set the DTT_end_date to the first datetime attached to the ticket.
                 */
                $first_dtt = $saved_dtts[reset( $tkt_dtt_rows )];
                $dtt['DTT_end_date'] = $first_dtt->start_date_and_time( $this->_date_format_strings['date'] . ' ' . $this->_date_format_string['time'] );
            }

            // if we have a TKT_ID then we need to get that existing TKT_obj and update it
            // we actually do our saves ahead of doing any add_relations to
            // because its entirely possible that this ticket wasn't removed or added to any datetime in the session
            // but DID have it's items modified.
            // keep in mind that if the TKT has been sold (and we have changed pricing information),
            // then we won't be updating the tkt but instead a new tkt will be created and the old one archived.
            if ( absint( $TKT_values['TKT_ID'] ) ) {
                $TKT = EE_Registry::instance()->load_model( 'Ticket', array( $timezone ) )->get_one_by_ID( $tkt['TKT_ID'] );
                if ( $TKT instanceof EE_Ticket ) {

                    $TKT = $this->_update_ticket_datetimes( $TKT, $saved_dtts, $dtts_added, $dtts_removed );
                    // are there any registrations using this ticket ?
                    $tickets_sold = $TKT->count_related(
                        'Registration',
                        array( array(
                                'STS_ID' => array( 'NOT IN', array( EEM_Registration::status_id_incomplete ) )
                        ) )
                    );

                    //set new values
                    foreach ( $TKT_values as $field => $value ) {
                        if ( $field === 'TKT_qty' ) {
                            $TKT->set_qty( $value );
                        } else {
                            $TKT->set( $field, $value );
                        }
                    }

                    //if $create_new_TKT is false then we can safely update the existing ticket.  Otherwise we have to create a new ticket.
                    if ( $create_new_TKT ) {
                        $new_tkt = $this->_duplicate_ticket( $TKT, $price_rows, $ticket_price, $base_price, $base_price_id );
                    }
                }

            } else {
                // no TKT_id so a new TKT
                $TKT = EE_Ticket::new_instance(
                    $TKT_values,
                    $timezone,
                    array( $this->_date_format_strings[ 'date' ], $this->_date_format_strings[ 'time' ]  )
                );
                if ( $TKT instanceof EE_Ticket ) {
                    // make sure ticket has an ID of setting relations won't work
                    $TKT->save();
                    $TKT = $this->_update_ticket_datetimes( $TKT, $saved_dtts, $dtts_added, $dtts_removed );
                    $update_prices = TRUE;
                }
            }
            //make sure any current values have been saved.
            //$TKT->save();

            //before going any further make sure our dates are setup correctly so that the end date is always equal or greater than the start date.
            if( $TKT->get_raw('TKT_start_date') > $TKT->get_raw('TKT_end_date') ) {
                $TKT->set('TKT_end_date', $TKT->get('TKT_start_date') );
                $TKT = EEH_DTT_Helper::date_time_add($TKT, 'TKT_end_date', 'days');
            }

        }

        return $saved_tickets;
    }


    public function autosave_handling( $event_admin_obj ) {
        return $event_admin_obj; //doing nothing for the moment.
        //todo when I get to this remember that I need to set the template args on the $event_admin_obj (use the set_template_args() method)

        /**
         * need to remember to handle TICKET DEFAULT saves correctly:  I've got two input fields in the dom:
         *
         * 1. TKT_is_default_selector (visible)
         * 2. TKT_is_default (hidden)
         *
         * I think we'll use the TKT_is_default for recording whether the ticket displayed IS a default ticket (on new event creations). Whereas the TKT_is_default_selector is for the user to indicate they want this ticket to be saved as a default.
         *
         * The tricky part is, on an initial display on create or edit (or after manually updating), the TKT_is_default_selector will always be unselected and the TKT_is_default will only be true if this is a create.  However, after an autosave, users will want some sort of indicator that the TKT HAS been saved as a default.. in other words we don't want to remove the check on TKT_is_default_selector. So here's what I'm thinking.
         * On Autosave:
         * 1. If TKT_is_default is true: we create a new TKT, send back the new id and add id to related elements, then set the TKT_is_default to false.
         * 2. If TKT_is_default_selector is true: we create/edit existing ticket (following conditions above as well).  We do NOT create a new default ticket.  The checkbox stays selected after autosave.
         * 3. only on MANUAL update do we check for the selection and if selected create the new default ticket.
         */
    }



    public function pricing_metabox() {
        $existing_datetime_ids = $existing_ticket_ids = $datetime_tickets = $ticket_datetimes = array();

        $evtobj = $this->_adminpage_obj->get_cpt_model_obj();

        //set is_creating_event property.
        $evtID = $evtobj->ID();
        $this->_is_creating_event = absint($evtID) != 0 ? FALSE : TRUE;

        //default main template args
        $main_template_args = array(
            'event_datetime_help_link' => EEH_Template::get_help_tab_link('event_editor_event_datetimes_help_tab', $this->_adminpage_obj->page_slug, $this->_adminpage_obj->get_req_action(), FALSE, FALSE ), //todo need to add a filter to the template for the help text in the Events_Admin_Page core file so we can add further help
            'existing_datetime_ids' => '',
            'total_dtt_rows' => 1,
            'add_new_dtt_help_link' => EEH_Template::get_help_tab_link('add_new_dtt_info', $this->_adminpage_obj->page_slug, $this->_adminpage_obj->get_req_action(), FALSE, FALSE ), //todo need to add this help info id to the Events_Admin_Page core file so we can access it here.
            'datetime_rows' => '',
            'show_tickets_container' => '',//$this->_adminpage_obj->get_cpt_model_obj()->ID() > 1 ? ' style="display:none;"' : '',
            'ticket_rows' => '',
            'existing_ticket_ids' => '',
            'total_ticket_rows' => 1,
            'ticket_js_structure' => '',
            'ee_collapsible_status' => ' ee-collapsible-open'//$this->_adminpage_obj->get_cpt_model_obj()->ID() > 0 ? ' ee-collapsible-closed' : ' ee-collapsible-open'
            );

        $timezone = $evtobj instanceof EE_Event ? $evtobj->timezone_string() : NULL;

        do_action( 'AHEE_log', __FILE__, __FUNCTION__, '' );

        /**
         * 1. Start with retrieving Datetimes
         * 2. For each datetime get related tickets
         * 3. For each ticket get related prices
         */

        $DTM = EE_Registry::instance()->load_model('Datetime', array($timezone) );
        $times = $DTM->get_all_event_dates( $evtID );



        $main_template_args['total_dtt_rows'] = count($times);

        /** @see https://events.codebasehq.com/projects/event-espresso/tickets/9486 for why we are counting $dttrow and then setting that on the Datetime object */
        $dttrow = 1;
        foreach ( $times as $time ) {
            $dttid = $time->get('DTT_ID');
            $time->set( 'DTT_order', $dttrow );
            $existing_datetime_ids[] = $dttid;

            //tickets attached
            $related_tickets = $time->ID() > 0 ? $time->get_many_related('Ticket', array( array( 'OR' => array( 'TKT_deleted' => 1, 'TKT_deleted*' => 0 ) ), 'default_where_conditions' => 'none', 'order_by' => array('TKT_order' => 'ASC' ) ) ) : array();

            //if there are no related tickets this is likely a new event OR autodraft
            // event so we need to generate the default tickets because dtts
            // ALWAYS have at least one related ticket!!.  EXCEPT, we dont' do this if there is already more than one
            // datetime on the event.
            if ( empty ( $related_tickets ) && count( $times ) < 2 ) {
                $related_tickets = EE_Registry::instance()->load_model('Ticket')->get_all_default_tickets();

                //this should be ordered by TKT_ID, so let's grab the first default ticket (which will be the main default) and ensure it has any default prices added to it (but do NOT save).
                $default_prices = EEM_Price::instance()->get_all_default_prices();

                $main_default_ticket = reset( $related_tickets );
                if ( $main_default_ticket instanceof EE_Ticket ) {
                    foreach ( $default_prices as $default_price ) {
                        if ( $default_price->is_base_price() ) {
                            continue;
                        }
                        $main_default_ticket->cache( 'Price', $default_price );
                    }
                }
            }


            //we can't actually setup rows in this loop yet cause we don't know all the unique tickets for this event yet (tickets are linked through all datetimes). So we're going to temporarily cache some of that information.

            //loop through and setup the ticket rows and make sure the order is set.
            foreach ( $related_tickets as $ticket ) {
                $tktid = $ticket->get('TKT_ID');
                $tktrow = $ticket->get('TKT_row');
                //we only want unique tickets in our final display!!
                if ( !in_array( $tktid, $existing_ticket_ids ) ) {
                    $existing_ticket_ids[] = $tktid;
                    $all_tickets[] = $ticket;
                }

                //temporary cache of this ticket info for this datetime for later processing of datetime rows.
                $datetime_tickets[$dttid][] = $tktrow;

                //temporary cache of this datetime info for this ticket for later processing of ticket rows.
                if ( !isset( $ticket_datetimes[$tktid] ) || ! in_array( $dttrow, $ticket_datetimes[$tktid] ) )
                    $ticket_datetimes[$tktid][] = $dttrow;
            }
            $dttrow++;
        }

        $main_template_args['total_ticket_rows'] = count( $existing_ticket_ids );
        $main_template_args['existing_ticket_ids'] = implode( ',', $existing_ticket_ids );
        $main_template_args['existing_datetime_ids'] = implode( ',', $existing_datetime_ids );

        //sort $all_tickets by order
        usort( $all_tickets, function( $a, $b ) {
            $a_order = (int) $a->get('TKT_order');
            $b_order = (int) $b->get('TKT_order');
            if ( $a_order == $b_order ) {
                return 0;
            }
            return ( $a_order < $b_order ) ? -1 : 1;
        });

        //then loop through all tickets for the ticket rows.
        $tktrow = 1;
        foreach ( $all_tickets as $ticket ) {
            $main_template_args['ticket_rows'] .= $this->_get_ticket_row( $tktrow, $ticket, $ticket_datetimes, $times, FALSE, $all_tickets );
            $tktrow++;
        }

        $main_template_args['ticket_js_structure'] = $this->_get_ticket_js_structure($times, $all_tickets);
        $template = PRICING_TEMPLATE_PATH . 'event_tickets_metabox_main.template.php';
        EEH_Template::display_template( $template, $main_template_args );
        return;
    }

    protected function _get_datetime_row( $dttrow, EE_Datetime $dtt, $datetime_tickets, $all_tickets, $default = FALSE, $all_dtts = array() ) {

        $dtt_display_template_args = array(
            'dtt_edit_row' => $this->_get_dtt_edit_row( $dttrow, $dtt, $default, $all_dtts ),
            'dtt_attached_tickets_row' => $this->_get_dtt_attached_tickets_row( $dttrow, $dtt, $datetime_tickets, $all_tickets, $default ),
            'dtt_row' => $default ? 'DTTNUM' : $dttrow
            );
        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_row_wrapper.template.php';
        return EEH_Template::display_template( $template, $dtt_display_template_args, TRUE);
    }



    /**
     * This method is used to generate a dtt fields  edit row.
     * The same row is used to generate a row with valid DTT objects and the default row that is used as the
     * skeleton by the js.
     *
     * @param int     $dttrow                               The row number for the row being generated.
     * @param mixed EE_Datetime|null $dtt      If not default row being generated, this must be a EE_Datetime
     *                                               object.
     * @param bool   $default                   Whether a default row is being generated or not.
     * @param EE_Datetime[] $all_dtts             This is the array of all datetimes used in the editor.
     *
     * @return string Generated edit row.
     */
    protected function _get_dtt_edit_row( $dttrow, $dtt, $default, $all_dtts ) {

        // if the incoming $dtt object is NOT an instance of EE_Datetime then force default to true.
        $default = ! $dtt instanceof EE_Datetime ? true : false;

        $template_args = array(
            'dtt_row' => $default ? 'DTTNUM' : $dttrow,
            'event_datetimes_name' => $default ? 'DTTNAMEATTR' : 'edit_event_datetimes',
            'edit_dtt_expanded' => '',//$this->_adminpage_obj->get_cpt_model_obj()->ID() > 0 ? '' : ' ee-edit-editing',
            'DTT_ID' => $default ? '' : $dtt->ID(),
            'DTT_name' => $default ? '' : $dtt->name(),
            'DTT_description' => $default ? '' : $dtt->description(),
            'DTT_EVT_start' => $default ? '' : $dtt->start_date( $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] ),
            'DTT_EVT_end' => $default ? '' : $dtt->end_date( $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] ),
            'DTT_reg_limit' => $default ? '' : $dtt->get_pretty('DTT_reg_limit','input'),
            'DTT_order' => $default ? 'DTTNUM' : $dttrow,
            'dtt_sold' => $default ? '0' : $dtt->get('DTT_sold'),
            'clone_icon' => !empty( $dtt ) && $dtt->get('DTT_sold') > 0 ? '' : 'clone-icon ee-icon ee-icon-clone clickable',
            'trash_icon' => !empty( $dtt ) && $dtt->get('DTT_sold') > 0  ? 'ee-lock-icon' : 'trash-icon dashicons dashicons-post-trash clickable',
            'reg_list_url' => $default || ! $dtt->event() instanceof \EE_Event
                ? ''
                : EE_Admin_Page::add_query_args_and_nonce(
                    array( 'event_id' => $dtt->event()->ID(), 'datetime_id' => $dtt->ID() ),
                    REG_ADMIN_URL
                )
        );

        $template_args['show_trash'] = count( $all_dtts ) === 1 && $template_args['trash_icon'] !== 'ee-lock-icon' ? ' style="display:none"' : '';

        //allow filtering of template args at this point.
        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_dtt_edit_row__template_args', $template_args, $dttrow, $dtt, $default, $all_dtts, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_edit_row.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }


    protected function _get_dtt_attached_tickets_row( $dttrow, $dtt, $datetime_tickets, $all_tickets, $default ) {

        $template_args = array(
            'dtt_row' => $default ? 'DTTNUM' : $dttrow,
            'event_datetimes_name' => $default ? 'DTTNAMEATTR' : 'edit_event_datetimes',
            'DTT_description' => $default ? '' : $dtt->description(),
            'datetime_tickets_list' => $default ? '<li class="hidden"></li>' : '',
            'show_tickets_row' => ' style="display:none;"', //$default || $this->_adminpage_obj->get_cpt_model_obj()->ID() > 0 ? ' style="display:none;"' : '',
            'add_new_datetime_ticket_help_link' => EEH_Template::get_help_tab_link('add_new_ticket_via_datetime', $this->_adminpage_obj->page_slug, $this->_adminpage_obj->get_req_action(), FALSE, FALSE ), //todo need to add this help info id to the Events_Admin_Page core file so we can access it here.
            'DTT_ID' => $default ? '' : $dtt->ID()
            );

        //need to setup the list items (but only if this isnt' a default skeleton setup)
        if ( !$default ) {
            $tktrow = 1;
            foreach ( $all_tickets as $ticket ) {
                $template_args['datetime_tickets_list'] .= $this->_get_datetime_tickets_list_item( $dttrow, $tktrow, $dtt, $ticket, $datetime_tickets, $default );
                $tktrow++;
            }
        }

        //filter template args at this point
        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_dtt_attached_ticket_row__template_args', $template_args, $dttrow, $dtt, $datetime_tickets, $all_tickets, $default, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_attached_tickets_row.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }



    protected function _get_datetime_tickets_list_item( $dttrow, $tktrow, $dtt, $ticket, $datetime_tickets, $default ) {
        $tktid = !empty( $ticket ) ? $ticket->ID() : 0;
        $dtt_tkts = $dtt instanceof EE_Datetime && isset( $datetime_tickets[$dtt->ID()] ) ? $datetime_tickets[$dtt->ID()] : array();

        $displayrow = !empty( $ticket ) ? $ticket->get('TKT_row') : 0;
        $template_args = array(
            'dtt_row' => $default ? 'DTTNUM' : $dttrow,
            'tkt_row' => $default && empty( $ticket ) ? 'TICKETNUM' : $tktrow,
            'datetime_ticket_checked' => in_array($displayrow, $dtt_tkts) ? ' checked="checked"' : '',
            'ticket_selected' => in_array($displayrow, $dtt_tkts) ? ' ticket-selected' : '',
            'TKT_name' => $default && empty( $ticket ) ? 'TKTNAME' : $ticket->get('TKT_name'),
            'tkt_status_class' => ( $default && empty( $ticket ) ) || $this->_is_creating_event ? ' tkt-status-' . EE_Ticket::onsale : ' tkt-status-' . $ticket->ticket_status(),
            );

        //filter template args
        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_datetime_tickets_list_item__template_args', $template_args, $dttrow, $tktrow, $dtt, $ticket, $datetime_tickets, $default, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_dtt_tickets_list.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }




    /**
     * This generates the ticket row for tickets.
     * This same method is used to generate both the actual rows and the js skeleton row (when default ==
     * true)
     *
     * @param int     $tktrow           Represents the row number being generated.
     * @param mixed null|EE_Ticket $ticket           If default then this will be null.
     * @param EE_Datetime[] $ticket_datetimes    Either an array of all datetimes on all tickets indexed by
     *                                                          each ticket or empty for  default
     * @param EE_Datetime[] $all_dtts                   All Datetimes on the event or empty for default.
     * @param bool   $default          Whether default row being generated or not.
     * @param EE_Ticket[]  $all_tickets      This is an array of all tickets attached to the event (or empty in the
     *                                                  case of defaults)
     *
     * @return [type] [description]
     */
    protected function _get_ticket_row( $tktrow, $ticket, $ticket_datetimes, $all_dtts, $default = FALSE, $all_tickets = array() ) {

        //if $ticket is not an instance of EE_Ticket then force default to true.
        $default =  ! $ticket instanceof EE_Ticket ? true : false;


        $template_args = array(
            'tkt_row' => $default ? 'TICKETNUM' : $tktrow,
            'TKT_order' => $default ? 'TICKETNUM' : $tktrow, //on initial page load this will always be the correct order.
            'tkt_status_class' => $ticket_status_class,
            'display_edit_tkt_row' => ' style="display:none;"',
            'edit_tkt_expanded' => '',
            'edit_tickets_name' => $default ? 'TICKETNAMEATTR' : 'edit_tickets',
            'TKT_name' => $default ? '' : $ticket->get('TKT_name'),
            'TKT_start_date' => $default ? '' : $ticket->get_date('TKT_start_date', $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] ),
            'TKT_end_date' => $default ? '' : $ticket->get_date('TKT_end_date', $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time']  ),
            'TKT_status' => $default ? EEH_Template::pretty_status(EE_Ticket::onsale, FALSE, 'sentence') : $ticket->is_default() ? EEH_Template::pretty_status( EE_Ticket::onsale, FALSE, 'sentence') : $ticket->ticket_status(TRUE),
            'TKT_price' => $default ? '' : EEH_Template::format_currency($ticket->get_ticket_total_with_taxes(), FALSE, FALSE),
            'TKT_price_code' => EE_Registry::instance()->CFG->currency->code,
            'TKT_price_amount' => $default ? 0 : $ticket_subtotal,
            'TKT_qty' => $default ? '' : $ticket->get_pretty('TKT_qty','symbol'),
            'TKT_qty_for_input'=> $default ? '' : $ticket->get_pretty('TKT_qty','input'),
            'TKT_uses' => $default ? '' : $ticket->get_pretty('TKT_uses','input'),
            'TKT_min' => $default ? '' : ( $ticket->get('TKT_min') === -1 || $ticket->get('TKT_min') === 0 ? '' : $ticket->get('TKT_min') ),
            'TKT_max' => $default ? '' :  $ticket->get_pretty('TKT_max','input'),
            'TKT_sold' => $default ? 0 : $ticket->tickets_sold('ticket'),
            'TKT_registrations' => $default ? 0 : $ticket->count_registrations( array( array( 'STS_ID' => array( '!=', EEM_Registration::status_id_incomplete ) ) ) ),
            'TKT_ID' => $default ? 0 : $ticket->get('TKT_ID'),
            'TKT_description' => $default ? '' : $ticket->get('TKT_description'),
            'TKT_is_default' => $default ? 0 : $ticket->get('TKT_is_default'),
            'TKT_required' => $default ? 0 : $ticket->required(),
            'TKT_is_default_selector' => '',
            'ticket_price_rows' => '',
            'TKT_base_price' => $default || ! $base_price instanceof EE_Price ? '' : $base_price->get_pretty('PRC_amount', 'localized_float'),
            'TKT_base_price_ID' => $default || ! $base_price instanceof EE_Price ? 0 : $base_price->ID(),
            'show_price_modifier' => count($prices) > 1 || ( $default && $count_price_mods > 0 ) ? '' : ' style="display:none;"',
            'show_price_mod_button' => count($prices) > 1 || ( $default && $count_price_mods > 0 ) || ( !$default && $ticket->get('TKT_deleted') ) ? ' style="display:none;"' : '',
            'total_price_rows' => count($prices) > 1 ? count($prices) : 1,
            'ticket_datetimes_list' => $default ? '<li class="hidden"></li>' : '',
            'starting_ticket_datetime_rows' => $default || $default_dtt ? '' : implode(',', $tkt_dtts),
            'ticket_datetime_rows' => $default ? '' : implode(',', $tkt_dtts),
            'existing_ticket_price_ids' => $default, '', implode(',', array_keys( $prices) ),
            'ticket_template_id' => $default ? 0 : $ticket->get('TTM_ID'),
            'TKT_taxable' => $TKT_taxable,
            'display_subtotal' => $ticket instanceof EE_Ticket && $ticket->get('TKT_taxable') ? '' : ' style="display:none"',
            'price_currency_symbol' => EE_Registry::instance()->CFG->currency->sign,
            'TKT_subtotal_amount_display' => EEH_Template::format_currency($ticket_subtotal, FALSE, FALSE ),
            'TKT_subtotal_amount' => $ticket_subtotal,
            'tax_rows' => $this->_get_tax_rows( $tktrow, $ticket ),
            'disabled' => $ticket instanceof EE_Ticket && $ticket->get('TKT_deleted') ? TRUE: FALSE,
            'ticket_archive_class' => $ticket instanceof EE_Ticket && $ticket->get('TKT_deleted') ? ' ticket-archived' : '',
            'trash_icon' => $ticket instanceof EE_Ticket && $ticket->get('TKT_deleted') ? 'ee-lock-icon ' : 'trash-icon dashicons dashicons-post-trash clickable',
            'clone_icon' => $ticket instanceof EE_Ticket && $ticket->get('TKT_deleted') ? '' : 'clone-icon ee-icon ee-icon-clone clickable'
            );

        $template_args['trash_hidden'] = count( $all_tickets ) === 1 && $template_args['trash_icon'] != 'ee-lock-icon' ? ' style="display:none"' : '';

        //handle rows that should NOT be empty
        if ( empty( $template_args['TKT_start_date'] ) ) {
            //if empty then the start date will be now.
            $template_args['TKT_start_date'] = date( $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] , current_time('timestamp'));
            $template_args['tkt_status_class'] = ' tkt-status-' . EE_Ticket::onsale;
        }

        if ( empty( $template_args['TKT_end_date'] ) ) {

            //get the earliest datetime (if present);
            $earliest_dtt = $this->_adminpage_obj->get_cpt_model_obj()->ID() > 0 ? $this->_adminpage_obj->get_cpt_model_obj()->get_first_related('Datetime', array('order_by'=> array('DTT_EVT_start' => 'ASC' ) ) ) : NULL;

            if ( !empty( $earliest_dtt ) ) {
                $template_args['TKT_end_date'] = $earliest_dtt->get_datetime('DTT_EVT_start', $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] );
            } else {
                //default so let's just use what's been set for the default date-time which is 30 days from now.
                $template_args['TKT_end_date'] = date( $this->_date_format_strings['date'] . ' ' . $this->_date_format_strings['time'] , mktime(24, 0, 0, date("m"), date("d") + 29, date("Y") )  );
            }
            $template_args['tkt_status_class'] = ' tkt-status-' . EE_Ticket::onsale;
        }

        //generate ticket_datetime items
        if ( ! $default ) {
            $dttrow = 1;
            foreach ( $all_dtts as $dtt ) {
                $template_args['ticket_datetimes_list'] .= $this->_get_ticket_datetime_list_item( $dttrow, $tktrow, $dtt, $ticket, $ticket_datetimes, $default );
                $dttrow++;
            }
        }

        //filter $template_args
        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_ticket_row__template_args', $template_args, $tktrow, $ticket, $ticket_datetimes, $all_dtts, $default, $all_tickets, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_ticket_row.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }



    protected function _get_ticket_price_row( $tktrow, $prcrow, $price, $default, $ticket, $show_trash = TRUE, $show_create = TRUE ) {
        $send_disabled = !empty( $ticket ) && $ticket->get('TKT_deleted') ? TRUE : FALSE;
        $template_args = array(
            'tkt_row' => $default && empty($ticket) ? 'TICKETNUM' : $tktrow,
            'PRC_order' => $default && empty($price) ? 'PRICENUM' : $prcrow,
            'edit_prices_name' => $default && empty($price) ? 'PRICENAMEATTR' : 'edit_prices',
            'price_type_selector' => $default && empty( $price ) ? $this->_get_base_price_template( $tktrow, $prcrow, $price, $default ) : $this->_get_price_type_selector( $tktrow, $prcrow, $price, $default, $send_disabled ),
            'PRC_ID' => $default && empty($price) ? 0 : $price->ID(),
            'PRC_is_default' => $default && empty($price) ? 0 : $price->get('PRC_is_default'),
            'PRC_name' => $default && empty($price) ? '' : $price->get('PRC_name'),
            'price_currency_symbol' => EE_Registry::instance()->CFG->currency->sign,
            'show_plus_or_minus' => $default && empty($price) ? '' : ' style="display:none;"',
            'show_plus' => $default && empty( $price ) ? ' style="display:none;"' : ( $price->is_discount() || $price->is_base_price() ? ' style="display:none;"' : ''),
            'show_minus' => $default && empty( $price ) ? ' style="display:none;"' : ($price->is_discount() ? '' : ' style="display:none;"'),
            'show_currency_symbol' => $default && empty( $price ) ? ' style="display:none"' : ($price->is_percent() ? ' style="display:none"' : '' ),
            'PRC_amount' => $default && empty( $price ) ? 0 : $price->get_pretty('PRC_amount', 'localized_float'),
            'show_percentage' => $default && empty( $price ) ? ' style="display:none;"' : ( $price->is_percent() ? '' : ' style="display:none;"' ),
            'show_trash_icon' => $show_trash ? '' : ' style="display:none;"',
            'show_create_button' => $show_create ? '' : ' style="display:none;"',
            'PRC_desc' => $default && empty( $price ) ? '' : $price->get('PRC_desc'),
            'disabled' => !empty( $ticket ) && $ticket->get('TKT_deleted') ? TRUE : FALSE
            );

    $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_ticket_price_row__template_args', $template_args, $tktrow, $prcrow, $price, $default, $ticket, $show_trash, $show_create, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_ticket_price_row.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }



    protected function _get_price_modifier_template( $tktrow, $prcrow, $price, $default, $disabled = FALSE ) {
        $select_name = $default && empty( $price ) ? 'edit_prices[TICKETNUM][PRICENUM][PRT_ID]' : 'edit_prices[' . $tktrow . '][' . $prcrow . '][PRT_ID]';
        $price_types = EE_Registry::instance()->load_model('Price_Type')->get_all(array( array('OR' => array('PBT_ID' => '2', 'PBT_ID*' => '3' ) ) ) );
        $price_option_span_template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_price_option_span.template.php';
        $all_price_types = $default && empty( $price ) ? array(array('id' => 0, 'text' => __('Select Modifier', 'event_espresso')) ) : array();
        $selected_price_type_id = $default && empty( $price ) ? 0 : $price->type();
        $price_option_spans = '';
        //setup pricetypes for selector
        foreach ( $price_types as $price_type ) {
            $all_price_types[] = array(
                'id' => $price_type->ID(),
                'text' => $price_type->get('PRT_name'),
                );

            //while we're in the loop let's setup the option spans used by js
            $spanargs = array(
                'PRT_ID' => $price_type->ID(),
                'PRT_operator' => $price_type->is_discount() ? '-' : '+',
                'PRT_is_percent' => $price_type->get('PRT_is_percent') ? 1 : 0
                );
            $price_option_spans .= EEH_Template::display_template($price_option_span_template, $spanargs, TRUE );
        }

        $select_params = $disabled ? 'style="width:auto;" disabled'  : 'style="width:auto;"';
        $main_name = $select_name;
        $select_name = $disabled ? 'archive_price[' . $tktrow . '][' . $prcrow . '][PRT_ID]' : $main_name;

        $template_args = array(
            'tkt_row' => $default ? 'TICKETNUM' : $tktrow,
            'PRC_order' => $default && empty( $price ) ? 'PRICENUM' : $prcrow,
            'price_modifier_selector' => EEH_Form_Fields::select_input( $select_name, $all_price_types, $selected_price_type_id, $select_params, 'edit-price-PRT_ID' ),
            'main_name' => $main_name,
            'selected_price_type_id' => $selected_price_type_id,
            'price_option_spans' => $price_option_spans,
            'price_selected_operator' => $default && empty( $price ) ? '' : ( $price->is_discount() ? '-' : '+' ),
            'price_selected_is_percent' => $default && empty( $price ) ? '' : ( $price->is_percent() ? 1 : 0 ),
            'disabled' => $disabled
            );

        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_price_modifier_template__template_args', $template_args, $tktrow, $prcrow, $price, $default, $disabled, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_price_modifier_selector.template.php';

        return EEH_Template::display_template( $template, $template_args, TRUE );
    }



    protected function _get_ticket_datetime_list_item( $dttrow, $tktrow, $dtt, $ticket, $ticket_datetimes, $default ) {
        $dttid = !empty($dtt) ? $dtt->ID() : 0;
        $displayrow = !empty($dtt) ? $dtt->get('DTT_order') : 0;
        $tkt_dtts = $ticket instanceof EE_Ticket && isset( $ticket_datetimes[$ticket->ID()] ) ? $ticket_datetimes[$ticket->ID()] : array();
        $template_args = array(
            'dtt_row' => $default && empty( $dtt ) ? 'DTTNUM' : $dttrow,
            'tkt_row' => $default ? 'TICKETNUM' : $tktrow,
            'ticket_datetime_selected' => in_array( $displayrow, $tkt_dtts ) ? ' ticket-selected' : '',
            'ticket_datetime_checked' => in_array( $displayrow, $tkt_dtts ) ? ' checked="checked"' : '',
            'DTT_name' => $default && empty( $dtt ) ? 'DTTNAME' : $dtt->get_dtt_display_name( TRUE ),
            'tkt_status_class' => '',
            );

        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_ticket_datetime_list_item__template_args', $template_args, $dttrow, $tktrow, $dtt, $ticket, $ticket_datetimes, $default, $this->_is_creating_event );
        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_ticket_datetimes_list_item.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }



    protected function _get_ticket_js_structure($all_dtts, $all_tickets) {
        $template_args = array(
            'default_datetime_edit_row' => $this->_get_dtt_edit_row('DTTNUM', NULL, TRUE, $all_dtts),
            'default_ticket_row' => $this->_get_ticket_row( 'TICKETNUM', NULL, array(), array(), TRUE),
            'default_price_row' => $this->_get_ticket_price_row( 'TICKETNUM', 'PRICENUM', NULL, TRUE, NULL ),
            'default_price_rows' => '',
            'default_base_price_amount' => 0,
            'default_base_price_name' => '',
            'default_base_price_description' => '',
            'default_price_modifier_selector_row' => $this->_get_price_modifier_template( 'TICKETNUM', 'PRICENUM', NULL, TRUE ),
            'default_available_tickets_for_datetime' => $this->_get_dtt_attached_tickets_row( 'DTTNUM', NULL, array(), array(), TRUE ),
            'existing_available_datetime_tickets_list' => '',
            'existing_available_ticket_datetimes_list' => '',
            'new_available_datetime_ticket_list_item' => $this->_get_datetime_tickets_list_item( 'DTTNUM', 'TICKETNUM', NULL, NULL, array(), TRUE ),
            'new_available_ticket_datetime_list_item' => $this->_get_ticket_datetime_list_item( 'DTTNUM', 'TICKETNUM', NULL, NULL, array(), TRUE )
            );


        $template_args = apply_filters( 'FHEE__espresso_events_Pricing_Hooks___get_ticket_js_structure__template_args', $template_args, $all_dtts, $all_tickets, $this->_is_creating_event );

        $template = PRICING_TEMPLATE_PATH . 'event_tickets_datetime_ticket_js_structure.template.php';
        return EEH_Template::display_template( $template, $template_args, TRUE );
    }


} //end class espresso_events_Pricing_Hooks
