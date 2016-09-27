<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * This file contains the module for the EE Certificates addon ee admin integration
 *
 * @since 1.0.0
 * @package  EE Cert
 * @subpackage modules, admin
 */
/**
 *
 * EED_Cert_Adminmodule.  Takes care of Cert integration with EE admin.
 *
 * @since 1.0.0
 *
 * @package        EE WP Users
 * @subpackage    modules, admin
 * @author         Darren Ethier
 *
 * ------------------------------------------------------------------------
 */
class EED_Cert_Admin  extends EED_Module {


    public static function set_hooks() {}
    public static function set_hooks_admin() {
       // add_action( 'admin_enqueue_scripts', array( 'EED_Cert_Admin', 'admin_enqueue_scripts_styles' ) );

        //hook into registration_form_admin_page routes and config.
       // add_filter( 'FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes', array( 'EED_Cert_Admin', 'add_cert_default_settings_route' ), 10, 2 );
      //  add_filter( 'FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_config', array( 'EED_Cert_Admin', 'add_cert_default_settings_config' ), 10, 2 );
        add_filter( 'FHEE__EE_Admin_Page___publish_post_box__box_label', array( 'EED_Cert_Admin', 'modify_settings_publish_box_label' ), 10, 3 );

        //hooking into event editor
        add_action( 'add_meta_boxes', array( 'EED_Cert_Admin', 'add_metaboxes' ) );
        add_filter( 'FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks', array( 'EED_Cert_Admin', 'set_callback_save_cert_event_setting' ), 10, 2 );
  //      add_filter( 'FHEE__EED_Cert_Admin__event_editor_metabox__cert_form_content', array( 'EED_Cert_Admin', 'set_capability_default_cert_role_event_editor' ), 10 );

        //hook into datetime editor in event editor.
         add_action('AHEE__event_tickets_datetime_attached_tickets_row_template__advanced_details_end', array('EED_Cert_Admin', 'insert_datetime_meta_interface'), 10, 2);
           //     add_action('AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end', array('EED_Cert_Admin', 'insert_datetime_meta_interface'), 10, 2);

        add_filter('FHEE__espresso_events_Pricing_Hooks___get_datetime_edit_row__template_args', array('EED_Cert_Admin', 'insert_datetime_meta_interface'), 10, 7);
        add_action( 'AHEE__espresso_events_Pricing_Hooks___update_dtts_update_datetime', array( 'EED_Cert_Admin', 'update_credits_on_datetime') , 10, 4 );
    }
    public function run( $WP ) {}

    public function add_datetime_credits_metabox() {
        $page = EE_Registry::instance()->REQ->get( 'page' );
        $route = EE_Registry::instance()->REQ->get( 'action' );

        // on event editor page?
        if ( $page == 'espresso_events' && ( $route == 'edit' || $route == 'create_new' ) ) {

            add_template_arg( 'eea_cert_integration', __('CE Credits', 'event_espresso' ), array( 'EED_Cert_Admin', 'datetime_editor_metabox' ) );
        }
    }

    /**
     * Register metaboxes for event editor.
     */
    public static function add_metaboxes() {
        $page = EE_Registry::instance()->REQ->get( 'page' );
        $route = EE_Registry::instance()->REQ->get( 'action' );

        // on event editor page?
        if ( $page == 'espresso_events' && ( $route == 'edit' || $route == 'create_new' ) ) {
            add_meta_box( 'eea_cert_integration', __('Certificate Settings', 'event_espresso' ), array( 'EED_Cert_Admin', 'event_editor_metabox' ), null, 'side', 'default' );
        }
    }


    /**
     * callback for FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes.
     * Add additional routes for saving Cert settings to the Registration Form admin page system
     *
     * @param array        $page_routes              current array of page routes.
     * @param EE_Admin_Page $admin_page
     * @since 1.0.0
     *
     * @return array
     */
    public static function add_cert_default_settings_route( $page_routes, EE_Admin_Page $admin_page ) {
        $page_routes['cert_settings'] = array(
            'func' => array( 'EED_Cert_Admin', 'cert_settings' ),
            'args' => array( $admin_page ),
            'capability' => 'manage_options'
            );
        $page_routes['update_cert_settings'] = array(
            'func' => array( 'EED_Cert_Admin', 'update_cert_settings' ),
            'args' => array( $admin_page ),
            'capability' => 'manage_options',
            'noheader' => true
            );
        return $page_routes;
    }

