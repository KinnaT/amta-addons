<?php ?>
<div class="padding">

    <h3>
        <?php _e('Basic Settings', 'event_espresso'); ?>
    </h3>

    <table class="form-table">
        <tbody>
            <tr>
                <th>
                    <label for="enable_certs">
                        <?php _e('Enable Certificates?', 'event_espresso'); ?>
                    </label>
                </th>
                <td>
                    <?php echo EEH_Form_Fields::select_input('cert[basic][enable_certs]', $values, $cert_config->basic->enable_certs, 'id="enable_certs"');?>
                </td>
            </tr>
            <tr>
                <th>
                    <label for="default_credits">
                        <?php _e('Default credits for new events', 'event_espresso'); ?>
                    </label>
                </th>
                <td>
                    <input id="espresso_cert_default_credits" type="text" name="cert[basic][default_credits]" value="<?php echo $cert_config->basic->default_credits; ?>" />
                    <br/>
                    <span class="description">
                        <?php _e('This will default the credits amount on any new ticket on any event to this value. Can be overridden individually while editing an event.', 'event_espresso'); ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    <h3>Certificate Display</h3>
       <table class="form-table">
        <tbody>
            <tr>
                <th>
                    <label for="cert_company">
                        <?php _e('Contact Info', 'event_espresso'); ?>
                    </label>
                   <br/>
                    <span class="description">
                        <?php _e('Enter company name and address. Basic HTML formatting is allowed.', 'event_espresso'); ?>
                    </span>
                </th>
                <td>
                    <input id="espresso_cert_company" type="textarea" name="cert[display][cert_company]" value="<?php echo $cert_config->display->cert_company; ?>" />
                </td>
            </tr>
                        <tr>
                <th>
                    <label for="company_phone">
                        <?php _e('Contact Phone', 'event_espresso'); ?>
                    </label>
                </th>
                <td>
                    <input id="espresso_cert_phone" type="textarea" name="cert[display][company_phone]" value="<?php echo $cert_config->display->company_phone; ?>" />
                </td>
            </tr>
           </tbody>
    </table>
</div>
<input type='hidden' name="return_action" value="<?php echo $return_action?>">
<!-- / .padding -->
