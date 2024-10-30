<?php
if ( !defined( 'ABSPATH' ) ) exit;

if(class_exists('WPBakeryShortCode')){

	class VCExtendAddonKBauthorTags {
		function __construct() {
			// We safely integrate with VC with this hook
			add_action( 'init', array( $this, 'KBTagsintegrateWithVC' ) );

			// Use this when creating a shortcode addon
			add_shortcode( 'kb_tag_sidebar', array( $this, 'renderKBTags' ) );
		}

		public function KBTagsintegrateWithVC() {
			// Check if Visual Composer is installed
			if ( ! defined( 'WPB_VC_VERSION' ) ) {
				// Display notice that Visual Compser is required
				add_action('admin_notices', array( $this, 'showVcVersionNotice' ));
				return;
			}

			/*
			Add your Visual Composer logic here.
			Lets call vc_map function to "register" our custom shortcode within Visual Composer interface.

			More info: http://kb.wpbakery.com/index.php?title=Vc_map
			*/
			vc_map( array(
				"name" => __("Sidebar of Kbucket tags", WPKB_TEXTDOMAIN),
				"description" => __("List of category, author, related tags", WPKB_TEXTDOMAIN),
				"base" => "kb_tag_sidebar",
				"class" => "",
				"controls" => "full",
				'icon' => WPKB_PLUGIN_URL.'/images/vc-icon.png',
				'category' => __('Optimal Access', WPKB_TEXTDOMAIN),
				"params" => array(
				    array(
						"type" => "textfield",
						"holder" => "div",
						"class" => "",
						"heading" => __("Content before", WPKB_TEXTDOMAIN),
						"param_name" => "before_widget",
					),
					array(
						"type" => "textfield",
						"holder" => "div",
						"class" => "",
						"heading" => __("Content after", WPKB_TEXTDOMAIN),
						"param_name" => "after_widget",
					),
				)
			) );
		}

		/*
		Shortcode logic how it should be rendered
		*/
		public function renderKBTags( $atts, $content = null ) {
			extract( shortcode_atts( array(
				'before_widget' => '',
				'after_widget' => '',
			), $atts ) );

			$url_parts = kb_parse_url();
			// $action = KBucket::get_kbucket_instance();

			if(!is_kbucket_page_func() && !is_vc_activate()) return false;

			$output = "";

			if(!empty($atts['before_widget'])) $output .= $atts['before_widget'];

			$output .= '<div id="kb-tags-wrap-1" class="kb_is_mobile_menu">';
				$settings = kb_get_settings();

				if(isset($settings['cat_sidebar']) && (int)$settings['cat_sidebar'] == 2) $output .= kb_render_sidebar_navigation(true);

				// Render category tags
				$categoryTags = kb_get_category_tags();
				if ( ! empty( $categoryTags ) ) {
					$output .= '<h3>Category Tags'.(kb_has_tag_tagcloud(kb_get_active_tagname(), $categoryTags) ? '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>':'').'</h3>';
					$output .= kb_render_tags_cloud(
						$categoryTags,
						'm',
						'main',
						array( 'value' => kb_get_active_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
						'c-tag',
						true
					);
				}


				// Render related tags
				if(!empty($url_parts['c-tag']) && !empty($url_parts['subcat'])){
					$relatedTags = kb_get_related_tags($url_parts['subcat'], $url_parts['c-tag']);

					// Render related tags
					if ( ! empty( $relatedTags ) ) {
						$output .= '<h3>Related Tags'.(kb_has_tag_tagcloud(kb_get_active_related_tagname(), $relatedTags) ? '<a href="'.kb_get_kbucket_url('related').'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>':'').'</h3>';

						$output .= kb_render_tags_cloud(
							$relatedTags,
							'm',
							'related',
							array( 'value' => kb_get_active_related_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
							'r-tag',
							true
						);
					}
				}


				// Render Author tags
				if ( $settings['atd'] ) {
					$authorTags = kb_get_author_tags();

					$output .= '<h3>Author Tags'.(kb_has_tag_tagcloud(kb_get_active_tagname(), $authorTags) ? '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>':'').'</h3>';
					$output .= kb_render_tags_cloud(
						$authorTags,
						'm',
						'author',
						array( 'value' => kb_get_active_tagname(), 'dbKey' => 'author', 'title' => 'author' ),
						'a-tag',
						true
					);
				}

				// Render Publisher tags
				if ( $settings['ptd'] ) {
					$publisherTags = kb_get_publisher_tags();

					if ( count( $publisherTags ) ) {
						$output .= '<h3>Publisher Tags'
						           . ( kb_has_tag_tagcloud( kb_get_active_tagname(),
								$publisherTags ) ? '<a href="' . kb_get_kbucket_url()
						                           . '" class="kb-clear-tag">' . __( "Reset",
									WPKB_TEXTDOMAIN ) . '</a>' : '' ) . '</h3>';
						$output .= kb_render_tags_cloud(
							$publisherTags,
							'm',
							'publisher',
							[
								'value' => kb_get_active_tagname(),
								'dbKey' => 'publisher',
								'title' => 'publisher'
							],
							'p-tag',
							true
						);
					}
				}

			$output .= '</div>';

			if(!empty($atts['after_widget'])) $output .= $atts['after_widget'];

			return $output;
		}

		/*
		Show notice if your plugin is activated but Visual Composer is not
		*/
		public function showVcVersionNotice() {
			if( !function_exists('get_plugin_data') ){
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data(__FILE__);
			echo '
			<div class="updated"><p>'.sprintf(__('<strong>%s</strong> requires <strong><a href="http://bit.ly/vcomposer" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', 'vc_extend'), $plugin_data['Name']).'</p></div>';
		}
	}
	// Finally initialize code
	new VCExtendAddonKBauthorTags();
}


function kb_shortcode_kbucket_tags($attrs){
	$args = shortcode_atts(array(
		'before_widget' => '',
		'after_widget' => '',
	),$attrs,'shortcode_kbucket_tags');

	if(!is_kbucket_page_func() && !is_vc_activate()) return false;

	$url_parts = kb_parse_url();

	$output = "";

	if(!empty($args['before_widget'])) $output .= $args['before_widget'];

	$output .= '<div id="kb-tags-wrap-1" class="kb_is_mobile_menu">';
	$output .= '<div class="kb_mobile_menu_wrap"> <button id="kb_is_menu_close" class="kb_mobile_menu_close"><svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg></button><div class="kb_mobile_menu_container">';

		$settings = kb_get_settings();

		if(isset($settings['cat_sidebar']) && (int)$settings['cat_sidebar'] == 2) $output .= kb_render_sidebar_navigation(true);

		// Render category tags
		$categoryTags = kb_get_category_tags();
		if ( ! empty( $categoryTags ) ) {
			$output .= '<h3>Category Tags '.(kb_has_tag_tagcloud(kb_get_active_tagname(), $categoryTags) ? '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>':'').'</h3>';
			$output .= kb_render_tags_cloud(
				$categoryTags,
				'm',
				'main',
				array( 'value' => kb_get_active_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
				'c-tag',
				true
			);
		}


		// Render related tags
		if(!empty($url_parts['c-tag']) && !empty($url_parts['subcat'])){
			$relatedTags = kb_get_related_tags($url_parts['subcat'], $url_parts['c-tag']);

			// Render related tags
			if ( ! empty( $relatedTags ) ) {
				$output .= '<h3>Related Tags'.(kb_has_tag_tagcloud(kb_get_active_related_tagname(), $relatedTags) ? '<a href="'.kb_get_kbucket_url('related').'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>':'').'</h3>';
				$output .= kb_render_tags_cloud(
					$relatedTags,
					'm',
					'related',
					array( 'value' => kb_get_active_related_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
					'r-tag',
					true
				);
			}
		}


		// Render Author tags
		if ( $settings['atd'] ) {
			$authorTags = kb_get_author_tags();

			$output .= '<h3>Author Tags'.(kb_has_tag_tagcloud(kb_get_active_tagname(), $authorTags) ? '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>':'').'</h3>';
			$output .= kb_render_tags_cloud(
				$authorTags,
				'm',
				'author',
				array( 'value' => kb_get_active_tagname(), 'dbKey' => 'author', 'title' => 'author' ),
				'a-tag',
				true
			);
		}

		// Render Publisher tags
		if ( $settings['ptd'] ) {
			$publisherTags = kb_get_publisher_tags();

			if ( count( $publisherTags ) ) {
				$output .= '<h3>Publisher Tags' . ( kb_has_tag_tagcloud( kb_get_active_tagname(),
						$publisherTags ) ? '<a href="' . kb_get_kbucket_url()
				                           . '" class="kb-clear-tag">' . __( "Reset",
							WPKB_TEXTDOMAIN ) . '</a>' : '' ) . '</h3>';
				$output .= kb_render_tags_cloud(
					$publisherTags,
					'm',
					'publisher',
					[
						'value' => kb_get_active_tagname(),
						'dbKey' => 'publisher',
						'title' => 'publisher'
					],
					'p-tag',
					true
				);
			}
		}

	$output .= '</div></div></div>';

	if(!empty($args['after_widget'])) $output .= $args['after_widget'];

	return $output;
}
add_shortcode( 'shortcode_kbucket_tags', 'kb_shortcode_kbucket_tags' );
