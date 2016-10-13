<?php
/**
 * Template for the "event_section" content template for each ticket/registration row via [ESPRESSO_MY_EVENTS] shortcode
 *
 * Available template args:
 * @type    $registration EE_Registration registration object
 */
$ticket = $registration->ticket();
$credits_tkt = $registration->ticket_ID();
$reg_id = $registration->ID();
$credits = $ticket instanceof EE_Ticket ? $ticket->get_extra_meta( 'ee_ticket_credits', true, '' ) : '';
$checked_in = $registration->check_in_status_for_datetime();
$reg_count = $registration->count();
$reg_check_count = $registration->count_checkins_not_checkedout();
?>
<tr>
    <td class="ee-status-strip reg-status-<?php echo $registration->status_ID(); ?>"></td>
    <td>
        <?php echo $ticket instanceof EE_Ticket ? $ticket->name() : ''; ?>
    </td>
    <td>
        <?php echo $ticket instanceof EE_Ticket ? $ticket->date_range() : ''; ?>
    </td>
    <td style="text-align: center;">
        <?php if($credits > 0 && $checked_in == 1) { echo $credits; }; ?>
    </td>
    <td style="line-height: 2em;">
        <?php
        $actions = array();
        //only show the edit registration link IF the registration has question groups.
        $actions['edit_registration'] = $registration->count_question_groups()
            ? '<a aria-label="' . __( 'Link to edit registration', 'event_espresso' ) . '" href="' . $registration->edit_attendee_information_url() . '">'
                                        . '<span class="ee-icon ee-icon-user-edit ee-icon-size-16"></span></a>'
            : '';
        //resend confirmation email.
        $resend_registration_link = add_query_arg(
            array( 'token' => $registration->reg_url_link(), 'resend' => true ),
            get_permalink( EE_Registry::instance()->REQ->get_post_id_from_request() )
        );
        if ( $registration->is_primary_registrant() ||
             ( ! $registration->is_primary_registrant()
               && $registration->status_ID() == EEM_Registration::status_id_approved ) ) {

            $actions['resend_registration'] = '<a aria-label="' . __( 'Link to resend registration message', 'event_espresso' ) . '" href="' . $resend_registration_link . '">'
                                              . '<span class="dashicons dashicons-email-alt"></span></a>';
        }

        //make payment?
        if ( $registration->is_primary_registrant()
             && $registration->transaction() instanceof EE_Transaction
             && $registration->transaction()->remaining() ) {
            $actions['make_payment'] = '<a aria-label="' . __( 'Link to make payment', 'event_espresso' ) . '" href="' . $registration->payment_overview_url() . '">'
                                       . '<span class="dashicons dashicons-cart"></span></a>';
        }

        //receipt link?
        if ( $registration->is_primary_registrant() && $registration->receipt_url() ) {
            $actions['receipt'] = '<a aria-label="' . __( 'Link to view receipt', 'event_espresso' ) . '" href="' . $registration->receipt_url() . '">'
                                  . '<span class="dashicons dashicons-media-default ee-icon-size-18"></span></a>';
        }

        //invoice link?
        if ( $registration->is_primary_registrant() && $registration->invoice_url() ) {
            $actions['invoice'] = '<a aria-label="' . __( 'Link to view invoice', 'event_espresso' ) . '" href="' . $registration->invoice_url() . '">'
                                  . '<span class="dashicons dashicons-media-spreadsheet ee-icon-size-18"></span></a>';
        }

        //filter actions
        $actions = apply_filters( 'FHEE__EES_Espresso_My_Events__actions',
            $actions,
            $registration
        );

        //...and echo the actions!
        echo implode( '&nbsp;', $actions );
        ?>
        <?php if($credits > 0 && $checked_in == 1) : ?>
        <a href="https://amtanewyork.org/view-certificate/?credits_id=<?php echo $reg_id ?>" target="_blank" style="color: #fff;"><div class="submit-dark" style="width: 90px; text-align: center; line-height: 1.2em; background-color: #336597; color: #fff; border: 1px solid #fff; border-radius: 3px; margin-top: 5px;">View Certificate</div></a>
        <?php endif; ?>
    </td>
</tr>

