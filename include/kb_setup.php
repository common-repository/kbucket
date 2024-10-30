<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Run install scripts by plugin activation
 * Create Kbucket tables and insert default settings
 * Fired by register_activation_hook
 */
function register_activation_kbucket() {
	/** @var $wpdb object */
	global $wpdb;
	$charsetCollate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kbucket (
				id_kbucket varchar(100) NOT NULL,
				id_cat varchar(100) DEFAULT NULL,
				title text,
				description text NOT NULL,
				link text,
				author varchar(50) DEFAULT NULL,
				publisher varchar(255) DEFAULT NULL,
				author_alias varchar(255) DEFAULT NULL,
				publisher_alias varchar(255) DEFAULT NULL,
				facebook varchar(200) NOT NULL,
				twitter varchar(200) NOT NULL,
				pub_date varchar(100) DEFAULT NULL,
				add_date date DEFAULT NULL,
				status enum( '0','1' ) DEFAULT '1',
				image_url varchar(255) DEFAULT '',
				short_url varchar(255) DEFAULT '',
				post_id int(11) DEFAULT NULL,
				url_kbucket varchar(255),
			PRIMARY KEY  (id_kbucket),
			UNIQUE KEY id_kbucket_id_cat_index (id_kbucket,id_cat)
			) ENGINE=MyISAM $charsetCollate";
	$wpdb->query( $sql );



	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kb_files (
				id_file int(9) NOT NULL AUTO_INCREMENT,
				name varchar(100) DEFAULT NULL,
				comment text DEFAULT NULL,
				encoding text NOT NULL,
				publisher varchar(255) DEFAULT NULL,
				author varchar(50) DEFAULT NULL,
				keywords text DEFAULT NULL,
				file_added varchar(100) DEFAULT NULL,
				file_path varchar(255) DEFAULT NULL,
			PRIMARY KEY (id_file)
			) ENGINE=MyISAM $charsetCollate";
	$wpdb->query( $sql );



	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kb_category (
				id_cat varchar(100) NOT NULL,
				name varchar(50) DEFAULT NULL,
    			alias_name varchar(255) DEFAULT NULL,
				description text,
				image text,
				parent_cat varchar(100) DEFAULT '0',
				level bigint(3) DEFAULT NULL,
				add_date date DEFAULT NULL,
				keywords varchar(255) DEFAULT NULL,
				status enum( '0','1' ) DEFAULT '1',
			PRIMARY KEY  (id_cat),
			UNIQUE KEY id_cat (id_cat,name,parent_cat)
			) ENGINE=MyISAM $charsetCollate";
	$wpdb->query( $sql );

	// $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}author_alias (
	// 			author varchar(100) NOT NULL,
	// 			alias varchar(100) DEFAULT NULL
	// 		) ENGINE=MyISAM $charsetCollate";
	// $wpdb->query( $sql );

	// $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kb_page_settings (
	// 			id_page int(3) NOT NULL AUTO_INCREMENT,
	// 			api_key varchar(255) DEFAULT NULL,
	// 			api_trial varchar(2) DEFAULT NULL,
	// 			kb_share_popap text NOT NULL,
	// 			api_user_id varchar(255) DEFAULT NULL,
	// 			sort_by varchar(10) DEFAULT NULL,
	// 			sort_order varchar(10) DEFAULT NULL,
	// 			no_listing_perpage int(4) DEFAULT NULL,
	// 			main_no_tags int(4) DEFAULT NULL,
	// 			author_no_tags int(4) DEFAULT NULL,
	// 			related_no_tags int(4) DEFAULT NULL,
	// 			main_tag_color1 varchar(10) DEFAULT NULL,
	// 			main_tag_color2 varchar(10) DEFAULT NULL,
	// 			author_tag_color1 varchar(10) DEFAULT NULL,
	// 			author_tag_color2 varchar(10) DEFAULT NULL,
	// 			related_tag_color1 varchar(10) DEFAULT NULL,
	// 			related_tag_color2 varchar(10) DEFAULT NULL,
	// 			author_tag_display enum( '0','1' ) DEFAULT '1',
	// 			header_color varchar(10) DEFAULT NULL,
	// 			author_color varchar(10) DEFAULT NULL,
	// 			content_color varchar(10) DEFAULT NULL,
	// 			tag_color varchar(10) DEFAULT NULL,
	// 			status enum( '0','1' ) DEFAULT '1',
	// 			page_title varchar(50) DEFAULT NULL,
	// 			site_search int(1) DEFAULT NULL,
	// 			m1 int(4) DEFAULT NULL,
	// 			m2 int(4) DEFAULT NULL,
	// 			m3 int(4) DEFAULT NULL,
	// 			m4 int(4) DEFAULT NULL,
	// 			a1 int(4) DEFAULT NULL,
	// 			a2 int(4) DEFAULT NULL,
	// 			a3 int(4) DEFAULT NULL,
	// 			a4 int(4) DEFAULT NULL,
	// 			r1 int(4) DEFAULT NULL,
	// 			r2 int(4) DEFAULT NULL,
	// 			r3 int(4) DEFAULT NULL,
	// 			r4 int(4) DEFAULT NULL,
	// 			bitly_username varchar(255) NOT NULL,
	// 			bitly_key varchar(255) NOT NULL,
	// 			vc enum( '0','1' ) DEFAULT '0',
	// 		PRIMARY KEY  (id_page)
	// 		) ENGINE=MyISAM $charsetCollate";
	// $wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kb_suggest (
				id_sug int(2) NOT NULL AUTO_INCREMENT,
				id_cat varchar(100) DEFAULT NULL,
				tittle varchar(50) DEFAULT NULL,
				description text NOT NULL,
				tags varchar(200) NOT NULL,
				add_date date DEFAULT NULL,
				author varchar(50) DEFAULT NULL,
				link text,
				twitter text,
				facebook text,
				status enum( '0','1' ) DEFAULT '1',
			PRIMARY KEY  (id_sug)
			) ENGINE=MyISAM $charsetCollate";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kb_tags (
				id_ktags int(3) NOT NULL AUTO_INCREMENT,
				id_kbucket varchar(100) DEFAULT NULL,
				id_tag int(3) DEFAULT NULL,
				status enum( '0','1' ) DEFAULT '1',
			PRIMARY KEY  (id_ktags),
			UNIQUE KEY id_kbucket_id_tag_index (id_kbucket,id_tag)
			) ENGINE=MyISAM  $charsetCollate;";
	$wpdb->query( $sql );

	$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}kb_tag_details (
				id_tag int(3) NOT NULL AUTO_INCREMENT,
				name varchar(50) DEFAULT NULL,
				status enum( '0','1' ) DEFAULT '1',
			PRIMARY KEY  (id_tag),
			UNIQUE KEY name_index (name)
			) ENGINE=MyISAM  $charsetCollate";
	$wpdb->query( $sql );

	// $col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kb_page_settings LIKE 'api_key'");
	// if(!$col){
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings ADD COLUMN api_key varchar(255) DEFAULT NULL AFTER id_page");
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings ADD COLUMN api_trial varchar(2) DEFAULT NULL AFTER api_key");
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings ADD COLUMN kb_share_popap text NULL AFTER api_trial");
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings ADD COLUMN api_user_id varchar(255) NULL AFTER kb_share_popap");
	// }else{
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings CHANGE COLUMN api_key api_key varchar(255) DEFAULT NULL AFTER id_page");
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings CHANGE COLUMN api_trial api_trial varchar(2) DEFAULT NULL AFTER api_key");
	// 	$res = $wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings CHANGE COLUMN kb_share_popap kb_share_popap text NULL AFTER api_trial");
	// }

	// $wpdb->query('INSERT IGNORE	INTO '.$wpdb->prefix."kb_page_settings VALUES (1,'',1,'',0,'add_date','desc','9',50,50,50,'#F67B1F','#000','#F67B1F','#000','#F67B1F','#000','1','#1982D1','#F67B1F','#666666','#109CCB','1','',1,10,12,14,16,10,12,14,16,10,12,14,16,'o_3fanobfis0','R_d4112d6f69ad4bd8706925e11c892893','0')");


	// $col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kb_category LIKE 'keywords'");
	// if(!$col){
	// 	$wpdb->query("ALTER TABLE {$wpdb->prefix}kb_category ADD COLUMN keywords varchar(255) DEFAULT NULL AFTER add_date");
	// }

	// $col = $wpdb->query("SHOW COLUMNS FROM {$wpdb->prefix}kb_page_settings LIKE 'vc'");
	// if(!$col){
	// 	$wpdb->query("ALTER TABLE {$wpdb->prefix}kb_page_settings ADD COLUMN vc enum( '0','1' ) DEFAULT '0' AFTER bitly_key");
	// }

	$post = get_post(get_option('kb_setup_page_id','kbucket'));
	$kpage = (isset($post->post_name)) ? $post->post_name : 'kbucket';

	$kbucketPageExists = get_posts(
		array(
			'name' => $kpage,
			'post_type' => 'page',
		)
	);

	if(!$kbucketPageExists) {
		$kbucketPage = array(
			'post_name' => 'kbucket',
			'post_title' => 'Kbucket',
			'post_status' => 'publish',
			'post_type' => 'page',
		);

		$post_id = wp_insert_post( $kbucketPage );
		update_option('kb_setup_page_id',$post_id);
	}
	add_option( 'kbucket_data', 'Default', '', 'yes' );

}


// Set Kbucket install hook.
register_activation_hook( WPKB_FILE, 'register_activation_kbucket' );


/**
 * Remove plugin data if plugin has been deleted
 * Fired by register_deactivation_hook
 */
// function register_deactivation_kbucket() {
// 	if ( ! current_user_can( 'activate_plugins' ) ) return;
// 	global $wpdb;

// 	$dropTables = array(
// 		$wpdb->prefix . 'kbucket',
// 		$wpdb->prefix . 'kb_category',
// 		$wpdb->prefix . 'kb_suggest',
// 		$wpdb->prefix . 'kb_tags',
// 		$wpdb->prefix . 'kb_tag_details',
// 		$wpdb->prefix . 'kb_page_settings',
// 	);

// 	$dropTablesStr = '' . implode( ',', $dropTables );
// 	$wpdb->query( "DROP TABLE $dropTablesStr" );
// }
