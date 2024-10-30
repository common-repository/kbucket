<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<ul id="kBucketTabs" class="shadetabs">
	<li><a href="#" rel="country0" class="selected"><?php _e("Home",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country1"><?php _e("Settings",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country2"><?php _e("Upload Kbucket",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country3"><?php _e("Set Social",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country5"><?php _e("user Comments",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country6"><?php _e("Published Links",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country7"><?php _e("Styles",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country8"><?php _e("Custom CSS",WPKB_TEXTDOMAIN) ?></a></li>
	<li><a href="#" rel="country9"><?php _e("Widget",WPKB_TEXTDOMAIN) ?></a></li>

</ul>

<script src="<?php echo WPKB_PLUGIN_URL ?>/js/vue.v2.6.1.js"></script>

<div id="messages"></div>
<div class="kbucket-tabcontent">
	<?php include_once(WPKB_PATH . "/tabs/home.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/settings.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/upload.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/social.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/comments.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/links.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/styles.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/css.php"); ?>
	<?php include_once(WPKB_PATH . "/tabs/widget.php"); ?>
</div><!-- /.kbucket-tabcontent -->
