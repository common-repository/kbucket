<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

include_once WPKB_PATH . '/inc/google-fonts.php';

function render_dashboard()
{
	global $wpdb;
	$amsg = '';
	if (!function_exists('get_plugin_data')) {
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	}
	$data = get_plugin_data(dirname(__FILE__) . '/../KBucket.php');
	echo '<h1>Kbucket <span style="font-size: 10px;font-family: Arial,sans-serif;font-weight: normal;">v' . $data['Version'] . '</span></h1>';

	if (isset($_REQUEST['submit'])) {
		$submit = $_REQUEST['submit'];

		$settings = kb_get_settings();

		switch ($submit) {

				// Update tag
			case 'Update Tag':
				$wpdb->update(
					$wpdb->prefix . 'kbucket',
					array('author_alias' => $_REQUEST['tag_name']),
					array('author' => $_REQUEST['author'])
				);
				$amsg = 'Author alias name has been successfully updated';
				break;

				// Delete Author
			case 'Delete Author':
				$wpdb->delete(
					$wpdb->prefix . 'kbucket',
					array('author' => $_REQUEST['author'])
				);
				$amsg = 'Author data have been successfully deleted';
				break;

				// Add Category
			case 'Add Category':

				$data = array(
					'name' => $_REQUEST['category'],
					'add_date' => date('Y-m-d'),
				);

				if ($_REQUEST['parent_category'] != '') {
					$parCatArr = explode('#', $_REQUEST['parent_category']);

					$data['parent_cat'] = $parCatArr[0];
					$data['level'] = $parCatArr[1] + 1;

					$esc = array(
						'%s',
						'%s',
						'$s',
						'%d',
					);
				} else {
					$data['level'] = 1;

					$esc = array(
						'%s',
						'%s',
						'%d'
					);
				}

				$wpdb->insert(
					$wpdb->prefix . 'kb_category',
					$data,
					$esc
				);
				break;

				// Update Settings
			case 'Update Settings':

				$apikey = kb_getAPIKey();
				if (!$apikey || $apikey !== $_REQUEST['api_key'] && !empty($_REQUEST['api_key'])) {
					// Send reg api key
					$url = 'https://optimalaccess.com/api-gateway/reg-api-key';
					$data = array(
						'apiKey' => $_REQUEST['api_key'],
						'apiHost' => $_REQUEST['api_host'],
						'uid' => get_current_user_id()
					);

					if (!function_exists('curl_init')) {
						die('CURL is not installed!');
					}
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					$result = curl_exec($ch);
					curl_close($ch);


					if ($result !== FALSE && !empty($result)) {
						$result = json_decode($result);
						if (isset($result->success) && $result->success) {
							$amsg = kb_dashboard_get_result_message(array('success' => array($result->message)));

							$settings['api_trial'] = $result->demo;
							$settings['api_key'] = $_REQUEST['api_key'];
						} else $amsg = kb_dashboard_get_result_message(array('error' => array($result->message)));
					}
				}

				// Update page id
				if (!empty($_REQUEST['kb_page_id'])) {
					update_option('kb_setup_page_id', (int)$_REQUEST['kb_page_id']);
				}
				if (!empty($_REQUEST['kb_show_style'])) {
					$settings['kb_show_style'] = $_REQUEST['kb_show_style'];
				}

				if (empty($_REQUEST['api_key'])) {
					$settings['api_trial'] = 0;
					$settings['api_key'] = '';
				}

				if (isset($_REQUEST['kb_share_popap'])) $settings['kb_share_popap'] = $_REQUEST['kb_share_popap'];
				if (isset($_REQUEST['sortBy'])) $settings['sortBy'] = $_REQUEST['sortBy'];
				if (isset($_REQUEST['sortOrder'])) $settings['sortOrder'] = $_REQUEST['sortOrder'];
				if (isset($_REQUEST['no_listing_page'])) $settings['no_listing_page'] = $_REQUEST['no_listing_page'];
				if (isset($_REQUEST['atd'])) $settings['atd'] = $_REQUEST['atd'];
				if (isset($_REQUEST['ptd'])) $settings['ptd'] = $_REQUEST['ptd'];
				if (isset($_REQUEST['dtd'])) $settings['dtd'] = $_REQUEST['dtd'];
				if (isset($_REQUEST['page_title'])) $settings['page_title'] = $_REQUEST['page_title'];
				if (isset($_REQUEST['site_search'])) $settings['site_search'] = $_REQUEST['site_search'];
				if (isset($_REQUEST['site_vc'])) $settings['site_vc'] = $_REQUEST['site_vc'];
				if (isset($_REQUEST['cat_sidebar'])) $settings['cat_sidebar'] = $_REQUEST['cat_sidebar'];
				if (isset($_REQUEST['sidebar_bg'])) $settings['sidebar_bg'] = $_REQUEST['sidebar_bg'];
				if (isset($_REQUEST['sidebar_font_color'])) $settings['sidebar_font_color'] = $_REQUEST['sidebar_font_color'];

				if (isset($_REQUEST['dropdown_menu_bg'])) $settings['dropdown_menu_bg'] = $_REQUEST['dropdown_menu_bg'];
				if (isset($_REQUEST['dropdown_menu_font_color'])) $settings['dropdown_menu_font_color'] = $_REQUEST['dropdown_menu_font_color'];
				if (isset($_REQUEST['dropdown_menu_hover_color'])) $settings['dropdown_menu_hover_color'] = $_REQUEST['dropdown_menu_hover_color'];

				if (isset($_REQUEST['yt_apikey'])) $settings['yt_apikey'] = sanitize_text_field($_REQUEST['yt_apikey']);
				

				kb_update_settings($settings);

				break;

				// Update Category
			case 'Update':
			// 	 echo "<pre>";
			//  print_R($_REQUEST);die();
				if (isset($_REQUEST['color_tag_cloud'])) $settings['color_tag_cloud'] = $_REQUEST['color_tag_cloud'];
				if (isset($_REQUEST['bg_tag_cloud'])) $settings['bg_tag_cloud'] = $_REQUEST['bg_tag_cloud'];
				if (isset($_REQUEST['headline_color'])) $settings['headline_color'] = $_REQUEST['headline_color'];
				if (isset($_REQUEST['post_font'])) $settings['post_font'] = $_REQUEST['post_font'];
				if (isset($_REQUEST['post_cards_color'])) $settings['post_cards_color'] = $_REQUEST['post_cards_color'];
				if (isset($_REQUEST['post_cards_bg'])) $settings['post_cards_bg'] = $_REQUEST['post_cards_bg'];
				if (isset($_REQUEST['post_cards_search_btn'])) $settings['post_cards_search_btn'] = $_REQUEST['post_cards_search_btn'];
				if (isset($_REQUEST['bg_active_tag_cloud'])) $settings['bg_active_tag_cloud'] = $_REQUEST['bg_active_tag_cloud'];
				if (isset($_REQUEST['fz_tag_cloud'])) $settings['fz_tag_cloud'] = $_REQUEST['fz_tag_cloud'];
				if (isset($_REQUEST['model_position'])) $settings['model_position'] = $_REQUEST['model_position'];
				

				if (!empty($_REQUEST['description'])) {
					foreach ($_REQUEST['description'] as $i => $categoryDescription) {

						$err = '';
						$values = array();
						if (isset($_REQUEST['upload_file'][$i]) && !empty($_REQUEST['upload_file'][$i])) $values['image'] = $_REQUEST['upload_file'][$i];

						/*if ($categoryDescription != '') {
							$values['description'] = $categoryDescription;
						}commented by Has team */
						$values['description'] = $categoryDescription;
						// No action if no upload image and description
						if (empty($values)) continue;

						$wpdb->update($wpdb->prefix . 'kb_category', $values, array('id_cat' => $_REQUEST['idArr'][$i]));

						if ($err !== '') : ?>
							<div class="updated updated-error">
								<p><strong><?php echo $err; ?></strong></p>
							</div>
		<?php endif;
					}
				}
				$settings['hidden_categories'] = [];
				if (!empty($_REQUEST['idArr'])) {
					foreach ($_REQUEST['idArr'] as $id => $val) {
						$settings['hidden_categories'][$val] = !empty($_REQUEST['hidden'][$val]) ? true : false;
					}
				}

			// 		 echo "<pre>";
			//  print_R($settings);die();
				kb_update_settings($settings);

				break;

			case 'Update CSS':
				if (isset($_REQUEST['custom_css'])) $settings['custom_css'] = $_REQUEST['custom_css'];
				kb_update_settings($settings);
				break;
			case 'Save Widget':
				if (isset($_REQUEST['kb_widget'])) $settings['kb_widget'] = $_REQUEST['kb_widget'];
				kb_update_settings($settings);
				break;
		} // end switch submit
	} // endif submit

	echo $amsg;

	kb_includeVar(
		WPKB_PATH . '/templates/admin-tabs.php',
		array()
	);
}

