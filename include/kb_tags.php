<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function kb_render_kbucket_tags( $baseUrl, $tagsStr, $class = '' ){
    $tagsArr = explode( '~', $tagsStr );
    $tagsStr = '';
    $attr = kb_is_mobile() ? ' data-ajax="false"' : '';



    foreach ( $tagsArr as $tag ) {
        $tag = explode( '|', $tag );
        if ( ! empty( $tag[1] ) ) {
            $tagsStr .= '<a class="kb-tag-link' . $class . '" href="'. kb_create_url_tag('c-tag', $tag[1]) .'"'.$attr.'>'.esc_html( $tag[1] ).'</a>';

            if ( !kb_is_mobile() ) {
                $tagsStr .= ',';
            }

            $tagsStr .= ' ';
        }
    }
    $tagsStr = trim( $tagsStr );
    if ( $tagsStr !== '' ) {
        echo substr( $tagsStr, 0, -1 );
    }
}


function kb_create_url_tag($tag_key, $tag){
    $tag = sanitize_title_with_dashes($tag);
    $parts = kb_parse_url();
    $home = get_bloginfo('url') . DIRECTORY_SEPARATOR;
    $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    if(!isset($parts['cat']) || !isset($parts['subcat'])){
        $category = kb_get_first_category();
        if(!empty($category->subcategories) && count($category->subcategories)){
            $subcategory = reset($category->subcategories);
        }
        $category = sanitize_title_with_dashes($category->name);
    }else{
        $category = $parts['cat'];
        $subcategory = $parts['subcat'];
    }

    $full_url = $home .
        $parts['parent'] . DIRECTORY_SEPARATOR .
        (!empty($category) ? $category . DIRECTORY_SEPARATOR : '') .
        (!empty($subcategory) ? $subcategory . DIRECTORY_SEPARATOR : '') .
        $tag_key . DIRECTORY_SEPARATOR .
        $tag . DIRECTORY_SEPARATOR;

    return $full_url;
}



function kb_get_category_tags($category = null, $blog_id = null){
    global $wpdb;
	if(!$blog_id) $blog_id = get_current_blog_id();
	$prefix = $wpdb->get_blog_prefix($blog_id);
    $sql = "
		SELECT t.id_tag,
				td.name,
				k.title,
				COUNT(t.id_tag) AS mcnt
		FROM {$prefix}kbucket k
		JOIN {$prefix}kb_tags t ON k.id_kbucket=t.id_kbucket
		JOIN {$prefix}kb_tag_details td ON td.id_tag=t.id_tag
		WHERE k.id_cat=%s AND td.name IS NOT NULL
		GROUP BY td.name
		ORDER BY td.name";

	if(!$category){
		$url_parts = kb_parse_url();

		if(isset($url_parts['subcat'])){
			$category = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;
		}else{
			$category = kb_get_current_subcat();
		}
	}

    if(!empty($category)){
	    $subcategory_id = kb_get_subcat_by_slug($category);
        // $values = array( $subcategory_id->id_cat );
        // $query = $wpdb->prepare( $sql, $values );
        // return $wpdb->get_results( $query );

        if(!empty($subcategory_id)){
            $values = array( $subcategory_id->id_cat );
            $query = $wpdb->prepare( $sql, $values );
            return $wpdb->get_results( $query );
        }else{
            return ;
        }
    }else return;
}


function kb_get_active_tagname(){
    $url_parts = kb_parse_url();

    if(isset($url_parts['a-tag'])) return urldecode($url_parts['a-tag']);
    if(isset($url_parts['p-tag'])) return urldecode($url_parts['p-tag']);
    if(isset($url_parts['c-tag'])) return urldecode($url_parts['c-tag']);
    if(isset($url_parts['r-tag'])) return urldecode($url_parts['r-tag']);
    return 0;
}

function kb_get_active_related_tagname(){
	$url_parts = kb_parse_url();
	if(isset($url_parts['r-tag'])) return urldecode($url_parts['r-tag']);
	return 0;
}

