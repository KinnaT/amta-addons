<?php
/**
 * Plugin Name: AMTA Chapter site modifications
 * Description: Various modifications and customizations for the AMTA chapter sites
 * Version: 1.0.3
 * Author: Kinna Thompson
 * License: GPL2
 *Copyright 2015  Kinna Thompson (email : kinna3@outlook.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


function theme_name_scripts() {
    wp_enqueue_style( 'kinna-nf-css', WP_CONTENT_URL . '/plugins/amta-chapter-tweaks/form-styling.css' );
    wp_enqueue_style( 'ny-styling', WP_CONTENT_URL . '/plugins/amta-chapter-tweaks/ny-styling.css' );
    wp_enqueue_style( 'ny-extra', WP_CONTENT_URL. '/plugins/amta-chapter-tweaks/extras.css' );
}

add_action( 'wp_enqueue_scripts', 'theme_name_scripts' );
add_action( 'admin_enqueue_scripts', 'theme_name_scripts' );

function kinna_add_nf_styles( ) {
    wp_enqueue_style( 'kinna-nf-css', WP_CONTENT_URL . '/plugins/amta-chapter-tweaks/form-styling.css' );
    }

add_action ( 'ninja_forms_display_css', 'kinna_add_nf_styles' );
function add_query_vars_filter( $vars ){
    $vars[] = "credits_id";
    return $vars;
}
add_filter( 'query_vars', 'add_query_vars_filter' );

/*
 *
 * CONTENT DISPLAY TWEAKS FOR EE4
 *
 * */

/* Removes "powered by Event Espresso" admin footer text */
add_filter( 'admin_footer_text', 'ee_remove_footer_text', 11 );
function ee_remove_footer_text() {
    remove_filter( 'admin_footer_text', array( 'EE_Admin', 'espresso_admin_footer' ), 10 );
}

/* Prints a message regarding the duration of the event. */
add_action( 'AHEE__ticket_selector_chart__template__before_ticket_selector', 'ee_print_event_duration', 10 );
function ee_print_event_duration( $event ) {
    $datetimes = $event->datetimes();
    foreach($datetimes as $datetime){
        if ( $datetime instanceof EE_Datetime ) {
            $start = $datetime->get_raw('DTT_EVT_start');
            $end = $datetime->get_raw('DTT_EVT_end');
            $diff = $end - $start;
            $hours = $diff / 3600;
            echo '<div><strong>This event is ' . $hours . ' hours in duration</strong></div>';
        }
    }
}

/**
 * Filters the event archive pages so they exclude events that have tickets no longer on sale
 * */
function de_ee_tweak_event_list_exclude_ticket_expired_events_where( $SQL, WP_Query $wp_query ) {
    if ( isset( $wp_query->query_vars['post_type'] ) && ( $wp_query->query_vars['post_type'] == 'espresso_events'  || ( is_array( $wp_query->query_vars['post_type'] ) && in_array( 'espresso_events', $wp_query->query_vars['post_type'] ) ) ) && ! $wp_query->is_singular ) {
        $SQL .= ' AND Ticket.TKT_end_date > "' . current_time( 'mysql', true ) . '" AND Ticket.TKT_deleted=0';
    }
    return $SQL;
}
add_filter( 'posts_where', 'de_ee_tweak_event_list_exclude_ticket_expired_events_where', 15, 2 );
function de_ee_tweak_event_list_exclude_ticket_expired_events_join( $SQL, $wp_query ) {
    if ( isset( $wp_query->query_vars['post_type'] ) && ( $wp_query->query_vars['post_type'] == 'espresso_events'  || ( is_array( $wp_query->query_vars['post_type'] ) && in_array( 'espresso_events', $wp_query->query_vars['post_type'] ) ) ) && ! $wp_query->is_singular ) {
        if ( ! $wp_query->is_espresso_event_archive && ! $wp_query->is_espresso_event_taxonomy  ) {
            $SQL .= ' INNER JOIN ' . EEM_Datetime::instance()->table() . ' ON ( ' . EEM_Event::instance()->table() . '.ID = ' . EEM_Datetime::instance()->table() . '.' . EEM_Event::instance()->primary_key_name() . ' ) ';
        }
        $SQL .= ' INNER JOIN ' . EEM_Datetime_Ticket::instance()->table() . ' AS Datetime_Ticket ON ( Datetime_Ticket.DTT_ID=' . EEM_Datetime::instance()->table() . '.' . EEM_Datetime::instance()->primary_key_name() . ' ) INNER JOIN ' . EEM_Ticket::instance()->table()  . ' AS Ticket ON ( Datetime_Ticket.TKT_ID=Ticket.' . EEM_Ticket::instance()->primary_key_name() . ' ) ';
    }
    return $SQL;
}
add_filter( 'posts_join', 'de_ee_tweak_event_list_exclude_ticket_expired_events_join', 3, 2 );

