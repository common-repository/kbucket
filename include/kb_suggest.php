<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function kb_get_admin_siggest_list(){
	global $wpdb;
	return $wpdb->get_results("
		SELECT
			a.*,
			b.* ,
			DATE_FORMAT(a.add_date,'%d-%m-%Y' ) AS pubdate
		FROM {$wpdb->prefix}kb_suggest a
	 	LEFT JOIN
		(
			SELECT c.id_cat AS parid,
				b.alias_name AS alias,
				c.name AS parcat,
				b.name AS subcat,
				b.description AS subdes,
				c.description AS pades
			FROM {$wpdb->prefix}kb_category b
			INNER JOIN {$wpdb->prefix}kb_category c ON c.id_cat = b.parent_cat
		) b ON a.id_cat = b.alias");
}

function kb_ajax_delsugest(){
	global $wpdb;
	if(!is_admin()) return;
	$delsug = (int)$_POST['delsug'];

	$sqldel = $wpdb->prepare("DELETE FROM {$wpdb->prefix}kb_suggest WHERE id_sug=%s", $delsug);
	$wpdb->query( $sqldel );
}

function kb_action_ajax_validate_suggest(){
	global $wpdb;
	$sid_cat = esc_attr( $_POST['sid_cat'] );
	$stitle = esc_attr( $_POST['stitle'] );
	$sdesc = esc_attr( $_POST['sdesc'] );
	$stags = esc_attr( $_POST['stags'] );
	$sauthor = esc_attr( $_POST['sauthor'] );
	$surl = esc_url( $_POST['surl'] );
	$stwitter = esc_attr( $_POST['stwitter'] );
	$sfacebook = esc_attr( $_POST['sfacebook'] );

	$sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}kb_suggest
		VALUE(
			%s,
			%s,
			%s,
			%s,
			%s,
			current_date,
			%s,
			%s,
			%s,
			%s,
			'0'
		)",
		"",
		$sid_cat,
		$stitle,
		$sdesc,
		$stags,
		$sauthor,
		$surl,
		$stwitter,
		$sfacebook
	);
	$wpdb->query($sql);
}


/**
 * Render suggest window content
 * Fired by custom shortcode hook: suggest-page
 */
function kb_shortcode_render_suggest_page() {
	//$page = $this->get_page_data();
	//$this->render_header_content( $page );
	global $wpdb;
	$err = '';
	if ( isset( $_POST['ssugg'] ) ) {

		$requredParams = array(
			'stitle',
			'sdesc',
			'stags',
			'surl',
			'sauthor',
		);

		foreach ( $requredParams as $param ) {
			if ( empty( $_POST[ $param ] ) ) {
				//$err = 'Failed, Please fill all required fields...';
				break;
			}
		}

		if ( $err = '' ) {

			$sid_cat = esc_attr($_POST['sid_cat']);
			$stitle = esc_attr($_POST['stitle']);
			$sdesc = esc_attr($_POST['sdesc']);
			$stags = esc_attr($_POST['stags']);
			$sauthor = esc_attr($_POST['sauthor']);
			$surl = esc_url($_POST['surl']);
			$stwitter = esc_attr($_POST['stwitter']);
			$sfacebook = esc_attr($_POST['sfacebook']);

			$sql = $wpdb->prepare(
			 'INSERT IGNORE INTO `' . $wpdb->prefix . "kb_suggest`
			  SET `id_sug`='',
				`id_cat`=%s,
				`tittle`=%s,
				`description`=%s,
				`tags`=%s,
				`add_date`=CURRENT_DATE,
				`author`=%s,
				`link`=%s,
				`twitter`=%s,
				`facebook`=%s,
				`status`='0'",
			 array(
				$sid_cat,
				$stitle,
				$sdesc,
				$stags,
				$sauthor,
				$surl,
				$stwitter,
				$sfacebook,
			 )
			);

			$err = $wpdb->query( $sql ) ? 'Suggest link has been added successfully...' : 'Inserted Failed...';
		}
	} ?>
	<div class="container">
		<div class="con_suggest">
			<h1>Suggested page URL's </h1>
			<?php if ( $err != '' ): ?>
				<p><label>&nbsp;</label><span style="font-weight:bold; color:red;"><?=$err;	?></span></p>
			<?php endif; ?>
			<form action="" method="post">
				<p>
					<label>Category</label><span><?php kb_render_categories_dropdown(); ?></span>
				</p>
				<p>
					<label for="stitle">Page Tittle* :</label>
					<span><input type="text" name="stitle" id="stitle" value="<?php	echo @$_REQUEST['stitle']; ?>"/></span>
				</p>
				<p>
					<label for="sdesc">Description* : </label>
					<span><input type="text" name="sdesc" id="sdesc" value="<?php echo @$_REQUEST['sdesc']; ?>"/></span>
				</p>
				<p>
					<label for="stags">Tags* : </label>
					<span><input type="text" name="stags" id="stags" value="<?php echo @$_REQUEST['stags']; ?>"/></span>
				</p>
				<p>
					<label for="surl">Page URL* :</label>
					<span><input type="text" name="surl" id="surl" value="<?php	echo @$_REQUEST['surl']; ?>"/></span>
				</p>
				<p>
					<label for="sauthor">Author* :</label>
					<span><input type="text" name="sauthor" id="sauthor" value="<?php echo @$_REQUEST['sauthor']; ?>"/></span>
				</p>
				<p>
					<label for="stwitter">Twitter Information page of author:</label>
					<span><input type="text" name="stwitter" id="stwitter" value="<?php	echo @$_REQUEST['stwitter']; ?>"/></span>
				</p>
				<p>
					<label for="sfacebook">Facebook information page of author:</label>
					<span><input type="text" name="sfacebook" id="sfacebook" value="<?php echo @$_REQUEST['sfacebook']; ?>"/></span>
				</p>
				<p>
					<label for="ssugg">&nbsp;</label>
					<span><input type="submit" name="ssugg" id="ssugg" value="Add New Suggest"/></span>
				</p>
			</form>
		</div>
	</div>
	<?php
}
add_shortcode( 'suggest-page', 'kb_shortcode_render_suggest_page' );



add_action('template_redirect', function(){
	if ( ! empty( $_GET['kbt'] ) ) {
		kb_includeVar(
			WPKB_PATH . '/templates/suggest.php',
			array()
		);
		exit;
	}
});



/**
 * Render Categories dropdown
 */
function kb_render_categories_dropdown(){
	$subcategories = kb_get_subcategories(kb_get_current_cat_id());
	$current_subcat = kb_get_current_subcat();
	?>
	<select name="sid_cat" id="sid_cat">
		<?php foreach ( $subcategories as $subcat ): ?>
			<?php $subcategory = kb_get_current_subcat(); ?>
			<?php $class = $current_subcat == $subcat['alias_name'] ? 'selected="selected"' : ''; ?>
			<option <?php echo $class ?> style="padding-left:20px;" value="<?php echo esc_attr( $subcat['alias_name'] );?>"><?php echo esc_html( $subcat['name'] ); ?></option>
		<?php endforeach; ?>
	</select><?php
}
