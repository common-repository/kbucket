<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly


function getCatByName($empty = false)
{
	global $wpdb;
	$sql = "SELECT name,description,image,parent_cat FROM {$wpdb->prefix}kb_category";
	$result = $wpdb->get_results($sql);
	$new_res = array();
	foreach ($result as $cat) {
		if (!$cat->description && !$empty) continue; // Pass empty description
		if (!$cat->parent_cat) continue; // Pass parent category
		$new_res[$cat->name]['description'] = $cat->description;
		$new_res[$cat->name]['image'] = $cat->image;
	}
	return $new_res;
}

function kb_parsing_xml($file_string)
{
	require_once('phpQuery-onefile.php');

	$file_string = str_replace([
		'<span itemprop="description"><![CDATA[',
		']]></span>'
	], [
		'<span itemprop="description">',
		'</span>'
	], $file_string);

	$XDOMContent = phpQuery::newDocumentHTML($file_string);
	$schema = "//schema.org/";

	$kbucketPageID = kbucketPageID();
	$post = get_post($kbucketPageID);
	$kbslug = (isset($post->post_name)) ? $post->post_name : 'kbucket';
	$uniq_page_slug = array();

	$fileArr = $XDOMContent->find('file');
	foreach ($fileArr as $fileKey => $file) {
		$library = pq($file);

		$attrFile_Name = $library->find('div[itemtype$="' . $schema . 'WebPage"]>span[itemprop="about"]')->html();
		if (empty($attrFile_Name)) $attrFile_Name = '';

		$commentFileNode = $library->find('div[itemtype$="' . $schema . 'WebPage"]>span[itemprop="comment"]')->html();
		if (empty($commentFileNode)) $commentFileNode = '';

		$attrFile_UID = $library->find('div[itemtype$="' . $schema . 'WebPage"]>span[itemprop="encoding"]')->html();
		if (empty($attrFile_UID)) $attrFile_UID = '';

		$attrFile_publisher = trim($library->find('div[itemtype$="' . $schema . 'WebPage"]>span[itemprop="publisher"]')->html());
		if (empty($attrFile_publisher)) $attrFile_publisher = '';

		$attrFile_author = trim($library->find('div[itemtype$="' . $schema . 'WebPage"]>span[itemprop="author"]')->html());
		if (empty($attrFile_author)) $attrFile_author = '';

		$attrFile_tooltip = $library->find('div[itemtype$="' . $schema . 'WebPage"]>span[itemprop="keywords"]')->html();
		if (empty($attrFile_tooltip)) $attrFile_tooltip = '';

		$bookArr = $fileArr->find('group');

		$kb_arr_image = [];
		$categoriesValues = [];


		foreach ($bookArr as $book) {
			$qbook = pq($book);
			$categoriesPlaceholders[] = '(%s,%s,%s,%s,%d,%s,%s,%s)';


			$attrBook_Name = $qbook->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="about"]')->html();
			$attrBookComment = $qbook->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="comment"]')->html();
			$attrBook_UID = $qbook->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="encoding"]')->html();
			$attrBookKeywords = $qbook->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="keywords"]')->html();

			if (!isset($categories[$attrBook_Name])) {
				$categoriesValues[] = [
					$attrBook_UID,
					$attrBook_Name,
					sanitize_title_with_dashes($attrBook_Name),
					'',
					1,
					date('Y') . '-' . date('m') . '-' . date('d'),
					$attrBookKeywords,
					$attrBookComment
				];
			}

			$term = get_term_by('name', $attrBook_Name, 'category');
			$cat_array = array(
				'cat_name'              => $attrBook_Name,
				'category_description'  => $attrBookComment
			);
			if (false !== $term) {
				$cat_array['cat_ID'] = $term->term_id;
			}

			$parentCategoryId = wp_insert_category($cat_array);

			$chapterArr = $qbook->find('page');

			foreach ($chapterArr as $chapter) {
				$qchapter = pq($chapter);
				$count = 0;

				$attrChapter_Name = $qchapter->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="about"]')->html();
				$attrChapterComment = $qchapter->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="comment"]')->html();
				$attrChapter_UID = $qchapter->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="encoding"]')->html();
				$attrChapterKeywords = $qchapter->find('&>div[itemtype$="' . $schema . 'WebPageElement"]>span[itemprop="keywords"]')->html();

				// Add subcategories values
				if (!isset($categories[$attrBook_Name])) {
					$categoriesValues[] = [
						$attrChapter_UID,
						$attrChapter_Name,
						sanitize_title_with_dashes($attrChapter_Name),
						$attrBook_UID,
						2,
						date('Y') . '-' . date('m') . '-' . date('d'),
						$attrChapterKeywords,
						$attrChapterComment
					];
				}

				$termChild = get_term_by('name', $attrChapter_Name, 'category');
				$cat_array = array(
					'cat_name'              => $attrChapter_Name,
					'category_description'  => $attrChapterComment,
					'category_parent'       => $parentCategoryId
				);
				if (false !== $termChild) {
					$cat_array['cat_ID'] = $termChild->term_id;
				}

				wp_insert_category($cat_array);

				$pageArr = $qchapter->find('div[itemtype$="' . $schema . 'Article"]');

				foreach ($pageArr as $page) {
					$qpage = pq($page);

					$attrPage_tooltip = trim($qpage->find('span[itemprop="keywords"]')->html());
					$attrPage_Name = $qpage->find('h2[itemprop="headline"]')->html();

					$urlPageNode = $qpage->find('meta[itemprop="mainEntityOfPage"]')->attr('itemid');

					// Check for uniq slug
					$page_slug = sanitize_title_with_dashes($attrPage_Name);
					$page_slug = kb_check_uniq_page_slug($page_slug, $uniq_page_slug);
					$uniq_page_slug[] = $page_slug;


					$attrPage_UID = strtoupper(md5($page_slug));

					$tagArr = explode(',', $attrPage_tooltip);

					// Iterate tags
					foreach ($tagArr as $tag) {
						$tag = strtolower(trim($tag));
						//						$tagsDetailsValues[$attrPage_UID][sanitize_title_with_dashes($tag)] = $tag;
						$tagsDetailsValues[$tag][] = $attrPage_UID;
					}

					unset($tagArr);

					$count++;
					$trial = kb_isTrialKey();
					if ($trial && $count >= 100) {
						continue;
					}


					$attrPage_author = trim($qpage->find('span[itemprop="name"]')->html());
					$attrPage_publisher = trim($qpage->find('meta[itemprop="name"]')->attr('content'));
					$attrPage_pub_date = $qpage->find('meta[itemprop="datePublished"]')->attr('content');
					$commentPageNode = $qpage->find('span[itemprop="description"]')->html();

					$attrTwitterName = trim($qpage->find('meta[name="twitter:site"]')->attr('content'));

					$attrImageUrl = $qpage->find('meta[itemprop="url"]')->attr('content');

					$attrPage_KID = $qpage->find('span[itemprop="ID"]')->html();
					$attrModified = $qpage->find('meta[itemprop="dateModified"]')->attr('content');

					$kbucketUrl = $kbslug . '/' . sanitize_title_with_dashes($attrBook_Name) . '/' . sanitize_title_with_dashes($attrChapter_Name) . '/' . $page_slug;

					$kbucketsValues[] = [
						$attrPage_UID,
						$attrPage_KID,
						$attrChapter_UID,
						$attrPage_Name,
						$commentPageNode,
						$urlPageNode,
						$attrPage_author,
						$attrPage_publisher,
						sanitize_title_with_dashes($attrPage_author),
						sanitize_title_with_dashes($attrPage_publisher),
						$attrTwitterName, // TWITTER
						date("Y-m-d H:i:s", strtotime($attrPage_pub_date)),
						date('Y') . '-' . date('m') . '-' . date('d'),
						$attrModified,
						'',
						$page_slug, // short url
						$kbucketUrl
					];


					// Collect images
					if (!empty($attrImageUrl)) {
						$kb_arr_image[$attrPage_UID]['image'] = $attrImageUrl;
						$kb_arr_image[$attrPage_UID]['name'] = $attrPage_Name;
					}
				}
			}
		} //end book
	}
	return [
		'kbuckets' => $kbucketsValues,
		'images' => $kb_arr_image,
		'categories' => $categoriesValues,
		'tags' => $tagsDetailsValues,
		'file' => [
			$attrFile_Name,
			$commentFileNode,
			$attrFile_UID,
			$attrFile_publisher,
			$attrFile_author,
			$attrFile_tooltip
		]
	];
}

