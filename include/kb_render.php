<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/**
 * Render KBucket listings page content
 * Fired by wp filter hook: the_content
 */
function kb_filter_the_content( $content = '' ) {
	ob_start();
	$settings = kb_get_settings();
	
	$localize = array(
		'kbucketUrl' => WPKB_PLUGIN_URL,
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'blog_id' => get_current_blog_id()
	);

	$url_parts = kb_parse_url();

	if ( kb_is_mobile() ) {
		if ( !empty($url_parts['article']) ) {
			$kbucket = get_kbucket_by_slug( $url_parts['article'] );
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

	//if(!is_vc_activate()){
		$localize['model_position']=$settings['model_position']?$settings['model_position']:'center';
		// If it's not a Kbucket listing page
		if ( !is_kbucket_page_func() ) { 
			//wp_localize_script( 'kbucket-js', 'kbObj', $localize );
			?>
			<script>
	       	  var dynamicLocalizationData = <?php echo json_encode($localize); ?>;
		   </script>
			
			<?php 
			//wp_localize_script('kbucket-custom-js', 'kbObj', $localize );
			$kbContent = ob_get_contents();
			ob_end_clean();
			return $kbContent . $content;
		}



		if ( !empty($url_parts['article']) ) {
			$localize['shareId'] = $url_parts['article'];
		}

		$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
		$subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;
		$cpage = isset($url_parts['page']) ? $url_parts['page'] : 1;

		$localize['categoryName'] = $category;
		$localize['subCategoryName'] = $subcategory;?>
		<script>
		var dynamicLocalizationData = <?php echo json_encode($localize); ?>;
		</script>

		<?php 
		//wp_localize_script( 'kbucket-js', 'kbObj', $localize );
		//wp_localize_script('kbucket-custom-js', 'kbObj', $localize );
		

		$searchtxt = isset( $_REQUEST['srch'] ) ? $_REQUEST['srch'] : '';

		?>




		<div id="kb-wrapper" class="kb-wrapper-kbucket-list">
			<!-- <div class="kb-tags-mobile">
				<span class="kb-toggle">
					<i class="fas fa-bars"></i>
				</span>
			</div>
		 -->
			<?php if(isset($settings['cat_sidebar']) && ((int)$settings['cat_sidebar'] == 2 || (int)$settings['cat_sidebar'] == 0)): ?>
				<div class="kb-right-cont">
					<div id="kb-search">
						<?php if ( 1 == $settings['site_search'] ): ?>
							<form method="GET" action="<?=add_query_arg('search', 'true'); ?>">
								<input type="text" name="srch" value="<?php echo esc_attr( $searchtxt ); ?>" id="kb-search-input" placeholder="<?php _e("Search the kbucket", WPKB_TEXTDOMAIN) ?>" />
								<button type="submit" value="<?php _e("Search", WPKB_TEXTDOMAIN) ?>"><?php _e("Search", WPKB_TEXTDOMAIN) ?></button>
							</form>
						<?php endif;?>
					</div>
				</div>
			<?php endif ?>

			<?php

			$sortBy = ! empty( $_REQUEST['sort'] ) ? esc_html( $_REQUEST['sort'] ) : esc_html( $settings['sortBy'] );

			if( $settings['page_title'] != '' ) echo '<h2><b>'.esc_html( $settings['page_title'] ).'</b></h2>';

			if(!isset($settings['cat_sidebar']) || (int)$settings['cat_sidebar'] == 1) kb_render_header_content();
			if(!isset($settings['cat_sidebar']) || (int)$settings['cat_sidebar'] == 3) kb_render_header_content_dropdown();

			if ( 1 == $settings['site_search'] && isset( $_REQUEST['srch'] ) ):

				include_once(WPKB_PATH.'templates/search.php');

				$kbContent = ob_get_contents();

				ob_end_clean();

				return $kbContent . $content . '</div>';

			endif;
			?>
			<!--<a href="<?php echo add_query_arg(array('kbt' => 'suggest')); ?>" id="kb-suggest" class="kb-suggest" title="suggest content">
				<img src="<?php echo WPKB_PLUGIN_URL; ?>/images/suggest_content.png" alt="Suggest Content">
			</a>-->

			<div class="wrap_sort">
				<div class="kb-sort">
					<button class="kb_mobile_menu_toggle" id="kb_is_menu_toggle">
						<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24"><path d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"/></svg> Menu
					</button>
					<?php $subcat_url = kb_get_current_subcat_url(); ?>
					<a href="<?php echo add_query_arg(array('format' => 'rss', 'xml' => 'true'),$subcat_url->url ?? ""); ?>" id="kb-rss" target="_blank">RSS</a>
					<div class="inner_sort">
						<span>Sort by:</span>
						<a href="<?php echo esc_attr( add_query_arg(array('sort' => 'author','order' => 'asc')) ); ?>" <?php if($sortBy == 'author') echo 'class="active"';?>>Publisher</a>
						<a href="<?php echo esc_attr( add_query_arg(array('sort' => 'add_date','order' => (!empty($_GET['order']) && $_GET['order'] == 'asc' ? 'desc' : 'asc'))) ); ?>" <?php if($sortBy == 'add_date') echo 'class="active"';?>>Date</a>
						<a href="<?php echo esc_attr( add_query_arg(array('sort' => 'title','order' => 'asc')) ); ?>" <?php if($sortBy == 'title') echo 'class="active"';?>>Title</a>
					</div>
				</div>
			</div>
            <div class="kb-description">
				<?php
                    $current_cat = kb_get_current_category();

                    if(!empty($current_cat['name']) && !empty($current_cat['description']))
						echo $current_cat['name'] . ": " . $current_cat['description'];
				?>
            </div>
			<div class="kb-row kb-curated-hub">
				<div id="kb-items-masonary" class="kb-col-12">

					<ul id="kb-items-list" class="kb-style-<?php echo $settings['kb_show_style'] ?? 'masonry'  ?> kb-items-list">
						<?php $kbuckets = kb_get_kbuckets();
						if( count( $kbuckets['kbuckets'] ) > 0 ){
							foreach ( $kbuckets['kbuckets'] as $i => $m ){
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

								$kbucket = get_kbucket_by_id( $m->kbucketId );
								if ( empty( $kbucket->post_id ) ) {
									$shareData = kb_get_kbucket_share_data( $kbucket );
								} else {
									$post = get_post( $kbucket->post_id );
									// Get sharing data
									$shareData = array(
										'url' => $kbucket->url_kbucket,
										'imageUrl' => $kbucket->image_url,
										'title' => $kbucket->title,
										'description' => $kbucket->description,

									);
								}

								$url = WPKB_SITE_URL . '/' . $shareData['url'];
								//echo"<pre>";print_R($kbucket);die();
								kb_includeVar(
									WPKB_PATH.'templates/kb-item.php',
									array(
										'kbucket' => $kbucket,
										'url' => $url,
										'm' => $m,
										'settings' => $settings,
										'share' => $shareData,
										'subcat' => $subcat_url
									)
								);

							}
						}else{
							echo '<div class="kb-list-item"><b>No result found. Please refine your search</b></div>';
						} ?>
					</ul>

					<div class="wrap_pagination">
						<span id="kb-pages-count"><?php	echo 'Page ' . (int) $kbuckets['currentPageI'] . ' of ' . (int) $kbuckets['pagesCount']; ?></span>
						<div id="kb-pagination"><?php render_pagination_links($kbuckets['currentPageI'], $kbuckets['pagesCount']); ?></div>
					</div>

                    <div class="powered_by">
                        <?php
                            echo sprintf(
                                '%s <a href="https://optimalaccess.com/" target="_blank"> %s </a>',
                                esc_html__('Powered by', 'kbucket'),
	                            esc_html__('Optimal Access', 'kbucket')
                            );
                        ?>
                    </div>

				</div>
			</div><!-- /.kb-row -->
		</div><!-- /#kb-wrapper -->
		<?php //$this->self->render_category_disqus(); ?>
		<noscript><?php _e("Please enable JavaScript to view the") ?> <a href="https://disqus.com/?ref_noscript"><?php _e("comments powered by Disqus.") ?></a></noscript>
		<?php
		$kbContent = ob_get_contents();
		ob_end_clean();

		return $kbContent . $content;
	//}else{
	//	return $content;
	//}
}


/**
 * Render share window content
 * Requires POST param kb-share
 * Fired by wp ajax hook: wp_ajax_nopriv_share_content
 */
function kb_action_ajax_share_content(){
	if ( empty( $_GET['kb-share'] ) ) {
		return false;
	}

	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );

	$kid = esc_attr( $_GET['kb-share'] );
	$blog_id = !empty($_GET['blog-id']) ? $_GET['blog-id'] : 1;

	$kbucket = get_kbucket_by_slug( $kid, $blog_id );


	if ( ! $kbucket ) {
		die("Can't find this kbucket!");
	}


	$imageUrl = $kbucket->image_url !== '' ? $kbucket->image_url : false;

	if ( empty( $kbucket->post_id ) ) {
		$shareData = kb_get_kbucket_share_data( $kbucket );
	} else {
		// Get sharing data
		$shareData = array(
			'url' => $kbucket->url_kbucket,
			'imageUrl' => $imageUrl,
			'title' => $kbucket->title,
			'description' => $kbucket->description,
		);
	}

	$url = WPKB_SITE_URL . '/' . $shareData['url'];

	if(empty($shareData['imageUrl'])){
		if(!empty($_GET['kb-category']) && !empty($_GET['kb-sub-category'])){
			$subcat = kb_get_current_subcat_url($_GET['kb-category'], $_GET['kb-sub-category']);
			if(!empty($subcat->image)){
				$shareData['imageUrl'] = $subcat->image;
			}
		}
	}
//die(WPKB_PATH.'templates/kb-share.php');
	kb_includeVar(
		WPKB_PATH.'templates/kb-share.php',
		array(
			'kbucket' => $kbucket,
			'url' => $url,
			'shareData' => $shareData,
			'subcat' => $subcat ?? false
		)
	);
	exit;
}

add_action( 'init', function(){
	add_action( 'wp_ajax_nopriv_share_content', 'kb_action_ajax_share_content' );
	add_action( 'wp_ajax_share_content', 'kb_action_ajax_share_content' );
	add_action( 'template_redirect', 'kb_action_ajax_share_content' );
	add_action( 'template_redirect', 'kb_redirect_from_category_to_sub' );
},100 );


function kb_redirect_from_category_to_sub(){
	$url_parts = kb_parse_url();
	if(empty($_GET['srch']) && !empty($url_parts['parent']) && $url_parts['parent'] == KBUCKET_SLUG && empty($url_parts['subcat'])){
		$subcat = kb_get_current_subcat_url();
		if($subcat && !empty($subcat->id_cat)){
			$subcat_url = $subcat->url;
			wp_redirect( $subcat_url . '?' . $_SERVER['QUERY_STRING'], 301 );
		}
	}
}





function kb_render_page_mobile($kbucket){
	// Get sharing data
	$shareData = kb_get_kbucket_share_data( $kbucket );

	$url = WPKB_SITE_URL . '/' . $shareData['url'];	?>
	<div role="main" class="ui-content">
		<div class="ui-grid-solo">
			<?php if ( ! empty( $shareData['imageUrl'] ) ): ?>
				<div class="share-image-wr">
					<img src="<?php	echo esc_attr( $shareData['imageUrl'] );?>"
						 class="share-image"
						 style="max-width:300px;max-height:300px"/>
				</div>
			<?php endif; ?>
			<h2><a href="<?php echo esc_attr( $url ); ?>" rel="external" data-ajax="false"><?php echo esc_html( $shareData['title'] ); ?></a></h2>
			<span class="kb-item-date"><?php echo esc_html(  $shareData['kbucket']->add_date ); ?> by </span>
			<span style="color:<?php echo esc_attr( $settings['author_color'] ); ?> !important;"><?php echo esc_html( $shareData['kbucket']->author ); ?></span>
			<p>
				<span style="color:<?php echo esc_attr( $settings['content_color'] );?> !important;">
					<?php
					echo wp_kses(
						$kbucket->description,
						array(
							'a' => array('href' => array(),'title' => array()),
							'br' => array(),
						)
					);?>
				</span>
			</p>
		</div>
	</div>
	<?php kb_render_footer_links();
}


function kb_render_footer_links(){ ?>
	<div class="ui-bar-a">
		<p style="text-align:center;">
			<?php $subcat_url = kb_get_current_subcat_url(); ?>
			<a href="<?php echo add_query_arg(array('mobile' => 'n'), $subcat_url->url); ?>" rel="external" data-ajax="false">Switch to full version</a> |
			<a href="#" class="scroll_to_up" rel="external" data-ajax="false">Up</a>
		</p>
	</div>

	<script>
		jQuery(".scroll_to_up").click(function() {
			jQuery("html, body").animate({ scrollTop: 0 }, "slow");
			return false;
		});
	</script>
	<?php
}

function kb_render_footer_mobile($kbuckets){
	?>
	<div id="kb-footer" data-role="footer" style="overflow:hidden;">
		<h4 style="text-align:center;"><?php echo 'Page: ' . (int) $kbuckets['currentPageI'] . ' of ' . (int) $kbuckets['pagesCount']; ?></h4>
		<div data-role="navbar">
			<ul><?php render_pagination_links($kbuckets['currentPageI'],$kbuckets['pagesCount']); ?></ul>
		</div>
	</div>
	<?php
	kb_render_footer_links();
}

function kb_render_head_mobile(){
	?>
	<div id="kb-tags" class="sidebar" data-role="panel" data-position="left" data-display="overlay" data-dismissible="false">
		<div class="ui-corner-all custom-corners">
			<a href="#kb-list" data-rel="close" class="ui-btn">Close</a>

			<div class="ui-bar ui-bar-a"><h3>Tags</h3></div>

			<div class="ui-body ui-body-a">
				<?php
				$categoryTags = kb_get_category_tags();
				if ( !empty( $categoryTags ) ) {
					kb_render_tags_cloud(
						$categoryTags,
						'm',
						'main',
						array( 'value' => kb_get_active_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
						'c-tag'
					);
				}
				?>
			</div>
			<a href="#kb-list" data-rel="close" class="ui-btn">Close</a>
		</div>
	</div>

	<header data-role="header">
		<div id="nav-icon2">
			<span></span>
			<span></span>
			<span></span>
			<span></span>
			<span></span>
			<span></span>
		</div>
	</header>

	<div class="menu">
		<?php $menu = kb_get_menu(); ?>
		<ul>
			<li><a href="<?php echo WPKB_SITE_URL; ?>" data-ajax="false"><?php _e("Home") ?></a></li>

			<?php $subcategories = array(); ?>
			<?php foreach ( $menu['categories'] as $c ): ?>
				<li>
					<a href="<?php echo esc_attr( $c->url ) . kb_get_subcat_by_cat_slug($c->alias); ?>" data-ajax="false"><?php echo esc_html( $c->name ); ?></a>

					<?php if(is_array($c->subcategories)): ?>
						<ul>
							<?php foreach ( $c->subcategories as $subcategoryId ): ?>
								<li>
									<?php $subcategory = $menu['subcategories'][$subcategoryId]; ?>
									<?php echo isset($url_parts['subcat']) && $url_parts['subcat'] == sanitize_title_with_dashes($subcategory->name) ? esc_html( $subcategory->name ) : '<a href="' . esc_attr( $subcategory->url ).'"  rel="external" data-ajax="false">'. esc_html( $subcategory->name ) . '</a>'; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

				</li>
			<?php endforeach; ?>

			<li><a href="#kb-tags">Tags Cloud</a></li>
		</ul>
	</div>

	<script type="text/javascript">
		jQuery('#nav-icon2').click(function(){
			jQuery(this).toggleClass('open');
			jQuery(".menu").slideToggle("slow");
		});
		jQuery( ".menu" ).hide();
	</script>
	<?php
}



function kb_render_content_mobile(){
	?>

	<div role="main" id="kb-items-list" class="ui-content">
		<?php $kbuckets = kb_get_kbuckets(); ?>
		<?php if( count( $kbuckets['kbuckets'] ) > 0 ): ?>
			<ul data-role="listview" data-inset="true" data-theme="a" data-divider-theme="a" class="ui-corner-all">
				<?php foreach ( $kbuckets['kbuckets'] as $m ): ?>
					<li class="ui-corner-all kb-list-item">
						<p><?php echo esc_html( $m->add_date ); ?></p>
						<?php $image_url = trim(kb_get_image_by_ID($m->kbucketId)); ?>
						<div class="wrap_image <?php if(!$image_url) echo 'no_image'; ?>">
							<div class="kbucket_img" style="background-image:url(<?php echo $image_url ?>)"></div>
						</div>
						<h2><a href="<?php echo esc_attr( $m->link ); ?>"><?php echo html_entity_decode(esc_html( $m->title )); ?></a></h2>
						<p><strong><?php echo esc_html( $m->author ); ?></strong></p>

						<p class="kb-wrap">
							<?php if($m->description){
								echo wp_kses(
								 $m->description,
								 array(
									'a' => array(
										'href' => array(),
										'title' => array()
									),
									'br' => array(),
								 )
								);
							}?>
						</p>
						<div data-role="controlgroup" data-type="horizontal" class="ui-mini kb-tag-links">
							<?php kb_render_kbucket_tags( kb_create_url_tag('c-tag',$m->tags), $m->tags, ' ui-btn' ); ?>
							</div>
						</div><!-- /.kb-tag-links -->




						<?php

						$kbucket = get_kbucket_by_id( $m->kbucketId );

						$imageUrl = $kbucket->image_url !== '' ? $kbucket->image_url : false;

						if ( empty( $kbucket->post_id ) ) {
							$shareData = kb_get_kbucket_share_data( $kbucket );
						} else {

							$post = get_post( $kbucket->post_id );

							// Get sharing data
							$shareData = array(
								'url' => $kbucket->url_kbucket,
								'imageUrl' => $kbucket->image_url,
								'title' => $kbucket->title,
								'description' => $kbucket->description,
							);
						}

						$url = WPKB_SITE_URL . '/' . $shareData['url'];


						$description = strip_tags(html_entity_decode(kbucket_strip_cdata($shareData['description'])));
						$description = strlen($description) >= 56 ? substr($description, 0, 56).'...' : $description;

						?>

						<div class="addthis_toolbox_custom addthis_default_style" addthis:url="<?php echo $url.'/?share=true' ?>" addthis:title="<?php echo trim($shareData['title']) ?>" addthis:description="<?php echo $description ?>">
							<div class="wrap_addthis_share">
								<div style="width:33.333%;">
									<div class="fb-share-button" data-action="like" data-show-faces="true" data-href="<?php echo $url.'/?share=true' ?>" data-layout="button_count"></div>
								</div>
								<div style="width:33.333%;">
									<a class="addthis_button_tweet"></a>
								</div>
								<div style="width:33.333%;"><a class="addthis_button_linkedin_counter" li:counter="none"></a></div>

							</div>

							<div class="extended_addthis_share">
								<div style="width:33.333%;">
									<a class="addthis_button_pinterest_pinit"></a>
								</div>
								<div style="width:33.333%;"><a class="addthis_button_stumbleupon_badge" su:badge:style="3" su:badge:url="<?php echo $url ?>" style="background-image: url('<?php echo WPKB_PLUGIN_URL; ?>/images/stumble.png')"></a></div>
								<div style="width:33.333%;"><a class="addthis_button_email"><img src="<?php echo WPKB_PLUGIN_URL; ?>/images/email-share.png" alt="mail"></a></div>
							</div>
						</div>


						<div id="fb-root"></div>

						<style>
							.fb_iframe_widget span,
							iframe.fb_iframe_widget_lift,
							.fb_iframe_widget iframe {
							    width:95px !important;
							    height:20px !important;
							    position:relative;
							}
						</style>

						<script>
							jQuery(document).ready(function($) {
								<?php if($kbucket->twitter): ?>
									if(window.addthis_share !== undefined)
										window.addthis_share.passthrough = {twitter: {via: '<?=str_replace('@', '', $kbucket->twitter);?>'}};
								<?php endif ?>
								addthis.toolbox('.addthis_toolbox_custom');
							});
						</script>


					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php kb_render_footer_mobile($kbuckets);?>
	<script type="text/javascript">
		addthis.toolbox('.addthis_toolbox_custom');

		(function(d, s, id) {
			var js, fjs = d.getElementsByTagName(s)[0];
			if (d.getElementById(id)) return;
			js = d.createElement(s); js.id = id;
			js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
			fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));

		jQuery(document).ready(function ($kbj) {

			$kbj('#kb-main-menu a').attr('data-ajax', 'false');
			if (typeof DISQUS !== 'undefined') {
				DISQUS.reset({
					reload: true,
					config: function () {
						this.page.identifier = disqus_identifier;
						this.page.url = disqus_url;
					}
				});
			}
		});
	</script><?php
}

function kb_get_image_by_ID($kb_id){
	global $wpdb;
	if(empty($kb_id)) return false;
	$query = $wpdb->prepare("SELECT image_url FROM {$wpdb->prefix}kbucket WHERE id_kbucket=%s", $kb_id);
	$image = $wpdb->get_col( $query );
	if(isset($image[0])) return $image[0];
	else return false;
}

/**
 * Render Meta tags for share scripts
 * @param $imageUrl
 * @param $title
 * @param $description
 */
function kb_render_meta_tags( $imageUrl, $title, $description, $via = null ){

	$imageUrl = esc_url( $imageUrl );
	$title = trim(esc_attr( $title ));
	$url = esc_url( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	
	$url = esc_url_raw(sanitize_text_field($url ));
	
	$description = html_entity_decode(strip_tags(esc_html($description)));
	
	if(strlen($description) >= 107){
		$description = substr($description, 0, 107).'...';
	}
	if(empty($imageUrl)) {
		$subcat = kb_get_current_subcat_url();
		if(!empty($subcat->image)){
			$imageUrl = $subcat->image;
		}
	}
	//die($description);
	?>
	<?php echo "\n\n<!-- Meta keys from Kbucket Plugin -->\n" ?>
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
	<meta property="og:url" content="<?php echo $url; ?>">
	<meta property="og:type" content="website" />
	<meta property="og:title" name="title" content="<?php echo $title; ?>"/>
	<meta name="bitly-verification" content="2809eb754836"/>


	<meta prefix="og: http://ogp.me/ns#" property="og:title" content="<?php echo $title; ?>" />
	<meta prefix="og: http://ogp.me/ns#" property="og:type" content="website" />
	<meta prefix="og: http://ogp.me/ns#" property="og:url" content="<?php echo $url ?>" />

	<?php if ( ! empty( $imageUrl ) ): ?>
	<link rel="image_src" href="<?php echo $imageUrl; ?>" />
	<meta property="og:image" name="image" content="<?php echo $imageUrl; ?>" />
	<meta property="og:image:width" content="0" class="kb-seo-meta-tag" />
	<meta property="og:image:height" content="0" class="kb-seo-meta-tag" />
	<meta property="og:image:type" content="image/png" class="kb-seo-meta-tag" />

	<meta name="twitter:card" content="photo" />
	<meta name="twitter:title" content="<?php echo $title; ?>" />
	<meta name="twitter:image" content="<?php echo $imageUrl; ?>" />
	<meta name="twitter:image:src" content="<?php echo $imageUrl; ?>" />

	<meta prefix="og: http://ogp.me/ns#" property="og:image" content="<?php echo $imageUrl; ?>" />
	<?php endif;

	if ( ! empty( $description ) ):
		$description = wp_strip_all_tags($description);;
	?>
	<meta name="description" content="<?php echo $description; ?>" />
	<meta property="og:description" content="<?php echo $description; ?>" />
	<meta name="twitter:description" content="<?php echo $description; ?>" />
	<?php
	endif;

	?>
	<?php echo "\n<!-- End Meta keys from Kbucket Plugin -->\n\n" ?>
	<?php
}


function kb_includeVar($fileName, $variablesArray) {
   extract($variablesArray);
   include($fileName);
}


function kb_get_kbucket_share_data( $m ){
	// Exit if kbucket record was not found
	if ( empty( $m ) ) {
		return false;
	}

	$share = array( 'kbucket' => $m );

	$share['url'] = $m->url_kbucket;
	$share['imageUrl'] = $m->image_url;
	$share['title'] = $m->title;
	$share['description'] = $m->description;

	return $share;
}


/**
 * Render head content
 * Fired by wp_head filter hook
 * @param $content string
 */
function kb_filter_wp_head( $content ) {
	$current_kb = kb_get_current_kbucket();
	$settings = kb_get_settings();
	// If Kbucket id is set,it's a link to share box.
	if ( $current_kb ) {
		$shareData = kb_get_kbucket_share_data( $current_kb );

		 // Set the metatags for Facebook Open Graph and Linkedin callback request
		kb_render_meta_tags(
			$shareData['imageUrl'],
			$shareData['title'],
			$shareData['description'],
			$shareData['kbucket']->twitter
		);

	} else {
		$subcategory = kb_get_subcategories_like_alias(kb_get_current_subcat());
		$imageUrl = !empty($subcategory[0]->image) ? $subcategory[0]->image : '';
		$category_name = !empty($subcategory[0]->subcat) ? $subcategory[0]->subcat : '';
		$category_description = !empty($subcategory[0]->description) ? $subcategory[0]->description : '';
		// Set the metatags for Facebook Open Graph and Linkedin callback request
		kb_render_meta_tags( $imageUrl, $category_name, $category_description );
		?><!-- /Generated by Kbucket --><?php
	}

	$content .= '<script type="text/javascript">';
		if( 1 == $settings['site_search'] ){
			$content .= "var kbSearch = '',";
			$content .= "permalink = '".esc_js(get_permalink())."',";
			$content .= "searchRes = '".(!empty( $_REQUEST['srch'] ) ? esc_js( $_REQUEST['srch'] ) : '')."';";
		}
	$content .= '</script>';
	return $content;
}

add_action('template_redirect', function(){
	if(is_kbucket_page_func() || !empty($_REQUEST['article'])){
		add_filter( 'wp_head', 'kb_filter_wp_head', 1 );
	}
}, 1 );



function kb_create_search_condition( $phrase, $cols, $operands ){
	$words = explode( ' ', $phrase );
	$conditionsArr = array();
	$values = array();
	$likeConditions = array();

	foreach ( $cols as $col ) {
		foreach ( $words as $word ) {
			if ( $word == '' ) {
				continue;
			}
			foreach ( $operands as $operand ) {
				$conditionsArr[] = "`$col`$operand" . ( $operand == 'LIKE' ? " '%%%s%%'" : '%s' );
				$values[] = $word;
			}
		}
		$likeConditions = implode( ' OR ', $conditionsArr );
	}

	return array( 'conditions' => $likeConditions, 'values' => $values );
}


function kb_get_search_item($conditions, $values){
	global $wpdb;
	$sql = "
		SELECT
			a.ID,
			user_nicename,
			DATE( post_date ) AS dt,
			post_content,
			post_title
		FROM {$wpdb->prefix}posts a
		INNER JOIN {$wpdb->prefix}users b ON a.post_author = b.ID
		WHERE ( $conditions )
			AND post_type IN ( 'post' )
			AND post_status = 'publish'
		LIMIT 100";

	$query = $wpdb->prepare( $sql, $values );

	return $wpdb->get_results( $query );
}



/**
 * Render header content
 */
function kb_render_header_content() {
	$file_param = kb_get_file_param();
	if(empty($file_param)) return;
	$searchtxt = isset( $_REQUEST['srch'] ) ? $_REQUEST['srch'] : '';

	$url_parts = kb_parse_url();

	$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
	$subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;

	$settings = kb_get_settings();

	 ?>
	<div id="kb-header" itemscope itemtype="http://schema.org/WebPage">

		<meta itemprop="about" content="<?=$file_param[0]['name']; ?>" />
		<meta itemprop="comment" content="<?=$file_param[0]['comment']; ?>" />
		<meta itemprop="encoding" content="<?=$file_param[0]['encoding']; ?>" />
		<meta itemprop="publisher" content="<?=$file_param[0]['publisher']; ?>" />
		<meta itemprop="author" content="<?=$file_param[0]['author']; ?>" />
		<meta itemprop="keywords" content="<?=$file_param[0]['keywords']; ?>" />

		<div id="kb-menu" itemscope itemtype="http://schema.org/WebPageElement">
			<div class="kb-right-cont">
				<div id="kb-search">
					<?php if ( 1 == $settings['site_search'] ): ?>
						<form method="GET" action="<?=add_query_arg('search', 'true'); ?>">
							<input type="text" name="srch" value="<?php echo esc_attr( $searchtxt ); ?>" id="kb-search-input" placeholder="<?php _e("Search the kbucket", WPKB_TEXTDOMAIN) ?>" />
							<button type="submit" value="<?php _e("Search", WPKB_TEXTDOMAIN) ?>"><?php _e("Search", WPKB_TEXTDOMAIN) ?></button>
						</form>
					<?php endif;?>
				</div>
			</div>
			<ul>
				<?php
                    $subcategories = array();
                    $menu = kb_get_menu();
                    $count = 0;
                    $class = '';


                    foreach ( $menu['categories'] as $c ) :
	                    $class = $category === sanitize_title_with_dashes($c->name) ? ' class="kb-menu-active"' : '';
	                    if( ! $category && empty( $class ) && ! $count) {
	                        $class = ' class="kb-menu-active"';
	                    }
                ?>
					<li <?php echo $class ?>>
						<a href="<?php echo esc_attr( $c->url ) . kb_get_subcat_by_cat_slug($c->alias) ?>" <?php echo $class ?>><?php echo esc_html( $c->name ); ?></a>
						<?php if( ! empty( $class ) ) : $subcategories = $c->subcategories; ?>
							<meta itemprop="about" content="<?=$c->name ?>" />
							<meta itemprop="comment" content="<?=$c->description ?>" />
							<meta itemprop="encoding" content="<?=$c->id_cat ?>" />
							<meta itemprop="keywords" content="" />
						<?php endif; ?>
					</li>
					<?php $count++; ?>
				<?php endforeach ?>
			</ul>
		</div>
		<div id="kb-submenu" itemscope itemtype="http://schema.org/WebPageElement">
			<?php $count = 0; ?>
			<ul>
				<?php foreach ( $subcategories as $subkey => $subcat ): ?>
					<?php $class = $subcategory === sanitize_title_with_dashes($menu['subcategories'][$subcat]->name) ? ' class="kb-menu-active"' : ''; ?>
					<?php if(!$subcategory && !$count) $class = ' class="kb-menu-active"'; ?>
					<li <?php echo $class ?>>
						<?php if(!empty($class)): ?>
							<meta itemprop="about" content="<?=$menu['subcategories'][$subcat]->name ?>" />
							<meta itemprop="comment" content="<?=$menu['subcategories'][$subcat]->description ?>" />
							<meta itemprop="encoding" content="<?=$menu['subcategories'][$subcat]->id_cat ?>" />
							<meta itemprop="keywords" content="" />
						<?php endif; ?>
						<a href="<?php echo esc_attr($menu['subcategories'][$subcat]->url); ?>"<?php echo $class; ?>><?php echo esc_html($menu['subcategories'][$subcat]->name); ?></a>
					</li>
					<?php $count++; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php
}

/**
 * Render header content dropdown
 */
function kb_render_header_content_dropdown() {
	$file_param = kb_get_file_param();
	if(empty($file_param)) return;
	$searchtxt = isset( $_REQUEST['srch'] ) ? $_REQUEST['srch'] : '';

	$url_parts = kb_parse_url();

	$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
	$subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;

	$settings = kb_get_settings();

?>
    <div id="kb-header" itemscope itemtype="http://schema.org/WebPage">

        <meta itemprop="about" content="<?php echo esc_attr($file_param[0]['name']); ?>" />
        <meta itemprop="comment" content="<?php echo esc_attr($file_param[0]['comment']); ?>" />
        <meta itemprop="encoding" content="<?php echo esc_attr($file_param[0]['encoding']); ?>" />
        <meta itemprop="publisher" content="<?php echo esc_attr($file_param[0]['publisher']); ?>" />
        <meta itemprop="author" content="<?php echo esc_attr($file_param[0]['author']); ?>" />
        <meta itemprop="keywords" content="<?php echo esc_attr($file_param[0]['keywords']); ?>" />

        <div id="kb-menu-dropdown" itemscope itemtype="http://schema.org/WebPageElement">
            <ul class="kb-menu-top">
		        <?php
                    $menu = kb_get_menu();
                    $count = 0;
                    $class = '';

		            foreach ( $menu['categories'] as $c ) :
                        $class = $category === sanitize_title_with_dashes($c->name) ? ' class="kb-menu-active"' : '';
                        if( ! $category && empty( $class ) && ! $count) {
                            $class = ' class="kb-menu-active"';
                        }
			    ?>
                    <li <?php echo $class ?>>
                        <a href="<?php echo esc_attr( $c->url ) . kb_get_subcat_by_cat_slug($c->alias) ?>" <?php echo $class ?>>
					        <?php echo esc_html( $c->name ); ?>
                        </a>

	                    <?php if( $category === sanitize_title_with_dashes($c->name) ) : ?>
                            <meta itemprop="about" content="<?=$c->name ?>" />
                            <meta itemprop="comment" content="<?=$c->description ?>" />
                            <meta itemprop="encoding" content="<?=$c->id_cat ?>" />
                            <meta itemprop="keywords" content="" />
	                    <?php endif; ?>

			            <?php if( count( (array) $c->subcategories ) ) : ?>
                            <ul class="kb-menu-dropdown">
                                <?php
                                    $count = 0;
                                    foreach ( $c->subcategories as $subcat ):
	                                    $active = $subcategory === sanitize_title_with_dashes($menu['subcategories'][$subcat]->name) ? true : false;
                                        $class = $active ? 'class="kb-menu-active"' : '';
                                ?>
                                    <li <?php echo $class; ?>>
                                        <?php if( ! empty( $active ) ) : ?>
                                            <meta itemprop="about" content="<?php echo $menu['subcategories'][$subcat]->name ?>" />
                                            <meta itemprop="comment" content="<?php echo $menu['subcategories'][$subcat]->description ?>" />
                                            <meta itemprop="encoding" content="<?php echo $menu['subcategories'][$subcat]->id_cat ?>" />
                                            <meta itemprop="keywords" content="" />
                                        <?php endif; ?>
                                        <a href="<?php echo esc_attr($menu['subcategories'][$subcat]->url); ?>">
                                            <?php echo esc_html($menu['subcategories'][$subcat]->name); ?>
                                        </a>
                                    </li>
                                    <?php $count++; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
			        <?php $count++; ?>
		        <?php endforeach ?>
            </ul>
            <div class="kb-right-cont">
                <div id="kb-search">
					<?php if ( 1 == $settings['site_search'] ) : ?>
                        <form method="GET" action="<?=add_query_arg('search', 'true'); ?>">
                            <input type="text" name="srch" value="<?php echo esc_attr( $searchtxt ); ?>" id="kb-search-input" placeholder="<?php _e("Search the kbucket", WPKB_TEXTDOMAIN) ?>" />
                            <button type="submit" value="<?php _e("Search", WPKB_TEXTDOMAIN) ?>"><?php _e("Search", WPKB_TEXTDOMAIN) ?></button>
                        </form>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<!--    <script type="text/javascript">-->
<!--        jQuery('#kb-menu-dropdown .kb-menu-top > li > a').on('click', function(event) {-->
<!--            event.preventDefault();-->
<!--            let parent = jQuery(this).parent();-->
<!---->
<!--            parent.siblings().removeClass('show');-->
<!--            parent.addClass('show');-->
<!--        });-->
<!--    </script>-->
<?php
}



function kb_render_sidebar_navigation($return = false){
	if($return) ob_start();
	?>
	<div class="wrap_category_nav">
		<div class="category_nav_inner bottom-margin">
			<?php
			$file_param = kb_get_file_param();
			$searchtxt = isset( $_REQUEST['srch'] ) ? $_REQUEST['srch'] : '';

			$url_parts = kb_parse_url();

			$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
			$subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;

			$settings = kb_get_settings();

			?>
			<div id="kb-header-sidebar" itemscope itemtype="http://schema.org/WebPage">

				<meta itemprop="about" content="<?=$file_param[0]['name']; ?>" />
				<meta itemprop="comment" content="<?=$file_param[0]['comment']; ?>" />
				<meta itemprop="encoding" content="<?=$file_param[0]['encoding']; ?>" />
				<meta itemprop="publisher" content="<?=$file_param[0]['publisher']; ?>" />
				<meta itemprop="author" content="<?=$file_param[0]['author']; ?>" />
				<meta itemprop="keywords" content="<?=$file_param[0]['keywords']; ?>" />

				<div id="kb-search" class="clearfix">
					<?php if ( 1 == $settings['site_search'] ): ?>
						<form method="GET" action="<?=add_query_arg('search', 'true'); ?>">
							<input type="text" name="srch" value="<?php echo esc_attr( $searchtxt ); ?>" id="kb-search-input" placeholder="<?php _e("Search the kbucket", WPKB_TEXTDOMAIN) ?>" />
							<button type="submit" value="<?php _e("Search", WPKB_TEXTDOMAIN) ?>"><span></span></button>
						</form>
					<?php endif;?>
				</div>

				<div id="kb-menu-sidebar" itemscope itemtype="http://schema.org/WebPageElement">
					<?php
						$subcategories = array();
						$menu = kb_get_menu();
						$count = 0;

						$class = '';
					?>
					<?php if(count($menu['categories'])): ?>
						<ul>
							<?php
							foreach ( $menu['categories'] as $c ):?>
								<li>
									<?php if(!empty($c->name)): ?>
										<h4 data-href="<?php echo esc_attr( $c->url ).kb_get_subcat_by_cat_slug($c->alias) ?>">
											<?php echo esc_html( $c->name ); ?>
										</h4>
									<?php endif; ?>

									<?php if($category === sanitize_title_with_dashes($c->name)): $subcategories = $c->subcategories; ?>
										<meta itemprop="about" content="<?=$c->name ?>" />
										<meta itemprop="comment" content="<?=$c->description ?>" />
										<meta itemprop="encoding" content="<?=$c->id_cat ?>" />
										<meta itemprop="keywords" content="" />
									<?php endif; ?>

									<?php if(count((array)$c->subcategories)): ?>
										<div itemscope itemtype="http://schema.org/WebPageElement">
											<ul>
												<?php $count = 0; ?>
												<?php foreach ($c->subcategories as $subkey => $subcat): ?>
													<?php $active = $subcategory === sanitize_title_with_dashes($menu['subcategories'][$subcat]->name) ? true : false; ?>
													<li>
														<?php if(!empty($active)): ?>
															<meta itemprop="about" content="<?=$menu['subcategories'][$subcat]->name ?>" />
															<meta itemprop="comment" content="<?=$menu['subcategories'][$subcat]->description ?>" />
															<meta itemprop="encoding" content="<?=$menu['subcategories'][$subcat]->id_cat ?>" />
															<meta itemprop="keywords" content="" />
														<?php endif; ?>

														<?php if(!empty($menu['subcategories'][$subcat]->name)): ?>
															<label data-href="<?php echo esc_attr($menu['subcategories'][$subcat]->url); ?>">
																<input type="radio" name="cat_id" value="<?=$c->id_cat ?>" <?php if($subcategory === sanitize_title_with_dashes($menu['subcategories'][$subcat]->name)) echo 'checked' ?>>
																<span><?php echo esc_html($menu['subcategories'][$subcat]->name); ?></span>
															</label>
														<?php endif; ?>

													</li>
													<?php $count++; ?>
												<?php endforeach; ?>
											</ul>
										</div>
									<?php endif; ?>


								</li>
								<?php $count++; ?>
							<?php endforeach ?>
						</ul>
					<?php endif; ?>
				</div>

			</div><!-- /#kb-header-sidebar -->



		</div>

		<script type="text/javascript">
		jQuery('#kb-menu-sidebar input[type=radio]').on('click', function(event) {
			event.preventDefault();
			var href = jQuery(this).parents('label').data('href');
			//jQuery('body').append('<div class="loader"/>');
			//jQuery('.loader').css('height', jQuery(window).innerHeight());
			window.location = href;
		});

		



		function copyingMyfunction(elements) {
			//console.log($("#".elements+"_title").text());
			var elementsToCopy = [

			 // $("#"+elements+"_title"), 
			 
			  $("#"+elements+"_publisher_date"),
			  $("#"+elements+"_author_name"),
			  $("#kb-share-item-"+elements),
			  $("#"+elements+"_descriptions"), 
			//  $("#"+elements+"_publisher_name"), 
			
			
			   
			];
		    		//console.log($("#F77B33AE65566BF4CBDE6F08DFA0CC9A_title").text())
			copyMultipleToClipboard(elementsToCopy,elements);
		 }

		 function copyMultipleToClipboard(elements,id) {
		  var concatenatedText = "";

		  // Ensure elements is a valid jQuery object
		  var $elements = $(elements);
		 

		  $elements.each(function () {
		   // concatenatedText += $(this).text(); 
		    concatenatedText += $(this).text().replace(/\s+/g, ' '); // Remove extra spaces
		    // Add a newline between each element's text
		  });
          concatenatedText +=  $("#kb-share-item-"+id).attr("href");
		  var $temp = $("<textarea>");
		  $("body").append($temp);
		  $temp.val(concatenatedText.trim()).select();
		  document.execCommand("copy");
		  $temp.remove();
		  
		}
		</script>
	</div>
	<?php

	if($return){
		return ob_get_clean();
	}
}
