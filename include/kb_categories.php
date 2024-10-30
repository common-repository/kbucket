<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function kb_get_first_category(){
	$menu = kb_get_menu();

	if(isset($menu['categories']) && count($menu['categories'])) $cat = reset($menu['categories']);
	else $cat = 0;

	return $cat;
}

function kb_getCatByName($empty=false){
	global $wpdb;
	$sql = "SELECT name,description,image,parent_cat FROM {$wpdb->prefix}kb_category";
	$result = $wpdb->get_results($sql);
	$new_res = array();
	foreach ($result as $key => $cat) {
		if(!$cat->description && !$empty) continue; // Pass empty description
		if(!$cat->parent_cat) continue; // Pass parent category
		$new_res[$cat->name]['description'] = $cat->description;
		$new_res[$cat->name]['image'] = $cat->image;
	}
	return $new_res;
}

function kb_get_first_subcategory( $categoryId = false ){
	$menu = kb_get_menu();

	if(isset($menu['subcategories']) && count($menu['subcategories']) && !empty($menu['subcategories'][$categoryId])) $subcat = $menu['subcategories'][$categoryId];
	else $subcat = 0;

	return $subcat;
}

function kb_get_subcat_by_cat_slug($cat_slug){
	$menu = kb_get_menu();
	if(isset($menu['categories']) && count($menu['categories']) && !empty($menu['categories'][$cat_slug])) $cat = $menu['categories'][$cat_slug];

	if(!empty($cat)){
		$subcat_slug = reset($cat->subcategories);
	}else $subcat_slug = 0;
	return $subcat_slug;

}



function kb_get_current_subcat(){
	$url_parts = kb_parse_url();

	$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;

	if(!$category){
		$category = kb_get_first_category();

	}

	$subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;
	if(!$subcategory){
		// TODO SLUG
		if(!empty($category->subcategories) && count($category->subcategories)){
			$subcategory = reset($category->subcategories);
			$subcategory = kb_get_first_subcategory($subcategory);
			$subcategory = $subcategory->alias;
		}else{
			$subcategory = kb_get_subcat_by_cat_slug($category);
		}
	}

	return $subcategory;
}

function kb_get_subcat_by_slug($slug) {
	$menu = kb_get_menu(true);
	if(!empty($menu['subcategories']) && !empty($menu['subcategories'][$slug]))
		return $menu['subcategories'][$slug];
	return false;
}



function kb_get_current_subcat_url($_category = '', $_sub_category = ''){
	$url_parts = kb_parse_url();

	$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
	if(!empty($_category)) $category = esc_attr($_category);

	if(!$category){
		$category = kb_get_first_category();
	}

	$subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;
	if(!empty($_sub_category)) $subcategory = esc_attr($_sub_category);
	if(!$subcategory){
		// TODO SLUG
		if(!empty($category->subcategories) && count($category->subcategories)){
			$subcategory = reset($category->subcategories);
			$subcategory = kb_get_first_subcategory($subcategory);
		}else{
			$subcategory_slug = kb_get_subcat_by_cat_slug($category);
			$subcategory = kb_get_first_subcategory($subcategory_slug);
		}
	}else{
		$subcategory = kb_get_first_subcategory($subcategory);
	}


	return $subcategory;
}




function get_category_id_by_slug($slug){
	global $wpdb;

	$slug = esc_sql($slug);

	$sql = "SELECT id_cat FROM {$wpdb->prefix}kb_category WHERE alias_name = '$slug'";
	return $wpdb->get_results($sql);
}


function kb_get_current_cat(){
	$url_parts = kb_parse_url();

	$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;

	if(!$category){
		$category = kb_get_first_category();
		return $category->alias;
	}
	return $category;
}


function kb_get_current_cat_id(){
	$url_parts = kb_parse_url();

	$category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;

	if($category === 0){
		$category = kb_get_first_category();
		return $category->id_cat;
	}else{
		$cat = get_category_id_by_slug($category);
		if(isset($cat[0]->id_cat)) return $cat[0]->id_cat;
	}
}


/**
 * Get categories or subcategories depending from $id_parent argument
 * Result can be filtered with columns returned
 * @param int $id_parent
 * @param bool $columns
 * @return mixed
 */
