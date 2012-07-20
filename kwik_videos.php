<?php
/*
Plugin Name: Kwik Videos
Plugin URI: http://kevin-chappell.com/kwik-videos
Description: A plugin for adding an easy to manage video gallery to your website. Features include Amazon S3 integration and membership level based video availability.
Author: Kevin Chappell
Version: 1.002
Author URI: http://kevin-chappell.com
*/


add_action('admin_init', 'kv_options_init_fn' );
add_action('admin_menu', 'kv_add_options_page');


// Register our settings, add sections and fields
function kv_options_init_fn(){
	register_setting('kwik_videos_options', 'kwik_videos_options', 'kv_options_validate' );
	add_settings_section('kv_general_options_section', 'General Options', 'kv_general_options_text', __FILE__);
	add_settings_field('player_width', 'Player Width:', 'kv_player_width', __FILE__, 'kv_general_options_section');
	add_settings_field('player_height', 'Player Height:', 'kv_player_height', __FILE__, 'kv_general_options_section');
	add_settings_field('player_theme', 'Player Theme:', 'kv_player_theme', __FILE__, 'kv_general_options_section');
	add_settings_field('use_amazon_s3', 'Use Amazon S3?:', 'kv_use_amazon_s3', __FILE__, 'kv_general_options_section');
	
	
	add_settings_section('kv_s3_settings_section', 'Amazon S3 Settings', 'kv_s3_section_text', __FILE__);
	add_settings_field('s3_access_key', 'S3 Access Key:', 'kv_s3access_string', __FILE__, 'kv_s3_settings_section');
	add_settings_field('s3_secret_key', 'S3 Secret Key:', 'kv_s3secret_string', __FILE__, 'kv_s3_settings_section');
	add_settings_field('s3_bucket_dropdown', 'S3 Bucket for uploads:', 'kv_s3_bucket_dropdown', __FILE__, 'kv_s3_settings_section');	
	
}



// Add sub page to the Settings Menu
function kv_add_options_page() {	
	add_options_page('Kwik Video Settings', 'Kwik Video Settings', 'manage_options', __FILE__, 'kv_options_page');	
}





function  kv_general_options_text() {
	echo '<p>Set the player options. (<small>future releases will have more options here.</small>)</p>';
}
// TEXTBOX - Name: kwik_videos_options[player_width]
function kv_player_width() {
	$kv_options = get_option('kwik_videos_options');
	echo "<input id='player_width' type='text' size='40' name='kwik_videos_options[player_width]' value='{$kv_options['player_width']}' />";
}
// TEXTBOX - Name: kwik_videos_options[player_height]
function kv_player_height() {
	$kv_options = get_option('kwik_videos_options');
	echo "<input id='player_height' type='text' size='40' name='kwik_videos_options[player_height]' value='{$kv_options['player_height']}' />";
}
// TEXTBOX - Name: kwik_videos_options[player_height]
function kv_use_amazon_s3() {
	$kv_options = get_option('kwik_videos_options');	
	$checked = (isset($kv_options['use_amazon_s3'])) ? 'checked="checked"' : '';	
	echo "<input id='use_amazon_s3' type='checkbox' name='kwik_videos_options[use_amazon_s3]' ".$checked." value='{$kv_options['use_amazon_s3']}' />";
}
// TEXTBOX - Name: kwik_videos_options[player_theme]
function kv_player_theme() {
	$kv_options = get_option('kwik_videos_options');
	$themes = array('light', 'dark');
	echo "<select id='player_theme' name='kwik_videos_options[player_theme]'>";			
	foreach ($themes as $theme)
		{
	$selected = ($kv_options['player_theme'] == $theme) ? 'selected="selected"' : '';
    echo "<option value='$theme' $selected>$theme</option>";
		} 
	echo "</select>";
}