function kb_ajax_parsing_xml()
{
	$res = kb_parsed_add_db();
	wp_send_json($res);
}
add_action('wp_ajax_ajax_parsing_kbucket', 'kb_ajax_parsing_xml');

//add_action('wp_ajax_ajax_parsing_kbucket', 'kb_parsed_add_db');
function kb_parsed_add_db($file_string = '')
{
	global $wpdb;

	if ($file_string == '') {
		$uploadFile = @file_get_contents($_POST['file']);
		if (empty($uploadFile)) return;
	} else {
		$uploadFile = $file_string;
	}

	$parsed = kb_parsing_xml($uploadFile);


	$update = true;
	$updated_channel = [];
	$f_exist = $wpdb->prepare("SELECT name, encoding FROM {$wpdb->prefix}kb_files WHERE encoding = %s", $parsed['file'][2]);
	$f_exist = $wpdb->get_results($f_exist, ARRAY_A);
	if ($update && (empty($f_exist) || empty($f_exist[0]['encoding']) || $f_exist[0]['encoding'] !== $parsed['file'][2])) {
		$update = false;
	}
	$resMsg = [];
	// echo "<pre>";
	// print_R($parsed);
	if (!$update) {
		//echo "DIRECT INSERT";
		// Prepare tables
		$truncateTables = array(
			$wpdb->prefix . 'kb_category',
			$wpdb->prefix . 'kb_tags',
			$wpdb->prefix . 'kb_tag_details',
			$wpdb->prefix . 'kb_files',
			$wpdb->prefix . 'kbucket'
		);
		foreach ($truncateTables as $table) {
			$wpdb->query("TRUNCATE TABLE {$table}");
		}

		// Insert file
		$file_sql = "
		INSERT IGNORE
		INTO {$wpdb->prefix}kb_files(
			name,
			comment,
			encoding,
			publisher,
			author,
			keywords,
			file_added,
			file_path
		) VALUES (
			'{$parsed['file'][0]}',
			'{$parsed['file'][1]}',
			'{$parsed['file'][2]}',
			'{$parsed['file'][3]}',
			'{$parsed['file'][4]}',
			'{$parsed['file'][5]}',
			'" . time() . "',
			'" . (!empty($file_string) ? 'schedule' : $_POST['file']) . "'
		)";
		$res = $wpdb->query($file_sql);
	} else {
		//echo "DIRECT Update mayra";
		// Update File
		$file_sql = $wpdb->prepare(
			"
		UPDATE {$wpdb->prefix}kb_files SET
			name=%s,
			comment=%s,
			encoding=%s,
			publisher=%s,
			author=%s,
			keywords=%s,
			file_added=%s,
			file_path=%s
		WHERE encoding=%s
		",
			$parsed['file'][0],
			$parsed['file'][1],
			$parsed['file'][2],
			$parsed['file'][3],
			$parsed['file'][4],
			$parsed['file'][5],
			time(),
			(!empty($file_string) ? 'schedule' : $_POST['file']),
			$parsed['file'][2]
		);
		$res = $wpdb->query($file_sql);


		// Check on modified channels
		$categories = $wpdb->get_col("SELECT id_cat FROM {$wpdb->prefix}kb_category");

		foreach ($parsed['categories'] as $category) {
			if (!in_array($category[0], $categories)) {
				$updated_channel[] = $category[0];
			}
		}
	}
	// echo "<pre>";
	// print_R($updated_channel);
	// Channel modified or not update. Insert channels
	if (!empty($updated_channel) || !$update) {
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}kb_category");
		// Insert Channels
		$categoriesValues = [];
		foreach ($parsed['categories'] as $category) {
			foreach ($category as $item) {
				$categoriesValues[] = $item;
			}
			$categoriesPlaceholders[] = '(%s,%s,%s,%s,%d,%s,%s,%s)';
		}
		$categoriesSql = "
		INSERT IGNORE INTO {$wpdb->prefix}kb_category (
			id_cat,
			name,
			alias_name,
			parent_cat,
			level,
			add_date,
			keywords,
			description
		) VALUES ";
		$categoriesSql .= implode(',', $categoriesPlaceholders);
		$categoriesSql .= ' ON DUPLICATE KEY UPDATE
	    name = VALUES(name),
		alias_name = VALUES(alias_name),
		parent_cat = VALUES(parent_cat),
		level = VALUES(level),
		add_date = VALUES(add_date),
		keywords = VALUES(keywords),
		description = VALUES(description)';
		$res = $wpdb->prepare($categoriesSql, $categoriesValues);
		$resMsg[] = 'Inserted Categories: ' . $wpdb->query($res);
	} else if ($update) {

		$order = 1;
		foreach ($parsed['categories'] as $category) {
			$new_Description = $category[7];
			$exist_Desc = $wpdb->prepare("SELECT description FROM {$wpdb->prefix}kb_category WHERE id_cat='$category[0]'");
			$existsing = $wpdb->get_results($exist_Desc);
			if (!empty($existsing)) {
				$desciption  = $existsing[0]->description;
				if ($desciption) {
					$new_Description = $existsing[0]->description;
				}
			}

			$sql = $wpdb->prepare(
				"
				UPDATE {$wpdb->prefix}kb_category 
				SET 
				    name=%s,
				    alias_name=%s,
				    description=%s,
				    keywords=%d 
				WHERE id_cat=%s",
				$category[1],
				$category[2],
				$new_Description,
				$order,
				$category[0]
			);
			$wpdb->query($sql);
			$order++;
		}
	}


	if (empty($updated_channel) && $update) { // Channels not modified, update Kbuckets
		$kb_db = $wpdb->get_results("SELECT id_kbucket, KID FROM {$wpdb->prefix}kbucket", ARRAY_A);
		$kb_compare = [];
		foreach ($kb_db as $item) {
			$kb_compare[$item['KID']] = $item['id_kbucket'];
		}

		$_upd = $_ins = $_del = 0;
		$kb_arr_image = [];
		foreach ($parsed['kbuckets'] as $k => $kbucket) {
			if (!empty($kb_compare[$kbucket[1]])) unset($kb_compare[$kbucket[1]]);
			$kb_exist = $wpdb->prepare("
				SELECT id_kbucket, last_updated 
				FROM {$wpdb->prefix}kbucket 
				WHERE KID = %s", $kbucket[1]);
			$kb_exist = $wpdb->get_results($kb_exist, ARRAY_A);
			$new = false;

			if (!empty($kbucket[13])) { //$attrModified
				$kbucket[13] = (int)$kbucket[13];
			}

			if (!empty($kb_exist[0]['id_kbucket'])) { // If kbucket exists
				if (empty($kbucket[13]) || $kbucket[13] === (int)$kb_exist[0]['last_updated']) { // Modified date not changed, continue
					continue;
				}
			} else {
				$new = true;
			}

			$kbucketsValues = [];
			foreach ($kbucket as $item) {
				$kbucketsValues[] = $item;
			}

			if (!empty($kbucket[13]))
				$kbucketsValues[13] = $kbucket[13];

			if ($new) {
				$res = $wpdb->prepare("
					INSERT IGNORE INTO {$wpdb->prefix}kbucket (
						id_kbucket,
						KID,
						id_cat,
						title,
						description,
						link,
						author,
						publisher,
						author_alias,
						publisher_alias,
						twitter,
						pub_date,
						add_date,
						last_updated,
						image_url,
						short_url,
						url_kbucket
					) 
				    VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)", $kbucketsValues);
				$_ins += $wpdb->query($res);
			} else {
				//				$kbucketsValues[] = $kbucket[0];
				$kbucketsValues[] = $kbucket[1];
				$res = $wpdb->prepare("
					UPDATE {$wpdb->prefix}kbucket SET 
						id_kbucket=%s,
						KID=%s,
						id_cat=%s,
						title=%s,
						description=%s,
						link=%s,
						author=%s,
						publisher=%s,
						author_alias=%s,
						publisher_alias=%s,
						twitter=%s,
						pub_date=%s,
						add_date=%s,
						last_updated=%s,
						image_url=%s,
						short_url=%s,
						url_kbucket=%s 
					WHERE KID=%s", $kbucketsValues);
				$_upd += $wpdb->query($res);
			}
			// Collect images
			if (!empty($parsed['images'][$kbucket[0]]))
				$kb_arr_image[$kbucket[0]] = $parsed['images'][$kbucket[0]];
		}

		if (!empty($kb_compare)) {
			foreach ($kb_compare as $id_kbucket => $KID) {
				$res = $wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}kbucket WHERE id_kbucket=%s AND KID=%s",
					$KID,
					$id_kbucket
				);
				$_del += $wpdb->query($res);
			}
		}

		if ($_upd) $resMsg[] .= 'Updated Kbuckets: ' . $_upd;
		if ($_ins) $resMsg[] .= 'Added Kbuckets: ' . $_ins;
		if ($_del) $resMsg[] .= 'Deleted Kbuckets: ' . $_del;
	} else { // Insert kbuckets
		$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}kbucket");
		$kbucketsSql = "
		INSERT IGNORE INTO {$wpdb->prefix}kbucket (
			id_kbucket,
			KID,
			id_cat,
			title,
			description,
			link,
			author,
			publisher,
			author_alias,
			publisher_alias,
			twitter,
			pub_date,
			add_date,
			last_updated,
			image_url,
			short_url,
			url_kbucket
		) VALUES ";
		$kb_err = $placeholders = $kb_arr_image = [];

		$kb_counts = 0;
		$placeholders[] = "(%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)\n";
		$kbucketsSql .= implode(',', $placeholders);
		foreach ($parsed['kbuckets'] as $kbucket) {
			$kbucketsValues = [];
			foreach ($kbucket as $item) {
				$kbucketsValues[] = $item;
			}

			if (!empty($parsed['images'][$kbucket[0]]))
				$kb_arr_image[$kbucket[0]] = $parsed['images'][$kbucket[0]];


			$res = $wpdb->prepare($kbucketsSql, $kbucketsValues);
			$query = $wpdb->query($res);
			if ($query) $kb_counts++;
			else $kb_err[] = $kbucket[0];
		}
		if ($kbucketsSql) {
			$resMsg[] = 'Inserted Kbuckets: ' . $kb_counts;
		}
		if ($kb_err) {
			$resMsg[] = 'Kbuckets with errors: ' . implode(',', $kb_err);
		}
	}

	// Insert Tags
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}kb_tag_details");
	$wpdb->query("TRUNCATE TABLE {$wpdb->prefix}kb_tags");

	$tags_cnt = 0;
	foreach ($parsed['tags'] as $tag => $KIDs) {
		$wpdb->insert(
			"{$wpdb->prefix}kb_tag_details",
			[
				'name' => $tag,
				'alias_name' => sanitize_title_with_dashes($tag)
			]
		);
		$detail_id = $wpdb->insert_id;
		foreach ($KIDs as $KI_d) {
			$wpdb->insert(
				"{$wpdb->prefix}kb_tags",
				[
					'id_kbucket' => $KI_d,
					'id_tag' => $detail_id
				]
			);
		}
		$tags_cnt++;
	}
	$resMsg[] = 'Inserted Tags: ' . $tags_cnt;



	$resMsg[] = 'KBuckets uploaded successfully!';
	$trial = kb_isTrialKey();
	if ($trial) $resMsg[] = 'You have entered the trial key. Your kbuckets limit is 100 in every category';


	return array(
		'debug'         => '',
		'success'       => $resMsg,
		'kb_arr_image'  => $kb_arr_image,
		'cnt'           => count($kb_arr_image)
	);
}


