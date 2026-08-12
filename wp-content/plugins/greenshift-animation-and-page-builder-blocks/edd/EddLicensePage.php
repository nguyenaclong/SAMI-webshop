<?php

if (!defined('ABSPATH')) {
	exit;
}

function greenshift_edd_check_all_licenses()
{
	$licenses = get_option('gspb_edd_licenses');
	$res = [];
	foreach ($licenses as $plugin_key => $data) {
		$expires = $data['expires'];
		$res[$plugin_key] = $data['status'] === 'valid' && !empty($expires) && ($expires === 'lifetime' || (date('Y-m-d') <= date('Y-m-d', strtotime($expires)))) ? 'valid' : 'invalid';
	}
	return $res;
}

// if all access key is valid return it, else return addon key
function greenshift_edd_get_license_for_addon($addon)
{
	$licenses = get_option('gspb_edd_licenses');

	foreach ($licenses[$addon]['included_in'] as $key_pack) {
		if ($licenses[$key_pack]['status'] === 'valid') return $licenses[$key_pack]['license'];
	}

	return $licenses[$addon]['license'];
}

//////////////////////////////////////////////////////////////////
// Schedule events
//////////////////////////////////////////////////////////////////
add_action('wp', 'greenshift_add_cron_event');
add_action('greenshift_check_cron_hook', 'greenshift_check_cron_exec');
function greenshift_check_cron_exec()
{ 
	if(class_exists('EddLicensePage')){
		EddLicensePage::edd_check_and_update_licenses_static();
	}
}
function greenshift_add_cron_event()
{
	if (!wp_next_scheduled('greenshift_check_cron_hook')) {
		wp_schedule_event(time(), 'daily', 'greenshift_check_cron_hook');
	}
}
