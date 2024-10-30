<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function kb_action_admin_enqueue_scripts()
{
	wp_enqueue_media();
	$version = WP_DEBUG ? time() : WPKB_VERSION;
	wp_enqueue_style('tabcontent-css', WPKB_PLUGIN_URL . '/css/tabcontent.css', array(), '1.0', 'all');
	wp_enqueue_style('colorpicker-css', WPKB_PLUGIN_URL . '/css/colorpicker.css', array(), '1.0', 'all');
	wp_enqueue_style('datatables-css', WPKB_PLUGIN_URL . '/css/jquery.dataTables.min.css');
	wp_enqueue_style('datatables-css', WPKB_PLUGIN_URL . '/css/jquery.dataTables_themeroller.css');
	wp_enqueue_style('kbucket-facebox-css', WPKB_PLUGIN_URL . '/css/facebox.css');
	wp_enqueue_style('kbucket-css', WPKB_PLUGIN_URL . '/css/kbucket-style.css', array('kbucket-facebox-css'), $version);
	wp_enqueue_style('fa', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', NULL, NULL, 'all');
	wp_enqueue_style('admin-kbucket-style', WPKB_PLUGIN_URL . '/css/admin-kbucket-style.css', NULL, $version, 'all');

	wp_enqueue_script('jquery-validate-js', WPKB_PLUGIN_URL . '/js/jquery.validate.min.js');
	wp_enqueue_script('tabcontent-js', WPKB_PLUGIN_URL . '/js/tabcontent.js');
	wp_enqueue_script('datatables-js', WPKB_PLUGIN_URL . '/js/jquery.dataTables.min.js');
	wp_enqueue_script('colorpicker-js', WPKB_PLUGIN_URL . '/js/colorpicker.js');
	wp_enqueue_script('admin-js', WPKB_PLUGIN_URL . '/js/admin.js', null, '1.3');

	wp_enqueue_script('wp-color-picker');
	wp_enqueue_style('wp-color-picker');

	//	wp_enqueue_script( 'jquery-ui-accordion' );
	//	global $wp_scripts;
	//	wp_enqueue_style("jquery-ui-css", "http://ajax.googleapis.com/ajax/libs/jqueryui/{$wp_scripts->registered['jquery-ui-core']->ver}/themes/ui-lightness/jquery-ui.min.css");

	wp_localize_script(
		'admin-js',
		'ajaxObj',
		array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'adminUrl' => admin_url() . 'admin.php',
			'pluginUrl' => WPKB_PLUGIN_URL,
			'kbucketUrl' => KBUCKET_URL,
		)
	);
}

/**
 * Filter the Media list table columns to add a File Size column.
 *
 * @param array $posts_columns Existing array of columns displayed in the Media list table.
 * @return array Amended array of columns to be displayed in the Media list table.
 */
function kb_media_columns_orig_path($posts_columns)
{
	$posts_columns['orig_path'] = __('Original Path', WPKB_TEXTDOMAIN);
	$posts_columns['orig_size'] = __('Original Size', WPKB_TEXTDOMAIN);

	return $posts_columns;
}

/**
 * Display File Size custom column in the Media list table.
 *
 * @param string $column_name Name of the custom column.
 * @param int    $post_id Current Attachment ID.
 */
function kb_media_custom_column_orig_path($column_name, $post_id)
{
	if ('orig_path' !== $column_name) {
		return;
	}
	$orig_path = get_post_meta($post_id, 'wp_attachment_orig_url', true);
	echo '<a href="' . $orig_path . '" title="' . $orig_path . '">' . basename($orig_path) . '</a>';
}

function kb_media_custom_column_orig_size($column_name, $post_id)
{
	if ('orig_size' !== $column_name) {
		return;
	}

	$orig_size = get_post_meta($post_id, 'wp_attachment_size', true);
	echo size_format($orig_size, 2);
}

/**
 * Adjust File Size column on Media Library page in WP admin
 */