/**
 * Add Kbucket section page in dashboard
 * Fired by wp action hook: admin_menu
 */
function kbucket_action_admin_menu()
{
	add_menu_page('KBucket Settings', 'KBucket', 'administrator', 'KBucket', 'render_dashboard');
}

add_action('init', 'kb_action_dashboard');
function kb_action_dashboard()
{
	if (is_admin()) {
		add_action('admin_menu', 'kbucket_action_admin_menu', 1);
	}
}

add_action('wp_footer', 'kb_custom_css_footer', 10000);
function kb_custom_css_footer()
{
	$settings = kb_get_settings();

	if (!empty($settings['custom_css'])) : ?>
		<style>
			<?php echo $settings['custom_css']; ?>;
		</style>
	<?php
	endif;
}

function kb_action_ajax_get_kbuckets()
{
	if (empty($_POST['category_id'])) return;
	global $wpdb;
	header('Content-Type: application/json');
	$sql = "
		SELECT DISTINCT 
			c.name,
			k.id_kbucket,
			k.title,
			k.url_kbucket,
			k.post_id AS link,
			k.author,
			DATE_FORMAT(STR_TO_DATE(k.pub_date, '%%Y-%%m-%%d %%H:%%i:%%s'), '%%Y-%%m-%%d') as add_date,
			k.image_url,
			k.post_id
		FROM {$wpdb->prefix}kbucket k
		LEFT JOIN {$wpdb->prefix}kb_category c ON k.id_cat=c.id_cat
		WHERE c.alias_name=%s
		ORDER BY add_date DESC";
	$categoryId = esc_sql($_POST['category_id']);
	$query = $wpdb->prepare($sql, $categoryId);
	$kbuckets = $wpdb->get_results($query);
	echo json_encode(array('data' => $kbuckets, 'limit' => count($kbuckets)));
	exit;
}
add_action('wp_ajax_get_kbuckets', 'kb_action_ajax_get_kbuckets');

