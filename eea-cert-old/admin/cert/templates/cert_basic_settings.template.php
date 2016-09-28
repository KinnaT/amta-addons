<?php
/* @var $config EE_Certificates_Config */
?>
<div class="padding">
    <table class="form-table">
        <tbody>
            <tr>
                <th><?php _e("Enable Certificates?", 'event_espresso');?></th>
                <td>
                    <?php echo EEH_Form_Fields::select( __('Enable Certificates?', 'event_espresso'), 0, $yes_no_values, 'enable_cert', 'enable_cert' ); ?><br/>
                </td>
            </tr>
            <tr>
                <th><?php _e('Heading Options', 'event_espresso'); ?></th>
                <br/>
            </tr>
            <tr>
                <th><?php _e("Certificate Label", 'event_espresso');?></th>
                <td>
                    <input class="regular-text" type="text" id="cert_heading" name="cert_heading" value=""/>
                    <span class="description">
                        <?php _e('Main heading for the certificate', 'event_espresso'); ?>
                    </span>
                    <br/>
                </td>
            </tr>
            <tr>
                <th><?php _e("Contact Name", 'event_espresso');?></th>
                <td>
                    <input class="regular-text" type="text" id="cert_contact_name" name="cert_contact_name" value=""/>
                    <span class="description">
                        <?php _e('Individual or person', 'event_espresso'); ?>
                    </span>
                    <br/>
                </td>
            </tr>
            <tr>
                <th><?php _e("Address 1", 'event_espresso');?></th>
                <td>
                    <input class="regular-text" type="text" id="cert_contact_addr1" name="cert_contact_addr1" value=""/>
                </td>
            </tr>
            <tr>
                <th><?php _e("Address 2", 'event_espresso');?></th>
                <td>
                    <input class="regular-text" type="text" id="cert_contact_addr2" name="cert_contact_addr2" value=""/>
                </td>
            </tr>
            <tr>
                <th><?php _e("City", 'event_espresso');?></th>
                <td>
                    <input class="regular-text" type="text" id="cert_contact_city" name="cert_contact_city" value=""/>
                </td>
            </tr>
            <tr>
                <th><?php _e("State", 'event_espresso');?></th>
                <td>
                    <input class="regular-text" type="text" id="cert_contact_state" name="cert_contact_state" value=""/>
                    <br/>
                </td>
            </tr>
            <tr>
                <th><?php _e("Reset Certificates Settings?", 'event_espresso');?></th>
                <td>
                    <?php echo EEH_Form_Fields::select( __('Reset Certificates Settings?', 'event_espresso'), 0, $yes_no_values, 'reset_cert', 'reset_cert' ); ?><br/>
                    <span class="description">
                        <?php _e('Set to \'Yes\' and then click \'Save\' to confirm reset all basic and advanced Event Espresso Certificates settings to their plugin defaults.', 'event_espresso'); ?>
                    </span>
                </td>
            </tr>

        </tbody>
    </table>

</div>

<input type='hidden' name="return_action" value="<?php echo $return_action?>">