// Section HTML, displayed before the first option
function  kv_s3_section_text() {
	echo '<p>To use the Amazon S3 upload option enter your Amazon S3 Access Key and Secret Key. Then choose a bucket where files will be uploaded.</p>';
}
// TEXTBOX - Name: kwik_videos_options[s3access_string]
function kv_s3access_string() {
	$options = get_option('kwik_videos_options');
	echo "<input id='s3_access_key' type='text' size='40' name='kwik_videos_options[s3access_string]' value='{$options['s3access_string']}' />";
}

// PASSWORD-TEXTBOX - Name: kwik_videos_options[s3secret_string]
function kv_s3secret_string() {
	$options = get_option('kwik_videos_options');
	echo "<input id='s3_secret_key' name='kwik_videos_options[s3secret_string]' size='40' type='password' value='{$options['s3secret_string']}' />&nbsp;<a href='http://aws-portal.amazon.com/gp/aws/developer/account/index.html/?ie=UTF8&action=access-key'>Login to AWS to retrieve your secret key</a>";
}


function  kv_s3_bucket_dropdown() {
	
if (!class_exists('S3'))require_once('lib/S3.php');
	
	$kv_options = get_option('kwik_videos_options');
	$s3key = $kv_options["s3access_string"]; 
	$s3secret = $kv_options["s3secret_string"]; 
	$s3 = new S3($s3key,$s3secret);
	$admin_buckets = $s3->listBuckets();

	
	if (empty($admin_buckets)){
		echo "Please check your AWS Access Keys";		
	} else {	
	
	 // Standard list:
	echo "<select id='s3_bucket_dropdown' name='kwik_videos_options[s3_bucket_dropdown]'>"; 
	foreach ($admin_buckets as $admin_bucket)
		{
	$selected = ($kv_options['s3_bucket_dropdown']==$admin_bucket) ? 'selected="selected"' : '';
    echo "<option value='$admin_bucket' $selected>$admin_bucket</option>";
		} 
		echo "</select>";
	}
		
}


function kv_options_page() {
?>
	<div class="wrap">
	
     <div class="icon32" id="icon-options-general"><br></div>

     <?php echo "<h2>" . __( 'Kwik Video Settings' ) . "</h2>";?>
       
    <form action="options.php" method="post">
		<?php settings_fields('kwik_videos_options'); ?>   
		<?php kv_settings_sections(__FILE__); ?>      
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
     
     <br />
    
      <p class="alignleft" style="width:310px; text-align:center; padding:10px; background-color:#efefef;">If you found this plugin useful for your project please consider making a donation. Even a dollar would be alright with me.</p><br /> <p class="alignright">
      
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="F5UYHK7JGFZFC">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
</p>
	
</div>
<?php
}

// Validate user data for some/all of your input fields
function kv_options_validate($input) {
	// Check our textbox option field contains no HTML tags - if so strip them out
	$input['text_string'] =  wp_filter_nohtml_kses($input['text_string']);	
	return $input; // return validated input
}




add_action( 'init', 'kwik_videos_setup' );

if ( ! function_exists( 'kwik_videos_setup' ) ):
function kwik_videos_setup() {
	
	register_post_type( 'videos',
		array(
			'labels' => array(
				'name' => __( 'Videos' ),
				'all_items' => __( 'Video List' ),				
				'singular_name' => __( 'Video' ),
				'add_new' => __( 'Add Video' ),
				'edit_item' => __( 'Edit Video' ),
				'menu_name' => __( 'Videos' )
			),
			'menu_icon' => __( WP_PLUGIN_URL . '/kwik-videos/lib/video.png' ),
			'menu_position' => 7,			
			
		'supports' => array('title','editor','thumbnail', 'comments'),
		'public' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'rewrite' => array("slug" => "videos"),
		'taxonomies' => array('video_cats', 'post_tag'),
		'register_meta_box_cb' => 'add_videos_metaboxes'	
		)
	);
	add_theme_support( 'post-thumbnails' ); 	
	
	
}  // kwik_videos_setup
endif;



foreach (glob(WP_PLUGIN_DIR."/kwik-videos/widgets/*.php") as $inc_filename){
    include $inc_filename;
}


