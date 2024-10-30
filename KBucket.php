<?php
/*
	Plugin Name: KBucket
	Plugin URI: https://optimalaccess.com/download/
	Description: KBucket Curated Pages
	Version: 4.1.6
	Author: Optimal Access Inc.
	Author URI: https://optimalaccess.com/
	License: KBucket
*/

if (!defined('ABSPATH')) exit;

ini_set('memory_limit', '768M');
//ini_set('allow_url_fopen', '1');
//ini_set('max_execution_time', 1800); // 30 min

ini_set('post_max_size', '50M');
ini_set('upload_max_filesize', '50M');

if (!function_exists('get_plugin_data')) {
	require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}
define('WPKB_VERSION', get_plugin_data(__FILE__)['Version']);

// Define Kbucket text domain
define('WPKB_TEXTDOMAIN', 'kbucket');
// Define site URL
define('WPKB_SITE_URL', home_url());
define('WPKB_FILE',  str_replace('kb-init.php', 'KBucket.php', __FILE__));
// Define plugin path
define('WPKB_PATH',  plugin_dir_path(WPKB_FILE));
// Define plugin URL
define('WPKB_PLUGIN_URL', plugins_url() . '/kbucket');
define('WPKB_PLUGIN_LOGS_DB', true);

// Include modules
foreach (glob(dirname(__FILE__) . "/include/*.php") as $filename) {
	if (file_exists($filename)) require_once($filename);
	
}

// Enable widget tags
include 'functions.php';
include 'inc/widget_tags.php';

// Define Kbucket listing page URL
$kbucketPageID = kbucketPageID();
$post_name = get_post_field('post_name', $kbucketPageID);
$slug = 'kbucket';
if ($post_name) $slug = $post_name;

//update_post_meta( $kbucketPageID, '_wp_page_template', 'template-kbucket.php' );
define('KBUCKET_URL', WPKB_SITE_URL . '/' . $slug);
define('KBUCKET_SLUG', $slug);

if (is_vc_activate()) {
	// Include vc elements
	require_once(WPKB_PATH . '/vc-elements/vc-kbuckets.php');
	require_once(WPKB_PATH . '/vc-elements/vc-widget-tags.php');
}

add_filter('wpseo_canonical', '__return_false');
add_filter('wpseo_metadesc', '__return_false');

add_action('template_redirect', function () {
	remove_action('init', array('B2S_Loader', 'load'));
	remove_action('wp_head', array('B2S_Loader', 'b2s_build_frontend_meta'), 1);
}, 1);

// Add settings link on plugin page
add_filter("plugin_action_links_" . plugin_basename(WPKB_FILE), function ($links) {
	$settings_link = '<a href="admin.php?page=KBucket">' . __("Settings", WPKB_TEXTDOMAIN) . '</a>';
	array_unshift($links, $settings_link);
	return $links;
});

// Remove invalid meta tags from wp_head
add_action('template_redirect', 'kb_remove_wpseo', 100);
function kb_remove_wpseo()
{
	if (is_kbucket_page_func() && class_exists('WPSEO_Frontend')) {

		global $wpseo_front;
		if (defined($wpseo_front)) {
			remove_action('wp_head', array($wpseo_front, 'head'), 1);
		} else {
			remove_action('wp_head', array(WPSEO_Frontend::get_instance(), 'head'), 1);
		}
	}
}

add_action('template_redirect', 'kb_disable_yoast_seo_frontend20');
function kb_disable_yoast_seo_frontend20()
{
	if (is_admin() || !defined('WPSEO_VERSION') || !is_kbucket_page_func() || !function_exists('YoastSEO')) return;
	$front_end = YoastSEO()->classes->get(Yoast\WP\SEO\Integrations\Front_End_Integration::class);
	remove_action('wpseo_head', [$front_end, 'present_head'], -9999);
	$loader = \YoastSEO()->classes->get(\Yoast\WP\SEO\Loader::class);
	\remove_action('init', [$loader, 'load_integrations']);
	\remove_action('rest_api_init', [$loader, 'load_routes']);
}



