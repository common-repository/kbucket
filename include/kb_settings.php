<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function kb_get_settings(){
	$settings = get_option('kb_settings');

	if(empty($settings)) $settings = kb_set_settings();

	return $settings;
}

function kb_update_settings($settings){
	update_option('kb_settings', $settings);
}

function kb_set_settings(){
	$settings = array(
		'api_key' => '',
		'api_host' => kbucket_url_origin($_SERVER),
		'api_trial' => 0,
		'kb_page_id' => '',
		'kb_show_style' => '',
		'atd' => 1,
		'ptd' => 1,
		'dtd' => 1,
		'page_title' => '',
		'site_search' => 1,
		'sortBy' => 'add_date',
		'sortOrder' => 'asc',
		'no_listing_page' => 10,
		'kb_share_popap' => '',
		'site_vc' => 0,
		'author_tag_display' => 1,
		'sfont' => array(
			'm1' => 10,
			'm2' => 12,
			'm3' => 14,
			'm4' => 16,
		),
		'yt_apikey' => ''
	);

	add_option('kb_settings', $settings);

	return $settings;
}

function kb_update_key($res_arr){
	$settings = get_option('kb_settings');

	if($settings){
		$settings = array_merge($settings, $res_arr);
		update_option('kb_settings', $settings);
	}
}

function kb_getAPIKey(){
	$settings = get_option('kb_settings');
	return (isset($settings['api_key']) ? $settings['api_key'] : false);
}


function kb_isTrialKey(){
	$settings = get_option('kb_settings');
	if(isset($settings['api_trial'])){
		if($settings['api_trial'] == '1') return true;
		else return false;
	}else return false;
}

function kb_get_fonts() {
	$fonts = [];
	$fonts['marvel'] = array(
		'name' => 'Marvel',
		'import' => '@import url(https://fonts.googleapis.com/css?family=Marvel);',
		'css' => "font-family: 'Marvel', sans-serif;"
	);
	return apply_filters('kb_get_fonts', $fonts);
}