function kb_empty_tables_start($blog_id)
{
	global $wpdb;
	$prefix = $wpdb->get_blog_prefix($blog_id);
	$truncateTables = array(
		$prefix . 'kb_category',
		$prefix . 'kb_tags',
		$prefix . 'kb_tag_details',
		$prefix . 'kb_files',
		$prefix . 'kbucket'
	);
	foreach ($truncateTables as $table) {
		$wpdb->query("TRUNCATE TABLE {$table}");
	}
}

function kb_remove_attaches_kbuckets($blog_id)
{
	global $wpdb;
	$prefix = $wpdb->get_blog_prefix($blog_id);
	$sql = "SELECT post_id FROM {$prefix}kbucket WHERE post_id IS NOT NULL";
	$res = $wpdb->get_results($sql);
	foreach ($res as $item) {
		wp_delete_attachment($item->post_id, true);
	}
}

function kb_check_uniq_page_slug($search, $array, $count = 2)
{
	if (!in_array($search, $array)) return $search;
	else {
		$search = $search . '-' . $count;
		kb_check_uniq_page_slug($search, $array, $count + 1);
	}
	return $search;
}

function kb_ajax_upload_xml()
{
	$apikey = kb_getAPIKey();
	if (!$apikey) {
		die(json_encode(array(
			'error' => 'You need to upgrade your API key! Please, go to http://optimalaccess.com/auth-reg/'
		)));
	}


	if ($_FILES['file']['error']) {
		die(json_encode(array(
			'error' => 'Cannot upload the file',
			'extend' => $_FILES['error']
		)));
	}

	$sourcePath = $_FILES['file']['tmp_name'];

	if (empty($sourcePath)) {
		die(json_encode(array(
			'error' => __("Please select file!", WPKB_TEXTDOMAIN),
		)));
	}
	$targetPath = WPKB_PATH . 'uploads/' . str_replace(' ', '_', $_FILES['file']['name']);
	if (!file_exists(WPKB_PATH . 'uploads/')) mkdir(WPKB_PATH . 'uploads/', 0755, true);
	move_uploaded_file($sourcePath, $targetPath);
	die(json_encode(array(
		'success' => WPKB_PATH . 'uploads/' . str_replace(' ', '_', $_FILES['file']['name']),
		'file' => str_replace(' ', '_', $_FILES['file']['name'])
	)));
}
add_action('wp_ajax_ajax_upload_kbucket', 'kb_ajax_upload_xml');

