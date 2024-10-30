<div id="country6" class="tabcontent">
	<p><?php _e("Here you can add and change images to your links. You can also save the RSS feed for each channel and use “messaging apps” like Buffer and Socialoomph to schedule and distribute your curated links on social media. Visit our Forum for more info.",WPKB_TEXTDOMAIN) ?></p>
	<?php $categories = kb_get_subcategories( 0, '`id_cat`,`name`,`alias_name`' );?>
	<?php $uri_parts = explode('?', esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI'])), 2); ?>
	<a class="rss_link" href="#" data-link="<?php echo get_site_url() . $uri_parts[0] . '?format=rss&'; ?>" target="_blank" style="display:none;vertical-align:middle;height:24px;">
			<img src="<?php echo WPKB_PLUGIN_URL; ?>/images/rss_icon.png">
		</a>
	<select name="category_id" id="category-dropdown">
		<option value=""><?php _e("Select Category",WPKB_TEXTDOMAIN) ?></option>
		<?php foreach ( $categories as $c ): ?>
			<option value="<?php echo esc_attr( $c['id_cat'] ); ?>"><?php echo esc_html( $c['name'] ); ?></option>
		<?php endforeach; ?>
	</select>
	<select name="subcategory_id" id="subcategory-dropdown">
		<option value=""><?php _e("Select Subcategory",WPKB_TEXTDOMAIN) ?></option>
	</select>

	<button id="button-save-sticky" class="button-secondary"><?php _e("Save changes",WPKB_TEXTDOMAIN) ?></button>
	<div id="kbuckets"></div>
</div><!-- /#country6 -->