function kb_has_tag_tagcloud($tag, $tag_cloud) {
	if(!$tag) return false;

	if(!empty($tag_cloud) && is_array($tag_cloud)) {
		foreach ( $tag_cloud as $item ) {
			if(!empty($item->name) && $tag == sanitize_title_with_dashes($item->name)) return true;
			if(!empty($item->publisher) && $tag == sanitize_title_with_dashes($item->publisher)) return true;
			if(!empty($item->author) && $tag == sanitize_title_with_dashes($item->author)) return true;
		}
	}
	return false;
}

function kb_get_active_tag_id(){
    $tagname = kb_get_active_tagname();

    if(!empty($tagname)){

    }else return false;
}


function kb_render_tags_cloud( $tagsArr, $sfont, $stag, $activeTag, $linkParams, $return = false ){
    if ( empty( $tagsArr ) ) return;

    if($return) $output = '';

    if($return) $output .= '<div class="kb-tags">';else echo '<div class="kb-tags">';

    $settings = kb_get_settings();
    $url_parts = kb_parse_url();
    foreach ( $tagsArr as $i => $tag ):

        if(isset($tag->name) && empty($tag->name)) continue;


        $fontSize = (int)$settings['fz_tag_cloud'] ?? 10;
	    if(!$fontSize) $fontSize = 10;
        $tagsCount = (int) $tag->mcnt;

        if ( $tagsCount > 3 and $tagsCount <= 15 ) {
            $fontSize += 2;
        } else if ( $tagsCount > 15 and $tagsCount <= 25 ) {
            $fontSize += 3;
        } else if ( $tagsCount > 25 ) {
            $fontSize += 4;
        }

        if(isset($tag->name)){
            $custom_value = sanitize_title_with_dashes($tag->name);
            $custom_name = $tag->name;
        }elseif(isset($tag->author)){
            $custom_value = sanitize_title_with_dashes($tag->author);
            $custom_name = $tag->author;
        }elseif(isset($tag->publisher)){
            $custom_value = sanitize_title_with_dashes($tag->publisher);
            $custom_name = $tag->publisher;
        }

        if ( 'related' == $stag && isset($url_parts['r-tag'])) $activeTag['value'] = $url_parts['r-tag'];

		$active = isset($custom_value) && $activeTag['value'] && $activeTag['value'] == $custom_value ? true : false;
        $linkStyle = $active ? 'color:#fff!important;font-size:'.$fontSize.'px;background-color:#5269ac' :'font-size:'.$fontSize.'px;';

        if ( 'related' == $stag ){
            $linkUrl = kb_create_url_tag('c-tag',$url_parts['c-tag']);
           // echo $activeTag['value'] .'!='. $custom_value;
            if ($activeTag['value'] !== $custom_value) {
               
                $linkUrl = $linkUrl . 'r-tag' . DIRECTORY_SEPARATOR . $custom_value . DIRECTORY_SEPARATOR;
            }
        }else{
            $linkUrl = kb_create_url_tag($linkParams,$custom_value);
        };

        if (isset($custom_value) && $activeTag['value'] && $activeTag['value'] == $custom_value && 'related' != $stag) {
            $parts = kb_parse_url();
            $home = get_bloginfo('url') . DIRECTORY_SEPARATOR;
            $linkUrl = $home . $parts['parent'] . DIRECTORY_SEPARATOR . $parts['cat'] . DIRECTORY_SEPARATOR . $parts['subcat'] . DIRECTORY_SEPARATOR;
        }
        ?>

        <?php if($return): ?>
            <?php $output .= '<a 
            	class="'.($active ? 'active-tag' : '').'"
            	data-mcnt="'.(!empty($tag->mcnt) ? $tag->mcnt : '0').'" 
            	href="'.esc_attr( $linkUrl ).'" 
            	style="'.esc_attr( $linkStyle ).'"'.( kb_is_mobile() ? ' class="ui-btn" data-ajax="false"' : '').'>'.esc_html( $custom_name ).'</a>';
        else: ?>
            <a
				class="<?php echo $active ? 'active-tag' : '' ?>"
				data-mcnt="<?=(!empty($tag->mcnt) ? $tag->mcnt : '0') ?>"
				href="<?php echo esc_attr( $linkUrl ); ?>"
				style="<?php echo esc_attr( $linkStyle ); ?>"<?php if ( kb_is_mobile() ) {echo ' class="ui-btn" data-ajax="false"';} ?>><?php echo esc_html( $custom_name ); ?></a>
        <?php endif; ?>
    <?php endforeach;

    if($return) $output .= '</div>';else echo '</div>';

    if($return) return $output;

}


