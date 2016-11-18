<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
*
* EED_Cert_Admin module.  Takes care of Certificates integration with EE admin.
*
* @since 1.0.0
*
* @package        EE Certificates
* @subpackage    modules, admin
* @author         Kinna Thompson
*
* ------------------------------------------------------------------------
*/
class EED_Cert_Admin  extends EED_Module {


public static function set_hooks() {}
public static function set_hooks_admin() {
add_action( 'admin_enqueue_scripts', array( 'EED_Cert_Admin', 'admin_enqueue_scripts_styles' ) );

//hook into registration_form_admin_page routes and config.
// add_filter( 'FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes', array( 'EED_Cert_Admin', 'add_cert_default_settings_route' ), 10, 2 );
// add_filter( 'FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_config', array( 'EED_Cert_Admin', 'add_cert_default_settings_config' ), 10, 2 );

//hooking into event editor
add_action( 'add_meta_boxes', array( 'EED_Cert_Admin', 'add_metaboxes' ) );
add_filter( 'FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks', array( 'EED_Cert_Admin', 'set_callback_save_cert_event_setting' ), 10, 2 );
add_filter( 'FHEE__EED_Cert_Admin__event_editor_metabox__cert_form_content', array( 'EED_Cert_Admin', 'set_credits_default_event_editor' ), 10 );

//hook into ticket editor in event editor.
add_action('AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end', array('EED_Cert_Admin', 'insert_ticket_credit_meta_interface'), 10, 2);
add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_new_ticket', array( 'EED_Cert_Admin', 'update_credits_on_ticket') , 10, 4 );
add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_update_ticket', array( 'EED_Cert_Admin', 'update_credits_on_ticket' ), 10, 4 );
add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_new_default_ticket', array( 'EED_Cert_Admin', 'update_credits_on_ticket' ), 10, 4 );

}


public static function admin_enqueue_scripts_styles() {
if ( get_current_screen()->base == 'profile' || get_current_screen()->base == 'user-edit') {
wp_register_style('ee-admin-css', EE_ADMIN_URL . 'assets/ee-admin-page.css', array(), EVENT_ESPRESSO_VERSION);
wp_register_style('espresso_att', REG_ASSETS_URL . 'espresso_attendees_admin.css', array('ee-admin-css'), EVENT_ESPRESSO_VERSION );
wp_enqueue_style('espresso_att');
}
if ( get_current_screen()->id == 'espresso_events' ) {
    wp_register_script('bokeh', EE_CERT_URL . 'bokeh.js', array(), EVENT_ESPRESSO_VERSION);
    wp_enqueue_script('bokeh');
}
}

public function run( $WP ) {}


//Register metaboxes for event editor. Can be useful later to create blanket CE values that apply to ALL tickets created for this event
public static function add_metaboxes() {
$page = EE_Registry::instance()->REQ->get( 'page' );
$route = EE_Registry::instance()->REQ->get( 'action' );

// on event editor page?
if ( $page == 'espresso_events' && ( $route == 'edit' || $route == 'create_new' ) ) {
add_meta_box( 'eea_cert', __('CE Credit Settings', 'event_espresso' ), array( 'EED_Cert_Admin', 'event_editor_metabox' ), null, 'side', 'high' );
}
}


    /**
     * callback for the cert_settings route.
     *
     * @param EE_Admin_Page $admin_page
     * @return string html for displaying wp_user_settings.
     */
    public static function cert_settings( EE_Admin_Page $admin_page ) {
        $template_args['admin_page_content'] = self::_cert_settings_form()->get_html_and_js();
        $admin_page->set_add_edit_form_tags( 'update_cert_settings' );
        $admin_page->set_publish_post_box_vars( null, false, false, null, false );
        $admin_page->set_template_args( $template_args );
    }


/**
* callback for the update_cert_settings route.
* This handles the config update when the settings are saved.
*
* @param EE_Admin_Page $admin_page
*
* @return void
*/
public static function update_cert_settings( EE_Admin_Page $admin_page ) {
$config = EE_Registry::instance()->CFG->addons->cert;
try {
$form = self::_cert_settings_form();
if ( $form->was_submitted() ) {
//capture form data
$form->receive_form_submission();

//validate_form_data
if ( $form->is_valid() ) {
$valid_data = $form->valid_data();
$config->has_credits = $valid_data['main_settings']['has_credits'];
$config->registration_page = $valid_data['main_settings']['ce_credits'];
}
} else {
if ( $form->submission_error_message() != '' ) {
EE_Error::add_error( $form->submission_error_message(), __FILE__, __FUNCTION__, __LINE__ );
}
}
} catch( EE_Error $e ) {
$e->get_error();
}

EE_Error::add_success( __('Cert Settings updated.', 'event_espresso' ) );
EE_Registry::instance()->CFG->update_config( 'addons', 'cert', $config );
$admin_page->redirect_after_action( false, '', '', array( 'action' => 'cert_settings' ), true );
}


/**
* This is the metabox content for the certificates integration in the event editor.
*/
public static function event_editor_metabox( $post, $metabox ) {
echo "<div><h4 style=\"color: #800000; position: absolute; display: inline; margin: 0 auto; width: 95%;\" >In order to set CE credit values for this event, you must add it to the Advanced Settings for tickets.<br/><span style=\"font-weight: normal; color: #000;\">This is on a per ticket basis.</span></h4><canvas id=\"bokeh\"></canvas></div>";
}