/**
 * Render dropdown with subcategories by category id
 * Requires POST param category_id
 * Fired by wp ajax hook: wp_ajax_get_subcategories
 */
function kb_action_ajax_get_subcategories()
{
	if (empty($_POST['category_id'])) return;

	global $wpdb;
	$sql = "
		SELECT
			id_cat,
			name,
			alias_name
		FROM {$wpdb->prefix}kb_category
		WHERE parent_cat=%s";

	$categoryId = sanitize_text_field($_POST['category_id']);

	$query = $wpdb->prepare($sql, $categoryId);

	$subcategories = $wpdb->get_results($query);

	$response = '<option value="">Select Subcategory</option>';
	foreach ($subcategories as $s) {
		$response .= '<option value="' . esc_attr($s->alias_name) . '">' . esc_html($s->name) . '</option>';
	}

	echo $response;
	exit;
}
add_action('wp_ajax_get_subcategories', 'kb_action_ajax_get_subcategories');

add_action('init', 'kb_download_logs');
function kb_download_logs()
{
	if (!empty($_REQUEST['get-log'])) {
		if (!is_super_admin()) return;
		if (WPKB_PLUGIN_LOGS_DB) {
			$log = implode("\n", kb_get_auto_upload_logs(0));
			$name = 'kb-log-' . date('d.m.Y') . '.log';
			header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
			header("Cache-Control: public"); // needed for internet explorer
			header("Content-Type: text/plain");
			header("Content-Transfer-Encoding: Binary");
			header("Content-Length:" . strlen($log));
			header("Content-Disposition: attachment; filename=$name");
			echo $log;
		} else {
		}

		die();
	}
}

add_action('wp_ajax_kb_get_content_tags', 'kb_ajax_get_category_tags');
function kb_ajax_get_category_tags()
{
	if (empty($_POST['category'])) return;
	if (!empty($_POST['blog_id'])) $GLOBALS['current_blog_id'] = (int)$_POST['blog_id'];
	$tags = kb_get_category_tags($_POST['category'], $_POST['blog_id']);
	foreach ($tags as $k => $tag) {
		if (!$tag->name) unset($tags[$k]);
	}
	die(json_encode([
		'tags' => array_values($tags),
		'author' => kb_get_author_tags($_POST['category']),
		'publisher' => kb_get_publisher_tags($_POST['category'])
	]));
}