function kb_get_related_tags( $catID, $activeTagName, $blog_id = 1 ){
    global $wpdb;

    $settings = kb_get_settings();
    $related_no_tags = !empty($settings['related_no_tags']) ? $settings['related_no_tags'] : 100;
	$prefix = $wpdb->get_blog_prefix($blog_id);
    $sql = "
		SELECT
			t.id_tag,
			d.name,
			k.title,
			k.id_kbucket,
			COUNT(t.id_tag) AS mcnt
		FROM {$prefix}kb_tag_details d
		JOIN {$prefix}kb_tags t ON t.id_tag=d.id_tag
		JOIN {$prefix}kbucket k ON k.id_kbucket=t.id_kbucket
		WHERE k.id_cat=%s AND d.alias_name!=%s AND k.id_kbucket IN (
			SELECT
				t.id_kbucket
			FROM {$prefix}kb_tag_details d
			JOIN {$prefix}kb_tags t ON t.id_tag=d.id_tag
			WHERE d.alias_name=%s
		)
		GROUP BY d.name
		ORDER BY d.name
		LIMIT 0, {$related_no_tags}";

	if(!empty($catID)){
		$catID = kb_get_subcat_by_slug($catID);
		$catID = $catID->id_cat;
	}
    $query = $wpdb->prepare( $sql, array( $catID, $activeTagName, $activeTagName ) );
    return $wpdb->get_results( $query );
}


function kb_get_author_tags($sub_category = null){
    global $wpdb;
	if(!empty($GLOBALS['current_blog_id'])) $prefix = $wpdb->get_blog_prefix($GLOBALS['current_blog_id']);
	else $prefix = $wpdb->prefix;

	$sql = "
		SELECT k.author,
		(
			SELECT COUNT(*)
			FROM {$prefix}kb_tags
			WHERE status='1' AND id_kbucket=k.id_kbucket
		) AS mcnt
		FROM {$prefix}kbucket k
		WHERE k.id_cat=%s AND k.author!=''
		GROUP BY author
		ORDER BY author";

	if(!empty($sub_category)){
		$sub_category = kb_get_subcat_by_slug($sub_category);
	}else{
		$sub_category = kb_get_subcat_by_slug(kb_get_current_subcat());
	}
	if(!empty($sub_category)) $sub_category = $sub_category->id_cat;
    $values = $sub_category ?? array();
    $query = $wpdb->prepare( $sql, $values );
    return $wpdb->get_results( $query );
}

add_action('wp_ajax_kb_get_author_publisher_tags', 'kb_get_ajax_author_tags');
function kb_get_ajax_author_tags() {
	$sub_category = esc_attr($_REQUEST['sub-category']);
	die(json_encode([
		'author' => kb_get_author_tags($sub_category),
		'publisher' => kb_get_publisher_tags($sub_category)
	]));
}

function kb_get_publisher_tags($sub_category = null){
    global $wpdb;
	if(!empty($GLOBALS['current_blog_id'])) $prefix = $wpdb->get_blog_prefix($GLOBALS['current_blog_id']);
	else $prefix = $wpdb->prefix;

	$sql = "
	SELECT DISTINCT
		k.publisher,
		(
			SELECT COUNT(*)
			FROM {$prefix}kb_tags
			WHERE status='1' AND id_kbucket=k.id_kbucket
		) AS mcnt
		FROM {$prefix}kbucket k
		WHERE k.id_cat=%s AND k.publisher!=''
		GROUP BY publisher
		ORDER BY publisher ASC";

	if(!empty($sub_category)){
		$sub_category = kb_get_subcat_by_slug($sub_category);
	}else{
		$sub_category = kb_get_subcat_by_slug(kb_get_current_subcat());
	}
	if(!empty($sub_category)) $sub_category = $sub_category->id_cat;
    $values = $sub_category ?? array();
    $query = $wpdb->prepare( $sql, $values );
    return $wpdb->get_results( $query );
}