/*
 * Excludes certain events from the events calendar for EE4
 */
function ee_hide_certain_event_statuses_from_events_calendar( $public_event_stati ) {
            unset( $public_event_stati[ EEM_Event::sold_out ] );
            unset( $public_event_stati[ EEM_Event::postponed ] );
            unset( $public_event_stati[ EEM_Event::cancelled ] );
            return $public_event_stati;
        }
add_filter( 'AFEE__EED_Espresso_Calendar__get_calendar_events__public_event_stati', 'ee_hide_certain_event_statuses_from_events_calendar', 10, 1 );

/*
 * Affects the month view of the calendar so that if there are multiple datetimes for a single event on the same day, only one will appear in the calendar. Week and day views are unaffected.
 */
add_filter( 'FHEE__EED_Espresso_Calendar__get_calendar_events__query_params', 'ee_calendar_group_by_day', 10, 7 );
function ee_calendar_group_by_day( $query_params,
        $category_id_or_slug,
        $venue_id_or_slug,
        $public_event_stati,
        $start_date,
        $end_date,
        $show_expired ) {
    //only override month view
    if( ( $end_date->getTimestamp() - $start_date->getTimestamp() ) <= WEEK_IN_SECONDS ) {
        return $query_params;
    }
    //ok so it's month view. Let's issue a query grouped by event Id and date
    $query_params[ 'group_by' ] = array( 'EVT_ID', 'event_date' );
    $datetime_ids = EEM_Datetime::instance()->get_all_wpdb_results(
            $query_params,
            ARRAY_A,
            array(
                'DTT_ID' => array( 'Datetime.DTT_ID', '%d' ),
                'EVT_ID' => array( 'Datetime.EVT_ID', '%d' ),
                'event_date' => array( 'DATE( Datetime.DTT_EVT_start )', '%s' ) ) );
    $ids_only = array_column( $datetime_ids, 'DTT_ID' );
    //...and return query params so we only look for those specific datetimes
    return array(
        array(
            'DTT_ID' => array( 'IN', $ids_only ) ),
            'limit' => count( $ids_only ) );
}

/*
 *
 * WP USER INTEGRATION TWEAKS FOR EE4
 *
 * */

/*
 * Creates a new user account for each attendee within a transaction.
 * If the person doing the registration is logged in, new user accounts will be created for additional registrations only
 */

