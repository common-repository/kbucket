<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<div class="kb-wrapper share_modal_wrapper hash-kbucket">
	<a class="copy_button"  onclick="copyingMyfunctionPOP();">
		<!-- https://feathericons.dev/?search=align-justify&iconset=feather -->
		<!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="main-grid-item-icon" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
		  <line x1="21" x2="3" y1="10" y2="10" />
		  <line x1="21" x2="3" y1="6" y2="6" />
		  <line x1="21" x2="3" y1="14" y2="14" />
		  <line x1="21" x2="3" y1="18" y2="18" />
		</svg> -->
		<img src="<?php echo WPKB_PLUGIN_URL .'/images/'?>citation_logo.png">
	</a>
	<div class="kb-item">

		<?php
		$settings = get_option('kb_settings');
		if (!empty($settings['yt_apikey'])) {
			$apiKey = esc_html($settings['yt_apikey']);
		} else {
			$apiKey = 'AIzaSyCdIlPm0sdZKxsMQTxnVGt5-5PcTr8M8VA';
		}

		$link = $kbucket->link; ?>
		<?php
		if (strpos($link, 'youtube') !== false || strpos($link, 'youtu.be') !== false) {
			if (strpos($link, 'youtube') !== false) {
				parse_str(parse_url($link, PHP_URL_QUERY), $query);
				$videoId = $query['v'];
			} else {
				$videoId = substr($link, strrpos($link, '/') + 1);
			}

			if (!empty($videoId)) {
				$videoDetails = getVideoDetails($videoId, $apiKey);

				if ($videoDetails !== null) {
		?>
					<div class="share-image-wr kb_video">
						<iframe id="youtubePlayer" width="560" height="315" src="https://www.youtube.com/embed/<?php echo $videoId; ?>?enablejsapi=1&autoplay=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				<?php
				} else {
					// Video is invalid or unavailable
					echo "YouTube video is broken or unavailable.";
				}
			}
		} else {
			if (!empty($shareData['imageUrl'])) : ?>
				<div class="share-image-wr kb_image">
					<img src="<?php echo esc_attr($shareData['imageUrl']); ?>" class="share-image" />
				</div>
		<?php endif;
		}
		?>

		<?php
		// Function to fetch video details using YouTube Data API
		function getVideoDetails($videoId, $apiKey)
		{
			$ytURL = 'https://www.googleapis.com/youtube/v3/videos?id=' . $videoId . '&key=' . $apiKey . '&part=snippet,statistics';

			$response = file_get_contents($ytURL);
			$data = json_decode($response, true);

			if (isset($data['items']) && !empty($data['items'])) {
				return $data['items'][0];
			}

			return null;
		}
		?>

		<div class="meta_tags">
			<span class="kb-item-author">
				<?php _e("Publisher:", WPKB_TEXTDOMAIN) ?> <?php echo esc_html($kbucket->publisher); ?><br>
				<?php _e("Author:", WPKB_TEXTDOMAIN) ?> <?php echo esc_html($kbucket->author); ?></span>
			</span>
			<span class="kb-item-date"><?php echo date("Y-m-d", strtotime($kbucket->pub_date)); ?></span>
		</div>

		<h3 class="model-post-heading"><a id="model-post-heading" href="<?php echo esc_attr($kbucket->link); ?>" target="_blank"><?php echo html_entity_decode(esc_html($shareData['title'])); ?></a></h3>

		<div class="body_text">
			<?php echo html_entity_decode(wp_kses(
				kbucket_strip_cdata($shareData['description']),
				array(
					'a' => array(
						'href' => true,
						'title' => true,
						'style' => true
					),
					'br' => array(),
					'p' => array(
						'class' => true,
						'style' => true
					),
					'div' => array(
						'class' => true,
						'style' => true
					),
					'i' => array(),
					'b' => array(),
					'blockquote' => array(),
					'img' => array(
						'class' => true,
						'src' => true,
						'style' => true
					),
					'h1' => array(),
					'h2' => array(),
					'h3' => array(),
					'h4' => array(),
					'h5' => array(),
					'h6' => array(),
				)
			)); ?>
		</div>
		<p class="blue" style=" margin-bottom:2px;">
			<span>Tags: <?php echo esc_html($kbucket->tags); ?></span>
			<?php if (!empty($current_url)) kb_render_kbucket_tags($current_url, $kbucket->tags); ?>
		</p>

		<?php
		$videoDetails = getVideoDetails($videoId, $apiKey);

		if ($videoDetails !== null) {
			$channelTitle = $videoDetails['snippet']['channelTitle'];
			$publishedAt = $videoDetails['snippet']['publishedAt'];
			$description = $videoDetails['snippet']['description'];
			$views = number_format($videoDetails['statistics']['viewCount']);
			$likes = number_format($videoDetails['statistics']['likeCount']);
			$channelId = $videoDetails['snippet']['channelId'];

			// Convert YouTube date to WordPress local date
			$wp_timezone = get_option('timezone_string');
			if (!$wp_timezone) {
				$wp_timezone = 'UTC';
			}
			$timezone = new DateTimeZone($wp_timezone);
			$datetime = new DateTime($publishedAt);
			$datetime->setTimezone($timezone);
			$publishedAt = $datetime->format('m/d/Y');  // Adjust the format as needed

			echo '<div class="ytinfo"><div class="views">' . $views . ' Views</div><div class="likes"><div class="icon icon-like"></div><span>' . $likes . '</span></div></div>';
			echo '<div class="channelInfo"><a target="_blank" href="https://www.youtube.com/channel/' . $channelId . '">' . $channelTitle . '</a></div>';
			echo '<div class="pubDate">Published on: ' . $publishedAt . '<div>';



			$description_length = strlen($description);

			if ($description_length > 100) {
				$description_short = substr($description, 0, 100) . '...';
				$description_full = $description;

				echo '<div class="content-short body_text">' . $description_short . ' <span class="show-more">Show More</span></div>';
				echo '<div class="content-full body_text" style="display: none;">' . $description_full . ' <span class="show-less">Show Less</span></div>';
			} else {
				echo '<div class="video-desc body_text">' . $description . '</div>';
			}?>
			<div class="body_text_copy" style="display:none;"><?php   
			$description = strip_tags(html_entity_decode(kbucket_strip_cdata($shareData['description'])));

			echo $description = strlen($description) >= 140 ? substr($description, 0, 140) . '...' : $description;
			?></div>
			<?php 
		}
		?>
	    
		<?php
		$description = strip_tags(html_entity_decode(kbucket_strip_cdata($shareData['description'])));
		$description = strlen($description) >= 56 ? substr($description, 0, 56) . '...' : $description;
		?>

		<div class="kb-footer-modal">
			<div class="addthis_toolbox_custom addthis_default_style" addthis:url="<?php echo $url . '/?share=true' ?>" addthis:title="<?php echo trim($shareData['title']) ?>" addthis:description="<?php echo $description ?>">
				<div class="wrap_addthis_share">
					<div style="width:33.333%;">
						<!--<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url . '/?share=true'); ?>" target="_blank">
							<div class="fb-share-button" data-action="like" data-show-faces="true" data-href="<?php echo $url . '/?share=true' ?>" data-layout="button_count"></div>
						</a>-->
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url . '/?share=true'); ?>" target="_blank">
							<div class="fb-share-button" data-action="like" data-show-faces="true" data-href="<?php echo $url . '/?share=true' ?>" data-layout="button_count"></div>
						</a>
					</div>
					<div style="width:33.333%;">
						<a class="share-btn tw" href="https://twitter.com/share?url=<?php echo urlencode($url . '/?share=true'); ?>&text=<?php echo urlencode(trim($shareData['title'])); ?>" target="_blank">
							<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
								<path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z" />
							</svg> Tweet
						</a>
					</div>
					<div style="width:33.333%;">
						<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($url . '/?share=true'); ?>&title=<?php echo urlencode(trim($shareData['title'])); ?>" target="_blank">
							<div class="share-btn in"><svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" viewBox="0 0 448 512">
									<path d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z" />
								</svg> Share</div>
						</a>
					</div>
				</div>

				<div class="extended_addthis_share">
					<div style="width:33.333%;">
						<a data-pin-do="buttonPin" href="https://www.pinterest.com/pin/create/button/?url=<?php echo $url . '/?share=true' ?>&media=<?php echo $shareData['imageUrl'] ?>&description=<?php echo $description ?>" class="pin-it-button" count-layout="none"><img src="//assets.pinterest.com/images/pidgets/pin_it_button.png" alt="Pin it" /></a>
					</div>
					<div style="width:33.333%;">
						<a class="addthis_button_stumbleupon_badge" su:badge:style="3" su:badge:url="<?php echo $url ?>" style="background-image: url('<?php echo WPKB_PLUGIN_URL; ?>/images/stumble.png')"></a>
					</div>
					<div style="width:33.333%;">
						<a class="addthis_button_email"><img src="<?php echo WPKB_PLUGIN_URL; ?>/images/email-share.png" alt="mail"></a>
					</div>
				</div>
			</div>
			<div id="fb-root"></div>

			<style>
				.fb_iframe_widget span,
				iframe.fb_iframe_widget_lift,
				.fb_iframe_widget iframe {
					width: 95px !important;
					height: 20px !important;
					position: relative;
				}
			</style>

			<script>
				jQuery(".url_share_addthis").focus(function() {
					jQuery(this).select();
				});
				<?php if ($kbucket->twitter) : ?>
					window.addthis_share.passthrough = {
						twitter: {
							via: '<?= str_replace('@', '', $kbucket->twitter); ?>'
						}
					};
				<?php endif ?>
				refreshAddthis();
			</script>
		</div>
	</div>

	<?php $kb_share = kb_get_settings(); ?>
	<?php if (isset($kb_share['kb_share_popap'])) : ?>
		<div class="ext_widget_sect">
			<?php echo kbucket_utf8_urldecode($kb_share['kb_share_popap']); ?>
		</div>
	<?php endif; ?>
