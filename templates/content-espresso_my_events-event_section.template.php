<?php
/**
 * Modified template for the "event_section" content template for the [ESPRESSO_MY_EVENTS] shortcode
 *
 * Available template args:
 * @type    EE_Event $event  event object
 * @type    string   $your_tickets_title    title for the ticket.
 * @type    int      $att_id                The id of the EE_Attendee related to the displayed data.
 */
$registrations = $event->get_many_related('Registration', array( array( 'ATT_ID' => $att_id ) ) );
?>
<tr class="ee-my-events-event-section-summary-row">
    <td class="ee-status-strip event-status-<?php echo $event->get_active_status(); ?>"></td>
    <td>
        <a aria-labelledby="<?php printf( __( 'Link to %s', 'event_espresso' ), $event->name() ); ?>" href="<?php echo get_permalink( $event->ID() ); ?>"><?php echo $event->name(); ?></a>
    </td>
    <td>
        <?php espresso_event_date_range('', '', '', '', $event->ID() ); ?>
    </td>
    <td>
        <?php echo count( $registrations ); ?>
    </td>
    <td>
        <span class="dashicons dashicons-admin-generic js-ee-my-events-toggle-details"></span>
    </td>
</tr>
<tr class="ee-my-events-event-section-details-row">
    <td colspan="6">
        <div class="ee-my-events-event-section-details-inner-container">
            <section class="ee-my-events-event-section-details-event-description">
                <div class="ee-my-events-right-container">
                    <span class="dashicons dashicons-admin-generic js-ee-my-events-toggle-details"></span>
                </div>
                <h3><?php echo $event->name(); ?></h3>
                <?php
                /**
                 * There is a ticket for EE core: https://events.codebasehq.com/projects/event-espresso/tickets/8405 that hopefully
                 * will remove the necessity for the apply_filters() here.
                 */
                ?>
                <?php echo apply_filters( 'the_content', $event->description() ); ?>
            </section>
            <section class="ee-my-events-event-section-tickets-list-table-container">
                <h3><?php echo $your_tickets_title; ?></h3>
                <?php if ( $registrations ) : ?>
                    <table class="espresso-my-events-table simple-list-table">
                        <thead>
                            <tr>
                                <th scope="col" class="espresso-my-events-reg-status ee-status-strip">
                                </th>
                                <th scope="col" class="espresso-my-events-ticket-th">
                                    <?php echo apply_filters(
                                        'FHEE__content-espresso_my_events__table_header_ticket',
                                        esc_html__( 'Ticket', 'event_espresso' ),
                                        $event
                                    ); ?>
                                </th>
                                <th scope="col" class="espresso-my-events-datetimes-th">
                                    <?php echo apply_filters(
                                        'FHEE__content-espresso_my_events__table_header_datetimes',
                                        esc_html__( 'Dates & Times', 'event_espresso' ),
                                        $event
                                    ); ?>
                                </th>
                                <th scope="col" class="espresso-my-events-credits-th">
                                    <?php echo apply_filters(
                                        'FHEE__content-espresso_my_events__table_header_credits',
                                        esc_html__( 'Credits Earned', 'event_espresso' ),
                                        $event
                                    ); ?>
                                </th>
                                <th scope="col" class="espresso-my-events-actions-th">
                                    <?php echo apply_filters(
                                        'FHEE__content-espresso_my_events__actions_table_header',
                                        esc_html__( 'Actions', 'event_espresso' ),
                                        $event
                                    ); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach( $registrations as $registration ) {
                                if ( ! $registration instanceof EE_Registration ) {
                                    continue;
                                }
                                $template_args = array( 'registration' => $registration );
                                $template      =  'content-espresso_my_events-event_section_tickets.template.php';
                                EEH_Template::locate_template( $template, $template_args, true, false );
                            }
                            ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-tickets-container">
                        <p>
                            <?php echo apply_filters(
                                    'FHEE__content-espresso_my_events-no_tickets_message',
                                    esc_html__( 'You have no tickets for this event', 'event_espresso' ),
                                    $event
                                       ); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </td>
</tr>
