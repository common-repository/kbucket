<div id="country9" class="tabcontent">
	<p><?php _e("Generate customize your kbucket widget", WPKB_TEXTDOMAIN) ?></p>
	<form method="post" id="customize_widget" enctype="multipart/form-data">

		<div id="widget_app">
			<div class="kb_widget_rows" v-for="(row, index) in rows" :class="{'kb-widget-preload': preload}">
				<div class="kb_widget_name">
					<span class="kb-widget-code"><textarea type="text" :value="row.code" readonly @click="copyClipboard"></textarea></span>
					<span class="kb-input-group-btn">
						<span class="kb_widget_remove" @click="removeRow(index)">×</span>
						<span v-if="!row.collapse" class="kb_widget_col_down" @click="row.collapse = true">↑</span>
						<span v-else class="kb_widget_col_up" @click="row.collapse = false">↓</span>
					</span>

				</div>
				<div class="kb_widget_body" :class="{'kb-collapsed': row.collapse}">
					<table>
						<tr>
							<th colspan="3"><?php _e("Custom widget Shortcode", WPKB_TEXTDOMAIN) ?></th>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Select Category", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][category]'" @change="changeCategory($event, row)" v-model="row.category">
									<option value=""><?php _e("Select Category", WPKB_TEXTDOMAIN) ?></option>
									<template v-for="category in row.categories">
										<option :value="category.alias">{{category.name}}</option>
									</template>
								</select>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Select Sub-Category", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][subcategory]'" @change="changeSubCategory(row, true)" v-model="row.subcategory" :disabled="!row.sub_categories || !row.sub_categories.length || !row.category">
									<option value=""><?php _e("Select Sub-Category", WPKB_TEXTDOMAIN) ?></option>
									<template v-for="sub in row.sub_categories">
										<option :value="sub.value">{{sub.name}}</option>
									</template>
								</select>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Select Content Tag", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][tag]'" @change="changeTag($event, row)" v-model="row.tag" :disabled="!row.subcategory || !row.tags || !row.tags.length">
									<option value=""><?php _e("Select Content Tag", WPKB_TEXTDOMAIN) ?></option>
									<template v-for="tag in row.tags">
										<option :value="tag.value">{{tag.name}}</option>
									</template>
								</select>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Select Related Tag", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][related]'" v-model="row.related" :disabled="!row.tag || !row.relateds || !row.relateds.length" @change="updateShortCode(row)">
									<option value=""><?php _e("Select Related Tag", WPKB_TEXTDOMAIN) ?></option>
									<template v-for="rel in row.relateds">
										<option :value="rel.value">{{rel.name}}</option>
									</template>
								</select>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Select Publisher", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][publisher]'" v-model="row.publisher" :disabled="!row.subcategory || !row.publishers || !row.publishers.length" @change="updateShortCode(row)">
									<option value=""><?php _e("Select Publisher", WPKB_TEXTDOMAIN) ?></option>
									<template v-for="publisher in row.publishers">
										<option :value="publisher.value">{{publisher.name}}</option>
									</template>
								</select>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Select Author", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][author]'" v-model="row.author" :disabled="!row.subcategory || !row.authors || !row.authors.length" @change="updateShortCode(row)">
									<option value=""><?php _e("Select Author", WPKB_TEXTDOMAIN) ?></option>
									<template v-for="author in row.authors">
										<option :value="author.value">{{author.name}}</option>
									</template>
								</select>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Type # of posts on page", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<input type="number" :name="'kb_widget['+index+'][per_page]'" v-model="row.count" @change="updateShortCode(row)" />
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Type # of posts in line", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<input type="number" :name="'kb_widget['+index+'][inlineCount]'" v-model="row.inlineCount" @change="updateShortCode(row)" />
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Kbucket Style", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<select :name="'kb_widget['+index+'][list_style]'" v-model="row.list_style" @change="updateShortCode(row)" style="width:100%">
									<template v-for="list_style in row.list_styles">
										<option :value="list_style.id">{{list_style.title}}</option>
									</template>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Show/Hide Author", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<label :for="'sh_author_y_'+index">
									<input type="radio" value="1" :id="'sh_author_y_'+index" :name="'kb_widget['+index+'][sh_author]'" v-model="row.sh_author" @change="updateShortCode(row)" />
									<span><?php _e("Yes", WPKB_TEXTDOMAIN) ?></span>
								</label>

								<label :for="'sh_author_n'+index">
									<input type="radio" value="0" :id="'sh_author_n'+index" :name="'kb_widget['+index+'][sh_author]'" v-model="row.sh_author" @change="updateShortCode(row)" />
									<span><?php _e("No", WPKB_TEXTDOMAIN) ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Show/Hide Publisher", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<label :for="'sh_publisher_y_'+index">
									<input type="radio" value="1" :id="'sh_publisher_y_'+index" :name="'kb_widget['+index+'][sh_publisher]'" v-model="row.sh_publisher" @change="updateShortCode(row)" />
									<span><?php _e("Yes", WPKB_TEXTDOMAIN) ?></span>
								</label>

								<label :for="'sh_publisher_n'+index">
									<input type="radio" value="0" :id="'sh_publisher_n'+index" :name="'kb_widget['+index+'][sh_publisher]'" v-model="row.sh_publisher" @change="updateShortCode(row)" />
									<span><?php _e("No", WPKB_TEXTDOMAIN) ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Show/Hide Date", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<label :for="'sh_date_y_'+index">
									<input type="radio" value="1" :id="'sh_date_y_'+index" :name="'kb_widget['+index+'][sh_date]'" v-model="row.sh_date" @change="updateShortCode(row)" />
									<span><?php _e("Yes", WPKB_TEXTDOMAIN) ?></span>
								</label>

								<label :for="'sh_date_n'+index">
									<input type="radio" value="0" :id="'sh_date_n'+index" :name="'kb_widget['+index+'][sh_date]'" v-model="row.sh_date" @change="updateShortCode(row)" />
									<span><?php _e("No", WPKB_TEXTDOMAIN) ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Only Show/Hide Image", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<label :for="'sh_image_y_'+index">
									<input type="radio" value="1" :id="'sh_image_y_'+index" :name="'kb_widget['+index+'][sh_image]'" v-model="row.sh_image" @change="updateShortCode(row)" />
									<span><?php _e("Yes", WPKB_TEXTDOMAIN) ?></span>
								</label>

								<label :for="'sh_image_n'+index">
									<input type="radio" value="0" :id="'sh_image_n'+index" :name="'kb_widget['+index+'][sh_image]'" v-model="row.sh_image" @change="updateShortCode(row)" />
									<span><?php _e("No", WPKB_TEXTDOMAIN) ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Show/Hide Heading On Image", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<label :for="'sh_heading_image_y_'+index">
									<input type="radio" value="1" :id="'sh_heading_image_y_'+index" :name="'kb_widget['+index+'][sh_heading_image]'" v-model="row.sh_heading_image" @change="updateShortCode(row)" />
									<span><?php _e("Yes", WPKB_TEXTDOMAIN) ?></span>
								</label>

								<label :for="'sh_heading_image_n'+index">
									<input type="radio" value="0" :id="'sh_heading_image_n'+index" :name="'kb_widget['+index+'][sh_heading_image]'" v-model="row.sh_heading_image" @change="updateShortCode(row)" />
									<span><?php _e("No", WPKB_TEXTDOMAIN) ?></span>
								</label>
							</td>
						</tr>
						<tr>
							<td class="kbucket-td-w30"><?php _e("Show/Hide Title", WPKB_TEXTDOMAIN) ?></td>
							<td class="kbucket-td-w5">:</td>
							<td class="kbucket-td-w60">
								<label :for="'sh_title_y_'+index">
									<input type="radio" value="1" :id="'sh_title_y_'+index" :name="'kb_widget['+index+'][sh_title]'" v-model="row.sh_title" @change="updateShortCode(row)" />
									<span><?php _e("Yes", WPKB_TEXTDOMAIN) ?></span>
								</label>

								<label :for="'sh_title_n'+index">
									<input type="radio" value="0" checked :id="'sh_title_n'+index" :name="'kb_widget['+index+'][sh_title]'" v-model="row.sh_title" @change="updateShortCode(row)" />
									<span><?php _e("No", WPKB_TEXTDOMAIN) ?></span>
								</label>
							</td>
						</tr>
					</table>
				</div>
			</div>

			<div class="kb_widget_actions">
				<button type="button" class="button button-default" @click="addRow"><?php _e("Add more", WPKB_TEXTDOMAIN) ?></button>
				<button type="button" class="button button-primary" @click="saveShortCodes"><?php _e("Save", WPKB_TEXTDOMAIN) ?></button>
			</div>
		</div>





	</form>

	<?php
	$categories = kb_get_subcategories(0, false, true);
	$temp = [];
	if (is_array($categories) && count($categories) > 0) {
		foreach ($categories as $category) {
			$temp[$category['id_cat']]['name'] = html_entity_decode($category['name']);
			$temp[$category['id_cat']]['alias'] = $category['alias_name'];

			$temp[$category['id_cat']]['sub'] = [];
			$categories2 = kb_get_subcategories($category['id_cat'], false, true);
			foreach ($categories2 as $item) {
				$temp[$category['id_cat']]['sub'][$item['id_cat']]['name'] = html_entity_decode($item['name']);
				$temp[$category['id_cat']]['sub'][$item['id_cat']]['alias'] = $item['alias_name'];
			}
		}
	}
	?>
	<script>
		let categories = <?php echo json_encode($temp); ?>;
		let list_styles = <?php echo json_encode(kb_get_list_kb_styles()); ?>;
		var blog_id = <?php echo get_current_blog_id() ?>;

		jQuery(document).ready(function($) {
			new Vue({
				el: '#widget_app',
				data: function() {
					return {
						preload: false,
						rows: [],
						categories: categories,
						sub_categories: [],
						tags: [],
						related: [],
						publishers: [],
						authors: [],
						default_row: {
							category: '',
							subcategory: '',
							tag: '',
							related: '',
							publisher: '',
							author: '',
							count: 9,
							inlineCount: 3,
							code: '[kb_widget_list]',
							list_style: '',
							collapse: true,
							sub_categories: [],
							categories: categories,
							tags: [],
							relateds: [],
							publishers: [],
							authors: [],
							list_styles: list_styles,
							sh_date: 0,
							sh_author: 1,
							sh_publisher: 1,
							sh_title: 1,
							sh_image: 1,
							sh_heading_image: 1,
						}
					}
				},
				mounted: function() {
					this.getShortCodes();
				},
				methods: {
					changeCategory(event, row) {
						let vm = this;
						if (event) {
							row.subcategory = '';
							row.tag = '';
							row.related = '';
							row.author = '';
							row.publisher = '';
							row.tags = [];
							row.relateds = [];
							row.authors = [];
							row.publishers = [];
						}
						row.sub_categories = [];
						const category = row.category;
						Object.keys(row.categories).map(cat => {
							if (row.categories[cat]['alias'] === category) {
								Object.keys(vm.categories[cat]['sub']).map(subId => {
									row.sub_categories.push({
										name: row.categories[cat]['sub'][subId]['name'],
										value: row.categories[cat]['sub'][subId]['alias']
									})
								})
							}
						});
						this.updateShortCode(row);
					},
					changeSubCategory(row, reset) {
						let vm = this;
						if (reset) {
							row.tag = row.related = '';
						}
						jQuery.ajax({
							type: 'POST',
							dataType: 'json',
							url: '/wp-admin/admin-ajax.php',
							data: {
								'action': 'kb_get_content_tags',
								'category': row.subcategory,
								'blog_id': blog_id
							},
							beforeSend: function() {
								vm.preload = true;
							},
							success: function(data) {
								vm.preload = false;
								vm.updateShortCode(row);
								if (data.tags && data.tags.length) {
									if (!row.tags) row.tags = [];
									data.tags.map(value => {
										row.tags.push({
											name: value['name'],
											value: value['id_tag'] ? value['name'] : ''
										})
									});
									if (!reset) vm.changeTag(null, row);
								}
								if (data.publisher && data.publisher.length) {
									if (!row.publishers) row.publishers = [];
									data.publisher.map(value => {
										row.publishers.push({
											name: value['publisher'],
											value: value['publisher']
										})
									});
								}
								if (data.author && data.author.length) {
									if (!row.authors) row.authors = [];
									data.author.map(value => {
										row.authors.push({
											name: value['author'],
											value: value['author']
										})
									});
								}
							},
						});
					},
					changeTag(event, row) {
						let vm = this;
						if (event) row.related = '';
						jQuery.ajax({
							type: 'POST',
							dataType: 'json',
							url: '/wp-admin/admin-ajax.php',
							data: {
								'action': 'kb_get_related_tags',
								'category': row.subcategory,
								'tag': event?.target?.value ?? row.tag,
								'blog_id': blog_id
							},
							beforeSend: function() {
								vm.preload = true;
							},
							success: function(data) {
								vm.preload = false;
								vm.updateShortCode(row);
								if (data.length) {
									data.map(value => {
										row.relateds.push({
											name: value['name'],
											value: value['id_tag'] ? value['name'] : ''
										})
									});
								}
							},
						});
					},
					collapse(event, row) {
						if (row.collapse) {
							jQuery(event.target).parents('.kb_widget_rows').find('.kb_widget_body').slideUp('slow');
							row.collapse = false;
						} else {
							jQuery(event.target).parents('.kb_widget_rows').find('.kb_widget_body').slideDown('slow');
							row.collapse = true;
						}
					},
					addRow() {
						this.default_row['list_style'] = list_styles[0].id;
						this.rows.push(this.default_row);
					},
					removeRow(index) {
						let _confirm = confirm('<?php _e("Remove shortcode?", WPKB_TEXTDOMAIN) ?>');
						if (_confirm) {
							if (this.rows.length < 2) this.addRow();
							if (index > -1) {
								this.rows.splice(index, 1);
							}
						}
					},
					copyClipboard(event) {
						$(event.target).select();
						document.execCommand('copy');
					},
					updateShortCode(row) {
						const category = row.category ?? '';
						const sub_category = row.subcategory ?? '';
						const tag = row.tag ?? '';
						const related = row.related ?? '';
						const publisher = row.publisher;
						const author = row.author;
						const count = row.count;
						const inlineCount = row.inlineCount ?? 3;
						const style = row.list_style ?? '';
						const sh_date = row.sh_date ?? this.default_row['sh_date'];
						const sh_author = row.sh_author ?? this.default_row['sh_author'];
						const sh_publisher = row.sh_publisher ?? this.default_row['sh_publisher'];
						const sh_title = row.sh_title ?? this.default_row['sh_title'];
						const sh_image = row.sh_image ?? this.default_row['sh_image'];
						const sh_heading_image = row.sh_heading_image ?? this.default_row['sh_heading_image'];
						row.code = `[kb_widget_list category="${category}" sub-category="${sub_category}" tag="${tag}" related="${related}" publisher="${publisher}" author="${author}" count="${count}" inline="${inlineCount}" style="${style}" sh_date="${sh_date}" sh_author="${sh_author}" sh_publisher="${sh_publisher}" sh_title="${sh_title}" sh_image="${sh_image}" sh_heading_image="${sh_heading_image}"]`;
					},
					getShortCodes() {
						let vm = this;
						jQuery.ajax({
							type: 'POST',
							dataType: 'json',
							url: '/wp-admin/admin-ajax.php',
							data: {
								'action': 'kb_get_widget_data',
								'data': vm.rows,
								'blog_id': blog_id
							},
							beforeSend: function() {
								vm.preload = true;
							},
							success: function(data) {
								vm.preload = false;
								if (data && data.options) {
									vm.rows = data.options;
									vm.rows.map(row => {
										row.categories = categories;
										row.list_styles = list_styles;
										row.relateds = [];
										if (!row.list_style) row.list_style = list_styles[0].id
										vm.changeCategory(null, row);
										if (row.subcategory) {
											vm.changeSubCategory(row);
										}
									})
								}
							},
						});
					},
					saveShortCodes() {
						let vm = this;
						let temp = JSON.parse(JSON.stringify(vm.rows));

						temp.map((el) => {
							el.categories = [];
							el.sub_categories = [];
							el.tags = [];
							el.relateds = [];
							el.publishers = [];
							el.authors = [];
							el.list_styles = []
						});
						jQuery.ajax({
							type: 'POST',
							dataType: 'json',
							url: '/wp-admin/admin-ajax.php',
							data: {
								'action': 'kb_save_widget_data',
								'data': temp,
								'blog_id': blog_id
							},
							beforeSend: function() {
								vm.preload = true;
							},
							success: function(data) {
								vm.preload = false;
							},
						});
					}
				},
				watch: {

				}
			});
		})
	</script>
</div><!-- /#country9 -->