<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if(is_admin()){
	global $wpdb;


	$col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kbucket LIKE 'publisher_alias'");
	if(!$col){
		$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kbucket ADD COLUMN publisher_alias varchar(255) DEFAULT NULL AFTER author_alias");
	}


	$col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kb_category LIKE 'alias_name'");
	if(!$col){
		$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_category ADD COLUMN alias_name varchar(255) DEFAULT NULL AFTER name");
	}

	$col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kb_tag_details LIKE 'alias_name'");
	if(!$col){
		$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_tag_details ADD COLUMN alias_name varchar(255) DEFAULT NULL AFTER name");
	}

	$col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kbucket LIKE 'KID'");
	if(!$col){
		$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kbucket ADD COLUMN KID varchar(255) DEFAULT NULL AFTER id_kbucket");
	}

		$col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kbucket LIKE 'last_updated'");
	if(!$col){
		$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kbucket ADD COLUMN last_updated varchar(255) DEFAULT NULL AFTER add_date");
	}


}
