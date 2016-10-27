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
            <td id="company" style="height: 150px; padding: 0px; text-align: right;"><b>American Massage Therapy Association<br>
            </b>New York Chapter<br>
            167 Chamberlain Rd<br>
            Honeoye Falls, NY 14472<br>
            5850582-6208<br>
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
            <td style="text-align: left; vertical-align: middle;"><?php echo $legacy_credit->first_name . ' ' . $legacy_credit->last_name ?><br>
            <?php echo get_user_field('address1'); ?><br /><?php echo get_user_field('city'); ?>,&nbsp;<?php echo get_user_field('state'); ?>&nbsp;&nbsp;<?php echo get_user_field('zip'); ?><br />
            <br>
            NYS License #:&nbsp;<?php echo get_user_field('license_number'); ?></td>
        </tr>
    </tbody>
</table>
    <br/>
<table style="border: #000; margin: 0 auto;" height="130" align="center" border="1" cellpadding="0" cellspacing="0" width="541">
    <tbody>
        <tr style="height: 20px">
            <td colspan="4" style="height: 20px border-color: #000; background: #000"><br>
            </td>
        </tr>
        <tr style="height: 30px; width: 500px; padding: 0 8px; border: 1px solid #555; text-align: center; vertical-align: middle;" colspan="4">
            <th>Name</th>
            <th>Location</th>
            <th style="width: 60px;">Credits</th>
        </tr>
        <tr style="height: 80px; width: 500px; padding: 0 8px; border: 1px solid #555; text-align: center; vertical-align: middle;" colspan="4">
            <td><?php echo $legacy_credit->details ?></td>
            <td><?php echo $legacy_credit->location ?><br /><?php echo $legacy_credit->address1 ?><br /><?php echo $legacy_credit->city ?>, <?php echo $legacy_credit->zip ?></td>
            <td><?php echo $legacy_credit->credits ?></td>
        </tr><br>
    </tbody>
</table>
<br>
<div style="text-align: center;"><strong>Credits Issued: <?php echo $legacy_credit->credits ?></strong></div>
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
                <img style="width: 130px; height: 27px;  margin: 5px 0;" src="https://amtanewyork.org/wp-content/uploads/2016/10/Eugene-Signature2.jpg">
                <br/>___________________________</td>
            <td style="text-align: center; margin-left: 10px; height: 71px;">
                <img style="width: 130px; height: 27px; margin: 5px 0;" src="https://amtanewyork.org/wp-content/uploads/2016/10/Pat_Collins_sign2.jpg"><br/>___________________________</td>

        </tr>
        <tr>
            <td style="text-align: center;">Pat Collins, Education Chair<br></td>
            <td style="text-align: center;">Eugene Wood, President<br></td>
        </tr>
    </tbody>
</table>
</div>
    </div>
<?php } ?>
<!--END_OF_FILE-->
