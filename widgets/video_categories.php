<?php
/**
 * Widget Name: Video Categories Widget
 * Description: A widget for displaying your video categories
 * Version: 0.1
 *
 */
/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'video_categories_load_widgets' );
/**
 * Register our widget.
 * 'Video_Categories_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function video_categories_load_widgets() {
	register_widget( 'Video_Categories_Widget' );
}
/**
 * Custom Category Widget class.
 * This class handles everything that needs to be handled with the widget:
 * the settings, form, display, and update.  Nice!
 *
 * @since 0.1
 */
class Video_Categories_Widget extends WP_Widget {
	/**
	 * Widget setup.
	 */
	function Video_Categories_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'widget_video_categories', 'description' => esc_html__('Collapsable video categories', 'kwik') );
		/* Widget control settings. */
		$control_ops = array( 'width' => 150, 'height' => 350, 'id_base' => 'video-categories-widget' );
		/* Create the widget. */
		$this->WP_Widget( 'video-categories-widget', esc_html__('Kwik Videos: Categories', 'kwik'), $widget_ops, $control_ops );
	}
	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		/* Before widget (defined by themes). */
		echo $before_widget;
		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
		    echo $before_title . $title . $after_title;
echo '<ul id="video_categories">'; 
$args = array(
  'taxonomy'     => 'video_cats',
  'orderby'      => 'name',
  'show_count'   => 1,
  'hierarchical' => 1,
  'title_li'     => ''
);
wp_list_categories( $args );
echo '</ul>'; 
?>				
		    </div><!-- end terms list -->
<?php 
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
		return $instance;
	}
	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {
		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Video Categories', 'kwik'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title:', 'kwik'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" type="text" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>		
                        
<?php
	}
}
