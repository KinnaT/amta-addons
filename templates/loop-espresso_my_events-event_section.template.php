<?php
/**
 * Modified template for the "event_section" loop template for the [ESPRESSO_MY_EVENTS] shortcode
 *
 * Available template args:
 * @type    string  $object_type  The type of object for objects in the 'object' array. It's expected for this template
 *                                that the type is 'Event'
 * @type    EE_Event[] $objects
 * @type    int     $object_count       Total count of all objects
 * @type    string  $your_events_title  The default label for the Events section
 * @type    string  $your_tickets_title The default label for the Tickets section
 * @type    string  $template_slug      The slug for the template.  For this template it will be 'simple_list_table'
 * @type    int     $per_page           What items are shown per page
 * @type    string  $path_to_template   The full path to this template
 * @type    int     $page               What the current page is (for the paging html).
 * @type    string  $with_wrapper       Whether to include the wrapper containers or not.
 * @type    int     $att_id             Attendee ID all the displayed data belongs to.
 */
$url = EES_Espresso_My_Events::get_current_page();
$pagination_html = EEH_Template::get_paging_html(
    $object_count,
    $page,
    $per_page,
    $url,
    false,
    'ee_mye_page',
    array(
        'single' => __( 'event', 'event_espresso' ),
        'plural' => __( 'events', 'event_espresso' )
    ));
?>
<?php if ( $with_wrapper ) : ?>
    <div class="espresso-my-events <?php echo $template_slug;?>_container">
    <?php do_action( 'AHEE__loop-espresso_my_events__before', $object_type, $objects, $template_slug, $att_id ); ?>
    <h3><?php echo $your_events_title; ?></h3>
    <div class="espresso-my-events-inner-content">
<?php endif; //$with_wrapper check ?>
<?php if ( $objects && reset( $objects ) instanceof EE_Event ) : ?>
    <table class="espresso-my-events-table <?php echo $template_slug;?>_table">
        <thead>
        <tr>
            <th scope="col" class="espresso-my-events-event-status ee-status-strip">
            </th>
            <th scope="col" class="espresso-my-events-event-th">
                <?php echo apply_filters(
                    'FHEE__loop-espresso_my_events__table_header_event',
                    esc_html__( 'Event', 'event_espresso' ),
                    $object_type,
                    $objects,
                    $template_slug,
                    $att_id
                ); ?>
            </th>
            <th scope="col" class="espresso-my-events-datetime-range-th">
                <?php echo apply_filters(
                    'FHEE__loop-espresso_my_events__datetime_range_table_header',
                    esc_html__( 'Date', 'event_espresso' ),
                    $object_type,
                    $objects,
                    $template_slug,
                    $att_id
                ); ?>
            </th>
            <th scope="col" class="espresso-my-events-tickets-num-th">
                <?php echo apply_filters(
                    'FHEE__loop-espresso_my_events__tickets_num_table_header',
                    esc_html__( 'Num. Registrations', 'event_espresso' ),
                    $object_type,
                    $objects,
                    $template_slug,
                    $att_id
                ); ?>
            </th>
            <th scope="col" class="espresso-my-events-actions-th">
                <?php echo apply_filters(
                    'FHEE__loop-espresso_my_events__actions_table_header',
                    esc_html__( 'Details & Actions', 'event_espresso' ),
                    $object_type,
                    $objects,
                    $template_slug,
                    $att_id
                ); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ( $objects as $object ) :
            if ( ! $object instanceof EE_Event ) {
                continue;
            }
            $template_args = array( 'event' => $object, 'your_tickets_title' => $your_tickets_title, 'att_id' => $att_id );
            $template =  'content-espresso_my_events-event_section.template.php';
            EEH_Template::locate_template( $template, $template_args, true, false );
            ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="espresso-my-events-footer">
        <div class="espresso-my-events-pagination-container <?php echo $template_slug;?>-pagination">
            <span class="spinner"></span>
            <?php echo $pagination_html; ?>
            <div style="clear: both"></div>
        </div>
        <div style="clear: both"></div>
        <?php EEH_Template::locate_template( 'status-legend-espresso_my_events.template.php', array( 'template_slug' => $template_slug ), true, false ); ?>
    </div>
<?php else : ?>
    <div class="no-events-container">
        <p><?php echo apply_filters(
                'FHEE__loop-espresso_my_events__no_events_message',
                esc_html__( 'You have no events yet', 'event_espresso' ),
                $object_type,
                $objects,
                $template_slug,
                $att_id
            ); ?>
        </p>
    </div>
<?php endif; ?>
<?php if ( $with_wrapper ) : ?>
    </div>
    <?php do_action( 'AHEE__loop-espresso_my_events__after', $object_type, $objects, $template_slug, $att_id ); ?>
    </div>
<?php endif; //end $wrapper check?>
<?php if( is_user_logged_in() ) {
function get_legacy_credits($output_type=OBJECT) {
global $wpdb;
global $current_user;
get_currentuserinfo();
$user = $current_user->user_login;
$leg_table  = $wpdb->prefix . 'ce_credits';
return $wpdb->get_results("SELECT * FROM {$leg_table} WHERE `username` = '$user'", OBJECT);
}
$legacy_credits = get_legacy_credits();
// print_r($legacy_credits);
if (!empty($legacy_credits)) :
?>
<div class="legacy-credits">
    <h3>CE Credits Earned Prior to October 2016</h3>
    <?php // print_r($legacy_credits); ?>
    <table id="legacy-credits-table" class="espresso-table footable table footable-loaded" data-filter="#filter">
        <thead class="espresso-table-header-row">
            <tr>
                <th class="th-group footable-sortable legacy-credits-date">Date<span class="footable-sort-indicator"></span></th>
                <th class="th-group footable-sortable">Event<span class="footable-sort-indicator"></span></th>
                <th class="th-group footable-sortable">Credits<span class="footable-sort-indicator"></span></th>
                <th class="th-group" data-sort-ignore="true"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($legacy_credits as $legacy_credit) {
                $credit = $legacy_credit->credits_id;
            ?>
            <tr class="espresso-table-row unit legacy-credits-body" style="display: table-row;">
                <td class="event-<?php echo $credit ?> legacy-credits-date"><?php echo $legacy_credit->start_date ?></td>
                <td class="event-<?php echo $credit ?> legacy-credits-event"><?php echo $legacy_credit->details ?></td>
                <td class="event-<?php echo $credit ?> legacy-credits-credits"><?php echo $legacy_credit->credits ?></td>
                <td class="legacy-credits-cert"><a id="a_leg_cert_link-<?php echo $credit ?>" class="a_cert_link" href="https://amtanewyork.org/view-legacy-certificate/?credits_id=<?php echo $credit ?>" target="_blank">View Certificate</a></td>
            </tr>
            <?php    } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"></td>
            </tr>
        </tfoot>
    </table>

</div>
<?php endif;
            }
?>
