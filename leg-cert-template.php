<?php
$id=$_GET['credits_id'];
function get_legacy_credits($output_type=OBJECT) {
    global $wpdb;
    $id=$_GET['credits_id'];
    $leg_table  = $wpdb->prefix . 'ce_credits';
    return $wpdb->get_results("SELECT * FROM {$leg_table} WHERE `credits_id` = '$id'", OBJECT);
    }
$legacy_credits = get_legacy_credits();
// print_r( $legacy_credits);
global $current_user;
get_currentuserinfo();
foreach( $legacy_credits as $legacy_credit){ ?>
    <link rel="stylesheet" type="text/css" href="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/container/assets/skins/sam/container.css"/>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/dragdrop/dragdrop-min.js"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/yui/2.9.0/build/container/container-min.js"></script>
    <style type="text/css">
        #printbuttons {
            margin-bottom: 10px;
        }
        body { font-family: "Roboto", Helvetica, sans-serif;
        }
    </style>
    <style type="text/css" media="print">
        #printbuttons {
            display: none;
        }
    </style>
    <div>
        <div id="printbuttons">
            <input type="button" onclick="self.print();" value="Print" id="btnPrint" class="formbutton" />
            <input type="button" onclick="self.open('', '_self', ''); self.close();" value="Close" id="btnClose" class="formbutton" />
        </div>

        <div id="ctl00_PageContent_pnlDoc">
<table class="" style="height: 188px; width: 627px;" align="center">
    <tbody>
        <tr>
            <td class="" style="height: 150px; width: 325px; padding: 0px; text-align: center;"><img style="text-align: center;" src="http://127.0.0.1/wordpress/wp-content/uploads/2016/03/20061222_051920_rapter3.jpg" height="226" width="200"><br>
            </td>
            <td class="" style="height: 150px; padding: 0px; text-align: right;"><b>American Massage Therapy Association<br>
            </b>New York Chapter<br>
            167 Chamberlain Rd<br>
            Honeoye Falls, NY 14472<br>
            5850582-6208<br>
            office@amta-ny.org<br>
            www.amtany.org<br>
            </td>
        </tr>
        <tr>
            <td class="" colspan="2" style="padding: 0px; text-align: center;"><span><strong style="font-size: 28px;">Certificate of Completion</strong></span></td>
        </tr>
    </tbody>
</table>
<br>
<blockquote><blockquote><blockquote><blockquote>
<table style="width: 596px; height: 61px;" class="" align="center">
    <tbody>
        <tr>
            <td class="" style="text-align: left; vertical-align: middle;"><?php echo $legacy_credit->full_name ?><br>
            <?php echo get_user_field('address1'); ?><br /><?php echo get_user_field('city'); ?>,&nbsp;<?php echo get_user_field('state'); ?>&nbsp;&nbsp;<?php echo get_user_field('zip'); ?><br />
            <br>
            NYS License #:&nbsp;<?php echo get_user_field('license_number'); ?></td>
        </tr>
    </tbody>
</table>
    <br/>
<table style="border: medium none; left: 107px; top: 313px;" class="" height="130" align="center" border="1" cellpadding="0" cellspacing="0" width="541">
    <tbody>
        <tr style="height: 16.6pt;">
            <td class="" colspan="4" style="height: 16.6pt; width: 315pt; padding: 0in 5.4pt; border-width: 1pt; border-style: solid; border-top-color: black; border-left-color: black; text-align: left; background: rgb(0, 0, 0);"><br>
            </td>
        </tr>
        <tr style="height: 65.65pt;">
            <td class="" style="width: 315pt; padding: 0in 5.4pt; border-style: none solid solid; border-width: medium 1pt 1pt; text-align: center; vertical-align: middle;" colspan="4">&nbsp;<span style="text-align: center;"><table class="invoice" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                <tr>
                    <th class='lined crs-title'>Name</th>
                    <th class='lined location'>Location</th>
                    <th class='lined ccu' style='text-align: right;'>Credits</th>
                </tr>
                <tr>
                    <td class='lined crs-title'><?php echo $legacy_credit->details ?></td>
                    <td class='lined location'>455 S Broadway<br />Tarrytown,&nbsp;Double Tree by Hilton Hotel Tarrytown&nbsp;&nbsp;10591</td>
                    <td class='lined ccu' style='text-align: right;'><?php echo $legacy_credit->credits ?></td>
                </tr></table></span><br>
            </td>
        </tr>
    </tbody>
</table>
</blockquote><blockquote><br>
<div style="text-align: center;"><strong style="text-align: right;">Credits Issued: <?php echo $legacy_credit->credits ?></strong></div>
<table style="top: 582px;" height="136" align="center" width="645">
    <tbody>
        <tr>
            <td colspan="3" style="padding: 0px; text-align: left;"><br>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px; text-align: left;"><img src="https://amtany.site-ym.com/resource/resmgr/Images/logo1.png" style="height: 107px; width: 107px;"></td>
            <td style="padding: 0px; text-align: left;"><img src="https://amtany.site-ym.com/resource/resmgr/Images/nylogo.jpg" height="32" width="156"></td>
            <td class="" style="padding: 0px; text-align: left;"><i><span style="color: black;"><strong>AMTA New York Chapter is an Approved Provider for Continuing Education by the NYSED Office of the Professions State Board for Massage Therapy (#003) and by the National Certification Board for Therapeutic Massage and Bodywork (NCBTMB) (#450787-08).</strong></span></i><br>
            </td>
        </tr>
    </tbody>
</table>
<br>
<table height="97" align="center" width="572">
    <tbody>
        <tr>
            <td class="" style="padding: 0px; text-align: center;"><br>
            <img style="width: 130px; height: 28px;" src="/resource/resmgr/Signatures/Pat_Collins_sign.jpg"><br>
            __________________________________</td>
            <td class="" style="padding: 0px; text-align: center;"><br>
            </td>
            <td class="" style="padding: 0px; text-align: center;"><img style="width: 130px; height: 42px;" src="/resource/resmgr/Signatures/Eugene-Signature1.jpg"><br>
            __________________________________<br>
            </td>
        </tr>
        <tr>
            <td style="padding: 0px; text-align: center;">Pat Collins, President<br>
            </td>
            <td style="padding: 0px; text-align: left;"><br>
            </td>
            <td class="" style="padding: 0px; text-align: center;"><span style="height: 2px; width: 271px; margin-top: 44px; margin-left: -12px;">&nbsp; &nbsp; &nbsp; &nbsp; <br>
            Eugene Wood, Education Chair<br>
            <br>
            </span></td>
        </tr>
    </tbody>
</table>
</blockquote></blockquote></blockquote></blockquote>
</div>
    </div>
<?php } ?>
<!--END_OF_FILE-->