function kb_ajax_scheduled_kbucket()
{
	kb_activate_cron_upload();
	$kb_auto_upload = get_option('kb_auto_upload');
	$kb_auto_upload['logs'] = kb_get_auto_upload_logs();
	if (!empty($kb_auto_upload)) {
		wp_send_json($kb_auto_upload);
	} else {
		wp_send_json([
			"logs" => kb_get_auto_upload_logs(),
			"url" => "",
			"interval" => 'day',
			'active' => false
		]);
	}
}
add_action('wp_ajax_ajax_scheduled_kbucket', 'kb_ajax_scheduled_kbucket');

function ajax_purge_kbuckets()
{
	if (is_super_admin()) {
		$blog_id = !empty($_REQUEST['blog_id']) ? $_REQUEST['blog_id'] : get_current_blog_id();;
		kb_remove_attaches_kbuckets($blog_id);
		kb_empty_tables_start($blog_id);
		update_option('kb_auto_upload_stage', '');
		update_option('kb_auto_upload_logs', []);
		update_option('kb_auto_upload_file', '');
		update_option('kb_auto_images', '');
		update_option('kb_auto_images_all', '');
		update_option('kb_auto_upload_image_hold', 0);
		wp_send_json([
			'success' => true
		]);
	} else {
		wp_send_json([
			'success' => false
		]);
	}
}
add_action('wp_ajax_ajax_purge_kbuckets', 'ajax_purge_kbuckets');