function kb_check_php_version()
{
	$version = explode('.', phpversion());
	if ((!empty($version[0]) && $version[0] < 7) || ($version[0] == 7 && !empty($version[1]) && $version[1] < 4)) :
?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<strong>
					<?php _e('Kabuket plugin has not been tested on Your php version ' . phpversion() . ' it is recommended to update php version to >= 7.3!', WPKB_TEXTDOMAIN); ?>
				</strong>
			</p>
		</div>
<?php
	endif;
}
add_action('admin_notices', 'kb_check_php_version');

/**
 * Set separate action hooks for admin and site
 * Fired by wp action hook: init
 */
// Start KBucket from init hook
add_action('init', 'action_set_kbucket');
function action_set_kbucket()
{
	/** @var $wp_rewrite object */
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
	add_action('wp_ajax_ajax_delsug', 'kb_ajax_delsugest');
	add_action('wp_ajax_nopriv_validate_suggest', 'kb_action_ajax_validate_suggest');
	add_action('wp_ajax_validate_suggest', 'kb_action_ajax_validate_suggest');
	add_image_size('kb_thumb_4-3', 480, 360, false);

	// Set admin hooks for dashboard
	if (is_admin()) {
		add_action('admin_enqueue_scripts', 'kb_action_admin_enqueue_scripts');
		// Support column for upload page
		add_filter('manage_media_columns', 'kb_media_columns_orig_path');
		add_action('manage_media_custom_column', 'kb_media_custom_column_orig_path', 10, 2);
		add_action('manage_media_custom_column', 'kb_media_custom_column_orig_size', 10, 2);
		add_action('admin_print_styles-upload.php', 'kb_orig_path_column_orig_path');

		return true;
	}
	// Init Kbucket when wp object is initialized
	add_action('wp', 'action_init_kbucket');
	return true;
}

/**
 * Kbucket initialization
 * Set action hooks,filters and templates
 * Fired by wp action hook: wp
 * @return bool
 */
function action_init_kbucket()
{

	if (!is_vc_activate() || kb_is_mobile()) {
		if (is_kbucket_page_func()) add_filter('the_content', 'kb_filter_the_content');
	}
	// Evil behaviour. Remove canonical Meta tag from <head> to avoid problems with share scripts and other plugins
	remove_action('wp_head', 'rel_canonical');

	if (is_kbucket_page_func()) {
		add_filter('jetpack_enable_opengraph', '__return_false', 99);
	}

	// Initialize scripts
	add_action('wp_enqueue_scripts', 'kb_action_init_scripts', 200);

	// On remove image
	add_action('delete_attachment', 'kb_on_remove_attach');

	// Set custom template
	if (is_kbucket_page_func() && kb_is_mobile()) {
		add_action('page_template', 'kb_set_page_template_mobile');
	}

	if (is_kbucket_page_func()) {
		// Set page title filter
		add_filter('wp_title', 'kb_filter_the_title', 100);
		add_filter('document_title_parts', 'kb_filter_the_title_alt', 100);
	}

	// Customize styles
	add_action('wp_enqueue_scripts', 'kb_stm_enqueue_custom_fonts', 150);
	add_action('wp_head', 'kb_stm_generate_css', 160);

	return true;
}

add_action('wp_enqueue_scripts', function () {
	wp_enqueue_media();
	wp_enqueue_script('kbucket-change-image-js', WPKB_PLUGIN_URL . '/js/change-thumbnail.js', ['jquery'], '1.1', true);

	wp_localize_script(
		'kbucket-change-image-js',
		'my_ajax_object',
		array('ajax_url' => admin_url('admin-ajax.php'))
	);
});

add_action('wp_ajax_change_kbucket_post_thumbnail', 'change_kbucket_post_thumbnail');

function change_kbucket_post_thumbnail()
{
	global $wpdb;
	$id = $_POST['id'];
	$url = $_POST['url'];
	$sql = 'UPDATE `' . $wpdb->prefix . 'kbucket` SET `image_url` = "' . $url . '" WHERE `id_kbucket`="' . $id . '"';
	$wpdb->query($sql);
	wp_send_json('ok');
}


