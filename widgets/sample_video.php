<?php
/**
 * Widget Name: Video Widget
 * Description: A widget for displaying a sample video from your library
 * Version: 0.1
 *
 */

/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'sample_videos_load_widgets' );

/**
 * Register our widget.
 * 'Sample_Videos_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function sample_videos_load_widgets() {
	register_widget( 'Sample_Videos_Widget' );
}

/**
 * Single Video Widget
 * This class displays a video in a widget
 *
 * @since 0.1
 */
class Sample_Videos_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Sample_Videos_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_sample_videos', 'description' => esc_html__('Video Widget with teaser text', 'kwik') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 150, 'height' => 350, 'id_base' => 'sample-videos-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'sample-videos-widget', esc_html__('Kwik Videos: Sample Video', 'kwik'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$category_id = $instance['category_id'];
		$num_videos = 1;
		$post_offset =  absint( $instance['post_offset'] );
		$num_words_limit = absint( $instance['num_words_limit'] );
		$kv_widget_width = $instance['width'];
		$kv_widget_height = $instance['height'];
		$btn_text = $instance['btn_text'];
		$btn_link = $instance['btn_link'];
		$btn_target = $instance['btn_target'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
		    echo $before_title . $title . $after_title;

		/* Display the Sample Videos accordinly... */
		$cats_to_include = ( $category_id ) ? "tax_query={$category_id}&": '';
		$num_videos_query = new WP_Query( "{$cats_to_include}showposts={$num_videos}&post_type=videos&offset={$post_offset}" );
		if( $num_videos_query->have_posts()) : ?>
		    <div class="sample_videos">
			<ul class="small-thumb">
<?php while( $num_videos_query->have_posts()) : $num_videos_query->the_post(); update_post_caches($posts); ?>
				<li class="clearfix">
				    <a class="teaser-title" title="<?php the_title(); ?>" href="<?php the_permalink() ?>"><?php the_title(); ?></a>
<?php 


		$cover = wp_get_attachment_image_src( get_post_thumbnail_id(get_the_ID()), 'medium' );
		$cover_url = $cover['0'];	
		// get video source	
        $video_sources = get_post_meta(get_the_ID(), '_source', true);
		$videos = explode(",", $video_sources);

		$kwik_player  .= '<div class="video-js-box">
    <video id="kv_video" class="video-js" width="'.$kv_widget_width.'" height="'.$kv_widget_height.'" style="height:'.$kv_widget_height.'; width:'.$kv_widget_width.'" controls="controls" autoplay="autoplay" preload="auto" poster="'.$cover_url.'">';
	
	
		foreach($videos as $video){
			// Set the video resource to be used by the flash fallback        
			if (strstr($video, 'rtmp')) {
				$video_resource = "resource=rtmp:default:" . htmlentities($video);
			} else if (strstr($video, 'youtube.com')) {
				$video_resource = parse_url($video, PHP_URL_QUERY);
				$video_resource = parse_str($video_resource, $feed_url_output);
				$video_resource = "id=" . $feed_url_output['v'];
			} else {
				$video_resource = "resource=video:default:" . $video;
			}
			
			$file_ext = pathinfo($video, PATHINFO_EXTENSION);			

			$kwik_player .= '<source src="'.$video.'" type=\''.kv_mimeType($video).'\'/>';			
			
		} // end foreach videos
	
        $kwik_player .= '
            <object width="'.$kv_widget_width.'" height="'.$kv_widget_height.'" id="live_video_object" type="application/x-shockwave-flash" data="' . get_bloginfo('url') . '/wp-content/plugins/kwik_videos/lib/universalPlayer.swf" name="live_video_object" >
                <param value="true" name="allowfullscreen">
                <param name="wmode" value="opaque" />
                <param value="always" name="allowscriptaccess">
                <param value="high" name="quality">
                <param name="bgcolor" value="#ffffff"/>
                <param value="player.style.global='.$kv_options['player_theme'].'&amp;player.start.' . $video_resource . '&amp;player.controls.hd=false&amp;player.start.paused=false&amp;player.start.cover='.$cover_url.' name="flashvars">
            </object></video></div>';
			
			echo $kwik_player;

 ?>
				    <div class="teaser-content"><?php if ( $num_words_limit ) echo kv_neat_trim( get_the_excerpt(), $num_words_limit ); ?></div>
                    
                    <?php if ( $btn_text != '' ): echo '<a title="'.$btn_text.'" href="'.$btn_link.'" target="'.$btn_target.'" class="pngfix small-dark-button align-btn-left"><span class="pngfix">'.$btn_text.'</span></a>'; endif;?>

				</li>
<?php endwhile; ?>
			</ul>
		    </div><!-- end widget_recent_videos -->
<?php endif; wp_reset_postdata();

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
		$instance['post_offset'] = strip_tags( $new_instance['post_offset'] );
		$instance['num_words_limit'] = strip_tags( $new_instance['num_words_limit'] );
		$instance['width'] = strip_tags( $new_instance['width'] );
		$instance['height'] = strip_tags( $new_instance['height'] );
		$instance['btn_text'] = strip_tags( $new_instance['btn_text'] );
		$instance['btn_link'] = strip_tags( $new_instance['btn_link'] );
		$instance['btn_target'] = strip_tags( $new_instance['btn_target'] );
		/* No need to strip tags for dropdowns and checkboxes. */
		$instance['category_id'] = $new_instance['category_id'];

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Video Widget', 'kwik'), 'category_id' => '', 'num_videos' => 3, 'post_offset' => 0, 'num_words_limit' => 99, 'btn_text' => __('Sign Up Now', 'kwik'), 'btn_link' => get_bloginfo('url'), 'btn_target' => __('_self', 'kwik') );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
        
		<!-- Player Width and Height -->
		<p>
			<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php esc_html_e('Width:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'width' ); ?>" type="text" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php echo $instance['width']; ?>" class="widefat" /><br />
            <label for="<?php echo $this->get_field_id( 'height' ); ?>"><?php esc_html_e('Height:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'height' ); ?>" type="text" name="<?php echo $this->get_field_name( 'height' ); ?>" value="<?php echo $instance['height']; ?>" class="widefat" />
		</p>

		<!-- Show Categories -->
		<p>
			<label for="<?php echo $this->get_field_id( 'category_id' ); ?>"><?php esc_html_e('Pick a specific category:', 'kwik'); ?></label>
			<?php wp_dropdown_categories('taxonomy=video_cats&show_option_all=All&hierarchical=1&orderby=name&selected='.$instance['category_id'].'&name='.$this->get_field_name( 'category_id' ).'&class=widefat'); ?>
		</p>


		<!-- Post Offset -->
		<p>
			<label for="<?php echo $this->get_field_id( 'post_offset' ); ?>"><?php esc_html_e('Number of videos to skip:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'post_offset' ); ?>" type="text" name="<?php echo $this->get_field_name( 'post_offset' ); ?>" value="<?php echo $instance['post_offset']; ?>" size="2" maxlength="2" />
			<br />
			<small><?php esc_html_e('(offset from sample)', 'kwik'); ?></small>
		</p>

		<!-- Number of Words Limit -->
		<p>
			<label for="<?php echo $this->get_field_id( 'num_words_limit' ); ?>"><?php esc_html_e('Limit the number of words from video description:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_words_limit' ); ?>" type="text" name="<?php echo $this->get_field_name( 'num_words_limit' ); ?>" value="<?php echo $instance['num_words_limit']; ?>" size="2" maxlength="2" />
		</p>
        
        <!-- Button Text -->
		<p>
			<label for="<?php echo $this->get_field_id( 'btn_text' ); ?>"><?php esc_html_e('Button Text:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'btn_text' ); ?>" type="text" name="<?php echo $this->get_field_name( 'btn_text' ); ?>" value="<?php echo $instance['btn_text']; ?>" size="20"/><br />
<small>leave blank to hide the button</small>
		</p>
        <!-- Button Link -->
		<p>
			<label for="<?php echo $this->get_field_id( 'btn_link' ); ?>"><?php esc_html_e('Button Link:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'btn_link' ); ?>" type="text" name="<?php echo $this->get_field_name( 'btn_link' ); ?>" value="<?php echo $instance['btn_link']; ?>" size="20"/>
		</p>
        <!-- Button Target -->
		<p>
			<label for="<?php echo $this->get_field_id( 'btn_target' ); ?>"><?php esc_html_e('Button Target:', 'kwik'); ?></label>
			<select id="<?php echo $this->get_field_id( 'btn_target' ); ?>" type="select" name="<?php echo $this->get_field_name( 'btn_target' ); ?>" >
            <option value="_self" <?php if ($instance['btn_target'] == '_self'): ?>selected="selected"<?php endif; ?>>Same Window</option>
            <option value="_blank" <?php if ($instance['btn_target'] == '_blank'): ?>selected="selected"<?php endif; ?>>New Window/Tab</option>
            </select>
		</p>

		
                        
<?php
	}
}