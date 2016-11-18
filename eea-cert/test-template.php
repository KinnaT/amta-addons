<?php
$id=$_GET['credits_id'];
if (!filter_var($id, FILTER_VALIDATE_INT) === false) {
$registration = EEM_Registration::instance()->get_one_by_ID( $id ); } else { echo '<h2 style="text-align: center; font-family: Helvetica, sans-serif; margin-top: 15px;">Request cannot be completed. If you are seeing this message in error, please contact the AMTA-NY chapter.</h2><input type="button" onclick="self.close();" value="Close" style="height: 30px; width: 70px; font-size: 16px; display: block; margin: 0 auto;"/>';};
$ticket = $registration->ticket();
$credits = $ticket instanceof EE_Ticket ? $ticket->get_extra_meta( 'ee_ticket_credits', true, '' ) : '';
$checked_in = $registration->check_in_status_for_datetime();
$event = $registration->event();
$venues = $event->venues();
$event_id = $event->id();
//print_r( $registration );
$user = wp_get_current_user();
if( is_user_logged_in() ) {
?>

<link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/container/assets/skins/sam/container.css"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/dragdrop/dragdrop-min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/container/container-min.js"></script>
<style type="text/css">
    .printbuttons {
        margin-bottom: 10px;
    }
    body { font-family: "Roboto", Helvetica, sans-serif;
    }
</style>
<style type="text/css" media="print">
    .printbuttons {
        display: none;
    }
</style>
<div>
    <div style="width: 15%; margin: 0 auto; padding-bottom: 10px;" class="printbuttons">
        <input type="button" onclick="self.print();" value="Print" id="btnPrint" class="formbutton" class="printbuttons" style="height: 30px; width: 70px; font-size: 16px; margin-right: 15px;"/>
        <input type="button" onclick="self.open('', '_self', ''); self.close();" value="Close" id="btnClose" class="formbutton printbuttons" style="height: 30px; width: 70px; font-size: 16px;    margin-left: 15px;    position: relative;    float: right;"/>
    </div>

    <div id="ctl00_PageContent_pnlDoc">
        <table style="height: 188px; width: 627px;" align="center">
            <tbody>
                <tr>
                    <td height="100" width="300"><img src="https://amtanewyork.org/wp-content/uploads/2016/09/AMTA-New-York-Website-Dark-300.png" height="100" width="300"><br>
                    </td>
                    <td id="cert_company" style="height: 150px; padding: 0px; text-align: right;"><b>American Massage Therapy Association<br>
                        </b>New York Chapter<br>
                        167 Chamberlain Rd<br>
                        Honeoye Falls, NY 14472<br>
                        585-582-6208<br>
                        office@amta-ny.org<br>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 0px;  padding-top: 10px; text-align: center;"><span><strong style="font-size: 28px;">Certificate of Completion</strong></span></td>
                </tr>
            </tbody>
        </table>
        <br>
        <table style="width: 596px; height: 61px; margin: 0 auto;" align="center">
            <tbody>
                <tr>
                    <td style="text-align: left; vertical-align: middle;"><?php echo $user->user_firstname . ' ' . $user->user_lastname ?><br>
                        <?php echo get_user_field('address1'); ?><br /><?php echo get_user_field('city'); ?>,&nbsp;<?php echo get_user_field('state'); ?>&nbsp;&nbsp;<?php echo get_user_field('zip'); ?><br />
                        <br>
                        NYS License #:&nbsp;<?php echo get_user_field('license_number'); ?></td>
                </tr>
            </tbody>
        </table>
        <br/>
        <?php $attendee = $registration->attendee();
                $att_email = $attendee->get('ATT_email');
                if( $att_email == $user->ID || $att_email == $user->user_email ) { ?>
        <table style="border: #000; margin: 0 auto;" height="130" align="center" border="1" cellpadding="0" cellspacing="0" width="600">
            <tbody>
                <tr style="height: 20px">
                    <td colspan="4" style="height: 20px border-color: #000; background: #000"><br>
                    </td>
                </tr>
                <tr style="height: 30px; width: 600px; padding: 0 8px; border: 1px solid #555; text-align: center; vertical-align: middle;" colspan="4">
                    <th style="200px;">Name</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th style="width: 60px;">Credits</th>
                </tr>
                <tr style="height: 80px; width: 600px; padding: 0 8px; border: 1px solid #555; text-align: center; vertical-align: middle;" colspan="4">
                    <td style="width: 200px; line-height: 1.4em;"><?php echo $registration->event_name() ?></td>
                    <td style="padding: 0 3px; white-space: nowrap;"><?php echo $ticket instanceof EE_Ticket ? $ticket->date_range() : ''; ?></td>
                    <td><?php foreach ( $venues as $venue ){
                                if ( $venue instanceof EE_Venue ){
                                 echo '<strong>' . $venue->name() . '</strong><br/>' . EEH_Venue_View::venue_address( 'multiline', $venue->ID() );
                                }
                        } ?></td>
                    <td><?php echo $credits ?></td>
                </tr><br>
            </tbody>
        </table>
        <br>
        <div style="text-align: center;"><strong>Credits Issued: <?php echo $credits ?></strong></div>
        <?php } else { ?>
        <table style="margin: 0 auto;" align="center" width="645">
            <div style="text-align: center; width: 572px; margin: 0 auto; border-top: 1px solid #333; border-bottom: 1px solid #333;"><strong>Information does not match. Please ensure your email address or username matches what was used to register for this event.</strong></div>
            <?php } ?>
        <table style="top: 582px; margin: 0 auto;" height="136" align="center" width="645">
            <tbody>
                <tr>
                    <td colspan="3"><br>
                    </td>
                </tr>
                <tr>
                    <td><img src="https://amtanewyork.org/wp-content/uploads/2016/10/providerlogo.png" style="height: 107px; width: 107px;"></td>
                    <td><img src="https://amtanewyork.org/wp-content/uploads/2016/10/nylogo.jpg" height="32" width="156"></td>
                    <td style="padding: 0px; text-align: left; font-size: 14px;"><i><strong>AMTA New York Chapter is an Approved Provider for Continuing Education by the NYSED Office of the Professions State Board for Massage Therapy (#003) and by the National Certification Board for Therapeutic Massage and Bodywork (NCBTMB) (#450787-08).</strong></i><br>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <table height="97" align="center" width="572" style="margin: 0 auto;">
            <tbody>
                <tr>
                    <td style="text-align: center; margin-right: 10px; height: 71px;">
                        <img style="width: 130px; height: 27px; margin: 5px 0;" src="https://amtanewyork.org/wp-content/uploads/2016/10/Pat_Collins_sign2.jpg">
                        <br/>___________________________</td>
                    <td style="text-align: center; margin-left: 10px; height: 71px;">
                        <img style="width: 130px; height: 27px;  margin: 5px 0;" src="https://amtanewyork.org/wp-content/uploads/2016/10/Eugene-Signature2.jpg">
                        <br/>___________________________</td>

                </tr>
                <tr>
                    <td style="text-align: center;">Pat Collins, Education Chair<br></td>
                    <td style="text-align: center;">Eugene Wood, President<br></td>
                </tr>
            </tbody>
        </table>
    </div>
<div class="test">
<h3>This is only a test.</h3>
</div>
<?php } else { ?>
<h2 style="text-align: center; margin-top: 15px; font-family: Helvetica, sans-serif;">Please log in again in order to view your certificate.</h2>
       <input type="button" onclick="self.open('', '_self', ''); self.close();" id="btnClose" class="formbutton printbuttons" value="Click to close" style="height: 30px; width: 130px; font-size: 16px; display: block; margin: 0 auto;"/>
<?php } ?>
