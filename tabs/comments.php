<div id="country5" class="tabcontent">
	<p><?php _e("All comments entered through “Suggest Content” tab on your KBucket page will appear here. You can subscribe to this page and follow the comments in your Kauthor Firefox Extension.",WPKB_TEXTDOMAIN) ?></p>
	<div style="width:700px;margin-top:10px; font-family:Arial, Helvetica, sans-serif; font-size:12px;">
		<a href="<?php echo esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI'])) . '&suggest_rss=y'; ?>" target="_blank"><img src="<?php echo WPKB_PLUGIN_URL; ?>/images/rss_icon.png"/></a>

		<?php

		$data = kb_get_admin_siggest_list();

		foreach ( $data as $item ): ?>
			<table data-id="<?=$item->id_sug ?>">
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Suggested for:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td>
						<span class="generic_text_3">
							<span style="text-transform: uppercase;"><?php echo esc_html( $item->subcat ); ?></span>
						</span>
					</td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Date:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->add_date ); ?></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Page Title:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->tittle ); ?></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Page Tags:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->tags ); ?></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Page URL:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><a href="<?php echo esc_attr( $item->link ); ?>" target="_blank"><?php echo esc_html( $item->link ); ?></a></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Page Author:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->author ); ?></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Twitter Handle:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->twitter ); ?></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Facebook Page:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->facebook ); ?></td>
				</tr>
				<tr>
					<td class="kbucket-text-right"><strong><?php _e("Comment:",WPKB_TEXTDOMAIN) ?></strong></td>
					<td><?php echo esc_html( $item->description ); ?></td>
				</tr>
				<tr>
				<td colspan=2 style="border-bottom:2px solid pink; width:100%; min-width:700px; text-align:right;">
					<a href="javascript:void(0);" onclick="confirmdelete(<?php echo esc_js( $item->id_sug ); ?>)"><?php _e("Delete",WPKB_TEXTDOMAIN) ?></a>
				</td>
				</tr>
			</table>
		<?php endforeach ?>

	</div>
</div><!-- /#country5 -->