function kb_ajax_activate_scheduled_kbucket()
{
	wp_clear_scheduled_hook('kb_activate_cron_upload_event');
	if ($_REQUEST['activate'] == 'true') {
		$kb_auto_upload = [
			"logs" => kb_get_auto_upload_logs(),
			"url" => esc_url($_REQUEST['url']),
			"interval" => esc_attr($_REQUEST['interval']),
			'active' => true
		];

		if (!empty($_REQUEST['initial_start'])) {
			update_option('kb_auto_upload_stage', '');
			update_option('kb_auto_upload_logs', []);
			update_option('kb_auto_upload_file', '');
			update_option('kb_auto_images', '');
			update_option('kb_auto_images_all', '');
			update_option('kb_auto_upload_image_hold', 0);
			$kb_auto_upload['logs'] = [];
		}
		update_option('kb_auto_upload', $kb_auto_upload);
		kb_auto_upload_put_log('Start process', true);
	} else {
		$kb_auto_upload = get_option('kb_auto_upload');
		if (empty($kb_auto_upload['active'])) {
			$kb_auto_upload =  [
				"logs" => kb_get_auto_upload_logs(),
				"url" => esc_url($_REQUEST['url']),
				"interval" => esc_attr($_REQUEST['interval']),
				'active' => false
			];
		}
		$kb_auto_upload['active'] = false;
		update_option('kb_auto_upload', $kb_auto_upload);
	}
	wp_send_json($kb_auto_upload);
}
add_action('wp_ajax_ajax_activate_scheduled_kbucket', 'kb_ajax_activate_scheduled_kbucket');

function kb_get_auto_upload_logs($limit = -100)
{
	if (WPKB_PLUGIN_LOGS_DB) {
		if (empty(get_option('kb_auto_upload_logs'))) return [];
		if ($limit) return array_slice((array)get_option('kb_auto_upload_logs'), $limit);
		else return (array)get_option('kb_auto_upload_logs');
	} else {
		$fp = fopen(WPKB_PATH . '/logs/' . date('d-m-Y') . '-auto-upload.log', 'a+');
		if (!$fp) {
			return false;
		}

		// Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
		flock($fp, LOCK_EX);

		$lines = array();
		while (!feof($fp)) {
			$l = rtrim(fgets($fp), "\r\n");
			if (!empty($l)) {
				$lines[] = $l;
			}
		}

		fclose($fp);
		if (!empty($lines)) return $lines;
		return [];
	}
}

function kb_auto_upload_put_log($message = '', $clear = false)
{
	if ($clear) $log = [];
	else $log = kb_get_auto_upload_logs(0);
	$message = time() . "|" . $message;
	$log[] = $message;
	if (WPKB_PLUGIN_LOGS_DB) update_option('kb_auto_upload_logs', $log);
	else {
		$fp = fopen(WPKB_PATH . '/logs/' . date('d-m-Y') . '-auto-upload.log', 'a+');
		if (!$fp) {
			return false;
		}
		if ($clear) {
			ftruncate($fp, 0);
		}

		// Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
		flock($fp, LOCK_EX);

		fwrite($fp, $message . "\r\n");
		fclose($fp);
	}
}

