<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    class KBauthorTags extends WP_Widget {
        function __construct() {
            parent::__construct(false, $name = __('Kbucket tags'), array('description' => __('Show tags from kbucket plugin')));
        }
        function widget($args, $instance) {
            extract( $args );
            //$action = KBucket::get_kbucket_instance();
            $url_parts = kb_parse_url();
            if(!is_kbucket_page_func()) return false;


            echo($args['before_widget']);
            echo($args['before_title'].$instance['title'].$args['after_title']);?>

            <div id="kb-tags-wrap-1" class="kb_is_mobile_menu">
                <?php
                $settings = kb_get_settings();

                if(isset($settings['cat_sidebar']) && (int)$settings['cat_sidebar'] == 2) kb_render_sidebar_navigation();

                // Render category tags
                $categoryTags = kb_get_category_tags();
                if ( ! empty( $categoryTags ) ) { ?>
                    <h3>Category Tags <?php if(kb_has_tag_tagcloud(kb_get_active_tagname(), $categoryTags)) echo '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>'; ?></h3>
                    <?php

                    kb_render_tags_cloud(
                        $categoryTags,
                        'm',
                        'main',
                        array( 'value' => kb_get_active_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
                        'c-tag'
                    );
                }

                if(!empty($url_parts['c-tag']) && !empty($url_parts['subcat'])){
                    $relatedTags = kb_get_related_tags($url_parts['subcat'], $url_parts['c-tag']);

                    // Render related tags
                    if ( ! empty( $relatedTags ) ) { ?>
                        <h3>Related Tags <?php if(kb_has_tag_tagcloud(kb_get_active_related_tagname(), $relatedTags)) echo '<a href="'.kb_get_kbucket_url('related').'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>'; ?></h3>
                        <?php
                        kb_render_tags_cloud(
                         $relatedTags,
                            'm',
                            'related',
                            array( 'value' => kb_get_active_related_tagname(), 'dbKey' => 'name', 'title' => 'name' ),
                            'r-tag'
                        );
                    }
                }



                // Render Author tags
                if ( $settings['atd'] ) {
                    $authorTags = kb_get_author_tags(); ?>
                    <h3>Author Tags <?php if(kb_has_tag_tagcloud(kb_get_active_tagname(), $authorTags)) echo '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>'; ?></h3>
	                <?php
					kb_render_tags_cloud(
                        $authorTags,
                        'm',
                        'author',
                        array( 'value' => kb_get_active_tagname(), 'dbKey' => 'author', 'title' => 'author' ),
                        'a-tag'
                    );
                }

                // Render Publisher tags
	        	if ( $settings['ptd'] ) {
					$publisherTags = kb_get_publisher_tags();

					if(count($publisherTags)){ ?>
						<h3>Publisher Tags <?php if(kb_has_tag_tagcloud(kb_get_active_tagname(), $publisherTags)) echo '<a href="'.kb_get_kbucket_url().'" class="kb-clear-tag">'.__("Reset", WPKB_TEXTDOMAIN).'</a>'; ?></h3>
						<?php
						kb_render_tags_cloud(
							$publisherTags,
							'm',
							'publisher',
							array( 'value' => kb_get_active_tagname(), 'dbKey' => 'publisher', 'title' => 'publisher' ),
							'p-tag'
						);
					}
		        }
                ?>
            </div>

            <?php
            echo($args['after_widget']);
        }

        function update($new_instance, $old_instance) {
            if(!isset($new_instance[ 'only_kb_page' ])) $new_instance[ 'only_kb_page' ] = false;
            return $new_instance;
        }

        function form($instance) {
            $title = '';
            $count_elm = true;
            if (isset( $instance['title'])) $title = esc_attr($instance['title']);
            else $title = 'Your Title';
            $only_kb_page = (isset( $instance['only_kb_page']) && $instance['only_kb_page']) ? true : false; ?>
            <p>
                <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:',WPKB_TEXTDOMAIN); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
                </label>
            </p>
            <?php
        }
    }
    function wd_KBauthorTags() { register_widget('KBauthorTags'); }
    add_action('widgets_init', 'wd_KBauthorTags');