    public static function add_dtt_ce_box( $evt_obj, $data ) {
        foreach ( $dtts as $dtt ) {
            //trim all values to ensure any excess whitespace is removed.
            $dtts = array_map(
                function( $datetime_data ) {
                    return is_array( $datetime_data ) ? $datetime_data : trim( $datetime_data );
                },
                $dtt
            );
            $dtt['DTT_ID'] = isset($dtt['DTT_ID']) && ! empty( $dtt['DTT_ID'] );
            $datetime_values = array(
                'DTT_ID'             => ! empty( $dtt['DTT_ID'] ) ? $dtt['DTT_ID'] : NULL,
                'DTT_name'             => ! empty( $dtt['DTT_name'] ) ? $dtt['DTT_name'] : '',
                'DTT_description'     => ! empty( $dtt['DTT_description'] ) ? $dtt['DTT_description'] : '',
                'DTT_EVT_start'     => $dtt['DTT_EVT_start'],
                'DTT_EVT_end'         => $dtt['DTT_EVT_end'],
                'DTT_reg_limit'     => empty( $dtt['DTT_reg_limit'] ) ? EE_INF : $dtt[ 'DTT_reg_limit' ],
                'DTT_order'         => ! isset( $dtt['DTT_order'] ) ? $row : $dtt['DTT_order'],
                'DTT_has_credits'     => ! isset( $dtt['DTT_has_credits']) ? $row : $dtt['DTT_has_credits']
            );
        }
    }
    /**
     * callback for the cert_settings route.
     *
     * @param EE_Admin_Page $admin_page
     * @return string html for displaying cert_settings.
     */
    public static function cert_settings( EE_Admin_Page $admin_page ) {
        $template_args['admin_page_content'] = self::_cert_settings_form()->get_html_and_js();
        $admin_page->set_add_edit_form_tags( 'update_cert_settings' );
        $admin_page->set_publish_post_box_vars( null, false, false, null, false );
        $admin_page->set_template_args( $template_args );
        $admin_page->display_admin_page_with_sidebar();
    }


