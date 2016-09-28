<?php
if ( ! defined('EVENT_ESPRESSO_VERSION')) exit('No direct script access allowed');
/**
 * This file contains the module for the EE WP Users addon ee admin integration
 *
 * @since 1.0.0
 * @package  EE WP Users
 * @subpackage modules, admin
 */
/**
 *
 * EED_Cert_Adminmodule.  Takes care of WP Users integration with EE admin.
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
        add_action( 'admin_enqueue_scripts', array( 'EED_Cert_Admin', 'admin_enqueue_scripts_styles' ) );

        //hook into EE contact publish metabox.
       // add_action( 'post_submitbox_misc_actions', array( 'EED_Cert_Admin', 'event_has_credits' ) );

        //hook into attendee saves
        // add_filter( 'FHEE__Registrations_Admin_Page__insert_update_cpt_item__attendee_update', array( 'EED_Cert_Admin', 'add_sync_with_Cert_callback' ), 10 );

        //hook into registration_form_admin_page routes and config.
        add_filter( 'FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes', array( 'EED_Cert_Admin', 'add_credits_default_settings_route' ), 10, 2 );
        add_filter( 'FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_config', array( 'EED_Cert_Admin', 'add_credits_default_settings_config' ), 10, 2 );
        add_filter( 'FHEE__Extend_Events_Admin_Page__page_setup__page_config', array( 'EED_Cert_Admin', 'add_datetime_capability_help_tab' ), 10, 2 );
        add_filter( 'FHEE__EE_Admin_Page___publish_post_box__box_label', array( 'EED_Cert_Admin', 'modify_settings_publish_box_label' ), 10, 3 );

        //hooking into event editor
        add_action( 'add_meta_boxes', array( 'EED_Cert_Admin', 'add_metaboxes' ) );
        add_filter( 'FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks', array( 'EED_Cert_Admin', 'set_callback_save_credits_event_setting' ), 10, 2 );
        add_filter( 'FHEE__EED_Cert_Admin__event_editor_metabox__credits_form_content', array( 'EED_Cert_Admin', 'set_capability_has_credits_event_editor' ), 10 );

        //hook into datetime editor in event editor.
        add_action('AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end', array('EED_Cert_Admin', 'insert_datetime_meta_interface'), 10, 2);
        add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_new_ticket', array( 'EED_Cert_Admin', 'update_capability_on_datetime') , 10, 4 );
        add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_update_ticket', array( 'EED_Cert_Admin', 'update_capability_on_datetime' ), 10, 4 );
        add_action( 'AHEE__espresso_events_Pricing_Hooks___update_tkts_new_default_ticket', array( 'EED_Cert_Admin', 'update_capability_on_datetime' ), 10, 4 );


        //hook into model deletes that may affect relations set on WP_User.
        add_action( 'AHEE__EE_Base_Class__delete_permanently__before', array( 'EED_Cert_Admin', 'remove_relations_on_delete' ) );
    }


    public static function admin_enqueue_scripts_styles() {
        if ( get_current_screen()->base == 'profile' || get_current_screen()->base == 'user-edit') {
            wp_register_style('ee-admin-css', EE_ADMIN_URL . 'assets/ee-admin-page.css', array(), EVENT_ESPRESSO_VERSION);
            wp_register_style('espresso_att', REG_ASSETS_URL . 'espresso_attendees_admin.css', array('ee-admin-css'), EVENT_ESPRESSO_VERSION );
            wp_enqueue_style('espresso_att');
        }
    }


    public function run( $WP ) {}



    /**
     * Register metaboxes for event editor.
     */
    public static function add_metaboxes() {
        $page = EE_Registry::instance()->REQ->get( 'page' );
        $route = EE_Registry::instance()->REQ->get( 'action' );

        // on event editor page?
        if ( $page == 'espresso_events' && ( $route == 'edit' || $route == 'create_new' ) ) {
            add_meta_box( 'eea_cert', __('Certificates Settings', 'event_espresso' ), array( 'EED_Cert_Admin', 'event_editor_metabox' ), null, 'side', 'default' );
        }
    }



    public static function add_sync_with_Cert_callback( $callbacks ) {
        $callbacks[] = array( 'EED_Cert_Admin', 'sync_with_wp_user' );
        return $callbacks;
    }



    /**
     * Callback for post_submitbox_misc_actions that adds a link to the wp user
     * edit page for the user attached to the EE_Attendee (if present).
     *
     * @since 1.0.0
     */
    public static function add_link_to_wp_user_account() {
        global $post;
        if ( ! $post instanceof WP_Post || $post->post_type != 'espresso_attendees' ) {
            return;
        }

        //is there an attached wp_user for this attendee record?
        $user_id = EE_Cert::get_attendee_user( $post->ID );

        if ( empty( $user_id ) ) {
            return;
        }


        //let's get the WP_user and setup the link
        $url = get_edit_user_link( $user_id );

        //if $url is empty, that means logged in user does not have access to view user details so we bail.
        if ( empty( $url ) ) {
            return;
        }
    }


    /**
     * callback for edit_user_profile that is used to display a table of all the registrations this
     * user is connected with.
     *
     * @param WP_User $user
     * @return string
     */
    public static function view_registrations_for_contact( $user ) {
        if ( ! $user instanceof WP_User ) {
            return '';
        }

        //is there an attadched EE_Attendee?
        $att_id = get_user_option( 'EE_Attendee_ID', $user->ID );

        if ( empty( $att_id ) ) {
            return; //bail, no attached attendee_id.
        }

        //grab contact
        $contact = EEM_Attendee::instance()->get_one_by_ID( $att_id );

        //if no contact then bail
        if ( ! $contact instanceof EE_Attendee ) {
            return;
        }

        $template_args = array(
            'attendee' => $contact,
            'registrations' => $contact->get_many_related( 'Registration' )
        );
        EEH_Template::display_template( EE_CERT_TEMPLATE_PATH . 'eea-wp-users-registrations-table.template.php', $template_args  );
    }



    /**
     * callback for edit_user_profile that is used to add link to the EE_Attendee
     * details if there is one attached to the user.
     *
     * @param WP_User $user
     */
    public static function add_link_to_ee_contact_details( $user ) {
        if ( ! $user instanceof WP_User ) {
            return;
        }

        //is there an attached EE_Attendee?
        $att_id = get_user_option( 'EE_Attendee_ID', $user->ID );

        if ( empty( $att_id ) ) {
            return; //bail, no attached attendee_id.
        }

        //does logged in user have the capability to edit this attendee?
        if ( ! EE_Registry::instance()->CAP->current_user_can( 'ee_edit_contacts', 'edit_attendee', $att_id ) )  {
            return; //bail no access.
        }

        //url
        $url = admin_url( add_query_arg( array(
            'page' => 'espresso_registrations',
            'action' => 'edit_attendee',
            'post' => $att_id
            ), 'admin.php' ) );
        ?>
        <table class="form-table">
            <tr class="ee-cert-row">
                <th></th>
                <td>
                </td>
            </tr>
        </table>
        <?php
    }


    /**
     * callback for FHEE__Extend_Registration_Form_Admin_Page__page_setup__page_routes.
     * Add additional routes for saving WP_User settings to the Registration Form admin page system
     *
     * @param array        $page_routes              current array of page routes.
     * @param EE_Admin_Page $admin_page
     * @since 1.0.0
     *
     * @return array
     */
    public static function add_credits_default_settings_route( $page_routes, EE_Admin_Page $admin_page ) {
        $page_routes['credits_settings'] = array(
            'func' => array( 'EED_Cert_Admin', 'credits_settings' ),
            'args' => array( $admin_page ),
            'capability' => 'manage_options'
            );
        $page_routes['update_credits_settings'] = array(
            'func' => array( 'EED_Cert_Admin', 'update_credits_settings' ),
            'args' => array( $admin_page ),
            'capability' => 'manage_options',
            'noheader' => true
            );
        return $page_routes;
    }


    /**
     * callback for the credits_settings route.
     *
     * @param EE_Admin_Page $admin_page
     * @return string html for displaying credits_settings.
     */
    public static function credits_settings( EE_Admin_Page $admin_page ) {
        $template_args['admin_page_content'] = self::_credits_settings_form()->get_html_and_js();
        $admin_page->set_add_edit_form_tags( 'update_credits_settings' );
        $admin_page->set_publish_post_box_vars( null, false, false, null, false );
        $admin_page->set_template_args( $template_args );
        $admin_page->display_admin_page_with_sidebar();
    }





    /**
     * This outputs the settings form for Cert.
     *
     * @since 1.0.0
     *
     * @return string html form.
     */
    protected static function _credits_settings_form() {
        EE_Registry::instance()->load_helper( 'HTML' );
        EE_Registry::instance()->load_helper( 'Template' );

        return new EE_Form_Section_Proper(
            array(
                'name' => 'credits_settings_form',
                'html_id' => 'credits_settings_form',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___credits_settings_form__form_subsections',
                    array(
                        'main_settings_hdr' => new EE_Form_Section_HTML( EEH_HTML::h3( __('CE Credits Defaults', 'event_espresso' ) ) ),
                        'main_settings' => EED_Cert_Admin::_main_settings()
                        )
                    )
                )
            );
    }




    /**
     * Output the main settings section for cert settings page.
     *
     * @return string html form.
     */
    protected static function _main_settings() {
        global $wp_roles;

        return new EE_Form_Section_Proper(
            array(
                'name' => 'credits_settings_tbl',
                'html_id' => 'credits_settings_tbl',
                'html_class' => 'form-table',
                'layout_strategy' => new EE_Admin_Two_Column_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___main_settigns__form_subsections',
                    array(
                        'has_credits' => new EE_Yes_No_Input(
                            array(
                                'html_label_text' => __( 'Default setting for CE Credits', 'event_espresso' ),
                                'html_help_text' => __( 'When this is set to "Yes", that means any new event will have to have a CE credits amount specified for each datetime. You can still override this on each event.', 'event_espresso' ),
                                'default' => isset( EE_Registry::instance()->CFG->addons->cert->has_credits ) ? EE_Registry::instance()->CFG->addons->cert->has_credits : false,
                                'display_html_label_text' => false
                                )
                            ),
                        'registration_page' => new EE_Text_Input(
                            array(
                                'html_label_text' => __( 'Registration Page URL (if different from default WordPress Registration)', 'event_espresso' ),
                                'html_help_text' => __( 'When login is required on an event, this will be the url used for the registration link on the login form', 'event_espresso' ),
                                'default' => isset( EE_Registry::instance()->CFG->addons->cert->registration_page ) ? EE_Registry::instance()->CFG->addons->cert->registration_page : '',
                                'display_html_label_text' => true
                            )
                        ),
                        'auto_create_user' => new EE_Yes_No_Input(
                            array(
                                'html_label_text' => __( 'Default setting for User Creation on Registration.', 'event_espresso' ),
                                'html_help_text' => __( 'When this is set to "Yes", that means when you create an event the default for the "Create User On Registration" setting on that event will be set to "Yes".  When this setting is set to "Yes" on an event it means that when new non-logged in users register for an event, a new WP_User is created for them.  You can still override this on each event.', 'event_espresso' ),
                                'default' => isset( EE_Registry::instance()->CFG->addons->cert->auto_create_user ) ? EE_Registry::instance()->CFG->addons->cert->auto_create_user : false,
                                'display_html_label_text' => false
                                )
                            ),
                        ) //end form subsections
                    ) //end apply_filters for form subsections
                )
            );
    }




    /**
     * callback for the update_credits_settings route.
     * This handles the config update when the settings are saved.
     *
     * @param EE_Admin_Page $admin_page
     *
     * @return void
     */
    public static function update_credits_settings( EE_Admin_Page $admin_page ) {
        $config = EE_Registry::instance()->CFG->addons->cert;
        try {
            $form = self::_credits_settings_form();
            if ( $form->was_submitted() ) {
                //capture form data
                $form->receive_form_submission();

                //validate_form_data
                if ( $form->is_valid() ) {
                    $valid_data = $form->valid_data();
                    $config->has_credits = $valid_data['main_settings']['has_credits'];
                    $config->ce_credits = $valid_data['main_settings']['ce_credits'];
                }
            } else {
                if ( $form->submission_error_message() != '' ) {
                    EE_Error::add_error( $form->submission_error_message(), __FILE__, __FUNCTION__, __LINE__ );
                }
            }
        } catch( EE_Error $e ) {
            $e->get_error();
        }

        EE_Error::add_success( __('Certificates Settings updated.', 'event_espresso' ) );
        EE_Registry::instance()->CFG->update_config( 'addons', 'cert', $config );
        $admin_page->redirect_after_action( false, '', '', array( 'action' => 'credits_settings' ), true );
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
    public static function add_credits_default_settings_config( $page_config, EE_Admin_Page $admin_page) {
        $page_config['credits_settings'] = array(
            'nav' => array(
                'label' => __( 'CE Credits Settings', 'event_espresso' ),
                'order' => 50
                ),
            'require_nonce' => false,
            'help_tabs' => array(
                'credits_settings_help_tab' => array(
                    'title' => __( 'CE Credits Settings', 'event_espresso' ),
                    'content' => self::_settings_help_tab_content()
                )
            ),
            'metaboxes' => array( '_publish_post_box', '_espresso_news_post_box', '_espresso_links_post_box' )
            );
        return $page_config;
    }



    /**
     * Callback for the WP Users Settings help tab content as set in the page_config array
     *
     * @return string
     */
    protected static function _settings_help_tab_content() {
        EE_Registry::instance()->load_helper( 'Template' );
        return EEH_Template::display_template( EE_CERT_TEMPLATE_PATH . 'settings_help_tab.help_tab.php', array(), true );
    }





    /**
     * Callback for FHEE__Extend_Events_Admin_Page__page_setup__page_config.
     * Just injecting config for help tab contents added for datetime capability fields.
     *
     * @param array        $page_config current page config
     * @param EE_Admin_Page $admin_page
     * @since 1.0.0
     *
     * @return array
     */
    public static function add_datetime_capability_help_tab( $page_config, EE_Admin_Page $admin_page ) {
        EE_Registry::instance()->load_helper('Template');
        $file = EE_CERT_TEMPLATE_PATH . 'datetime_capability_help_content.template.php';
        $page_config['create_new']['help_tabs']['datetime_capability_info'] = array(
            'title' => __( 'Datetime Capability Restrictions', 'event_espresso' ),
            'content' => EEH_Template::display_template($file,array(),true)
            );
        $page_config['edit']['help_tabs']['datetime_capability_info'] = array(
            'title' => __( 'Datetime Capability Restrictions', 'event_espresso' ),
            'content' => EEH_Template::display_template($file,array(),true)
            );
        return $page_config;
    }




    /**
     * This is the metabox content for the wp user integration in the event editor.
     *
     * @param WP_Post $post
     * @param array $metabox metabox arguments
     *
     * @return string html for metabox content.
     */
    public static function event_editor_metabox( $post, $metabox ) {
        //setup form and print out!
        echo self::_get_event_editor_Cert_form( $post )->get_html_and_js();
    }




    /**
     * Generate the event editor wp user settings form.
     *
     * @return EE_Form_Section_Proper
     */
    protected static function _get_event_editor_cert_form( $post ) {
        global $wp_roles;
        $evt_id = $post instanceof EE_Event ? $post->ID() : null;
        $evt_id = empty( $evt_id ) &&  isset( $post->ID ) ? $post->ID : 0;
        EE_Registry::instance()->load_helper( 'HTML' );

        return new EE_Form_Section_Proper(
            array(
                'name' => 'credits_event_settings_form',
                'html_id' => 'credits_event_settings_form',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin__event_editor_metabox__credits_form_content', array(
                    'has_credits' => new EE_Yes_No_Input(
                        array(
                            'html_label_text' => __('Event has CE credits?', 'event_espresso' ),
                            'html_help_text' => __( 'If yes, then all datetimes must have a CE credit amount specified.', 'event_espresso' ),
                            'default' => 'No',
                            'display_html_label_text' => true
                            )
                        ),
                    'spacing1' => new EE_Form_Section_HTML( '<br>' ),
                    'default_ce_credits' => new EE_Select_Input(
                        $wp_roles->get_names(),
                        array(
                            'html_label_text' => __('Default CE credit amount', 'event_espresso' ),
                            'html_help_text' => __( '', 'event_espresso' ),
                            'default' => '',
                            'display_html_label_text' => true
                            )
                        ),
                    )
                )
            )
        );
    }

    /**
     * Callback for FHEE__EED_Cert_Admin__event_editor_metabox__credits_form_content.
     * Limit the Default role for auto-created users option to roles with manage_options cap.
     *
     * @param array $array of meta_box subsections.
     *
     * @return array
     */
    public static function set_capability_has_credits_event_editor( $array ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            unset( $array['has_credits'] );
        }
        return $array;
    }

    /**
     * callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks
     * Set the callback for updating credits_settings on event update.
     *
     * @param  array $callbacks existing array of callbacks.
     */
    public static function set_callback_save_credits_event_setting( $callbacks ) {
        $callbacks[] = array( 'EED_Cert_Admin', 'save_credits_event_setting' );
        return $callbacks;
    }




    /**
     * Callback for FHEE__Events_Admin_Page___insert_update_cpt_item__event_update_callbacks.
     * Saving WP_User event specific settings when events updated.
     *
     * @param EE_Event $event
     * @param array   $req_data request data.
     *
     * @return bool   true success, false fail.
     */
    public static function save_cert_event_setting( EE_Event $event, $req_data ) {
        try {
            $form = self::_get_event_editor_Cert_form( $event );
            if ( $form->was_submitted() ) {
                $form->receive_form_submission();

                if ( $form->is_valid() ) {
                    $valid_data = $form->valid_data();
                    EE_Cert::update_event_has_credits( $event, $valid_data['has_credits'] );
                    EE_Cert::update_auto_create_user( $event, $valid_data['auto_user_create'] );
                    EE_Cert::update_default_credits_role( $event, $valid_data['default_ce_credits'] );
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

        EE_Error::add_success( __('CE Credits Event Settings updated.', 'event_espresso' ) );
        return true;
    }





    /**
     * Callback for FHEE__EE_Admin_Page___publish_post_box__box_label.
     * Used to change the label to something more descriptive for the Cert settings page.
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
     * Callback for AHEE__event_tickets_datetime_ticket_row_template__advanced_details_end.
     * This is used to add the form to the datetimes for the capabilities.
     *
     * @since 1.0.0
     * @param string|int $tkt_row This will either be the datetime row number for an existing datetime or
     *                                             'DATETIMENUM' for datetime skeleton.
     * @param int $TKT_ID          The id for a Datetime or 0 (which is not for any datetime)
     *
     * @return string form for capabilities required.
     */
    public static function insert_datetime_meta_interface($tkt_row, $TKT_ID) {
        //build our form and print.
        echo self::_get_datetime_capability_required_form( $tkt_row, $TKT_ID )->get_html_and_js();
    }




    /**
     * Form generator for capability field on datetimes.
     *
     * @since 1.0.0
     * @see EED_Cert_Admin::insert_datetime_meta_interface for params documentation
     *
     * @return string
     */
    protected static function _get_datetime_capability_required_form( $tkt_row, $TKT_ID ) {
        $datetime = EE_Registry::instance()->load_model('Datetime')->get_one_by_ID( $TKT_ID );
        $current_cap = $datetime instanceof EE_Datetime ? $datetime->get_extra_meta( 'ee_datetime_cap_required', true, '' ) : '';

        EE_Registry::instance()->load_helper( 'HTML' );

        return new EE_Form_Section_Proper(
            array(
                'name' => 'wp-user-datetime-capability-container-' . $tkt_row,
                'html_class' => 'wp-user-datetime-capability-container',
                'layout_strategy' => new EE_Div_Per_Section_Layout(),
                'subsections' => apply_filters( 'FHEE__EED_Cert_Admin___get_datetime_capability_required_form__form_subsections',
                    array(
                        'datetime_capability_hdr-' . $tkt_row => new EE_Form_Section_HTML( EEH_HTML::h5( __( 'Datetime Capability Requirement', 'event_espresso' ). EEH_Template::get_help_tab_link( 'datetime_capability_info' ), '', 'datetimes-heading' )),
                        'TKT_capability' => new EE_Text_Input(
                            array(
                                'html_class' => 'TKT-capability',
                                'html_name' => 'cert_datetime_capability_input[' . $tkt_row . '][TKT_capability]',
                                'html_label_text' => __('WP User Capability required for purchasing this datetime:', 'event_espresso'),
                                'default' => $current_cap,
                                'display_html_label_text' => true
                                )
                            )
                        ) // end EE_Form_Section_Proper subsections
                    ) // end subsections apply_filters
                ) //end  main EE_Form_Section_Proper options array
            ); //end EE_Form_Section_Proper
    }






    /**
     * Callback for AHEE__espresso_events_Pricing_Hooks___update_tkts_new_datetime and
     * AHEE__espresso_events_Pricing_Hooks___update_tkts_update_datetime.
     * Used to hook into datetime saves so that we update any capability requirement set for a datetime.
     *
     * @param EE_Datetime $tkt
     * @param int         $tkt_row       The datetime row this datetime corresponds with (used for knowing
     *                                          what form element to retrieve from).
     * @param array | EE_Datetime   $tkt_form_data The original incoming datetime form data OR the original created EE_Datetime from that form data
     *                                           depending on which hook this callback is called on.
     * @param array    $all_form_data All incoming form data for datetime editor (includes datetime data)
     *
     * @return void      This is an action callback so returns are ignored.
     */
    public static function update_capability_on_datetime( EE_Datetime $tkt, $tkt_row, $tkt_form_data, $all_form_data ) {
        try {
            $datetime_id = $tkt_form_data instanceof EE_Datetime ? $tkt_form_data->ID() : $tkt->ID();
            $form = self::_get_datetime_capability_required_form( $tkt_row, $datetime_id );
            if ( $form->was_submitted() ) {
                $form->receive_form_submission();
                if ( $form->is_valid() ) {
                    $valid_data = $form->valid_data();
                    $tkt->update_extra_meta( 'ee_datetime_cap_required', $valid_data['TKT_capability'] );
                }
            }
        } catch ( EE_Error $e ) {
            $e->get_error();
        }
    }


    /**
     * Callback for AHEE__EE_Base__delete__before to handle ensuring any relations WP_UserIntegration has set up with the
     * EE_Base_Class child object is handled when the object is permanently deleted.
     *
     * @param EE_Base_Class $model_object
     */
    public static function remove_relations_on_delete( EE_Base_Class $model_object ) {
        if ( $model_object instanceof EE_Event ) {
            delete_post_meta( $model_object->ID(), 'ee_wpcert_settings' );
        }

        if ( $model_object instanceof EE_Datetime ) {
            $model_object->delete_extra_meta( 'ee_datetime_cap_required' );
        }
    }

} //end EED_Cert_Admin