function kb_activate_auto_stage_upload()
{
	$stage = get_option('kb_auto_upload_stage');
	if ($stage == 'done' || empty($stage)) {
		$kb_auto_upload = get_option('kb_auto_upload');
		if (!empty($kb_auto_upload['url'])) {
			if (!empty($kb_auto_upload['active']) && $kb_auto_upload['active'] == 'true') {
				$file = (string)kb_file_get_contents($kb_auto_upload['url']);
				if (!empty($file)) {
					$file = openssl_encrypt($file, "AES-128-CTR", "KBucket-Crypt");
					kb_auto_upload_put_log('File Uploaded!<br>');
					update_option('kb_auto_upload_file', $file);
					update_option('kb_auto_upload_stage', 'uploaded');
				} else {
					kb_auto_upload_put_log('File is empty!<br>');
				}
			}
		} else {
			kb_auto_upload_put_log('Settings empty!<br>');
		}
	}
	return true;
}

function kb_file_get_contents($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function kb_activate_auto_stage_parsed()
{
	$stage = get_option('kb_auto_upload_stage');
	$file = get_option('kb_auto_upload_file');
	$file = openssl_decrypt($file, "AES-128-CTR", "KBucket-Crypt");

	if (empty($file)) {
		update_option('kb_auto_upload_stage', '');
		update_option('kb_auto_upload_image_hold', 0);
		kb_auto_upload_put_log("File empty <br>");
		//		$kb_auto_upload = get_option('kb_auto_upload');
		//		$kb_auto_upload['active'] = false;
		//		update_option('kb_auto_upload', $kb_auto_upload);
		return false;
	}
	if ($stage == 'parsed') return true;
	if ($stage == 'uploaded') {
		require_once(ABSPATH . '/wp-admin/includes/taxonomy.php');
		kb_auto_upload_put_log('Begin parsing<strong class="saving"><span>.</span><span>.</span><span>.</span></strong><br>');
		$res = kb_parsed_add_db($file);

		if (!empty($res['success'])) {
			foreach ($res['success'] as $msg) {
				kb_auto_upload_put_log($msg . '<br>');
			}
			if (empty($res['kb_arr_image'])) { // On update may be empty updated kbuckets
				kb_auto_upload_put_log("Available images not found!<br>");
				update_option('kb_auto_upload_stage', 'parsed');
			} else {
				kb_auto_upload_put_log("Parsing complete! Parsed " . count($res['kb_arr_image']) . " images<br>");
				$images = [];
				foreach ($res['kb_arr_image'] as $k => $re) {
					$re['key'] = $k;
					$images[] = $re;
				}
				$cnt = count($images);
				update_option('kb_auto_images', json_encode($images));
				update_option('kb_auto_images_all', $cnt);
				kb_auto_upload_put_log($cnt . " images prepared for  kbuckets.<br>");
				update_option('kb_auto_upload_stage', 'parsed');
			}
		} else {
			kb_auto_upload_put_log('Parsing file not success!<br>');
		}
	}
	return true;
}

function kb_activate_auto_stage_images()
{
	$stage = get_option('kb_auto_upload_stage');
	if ($stage == 'done' || $stage == '' || $stage == 'uploaded') {
		return true;
	}
	$images_list = json_decode(get_option('kb_auto_images'), ARRAY_A);

	//	preg_match('/images_([0-9]+)/', $stage, $matches);


	$all = get_option('kb_auto_images_all');
	if (!empty($images_list)) {
		$count = 0;

		$logs = kb_get_auto_upload_logs(-1);
		if (!empty($logs[0])) {
			$logs = explode('|', $logs[0]);
			if (!empty($logs[0])) {
				if (time() > $logs[0] + 3 * 60) {
					update_option('kb_auto_upload_image_hold', 0);
				}
			}
		}

		foreach ($images_list as $num => $image) {
			//		$image = reset($images_list);
			//		$num = key($images_list);
			update_option('kb_auto_upload_stage', "images_{$num}");
			$res = kb_ajax_upload_image($image);
			if (!empty($res['status'])) {
				kb_auto_upload_put_log("Progress upload: <a data-uploaded='{$res['id']}' target='_blank' title='{$image['name']}' href='{$image['image']}'>Image</a> " . ($num + 1) . "/{$all} - <b>(Status: {$res['status']})</b>");
				unset($images_list[$num]);
				update_option('kb_auto_images', json_encode($images_list));
			}
			$count++;
			if ($count > 4) break;
		}
	} else {
		update_option('kb_auto_upload_stage', "done");
		kb_auto_upload_put_log('All stages finished!');
		$settings = get_option('kb_auto_upload');
		if (empty($settings['interval'])) {
			$settings['active'] = 0;
			update_option('kb_auto_upload', $settings);
		}
	}
}



function kb_activate_auto_upload_kbucket()
{
	/*
	 * Stage of previous upload
	 * 'uploaded' - uploaded
	 * 'parsed' - parsed
	 * 'images' - image uploaded
	 * 'images_01' - image stage uploaded
	 * 'done' - all down
	 */

	kb_activate_auto_stage_upload();
	kb_activate_auto_stage_parsed();
	kb_activate_auto_stage_images();
}

function kb_ajax_upload_image($img)
{
	$hold = (int)get_option('kb_auto_upload_image_hold');
	if ($hold === 1) return false;
	update_option('kb_auto_upload_image_hold', 1);
	$image = !empty($img['image']) ? $img['image'] : '';
	$name  = !empty($img['name']) ? $img['name'] : '';
	$uid   = $img['key'];
	if (empty($image)) return array(
		'status' => 'Empty'
	);

	if ($s = kb_update_image($uid, $image, $name)) {
		if (substr($s, 0, 3) === '000') {
			$status = 'Error ' . $s;
		} else {
			$status = 'Success';
		}
	} else {
		$status = 'Error';
	}
	update_option('kb_auto_upload_image_hold', 0);
	return array(
		'status' => $status,
		'id' => $s
	);
}


function kb_ajax_upload_images($images = [])
{
	$kb_arr_image = array();
	if (!empty($images)) {
		$kb_arr_image = $images;
	} else {
		if (isset($_POST['images'])) {
			$kb_arr_image = $_POST['images'];
		}
	}

	/*
	$kb_arr_image =
	[
		[
			image => Image URL,
			name => Title of kbucket
		]
	]
	...

	*/
	$inc = $alt_inc = 0;
	$files = $empty = $ex_files = array();

	if (!empty($kb_arr_image)) {
		$urls = [];
		foreach ($kb_arr_image as $post_arr) {
			$image = !empty($post_arr['image']) ? $post_arr['image'] : '';
			$name  = !empty($post_arr['name']) ? $post_arr['name'] : '';
			$uid   = $post_arr['key'];

			if ($url = kb_update_image($uid, $image, $name)) {
				$files[$uid] = $image;
				$inc++;
				$urls[] = $url;
			} else if (empty($image)) {
				$empty[$uid] = $name;
			} else {
				$ex_files[$uid] = $image;
			}

			$alt_inc++;
		}
	}

	if (!empty($images)) {
		return array(
			'cnt'        => count($kb_arr_image),
			'success'    => true,
			's_uploaded' => $inc,
			'e_uploaded' => count($ex_files),
			'm_uploaded' => count($empty),
		);
	} else {
		wp_send_json(
			array(
				'cnt'        => count($kb_arr_image),
				'success'    => true,
				's_uploaded' => $inc,
				'e_uploaded' => count($ex_files),
				'm_uploaded' => count($empty),
				'images' => $urls
			)
		);
	}
	return false;
}
add_action('wp_ajax_ajax_upload_img_kbucket', 'kb_ajax_upload_images');

/**
 * Upload or assign image to Kbucket
 *
 * @param $uid - Kbucket ID
 * @param $image_url - URL of image
 * @param $attrName - Kbucket Title
 */
function kb_update_image($UID, $image_url, $attrName, $user_id = 1)
{
	global $wpdb;
	if (!empty($image_url)) {
		$attrImageInnerID = addFeaturedImg($image_url, $attrName, $user_id);
		if ($attrImageInnerID && substr($attrImageInnerID, 0, 3) === '000') return $attrImageInnerID;
		$attrImageInnerUrl = '';
		if ($attrImageInnerID) $attrImageInnerUrl = wp_get_attachment_url($attrImageInnerID);

		if (!empty($attrImageInnerUrl)) {
			$sql = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}kbucket 
						SET image_url=%s, post_id=%d 
						WHERE id_kbucket=%s",
				$attrImageInnerUrl,
				$attrImageInnerID,
				$UID
			);
			$wpdb->query($sql);
			return $attrImageInnerUrl;
		}
	} else { // Find photo in database and assign
		$post_id = kb_get_image_by_uid($UID);
		$attrImageInnerUrl = wp_get_attachment_url($post_id);
		if ($post_id) {
			$sql = $wpdb->prepare("UPDATE {$wpdb->prefix}kbucket SET image_url=%s WHERE id_kbucket=%s", $attrImageInnerUrl, $UID);
			$wpdb->query($sql);
			return $attrImageInnerUrl;
		}
	}
	return false;
}