</div>
<script type="text/javascript">

 function copyingMyfunctionPOP() {
	//var href =  $('.addthis_button_stumbleupon_badge').attr("su:badge:url");
	//alert(href);
    var elementsToCopyModel = [
        $("#facebox .kb-item .kb-item-date"),
        $("#facebox .kb-item .kb-item-author"),
        $("#facebox .kb-item #model-post-heading"),
        $("#facebox .kb-item .body_text_copy"),
    ];
    concatenatedText = "";
    var $elements = $(elementsToCopyModel);

    $elements.each(function () {
        concatenatedText += $(this).text().replace(/\s+/g, ' '); // Remove extra spaces
    });

    concatenatedText += $('.addthis_button_stumbleupon_badge').attr("su:badge:url");;

    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val(concatenatedText.trim()).select();
    document.execCommand("copy");
    $temp.remove();
   }

	/*Hash */
	// $(document).on("click", ".share_modal_wrapper .show-more", function() {

	// 	document.querySelector('.content-short').style.display = 'none';
	// 	document.querySelector('.content-full').style.display = 'block';

	// });

	// $(document).on("click", ".share_modal_wrapper .show-less", function() {

	// 	document.querySelector('.content-full').style.display = 'none';
	// 	document.querySelector('.content-short').style.display = 'block';

	// });
	// $kbj(".share_modal_wrapper .show-more").on("click", function (e) {



	// });
	//  $kbj(".share_modal_wrapper .show-less").on("click", function (e) {
	// 	document.querySelector('.content-full').style.display = 'none';
	// 					document.querySelector('.content-short').style.display = 'block';

	// });
	/*Enable Hash*/
</script>