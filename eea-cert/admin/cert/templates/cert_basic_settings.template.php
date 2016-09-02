<?php
/* @var $config EE_Certificates_Config */
?>
<div class="padding">
    <h4>
        <?php _e('Certificates Settings', 'event_espresso'); ?>
    </h4>
    <table class="form-table">
        <tbody>
            <tr>
                <th><?php _e("Enable Certificates?", 'event_espresso');?></th>
                <td>
                    <?php echo EEH_Form_Fields::select( __('Enable Certificates?', 'event_espresso'), 0, $yes_no_values, 'enable_cert', 'enable_cert' ); ?><br/>
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

