<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>

<li id="kb-item-<?php echo esc_attr($m->kbucketId); ?>" class="kb-item kb-col-4 kb-item-render">
	
	<div class="grid-sizer"></div>
	<?php
	// echo "<pre>";
	// print_R($kbucket);
	// die();
	$imageUrl = isset($kbucket->image_url) && $kbucket->image_url !== '' ?
		wp_get_attachment_image_src($kbucket->post_id, 'kb_thumb_4-3')[0] :
		'';
	$link = $kbucket->link;

	if (!empty($kbucket->image_url) && empty($imageUrl)) $imageUrl = $kbucket->image_url;
	$image_exist = pathinfo($imageUrl);

	global $wp;
	$current_url = add_query_arg($wp->query_string, '', home_url($wp->request));
	?>
    <a class="copy_button" onclick="copyingMyfunction('<?php echo $m->kbucketId; ?>');">
		<!-- https://feathericons.dev/?search=align-justify&iconset=feather -->
		<!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
		  <line x1="21" x2="3" y1="10" y2="10" />
		  <line x1="21" x2="3" y1="6" y2="6" />
		  <line x1="21" x2="3" y1="14" y2="14" />
		  <line x1="21" x2="3" y1="18" y2="18" />
		</svg> -->
		<img src="<?php echo WPKB_PLUGIN_URL .'/images/'?>citation_logo.png">
	</a>
	<div data-id="<?= $m->kbucketId ?>" data-thumb-id="<?php echo !empty($kbucket->post_id) ? $kbucket->post_id : '' ?>" class="kb-item-inner-wrap" itemscope itemtype="http://schema.org/Article">
		<meta itemscope itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="<?php echo esc_attr($m->link); ?>" />

		<div class="__img-wrap" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
			<?php if (!empty($image_exist['filename']) && !empty($image_exist['basename'])) : ?>
				<img src="<?php echo $imageUrl ?>" alt="<?php echo $m->title ?>" class="__img-source-kbucket">
				<meta itemprop="url" content="<?php echo $imageUrl ?>" />
				<meta itemprop="width" content="500" />
				<meta itemprop="height" content="500" />
			<?php else : ?>
				<?php if (!empty($subcat->image)) : ?>
					<div class="image_wrap" style="background-image:url('<?php echo $subcat->image ?>')"></div>
					<meta itemprop="url" content="<?php echo $subcat->image ?>" />
					<meta itemprop="width" content="500" />
					<meta itemprop="height" content="500" />
				<?php else : ?>
					<div class="image_wrap no_image"></div>
					<meta itemprop="url" content="http://placehold.it/150x150" />
					<meta itemprop="width" content="500" />
					<meta itemprop="height" content="500" />
				<?php endif ?>
			<?php endif; ?>
			<?php if (is_user_logged_in()) : ?>
				<a href="#" class="open-gallery" data-id="<?= $m->kbucketId ?>">
					<img src="<?php echo WPKB_PLUGIN_URL . '/images/camera.png'; ?>" alt="camera.png">
				</a>
			<?php endif; ?>
		</div>

		<div class="body_wrap">
			<div class="meta_tags">
				<span class="kb-item-author">
					<span class="kb-item-author-name" title="<?php echo esc_html($m->publisher); ?>" itemprop="publisher" itemscope itemtype="https://schema.org/Organization">
						<?php _e("Publisher:", WPKB_TEXTDOMAIN) ?> 
						<span itemprop="name"  id="<?php echo $m->kbucketId;?>_publisher_name" > <?php if (!empty($m->publisher)) echo esc_html($m->publisher);
																							else echo '<i style="display:none">No Publisher</i>' ?>
																								
																							</span>
						<span itemprop="logo" itemscope itemtype="https://schema.org/ImageObject"><img itemprop="url" src="/" style="display:none;" /></span>
					</span><br>
					<span class="kb-item-author-name" title="<?php echo esc_html($m->author); ?>" itemprop="author" itemscope itemtype="https://schema.org/Person">
						<?php _e("Author:", WPKB_TEXTDOMAIN) ?> <span itemprop="name" id="<?php echo $m->kbucketId;?>_author_name"  ><?php if (!empty($m->author)) echo esc_html($m->author);
																						else echo '<i style="display:none">No Author</i>' ?></span>
					</span>
				</span>
				<?php if ($settings['dtd']) : ?>
					<span class="kb-item-date" id="<?php echo $m->kbucketId;?>_publisher_date" itemprop="dateModified">
						<?php echo date("Y-m-d", strtotime($m->add_date)); ?>
					</span>
					<meta itemprop="datePublished" content="<?php echo date("Y-m-d", strtotime($m->add_date)); ?>" />
				<?php endif ?>
			</div>

			<h3 itemprop="headline" >
				<?php $title = html_entity_decode($m->title); ?>
				<a href="<?php echo $url ?>" data-slug="<?= $m->short_url ?>" id="kb-share-item-<?php echo esc_attr($m->kbucketId); ?>" class="kb-read-more-link kb-link" title="<?php echo esc_attr($m->title); ?>">
					<?= (strlen($title) >= 107 ? esc_html(substr($title, 0, 107) . '...') : $title); ?>
				</a>

			</h3>
			<?php
			$description = html_entity_decode(kbucket_strip_cdata($share['description']));
			$description = strip_tags($description);
			$description = strlen($description) > 250 ? substr($description, 0, 250) . "..." : $description;
			?>
			<div class="body_text" itemprop="description">
				<div class="" style="display:none;" id="<?php echo $m->kbucketId;?>_descriptions">
					<?php echo substr($description, 0, 500); ?>
			    </div>
			<div class="show_in_mobile">
				<?php echo $description ?>
				<?php if ($share) : ?>
					<div style="text-align: center;margin-bottom: 15px">
						<a   href="<?php echo $url ?>" data-slug="<?= $m->short_url ?>" id="kb-share-item-<?php echo esc_attr($m->kbucketId); ?>" class="kb-read-more-link link-link-link" title="<?php echo esc_attr($m->title); ?>" rel="facebox[.bolder]">
							<?php if (strpos($link, 'youtube') !== false || strpos($link, 'youtu.be') !== false) { ?>
								<?php _e("Watch Now", WPKB_TEXTDOMAIN) ?>
							<?php } else { ?>
								<?php _e("Read More", WPKB_TEXTDOMAIN) ?>

							<?php } ?>
							<?php if (false) : ?>
								<img src="<?php echo WPKB_PLUGIN_URL . '/images/kshare.png'; ?>" alt="<?php _e("Share Button", WPKB_TEXTDOMAIN) ?>" />
							<?php endif ?>
						</a>
					</div>


					<?php if (false) : ?>
						<a href="#" class="stm-share" title="<?php echo esc_attr($m->title); ?>" download>
							<img src="<?php echo WPKB_PLUGIN_URL . '/images/kshare.png'; ?>" alt="<?php _e("Share Button", WPKB_TEXTDOMAIN) ?>" />
						</a>
						<div class="stm-a2a-popup">
							<div class="addtoany_shortcode">
								<div class="addthis_toolbox_custom addthis_default_style" addthis:url="<?php echo $url . '/?share=true' ?>" addthis:title="<?php echo esc_attr($m->title); ?>" addthis:description="<?php echo html_entity_decode(kbucket_strip_cdata($share['description'])); ?>">
									<div class="wrap_addthis_share">
										<div style="width:40%;">
											<div class="fb-share-button" data-action="like" data-show-faces="true" data-href="<?php echo $url . '/?share=true' ?>" data-layout="button_count"></div>
										</div>
										<div style="width:30%;">
											<a class="addthis_button_tweet"></a>
										</div>
										<div style="width:30%;">
											<a class="addthis_button_linkedin_counter" li:counter="none"></a>
										</div>
									</div>

									<div class="extended_addthis_share">
										<div style="width:33.333%;">
											<a data-pin-do="buttonPin" href="https://www.pinterest.com/pin/create/button/?url=<?php echo $url . '/?share=true' ?>&media=<?php echo $imageUrl ?>&description=<?php echo $description ?>" class="pin-it-button" count-layout="none">
												<img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" alt="Pin it" /></a>
										</div>
										<div style="width:33.333%;">
											<a class="addthis_button_stumbleupon_badge" su:badge:style="3" su:badge:url="<?php echo $url ?>" style="background-image: url('<?php echo WPKB_PLUGIN_URL; ?>/images/stumble.png')">
												<img src="<?php echo WPKB_PLUGIN_URL; ?>/images/stumble.png" alt="stumble">
											</a>
										</div>
										<div style="width:33.333%;">
											<a class="addthis_button_email">
												<img src="<?php echo WPKB_PLUGIN_URL; ?>/images/email-share.png" alt="mail"></a>
										</div>
									</div>
								</div>

							</div>
						</div>
					<?php endif ?>
				<?php endif ?>
			</div>
			<div class="hide_in_mobile stm-shareble">
				<?php echo $description ?>
				<?php if ($share) : ?>
					<div style="text-align: center;margin-bottom: 15px">
						<a href="<?php echo $url ?>" data-slug="<?= $m->short_url ?>" id="kb-share-item-<?php echo esc_attr($m->kbucketId); ?>" class="kb-read-more-link link-link-link" title="<?php echo esc_attr($m->title); ?>" >
							<?php if (strpos($link, 'youtube') !== false || strpos($link, 'youtu.be') !== false) { ?>
								<?php _e("Watch Now", WPKB_TEXTDOMAIN) ?>
							<?php } else { ?>
								<?php _e("Read More", WPKB_TEXTDOMAIN) ?>

							<?php } ?>
							<?php if (false) : ?>
								<img src="<?php echo WPKB_PLUGIN_URL . '/images/kshare.png'; ?>" alt="<?php _e("Share Button", WPKB_TEXTDOMAIN) ?>" />
							<?php endif ?>
						</a>
					</div>


					<?php if (false) : ?>
						<a href="#" class="stm-share" title="<?php echo esc_attr($m->title); ?>" download>
							<img src="<?php echo WPKB_PLUGIN_URL . '/images/kshare.png'; ?>" alt="<?php _e("Share Button", WPKB_TEXTDOMAIN) ?>" />
						</a>
						<div class="stm-a2a-popup">
							<div class="addtoany_shortcode">
								<div class="addthis_toolbox_custom addthis_default_style" addthis:url="<?php echo $url . '/?share=true' ?>" addthis:title="<?php echo esc_attr($m->title); ?>" addthis:description="<?php echo html_entity_decode(kbucket_strip_cdata($share['description'])); ?>">
									<div class="wrap_addthis_share">
										<div style="width:40%;">
											<div class="fb-share-button" data-action="like" data-show-faces="true" data-href="<?php echo $url . '/?share=true' ?>" data-layout="button_count"></div>
										</div>
										<div style="width:30%;">
											<a class="addthis_button_tweet"></a>
										</div>
										<div style="width:30%;">
											<a class="addthis_button_linkedin_counter" li:counter="none"></a>
										</div>
									</div>

									<div class="extended_addthis_share">
										<div style="width:33.333%;">
											<a data-pin-do="buttonPin" href="https://www.pinterest.com/pin/create/button/?url=<?php echo $url . '/?share=true' ?>&media=<?php echo $imageUrl ?>&description=<?php echo $description ?>" class="pin-it-button" count-layout="none">
												<img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" alt="Pin it" /></a>
										</div>
										<div style="width:33.333%;">
											<a class="addthis_button_stumbleupon_badge" su:badge:style="3" su:badge:url="<?php echo $url ?>" style="background-image: url('<?php echo WPKB_PLUGIN_URL; ?>/images/stumble.png')">
												<img src="<?php echo WPKB_PLUGIN_URL; ?>/images/stumble.png" alt="stumble">
											</a>
										</div>
										<div style="width:33.333%;">
											<a class="addthis_button_email">
												<img src="<?php echo WPKB_PLUGIN_URL; ?>/images/email-share.png" alt="mail"></a>
										</div>
									</div>
								</div>

							</div>
						</div>
					<?php endif ?>
				<?php endif ?>
			</div>
		</div>
			<p class="kb-item-tags blue" style="margin-bottom:2px;">
				<span><?php _e("Tags:", WPKB_TEXTDOMAIN) ?></span>
				<span itemprop="keywords"><?php kb_render_kbucket_tags($current_url, $m->tags); ?></span>
			</p>

			<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" style="display: none !important;">
				<span itemprop="ratingValue">5</span>
				<span itemprop="reviewCount">1</span>
			</div>

			<div id="fb-root"></div>

		</div>
		<?php if (false) : ?>
			<div class="kb-footer">
				<div class="addthis_toolbox addthis_default_style">
					<table class="extended_addthis_share">
						<tr>
							<td colspan="4">
								<div class="btn-wrap">
									<a rel="nofollow,noindex" href="#" data-clipboard-text="<?php echo $url ?>" class="btn btn-danger copy_clipboard">
										<?php _e("Get URL", WPKB_TEXTDOMAIN) ?>
										<span class="kb_popover"><?php _e("URL copied to clipboard", WPKB_TEXTDOMAIN) ?></span>
									</a>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
		<?php endif ?>


	</div><!-- /.kb-item-inner-wrap -->
</li>