function kb_get_image_by_uid($UID)
{
	global $wpdb;
	$sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key=%s AND meta_value=%s", 'kb_custom_photo_' . $UID, $UID);
	$post_id = $wpdb->get_row($sql, ARRAY_A);
	if (!empty($post_id['post_id'])) return $post_id['post_id'];
	return false;
}

function kb_get_attachment_id($url, $size = 0, $uid = 0)
{
	$attachment_id = 0;

	global $wpdb;

	$url = html_entity_decode($url);

	if (!$size) {
		$sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='wp_attachment_orig_url' AND meta_value=%s", $url);
	} else {
		$sql = $wpdb->prepare(
			"
			SELECT post_id FROM {$wpdb->prefix}postmeta a
			LEFT JOIN {$wpdb->prefix}postmeta b on b.meta_id  = a.meta_id
			WHERE a.meta_key=%s AND a.meta_value=%s AND b.meta_key=%s AND b.meta_value=%s",
			'wp_attachment_orig_url',
			$url,
			'wp_attachment_size',
			(int)$size
		);
	}

	$query = $wpdb->get_results($sql);

	if (count($query)) {
		return $query[0]->post_id;
	} else return false;
}
function kb_get_filetype_by_curl($url)
{

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$results = explode("\n", trim(curl_exec($ch)));
	curl_close($ch);

	foreach ($results as $line) {
		if (strtok($line, ':') == 'Content-Type' || strtok($line, ':') == 'content-type') {
			$parts = explode(":", $line);
			switch (trim($parts[1])) {
				case 'image/jpeg':
					return 'jpg';
				case 'image/png':
					return 'png';
				case 'image/gif':
					return 'gif';
				default:
					return false;
			}
		}
	}

	return false;
}
function kb_get_extension_by_name($image_name)
{
	if (stripos($image_name, '#') !== false) $image_name = strstr($image_name, '#', true);
	$extension = pathinfo($image_name, PATHINFO_EXTENSION);
	if ($pos = strpos($extension, '?')) $extension = substr_replace($extension, '', $pos, strlen($extension));
	if ($pos = strpos($extension, '&')) $extension = substr_replace($extension, '', $pos, strlen($extension));
	if ($pos = strpos($extension, '%')) $extension = substr_replace($extension, '', $pos, strlen($extension));
	$extension = strtolower($extension);
	return $extension;
}

