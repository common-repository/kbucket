<div id="country2" class="tabcontent">
	<style type="text/css">
		@font-face {
			font-family: "pixel_font";
			src: url("<?php echo WPKB_PLUGIN_URL; ?>/fonts/Gameplay.ttf"),
			url("<?php echo WPKB_PLUGIN_URL; ?>/fonts/Gameplay.ttf") format("truetype");
			font-style: normal;
			font-weight: normal;
		}
		.wrap_textarea{
			width: 100%;
			box-shadow: 0 0 6px rgba(0,0,0,0.4) inset;
			border: 1px solid #bababa;
			padding: 10px;
			background-color: #eaeaea;
			height: 195px;
			position: relative;
			margin: 0 !important;
			position: relative;
			margin: 20px 0 !important;
			overflow: hidden;
		}

		.wrap_textarea .textarea{
			overflow: auto;
			height: 100%;
			margin-right: -30px;
		}

		.wrap_textarea .textarea p{
			margin: 0;
			font-family: pixel_font,sans-serif;
			color: #888;
			font-size: 10px;
			letter-spacing: 1.1px;
			position: relative;
			padding-left: 55px;
		}
		.wrap_textarea .textarea p a{
			color: #888;
		}
		.wrap_textarea .textarea p span.timer{
			position: absolute;
			left: 0;
			top: 0;
			width: 40px;
			height: 100%;
		}

		.wrap_textarea:before{
			content: "";
			left: 0;
			top: 0;
			display: inline-block;
			width: 100%;
			height: 100%;
			position: absolute;
			background-image: linear-gradient(rgba(255,255,255,0.4),rgba(0,0,0,0.1));
			/*z-index: 1;*/
		}
		@keyframes blink {
			0% {opacity: .2;}
			20% {opacity: 1;}
			100% {opacity: .2;}
		}
		.saving span {
			animation-name: blink;
			animation-duration: 1.4s;
			animation-iteration-count: infinite;
			animation-fill-mode: both;
			display: inline-block;
			padding: 0 2px;
			font-size: 15px;
			line-height: 5px;
		}
		.saving span:nth-child(2) {
			animation-delay: .2s;
		}
		.saving span:nth-child(3) {
			animation-delay: .4s;
		}
	</style>
	<p><?php _e("Upload KBuckets to Upload KBucket",WPKB_TEXTDOMAIN) ?></p>
	<a style="display: none" href="<?php echo add_query_arg('get-log', '1', esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI']))) ?>"><?php _e("Download Log", WPKB_TEXTDOMAIN) ?></a>
	<form method="post" id="form_upload_xml" enctype="multipart/form-data">
		<div class="wrap_upload_xml_form" style="margin-top:10px;">
			<a target="_blank" class="kb_full_log" style="display: none" href="<?php echo get_admin_url() ?>?page=KBucket&show-log=1"><?php _e("Full Log",WPKB_TEXTDOMAIN) ?></a>

			<input type="file" size="24" name="upload_xml" id="upload_xml" accept="text/xml" />
			<input type="submit" class="button button-primary" name="submit"
			       value="<?php _e("Upload XML",WPKB_TEXTDOMAIN) ?>"/>
			<a href="#"
			   style="float: right"
			   class="button button-default kb-remove"><?php _e("Clear KBucket Content",WPKB_TEXTDOMAIN) ?></a>
		</div>
	</form>

	<p><?php _e("Or, paste the link to your KBucket file",WPKB_TEXTDOMAIN) ?></p>
	<form method="post" id="form_url_xml" enctype="multipart/form-data">
		<div class="">
			<textarea name="url_xml" id="url_xml" style="width: 100%" rows="5"
			          placeholder="<?php _e("Insert Kbucket file url", WPKB_TEXTDOMAIN) ?>"></textarea>
			<select name="update_interval" id="update_interval">
				<option value=""><?php _e("Manual",WPKB_TEXTDOMAIN) ?></option>
				<option value="day"><?php _e("Every day",WPKB_TEXTDOMAIN) ?></option>
				<option value="3h"><?php _e("Every 3 hours",WPKB_TEXTDOMAIN) ?></option>
				<option value="1h"><?php _e("Every 1 hour",WPKB_TEXTDOMAIN) ?></option>
			</select>
			<input type="submit" class="button button-primary" name="submit"
			       value="<?php _e("Start",WPKB_TEXTDOMAIN) ?>"/>
		</div>
	</form>

	<script>
	var $ = jQuery;
	jQuery(document).ready(function($) {
		jQuery('input.kb_color_picker').wpColorPicker();
	});

	var timer = new Date().getTime();
	var blog_id  = <?php echo get_current_blog_id() ?>;

	function kbucket_timer(timer){
		var new_timer = new Date().getTime();
		return (((new_timer - timer) / 1000)/60).toString().substr(0, 5);
	}

	function inserted_text(target, text, updated, upd_class){
		jQuery('.kb_full_log').show();
		if(updated){
			if(jQuery(target).find('p.kbv_updated.'+upd_class).length){
				jQuery(target).find('p.kbv_updated.'+upd_class).css('opacity','0').html(text);
				jQuery(target).find('p.kbv_updated.'+upd_class).animate({
						'opacity': '1'},
					200, function() {
						setTimeout(function(){
							var elem = document.getElementById('log_upload');
							elem.scrollTop = elem.scrollHeight;
						},600);
					});
			}else{
				jQuery(target).append('<p class="kbv_updated '+upd_class+'" style="opacity:0"><span class="timer">'+ kbucket_timer(timer) + ': </span>' +text+'</p>');
				jQuery(target).find('p.kbv_updated.'+upd_class).animate({
						'opacity': '1'},
					200, function() {
						setTimeout(function(){
							var elem = document.getElementById('log_upload');
							elem.scrollTop = elem.scrollHeight;
						},600);
					});
			}
		}else{
			jQuery(target).append('<p style="opacity:0"><span class="timer">'+ kbucket_timer(timer) + ': </span>' +text+'</p>');
			jQuery(target).find('p:last-child').animate({
					'opacity': '1'},
				200, function() {
					setTimeout(function(){
						var elem = document.getElementById('log_upload');
						elem.scrollTop = elem.scrollHeight;
					},600);
				});
		}
	}


	function updateAllImages(img_arr) {
		var imgCount = 0;

		var count_limit = 5;
		var count = Math.ceil(parseInt(img_arr.length) / count_limit);
		var all_img = img_arr.length;

		function getNextImage() {
			jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: ajax_url,
				timeout: 2200000, // 20 min
				data: {
					'action': 'ajax_upload_img_kbucket',
					'images': img_arr.slice(imgCount * count_limit, (imgCount * count_limit) + count_limit),
					'blog_id': blog_id
				},
				async: true,
				success: function(response) {
					if (response.success && imgCount <= count) {
						++imgCount;


						var s_increment = parseInt(jQuery('#log_upload').find('.success_upload b').text());
						if(!jQuery('#log_upload').find('.success_upload b').length) s_increment = response.s_uploaded;
						else s_increment = s_increment + parseInt(response.s_uploaded);

						var e_increment = parseInt(jQuery('#log_upload').find('.error_upload b').text());
						if(!jQuery('#log_upload').find('.error_upload b').length) e_increment = response.e_uploaded;
						else e_increment = e_increment + parseInt(response.e_uploaded);

						var m_increment = parseInt(jQuery('#log_upload').find('.empty_upload b').text());
						if(!jQuery('#log_upload').find('.empty_upload b').length) m_increment = response.m_uploaded;
						else m_increment = m_increment + parseInt(response.m_uploaded);

						var all_ready = parseInt(s_increment) + parseInt(e_increment) + parseInt(m_increment);

						var upload_f_arr = img_arr.slice(imgCount * count_limit, (imgCount * count_limit) + count_limit);
						var upload_f_list = '';

						for(var i in upload_f_arr){
							upload_f_list = upload_f_list + upload_f_arr[i].name + '<br>';
						}

						inserted_text('#log_upload','Uploaded or exist files: <b>'+s_increment+'</b>', true, 'success_upload');
						inserted_text('#log_upload','Error with upload: <b>'+e_increment +'</b>', true, 'error_upload');
						inserted_text('#log_upload','Without image: <b>'+m_increment+'</b>', true, 'empty_upload');
						inserted_text('#log_upload','Total images: <b>'+all_ready+'</b>'+' of '+'<b>'+all_img+'</b>', true, 'all_upload');
						inserted_text('#log_upload','Progress: <b>'+Math.ceil((all_ready/all_img) * 100)+'%</b><strong class="saving"><span>.</span><span>.</span><span>.</span></strong>', true, 'c_upload');

						getNextImage();

						if( (imgCount - 1) === count ){
							inserted_text('#log_upload','All done!');
							jQuery('#form_upload_xml .button-primary').removeAttr('disabled');
							jQuery('#form_upload_xml input[type="file"]').show();

						}
					}

				},
				error: function(e){
					inserted_text('#log_upload','Error: '+e.statusText);
				}
			});
		}
		getNextImage();
	}

	ajax_url = '<?=admin_url('admin-ajax.php') ?>';

	function getScheduledKbuckets() {
		jQuery.ajax({
			dataType: 'json',
			url: ajax_url,
			data: {"action": "ajax_scheduled_kbucket",'blog_id': blog_id},
			async: false,
			success: function(response) {
				if(response.logs && Object.keys(response.logs).length) {
					if(!jQuery('.wrap_upload_xml_form').find('#log_upload').length)
						jQuery('.wrap_upload_xml_form').prepend('<div class="wrap_textarea"><div class="textarea" id="log_upload"></div></div>');
					else
						jQuery('#log_upload').html('');
					for(var i in response.logs){
						let data = response.logs[i].split('|');
						var a = new Date(data[0] * 1000);
						var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
						var year = a.getFullYear();
						var month = months[a.getMonth()];
						var date = a.getDate();
						var hour = ("0" + a.getHours()).slice(-2);
						var min = ("0" + a.getMinutes()).slice(-2);
						var sec = ("0" + a.getSeconds()).slice(-2);
						var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;

						inserted_text('#log_upload', time + ' - ' + data[1]);
					}
				}
				if(response.active){
					// let t = Object.keys(response.logs);// Redirect after 5 min not active
					// if(Math.floor(Date.now() / 1000) > parseInt(t[t.length -1]) + 5 * 60){
					// 	setTimeout(() => {window.location.href = window.location.href}, 30000);
					// }
					$('#form_url_xml [name="url_xml"]').val(response.url).prop('disabled', true);
					$('#form_url_xml select[name="update_interval"]').val(response.interval).prop('disabled', true);
					$('.wrap_upload_xml_form [name="upload_xml"]').prop('disabled', true);
					$('.wrap_upload_xml_form input[name="submit"]').prop('disabled', true);
					$('#form_url_xml input[name="submit"]').val('<?php _e("Stop", WPKB_TEXTDOMAIN) ?>');

				}else{
					$('#form_url_xml [name="url_xml"]').prop('disabled', false);
					$('#form_url_xml select[name="update_interval"]').prop('disabled', false);
					$('.wrap_upload_xml_form [name="upload_xml"]').prop('disabled', false);
					$('.wrap_upload_xml_form input[name="submit"]').prop('disabled', false);
					$('#form_url_xml input[name="submit"]').val('<?php _e("Start", WPKB_TEXTDOMAIN) ?>');
					clearInterval(schedule);
				}
				if(response.url) $('#form_url_xml [name="url_xml"]').val(response.url);
				if(response.interval) $('#form_url_xml select[name="update_interval"]').val(response.interval);
			},
			error: function(e){
				inserted_text('#log_upload','Error: '+e.statusText);
			}
		});
	}

	getScheduledKbuckets();
	var schedule = setInterval(getScheduledKbuckets, 1000 * 8); // every 8 seconds

	jQuery('.wrap_upload_xml_form .button.kb-remove').on('click', function(event) {
		event.preventDefault();
		let p = confirm("Do You have delete all kbuckets, attached images, categories, sub-categories & tags?");
		if(p){
			jQuery.ajax({
				dataType: 'json',
				url: ajax_url,
				data: {"action": "ajax_purge_kbuckets",'blog_id': blog_id},
				async: false,
				success: function (response) {
					alert("Your KBucket page has been reset. Go ahead and upload your new content");
				}
			});

		}
	});

	jQuery('#form_url_xml [name="submit"]').on('click', function(event) {
		let self = this;
		if($('#form_url_xml [name="url_xml"]').val() === '') return;
		let type = $('#form_url_xml [name="url_xml"]').prop('disabled');
		if(!type){
			$('#form_url_xml [name="url_xml"]').prop('disabled', true);
			$('#form_url_xml select[name="update_interval"]').prop('disabled', true);
			$('.wrap_upload_xml_form [name="upload_xml"]').prop('disabled', true);
			$('.wrap_upload_xml_form input[name="submit"]').prop('disabled', true);
			$('#form_url_xml input[name="submit"]').val('<?php _e("Stop", WPKB_TEXTDOMAIN) ?>');
		}else{
			$('#form_url_xml [name="url_xml"]').prop('disabled', false);
			$('#form_url_xml select[name="update_interval"]').prop('disabled', false);
			$('.wrap_upload_xml_form [name="upload_xml"]').prop('disabled', false);
			$('.wrap_upload_xml_form input[name="submit"]').prop('disabled', false);
			$('#form_url_xml input[name="submit"]').val('<?php _e("Start", WPKB_TEXTDOMAIN) ?>');
			clearInterval(schedule);
		}
		$(self).prop('disabled', true);
		jQuery.ajax({
			dataType: 'json',
			url: ajax_url,
			data: {
				"action": "ajax_activate_scheduled_kbucket",
				"activate": !type,
				"url": $('#form_url_xml [name="url_xml"]').val(),
				"interval": $('#form_url_xml select[name="update_interval"]').val(),
				"initial_start": true,
				'blog_id': blog_id
			},
			success: function(response) {
				schedule = setInterval(getScheduledKbuckets, 1000 * 10);
				$(self).prop('disabled', false);
				//setTimeout(() => {
				//	jQuery.ajax({
				//		dataType: 'html',
				//		url: '<?php //echo get_site_url() ?>///wp-admin/admin.php?page=KBucket',
				//		data: {}
				//	});
				//}, 5000);
			},
			error: function(e){
				inserted_text('#log_upload','Error: '+e.statusText);
			}
		});
	})

	jQuery('#form_upload_xml').on('submit', function(event) {
		event.preventDefault();

		if(jQuery(document.getElementById("form_upload_xml")).find("input[type=file]").val() == '') return false;

		if(!jQuery('.wrap_upload_xml_form').find('#log_upload').length) jQuery('.wrap_upload_xml_form').prepend('<div class="wrap_textarea"><div class="textarea" id="log_upload"></div></div>');
		else jQuery('#log_upload').html('');

		inserted_text('#log_upload','Begin upload<strong class="saving"><span>.</span><span>.</span><span>.</span></strong><br>');

		var uploadform = document.getElementById("form_upload_xml");

		var form_data = new FormData();
		var file = jQuery(uploadform).find("input[type=file]");
		var individual_file = file[0].files[0];
		form_data.append("file", individual_file);
		form_data.append("security", jQuery(uploadform).find("input[name=security]").val());
		form_data.append("action", "ajax_upload_kbucket");
		form_data.append("size", "");
		form_data.append("blog_id", blog_id);

		jQuery('#form_upload_xml .button-primary').attr('disabled','disabled');
		jQuery('#form_upload_xml input[type="file"]').hide();


		jQuery.ajax({
			url: ajax_url,
			type: "POST",
			data: form_data,
			contentType: false,
			cache: false,
			dataType: "json",
			processData: false,
			timeout: 2200000, // 20 min
			success: function(response) {
				jQuery('#log_upload').find('.saving').remove();
				if(response.error){
					inserted_text('#log_upload', response.error);
				}else{
					inserted_text('#log_upload', 'File Uploaded!<br>');
					inserted_text('#log_upload', 'Begin parsing '+response.file+'<strong class="saving"><span>.</span><span>.</span><span>.</span></strong><br>');

					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: ajax_url,
						timeout: 2200000, // 20 min
						data: {
							'action': 'ajax_parsing_kbucket',
							'file': response.success
						},
						success: function(response){
							jQuery('#log_upload').find('.saving').remove();

							inserted_text('#log_upload', 'Parsing complete!<br>');
							inserted_text('#log_upload', response.success.join('<br>'));

							inserted_text('#log_upload','Begin upload images<strong class="saving"><span>.</span><span>.</span><span>.</span></strong><br>');
							if(response.kb_arr_image){
								var arr = Object.keys(response.kb_arr_image).map(function (key) {
									response.kb_arr_image[key].key = key;
									return response.kb_arr_image[key];
								});
								updateAllImages(arr);
							}else{
								inserted_text('#log_upload','Images not found', true, 'success_upload');
								inserted_text('#log_upload','Progress: <b>100%</b><strong class="saving"><span>.</span><span>.</span><span>.</span></strong>', true, 'c_upload');
								inserted_text('#log_upload','All done!');
								jQuery('#form_upload_xml .button-primary').removeAttr('disabled');
								jQuery('#form_upload_xml input[type="file"]').show();
							}
						},
						error: function(e){
							inserted_text('#log_upload','Error: '+e.statusText);
						}
					});
				}
			},
			error: function(e){
				inserted_text('#log_upload','Error: '+e.statusText);
			}
		});
	});
	</script>
</div>
