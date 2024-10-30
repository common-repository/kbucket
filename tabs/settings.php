<div id="country1" class="tabcontent">
	<form method="post" id="form" enctype="multipart/form-data">
		<?php $thepost = kb_get_settings(); ?>
		<table>
			<tr>
				<th colspan="3"><?php _e("Page Settings",WPKB_TEXTDOMAIN) ?></th>
			</tr>

			<tr>
				<td class="kbucket-td-w30"><?php _e("API Key",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">
					<input type="text" name="api_key" id="api_key" value="<?php echo (isset($thepost['api_key']) ? $thepost['api_key']: ''); ?>"/>
					<label for="api_key"></label>
					<input type="hidden" name="api_host" value="<?php echo kbucket_url_origin($_SERVER) ?>">
				</td>
			</tr>

			<tr>
				<td class="kbucket-td-w30"><?php _e("Select KB Page",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">
					<?php
					$pages = get_posts(array('post_type'=>'page','posts_per_page' => -1,'post_status' => 'publish'));
					$current_page = kbucketPageID();
					?>
					<select name="kb_page_id" id="kb_page_id" style="width:100%">
						<?php foreach ($pages as $page):?>
							<option value="<?=$page->ID ?>" <?php if($page->ID == $current_page) echo 'selected="selected"' ?>><?=$page->post_title ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>

			<tr>
				<td class="kbucket-td-w30"><?php _e("Author Tag Display",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">

					<label for="atd-y">
						<input type="radio" name="atd" id="atd-y" value="1" <?php if ( 1 == $thepost['atd'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Yes",WPKB_TEXTDOMAIN) ?></span>
					</label>

					<label for="atd-n">
						<input type="radio" name="atd" id="atd-n" value="0" <?php if ( 0 == $thepost['atd'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("No",WPKB_TEXTDOMAIN) ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="kbucket-td-w30"><?php _e("Publisher Tag Display",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">

					<label for="atd-y">
						<input type="radio" name="ptd" id="ptd-y" value="1" <?php if ( 1 == $thepost['ptd'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Yes",WPKB_TEXTDOMAIN) ?></span>
					</label>

					<label for="atd-n">
						<input type="radio" name="ptd" id="ptd-n" value="0" <?php if ( 0 == $thepost['ptd'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("No",WPKB_TEXTDOMAIN) ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="kbucket-td-w30"><?php _e("Show/Hide date",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">

					<label for="atd-y">
						<input type="radio" name="dtd" id="dtd-y" value="1" <?php if ( 1 == $thepost['dtd'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Show",WPKB_TEXTDOMAIN) ?></span>
					</label>

					<label for="atd-n">
						<input type="radio" name="dtd" id="dtd-n" value="0" <?php if ( 0 == $thepost['dtd'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Hide",WPKB_TEXTDOMAIN) ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="kbucket-td-w30"><?php _e("Page Title",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">
					<input type="text" name="page_title" id="page_title" value="<?php echo(isset($thepost['page_title']) ? $thepost['page_title'] : ''); ?>"/>
					<label for="page_title"><?php _e("If blank, empty title",WPKB_TEXTDOMAIN) ?></label>
				</td>
			</tr>
			<tr>
				<td class="kbucket-td-w30"><?php _e("Site Search",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">

					<label for="site_search-1">
						<input type="radio" name="site_search" id="site_search-1" value="1" <?php if ( 1 == $thepost['site_search'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Yes",WPKB_TEXTDOMAIN) ?></span>
					</label>

					<label for="site_search-0">
						<input type="radio" name="site_search" id="site_search-0" value="0" <?php if ( 0 == $thepost['site_search'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("No",WPKB_TEXTDOMAIN) ?></span>
					</label>
				</td>
			</tr>
			<tr>
				<td class="kbucket-td-w30"><?php _e("Sort By",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">
					<select name="sortBy" id="sortBy">
						<option value=""><?php _e("Select",WPKB_TEXTDOMAIN) ?></option>
						<option	value="author" <?php if ( 'author' == $thepost['sortBy'] ) echo 'selected="selected"';?>><?php _e("Author",WPKB_TEXTDOMAIN) ?></option>
						<option value="title" <?php	if ( 'title' == $thepost['sortBy'] ) echo 'selected="selected"';?>><?php _e("Title",WPKB_TEXTDOMAIN) ?></option>
						<option value="add_date" <?php if ( 'add_date' == $thepost['sortBy'] ) echo 'selected="selected"' ?>><?php _e("Add Date",WPKB_TEXTDOMAIN) ?></option>
						<option value="pub_date" <?php if ( 'pub_date' == $thepost['sortBy'] ) echo 'selected="selected"' ?>><?php _e("Publish Date",WPKB_TEXTDOMAIN) ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td align="left" height="40"><?php _e("Sort Order",WPKB_TEXTDOMAIN) ?></td>
				<td>:</td>
				<td align="left">
					<select name="sortOrder" id="sortOrder">
						<option value=""><?php _e("Select",WPKB_TEXTDOMAIN) ?></option>
						<?php if ( isset( $thepost['sortOrder'] ) ): ?>
							<option value="asc" <?php if ( 'asc' == $thepost['sortOrder'] ) echo 'selected="selected"';?>><?php _e("Ascending",WPKB_TEXTDOMAIN) ?></option>
							<option value="desc" <?php if ( 'desc' == $thepost['sortOrder'] ) echo 'selected="selected"';?>><?php _e("Descending",WPKB_TEXTDOMAIN) ?></option>
							<option value="desc" <?php if ( 'desc' == $thepost['sortOrder'] ) echo 'selected="selected"';?>><?php _e("Newest",WPKB_TEXTDOMAIN) ?></option>
							<option value="asc" <?php if ( 'asc' == $thepost['sortOrder'] ) echo 'selected="selected"';?>><?php _e("Oldest",WPKB_TEXTDOMAIN) ?></option>
						<?php else: ?>
							<option value="asc"><?php _e("Ascending",WPKB_TEXTDOMAIN) ?></option>
							<option value="desc"><?php _e("Descending",WPKB_TEXTDOMAIN) ?></option>
							<option value="desc"><?php _e("Newest",WPKB_TEXTDOMAIN) ?></option>
							<option value="asc"><?php _e("Oldest",WPKB_TEXTDOMAIN) ?></option>
						<?php endif ?>
					</select>
				</td>
			</tr>
			<tr>
				<td align="left" height="40"><?php _e("Number Of Listing per Page",WPKB_TEXTDOMAIN) ?></td>
				<td>:</td>
				<td align="left">
					<?php if ( isset( $thepost['no_listing_page'] ) ): ?>
						<input type="text" id="no_listing_page" name="no_listing_page" value="<?php	echo $thepost['no_listing_page'];?>" class="number" />
					<?php else : ?>
						<input type="textbox" id="no_listing_page" name="no_listing_page" value="10" class="number"/>
					<?php endif; ?>
				</td>
			</tr>


			<tr>
				<td align="left" height="40"><?php _e("Html for K-Share popap",WPKB_TEXTDOMAIN) ?></td>
				<td>:</td>
				<td align="left">
					<?php if ( isset( $thepost['kb_share_popap'] ) ): ?>
						<input style="width: 100%;min-height: 90px;" id="kb_share_popap" name="kb_share_popap"><?php echo kbucket_utf8_urldecode($thepost['kb_share_popap']);?></textarea>
					<?php else : ?>
						<textarea style="width: 100%;min-height: 90px;" id="kb_share_popap" name="kb_share_popap"></textarea>
					<?php endif; ?>
				</td>
			</tr>

			<?php if ( isset($thepost['cat_sidebar']) && 2 == (int)$thepost['cat_sidebar'] ): ?>
				<tr>
					<td align="left" height="40"><?php _e("Theme sidebar BG color",WPKB_TEXTDOMAIN) ?></td>
					<td>:</td>
					<td align="left">
						<input class="kb_color_picker" type="text" name="sidebar_bg" id="sidebar_bg" value="<?php echo(isset($thepost['sidebar_bg']) ? $thepost['sidebar_bg'] : '#dfdfdf'); ?>"/>
					</td>
				</tr>

				<tr>
					<td align="left" height="40"><?php _e("Theme sidebar Font color",WPKB_TEXTDOMAIN) ?></td>
					<td>:</td>
					<td align="left">
						<input class="kb_color_picker" type="text" name="sidebar_font_color" id="sidebar_font_color" value="<?php echo(isset($thepost['sidebar_font_color']) ? $thepost['sidebar_font_color'] : '#333333'); ?>"/>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( isset($thepost['cat_sidebar']) && 3 == (int)$thepost['cat_sidebar'] ): ?>
				<tr>
                    <td align="left" height="40"><?php _e("Theme dropdown menu BG color",WPKB_TEXTDOMAIN) ?></td>
                    <td>:</td>
                    <td align="left">
                        <input class="kb_color_picker" type="text" name="dropdown_menu_bg" id="dropdown_menu_bg" value="<?php echo(isset($thepost['dropdown_menu_bg']) ? $thepost['dropdown_menu_bg'] : '#dfdfdf'); ?>"/>
                    </td>
                </tr>

				<tr>
                    <td align="left" height="40"><?php _e("Theme dropdown menu Font color",WPKB_TEXTDOMAIN) ?></td>
                    <td>:</td>
                    <td align="left">
                        <input class="kb_color_picker" type="text" name="dropdown_menu_font_color" id="dropdown_menu_font_color" value="<?php echo(isset($thepost['dropdown_menu_font_color']) ? $thepost['dropdown_menu_font_color'] : '#333333'); ?>"/>
                    </td>
                </tr>

				<tr>
                    <td align="left" height="40"><?php _e("Theme dropdown menu hover color",WPKB_TEXTDOMAIN) ?></td>
                    <td>:</td>
                    <td align="left">
                        <input class="kb_color_picker" type="text" name="dropdown_menu_hover_color" id="dropdown_menu_hover_color" value="<?php echo(isset($thepost['dropdown_menu_hover_color']) ? $thepost['dropdown_menu_hover_color'] : '#9d9797'); ?>"/>
                    </td>
                </tr>
			<?php endif; ?>


			<tr>
				<td align="left" height="40">
					<?php _e("Use as ShortCode",WPKB_TEXTDOMAIN) ?>
					<p>
						<i>Please, select "YES" if you are using some Page Builder plugin on your site (for example, Visual Composer, Divi builder, Beaver builder, etc.).
Leave "NO" if you are not using Page Builder plugin on your site.</i>
					</p>
				</td>
				<td></td>
				<td align="left">
					<label for="site_vc-1">
						<input type="radio" name="site_vc" id="site_vc-1" value="1" <?php if ( 1 == (int)$thepost['site_vc'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Yes",WPKB_TEXTDOMAIN) ?></span>
					</label>
					<label for="site_vc-0">
						<input type="radio" name="site_vc" id="site_vc-0" value="0" <?php if ( 0 == (int)$thepost['site_vc'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("No",WPKB_TEXTDOMAIN) ?></span>
					</label>

					<?php if((int)$thepost['site_vc']): ?>
						<div class="kb_admin_error">
							[shortcode_kbucket_list] - Post list
							[shortcode_kbucket_tags] - Tags cloud
						</div>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<td align="left" height="40">
					<?php _e("Chose Theme",WPKB_TEXTDOMAIN) ?>
				</td>
				<td></td>
				<td align="left">
					<label for="cat_sidebar-1">
						<input type="radio" name="cat_sidebar" id="cat_sidebar-1" value="1" <?php if ( isset($thepost['cat_sidebar']) && 1 == (int)$thepost['cat_sidebar'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Menu as Tabs", WPKB_TEXTDOMAIN) ?></span>
					</label>
					<label for="cat_sidebar-2">
						<input type="radio" name="cat_sidebar" id="cat_sidebar-2" value="2" <?php if ( !isset($thepost['cat_sidebar']) || 2 == (int)$thepost['cat_sidebar'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Menu in Sidebar", WPKB_TEXTDOMAIN) ?></span>
					</label>
                    <label for="cat_sidebar-3">
                        <input type="radio" name="cat_sidebar" id="cat_sidebar-3" value="3" <?php if ( !isset($thepost['cat_sidebar']) || 3 == (int)$thepost['cat_sidebar'] ) echo 'checked="checked"'; ?> />
                        <span><?php _e("Menu in Dropdown", WPKB_TEXTDOMAIN) ?></span>
                    </label>
					<label for="cat_sidebar-0">
						<input type="radio" name="cat_sidebar" id="cat_sidebar-0" value="0" <?php if ( !isset($thepost['cat_sidebar']) || 0 == (int)$thepost['cat_sidebar'] ) echo 'checked="checked"'; ?> />
						<span><?php _e("Hide Menu", WPKB_TEXTDOMAIN) ?></span>
					</label>

				</td>
			</tr>
			
			<tr>
				<td class="kbucket-td-w30"><?php _e("Youtube API",WPKB_TEXTDOMAIN) ?></td>
				<td class="kbucket-td-w5">:</td>
				<td class="kbucket-td-w60">
					<input type="text" name="yt_apikey" id="yt_apikey" value="<?php echo(isset($thepost['yt_apikey']) ? $thepost['yt_apikey'] : ''); ?>"/>
					<label for="yt_apikey"></label>
				</td>
			</tr>


			<tr>
				<td class="kbucket-text-left-h40" colspan="3">
					<input class="button button-primary" type="submit" name="submit" id="submit" value="<?php _e("Update Settings", WPKB_TEXTDOMAIN) ?>"/>
				</td>
			</tr>


		</table>
	</form>
</div>
