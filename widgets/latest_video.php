<?php
/**
 * Widget Name: Latest Videos
 * Description: A widget that allows you to display the latest videos from your library.
 * Version: 0.1
 *
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'latest_videos_load_widgets' );

/**
 * Register our widget.
 * 'Latest_Videos_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function latest_videos_load_widgets() {
	register_widget( 'Latest_Videos_Widget' );
}

/**
 * Custom Category Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Latest_Videos_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Latest_Videos_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_latest_videos', 'description' => esc_html__('The most recent videos with teaser text', 'kwik') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 150, 'height' => 350, 'id_base' => 'latest-videos-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'latest-videos-widget', esc_html__('Kwik Videos: Recent Videos', 'kwik'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$category_id = $instance['category_id'];
		$num_videos = absint( $instance['num_videos'] );
		$post_offset =  absint( $instance['post_offset'] );
		$num_words_limit = absint( $instance['num_words_limit'] );
		$show_date_author = isset( $instance['show_date_author'] ) ? $instance['show_date_author'] : false;
		$show_more_link = isset( $instance['show_more_link'] ) ? $instance['show_more_link'] : false;
		$show_thumbs = isset( $instance['show_thumbs'] ) ? $instance['show_thumbs'] : false;
		$thumb_frame_shadow = isset( $instance['thumb_frame_shadow'] ) ? $instance['thumb_frame_shadow'] : false;
		$post_thumb_width = absint( $instance['post_thumb_width'] );
		$post_thumb_height = absint( $instance['post_thumb_height'] );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
		    echo $before_title . $title . $after_title;

		/* Display the Latest Videos accordinly... */
		$cats_to_include = ( $category_id ) ? "tax_query={$category_id}&": '';
		$num_videos_query = new WP_Query( "{$cats_to_include}showposts={$num_videos}&post_type=videos&offset={$post_offset}" );
		if( $num_videos_query->have_posts()) : ?>
		    <div class="latest_videos">
			<ul class="small-thumb">
<?php			    while( $num_videos_query->have_posts()) : $num_videos_query->the_post();
			    update_post_caches($posts); ?>
				<li>
<?php                               if ( $show_thumbs ) { include (TEMPLATEPATH . '/scripts/post-thumbnail.php'); } ?>
				    <a class="teaser-title" title="<?php the_title(); ?>" href="<?php the_permalink() ?>"><?php the_title(); ?></a>

				    <div class="teaser-content"><?php if ( $num_words_limit ) echo kv_neat_trim( get_the_excerpt(), $num_words_limit ); ?></div>
<?php				    if ( $show_more_link )  : ?>
                                        <a title="<?php the_title_attribute(); ?>" href="<?php the_permalink(); ?>" class="read-more-align-right"><span><?php esc_html_e('Read more', 'kwik'); ?></span> &rarr;</a>
<?php				    endif; ?>
				    <div class="clear"></div>
				</li>
<?php			    endwhile; ?>
			</ul>
		    </div><!-- end widget_recent_videos -->