function kb_get_subcategories( $id_parent = 0, $columns = false, $setsocial=false ) {
	global $wpdb;
	if ( ! $columns ) {
		$columns = 'id_cat,name,level,image,description,alias_name';
	}

	if($id_parent == '0'){
		if($setsocial) $sql = "SELECT $columns	FROM {$wpdb->prefix}kb_category WHERE parent_cat=''";
		else $sql = "SELECT $columns FROM {$wpdb->prefix}kb_category WHERE parent_cat='' AND id_cat IN	(SELECT parent_cat FROM {$wpdb->prefix}kbucket a INNER JOIN {$wpdb->prefix}kb_category b ON a.id_cat=b.id_cat WHERE level=2	GROUP BY parent_cat)";
		$sql = "SELECT $columns	FROM {$wpdb->prefix}kb_category WHERE parent_cat=''";
	}else{
		$id_parent = esc_sql( $id_parent );
		if($setsocial) $sql = "SELECT $columns FROM {$wpdb->prefix}kb_category WHERE parent_cat='$id_parent'";
		else $sql = "SELECT $columns FROM {$wpdb->prefix}kb_category WHERE parent_cat='$id_parent' AND alias_name IN(SELECT id_cat FROM {$wpdb->prefix}kbucket GROUP BY id_cat)";
	}

	$res = $wpdb->get_results( $sql, ARRAY_A );

	return $res;
}


function kb_get_categories(){
	global $wpdb;
	return $wpdb->get_results( "SELECT id_cat,name,level FROM {$wpdb->prefix}kb_category" );
}

function kb_get_categories_dropdown() {

	$return_text = '';
	$settings = kb_get_settings();
	$settings = $settings['hidden_categories'];

	$categories = kb_get_subcategories( 0, false, true );
	if ( is_array( $categories ) && count( $categories ) > 0 ) {
		foreach ( $categories as $category ) {
			$id_category = $category['id_cat'];
			$category_name = $category['name'];

			$return_text .= '<tr><th colspan="3" class="kbucket-text-left-h40">'.ucfirst( $category_name ).'</th></tr>';
			$categories2 = kb_get_subcategories( $id_category, false, true );

			foreach ( $categories2 as $category2 ) {

				$return_text .= '<tr><td>----' . ucfirst( $category2['name'] ) . '</td>
								<td><textarea style="width:100%;" name="description[]">' . $category2['description'] . '</textarea>
								<input type="hidden" name="idArr[]" value="' . $category2['id_cat'] . '" />
								</td>
							<td style="position:relative;width:20%;"><input type="hidden" value="" name="upload_file[]"/><button style="position:relative;z-index:2;" class="upload_image_cat button"><i class="fa fa-camera"></i></button>';
				if ( ! empty( $category2['image'] ) ) {
					$return_text .= '<div style="background-image:url('. $category2['image'] .');width:70px;height:70px;background-repeat:no-repeat;background-size:cover;position:absolute;right:0;top:0;"></div>';
				}
				$return_text .= '</td>';

				$return_text .= '<td style="position:relative;width:20%;">
					<input 
						type="checkbox" 
						'.(isset($settings[$category2['id_cat']]) && $settings[$category2['id_cat']] ? 'checked':'').' 
						value="1" 
						name="hidden['.$category2['id_cat'].']" />
					</td>';
				$return_text .= '</tr>';
			}
		}
	} else {
		return false;
	}

	return $return_text;
}


function kb_get_categories_like_name($name){
	global $wpdb;

	$name = urldecode( $name );
	$sql = "SELECT id_cat,name,alias_name FROM {$wpdb->prefix}kb_category WHERE parent_cat=0 AND name like '%%%s%%'";

	$query = $wpdb->prepare( $sql, $name );
	return $wpdb->get_results( $query );
}

function kb_get_subcategories_like_name($name){
	global $wpdb;

	$name = urldecode( $name );
	$sql = "
		SELECT
			c.id_cat AS parid,
			b.id_cat AS subid,
			c.name AS parcat,
			b.name AS subcat,
			b.alias_name AS alias,
			c.alias_name AS palias
		FROM {$wpdb->prefix}kb_category b
		INNER JOIN {$wpdb->prefix}kb_category c	ON c.id_cat = b.parent_cat
		WHERE b.parent_cat <> 0 AND b.name LIKE '%%%s%%'";

	$query = $wpdb->prepare( $sql, $name );
	return $wpdb->get_results( $query );
}

function kb_get_subcategories_like_alias($name){
	global $wpdb;

	$name = urldecode( $name );
	$sql = "
		SELECT
			c.id_cat AS parid,
			b.id_cat AS subid,
			c.name AS parcat,
			b.name AS subcat,
			b.description AS description,
			b.image AS image
		FROM {$wpdb->prefix}kb_category b
		INNER JOIN {$wpdb->prefix}kb_category c	ON c.id_cat = b.parent_cat
		WHERE b.parent_cat IS NOT NULL AND b.alias_name LIKE '%%%s%%'";

	$query = $wpdb->prepare( $sql, $name );
	return $wpdb->get_results( $query );
}


function kb_get_categories_like_alias($name){
	global $wpdb;

	$name = urldecode( $name );
	$sql = "
		SELECT
			id_cat,
			name,
			description,
			image
		FROM {$wpdb->prefix}kb_category
		WHERE alias_name LIKE '%%%s%%'";

	$query = $wpdb->prepare( $sql, $name );
	return $wpdb->get_results( $query );
}