/**
 * Returns the size of a file without downloading it, or -1 if the file
 * size could not be determined.
 *
 * @param $url - The location of the remote file to download. Cannot
 * be null or empty.
 *
 * @return int|string The size of the file referenced by $url, or -1 if the size
 * could not be determined.
 */
function kb_curl_get_file_size($url)
{
	// Assume failure.
	$result = -1;

	$curl = curl_init($url);

	// Issue a HEAD request and follow any redirects.
	curl_setopt($curl, CURLOPT_NOBODY, true);
	curl_setopt($curl, CURLOPT_HEADER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	//curl_setopt( $curl, CURLOPT_USERAGENT, get_user_agent_string() );

	$data = curl_exec($curl);
	curl_close($curl);

	if ($data) {
		$content_length = "unknown";
		$status = "unknown";

		if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
			$status = (int)$matches[1];
		}

		if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
			$content_length = (int)$matches[1];
		}

		// http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
		if ($status == 200 || ($status > 300 && $status <= 308)) {
			$result = $content_length;
		}
	}
	return $result;
}

/**
 * Set thumbnail image to the post
 * @param integer $post_id: Current the post
 * @param string $image_url: Uri of image
 * @return void|int
 * Error codes:
 * 00011 - empty image name
 * 00012 - can't upload image
 * 00013 - empty extension
 * 00014 - not allowed extension
 * 00015 - file already exists
 * @author Max Pilegi <3972349@gmail.com>
 * @version 2.1 (Correct work if has the image in destination)
 */
function addFeaturedImg($image_url, $title, $uid = 0)
{
	global $wpdb;
	$image_url = trim($image_url);
	$title = trim($title);

	// Check image on begin //
	if (preg_match('/^\/\//i', $image_url)) $image_url = 'http:' . $image_url;

	$image_name = basename($image_url);
	if (empty($image_name)) return '00011';

	$extension = kb_get_extension_by_name($image_name);
	if (empty($extension)) return '00013';
	if (!in_array($extension, ['jpeg', 'jpg', 'png', 'webp', 'svg', 'gif'])) return '00014';

	// Prepare for new image

	// Uniq name to image
	$ecr_title = md5($image_url) . '_' . $uid . '.' . $extension;

	$sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta 
		WHERE meta_key='_wp_attached_file' AND meta_value LIKE %s", "%$ecr_title");
	$post_id = $wpdb->get_row($sql, ARRAY_A);
	if (!empty($post_id['post_id'])) {
		return $post_id['post_id'];
	}


	$upload_dir = wp_upload_dir();
	$file_path = $upload_dir['path'] . '/' . $ecr_title;

	$sql = $wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='wp_attachment_url' AND meta_value=%s", $file_path);
	$post = $wpdb->get_row($sql, ARRAY_A);
	if (!empty($post['post_id'])) return $post['post_id'];

	// Copy image to destination
	if (!file_exists($file_path)) {

		$image = kb_copy_image_by_curl($image_url, $file_path);

		if (!$image) return '00012';

		$file_url = $upload_dir['url'] . '/' . $ecr_title;

		$user_id = $uid;

		$wp_filetype = wp_check_filetype($file_path, null);
		$attachment = array(
			'guid'				=> $file_url,
			'post_mime_type'	=> $wp_filetype['type'],
			'post_title'		=> preg_replace('/\.[^.]+$/', '', $image_name),
			'post_content'		=> '',
			'post_status'		=> 'inherit',
			'post_author'		=> $user_id
		);
		$attach_id = wp_insert_attachment($attachment, $file_path);
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
		wp_update_attachment_metadata($attach_id, $attach_data);

		if (!empty($file_path)) update_post_meta($attach_id, 'wp_attachment_url', html_entity_decode($file_path));
		if (!empty($image_url)) update_post_meta($attach_id, 'wp_attachment_orig_url', html_entity_decode($image_url));
		if (!empty($file_path)) update_post_meta($attach_id, 'wp_attachment_size', filesize($file_path));

		return $attach_id;
	} else {
		return '00015';
	}
}

function kb_copy_image_by_curl($url, $destination)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// Add these lines for SSL support
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Use for development only 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Use for development only 

	$image = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	if (!empty($info['redirect_url'])) {
		return kb_copy_image_by_curl($info['redirect_url'], $destination);
	}

	if (!empty($info['http_code']) && in_array($info['http_code'], [200, 301, 505])) {
		file_put_contents($destination, $image);
		return true;
	} else {
		return false;
	}
}


function kb_on_remove_attach($attach_id)
{
	global $wpdb;
	$attrImageUrl = wp_get_attachment_url($attach_id);
	if (!empty($attrImageUrl)) {
		$sql = $wpdb->prepare("UPDATE {$wpdb->prefix}kbucket SET image_url='' WHERE image_url=%s", $attrImageUrl);
		$res = $wpdb->query($sql);
	}

	delete_post_meta($attach_id, 'wp_attachment_url');
	delete_post_meta($attach_id, 'wp_attachment_size');
	delete_post_meta($attach_id, 'kb_custom_photo');
	delete_post_meta($attach_id, 'wp_attachment_orig_url');
}
