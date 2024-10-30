<div id="country7" class="tabcontent">
	<p><?php _e("Stylize your Kbucket page",WPKB_TEXTDOMAIN) ?></p>
	<form method="post" id="form" enctype="multipart/form-data">
		<table>
			<tr>
				<th colspan="3"><?php _e("Tag cloud",WPKB_TEXTDOMAIN) ?></th>
			</tr>

			<tr>
				<td height="40"><label for="color_tag_cloud"><?php _e("Color",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="color_tag_cloud" id="color_tag_cloud" value="<?php echo(isset($thepost['color_tag_cloud']) ? $thepost['color_tag_cloud'] : '#828282'); ?>"/>
				</td>
			</tr>
			<tr>
				<td height="40"><label for="bg_tag_cloud"><?php _e("Background color",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="bg_tag_cloud" id="bg_tag_cloud" value="<?php echo(isset($thepost['bg_tag_cloud']) ? $thepost['bg_tag_cloud'] : ''); ?>"/>
				</td>
			</tr>
			<tr>
				<td height="40"><label for="bg_active_tag_cloud"><?php _e("Background color active tag",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="bg_active_tag_cloud" id="bg_active_tag_cloud" value="<?php echo(isset($thepost['bg_active_tag_cloud']) ? $thepost['bg_active_tag_cloud'] : '#5269ac'); ?>"/>
				</td>
			</tr>
			<tr>
				<td height="40"><label for="fz_tag_cloud"><?php _e("Font Size",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input type="number" name="fz_tag_cloud" id="fz_tag_cloud" value="<?php echo(isset($thepost['fz_tag_cloud']) ? $thepost['fz_tag_cloud'] : '10'); ?>"/>
				</td>
			</tr>

			<tr>
				<th colspan="3"><?php _e("Post",WPKB_TEXTDOMAIN) ?></th>
			</tr>
			<tr>
				<td height="40"><label for="headline_color"><?php _e("Headline Color",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="headline_color" id="headline_color" value="<?php echo(isset($thepost['headline_color']) ? $thepost['headline_color'] : '#e64d47'); ?>"/>
				</td>
			</tr>
			<tr>
				<td height="40"><label for="post_font"><?php _e("Font",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<div class="fz_preview_wrap">
						<div class="fz_preview">
							<p style="font-size: 12px;">Whereas recognition of the inherent dignity</p>
							<p style="font-size: 14px;font-weight: 700;">Whereas recognition of the inherent dignity</p>
							<p style="font-size: 16px;">Whereas recognition of the inherent dignity</p>
							<p style="font-size: 18px;">Whereas recognition of the inherent dignity</p>
							<p style="font-size: 10px;">Whereas recognition of the inherent dignity</p>
						</div>
					</div>
					<select name="post_font" id="post_font" style="min-width: 360px">
						<?php foreach ( kb_stm_get_google_fonts() as $key => $stm_get_google_font ): ?>
							<option
								value="<?php echo $key ?>"
								<?php echo( $thepost['post_font'] && $thepost['post_font'] == $key ? 'selected' : ''); ?>>
								<?php echo $stm_get_google_font ?>
							</option>
						<?php endforeach; ?>
					</select>
					<script>
						const fonts = <?php echo json_encode(kb_stm_google_fonts_array()) ?>;
						jQuery('#post_font').on('change', function(event) {
							event.preventDefault();
							if(fonts[event.target.value] && fonts[event.target.value].category){
								jQuery('.fz_preview p').css('font-family', fonts[event.target.value].category);
							}
						})
					</script>
				</td>
			</tr>


			<tr>
				<th colspan="3"><?php _e("Post-Cards",WPKB_TEXTDOMAIN) ?></th>
			</tr>
			<tr>
				<td height="40"><label for="post_cards_color"><?php _e("Color",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="post_cards_color" id="post_cards_color" value="<?php echo(isset($thepost['post_cards_color']) ? $thepost['post_cards_color'] : '#333333'); ?>"/>
				</td>
			</tr>
			<tr>
				<td height="40"><label for="post_cards_bg"><?php _e("Background color",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="post_cards_bg" id="post_cards_bg" value="<?php echo(isset($thepost['post_cards_bg']) ? $thepost['post_cards_bg'] : '#ffffff'); ?>"/>
				</td>
			</tr>
			<tr>
				<td height="40"><label for="post_cards_search_btn"><?php _e("Background color the 'Search' button",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
					<input class="kb_color_picker" type="text" name="post_cards_search_btn" id="post_cards_search_btn" value="<?php echo(isset($thepost['post_cards_search_btn']) ? $thepost['post_cards_search_btn'] : '#e76c67'); ?>"/>
				</td>
			</tr>

			<tr>
				<td height="40"><label for="post_cards_search_btn"><?php _e("Social Pop up Position",WPKB_TEXTDOMAIN) ?></label></td>
				<td>:</td>
				<td>
				<select  class="kb_color_picker" name="model_position" id="model_position">
					<option <?php echo (isset($thepost['model_position']) && $thepost['model_position']=='center')?'selected':''; ?> value="center">Center</option>
					<option <?php echo (isset($thepost['model_position']) && $thepost['model_position']=='left')?'selected':''; ?> value="left">Left</option>
					<option <?php echo (isset($thepost['model_position']) && $thepost['model_position']=='right')?'selected':''; ?> value="right">Right</option>
				</select>
			    </td>
			</tr>				
		
						
		</table>


		<input class="button button-primary" type="submit" name="submit" id="submit" value="Update">
	</form>
</div><!-- /#country7 -->
