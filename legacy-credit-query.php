function get_legacy_credits() {
if ( is_user_logged_in() ) {
	global $wpdb;
	$user = wp_get_current_user();
	$username = $user->user_login;
	$results = $wpdb->get_results("SELECT * FROM wp_ce_credits WHERE username = $username ORDER BY entry_date", OBJECT);
}
$legacy_credits = get_legacy_credits();