function plugin_ee_wpuser_for_attendee_set_hooks() {
    if ( ! is_admin() ) {
        remove_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', array( 'EED_WP_Users_SPCO', 'process_wpuser_for_attendee' ), 11 );
        add_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', 'plugin_ee_process_wpuser_for_attendee', 10, 2 );
    }
    if ( EE_FRONT_AJAX ) {
        remove_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', array( 'EED_WP_Users_SPCO', 'process_wpuser_for_attendee' ), 11 );
        add_action( 'AHEE__EE_Single_Page_Checkout__process_attendee_information__end', 'plugin_ee_process_wpuser_for_attendee', 10, 2 );
    }
}
add_action( 'AHEE__EE_System__load_espresso_addons', 'plugin_ee_wpuser_for_attendee_set_hooks', 11 );
function jf_ee_process_wpuser_for_attendee( EE_SPCO_Reg_Step_Attendee_Information $spco, $valid_data ) {
    if ( class_exists( 'EED_WP_Users_SPCO' ) ) {
        //use spco to get registrations from the
        $registrations = EED_WP_Users_SPCO::_get_registrations( $spco );
        foreach ( $registrations as $registration ) {
            $user_created = FALSE;
            $att_id = '';
            $attendee = $registration->attendee();
            if ( ! $attendee instanceof EE_Attendee ) {
                // Should always be an attendee, but if not we continue just to prevent errors.
                continue;
           }
            // If user logged in, then let's just use that user.  Otherwise we'll attempt to get a user via the attendee info.
            if ( is_user_logged_in() && $registration->is_primary_registrant() ) {
                $user = get_userdata( get_current_user_id() );
            } else {
                //is there already a user for the given attendee?
                $user = get_user_by( 'email', $attendee->email() );
                //does this user have the same att_id as the given att?  If NOT, then we do NOT update because it's possible there was a family member or something sharing the same email address but is a different attendee record.
                $att_id = $user instanceof WP_User ? get_user_option( 'EE_Attendee_ID', $user->ID ) : $att_id;
                if ( ! empty( $att_id ) && $att_id != $attendee->ID() ) {
                    continue;
                }
            }
            $event = $registration->event();
            // No existing user? then we'll create the user from the date in the attendee form.
            if ( ! $user instanceof WP_User ) {
                // If this event does NOT allow automatic user creation then let's bail.
                if ( ! EE_WPUsers::is_auto_user_create_on( $event ) ) {
                    return;
                }
                $password = wp_generate_password( 12, false );
                // Remove our action for creating contacts on creating user because we don't want to loop!
                remove_action( 'user_register', array( 'EED_WP_Users_Admin', 'sync_with_contact' ) );
                $user_id = wp_create_user(
                    apply_filters(
                        'FHEE__EED_WP_Users_SPCO__process_wpuser_for_attendee__username',
                        $attendee->email(),
                        $password,
                       $registration
                    ),
                    $password,
                    $attendee->email()
                );
                $user_created = true;
                if ( $user_id instanceof WP_Error ) {
                    continue; // Get out because something went wrong with creating the user.
                }
                $user = new WP_User( $user_id );
                update_user_option( $user->ID, 'description', apply_filters( 'FHEE__EED_WP_Users_SPCO__process_wpuser_for_attendee__user_description_field', __( 'Registered via event registration form', 'event_espresso' ), $user, $attendee, $registration ) );
            }
            // Only do the below if syncing is enabled.
            if ( $user_created || EE_Registry::instance()->CFG->addons->user_integration->sync_user_with_contact ) {
                // Remove our existing action for updating users via saves in the admin to prevent recursion
                remove_action( 'profile_update', array( 'EED_WP_Users_Admin', 'sync_with_contact' ) );
                wp_update_user(
                    array(
                        'ID'           => $user->ID,
                        'nickname'     => $attendee->fname(),
                        'display_name' => $attendee->full_name(),
                        'first_name'   => $attendee->fname(),
                        'last_name'    => $attendee->lname()
                    )
                );
            }
            // If user gets created then send notification and attach attendee to user
            if ( $user_created ) {
                do_action( 'AHEE__EED_WP_Users_SPCO__process_wpuser_for_attendee__user_user_created', $user, $attendee, $registration, $password );
                $user->set_role( EE_WPUsers::default_user_create_role( $event ) );
                update_user_option( $user->ID, 'EE_Attendee_ID', $attendee->ID() );
            } else {
                do_action( 'AHEE__EED_WP_Users_SPCO__process_wpuser_for_attendee__user_user_updated', $user, $attendee, $registration );
            }
          // Failsafe in case this is a logged in user not created by this system that has never had an attendee record.
            $att_id = empty( $att_id ) ? get_user_option( 'EE_Attendee_ID', $user->ID ) : $att_id;
            if ( empty( $att_id ) && (EE_Registry::instance()->CFG->addons->user_integration->sync_user_with_contact
            || (
                $attendee->fname() === $user->first_name
                && $attendee->lname() === $user->last_name
                && $attendee->email() === $user->user_email
            ) ) ) {
                update_user_option( $user->ID, 'EE_Attendee_ID', $attendee->ID() );
            }
        } // End registrations loop
    }
}


