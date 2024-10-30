<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


$srch = trim( substr( sanitize_text_field($_REQUEST['srch']),0, 100 ) );
if ( $srch == '' ) return false;


$srch = urldecode( $srch );
$highlight = '<span class="kb-hlt">'.$srch.'</span>';

if ( strlen( $srch ) < 2 ):?>
	<h1 class="page-title kb-search-title">No results found for your search</h1>
	<?php return false; ?>
<?php endif; ?>


<h1 class="page-title kb-search-title">Searching in Kbucket, Cabinet, Drawer, Link for <b><?php echo esc_html( $srch ); ?></b></h1>

<?php

// Search in category
$data = kb_get_categories_like_name($srch);

foreach ( $data as $m ): ?>
	<div class="kb-item kb-item-category">
		<h3>
			<span style="background-color: #217BBA;color: #FFFFFF;padding: 1px 3px;">KBUCKET</span>
			<a href="<?php echo KBUCKET_URL . '/' . $m->alias_name; ?>" class="highlight"><?php echo str_ireplace( $srch, $highlight, $m->name ); ?></a>
		</h3>
	</div>
<?php endforeach;


// Search in subcategory
$data = kb_get_subcategories_like_name($srch);

foreach ( $data as $m ): ?>
	<div class="kb-item kb-item-subcategory">
		<h3>
			<span style="background-color: #217BBA;color: #FFFFFF;padding: 1px 3px;">KBUCKET</span>
			<a href="<?php echo KBUCKET_URL . '/' .$m->palias; ?>"><?php echo str_ireplace( $srch, $highlight, $m->parcat ); ?></a>
			/
			<a href="<?php echo KBUCKET_URL . '/' . $m->alias . '/' . $m->palias ?>"><?php echo str_ireplace( $srch, $highlight, $m->subcat ); ?></a>
		</h3>
	</div>
<?php
endforeach;


// Search in kbuckets
$data = kb_get_kbuckets_like_name($srch);

$i = 0;
foreach ( $data['kb'] as $m ) {
	if ( empty( $m->post_id ) ) {
		$shareData = kb_get_kbucket_share_data( $m );
	} else {
		$post = get_post( $m->post_id );
		// Get sharing data
		$shareData = array(
			'url' => $m->url_kbucket,
			'imageUrl' => $m->image_url,
			'title' => $m->title,
			'description' => $m->description,

		);
	}

	$url = WPKB_SITE_URL . '/' . $shareData['url'];

	$i ++;
	$description = wp_kses($m->description,
		array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'br' => array(),
		)
	);?>
	<div id="kb-item-<?php echo esc_attr( $m->id_kbucket );?>" class="kb-item kb-search-item kb-item-kbucket">

		<h3>
			<span style="background-color: #217BBA;color: #FFFFFF;padding: 1px 3px; line-height:35px;">KBUCKET</span>
			<a href="<?php echo KBUCKET_URL . '/' . $m->parid; ?>"><?php echo str_ireplace( $srch, $highlight, $m->parcat ); ?></a>
			/
			<a href="<?php echo KBUCKET_URL  . '/' . $m->parid . '/' . $m->subid;?>"><?php echo str_ireplace( $srch, $highlight, $m->subcat ); ?></a>
			/
			<a href="<?php echo esc_attr( $m->link ); ?>" target="_blank"><b><?php echo str_ireplace( $srch, $highlight, $m->title ); ?></b></a>
		</h3>

		<div class="wrap_kb_search_misc">
			<span>Publisher: <?php echo str_ireplace( $srch, $highlight, $m->author ); ?></span>
			<span class="kb-item-date"><?php echo esc_html( $m->postedDate ); ?></span>
		</div>

		<p>
			<span>
				<?php echo str_ireplace( $srch, $highlight, substr( $description, 0, 100 ) );?>
			</span>

			<?php if ( strlen( $description ) > 100 ): ?>
				<span style="display:none;" id="kb-item-text-<?php echo (int) $i; ?>">
					<?php echo str_ireplace( $srch, $highlight, ( substr($description, 100, strlen( $description ) ) )); ?>
				</span>

				<a href="<?=$url ?>" data-slug="<?php echo esc_attr( $m->short_url ); ?>" id="kb-share-item-<?php echo esc_attr( $m->id_kbucket ); ?>" class="kb-share-item" title="<?php echo esc_html( $m->title ); ?>">
					<img src="<?php echo WPKB_PLUGIN_URL . '/images/kshare.png'; ?>" alt="Share Button"/>
				</a>
			<?php endif; ?>
		</p>

		<p class="blue" style=" margin-bottom:2px;">
			<span>Tags: <?php echo str_ireplace( $srch, $highlight, $data['tag'][ $m->id_kbucket ] ); ?></span>
		</p>

	</div>
<?php }

$res = kb_create_search_condition( $srch, array( 'post_title', 'post_content' ), array( '=', 'LIKE' ) );
$conditions = $res['conditions'];
$values = $res['values'];
$data = kb_get_search_item($conditions, $values);


foreach ( $data as $m ): ?>
	<div class="kb-item kb-item-external-link">
		<h3>
			<span style="background-color: #217BBA;color: #FFFFFF;padding: 1px 3px;">LINK</span>
			<a href="<?php echo esc_attr( get_permalink( $m->ID ) ); ?>" target="_blank"><?php echo str_ireplace( $srch, $highlight, $m->post_title ); ?></a>
		</h3>
		<p>Publisher: <?php echo str_ireplace( $srch, $highlight, $m->user_nicename ); ?></p>
		<p>Date:<?php echo esc_html( $m->dt ); ?></p>
		<span><?php	echo str_ireplace( $srch, $highlight, substr( strip_tags( $m->post_content ), 0, 300 ) );?></span>
	</div>
<?php endforeach; ?>
