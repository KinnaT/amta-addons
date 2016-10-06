<?php
function leg_credits() {
    if ( is_page( 5212 ) && is_user_logged_in() ) {
        function get_legacy_credits($output_type=OBJECT) {
            global $wpdb;
            global $current_user;
            get_currentuserinfo();
            $user = $current_user->user_login;
            $leg_table  = $wpdb->prefix . 'ce_credits';
            return $wpdb->get_results("SELECT * FROM {$leg_table} WHERE `username` = '$user'", OBJECT);
        }
        $legacy_credits = get_legacy_credits();
        if (!empty($legacy_credits)){
            ?>
            <div class="legacy-credits">
                <h3>Legacy CE Credits Earned</h3>
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
                            <td class="event-<?php echo $credit ?> legacy-credits-date"><?php echo $legacy_credit->entry_date ?></td>
                            <td class="event-<?php echo $credit ?> legacy-credits-event"><?php echo $legacy_credit->details ?></td>
                            <td class="event-<?php echo $credit ?> legacy-credits-credits"><?php echo $legacy_credit->credits ?></td>
                            <td class="legacy-credits-cert"><a id="a_leg_cert_link-<?php echo $credit ?>" class="a_cert_link" href="http://127.0.0.1/wordpress/?page_id=5958&credits_id=<?php echo $credit ?>" target="_blank">View Certificate</a></td>
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
        <?php }
    }
}
/**
 * The page selector needs to be changed.
 *
 * This pulls from the wp_ce_credits table and needs the following columns:
 * credits_id (unique key)
 * entry_date
 * details (description)
 * credits (number)
 *
 * This takes styling from the Event Espresso footable plugin (and partially from the AMTA chapter tweaks plugin).
 */
