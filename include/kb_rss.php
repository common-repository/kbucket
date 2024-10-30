<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!function_exists('kb_rss_action_hook')){
    function kb_rss_action_hook(){
        if(isset($_REQUEST['xml']) && isset($_REQUEST['format'])){
            $cid = esc_attr($_REQUEST['xml']);
            $url_args = array_filter(explode("/", strtok($_SERVER["REQUEST_URI"], '?')));
            $cid = end($url_args);//'research';
            global $wp,$wpdb;
            $current_url = home_url(add_query_arg(array(),$wp->request));

            $subcat = kb_get_current_subcat_url();
            $url = $subcat->url;

            $catinfo = $wpdb->get_row("
				SELECT name,description,add_date 
				FROM {$wpdb->prefix}kb_category 
				WHERE alias_name = '$cid' AND status='1'");

	        $__cid = get_category_id_by_slug($cid);
			if(!empty($__cid[0]->id_cat)) $cid = $__cid[0]->id_cat;
            $sql = "
				SELECT
					a.id_kbucket,
					id_cat,
					title,
					description,
					link,
					author,
					add_date,
					tags,
					pub_date,
					twitter,
					last_updated,
					image_url,
					publisher,
					post_id,
					url_kbucket
				FROM {$wpdb->prefix}kbucket a
				LEFT JOIN(
					SELECT
						a.id_kbucket,
						GROUP_CONCAT(name) as tags
					FROM {$wpdb->prefix}kbucket a
					LEFT JOIN {$wpdb->prefix}kb_tags b ON b.id_kbucket  = a.id_kbucket
					LEFT JOIN {$wpdb->prefix}kb_tag_details c ON c.id_tag = b.id_tag
					WHERE a.id_cat = '$cid'
					GROUP BY a.id_kbucket
				) b ON a.id_kbucket = b.id_kbucket
				WHERE a.id_cat = '$cid'
				ORDER BY pub_date DESC";

            $data = $wpdb->get_results($sql);


            header("Content-type: application/rss+xml");
            $result = '<?xml version="1.0" encoding="UTF-8" ?>'."\n";
            $result .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
            $result .= '<channel>'."\n";
            $result .= '<title>'.(isset($catinfo->name) ? $catinfo->name : '').'</title>'."\n";
            $result .= '<link>'.$url.'</link>'."\n";
            $result .= '<description>'.(isset($catinfo->description) ? htmlspecialchars($catinfo->description) : '').'</description>'."\n";
            $date = isset($catinfo->add_date) ? strtotime($catinfo->add_date) : '';

            $result .= '<pubDate>'.(!empty($date) ? date('r',$date) : '').'</pubDate>'."\n";
            foreach ($data as $item) {
                $modified = RFC2822(date('d-m-Y',(int)$item->last_updated/1000));
                $published = $item->pub_date;
                $publisher = $item->publisher;
                $twitter = !empty($item->twitter) ? $item->twitter : '';
	            $img_width = $img_height = '';
                if(!empty($item->image_url)){
                    $img_url = $item->image_url;
                    $length = filesize(get_attached_file(get_post_thumbnail_id($item->post_id)));
                    $mime = '';
                    $image_type = @exif_imagetype($img_url);
                    switch ($image_type) {
                        case 1:
                            $mime = 'image/gif';
                            break;
                        case 2:
                            $mime = 'image/jpg';
                            break;
                        case 3:
                            $mime = 'image/png';
                            break;
                        default:
                            $mime = 'image/jpeg';
                            break;
                    }
                }else{
                    $img_url = 'https://placehold.it/150x150';
                    $img_width = 150;
                    $img_height = 150;
                    $length = 0;
                    $mime = 'image/jpeg';
                }
                $link = get_site_url() . '/' .$item->url_kbucket;
                $guid = htmlspecialchars($item->link);
                $result .= '<item>'."\n";
                $result .= '<guid isPermaLink="false">'.$guid.'</guid>'."\n";
                $result .= '<title><![CDATA['.utf8_decode($item->title).']]></title>'."\n";
                $result .= '<author><![CDATA['.$item->author.']]></author>'."\n";
                $result .= '<link><![CDATA['.$link.']]></link>'."\n";
                $result .= '<modDate><![CDATA['.$modified.']]></modDate>'."\n";
                $result .= '<pubDate><![CDATA['.RFC2822(date('d-m-Y',strtotime($item->pub_date))).']]></pubDate>'."\n";
                $result .= '<enclosure url="'.$img_url.'" type="'.$mime.'" length="'.$length.'" />'."\n";
                $result .= '<img src="'.$img_url.'" width="'.$img_width.'" height="'.$img_height.'" />'."\n";
                $result .= '<publisher><![CDATA['.$publisher.']]></publisher>'."\n";
                $result .= '<description><![CDATA['.htmlspecialchars($item->description).'<br />Tags: '.$item->tags.']]></description>'."\n";
                $result .= '<category><![CDATA['.$item->tags.']]></category>'."\n";
                if(!empty($twitter)) $result .= '<meta name="twitter:site" content="'.$twitter.'" />'."\n";
                $result .= '</item>'."\n";
            }
            $result .= '</channel>'."\n";
            $result .= '</rss>';
            echo $result;
            exit();
        }
    }
    add_action('template_redirect', 'kb_rss_action_hook');
}



if(!function_exists('kb_rss_category_action_hook')){
    function kb_rss_category_action_hook(){
        if(isset($_REQUEST['cat']) && isset($_REQUEST['subcat'])){
            $cid = $_REQUEST['cat'];
            $scid = (isset($_REQUEST['subcat'])) ? $_REQUEST['subcat'] : false;

            $subcat = kb_get_current_subcat_url();
            $url = $subcat->url;

			if(!empty($scid)){
				$__scid = get_category_id_by_slug($scid);
				if(!empty($__scid[0]->id_cat)) $scid = $__scid[0]->id_cat;
			}
            global $wpdb;

            $catinfo = $wpdb->get_row("SELECT name,description,add_date FROM {$wpdb->prefix}kb_category WHERE id_cat = '$cid' AND status='1'");
            $scatinfo = $wpdb->get_row("SELECT name,description,add_date FROM {$wpdb->prefix}kb_category WHERE alias_name = '$scid' AND status='1'");

            $sql = "
				SELECT
					a.id_kbucket,
					id_cat,
					title,
					description,
					link,
					author,
					add_date,
					url_kbucket,
					tags,
					pub_date,
					twitter,
					last_updated,
					image_url,
					publisher,
					post_id
				FROM {$wpdb->prefix}kbucket a
				LEFT JOIN(
					SELECT
						a.id_kbucket,
						GROUP_CONCAT(name) as tags
					FROM {$wpdb->prefix}kbucket a
					LEFT JOIN {$wpdb->prefix}kb_tags b ON b.id_kbucket  = a.id_kbucket
					LEFT JOIN {$wpdb->prefix}kb_tag_details c ON c.id_tag = b.id_tag
					WHERE a.id_cat = '$cid'
					GROUP BY a.id_kbucket
				) b ON a.id_kbucket = b.id_kbucket
				WHERE a.id_cat = '$scid'
				ORDER BY pub_date DESC";

            $data = $wpdb->get_results($sql);



            header("Content-type: application/rss+xml");
            $result = '<?xml version="1.0" encoding="iso-8859-1"?>';
            $result .= '<rss version="2.0">';
            $result .= '<channel>';
            $result .= '<title>Kbucket - (Category: '.(!empty($catinfo->name) ? $catinfo->name.($scid ? ', Subcategory: '.$scatinfo->name : '') : '').')</title>';
            $result .= '<link>'.$url.'</link>';
            $result .= '<description>'.(!empty($catinfo->description) ? $catinfo->description : '').'</description>';
            $result .= '<pubDate>'.(!empty($catinfo) ? $catinfo->add_date : date('r', time())).'</pubDate>';
            foreach ($data as $item) {
                $modified = $item->last_updated;
                $publisher = $item->publisher;
                $twitter = !empty($item->twitter) ? $item->twitter : '';
                if(!empty($item->image_url)){
                    $img_url = $item->image_url;
                    $length = filesize(get_attached_file(get_post_thumbnail_id($item->post_id)));
                    $mime = '';
                    $image_type = @exif_imagetype($img_url);
                    switch ($image_type) {
                        case 1:
                            $mime = 'image/gif';
                            break;
                        case 2:
                            $mime = 'image/jpg';
                            break;
                        case 3:
                            $mime = 'image/png';
                            break;
                        default:
                            $mime = 'image/jpeg';
                            break;
                    }
                }else{
                    $img_url = 'https://placehold.it/150x150';
                    $img_width = 150;
                    $img_height = 150;
                    $length = 0;
                    $mime = 'image/jpeg';
                }
                $result .= '<item>';
                $result .= '<title><![CDATA['.$item->title.']]></title>';
                $result .= '<pubDate><![CDATA['.$item->pub_date.']]></pubDate>';
                $result .= '<author><![CDATA['.$item->author.']]></author>';
                $result .= '<link><![CDATA['.get_bloginfo('home').'/'.$item->url_kbucket.']]></link>';
                $result .= '<modDate><![CDATA['.$modified.']]></modDate>'."\n";
                $result .= '<enclosure url="'.$img_url.'" type="'.$mime.'" length="'.$length.'" />'."\n";
                $result .= '<img src="'.$img_url.'" width="'.$img_width.'" height="'.$img_height.'" />'."\n";
                $result .= '<publisher><![CDATA['.$publisher.']]></publisher>'."\n";
                $result .= '<description><![CDATA['.$item->description.'<br>Tags: '.$item->tags.']]></description>';
                $result .= '<category><![CDATA['.$item->tags.']]></category>';
                if(!empty($twitter)) $result .= '<meta name="twitter:site" content="<![CDATA['.$twitter.']]>">'."\n";
                $result .= '</item>';
            }
            $result .= '</channel>';
            $result .= '</rss>';
            echo $result;
            exit();
        }
    }
    add_action('init', 'kb_rss_category_action_hook');
}



if(!function_exists('kb_rss_suggest_action_hook')){
    function kb_rss_suggest_action_hook(){
        if(isset($_REQUEST['suggest_rss'])){
            global $wpdb;

            $pid = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_content LIKE '%[KBucket-Page]%' AND post_status = 'publish' ");
            $url = get_permalink($pid);
            $data = $wpdb->get_results("SELECT a.*,b.* ,DATE_FORMAT(a.add_date,'%d-%m-%Y') as pubdate
				FROM {$wpdb->prefix}kb_suggest a
				left join(
					SELECT c.id_cat as parid, b.id_cat as subid,c.name as parcat,b.name as  subcat,b.description as subdes, c.description as pades
					FROM {$wpdb->prefix}kb_category b
					inner join {$wpdb->prefix}kb_category c on c.id_cat = b.parent_cat
				) b on a.id_cat = b.subid

			");

            header("Content-type: application/rss+xml");
            $result = '<?xml version="1.0" encoding="iso-8859-1"?>';
            $result .= '<rss version="2.0">';
            $result .= '<channel>';
            $result .= '<title><![CDATA[Suggested Page URLs]]></title>';
            $result .= '<link><![CDATA['.$url.']]></link>';
            $result .= '<pubDate>'.RFC2822(date('d-m-Y')).'</pubDate>';
            foreach($data  as $item) {
                $result .= '<item>';
                $result .= '<title><![CDATA['.$item->tittle.']]></title>';
                $result .= '<author><![CDATA['.$item->author.']]></author>';
                $result .= '<pubDate><![CDATA['.$item->add_date.']]></pubDate>';
                $result .= '<link><![CDATA['.$item->link.']]></link>';
                $result .= '<description><![CDATA['.$item->description.']]></description>';
                $result .= '<category><![CDATA['.$item->tags.']]></category>';
                $result .= '<twitter_handle><![CDATA['.$item->twitter.']]></twitter_handle>';
                $result .= '<facebook_page><![CDATA['.$item->facebook.']]></facebook_page>';
                $result .= '</item>';
            }
            $result .= '</channel>';
            $result .= '</rss>';
            echo $result;
            exit();
        }
    }
    add_action('wp_loaded', 'kb_rss_suggest_action_hook');

}