/**
 * Populates the user's stored System Address questions too
 */
add_filter( 'FHEE__EEM_Answer__get_attendee_question_answer_value__answer_value', 'jf_filter_address_answer_for_wpuser', 10, 4 );
function jf_filter_address_answer_for_wpuser( $value, EE_Registration $registration, $question_id, $system_id = null ) {
    if( class_exists( 'EED_WP_Users_SPCO' ) ) {
        global $current_user;
        $attendee = EED_WP_Users_SPCO::get_attendee_for_user( $current_user );
    } else {
        $attendee = null;
    }
    //only fill for primary registrant
    if ( ! $registration->is_primary_registrant() ) {
        return $value;
    }
    if ( empty($value) ) {
        if( is_numeric( $question_id ) &&
        defined( 'EEM_Attendee::system_question_address' ) ) {
            $address = EEM_Attendee::system_question_address;
            $address2 = EEM_Attendee::system_question_address2;
            $city = EEM_Attendee::system_question_city;
            $state = EEM_Attendee::system_question_state;
            $country = EEM_Attendee::system_question_country;
            $zip = EEM_Attendee::system_question_zip;
            $phone = EEM_Attendee::system_question_phone;
            $id_to_use = $system_id;
        }
        if ( $current_user instanceof WP_User &&
        $attendee instanceof EE_Attendee ) {
            switch ( $id_to_use ) {
                case $address :
                    $value = $attendee->get( 'ATT_address' );
                    break;
                case $address2 :
                    $value = $attendee->get( 'ATT_address2' );
                    break;
                case $city :
                   $value = $attendee->get( 'ATT_city' );
                    break;
                case $country :
                   $value = $attendee->get( 'CNT_ISO' );
                    break;
                case $state :
                   $value = $attendee->get( 'STA_ID' );
                    break;
                case $zip :
                    $value = $attendee->get( 'ATT_zip' );
                    break;
                case $phone :
                    $value = $attendee->get( 'ATT_phone' );
                    break;
                default:
            }
        }
    }
    return $value;
}

/**
 * Remembers the user's last answer to custom questions too.
 */
add_filter('FHEE__EE_SPCO_Reg_Step_Attendee_Information___generate_question_input__input_constructor_args', 'my_question_input', 10, 4);
function my_question_input( $input_args, EE_Registration $registration = null, EE_Question $question = null, EE_Answer $answer = null ) {
    if( class_exists( 'EED_WP_Users_SPCO' ) ) {
        global $current_user;
        $attendee = EED_WP_Users_SPCO::get_attendee_for_user( $current_user );
    } else {
        $attendee = null;
    }
    if( $question instanceof EE_Question &&
            ! $question->system_ID() &&
            $registration instanceof EE_Registration &&
            $registration->is_primary_registrant() &&
            $attendee instanceof EE_Attendee ) {
        $prev_answer_value = EEM_Answer::instance()->get_var(
                array(
                    array(
                        'Registration.ATT_ID' => $attendee->ID(),
                        'QST_ID' => $question->ID()
                    ),
                    'order_by' => array(
                        'ANS_ID' => 'DESC'
                    ),
                    'limit' => 1
                ),
                'ANS_value' );
        if( $prev_answer_value ) {
            $field_obj = EEM_Answer::instance()->field_settings_for( 'ANS_value' );
            $prev_answer_value = $field_obj->prepare_for_get( $field_obj->prepare_for_set_from_db( $prev_answer_value ) );
            $input_args[ 'default' ] = $prev_answer_value;
        }
    }
    return $input_args;
}

