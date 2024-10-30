<div id="country8" class="tabcontent">
	<p><?php _e("Put here your custom CSS for right visible site",WPKB_TEXTDOMAIN) ?></p>
	<form method="post" id="form" enctype="multipart/form-data">
		<textarea name="custom_css" id="custom_css"><?php if(!empty($thepost['custom_css'])) echo $thepost['custom_css'] ?></textarea>

		<input class="button button-primary" type="submit" name="submit" id="submit" value="Update CSS">
	</form>
</div><!-- /#country8 -->