function add_kv_player($content)
{	
    global $post;
	$kv_options = get_option('kwik_videos_options');
	// Set the cover image/poster
    
    if (is_singular() && is_main_query() && get_post_type() == 'videos') {	
		$cover = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large' );
		$cover_url = $cover['0'];	
		// get video source	
        $video_sources = get_post_meta($post->ID, '_source', true);
		$videos = explode(",", $video_sources);

		$kwik_player  .= '<div class="video-js-box">
    <video id="kv_video" class="video-js" width="'.$kv_options['player_width'].'" height="'.$kv_options['player_height'].'" style="height:'.$kv_options['player_height'].'; width:'.$kv_options['player_width'].'" controls="controls" autoplay="autoplay" preload="auto" poster="'.$cover_url.'">';
	
	
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
            <object width="'.$kv_options['player_width'].'" height="'.$kv_options['player_height'].'" id="live_video_object" type="application/x-shockwave-flash" data="' . get_bloginfo('url') . '/wp-content/plugins/kwik-videos/lib/universalPlayer.swf" name="live_video_object" >
                <param value="true" name="allowfullscreen">
                <param name="wmode" value="opaque" />
                <param value="always" name="allowscriptaccess">
                <param value="high" name="quality">
                <param name="bgcolor" value="#ffffff"/>
                <param value="player.style.global='.$kv_options['player_theme'].'&amp;player.start.' . $video_resource . '&amp;player.controls.hd=false&amp;player.start.paused=false&amp;player.start.cover='.$cover_url.' name="flashvars">
            </object></video></div>';
			
			
        $content = $kwik_player . $content;
    }
    return $content;
}

add_filter('the_content', 'add_kv_player', 4);






    add_action('init', 'add_video_tags');     
    function add_video_tags() {
    register_taxonomy_for_object_type('post_tag', 'videos');
    }


add_filter('gettext', 'kv_featured_image_text', 10, 4);
function kv_featured_image_text($translation, $text, $domain) {
	global $post;
	if ($post->post_type == 'videos') {
	        $translations = get_translations_for_domain($domain);
	        if ( $text == 'Featured Image')
	            return $translations->translate( 'Video Cover Image' );
		if ( $text == 'Set featured image')
	            return $translations->translate( 'Change cover image' );
	}
	return $translation;
}


add_action( 'restrict_manage_posts', 'kv_taxonomy_filter' );
function kv_taxonomy_filter() {
    global $typenow;
    $post_types = get_post_types( array( '_builtin' => false ) );

    if ( in_array( $typenow, $post_types ) ) {
    	$filters = get_object_taxonomies( $typenow );

        foreach ( $filters as $tax_slug ) {
            $tax_obj = get_taxonomy( $tax_slug );
            wp_dropdown_categories( array(
                'show_option_all' => __('Show All '.$tax_obj->label ),
                'taxonomy' 	  => $tax_slug,
                'name' 		  => $tax_obj->name,
                'orderby' 	  => 'name',
                'selected' 	  => $_GET[$tax_slug],
                'hierarchical' 	  => $tax_obj->hierarchical,
                'show_count' 	  => false,
                'hide_empty' 	  => true
            ) );
        }
    }
}




add_filter( 'parse_query', 'kv_taxonomy_filter_request' );
function kv_taxonomy_filter_request( $query ) {
  global $pagenow, $typenow;

  if ( 'edit.php' == $pagenow ) {
    $filters = get_object_taxonomies( $typenow );
    foreach ( $filters as $tax_slug ) {
      $var = &$query->query_vars[$tax_slug];
      if ( isset( $var ) ) {
        $term = get_term_by( 'id', $var, $tax_slug );
        $var = $term->slug;
      }
    }
  }
}


add_filter( "manage_videos_posts_columns", "kv_change_columns" );
function kv_change_columns( $cols ) {
  $cols = array(
    'cb'		=> '<input type="checkbox" />',
	'cover'		=> __( 'Cover', 'kwik' ),
    'title'		=> __( 'Title', 'kwik' ),
    'tags'		=> __( 'Tags', 'kwik' ),
    'video_cats'=> __( 'Categories', 'kwik' ),
	'date'		=> __( 'Date', 'kwik' )
  );
  return $cols;
}