// Add cron job for automatic upload kbucket file
add_action('wp', 'kb_activate_cron_upload');
function kb_activate_cron_upload()
{
	if (!wp_next_scheduled('kb_activate_cron_upload_event')) {
		$activated = get_option('kb_auto_upload');
		if (!empty($activated['active']) && $activated['active']) {
			switch ($activated['interval']) {
				case 'day':
					$time = time() + 3600 * 24;
					break;
				case '3h':
					$time = time() + 3600 * 3;
					break;
				case '1h':
					$time = time() + 3600;
					break;
				default:
					$time = time();
					kb_activate_auto_upload_kbucket();
			}
			wp_schedule_single_event($time, 'kb_activate_cron_upload_event');
		}
	}

	$kb_auto_upload = get_option('kb_auto_upload');
	// Continue un-finished upload images
	if (!empty($kb_auto_upload['active']) && $kb_auto_upload['active']) {
		if (!empty($kb_auto_upload['interval'])) {
			kb_activate_auto_stage_images();
		}
	}
}
add_action('kb_activate_cron_upload_event', 'kb_activate_cron_upload_event_callback');
function kb_activate_cron_upload_event_callback()
{
	$activated = get_option('kb_auto_upload');
	if (!empty($activated['active']) && $activated['active']) {
		kb_activate_auto_upload_kbucket(); // Run import by cron
	}
}
// kb_activate_auto_upload_kbucket();
register_activation_hook(__FILE__, function () {
	update_option('kb_auto_upload_stage', '');
	update_option('kb_auto_upload_logs', []);
	update_option('kb_auto_upload_file', '');
	update_option('kb_auto_images', '');
	update_option('kb_auto_images_all', '');
	//	update_option('kb_auto_upload', []);
	update_option('kb_auto_upload_image_hold', 0);
});


add_action('init', function () {
	if (isset($_REQUEST['dosa'])) {
		kb_activate_auto_stage_upload();
		kb_activate_auto_stage_parsed();
		kb_activate_auto_stage_images();
		die;
	}

	if (!empty($_REQUEST['check-image-parser'])) {
		$image_url = esc_url($_REQUEST['check-image-parser']);
		$uid = 0;

		global $wpdb;
		$image_url = trim($image_url);

		// Check image on begin //
		if (preg_match('/^\/\//i', $image_url)) $image_url = 'http:' . $image_url;

		$image_name = basename($image_url);
		if (empty($image_name)) echo "IMAGE NAME EMPTY!";

		$extension = kb_get_extension_by_name($image_name);
		if (empty($extension)) echo "IMAGE EMPTY EXTENSION!";
		if (!in_array($extension, ['jpeg', 'jpg', 'png', 'webp']))  echo "IMAGE EXTENSION NOT ALLOWED!";

		// Prepare for new image

		// Uniq name to image
		$ecr_title = md5($image_url) . '_' . $uid . '.' . $extension;

		$sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta 
		WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s", "%$ecr_title");
		$post_id = $wpdb->get_row($sql, ARRAY_A);
		if (!empty($post_id['post_id'])) {
			echo "IMAGE ID: " . $post_id['post_id'];
		}

		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['path'] . '/' . $ecr_title;

		$sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='wp_attachment_url' AND meta_value=%s", $file_path);
		$post = $wpdb->get_row($sql, ARRAY_A);
		if (!empty($post['post_id'])) echo "IMAGE ID: " . $post['post_id'];
		$file_url = $upload_dir['url'] . '/' . $ecr_title;

		if (!file_exists($file_path)) {
			$image = kb_copy_image_by_curl($image_url, $file_path);
			if (!$image) echo "CANT UPLOAD IMAGE BY CURL!";
			else echo "FILE UPLOADED: <a target='_blank' href='$file_url'>$file_path</a>";
		} else {
			echo "FILE EXISTS: <a target='_blank' href='$file_url'>$file_path</a>";
		}
		die;
	}
	if (isset($_REQUEST['show-log'])) {
		$arr = get_option('kb_auto_upload_logs');
		foreach ($arr as $item) {
			$val = explode('|', $item);
			echo '<div>' . date('d.m.Y H:i:s', $val[0]) . ' - ' . $val[1] . "</div>\n";
		}
		die;
	}
});

add_action('init', function () {
	if (isset($_REQUEST['sonata'])) {
		//		ini_set('display_errors', 1);
		//		ini_set('display_startup_errors', 1);
		//		error_reporting(E_ALL);
		//		ob_start();

		kb_activate_auto_stage_images();

		//		$res = ob_get_contents();

		//		var_dump($res);
		die;
	}
});

