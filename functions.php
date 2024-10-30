<?php

  //die($makeUrl);
    if ( !defined( 'ABSPATH' ) ) exit;

    function kbucket_url_origin( $s, $use_forwarded_host = false ){
        $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
        $sp       = strtolower( $s['SERVER_PROTOCOL'] );
        $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
        $port     = $s['SERVER_PORT'];
        $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
        $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
        $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    function kbucket_utf8_urldecode($str) {
        return html_entity_decode(htmlspecialchars_decode(stripslashes(urldecode($str))), null, 'UTF-8');
    }

    // Hide template select in kbucket admin page
    function kbucket_hide_template_add_custom_box(){
        global $post;
        $kb_page_id = kbucketPageID();
        if($kb_page_id && $post->ID == (int)$kb_page_id){
            add_meta_box(
                'pageparentdiv',
                __('Page Attributes'),
                'kbucket_hide_template_attributes_meta_box',
                'page',
                'side'
            );
        }
    }
    add_action('add_meta_boxes', 'kbucket_hide_template_add_custom_box');

    /* START Cache feed disabled */

        if( defined('WP_DEBUG') && WP_DEBUG ){
            add_action('wp_feed_options', function( &$feed ){
                $feed->enable_cache(false);
            });

	        add_filter( 'wp_headers', function($headers){
		        if( !empty($GLOBALS['wp']->query_vars['feed']) ){
			        unset( $headers['ETag'], $headers['Last-Modified'] );
		        }

		        return $headers;
	        });
        }

    /* END Cache feed disabled */

    function kbucket_hide_template_attributes_meta_box($post) {

        $post_type_object = get_post_type_object($post->post_type);
        if ( $post_type_object->hierarchical ) {
            $dropdown_args = array(
                'post_type'        => $post->post_type,
                'exclude_tree'     => $post->ID,
                'selected'         => $post->post_parent,
                'name'             => 'parent_id',
                'show_option_none' => __('(no parent)'),
                'sort_column'      => 'menu_order, post_title',
                'echo'             => 0,
            );

            $dropdown_args = apply_filters( 'page_attributes_dropdown_pages_args', $dropdown_args, $post );
            $pages = wp_dropdown_pages( $dropdown_args );
            if ( ! empty($pages) ) { ?>
                <p><strong><?php _e('Parent') ?></strong></p>
                <label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>
                <?php echo $pages; ?>
                <?php
            } // end empty pages check
        } // end hierarchical check.
        if ( 'page' == $post->post_type && 0 != count( get_page_templates() ) ) {
            $template = !empty($post->page_template) ? $post->page_template : false;
        } ?>
        <p><strong><?php _e('Order') ?></strong></p>
        <p><label class="screen-reader-text" for="menu_order"><?php _e('Order') ?></label><input name="menu_order" type="text" size="4" id="menu_order" value="<?php echo esc_attr($post->menu_order) ?>" /></p>
        <p><?php if ( 'page' == $post->post_type ) _e( 'Need help? Use the Help tab in the upper right of your screen.' ); ?></p>
        <?php
    }

    /**
     * @param $wp_rewrite
     */
    function kbucket_rewrite_rules( $wp_rewrite ) {
        $kb_page_id = kbucketPageID();
        $post = get_post($kb_page_id);
        $slug = ($post && isset($post->post_name)) ? $post->post_name : 'kbucket';

        $kbucketRules = array(
            "^$slug/(.*)" => "index.php?pagename=$slug",
        );

        $wp_rewrite->rules = $kbucketRules + $wp_rewrite->rules;
    }
    // Add custom rewrite rules for SEO links
    add_action( 'generate_rewrite_rules', 'kbucket_rewrite_rules' );

    //function kbucket_custom_excerpt_length() {
    //	return 2000;
    //}
    //add_filter( 'excerpt_length', 'kbucket_custom_excerpt_length', 999 );

    function kbucket_strip_cdata($string){
        $matches = $matches1 =  array();

        preg_match_all('/&lt\;!\[CDATA\[(.*?)\]\]\&gt\;&gt\;/is', $string, $matches);
        if(isset($matches[0][0]) && isset($matches[1][0])) $new_string = str_replace($matches[0][0], $matches[1][0], $string);

        if(isset($new_string)){
            preg_match_all('/<!\[cdata\[(.*?)\]\]>/is', $new_string, $matches1);
            if(isset($matches1[0][0]) && isset($matches1[1][0])) $new_string = str_replace($matches1[0][0], $matches1[1][0], $new_string);
            return str_replace(']]>', '', $new_string);
        }else return $string;

    }

    function kbucketPageID(){
        $kpost = get_post(get_option('kb_setup_page_id','kbucket'));
        if(!isset($kpost->post_name)) $kpost = get_page_by_path('kbucket', OBJECT, 'page');
        if(!is_object($kpost)) return false;
        else return $kpost->ID;
    }

    // Add status label for kbucket page in admin list pages
    add_filter( 'display_post_states','kbucket_post_state');
    function kbucket_post_state( $states ) {
        global $post;
        $kpageID = kbucketPageID();

        if(!$kpageID) return $states;
        if ( $post->ID == $kpageID ) {
            $states[] = '<span class="custom_state kbucket">'.__('Kbucket page',WPKB_TEXTDOMAIN).'</span>';
        }
        return $states;
    }

    function is_vc_activate(){
        $settings = kb_get_settings();
        if(!empty($settings['site_vc'])){
            if((int)$settings['site_vc']) return true;
            else return false;
        }else return false;
    }

    function is_kbucket_page_func(){
        global $wp;

        $slugsArr = explode( '/', $wp->request );

        $kbucketSlug = array_shift( $slugsArr );

        $kb_page_id = kbucketPageID();
        $post = get_post($kb_page_id);
        $slug = $post->post_name;

        if ( !empty( $kbucketSlug ) && $slug == $kbucketSlug ) {
            return true;
        }

        return false;
    }

    function kb_is_mobile(){
        return false;
//        if ( wp_is_mobile() && !isset($_GET['mobile']) ) {
//            return true;
//        } elseif ( isset( $_COOKIE['kb-mobile'] ) && 'y' == $_COOKIE['kb-mobile'] ) {
//            return true;
//        }
//
//        if ( isset( $_GET['mobile'] ) ) {
//            $switch = $_GET['mobile'];
//            if ( $switch = 'n' ) {
//                return false;
//            }
//        }
//
//        if ( ( isset( $_GET['mobile'] ) && 'y' == $_GET['mobile'] ) ) {
//            return true;
//        }
    }

    function kb_get_file_param(){
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}kb_files LIMIT 0,1";
        return $wpdb->get_results($sql, ARRAY_A);
    }

    function kb_get_menu($show_empty = false){
        global $wpdb;
		if(!empty($GLOBALS['current_blog_id'])) $prefix = $wpdb->get_blog_prefix($GLOBALS['current_blog_id']);
		else $prefix = $wpdb->prefix;
		$hidden = kb_get_settings();
	    $hidden = $hidden['hidden_categories'] ?? [];
        //Set menu data
        $sql = "
            SELECT
                c.id_cat AS categoryId,
                c.name AS categoryName,
                c.image AS categoryImage,
                c.description AS categoryDescription,
                c.keywords AS categoryKeywords,
                c.alias_name AS alias,
                s.id_cat AS subcategoryId,
                s.name AS subcategoryName,
                s.image AS subcategoryImage,
                s.description AS subcategoryDescription,
                s.keywords AS subcategoryKeywords,
                s.alias_name AS sub_alias,
                s.parent_cat
            FROM {$prefix}kb_category c
            LEFT JOIN {$prefix}kb_category s ON s.parent_cat=c.id_cat
            WHERE c.parent_cat='' ORDER BY c.keywords ASC";

        $res = $wpdb->get_results( $sql );

        $menu = array( 'categories' => array(), 'subcategories' => array() );

        foreach ( $res as $cat ) {
	        if(!$show_empty && isset($hidden[$cat->subcategoryId]) && $hidden[$cat->subcategoryId]) continue;
            $cat_slug = sanitize_title_with_dashes( $cat->categoryName );
            if ( ! isset( $menu['categories'][ $cat_slug ] ) ) {
                $category = new stdClass();
                $category->id_cat = $cat->categoryId;
                $category->url = KBUCKET_URL . '/' . sanitize_title_with_dashes( $cat->categoryName ) . '/';
                $category->route = sanitize_title_with_dashes( $cat->categoryName ) . '/kc/' . $cat->categoryId . '/';
                $category->name = $cat->categoryName;
                $category->image = $cat->categoryImage;
                $category->description = $cat->categoryDescription;
                $category->keywords = !empty($cat->categoryKeywords) ? $cat->categoryKeywords : '';
                $category->alias = $cat->alias;
                $category->subcategories = array();
                $menu['categories'][ $cat_slug ] = $category;
            }

            $subcategory = new stdClass();
            $subcategory->id_cat = $cat->subcategoryId;
            $subcategory->url = KBUCKET_URL.'/'.sanitize_title_with_dashes($cat->categoryName).'/'.sanitize_title_with_dashes($cat->subcategoryName).'/';
            $subcategory->route = sanitize_title_with_dashes($cat->categoryName).'/'.sanitize_title_with_dashes($cat->subcategoryName).'/';
            $subcategory->name = $cat->subcategoryName;
            $subcategory->image = $cat->subcategoryImage;
            $subcategory->description = $cat->subcategoryDescription;
            $subcategory->parent_cat = $cat->parent_cat;
            $subcategory->keywords = $cat->subcategoryKeywords;
            $subcategory->alias = $cat->sub_alias;

            $subcat_slug = sanitize_title_with_dashes($cat->subcategoryName);

            $menu['categories'][ $cat_slug ]->subcategories[] = $subcat_slug;
            $menu['subcategories'][ $subcat_slug ] = $subcategory;
        }

        return $menu;
    }

    function kb_parse_url(){
        $url_parts = preg_split("/\//", esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI'])));
	    $url_parts_exclude = preg_split("/\//", get_bloginfo( 'url' ));
        if ( empty($url_parts) ) $url_parts = explode(DIRECTORY_SEPARATOR, esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI'])));

        $val_parts = array();
        foreach ($url_parts as $part) {
            if(!empty($part) && !in_array($part, $url_parts_exclude)) $val_parts[] = $part;
        }

        if(!empty($val_parts[0])) $parts['parent'] = $val_parts[0];
        if(!empty($val_parts[1]) && stripos($val_parts[1], '?') === false ) $parts['cat'] = $val_parts[1];
        if(!empty($val_parts[2])) $parts['subcat'] = $val_parts[2];
        if(!empty($val_parts[3]) && substr($val_parts[3], 0,1) !== '?' && $val_parts[3] !== 'c-tag' && $val_parts[3] !== 'a-tag' && $val_parts[3] !== 'p-tag' && $val_parts[3] !== 'page' && $val_parts[3] !== 'r-tag') $parts['article'] = $val_parts[3];
        if(!empty($val_parts[3]) && $val_parts[3] == 'c-tag') $parts['c-tag'] = $val_parts[4];
        if(!empty($val_parts[3]) && $val_parts[3] == 'a-tag') $parts['a-tag'] = $val_parts[4];
        if(!empty($val_parts[3]) && $val_parts[3] == 'p-tag') $parts['p-tag'] = $val_parts[4];
        if(!empty($val_parts[4]) && $val_parts[3] == 'page') $parts['page'] = $val_parts[4];
        if(!empty($val_parts[6]) && $val_parts[5] == 'page') $parts['page'] = $val_parts[6];
        if(!empty($val_parts[5]) && $val_parts[5] == 'r-tag') $parts['r-tag'] = $val_parts[6];
	    if(!empty($_REQUEST['article'])) $parts['article'] = $_REQUEST['article'];

        if(!empty($parts)) return $parts;
        else return false;
    }

    function kb_set_page_url($pageNo){
        $parts = kb_parse_url();
        $home = get_bloginfo('url') . DIRECTORY_SEPARATOR;

        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $query_str = parse_url($actual_link, PHP_URL_QUERY);


        if(!isset($parts['cat']) && !isset($parts['subcat'])){
            $category = kb_get_first_category();
            if(!empty($category->subcategories) && count($category->subcategories)){
                $subcategory = reset($category->subcategories);
            }
            $category = sanitize_title_with_dashes($category->name);
        }elseif(isset($parts['cat']) && !isset($parts['subcat'])){
            $category = $parts['cat'];
            $subcategory = kb_get_current_subcat();
        }else{
            $category = $parts['cat'];
            $subcategory = $parts['subcat'];
        }

        return $home .
            $parts['parent'] . DIRECTORY_SEPARATOR .
            (!empty($category) ? $category . DIRECTORY_SEPARATOR : '') .
            (!empty($subcategory) ? $subcategory . DIRECTORY_SEPARATOR : '') .
            (isset($parts['c-tag']) ? 'c-tag' . DIRECTORY_SEPARATOR . $parts['c-tag'] . DIRECTORY_SEPARATOR : '') .
            (isset($parts['a-tag']) ? 'a-tag' . DIRECTORY_SEPARATOR . $parts['a-tag'] . DIRECTORY_SEPARATOR : '') .
            (isset($parts['p-tag']) ? 'p-tag' . DIRECTORY_SEPARATOR . $parts['p-tag'] . DIRECTORY_SEPARATOR : '') .
            'page' . DIRECTORY_SEPARATOR . $pageNo . DIRECTORY_SEPARATOR . (!empty($query_str) ? '?' . $query_str : '');

    }

	function kb_set_page_url_widget($pageNo){
		$parts = kb_parse_url();
		$home = get_bloginfo('url') . DIRECTORY_SEPARATOR;
		$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$query_str = parse_url($actual_link, PHP_URL_QUERY);
		return $home .
		   $parts['parent'] . DIRECTORY_SEPARATOR .
		   'page' . DIRECTORY_SEPARATOR . $pageNo . DIRECTORY_SEPARATOR . (!empty($query_str) ? '?' . $query_str : '');
	}

    /**
     * Get Kbuckets records by where criteria
     * @param $where
     * @return mixed -numerically indexed array of row objects.
     */
    function kb_get_kbuckets_db( $where ){
        global $wpdb;
        $sql = "
                SELECT a.id_kbucket,
                a.id_cat,
                a.title,
                a.description,
                a.link,
                a.author,
                a.twitter,
                a.publisher,
                a.add_date,
                a.pub_date,
                tags,
                a.image_url,
                a.short_url,
                a.post_id,
                c.name AS categoryName,
                a.url_kbucket
            FROM {$wpdb->prefix}kbucket a
            LEFT JOIN
            (
                SELECT a.id_kbucket,GROUP_CONCAT(name) as tags
                FROM {$wpdb->prefix}kbucket a
                LEFT JOIN {$wpdb->prefix}kb_tags b ON b.id_kbucket  = a.id_kbucket
                LEFT JOIN {$wpdb->prefix}kb_tag_details c ON c.id_tag = b.id_tag
                WHERE $where
            ) b ON a.id_kbucket = b.id_kbucket
            LEFT JOIN {$wpdb->prefix}kb_category c ON a.id_cat=c.id_cat
            WHERE $where";
        return $wpdb->get_results( $sql );
    }

    function kb_get_current_category()
    {
//        $cat_info = wp_cache_get('object', 'category');
        if ( ! empty( $cat_info ) ) {
            return $cat_info;
        }

        global $wpdb;

        $url_parts = kb_parse_url();

        $category = isset( $url_parts['cat'] ) ? $url_parts['cat'] : 0;

        if( ! $category ) {
            $category = kb_get_first_category();
        }

        $subcategory = isset( $url_parts['subcat'] ) ? $url_parts['subcat'] : 0;
        if( ! $subcategory ) {
            // TODO SLUG
            if( ! empty( $category->subcategories ) && count( $category->subcategories ) ) {
                $subcategory = reset($category->subcategories);
                $subcategory = kb_get_first_subcategory( $subcategory );
            }
        }

        if ( ! empty( $subcategory ) ){
            $current = $subcategory;
        } else {
            $current = $category;
        }

        $query = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}kb_category WHERE alias_name = %s", $current);
        $cat_info = $wpdb->get_row( $query, ARRAY_A );

//        wp_cache_set('object', $cat_info, 'category');

        return $cat_info;
    }

    function kb_get_kbuckets($args = []){
        global $wpdb;

        if ( is_kbucket_page_func() || is_vc_activate() || !empty($args) ) {

            $values = array();

            $settings = kb_get_settings();

            $url_parts = kb_parse_url();

            $category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
            $subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;

            $atag = isset($url_parts['a-tag']) ? urldecode($url_parts['a-tag']) : 0;
            $ptag = isset($url_parts['p-tag']) ? urldecode($url_parts['p-tag']) : 0;
            $ctag = isset($url_parts['c-tag']) ? urldecode($url_parts['c-tag']) : 0;
            $rtag = isset($url_parts['r-tag']) ? urldecode($url_parts['r-tag']) : 0;

			if(!empty($args)){
				if(!empty($args['category'])) $category =  $args['category'];
				if(!empty($args['sub-category'])) $subcategory =  $args['sub-category'];
				if(!empty($args['tag'])) $ctag =  sanitize_title($args['tag']);
				if(!empty($args['related'])) $rtag =  sanitize_title($args['related']);
				if(!empty($args['publisher'])) $ptag =  $args['publisher'];
				if(!empty($args['author'])) $atag =  $args['author'];
				if(!empty($args['count'])) $settings['no_listing_page'] = $args['count'];
			}

	        if(!$category){
		        $category = kb_get_first_category();
	        }
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

            //Build query to select Kbuckets
            $sql = "
                SELECT id_kbucket AS kbucketId,
                    id_cat,
                    title,
                    description,
                    link,
                    author,
                    publisher,
                    short_url,
                    pub_date AS add_date,
                    (
                        SELECT GROUP_CONCAT(CONCAT(t.`id_tag`, '|', td.`name`) SEPARATOR '~' )
                        FROM {$wpdb->prefix}kb_tags t
                        JOIN {$wpdb->prefix}kb_tag_details td	ON td.id_tag=t.id_tag
                        WHERE t.id_kbucket = kbucketId
                    ) AS tags
                FROM {$wpdb->prefix}kbucket WHERE ";


	        $subcategory_id = kb_get_subcat_by_slug($subcategory);
			if(!empty($subcategory_id->id_cat)){
				$where = "status='1' AND id_cat=%s ";
				$values[] = $subcategory_id->id_cat;
			}else{
				$where = "status='1'";
			}

            // Select by author name if has been passed
            if ( $atag ) {
                $authorTagName = urldecode( $atag );
                $where .= " AND (author_alias=%s OR author=%s) ";
                $values[] = $authorTagName;
                $values[] = $authorTagName;
            }

            // Select by publisher name if has been passed
            if ( $ptag ) {
                $publisherTagName = urldecode( $ptag );
                $where .= " AND (publisher_alias=%s OR publisher=%s) ";
                $values[] = $publisherTagName;
                $values[] = $publisherTagName;
            }



            if ( $ctag ) {
                $tagIds[] = $ctag;
                $where .= " AND id_kbucket
                        IN (
                            SELECT t.id_kbucket FROM {$wpdb->prefix}kb_tags t
                            JOIN {$wpdb->prefix}kb_tag_details td ON td.id_tag=t.id_tag
                            WHERE td.alias_name=%s)";
                $values[] = $ctag;
            }


            // Select by active and related tag name if both has been passed
            if ( $rtag ) {

                $relatedTagName = urldecode( substr( $rtag, 0 , 80 ) );

                $where .= " AND id_kbucket
                    IN (
                        SELECT t.id_kbucket FROM {$wpdb->prefix}kb_tags t
                        JOIN {$wpdb->prefix}kb_tag_details td ON td.id_tag=t.id_tag
                        WHERE td.alias_name=%s)";
                $values[] = $relatedTagName;
            }

            $sql .= $where;

            $countSql = "SELECT COUNT(*) FROM {$wpdb->prefix}kbucket WHERE {$where}";
            $countQuery = $wpdb->prepare( $countSql, $values );
            $kbucketsCount = $wpdb->get_var( $countQuery );

            $sortCols = array( 'author', 'add_date', 'title' );

            if ( ! empty( $_REQUEST['sort'] ) && in_array( $_REQUEST['sort'], $sortCols ) ) {
                if($_REQUEST['sort'] == 'author') $sql .= " ORDER BY publisher";
                else $sql .= " ORDER BY {$_REQUEST['sort']}";
                if ( isset( $_REQUEST['order'] ) && ( 'asc' == $_REQUEST['order'] || 'desc' == $_REQUEST['order'] ) ) {
                    $sql .= strtoupper(" {$_REQUEST['order']}");
                }
            } else {
                $sql .= " ORDER BY {$settings['sortBy']} {$settings['sortOrder']}";
            }

            $pagin = isset( $url_parts['page'] ) ? (int) $url_parts['page'] : 1;
	        preg_match_all('/\/page\/([0-9]+)/', esc_url_raw(sanitize_text_field($_SERVER['REQUEST_URI'])), $matches);
			if(!empty($matches[1][0])) $pagin = $matches[1][0];


            // Go to page with number
            if(!empty($url_parts['article'])){
                $kbucket = get_kbucket_by_slug($url_parts['article']);

                $query = $wpdb->prepare( $sql, $values );
                $all_kbuckets = $wpdb->get_results( $query );

                if(count($all_kbuckets)){
                    $kb_num = 0;
                    foreach ($all_kbuckets as $kb_key => $kb_item) {
                        if($kbucket && $kb_item->kbucketId == $kbucket->id_kbucket) $kb_num = $kb_key + 1;
                    }
                }
                if($kb_num) $pagin = ceil($kb_num/$settings['no_listing_page']);
            }

            $start = ( $pagin - 1 ) * $settings['no_listing_page'];

			$sql .= " LIMIT $start," . $settings['no_listing_page'];

            $query = $wpdb->prepare( $sql, $values );

            $res['pagesCount'] = ceil( $kbucketsCount / $settings['no_listing_page'] );
            $res['currentPageI'] = $pagin;
            $res['kbuckets'] = $wpdb->get_results( $query );

            return $res;
        }

        return array();
    }

    function isKbucketKeyStatus()
    {
        $status = wp_cache_get('key_status', 'kbucket');

        if ( empty($status) ) {
            /** Statuses
             * 1 - Trial
             * 2 - Error request
             * 3 - Invalid key
             * 4 - Active subscription
             * */
            $status = 2;

            $url = 'https://optimalaccess.com/api-gateway/reg-api-key';
            $settings = kb_get_settings();

            $data = array(
                'apiKey'  => $settings['api_key'],
                'apiHost' => $settings['api_host'],
                'uid'     => get_current_user_id()
            );

            // use key 'http' even if you send the request to https://...
//            $options = array(
//                'http' => array(
//                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//                    'method'  => 'POST',
//                    'content' => http_build_query($data)
//                )
//            );
            //$context  = stream_context_create($options);
            //$result = file_get_contents($url, false, $context);
	        $ch = curl_init();
	        curl_setopt( $ch, CURLOPT_URL, $url);
	        curl_setopt($ch,CURLOPT_HTTPHEADER, ["Content-type: application/x-www-form-urlencoded"]);
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	        $result = curl_exec($ch);
	        curl_close($ch);

            if ( $result !== FALSE && ! empty( $result ) ) {
                $result = json_decode( $result );
                if( isset( $result->success ) && $result->success ) {
                    if ( $result->demo == "1" ) {
                        $status = 1;
                    } else {
                        $status = 4;
                    }
                } else {
                    $status = 3;
                }
            }

            $status = ($status == 2 || $status == 4) ? true : false;
            wp_cache_set('key_status', $status, 'kbucket');
        }

        return $status;
    }

    function render_pagination_links($currentPageI, $pagesCount){
        $start = $currentPageI - 3 > 0 ? $currentPageI - 3 : 1;
        $end = $currentPageI + 3 < $pagesCount ? $currentPageI + 3 : $pagesCount;
		for ( $i = $start; $i <= $end; $i ++ ) {
			$mobileAttr = '';
			if ( kb_is_mobile() ) {
				echo '<li>';
				$mobileAttr = ' rel="external" data-ajax="false"';
			}
			echo
			$currentPageI == $i
				?
				'<a href="#ratop"' . $mobileAttr . ' class="' . ( kb_is_mobile() ? 'ui-btn-active' : 'selected' ) . '"><span>' . $i . '</span></a>'
				:
				'<a href="' . kb_set_page_url($i) . '"' . $mobileAttr . '><span>'. $i . '</span></a>';
			if ( kb_is_mobile() ) {
				echo '</li>';
			}
		}
    }

	function render_pagination_links_widget($currentPageI, $pagesCount) {
		$start = $currentPageI - 3 > 0 ? $currentPageI - 3 : 1;
		$end = $currentPageI + 3 < $pagesCount ? $currentPageI + 3 : $pagesCount;
		$keyStatus = isKbucketKeyStatus();

		if ( $keyStatus ) {
			for ( $i = $start; $i <= $end; $i ++ ) {
				$mobileAttr = '';
				if ( kb_is_mobile() ) {
					echo '<li>';
					$mobileAttr = ' rel="external" data-ajax="false"';
				}
				echo
				$currentPageI == $i
					?
					'<a href="#ratop"' . $mobileAttr . ' class="' . ( kb_is_mobile() ? 'ui-btn-active' : 'selected' ) . '"><span>' . $i . '</span></a>'
					:
					'<a href="' . kb_set_page_url_widget($i) . '"' . $mobileAttr . '><span>'. $i . '</span></a>';
				if ( kb_is_mobile() ) {
					echo '</li>';
				}
			}
		}
	}

    function get_kbucket_by_id( $kid ){
        global $wpdb;
        $kid = esc_sql( $kid );
        $sql = "
                SELECT a.id_kbucket,
                a.id_cat,
                a.title,
                a.description,
                a.link,
                a.author,
                a.twitter,
                a.publisher,
                a.add_date,
                a.pub_date,
                tags,
                a.image_url,
                a.short_url,
                a.post_id,
                c.name AS categoryName,
                a.url_kbucket
            FROM {$wpdb->prefix}kbucket a
            LEFT JOIN
            (
                SELECT a.id_kbucket,GROUP_CONCAT(name) as tags
                FROM {$wpdb->prefix}kbucket a
                LEFT JOIN {$wpdb->prefix}kb_tags b ON b.id_kbucket  = a.id_kbucket
                LEFT JOIN {$wpdb->prefix}kb_tag_details c ON c.id_tag = b.id_tag
                WHERE a.id_kbucket='{$kid}'
            ) b ON a.id_kbucket = b.id_kbucket
            LEFT JOIN {$wpdb->prefix}kb_category c ON a.id_cat=c.id_cat
            WHERE a.id_kbucket='{$kid}'";
        $kbucket = $wpdb->get_results( $sql );

        return !empty( $kbucket[0] ) ? $kbucket[0] : false;
    }

    function get_kbucket_by_slug( $kslug, $blog_id = 1 ){
        global $wpdb;
	    $prefix = $wpdb->get_blog_prefix($blog_id);
        $kslug = esc_sql( $kslug );
        $sql = "
                SELECT a.id_kbucket,
                a.id_cat,
                a.title,
                a.description,
                a.link,
                a.author,
                a.twitter,
                a.publisher,
                a.add_date,
                a.pub_date,
                tags,
                a.image_url,
                a.short_url,
                a.post_id,
                c.name AS categoryName,
                a.url_kbucket
            FROM {$prefix}kbucket a
            LEFT JOIN
            (
                SELECT a.id_kbucket,GROUP_CONCAT(name) as tags
                FROM {$prefix}kbucket a
                LEFT JOIN {$prefix}kb_tags b ON b.id_kbucket  = a.id_kbucket
                LEFT JOIN {$prefix}kb_tag_details c ON c.id_tag = b.id_tag
                WHERE a.short_url='{$kslug}'
            ) b ON a.id_kbucket = b.id_kbucket
            LEFT JOIN {$prefix}kb_category c ON a.id_cat=c.id_cat
            WHERE a.short_url='{$kslug}'";


        $kbucket = $wpdb->get_results( $sql );

        return !empty( $kbucket[0] ) ? $kbucket[0] : false;
    }

    function kb_dashboard_get_result_message( $result ){
        $msg = '';

        if ( is_string( $result ) ) {
            $result = array( 'error' => array( $result ) );
        }

        foreach ( $result as $type => $messages ) {
            foreach ( $messages as $message ) {
                $msg .= '
                <div class="updated updated-' . $type . '">
                    <p><strong>' . $message . '</strong></p>
                </div>';
            }
        }

        return $msg;
    }

    function crunchify_print_scripts_styles() {

        $result = [];
        $result['scripts'] = [];
        $result['styles'] = [];

        // Print all loaded Scripts
        global $wp_scripts;
        foreach( $wp_scripts->queue as $script ) :
           $result['scripts'][] =  $wp_scripts->registered[$script]->src . ";";
        endforeach;

        // Print all loaded Styles (CSS)
        global $wp_styles;
        foreach( $wp_styles->queue as $style ) :
           $result['styles'][] =  $wp_styles->registered[$style]->src . ";";
        endforeach;

        return $result;
    }

    function kb_get_current_kbucket(){
        $url_parts = kb_parse_url();

        if(isset($url_parts['article'])){
            $kbucket = get_kbucket_by_slug( $url_parts['article'] );

            if($kbucket) return $kbucket;
            else return false;
        }else return false;
    }

    function kb_get_kbuckets_like_name($srch){
        global $wpdb;
        $sql = "
            SELECT
                a.id_kbucket
            FROM {$wpdb->prefix}kbucket a
            INNER JOIN {$wpdb->prefix}kb_tags b ON a.id_kbucket = b.id_kbucket
            INNER JOIN {$wpdb->prefix}kb_tag_details c ON b.id_tag = c.id_tag
            WHERE
            (
                a.title LIKE '%%%s%%'
                OR a.description LIKE '%%%s%%'
                OR a.author=%s
                OR c.name=%s
            )
            GROUP BY a.id_kbucket
            LIMIT 100";

        $query = $wpdb->prepare( $sql, array( $srch, $srch, $srch, $srch ) );
        $res = $wpdb->get_results( $query );

        $id_kbucket = array();

        foreach ( $res as $row ) {
            $id_kbucket[] = $row->id_kbucket;
        }
        $id_kbucket = "'" . implode( "','", esc_sql( $id_kbucket ) ) . "'";

        $sql = "
            SELECT
                a.id_kbucket,
                GROUP_CONCAT(`name`) AS tags
            FROM {$wpdb->prefix}kbucket a
            INNER JOIN {$wpdb->prefix}kb_tags b	ON b.id_kbucket = a.id_kbucket
            INNER JOIN {$wpdb->prefix}kb_tag_details c ON c.id_tag = b.id_tag
            WHERE a.id_kbucket IN ( $id_kbucket )
            GROUP BY a.id_kbucket
            LIMIT 100";

        $tag = array();

        $res = $wpdb->get_results( $sql );

        foreach ( $res as $r ) {
            $tag[ $r->id_kbucket ] = $r->tags;
        }

        $sql = "
            SELECT
                a.id_kbucket,
                a.id_cat,
                a.title,
                a.description,
                a.link,
                a.author,
                a.short_url,
                STR_TO_DATE(a.pub_date, '%Y-%m-%d 00:00:00' ) AS postedDate,
                c.alias_name AS parid,
                b.alias_name AS subid,
                c.name AS parcat,
                b.name AS subcat,
                a.url_kbucket,
                a.image_url
            FROM {$wpdb->prefix}kbucket a
            INNER JOIN {$wpdb->prefix}kb_category b ON a.id_cat = b.id_cat
            INNER JOIN {$wpdb->prefix}kb_category c ON c.id_cat = b.parent_cat
            WHERE a.id_kbucket IN ( $id_kbucket )
            LIMIT 100";
        $res['tag'] = $tag;
        $res['kb'] = $wpdb->get_results( $sql );
        return $res;
    }

    function kb_action_ajax_save_kbucket_image(){
        global $wpdb;
        $image_url = esc_sql( $_POST['url'] );
        $id_kbucket = esc_sql( $_POST['id_kbucket'] );
        $attach_id = esc_sql( $_POST['attach_id'] );

        $sql = "UPDATE {$wpdb->prefix}kbucket SET image_url=%s,post_id=%d WHERE id_kbucket=%s";
        $query = $wpdb->prepare( $sql, $image_url, $attach_id, $id_kbucket );
        $wpdb->query( $query );

        // Remove previously attaches
        $sql = $wpdb->prepare( "SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key=%s", 'kb_custom_photo_'.$id_kbucket );
        $metas = $wpdb->get_results($sql, ARRAY_A);
        if(!empty($metas)){
            foreach ( $metas as $meta ) {
                delete_post_meta( $meta['post_id'], 'kb_custom_photo_'.$id_kbucket);
            }
        }

        $res = update_post_meta( $attach_id, 'kb_custom_photo_' . $id_kbucket, $id_kbucket );
		wp_send_json([
			'image_id' => $attach_id,
			'url' => $image_url,
			'updated_id' => $res,
			'id_kbucket' => $id_kbucket
		]);
    }
    add_action( 'wp_ajax_save_kbucket_image', 'kb_action_ajax_save_kbucket_image' );


    /**
     * Replace post link URL with Kbuckets listings page URL with share window
     * Fired by wp filter hook: post_link
     * @param $url
     * @param $post
     * @return string
     */
    function kb_filter_post_link( $url, $post ){
        if ( is_sticky( $post->ID ) && is_home() ) {
            $articleUrl = get_post_meta( $post->ID, 'article_url', true );
            return $articleUrl ? $articleUrl : $url;
        }
        return $url;
    }
    add_filter( 'post_link', 'kb_filter_post_link', 10, 3 );

    /**
     * Save Kbucket image by URL as WP Post attachment
     * @param $imageUrl
     * @return array|bool
     */
    function kb_save_post_image( $imageUrl ){
        if ( ! class_exists( 'WP_Http' ) ) {
            include_once( ABSPATH . WPINC. '/class-http.php' ); }

        $httpObj = new WP_Http();

        $photo = $httpObj->request( $imageUrl );
        if ( is_object( $photo ) || $photo['response']['code'] != 200 ) {
            return false;
        }

        global $user_login;

        $attachment = wp_upload_bits(
         $user_login . '.jpg',
         null,
         $photo['body'],
         date( 'Y-m', strtotime( $photo['headers']['last-modified'] ) )
        );

        return $attachment;
    }
if(!function_exists('write_log')){
    function write_log($log)
    {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    } 
}
   

    /**
     * Insert Sticky Kbuckets as WP posts and return JSON result response
     * Requires one of POST params kb_on | kb_off
     * Fired by wp ajax hook: wp_ajax_save_sticky
     */
    function kb_action_ajax_save_sticky(){

        if ( isset($_POST['kb_on']) && isset($_POST['kb_off']) ) {
            wp_send_json( array('status' => 'error') );
        }

        header( 'Content-Type: application/json' );

        global $wpdb;

        // Posts to write
        if ( ! empty( $_POST['kb_on'] ) ) {

            $args = array( 'hide_empty' => 0 );
            $wpCategories = array();
            $wpc = get_categories( $args );

            if ( ! empty( $wpc ) ) {
	            foreach ( $wpc as $cat ) {
		            $wpCategories[ $cat->name ] = $cat->cat_ID;
	            }
            }

            $where = "a.id_kbucket IN ( '" . implode( "','", $_POST['kb_on'] ) . "' )";
            $kbuckets = kb_get_kbuckets_db( $where );
            $savedImages = array();

            foreach ( $kbuckets as $kbucket ) {

                if ( $kbucket->post_id > 0 || is_sticky( $kbucket->post_id ) ) {
                    continue;
                }

                $content = $kbucket->description;

                $postData = array(
                    'post_content'   => $content,
                    'post_title'     => $kbucket->title,
                    'post_status'    => 'publish',
                    'post_author'    => $kbucket->author,
                    'post_date'      => $kbucket->add_date,
                    'post_date_gmt'  => $kbucket->add_date,
                    'comment_status' => 'closed'
                );

                if ( isset( $wpCategories[ $kbucket->categoryName ] ) ) {
                    $postData['post_category'] = array( $wpCategories[ $kbucket->categoryName ] );
                }

                /** @var $postId object|int */
                $postId = wp_insert_post( $postData, true );

                // Wpdb error object returned if there was an error
                if ( is_object( $postId ) ) {
                    echo json_encode( array( 'status' => $postId->last_error ) );
                    exit;
                }

                stick_post( $postId );

                add_post_meta( $postId, 'article_url', $kbucket->link, true );
                add_post_meta( $postId, '_url_kbucket', $kbucket->url_kbucket, true );
                add_post_meta( $postId, '_id_kbucket', $kbucket->id_kbucket, true );

                $updateArr = array( 'post_id' => $postId );
                $updatePlaceholders = array( '%d' );

                if ( isset($imageUrl) && $imageUrl !== '' ) {

    //				$attachment = kb_save_post_image( $imageUrl, $postId, $kbucket->title );
                    $attachment = kb_save_post_image( $imageUrl );

                    if ( empty( $attachment['error'] ) && ! empty( $attachment['url'] ) ) {

                        $imageFile = $attachment['file'];

                        $filetype = wp_check_filetype( basename( $imageFile ), null );

                        $postData = array(
                            'post_mime_type' => $filetype['type'],
                            'post_title' => $kbucket->title,
                            'post_content' => '',
                            'post_status' => 'inherit',
                        );

                        $attachmentId = wp_insert_attachment( $postData, $imageFile, $postId );

                        if ( ! function_exists( 'wp_generate_attachment_data' ) ) {
                            require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
                        }

                        $attachmentData = wp_generate_attachment_metadata( $attachmentId, $imageFile );
                        if ( wp_update_attachment_metadata( $attachmentId,  $attachmentData ) ) {
                            add_post_meta( $postId, '_thumbnail_id', $attachmentId, true );
                        }

                        $savedImages[ $kbucket->id_kbucket ] = $imageUrl;

                        $updateArr['image_url'] = $imageUrl;
                        $updatePlaceholders[] = '%s';
                    }
                }
                $wpdb->update(
                    $wpdb->prefix . 'kbucket',
                    $updateArr,
                    array( 'id_kbucket' => $kbucket->id_kbucket ),
                    $updatePlaceholders,
                    '%s'
                );
            }
        }

        // Posts to delete
        if ( ! empty( $_POST['kb_off'] ) ) {

            $where = "a.id_kbucket IN ( '" . implode( "','", $_POST['kb_off'] ) . "' )";

            $kbuckets = kb_get_kbuckets_db( $where );

            foreach ( $kbuckets as $kbucket ) {

//                if ( absint( $kbucket->post_id ) > 0 ) {
//                    continue;
//                }

                $args = array(
                    'post_type'      => 'attachment',
                    'post_status'    => 'any',
                    'posts_per_page' => -1,
                    'post_parent'    => $kbucket->post_id,
                    'fields'         => 'ids'
                );
                $attachments = new WP_Query( $args );
                $attachment_ids = isset( $attachments->posts ) ? $attachments->posts : array();

                wp_reset_postdata();

                if ( ! empty( $attachment_ids ) ) {
                    $delete_attachments_query = $wpdb->prepare(
                        'DELETE FROM %1$s WHERE %1$s.ID IN (%2$s)',
                        $wpdb->posts,
                        join( ',', $attachment_ids )
                    );
                    $wpdb->query( $delete_attachments_query );
                }

	            write_log($kbucket->post_id);

                wp_delete_post($kbucket->post_id, true );
            }

            $sql = $wpdb->prepare(
                "UPDATE {$wpdb->prefix}kbucket SET post_id=NULL WHERE id_kbucket IN (%s)",
                implode("','",esc_sql($_POST['kb_off']))
            );
            $wpdb->query($sql);
        }

        if ( $wpdb->last_error !== '' ) {
	        wp_send_json( array( 'status' => $wpdb->last_error ) );
        }

        $response = array( 'status' => 'ok' );

        if ( ! empty( $savedImages ) ) {
            $response['images'] = $savedImages;
        }

        wp_send_json( $response );
    }
    add_action( 'wp_ajax_save_sticky', 'kb_action_ajax_save_sticky' );

    function RFC2822($date, $time = '00:00'){
        list($d, $m, $y) = explode('-', $date);
        list($h, $i) = explode(':', $time);
        return date('r', mktime($h,$i,0,$m,$d,$y));
    }

    function kb_filter_the_title($title) {
        $title = explode(' - ', $title);

        $new_title = array();

        if(count($title)){
            $first = reset($title);
            $end = end($title);
        }else{
            $first = $end = '';
        }
        $new_title[] = $first;

        $url_parts = kb_parse_url();

        // Paginate
        if(!empty($url_parts['page'])){
            $page = sprintf(__("Page of %s", WPKB_TEXTDOMAIN), (int)$url_parts['page']);
        }else{
            $page = '';
        }

        // Sort by category
        if(!empty($url_parts['c-tag'])){
            $mtag = sprintf(__("Sort by Category: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['c-tag']));
        }else{
            $mtag = '';
        }

        // Sort by Related tags
        if(!empty($url_parts['r-tag'])){
            $rtag = sprintf(__("Sort by Related tag: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['r-tag']));
        }else{
            $rtag = '';
        }

        // Sort by Publisher tags
        if(!empty($url_parts['p-tag'])){
            $ptag = sprintf(__("Sort by Publisher tag: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['p-tag']));
        }else{
            $ptag = '';
        }

        // Sort by Author tags
        if(!empty($url_parts['a-tag'])){
            $atag = sprintf(__("Sort by Author tag: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['a-tag']));
        }else{
            $atag = '';
        }

        // Sort by Author, Title or date
        if(!empty($_GET['sort'])){
            if($_GET['sort'] == 'author'){
                $sort = sprintf(__("Sort by Author: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }elseif($_GET['sort'] == 'add_date'){
                $sort = sprintf(__("Sort by Date: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }elseif($_GET['sort'] == 'title'){
                $sort = sprintf(__("Sort by Title: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }else{
                $sort = sprintf(__("Sort by Undefined: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }
        }else{
            $sort = '';
        }

        // Search page
        if(!empty($_GET['srch'])){
            $search = __("Search in Kbuckets", WPKB_TEXTDOMAIN);
        }else{
            $search = '';
        }

        $category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
        $category = kb_get_categories_like_alias($category);

        $subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;
        $subcategory = kb_get_subcategories_like_alias($subcategory);

        if(!empty($category)) $new_title[] = $category[0]->name;
        if(!empty($subcategory)) $new_title[] = $subcategory[0]->subcat;


        if(!empty($page)) $new_title[] = $page;
        if(!empty($mtag)) $new_title[] = $mtag;
        if(!empty($rtag)) $new_title[] = $rtag;
        if(!empty($ptag)) $new_title[] = $ptag;
        if(!empty($atag)) $new_title[] = $atag;
        if(!empty($sort)) $new_title[] = $sort;
        if(!empty($search)) $new_title[] = $search;


        $new_title[] = $end;

        $new_title = implode(' - ', $new_title);
        return $new_title;
    }

    function kb_filter_the_title_alt($title) {
        $url_parts = kb_parse_url();

        // Paginate
        if(!empty($url_parts['page'])){
            $page = sprintf(__("Page of %s", WPKB_TEXTDOMAIN), (int)$url_parts['page']);
        }else{
            $page = '';
        }

        // Sort by category
        if(!empty($url_parts['c-tag'])){
            $mtag = sprintf(__("Sort by Category: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['c-tag']));
        }else{
            $mtag = '';
        }

        // Sort by Related tags
        if(!empty($url_parts['r-tag'])){
            $rtag = sprintf(__("Sort by Related tag: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['r-tag']));
        }else{
            $rtag = '';
        }

        // Sort by Publisher tags
        if(!empty($url_parts['p-tag'])){
            $ptag = sprintf(__("Sort by Publisher tag: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['p-tag']));
        }else{
            $ptag = '';
        }

        // Sort by Author tags
        if(!empty($url_parts['a-tag'])){
            $atag = sprintf(__("Sort by Author tag: %s", WPKB_TEXTDOMAIN), sanitize_text_field($url_parts['a-tag']));
        }else{
            $atag = '';
        }

        // Sort by Author, Title or date
        if(!empty($_GET['sort'])){
            if($_GET['sort'] == 'author'){
                $sort = sprintf(__("Sort by Author: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }elseif($_GET['sort'] == 'add_date'){
                $sort = sprintf(__("Sort by Date: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }elseif($_GET['sort'] == 'title'){
                $sort = sprintf(__("Sort by Title: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }else{
                $sort = sprintf(__("Sort by Undefined: %s", WPKB_TEXTDOMAIN), sanitize_text_field($_GET['sort']));
            }
        }else{
            $sort = '';
        }

        // Search page
        if(!empty($_GET['srch'])){
            $search = __("Search in Kbuckets", WPKB_TEXTDOMAIN);
        }else{
            $search = '';
        }


        $new_title = array();
        if(count($title)){
            $first = reset($title);
            $end = end($title);
        }else{
            $first = $end = '';
        }

        $new_title[] = $first;

        $category = isset($url_parts['cat']) ? $url_parts['cat'] : 0;
        $category = kb_get_categories_like_alias($category);

        $subcategory = isset($url_parts['subcat']) ? $url_parts['subcat'] : 0;
        $subcategory = kb_get_subcategories_like_alias($subcategory);

        if(!empty($category)) $new_title[] = $category[0]->name;
        if(!empty($subcategory)) $new_title[] = $subcategory[0]->subcat;

        if(!empty($page)) $new_title[] = $page;
        if(!empty($mtag)) $new_title[] = $mtag;
        if(!empty($rtag)) $new_title[] = $rtag;
        if(!empty($ptag)) $new_title[] = $ptag;
        if(!empty($atag)) $new_title[] = $atag;
        if(!empty($sort)) $new_title[] = $sort;
        if(!empty($search)) $new_title[] = $search;


        $new_title[] = $end;
        return $new_title;
    }

    function kb_set_page_template_mobile( $template ){
        $file = WPKB_PATH . 'templates/' . 'mobile.php';
        return file_exists( $file ) ? $file : $template;
    }

    add_filter('body_class', 'kb_body_classes');
    function kb_body_classes($classes) {
        $id = get_current_blog_id();
        if( is_kbucket_page_func() ){
            $classes[] = 'page-template-template-kbucket';
        }
        return $classes;
    }

    add_action('delete_post', 'kbucket_delete_post');
    function kbucket_delete_post($postID)
    {
        if ( get_post_type($postID) == 'post' ) {
            global $wpdb;

	        $wpdb->update(
		        $wpdb->prefix . 'kbucket',
		        ['post_id' => NULL],
		        ['post_id' => $postID]
	        );
        }
    }

	function kb_get_kbucket_url($remove = '') {
		$parts = kb_parse_url();
		$home = get_bloginfo('url') . DIRECTORY_SEPARATOR;
		$linkUrl = $home . $parts['parent'] . DIRECTORY_SEPARATOR . $parts['cat'] . DIRECTORY_SEPARATOR . $parts['subcat'] . DIRECTORY_SEPARATOR;
		if($remove == 'related' && !empty($parts['c-tag']))
			$linkUrl .= 'c-tag' . DIRECTORY_SEPARATOR . $parts['c-tag'] . DIRECTORY_SEPARATOR;

		return $linkUrl;
	}

function kb_get_list_kb_styles() {
	return [
		[
			'id' => 'masonry',
			'title' => __("Masonry Grid", WPKB_TEXTDOMAIN)
		],
		[
			'id' => 'carousel',
			'title' => __("Carousel", WPKB_TEXTDOMAIN)
		],
		[
			'id' => 'slider',
			'title' => __("Slider", WPKB_TEXTDOMAIN)
		]
	];
}