add_action( "manage_posts_custom_column", "kv_custom_videos_columns", 10, 2 );
function kv_custom_videos_columns( $column, $post_id ) {
  switch ( $column ) {
    case "video_cats":
      $video_cats = get_the_terms( $post_id, 'video_cats');
	  if($video_cats){
		$results = '';
		foreach ($video_cats as $video_cat) {
      $results .= '<a href="edit.php?post_type=videos&amp;video_cats=' . $video_cat->term_id . '">' . $video_cat->name. '</a>, ';
		}		
		$results = rtrim($results, ', ');
		echo $results;
	  }
      break;
    case "cover":
      if(has_post_thumbnail()) {
	the_post_thumbnail(array(75, 75));
} else {

}
      break;
  }
}



add_filter( "manage_edit-videos_sortable_columns", "kv_sortable_videos_columns" );
function kv_sortable_videos_columns() {
  return array(
    'title'	=> 'title',
    'date'	=> 'date'
  );
}



function kv_add_videos_to_feed( $qv ) {
  if ( isset($qv['feed']) && !isset($qv['post_type']) )
    $qv['post_type'] = array('post', 'videos');
  return $qv;
}
add_filter( 'request', 'kv_add_videos_to_feed' );




function kv_videos_in_right_now() {
	
	$post_type = 'videos';
	
	if (!post_type_exists($post_type)) {
             return;
    }
	$num_posts = wp_count_posts( $post_type );
	echo '';
	$num = number_format_i18n( $num_posts->publish );
	$text = _n( 'Video', 'Videos', $num_posts->publish );
	if ( current_user_can( 'edit_posts' ) ) {
            $num = '<a href="edit.php?post_type='.$post_type.'">'.$num.'</a>';
            $text = '<a href="edit.php?post_type='.$post_type.'">'.$text.'</a>';
    }
	echo '<td class="first b b-videos">' . $num . '</td>';
	echo '<td class="t videos">' . $text . '</td>';



if ($num_posts->pending > 0) {
            $num = number_format_i18n( $num_posts->pending );
            $text = _n( 'Video Pending', 'Videos Pending', intval($num_posts->pending) );
            if ( current_user_can( 'edit_posts' ) ) {
                $num = '<a href="edit.php?post_status=pending&post_type='.$post_type.'">'.$num.'</a>';
                $text = '<a href="edit.php?post_status=pending&post_type='.$post_type.'">'.$text.'</a>';
            }
            echo '<td class="first b b-videos">' . $num . '</td>';
            echo '<td class="t videos">' . $text . '</td>';

        }
		
         echo '</tr>';


}
add_action('right_now_content_table_end','kv_videos_in_right_now');





add_action( 'init', 'kv_create_gallery_taxonomies', 0 );


function kv_create_gallery_taxonomies() {
	
	$videolabels = array(
		'name' => _x( 'Video Categories', 'taxonomy general name' ),
		'menu_name' => __( 'Categories' ),
		'singular_name' => _x( 'Category', 'taxonomy singular name' ),
		'search_items' =>  __( 'Search Categories' ),
		'all_items' => __( 'All Categories' ),
		'parent_item' => __( 'Parent Category' ),
		'parent_item_colon' => __( 'Parent Category:' ),
		'edit_item' => __( 'Edit Category' ),
		'update_item' => __( 'Update Category' ),
		'add_new_item' => __( 'Add New Category' ),
		'new_item_name' => __( 'New Category Name' ),
	); 

	
	register_taxonomy( 'video_cats', array( 'videos' ), array(
		'hierarchical' => true,
		'labels' => $videolabels,
		'show_ui' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'video-categories' ),
		'with_front' => true
	));
}



