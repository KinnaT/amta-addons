<div class="outside">
  <div class="instructions"><?php _e('Print and bring this ticket with you to the event', 'event_espresso'); ?></div>
  <div class="cert">
    <table width="100%" border="0">
      <tr>
        <td width="55%" rowspan="2" valign="top"><span class="top_event_title">[EVENT_NAME]</span><br>
            <ul>
            [DATETIME_LIST]
            </ul>
            [TICKET_NAME]<br>
            [VENUE_TITLE]<br>

          <div class="logo">[EVENT_IMAGE]</div></td>
        <td width="22%" align="right" valign="top"><div class="gravatar">[GRAVATAR_*]</div></td>
        <td width="23%" align="right" valign="top"><div class="qr_code">[QRCODE_*]</div></td>
      </tr>
      <tr>
        <td colspan="2" align="right" valign="top"><span class="price">[TICKET_PRICE]</span><br>
          [RECIPIENT_FNAME] [RECIPIENT_LNAME] (ID: [RECIPIENT_REGISTRATION_CODE])<br>
      </tr>
    </table>
   [BARCODE_* w=1 h=70 fsize=18 bgcolor=#ffffff color=#000000 output_type=bmp]
      <div style="text-align: center">[RECIPIENT_REGISTRATION_CODE]</div>
  </div>
  <div class="extra_info">
    <div class="divider"></div>
    <table width="100%" border="0">
      <tr>
        <td width="45%" height="35" align="left" valign="top"><p><strong><?php _e('Location:', 'event_espresso'); ?></strong><br>
            [VENUE_TITLE]<br>
            [VENUE_ADDRESS]<br>
            [VENUE_ADDRESS2]<br>
            [VENUE_CITY], [VENUE_STATE]<br>
            [VENUE_PHONE]</p>
          <p><strong><?php _e('More Information:', 'event_espresso'); ?></strong></p>
          <p>[VENUE_DESCRIPTION]</p>
          <p><strong><?php _e('Ticket Instructions:', 'event_espresso'); ?></strong></p>
          <p>[TICKET_DESCRIPTION]</p></td>
        <td width="55%" valign="top">[GOOGLE_MAP_IMAGE]</td>
      </tr>
    </table>
  </div>
  <div class="footer">Ticketing powered by <a href="http://eventespresso.com" target="_blank">Event Espresso</a></div>
</div>
