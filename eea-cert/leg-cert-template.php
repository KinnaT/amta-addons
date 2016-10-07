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
            <td style="height: 150px; width: 325px; padding: 0px; text-align: center;"><img style="text-align: center;" src="http://127.0.0.1/wordpress/wp-content/uploads/2016/03/20061222_051920_rapter3.jpg" height="226" width="200"><br>
            </td>
            <td style="height: 150px; padding: 0px; text-align: right;"><b>American Massage Therapy Association<br>
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
<table style="width: 596px; height: 61px; margin: 0 auto;" class="" align="center">
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
<table style="border: #000; margin: 0 auto;" height="130" align="center" border="1" cellpadding="0" cellspacing="0" width="541">
    <tbody>
        <tr style="height: 20px">
            <td class="" colspan="4" style="height: 20px border-color: #000; background: #000"><br>
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
            <td><img src="https://amtany.site-ym.com/resource/resmgr/Images/logo1.png" style="height: 107px; width: 107px;"></td>
            <td><img src="https://amtany.site-ym.com/resource/resmgr/Images/nylogo.jpg" height="32" width="156"></td>
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
                <img style="width: 130px; height: 28px; margin: 12px 0;" src="/resource/resmgr/Signatures/Pat_Collins_sign.jpg"><br/>___________________________</td>
            <td style="text-align: center; margin-left: 10px; height: 71px;">
                <img style="width: 130px; height: 42px;  margin: 5px 0;" src="/resource/resmgr/Signatures/Eugene-Signature1.jpg">
                <br/>___________________________</td>
        </tr>
        <tr>
            <td style="text-align: center;">Pat Collins, President<br></td>
            <td style="text-align: center;">Eugene Wood, Education Chair<br></td>
        </tr>
    </tbody>
</table>
</div>
    </div>
<?php } ?>
<!--END_OF_FILE-->