add_action('wp_ajax_kb_get_related_tags', 'kb_ajax_get_related_tags');
function kb_ajax_get_related_tags()
{
	if (empty($_POST['category']) || empty($_POST['tag'])) return;
	if (!empty($_POST['blog_id'])) $GLOBALS['current_blog_id'] = (int)$_POST['blog_id'];
	$tags = kb_get_related_tags($_POST['category'], sanitize_title($_POST['tag']), $_POST['blog_id']);
	die(json_encode($tags));
}

add_action('wp_ajax_kb_save_widget_data', 'kb_save_widget_data');
function kb_save_widget_data()
{
	if (empty($_POST['data'])) return;

	if (kb_isTrialKey()) {
		$_POST['data'] = array_slice($_POST['data'], 0, 3);
	}

	$blog_id = !empty($_POST['blog_id']) ? (int)$_POST['blog_id'] : 1;
	$res = update_option("kb_widget_data_{$blog_id}", $_POST['data']);
	die(json_encode([
		'options' => get_option('kb_widget_data'),
		'success' => $res
	]));
}

add_action('wp_ajax_kb_get_widget_data', 'kb_get_widget_data');
function kb_get_widget_data()
{
	$blog_id = !empty($_POST['blog_id']) ? (int)$_POST['blog_id'] : 1;
	$options = get_option("kb_widget_data_{$blog_id}");
	if (empty($options)) $options = get_option("kb_widget_data");
	die(json_encode([
		'options' => $options
	]));
}