add_filter( 'post_class', 'kv_post_class', 10, 3 ); 
function kv_post_class( $classes, $class, $ID ) {
	$video_taxonomy = 'video_cats';
    $terms = get_the_terms( (int) $ID, $video_taxonomy );
    if( !empty( $terms ) ) {
        foreach( (array) $terms as $order => $term ) {
            if( !in_array( $term->slug, $classes ) ) {
                $classes[] = $term->slug;
            }
        }
    }

    return $classes;
} 





// Add the Videos meta box
function add_videos_metaboxes() {
    add_meta_box('kv_video_details', 'Video Details', 'kv_video_details', 'videos', 'side', 'default');	
}


// The Video details meta box
function kv_video_details() {
    global $post;
 
    // Noncename for security check on data origin
    echo '<input type="hidden" name="videometa_noncename" id="videometa_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
 
    // Get the current data
    $credit = get_post_meta($post->ID, '_credit', true);
	$credit_link = get_post_meta($post->ID, '_credit_link', true);
	$runtime = get_post_meta($post->ID, '_runtime', true);
	$video_source = get_post_meta($post->ID, '_source', true);
	
	
	
// all these echos will be cleaned up, promise!
echo '<span id="checkvars" style="display:none">'.WP_PLUGIN_URL . '/kwik-videos/lib/upload_s3.php</span>';

		echo '
		<div id="video_upload_wrap">
		<p>Upload the Video:</p>
		    <input type="file" name="images" multiple="" id="images" />
			<div id="response"></div>
		</div>
		';
				

		echo '<p>Video Source(s):</p>';
		echo '<input type="text" name="_source[]" value="' . $video_source  . '" id="video_source" class="widefat" /><br />';

        echo '<p>Video Credit:</p>';
        echo '<input type="text" name="_credit" value="' . $credit  . '" class="widefat" />';
		
        echo '<p>Video Credit Link:</p>';
        echo '<input type="text" name="_credit_link" value="' . $credit_link  . '" class="widefat" />';
		
		echo '<p>Runtime:</p>';
        echo '<input type="text" name="_runtime" value="' . $runtime  . '" id="_runtime" class="widefat" />';
 
}


// Save the Metabox Data 
function kv_save_video_meta($post_id, $post) {
 
    // make sure there is no conflict with other post save function and verify the noncename
    if ( !wp_verify_nonce( $_POST['videometa_noncename'], plugin_basename(__FILE__) )) {
    return $post->ID;
    }
 
    // Is the user allowed to edit the post or page?
    if ( !current_user_can( 'edit_post', $post->ID ))
        return $post->ID;
	
	$videos_meta = array( 
	'_source' => $_POST['_source'],
	'_credit' => $_POST['_credit'],
	'_credit_link' => $_POST['_credit_link'],
	'_runtime' => $_POST['_runtime']
	);

 
    // Add values of $videos_meta as custom fields 
    foreach ($videos_meta as $key => $value) {
        if( $post->post_type == 'revision' ) return;
        $value = implode(',', (array)$value); // CSV the value if it is an array()
        if(get_post_meta($post->ID, $key, FALSE)) {
            update_post_meta($post->ID, $key, $value);
        } else {
            add_post_meta($post->ID, $key, $value);
        }
        if(!$value) delete_post_meta($post->ID, $key);
    }
 
} 
add_action('save_post', 'kv_save_video_meta', 1, 2);



// Auto-set the featured image
function videos_autoset_featured() {
          global $post;
          $already_has_thumb = has_post_thumbnail($post->ID);
              if (!$already_has_thumb)  {
              $attached_image = get_children( "post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1" );
                          if ($attached_image) {
                                foreach ($attached_image as $attachment_id => $attachment) {
                                set_post_thumbnail($post->ID, $attachment_id);
                                }
                           }
                        }
      }  //end function
add_action('the_post', 'videos_autoset_featured');
add_action('save_post', 'videos_autoset_featured');
add_action('draft_to_publish', 'videos_autoset_featured');
add_action('new_to_publish', 'videos_autoset_featured');
add_action('pending_to_publish', 'videos_autoset_featured');
add_action('future_to_publish', 'videos_autoset_featured');






