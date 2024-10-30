<div id="country3" class="tabcontent">
	<p><?php _e("Enter a description and an image for social sharing of your KBucket pages. Each channel can have a unique description and image.",WPKB_TEXTDOMAIN) ?></p>
	<div style="width:700px;height:auto; margin-top:10px; font-family:Arial, Helvetica, sans-serif">
		<form method="post" id="form" enctype="multipart/form-data">
			<table width="700" border="0" cellspacing="0" cellpadding="0">
				<tr>
					<th width="180" class="kbucket-text-left" height="35"><?php _e("Categories",WPKB_TEXTDOMAIN) ?></th>
					<th width="250"><?php _e("Description",WPKB_TEXTDOMAIN) ?></th>
					<th width="250"><?php _e("Images",WPKB_TEXTDOMAIN) ?></th>
					<th width="250"><?php _e("Hide in menu",WPKB_TEXTDOMAIN) ?></th>
				</tr>
				<?php print_r( kb_get_categories_dropdown( 0 ) ); ?>
				<tr>
					<td colspan="3"><input type="submit" class="button button-primary" name="submit" value="<?php _e("Update",WPKB_TEXTDOMAIN) ?>"></td>
				</tr>
			</table>

			<table width="500" cellspacing="0" cellpadding="0" style="display:none;">
				<tr>
					<th colspan="3" height="40"><?php _e("Add New Categories",WPKB_TEXTDOMAIN) ?></th>
				</tr>
				<tr>
					<td class="kbucket-text-left-h40" width="45%"><?php _e("Parent Category",WPKB_TEXTDOMAIN) ?><br/>
						[<span style="color:red"><?php _e("Select Parent Category If Exists",WPKB_TEXTDOMAIN) ?></span>]
					</td>
					<td width="5%">:</td>
					<td class="kbucket-text-left" width="50%">
						<select name="parent_category">
							<option value=""><?php _e("Select Parent category",WPKB_TEXTDOMAIN) ?></option>
							<?php $categories = kb_get_categories();
							foreach ( $categories as $c ) { ?>
								<option value="<?php echo esc_attr( $c->id_cat ) . '#' . esc_attr( $c->level ); ?>"><?php echo ucfirst( esc_html( $c->name ) ); ?></option><?php
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="kbucket-text-left-h40"><?php _e("Category Name",WPKB_TEXTDOMAIN) ?></td>
					<td>:</td>
					<td class="kbucket-text-left"><input type="text" name="category" value=""/></td>
				</tr>
				<tr>
					<td colspan="3" height="40">
						<input type="submit" name="submit" class="button button-primary" value="<?php _e("Add Category",WPKB_TEXTDOMAIN) ?>"/>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