add_shortcode('kb_widget_list', 'kb_widget_kbucket_list');
function kb_widget_kbucket_list($attrs)
{
	$settings = kb_get_settings();
	$args = shortcode_atts(array(
		'category' 		=> '',
		'sub-category'	=> "",
		'tag'			=> "",
		'related'		=> "",
		'publisher'		=> "",
		'author'		=> "",
		'count'			=> 9,
		'inline'		=> 3,
		'style'			=> 'masonry',
		'sh_date'		=> 0,
		'sh_author' 	=> 1,
		'sh_publisher' 	=> 1,
		'sh_title' 		=> 1,
		'sh_image' 		=> 0,
		'sh_heading_image' => 0
	), $attrs, 'kb_widget_list');

	$html = '';
	// Fill $html var with data
	$html .= '<div class="kb-widget-wrap">';
	wp_enqueue_style('kbucket-facebox-css', WPKB_PLUGIN_URL . '/css/facebox.css');
	wp_enqueue_style('kbucket-css', WPKB_PLUGIN_URL . '/css/kbucket-style.css', array('kbucket-facebox-css'), WPKB_VERSION);
	wp_enqueue_style('fa', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', NULL, NULL, 'all');



	//	$html .= '<script>'.file_get_contents(WPKB_PLUGIN_URL . '/js/kbucket.js').'</script>';
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	if (!wp_script_is('addthis_widget', 'enqueued')) wp_enqueue_script('addthis-kb-footer', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-xxxxxxxxxxxxxxxxx', array('jquery'), (WP_DEBUG) ? time() : null, true);

	//wp_enqueue_script('kbucket-widget-js', WPKB_PLUGIN_URL . '/js/kbucket.widget.js', array('jquery', 'facebox-js', 'jquery-ui-core', 'kbucket-slick-js', 'jquery-ui-draggable', 'jquery-ui-droppable', 'kb_sp_masonry'), (WP_DEBUG) ? time() : null, true);
	wp_register_script('kbucket-custom-widget-js', WPKB_PLUGIN_URL . '/js/kbucket.custom-widget.js', array('jquery', 'facebox-js', 'kbucket-slick-js','kb_sp_masonry'), (WP_DEBUG) ? time() : null, false);
	wp_enqueue_script('kbucket-custom-widget-js');
	
	wp_enqueue_script('facebox-js', WPKB_PLUGIN_URL . '/js/facebox.js', array('jquery'), '', false);
	
	

  
	$localize = array(
		'kbucketUrl' => WPKB_PLUGIN_URL,
		'ajaxurl' => admin_url('admin-ajax.php'),
		'blog_id' => get_current_blog_id()

	);
	$localize['model_position']=$settings['model_position']?$settings['model_position']:'center';
	//echo "<pre>";print_R($settings);die();
	wp_localize_script('kbucket-custom-widget-js', 'kbObj', $localize);

	
	wp_enqueue_script('kbucket-slick-js');
	wp_enqueue_style('kbucket-slick-css');
	ob_start();

	$url_parts = kb_parse_url();

	if (kb_is_mobile()) {
		if (!empty($url_parts['article'])) {
			$kbucket = get_kbucket_by_slug($url_parts['article']);
			kb_render_page_mobile($kbucket);
			$kbContent = ob_get_contents();
			ob_end_clean();
			return $kbContent;
		}

		kb_render_head_mobile();
		kb_render_content_mobile();
		$kbContent = ob_get_contents();
		ob_end_clean();
		return $kbContent;
	}

	$localize['inline'] = $args['inline'];
	if (!empty($url_parts['article'])) {
		$localize['shareId'] = $url_parts['article'];
	}

	$category = !empty($args['category']) ? $args['category'] : 0;
	$localize['categoryName'] = $category;
	$localize['model_position']=$settings['model_position']?$settings['model_position']:'center';
	//echo"<pre>";print_R($settings);die();
	//wp_localize_script('kbucket-js', 'kbObj', $localize);
	?>
	<script>
	   var dynamicLocalizationData = <?php echo json_encode($localize); ?>;
	</script>
<?php
	//wp_localize_script('kbucket-custom-js', 'kbObj', $localize);
	
	$subcategory = kb_get_subcat_by_slug($args['sub-category']);

	$wrapper_Class = $args['style'];
	if ($args['style'] == "slider" && $args['sh_image'] == 1) {
		$wrapper_Class = "image-slider";
	}
	//	$args['inline'] = 5;

	?>
	<style>
		.kb-items-list.slider .kb-item-inner-wrap .__img-wrap,
		.kb-items-list.slider .kb-item-inner-wrap .body_wrap {
			flex: 1 0;
		}
	</style>
	<div id="kb-wrapper" class="kb-wrapper-kbucket-widget">
		<div class="kb-row">
			<div id="kb-items-<?php echo rand(1000, 10000) ?>" class="kb-col-12">
				<ul class="kb-items-list <?php echo $wrapper_Class;
											?>" data-inline="<?php echo $args['inline'] ?>">
					<?php $kbuckets = kb_get_kbuckets($args);
					if (count($kbuckets['kbuckets']) > 0) {
						foreach ($kbuckets['kbuckets'] as $i => $m) {
							$description = wp_kses(
								$m->description,
								array(
									'a' => array(
										'href' => array(),
										'title' => array()
									),
									'br' => array(),
								)
							);

							$kbucket = get_kbucket_by_id($m->kbucketId);
							if (empty($kbucket->post_id)) {
								$shareData = kb_get_kbucket_share_data($kbucket);
							} else {
								$post = get_post($kbucket->post_id);
								// Get sharing data
								$shareData = array(
									'url' => add_query_arg([
										'article' => $kbucket->short_url
									], esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI']))),
									'imageUrl' => $kbucket->image_url,
									'title' => $kbucket->title,
									'description' => $kbucket->description,

								);
							}

							$url = add_query_arg([
								'article' => $kbucket->short_url
							], WPKB_SITE_URL . esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI']))); //WPKB_SITE_URL . '/' . $shareData['url'];
							
							kb_includeVar(
								WPKB_PATH . 'templates/kb-item-widget.php',
								array(
									'kbucket' => $kbucket,
									'url' => $url,
									'm' => $m,
									'settings' => $settings,
									'share' => $shareData,
									'perLine' => $args['inline'],
									'subcat' => $subcategory,
									'sh_date' => $args['sh_date'],
									'sh_author' => $args['sh_author'],
									'sh_publisher' => $args['sh_publisher'],
									'sh_title' => $args['sh_title'],
									'sh_image' => $args['sh_image'],
									'sh_heading_image' => $args['sh_heading_image']
								)
							);
						}
					} else {
						echo '<div class="kb-list-item"><b>No result found. Please refine your search</b></div>';
					} ?>
				</ul>
			</div>
		</div><!-- /.kb-row -->
	</div><!-- /#kb-wrapper -->
<?php
	$kbContent = ob_get_contents();
	ob_end_clean();

	$html .= $kbContent;
	$html .= '</div>';

	return $html;
}