function kv_neat_trim($str, $n, $delim='&hellip;') {
   $len = strlen($str);
   if ($len > $n) {
       preg_match('/(.{' . $n . '}.*?)\b/', $str, $matches);
       return rtrim($matches[1]) . $delim;
   }
   else {
       return $str;
   }
}


add_filter("attachment_fields_to_edit", "kv_edit_attachment_fields", null, 2);
function kv_edit_attachment_fields($form_fields, $post) {
$form_fields["photo_credit"] = array(
"label" => __("Photo Credit"),
"input" => "text", // default
"value" => get_post_meta($post->ID, "photo_credit", true),
);
return $form_fields;
}


add_filter("attachment_fields_to_save", "kv_save_attachment_fields", null , 2);
function kv_save_attachment_fields($post, $attachment) {

	if( isset($attachment['photo_credit']) ){
	  update_post_meta($post['ID'], 'photo_credit', $attachment['photo_credit']);
	}
	return $post;
}



add_action('wp_print_styles', 'kv_add_style');
function kv_add_style() {
	
	$kv_css = WP_PLUGIN_URL . '/kwik-videos/lib/kwik_videos.css';
	wp_register_style('kv_css', $kv_css);
	wp_enqueue_style('kv_css');

}



add_action('wp_enqueue_scripts', 'kv_add_script');
function kv_add_script()
{      
    wp_enqueue_script('kwik_gallery', plugins_url('/kwik-videos/lib/kwik_videos.js'), array('jquery'));      
}



add_action( 'admin_enqueue_scripts', 'kv_add_admin_script' ,10,1);
function kv_add_admin_script($hook) {
	 $screen = get_current_screen();
    if ( 'settings_page_kwik_videos/kwik-videos' == $hook || 'post.php' == $hook && 'videos' == $screen->post_type || 'post-new.php' == $hook && 'videos' == $screen->post_type ){
       wp_enqueue_script( 'kwik_video_admin_script', plugins_url('/lib/kwik_videos_admin.js', __FILE__) );
	   wp_register_style( 'kwik_video_admin_css', plugins_url('/lib/kwik_videos_admin.css', __FILE__)  );
       wp_enqueue_style( 'kwik_video_admin_css' );
    }	elseif('edit.php' == $hook && 'videos' == $screen->post_type){
		wp_register_style( 'kwik_video_admin_css', plugins_url('/lib/kwik_videos_admin.css', __FILE__)  );
		wp_enqueue_style( 'kwik_video_admin_css' );	
		}
}

	
function kv_settings_sections($page) {
	        global $wp_settings_sections, $wp_settings_fields;
	
	        if ( !isset($wp_settings_sections) || !isset($wp_settings_sections[$page]) )
	                return;
	
	        foreach ( (array) $wp_settings_sections[$page] as $section ) {
	                if ( $section['title'] )
							if(isset($wp_settings_fields[$page][$section['id']])) echo "<div id=".$section['id'].">\n";
	                        echo "<h3>{$section['title']}</h3>\n";
	                call_user_func($section['callback'], $section);
	                if ( !isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]) )
	                        continue;
	                echo '<table class="form-table">';
	                do_settings_fields($page, $section['id']);
	                echo '</table>';
					if(isset($wp_settings_fields[$page][$section['id']])) echo "</div>\n";
	        }
}


function kv_runTime($file){
	if (!class_exists('getID3'))require_once('lib/getid3/getid3.php');
	$getID3 = new getID3;
	$video_info = $getID3->analyze($file);	
	//return $video_info['playtime_string'];
	return $video_info['playtime_string'];	
	}
	
function kv_mimeType($filename) {
   $fileext = substr(strrchr($filename, '.'), 1);
   if (empty($fileext)) return (false);
   $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
   $lines = file(WP_PLUGIN_DIR.'/kwik-videos/lib/mime_types.txt');
   foreach($lines as $line) {
      if (substr($line, 0, 1) == '#') continue; // skip comments
      $line = rtrim($line) . " ";
      if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
      return ($matches[1]);
   }
   return (false); // no match at all 
	}