    /**
     * Generate the event editor cert settings form
     */
    protected static function _get_event_editor_cert_form( $post ) {
        $evt_id = $post instanceof EE_Event ? $post->ID() : null;
        $evt_id = empty( $evt_id ) &&  isset( $post->ID ) ? $post->ID : 0;
        EE_Registry::instance()->load_helper( 'HTML' );
        return new EE_Form_Section_Proper();
    }

/**
* callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks
* Set the callback for updating cert_settings on event update.
*
* @param  array $callbacks existing array of callbacks.
*/
public static function set_callback_save_cert_event_setting( $callbacks ) {
    $callbacks[] = array( 'EED_Cert_Admin', 'save_cert_event_setting' );
    return $callbacks;
}


/**
* Callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks.
* Saving Cert event specific settings when events updated.
*
* @return bool   true success, false fail.
*/
public static function save_cert_event_setting( EE_Event $event, $req_data ) {
    try {
    $form = self::_get_event_editor_cert_form( $event );
    if ( $form->was_submitted() ) {
        $form->receive_form_submission();

        if ( $form->is_valid() ) {
            $valid_data = $form->valid_data();
            EE_Cert::update_event_has_credits( $event, $valid_data['has_credits'] );
            EE_Cert::update_ce_credits( $event, $valid_data['ce_credits'] );
        }
    } else {
        if ( $form->submission_error_message() != '' ) {
            EE_Error::add_error( $form->submission_error_message(), __FILE__, __FUNCTION__, __LINE__ );
            return false;
        }
    }
    } catch( EE_Error $e ) {
        $e->get_error();
    }

    EE_Error::add_success( __('Certificates Event Settings updated.', 'event_espresso' ) );
    return true;
}


/**
* Callback for AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end.
* This is used to add the form to the tickets for the capabilities.
*
* @return string form for capabilities required.
*/
public static function insert_ticket_credit_meta_interface($tkt_row, $TKT_ID) {
    //build our form and print.
    echo self::_get_ticket_credits_form( $tkt_row, $TKT_ID )->get_html_and_js();
}


/**
* Form generator for credits field on tickets.
* @return string
*/
protected static function _get_ticket_credits_form( $tkt_row, $TKT_ID ) {
    $ticket = EE_Registry::instance()->load_model('Ticket')->get_one_by_ID( $TKT_ID );
    $credits = $ticket instanceof EE_Ticket ? $ticket->get_extra_meta( 'ee_ticket_credits', true, '' ) : '';

    EE_Registry::instance()->load_helper( 'HTML' );

    return new EE_Form_Section_Proper(
        array(
            'name' => 'cert-ticket-credits-container-' . $tkt_row,
            'html_class' => 'cert-ticket-credits-container',
            'layout_strategy' => new EE_Div_Per_Section_Layout(),
            'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___get_ticket_credits_form__form_subsections',
                array(
                    'ticket_credits_hdr-' . $tkt_row => new EE_Form_Section_HTML( EEH_HTML::h4( __( 'Continuing Education Credit Settings', 'event_espresso' ), 'credits-heading' )),
                    'TKT_credits' => new EE_Text_Input(
                        array(
                            'html_class' => 'TKT-credits',
                            'html_name' => 'cert_ticket_credits_input[' . $tkt_row . '][TKT_credits]',
                            'html_label_text' => __('CE credits earned for this ticket:', 'event_espresso'),
                            'default' => $credits,
                            'display_html_label_text' => true
                        )
                    )
                )
            )
        )
    );
}


/**
* Callback for AHEE__espresso_events_Pricing_Hooks___update_tkts_new_ticket and
* AHEE__espresso_events_Pricing_Hooks___update_tkts_update_ticket.
* Used to hook into ticket saves so that we update any credits set for a ticket.
*
* @return void      This is an action callback so returns are ignored.
*/
public static function update_credits_on_ticket( EE_Ticket $tkt, $tkt_row, $tkt_form_data, $all_form_data ) {
    try {
        $ticket_id = $tkt_form_data instanceof EE_Ticket ? $tkt_form_data->ID() : $tkt->ID();
        $form = self::_get_ticket_credits_form( $tkt_row, $ticket_id );
        if ( $form->was_submitted() ) {
            $form->receive_form_submission();
            if ( $form->is_valid() ) {
                $valid_data = $form->valid_data();
                $tkt->update_extra_meta( 'ee_ticket_credits', $valid_data['TKT_credits'] );
                }
        }
    } catch ( EE_Error $e ) {
        $e->get_error();
    }
}

/**
* Callback for AHEE__EE_Base__delete__before to handle ensuring any relations Certificates has set up with the EE_Base_Class child object is handled when the object is permanently deleted.
*
* @param EE_Base_Class $model_object
*/
public static function remove_relations_on_delete( EE_Base_Class $model_object ) {
if ( $model_object instanceof EE_Event ) {
delete_post_meta( $model_object->ID(), 'ee_cert_settings' );
}

if ( $model_object instanceof EE_Ticket ) {
$model_object->delete_extra_meta( 'ee_ticket_credits' );
}
}

} //end EED_Cert_Admin