function kb_orig_path_column_orig_path()
{
	echo '<style>.fixed .column-orig_path,.fixed .column-orig_size {width: 10%;}</style>';
}

add_action('wp_enqueue_scripts', 'kbucket_masonry_scripts_add');
function kbucket_masonry_scripts_add()
{
	wp_enqueue_script('kb_imagesloaded', WPKB_PLUGIN_URL . '/js/masonry/imagesloaded.pkgd.min.js', 'jquery', (WP_DEBUG) ? time() : null, false);
	//wp_enqueue_script('kb_sp_masonry', WPKB_PLUGIN_URL . '/js/masonry/masonry.pkgd.js', 'jquery', (WP_DEBUG) ? time() : null, false);
	wp_enqueue_script('kb_sp_masonry', WPKB_PLUGIN_URL . '/js/masonry/masonry-new.pkgd.js', 'jquery', (WP_DEBUG) ? time() : null, false);


	
}


/**
 * Set frontend styles and scripts
 * Fired by wp action hook: wp_enqueue_scripts
 */
function kb_action_init_scripts()
{
	// Invoke jQuery mobile for mobile version
	if (is_kbucket_page_func()) {
		if (kb_is_mobile()) {

			global $wp_styles;

			// Reset theme css
			$wp_styles = array();

			global $wp_scripts;

			// Reset all theme scripts
			$wp_scripts = array();

			wp_enqueue_style('jquery-mobile-css', WPKB_PLUGIN_URL . '/css/jquery.mobile-1.4.5.min.css');
			wp_enqueue_style('jquery-mobile-structure-css', WPKB_PLUGIN_URL . '/css/jquery.mobile.structure-1.4.5.min.css');
			wp_enqueue_style('jquery-mobile-theme-css', WPKB_PLUGIN_URL . '/css/jquery.mobile.theme-1.4.5.min.css');
			wp_enqueue_style('kbucket-mobile-css', WPKB_PLUGIN_URL . '/css/style-mobile.css');

			wp_enqueue_script('jquery-mobile-js', WPKB_PLUGIN_URL . '/js/jquery.mobile-1.4.5.js', array('jquery'));
			//wp_enqueue_script( 'addthis-kb-footer', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-xxxxxxxxxxxxxxxxx', array( 'jquery' ) );

			return;
		}

		wp_enqueue_script('clipboard-js', '//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.4.0/clipboard.min.js', array('jquery'), '', false);
		wp_enqueue_script('pinterest-widget-js', '//assets.pinterest.com/js/pinit.js', array('jquery'), '', false);

		wp_enqueue_style('kbucket-facebox-css', WPKB_PLUGIN_URL . '/css/facebox.css');
		wp_enqueue_style('kbucket-css', WPKB_PLUGIN_URL . '/css/kbucket-style.css', array('kbucket-facebox-css'), WPKB_VERSION);

		$settings = kb_get_settings();
		$custom_css = '';


		if (isset($settings['cat_sidebar']) && 2 == (int)$settings['cat_sidebar']) {
			$sidebar_color = isset($settings['sidebar_bg']) ? $settings['sidebar_bg'] : '#dfdfdf';
			$sidebar_font_color = isset($settings['sidebar_font_color']) ? $settings['sidebar_font_color'] : '#333333';
			list($sfc_r, $sfc_g, $sfc_b) = sscanf($sidebar_font_color, "#%02x%02x%02x");

			$custom_css .= "
				.wrap_category_nav .category_nav_inner{
					background-color: {$sidebar_color};
				}
				.wrap_category_nav .category_nav_inner #kb-menu-sidebar ul li h4,
				.wrap_category_nav .category_nav_inner #kb-menu-sidebar ul li span,
				.wrap_category_nav .category_nav_inner input[type=radio]:checked + span:after,
				.wrap_category_nav .category_nav_inner input[type=radio]:not(:checked) + span:after{
					color: {$sidebar_font_color};
				}
				.wrap_category_nav .category_nav_inner input[type=radio]:checked + span:before,
				.wrap_category_nav .category_nav_inner input[type=radio]:not(:checked) + span:before{
					border: 1px solid {$sidebar_font_color};
					box-shadow: 2px 2px 7px rgba({$sfc_r},{$sfc_g},{$sfc_b},0.2);
				}
			";
		}
		if (isset($settings['cat_sidebar']) && 3 == (int)$settings['cat_sidebar']) {
			$menu_color = isset($settings['dropdown_menu_bg']) ? $settings['dropdown_menu_bg'] : '#dfdfdf';
			$menu_font_color = isset($settings['dropdown_menu_font_color']) ? $settings['dropdown_menu_font_color'] : '#333333';
			$menu_hover_color = isset($settings['dropdown_menu_hover_color']) ? $settings['dropdown_menu_hover_color'] : '#9d9797';

			$custom_css .= "
				#kb-menu-dropdown{
					background-color: {$menu_color} !important;
				}
				.kb-menu-top li a{
					color: {$menu_font_color} !important;
				}
				.kb-menu-top > li > a:hover{
					color: {$menu_hover_color} !important;
				}
				.kb-menu-top > li > .kb-menu-dropdown li:hover,
				.kb-menu-top > li > .kb-menu-dropdown li.kb-menu-active{
					background-color: {$menu_hover_color} !important;
				}
			";
		}
		wp_add_inline_style('kbucket-css', $custom_css);

		// Register Facebox modal window
		//wp_register_script('facebox-js', WPKB_PLUGIN_URL . '/js/facebox.js', array('jquery'), '', false);
		//if(!wp_script_is( 'addthis_widget', 'enqueued')) wp_enqueue_script('addthis-kb-footer', '//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-xxxxxxxxxxxxxxxxx',array( 'jquery' ),( WP_DEBUG ) ? time() : null,true);


		// Register Kbucket scripts
		//		if(!empty($settings['kb_show_style']) && $settings['kb_show_style'] == 'carousel') {
		//			wp_enqueue_script('kbucket-slick-js');
		//			wp_enqueue_style('kbucket-slick-css');
		//			wp_register_script( 'kbucket-js',WPKB_PLUGIN_URL . '/js/kbucket.js', array( 'jquery', 'kbucket-slick-js', 'clipboard-js', 'facebox-js', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'kb_sp_masonry' ),( WP_DEBUG ) ? time() : null,true);
		//		}else{
		//			wp_register_script( 'kbucket-js',WPKB_PLUGIN_URL . '/js/kbucket.js', array( 'jquery', 'clipboard-js', 'facebox-js', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'kb_sp_masonry' ),( WP_DEBUG ) ? time() : null,true);
		//		}


		wp_register_script('kbucket-js', WPKB_PLUGIN_URL . '/js/kbucket.js', array('jquery', 'clipboard-js', 'facebox-js', 'jquery-ui-core', 'jquery-ui-draggable', 'jquery-ui-droppable', 'kb_sp_masonry'), (WP_DEBUG) ? time() : null, false);
	    //wp_enqueue_script('kbucket-js');
		wp_register_script('kbucket-custom-js', WPKB_PLUGIN_URL . '/js/custom.js', array('jquery', 'clipboard-js', 'facebox-js', 'kb_sp_masonry'), (WP_DEBUG) ? time() : null, false);

		wp_enqueue_script('kbucket-custom-js');

		//wp_enqueue_script('facebox-js');
		wp_enqueue_script('facebox-js', WPKB_PLUGIN_URL . '/js/facebox.js', array('jquery'), '', false);

		//wp_enqueue_script('facebox-js', WPKB_PLUGIN_URL . '/js/facebox-right.js', array('jquery'), '', false);
	}
	// Slick.JS for carousel effect
	wp_register_script('kbucket-slick-js', WPKB_PLUGIN_URL . '/js/slick.min.js', array('jquery'), (WP_DEBUG) ? time() : null, false);
	wp_register_style('kbucket-slick-css', WPKB_PLUGIN_URL . '/css/slick.css', null, (WP_DEBUG) ? time() : null, false);
}