    /**
     * This outputs the settings form for cert_integration.
     *
     * @since 1.0.0
     *
     * @return string html form.
     */
    protected static function _cert_settings_form() {
        EE_Registry::instance()->load_helper( 'HTML' );
        EE_Registry::instance()->load_helper( 'Template' );

        return new EE_Form_Section_Proper(
            array(
                'name' => 'cert_settings_form',
                'html_id' => 'cert_settings_form',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___cert_settings_form__form_subsections',
                    array(
                        'main_settings_hdr' => new EE_Form_Section_HTML( EEH_HTML::h3( __('Event Certificate Defaults', 'event_espresso' ) ) ),
                        'main_settings' => EED_Cert_Admin::_main_settings()
                        )
                    )
                )
            );
    }


    /**
     * Output the main settings section for cert_integration settings page.
     *
     * @return string html form.
     */
    protected static function _main_settings() {
        global $wp_roles;

        return new EE_Form_Section_Proper(
            array(
                'name' => 'cert_settings_tbl',
                'html_id' => 'cert_settings_tbl',
                'html_class' => 'form-table',
                'layout_strategy' => new EE_Admin_Two_Column_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___main_settigns__form_subsections',
                    array(
                        'has_credits' => new EE_Yes_No_Input(
                            array(
                                'html_label_text' => __( 'Default setting for new events', 'event_espresso' ),
                                'html_help_text' => __( 'When this is set to "Yes", that means when you create an event the default for the "Has credits?" will be set to "Yes".  When Has Credits is set to "Yes" on an event it means that a CE credit value will be attached to each event. This is not advisable for most events. You can still override this on each event.', 'event_espresso' ),
                                'default' => isset( EE_Registry::instance()->CFG->addons->cert->has_credits ) ? EE_Registry::instance()->CFG->addons->cert->has_credits : false,
                                'display_html_label_text' => false
                                )
                            ),
                        'ce_credits' => new EE_Text_Input(
                            array(
                                'html_label_text' => __( 'CE Credit Amount', 'event_espresso' ),
                                'html_help_text' => __( 'On new events, when Has Credits is set to Yes, this default value will show. You can still override this on each event.', 'event_espresso' ),
                                'default' => isset( EE_Registry::instance()->CFG->addons->cert->ce_credits ) ? EE_Registry::instance()->CFG->addons->cert->ce_credits : '',
                                'display_html_label_text' => false
                                )
                            ),
                        'cert_available' => new EE_Yes_No_Input(
                            array(
                                'html_label_text' => __( 'Create cert for event?', 'event_espresso' ) . EEH_Template::get_help_tab_link( 'user_sync_info'),
                                'html_help_text' => __(
                                    'This global option is used to indicate behaviour when an event has passed, and if a certificate is available to registrants, which is related to each attendee.',
                                    'event_espresso'
                                    ),
                                'default' => isset( EE_Registry::instance()->CFG->addons->cert->cert_available ) ? EE_Registry::instance()->CFG->addons->cert->cert_available : false,
                                'display_html_label_text' => false
                                )
                            )
                        ) //end form subsections
                    ) //end apply_filters for form subsections
                )
            );
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
                    $config->has_credits = $valid_data['datetime_settings']['has_credits'];
                    $config->ce_credits = $valid_data['datetime_settings']['ce_credits'];
                    $config->cert_available = $valid_data['datetime_settings']['cert_available'];
                }
            } else {
                if ( $form->submission_error_message() != '' ) {
                    EE_Error::add_error( $form->submission_error_message(), __FILE__, __FUNCTION__, __LINE__ );
                }
            }
        } catch( EE_Error $e ) {
            $e->get_error();
        }

        EE_Error::add_success( __('Certificate Settings updated.', 'event_espresso' ) );
        EE_Registry::instance()->CFG->update_config( 'addons', 'cert', $config );
        $admin_page->redirect_after_action( false, '', '', array( 'action' => 'cert_settings' ), true );
    }

    /**
     * callback for FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_config.
     * Add additional config for saving WP_User settings to the Registration Form admin page system.
     *
     * @param array        $page_config current page config.
     * @param EE_Admin_Page $admin_page
     * @since  1.0.0
     *
     * @return array
     */
   /* public static function add_cert_default_settings_config( $page_config, EE_Admin_Page $admin_page) {
        $page_config['cert_settings'] = array(
            'nav' => array(
                'label' => __( 'Certificate Settings', 'event_espresso' ),
                'order' => 50
                ),
            'require_nonce' => false,
            'help_tabs' => array(
                'cert_settings_help_tab' => array(
                    'title' => __( 'Certificate Settings', 'event_espresso' ),
//                    'content' => self::_settings_help_tab_content()
                )
            ),
            'metaboxes' => array( '_publish_post_box', '_espresso_news_post_box', '_espresso_links_post_box' )
            );
        return $page_config;
    } */

    /**
     * This is the metabox content for the cert in the event editor.
     *
     * @param WP_Post $post
     * @param array $metabox metabox arguments
     *
     * @return string html for metabox content.
     */
    public static function event_editor_metabox( $post, $metabox ) {
        //setup form and print out!
        echo self::_get_event_editor_wp_users_form( $post )->get_html_and_js();
    }

    /**
     * Generate the event editor cert form.
     *
     * @return EE_Form_Section_Proper
     */
    protected static function _get_event_editor_wp_users_form( $post ) {
        global $wp_roles;
        $evt_id = $post instanceof EE_Event ? $post->ID() : null;
        $evt_id = empty( $evt_id ) &&  isset( $post->ID ) ? $post->ID : 0;
        EE_Registry::instance()->load_helper( 'HTML' );

        return new EE_Form_Section_Proper(
            array(
                'name' => 'cert_event_settings_form',
                'html_id' => 'cert_event_settings_form',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin__event_editor_metabox__cert_form_content', array(
                    'has_credits' => new EE_Yes_No_Input(
                        array(
                            'html_label_text' => __('Event has CE credits?', 'event_espresso' ),
                            'html_help_text' => __( 'If yes, event is counted as a class that provides CE credits.', 'event_espresso' ),
                            'default' => EE_Cert::is_datetime_has_credits_on( $evt_id ),
                            'display_html_label_text' => true
                            )
                        ),
                    'spacing1' => new EE_Form_Section_HTML( '<br>' ),
                    'ce_credits' => new EE_Text_Input(
                        array(
                            'html_label_text' => __('CE Credits earned for event', 'event_espresso' ),
                            'html_help_text' => __( 'Please enter the CE credits that will be earned for this event.', 'event_espresso' ),
                            'default' => EE_Cert::has_ce_credits( $evt_id ),
                            'display_html_label_text' => ''
                            )
                        ),
                    )
                )
            )
        );
    }

    /**
     * Callback for FHEE__EED_Cert_Admin__event_editor_metabox__cert_form_content.
     * Limit the Default role for auto-created users option to roles with manage_options cap.
     *
     * @param array $array of meta_box subsections.
     *
     * @return array
     */
    public static function set_capability_default_user_has_credits_event_editor( $array ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            unset( $array['has_credits'] );
        }
        return $array;
    }

    /**
     * callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks
     * Set the callback for updating cert_settings on event update.
     *
     * @param  array $callbacks existing array of callbacks.
     */
    public static function set_callback_save_cert_event_setting( $callbacks ) {
        $callbacks = array( 'EED_Cert_Admin', 'save_cert_event_setting' );
        return $callbacks;
    }

    /**
     * Callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks.
     * Saving Cert event specific settings when events updated.
     *
     * @param EE_Event $event
     * @param array   $req_data request data.
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
     * Callback for FHEE__EE_Admin_Page___publish_post_box__box_label.
     * Used to change the label to something more descriptive for the WP_Users settings page.
     *
     * @param string        $box_label  original label
     * @param string        $route      The route (used to target the specific box)
     * @param EE_Admin_Page $admin_page
     *
     * @return string        New label
     */
    public static function modify_settings_publish_box_label( $box_label, $route, EE_Admin_Page $admin_page )  {
        if ( $route == 'cert_settings' ) {
            $box_label = __('Update Settings', 'event_espresso' );
        }
        return $box_label;
    }

    /**
     * This is used to add the form to the datetimes for the certs.
     *
     * @since 1.0.0
     * @param string|int $tkt_row This will either be the ticket row number for an existing ticket or
     *                                             'TICKETNUM' for ticket skeleton.
     * @param int $TKT_ID          The id for a Ticket or 0 (which is not for any ticket)
     *
     * @return string form for capabilities required.
     */

    public static function insert_datetime_meta_interface( $dtt_row, $DTT_ID ) {
        //build our form and print.
        echo self::_get_datetime_credits_form( $dtt_row, $DTT_ID )->get_html_and_js();
    }


    /**
     * Form generator for credits field on datetimes.
     *
     * @since 1.0.0
     * @see EED_Cert_Admin::insert_datetime_meta_interface for params documentation
     *
     * @return string
     */
    protected static function _get_datetime_credits_form( $dtt_row, $DTT_ID ) {
        $datetime = EE_Registry::instance()->load_model('Datetime')->get_one_by_ID( $DTT_ID );

        EE_Registry::instance()->load_helper( 'HTML' );
        return new EE_Form_Section_Proper(
            array(
                'name' => 'cert-datetime-credits-container-' . $datetime,
                'html_class' => 'cert-datetime-credits-container',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___get_datetime_credits_form__form_subsections',
                    array(
                        'datetime_capability_hdr-' . $datetime => new EE_Form_Section_HTML( EEH_HTML::h5( __( ' Credits Earned', 'event_espresso' ). EEH_Template::get_help_tab_link( 'datetime_credits_info' ), '', 'datetimes-heading' )),
                        'TKT_credits' => new EE_Text_Input(
                            array(
                                'html_class' => 'DTT-credits',
                                'html_name' => 'cert_datetime_credits_input[' . $datetime . '][DTT_credits]',
                                'html_label_text' => __('CE credits earned for this datetime:', 'event_espresso'),
                                'default' => '',
                                'display_html_label_text' => true
                                )
                            )
                        ) // end EE_Form_Section_Proper subsections
                    ) // end subsections apply_filters
                ) //end  main EE_Form_Section_Proper options array
            ); //end EE_Form_Section_Proper
    }

    /**
     * Callback for AHEE__espresso_events_Pricing_Hooks___update_dtts_update_datetime.
     * Used to hook into datetime saves so that we update any values for a datetime.
     */
    public static function update_credits_on_datetime( EE_Datetime $dtt, $dtt_row, $dtt_form_data, $all_form_data ) {
        try {
            $datetime_id = $dtt_form_data instanceof EE_Datetime ? $dtt_form_data->ID() : $dtt->ID();
            $form = self::_get_datetime_credits_form( $dtt_row, $datetime_id );
            if ( $form->was_submitted() ) {
                $form->receive_form_submission();
                if ( $form->is_valid() ) {
                    $valid_data = $form->valid_data();
                    $dtt->update_extra_meta( 'ee_datetime_ce_credits', $valid_data['CE_credits'] );
                }
            }
        } catch ( EE_Error $e ) {
            $e->get_error();
        }
    }


    /**
     * Callback for AHEE__EE_Base__delete__before to handle ensuring any relations Certificates has set up with the
     * EE_Base_Class child object is handled when the object is permanently deleted.
     *
     * @param EE_Base_Class $model_object
     */
    public static function remove_relations_on_delete( EE_Base_Class $model_object ) {
        if ( $model_object instanceof EE_Event ) {
            delete_post_meta( $model_object->ID(), 'EE_Cert_integration_settings' );
        }

        if ( $model_object instanceof EE_Datetime ) {
            $model_object->delete_extra_meta( 'ee_datetime_ce_credits' );
        }
    }

} //end EED_Cert_Admin