<?php		endif;
                wp_reset_postdata();

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['num_videos'] = strip_tags( $new_instance['num_videos'] );
		$instance['post_offset'] = strip_tags( $new_instance['post_offset'] );
		$instance['num_words_limit'] = strip_tags( $new_instance['num_words_limit'] );
		/* No need to strip tags for dropdowns and checkboxes. */
		$instance['category_id'] = $new_instance['category_id'];
		$instance['show_date_author'] = $new_instance['show_date_author'];
		$instance['show_more_link'] = $new_instance['show_more_link'];
		$instance['show_thumbs'] = $new_instance['show_thumbs'];
		$instance['thumb_frame_shadow'] = $new_instance['thumb_frame_shadow'];
		$instance['post_thumb_width'] = ( $new_instance['post_thumb_width'] ) ? absint(strip_tags( $new_instance['post_thumb_width'] )) : 60;
		$instance['post_thumb_height'] = ( $new_instance['post_thumb_height'] ) ? absint(strip_tags( $new_instance['post_thumb_height'] )) : 60;

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Latest Videos', 'kwik'), 'category_id' => '', 'num_videos' => 3, 'post_offset' => 0, 'num_words_limit' => 13,'show_date_author' => false,'show_more_link' => false, 'show_thumbs' => true, 'thumb_frame_shadow' => false, 'post_thumb_width' => 60, 'post_thumb_height' => 60 );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>

		<!-- Show Categories -->
		<p>
			<label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php esc_html_e('Pick a specific category:', 'kwik'); ?></label>
			<?php wp_dropdown_categories('taxonomy=video_cats&show_option_all=All&hierarchical=1&orderby=name&selected='.$instance['category_id'].'&name='.$this->get_field_name( 'category_id' ).'&class=widefat'); ?>
		</p>

		<!-- Number of Posts -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_videos' ); ?>"><?php esc_html_e('Number of videos to show:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_videos' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_videos' ); ?>" value="<?php echo $instance['num_videos']; ?>" size="2" maxlength="2" />
			<br />
			<small><?php esc_html_e('(at most 15)', 'kwik'); ?></small>
		</p>

		<!-- Post Offset -->
		<p>
			<label for="<?php echo $this->get_field_id( 'post_offset' ); ?>"><?php esc_html_e('Number of videos to skip:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'post_offset' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_offset' ); ?>" value="<?php echo $instance['post_offset']; ?>" size="2" maxlength="2" />
			<br />
			<small><?php esc_html_e('(offset from latest)', 'kwik'); ?></small>
		</p>

		<!-- Number of Words Limit -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_words_limit' ); ?>"><?php esc_html_e('Limit the number of words to show from each post:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_words_limit' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_words_limit' ); ?>" value="<?php echo $instance['num_words_limit']; ?>" size="2" maxlength="2" />
			<br />
			<small><?php esc_html_e('(Could also be limited by "Excerpt Length" defined in the theme\'s options page)', 'kwik'); ?></small>
		</p>

		<!-- Show date & author info checkbox -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_date_author' ); ?>">
			    <input class="checkbox" type="checkbox" <?php checked( $instance['show_date_author'], true ); ?> id="<?php echo $this->get_field_id( 'show_date_author' ); ?>" name="<?php echo $this->get_field_name( 'show_date_author' ); ?>" value="1" <?php checked('1', $instance['show_date_author']); ?> />
			    <?php esc_html_e('Show date & author info', 'kwik'); ?>
			</label>
		</p>
                
		<!-- Show "Read more ->" link checkbox -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_more_link' ); ?>">
			    <input class="checkbox" type="checkbox" <?php checked( $instance['show_more_link'], true ); ?> id="<?php echo $this->get_field_id( 'show_more_link' ); ?>" name="<?php echo $this->get_field_name( 'show_more_link' ); ?>" value="1" <?php checked('1', $instance['show_more_link']); ?> />
			    <?php esc_html_e('Show "Read more &rarr;" link ', 'kwik'); ?>
			</label>
		</p>

		<!-- Show Thumbnails -->
		<p>
			<label for="<?php echo $this->get_field_id( 'show_thumbs' ); ?>">
			    <input class="checkbox" type="checkbox" <?php checked( $instance['show_thumbs'], true ); ?> id="<?php echo $this->get_field_id( 'show_thumbs' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbs' ); ?>" value="1" <?php checked('1', $instance['show_thumbs']); ?> />
			    <?php esc_html_e('Show thumbnails', 'kwik'); ?>
			</label>
		</p>
                            
<?php           if($instance['show_thumbs']) : ?>
                    Thumbnail Properties:
                    <div style="border: 1px solid #DDD; background-color: #F8F8F1; padding:7px;">
                        <!-- Thumbnail shadow ON/OFF -->
                        <p>
                                <label for="<?php echo $this->get_field_id( 'thumb_frame_shadow' ); ?>">
                                    <input class="checkbox" type="checkbox" <?php checked( $instance['thumb_frame_shadow'], true ); ?> id="<?php echo $this->get_field_id( 'thumb_frame_shadow' ); ?>" name="<?php echo $this->get_field_name( 'thumb_frame_shadow' ); ?>" value="1" <?php checked('1', $instance['thumb_frame_shadow']); ?> />
                                    <?php esc_html_e('Show thumbnail frame shadow', 'kwik'); ?>
                                </label>
                        </p>

                        <!-- Thumb Dimension -->
                        <p>
                                <label for="<?php echo $this->get_field_id( 'post_thumb_width' ); ?>"><?php esc_html_e('Thumbnail Dimensions:', 'kwik'); ?></label><br />
                                <input id="<?php echo $this->get_field_id( 'post_thumb_width' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_thumb_width' ); ?>" value="<?php echo $instance['post_thumb_width']; ?>" size="5" maxlength="4" />
                                <span> X </span>
                                <input id="<?php echo $this->get_field_id( 'post_thumb_height' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_thumb_height' ); ?>" value="<?php echo $instance['post_thumb_height']; ?>" size="5" maxlength="4" />
                                <br />
                                <?php esc_html_e('(Width X Height) in pixels', 'kwik'); ?>
                        </p>
                    </div>
<?php           endif; ?>
                        
<?php
	}
}


