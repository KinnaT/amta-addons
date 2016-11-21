## EE4 WP Users Integration Addon

Changes:
* EE_WPUsers.class.php - Line 104  
  Amended query, as the table should be get_blog_prefix() . 'usermeta'; and the meta should simply be 'EE_Attendee_ID' without a prefix
* EE_WP_Users_Admin.module.php - Line 328  
  update_user_option changed to update_user_meta
* EED_WP_Users_SPCO.module.php - Lines 635, 668, 691, 697, 699 and 785  
  All instances of get_user_option changed to get_user_meta  
  All instances of update_user_option changed to update_user_meta
