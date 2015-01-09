<?php
/*
Plugin Name: Kwik Videos
Plugin URI: http://kevin-chappell.com/kwik-videos
Description: A plugin for adding an easy to manage video gallery to your website. Features include Amazon S3 integration and membership level based video availability.
Author: Kevin Chappell
Version: 1.1
Author URI: http://kevin-chappell.com
*/





add_action('wp_print_styles', 'kv_add_style');
function kv_add_style()
{
	$kv_options = get_option('kwik_videos_options');
    $kv_css = WP_PLUGIN_URL . '/kwik-videos/lib/kwik_videos.css';
    wp_register_style('kv_css', $kv_css);
    wp_enqueue_style('kv_css');
	
	if($kv_options['player'] == 'video-js') {		
		$videojs = WP_PLUGIN_URL . '/kwik-videos/lib/players/video-js/video-js.min.css';		
		//$videojs_style = WP_PLUGIN_URL . '/kwik-videos/lib/players/video-js/video-js.min.css';
		wp_register_style('videojs', $videojs);
		//wp_register_style('videojs_style', $videojs_style);
		wp_enqueue_style('videojs');
		//wp_enqueue_style('videojs_style');
		}
}
add_action('wp_enqueue_scripts', 'kv_add_script');
function kv_add_script(){
	global $wp_scripts;	
    $kv_options = get_option('kwik_videos_options');
    wp_enqueue_script('cookie', plugins_url('/kwik-videos/lib/jquery.cookie.js'), array(
        'jquery'
    ));
    wp_enqueue_script('treeview', plugins_url('/kwik-videos/lib/jquery.treeview.js'), array(
        'cookie',
        'jquery'
    ));
	$browser = Browser::detect();
	if ($browser['name'] == 'msie' && $browser['version'] < 10)
	wp_enqueue_script( 'kv_ie', 'https://raw.github.com/balupton/history.js/master/scripts/compressed/history.adapter.jquery.js', array(), '1.0' );
    wp_enqueue_script('kwik_video', plugins_url('/kwik-videos/lib/kwik_videos.js'), array(
        'cookie',
        'treeview',
        'jquery'
    ));
	if($kv_options['player'] == 'jwplayer5') wp_enqueue_script('jwplayer', plugins_url('/kwik-videos/lib/players/jwplayer5/jwplayer.js'));
	if($kv_options['player'] == 'jwplayer6') wp_enqueue_script('jwplayer', plugins_url('/kwik-videos/lib/players/jwplayer6/jwplayer.js'));
	if($kv_options['player'] == 'video-js') wp_enqueue_script('video-js', plugins_url('/kwik-videos/lib/players/video-js/video.min.js'));
	

}

function jwplayer_key() {
	$kv_options = get_option('kwik_videos_options');
  if ( $kv_options['player'] == 'jwplayer' && wp_script_is( 'jwplayer', 'done' ) ) echo '<script type="text/javascript">jwplayer.key="'.$kv_options['jwp_key'].'";</script>';

}
add_action( 'wp_head', 'jwplayer_key' );


add_action('admin_enqueue_scripts', 'kv_add_admin_script', 10, 1);
function kv_add_admin_script($hook)
{
    $screen = get_current_screen();
    if ('settings_page_kwik-videos/kwik_videos' == $hook || 'post.php' == $hook && 'videos' == $screen->post_type || 'post-new.php' == $hook && 'videos' == $screen->post_type) {

        wp_enqueue_script('jcycle', "http://malsup.github.io/jquery.cycle.all.js", array('jquery'), '2011-06-10', true);
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-progressbar');

        wp_register_style('kwik_video_admin_css', plugins_url('/lib/kwik_videos_admin.css', __FILE__));
        wp_enqueue_style('kwik_video_admin_css');
		wp_enqueue_script('kwik_video_admin_script', plugins_url('/lib/kwik_videos_admin.js', __FILE__), array('jcycle','jquery-ui-core', 'jquery'), true);
    } elseif ('edit.php' == $hook && 'videos' == $screen->post_type) {
        wp_register_style('kwik_video_admin_css', plugins_url('/lib/kwik_videos_admin.css', __FILE__));
        wp_enqueue_style('kwik_video_admin_css');
    }
}


function kv_sidebar_init() {

	register_sidebar( array(
		'name' => __( 'Kwik Videos | Category View', 'kwik' ),
		'id' => 'kv_cat_sb',
		'description' => __( 'A sidebar while browsing categories.', 'kwik' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Kwik Videos | Archive View', 'kwik' ),
		'id' => 'kv_archive_sb',
		'description' => __( 'A sidebar while browsing video archives.', 'kwik' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => "</aside>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

}
add_action( 'widgets_init', 'kv_sidebar_init' );

















add_action('admin_init', 'kv_options_init_fn');
add_action('admin_menu', 'kv_add_options_page');
// Register our settings, add sections and fields
function kv_options_init_fn()
{
    register_setting('kwik_videos_options', 'kwik_videos_options', 'kv_options_validate');
    add_settings_section('kv_general_options_section', 'General Options', 'kv_general_options_text', __FILE__);
    add_settings_field('default_view', 'Default View Type:', 'kv_default_view', __FILE__, 'kv_general_options_section');
    add_settings_field('num_cols', 'Number of Grid Columns:', 'kv_num_cols', __FILE__, 'kv_general_options_section');
    add_settings_field('videos_per_page', 'Videos Per Page:', 'kv_videos_per_page', __FILE__, 'kv_general_options_section');
	
	add_settings_field('thumb_size', 'Thumbnail Size:', 'kv_thumb_size', __FILE__, 'kv_general_options_section');
    
    add_settings_field('enable_video_types', 'Enable Video Types?:', 'kv_enable_video_types', __FILE__, 'kv_general_options_section');
    add_settings_field('video_types', 'Video Types:', 'kv_video_types', __FILE__, 'kv_general_options_section');
    
    
    
    add_settings_section('kv_video_options_section', 'Video Options', 'kv_video_options_text', __FILE__);
	add_settings_field('player', 'Player:', 'kv_player', __FILE__, 'kv_video_options_section');
	add_settings_field('player_theme', 'Player Theme:', 'kv_player_theme', __FILE__, 'kv_video_options_section');
	add_settings_field('jwp_skin', 'JW Player Skin:', 'kv_jwp_skin', __FILE__, 'kv_video_options_section');
	add_settings_field('jwp_key', 'JW Player License Number:', 'kv_jwp_key', __FILE__, 'kv_video_options_section');
    add_settings_field('player_width', 'Player Width:', 'kv_player_width', __FILE__, 'kv_video_options_section');
    add_settings_field('player_height', 'Player Height:', 'kv_player_height', __FILE__, 'kv_video_options_section');
    
    
    
    add_settings_section('kv_s3_settings_section', 'Amazon S3 Settings', 'kv_s3_section_text', __FILE__);
    add_settings_field('use_amazon_s3', 'Use Amazon S3?:', 'kv_use_amazon_s3', __FILE__, 'kv_s3_settings_section');
    add_settings_field('s3_access_key', 'S3 Access Key:', 'kv_s3access_string', __FILE__, 'kv_s3_settings_section');
    add_settings_field('s3_secret_key', 'S3 Secret Key:', 'kv_s3secret_string', __FILE__, 'kv_s3_settings_section');
    add_settings_field('s3_bucket_dropdown', 'S3 Bucket for uploads:', 'kv_s3_bucket_dropdown', __FILE__, 'kv_s3_settings_section');
    
}
// Add sub page to the Settings Menu
function kv_add_options_page()
{
    add_options_page('Kwik Video Settings', 'Kwik Video Settings', 'manage_options', __FILE__, 'kv_options_page');
}
function kv_general_options_text()
{
    echo '<p>Set default plugin options.</p>';
}
function kv_video_options_text()
{
    echo '<p>Set the player options.</p>';
}
// TEXTBOX - Name: kwik_videos_options[player_width]
function kv_player_width()
{
    $kv_options = get_option('kwik_videos_options');
    echo "<input id='player_width' type='text' size='40' name='kwik_videos_options[player_width]' value='{$kv_options['player_width']}' /><em>ex. 100% or 100px</em>";
}
// TEXTBOX - Name: kwik_videos_options[player_height]
function kv_player_height()
{
    $kv_options = get_option('kwik_videos_options');
    echo "<input id='player_height' type='text' size='40' name='kwik_videos_options[player_height]' value='{$kv_options['player_height']}' /> <em>ex. 100% or 100px</em>";
}


// CHECKBOX - Name: kwik_videos_options[use_amazon_s3]
function kv_use_amazon_s3()
{
    $kv_options = get_option('kwik_videos_options');
    $checked    = (isset($kv_options['use_amazon_s3'])) ? 'checked="checked"' : '';
    echo "<input id='use_amazon_s3' type='checkbox' name='kwik_videos_options[use_amazon_s3]' " . $checked . " value='{$kv_options['use_amazon_s3']}' />";
}





// SELECTBOX - Name: kwik_videos_options[default_view]
function kv_default_view()
{
    $kv_options = get_option('kwik_videos_options');
    $views      = array(
        'grid',
        'list'
    );
    echo "<select id='default_view' name='kwik_videos_options[default_view]'>";
    foreach ($views as $view) {
        $selected = ($kv_options['default_view'] == $view) ? 'selected="selected"' : '';
        echo "<option value='$view' $selected>$view &nbsp;</option>";
    }
    echo "</select>";
}

// SELECTBOX - Name: kwik_videos_options[thumbsize]
function kv_thumb_size()
{
    $kv_options = get_option('kwik_videos_options');
	$kv_thumb_size = $kv_options['thumb_size'];
    echo 'Width: <input type="text" name="kwik_videos_options[thumb_size][]" value="'.$kv_thumb_size[0].'" id="kv_thumb_width">
	&nbsp; Height: <input type="text" name="kwik_videos_options[thumb_size][]" value="'.$kv_thumb_size[1].'" id="kv_thumb_height">';
}



// SELECTBOX - Name: kwik_videos_options[num_cols]
function kv_num_cols()
{
    $kv_options = get_option('kwik_videos_options');
    $cols       = array(
        '2',
        '3',
        '4'
    );
    echo "<select id='num_cols' name='kwik_videos_options[num_cols]'>";
    foreach ($cols as $col) {
        $selected = ($kv_options['num_cols'] == $col) ? 'selected="selected"' : '';
        echo "<option value='$col' $selected>$col &nbsp;</option>";
    }
    echo "</select>";
}

// TEXTBOX - Name: kwik_videos_options[videos_per_page]
function kv_videos_per_page()
{
    $kv_options = get_option('kwik_videos_options');
    echo "<input id='videos_per_page' type='text' size='10' name='kwik_videos_options[videos_per_page]' value='{$kv_options['videos_per_page']}' /> <em>How many videos should be shown on each page?</em>";
}


// CHECKBOX - Name: kwik_videos_options[enable_video_types]
function kv_enable_video_types()
{
    $kv_options = get_option('kwik_videos_options');
    $checked    = (isset($kv_options['enable_video_types'])) ? 'checked="checked"' : '';
    
    echo "<input id='enable_video_types' type='checkbox' name='kwik_videos_options[enable_video_types]' " . $checked . " value='{$kv_options['enable_video_types']}' />*** Note this feature was dumped in favor of a custom taxonomy and will soon be removed.";
}

// SELECT - Name: kwik_videos_options[video_types]
function kv_video_types()
{
    $kv_options  = get_option('kwik_videos_options');
    $video_types = $kv_options['video_types'];
    //var_dump($video_types);	
    
    /*	$videos_types = array(
    'X Small' => 'X Small',
    'Small' => 'Small',
    'Medium' => 'Medium',
    'Large' => 'Large',
    'X Large' => 'X Large',
    'XX Large' => 'XX Large',
    'XXX Large' => 'XXX Large',
    'S/M' => 'S/M',
    'M/L' => 'M/L',
    'L/XL' => 'L/XL',
    'XXL' => 'XXL'
    );*/
    
    $size_count     = count($video_types);
    $kv_video_types = '<div id="video_types_wrap">';
    $kv_video_types .= '<ul id="video_types">';
    
    //var_dump($videos_types);							
    if (is_array($video_types)) {
        $i = 1;
        foreach ($video_types as $video_type):
            $kv_video_types .= '
	<li class="video_type">
	<label>' . $i . '</label>
	<input type="text" name="kwik_videos_options[video_types][]" value="' . $video_type . '" />
	<span class="move_box"></span>
	</li>';
            $i++;
        endforeach;
    } else {
        $kv_video_types .= '
	<li class="video_type">
	<label>1</label>
	<input type="text" name="kwik_videos_options[video_types][]" value="' . $video_types . '" />
	<span class="move_box"></span>
	</li>';
    }
    $kv_video_types .= '</ul>';
    $kv_video_types .= '<input type="button" value="+" title="add type" id="add_type" />';
    if ($size_count > 1)
        $kv_video_types .= '<input type="button" value="-" title="remove types" id="remove_type" />';
    $kv_video_types .= '</div>';
    
    echo $kv_video_types;
    
}



// SELECT BOX - Name: kwik_videos_options[jwp_skin]
function kv_jwp_skin(){
    $kv_options = get_option('kwik_videos_options');
    $skins     = array(
        'Default' => 'default',
        'FS40' => 'fs40',
		'Lulu' => 'lulu',
		'Modieus' => 'modieus',
		'Slim' => 'slim'
    );
    echo "<select id='jwp_skin' name='kwik_videos_options[jwp_skin]'>";
    foreach ($skins  as $k => $v ) {
        $selected = ($kv_options['jwp_skin'] == $v) ? 'selected="selected"' : '';
        echo "<option value='$v' $selected>$k</option>";
    }
    echo "</select>";
}



// TEXTBOX - Name: kwik_videos_options[jwp_key]
function kv_jwp_key()
{
    $kv_options = get_option('kwik_videos_options');
    echo "<input id='jwp_key' type='text' size='54' name='kwik_videos_options[jwp_key]' value='{$kv_options['jwp_key']}' /> <em>If you have a JW Player license key, enter it here</em>";
}



// SELECT BOX - Name: kwik_videos_options[player]
function kv_player(){
    $kv_options = get_option('kwik_videos_options');
    $players     = array(
        'Default' => 'default',
		'JW Player 5' => 'jwplayer5',
        'JW Player 6' => 'jwplayer6',
		'Video JS' => 'video-js'
    );
    echo "<select id='player' name='kwik_videos_options[player]'>";
    foreach ($players  as $k => $v ) {
        $selected = ($kv_options['player'] == $v) ? 'selected="selected"' : '';
        echo "<option value='$v' $selected>$k</option>";
    }
    echo "</select>";
}

// SELECT BOX - Name: kwik_videos_options[player_theme]
function kv_player_theme(){
    $kv_options = get_option('kwik_videos_options');
    $themes     = array(
        'light',
        'dark'
    );
    echo "<select id='player_theme' name='kwik_videos_options[player_theme]'>";
    foreach ($themes as $theme) {
        $selected = ($kv_options['player_theme'] == $theme) ? 'selected="selected"' : '';
        echo "<option value='$theme' $selected>$theme</option>";
    }
    echo "</select>";
}
// Section HTML, displayed before the first option
function kv_s3_section_text()
{
    echo '<p>To use the Amazon S3 upload option enter your Amazon S3 Access Key and Secret Key. Then choose a bucket where files will be uploaded.</p>';
}
// TEXTBOX - Name: kwik_videos_options[s3access_string]
function kv_s3access_string()
{
    $options = get_option('kwik_videos_options');
    echo "<input id='s3_access_key' type='text' size='40' name='kwik_videos_options[s3access_string]' value='{$options['s3access_string']}' />";
}
// PASSWORD-TEXTBOX - Name: kwik_videos_options[s3secret_string]
function kv_s3secret_string()
{
    $options = get_option('kwik_videos_options');
    echo "<input id='s3_secret_key' name='kwik_videos_options[s3secret_string]' size='40' type='password' value='{$options['s3secret_string']}' />&nbsp;<a href='http://aws-portal.amazon.com/gp/aws/developer/account/index.html/?ie=UTF8&action=access-key'>Login to AWS to retrieve your secret key</a>";
}
function kv_s3_bucket_dropdown()
{
    if (!class_exists('S3'))
        require_once('lib/S3.php');
    
    $kv_options    = get_option('kwik_videos_options');
    $s3key         = $kv_options["s3access_string"];
    $s3secret      = $kv_options["s3secret_string"];
    $s3            = new S3($s3key, $s3secret);
    $admin_buckets = $s3->listBuckets();
    
    if (empty($admin_buckets)) {
        echo "Please check your AWS Access Keys";
    } else {
        // Standard list:
        echo "<select id='s3_bucket_dropdown' name='kwik_videos_options[s3_bucket_dropdown]'>";
        foreach ($admin_buckets as $admin_bucket) {
            $selected = ($kv_options['s3_bucket_dropdown'] == $admin_bucket) ? 'selected="selected"' : '';
            echo "<option value='$admin_bucket' $selected>$admin_bucket</option>";
        }
        echo "</select>";
    }
    
}
function kv_options_page()
{
?>
	<div class="wrap">
	
     <div class="icon32" id="icon-options-general"><br></div>
     <?php
    echo "<h2>" . __('Kwik Video Settings') . "</h2>";
?>
       
    <form action="options.php" method="post">
		<?php
    settings_fields('kwik_videos_options');
?>   
		<?php
    kv_settings_sections(__FILE__);
?>      
		<p style="display:none" class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php
    esc_attr_e('Save Changes');
?>" />
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
function kv_options_validate($input)
{
    // Check our textbox option field contains no HTML tags - if so strip them out
    $input['text_string'] = wp_filter_nohtml_kses($input['text_string']);
    return $input; // return validated input
}
add_action('init', 'kwik_videos_setup');
if (!function_exists('kwik_videos_setup')):
    function kwik_videos_setup()
    {
		
		$kv_options =  get_option('kwik_videos_options');
		$kv_thumb_size = $kv_options['thumb_size'];
		
        register_post_type('videos', array(
            'labels' => array(
                'name' => __('Videos'),
                'all_items' => __('Video List'),
                'singular_name' => __('Video'),
                'add_new' => __('Add Video'),
                'edit_item' => __('Edit Video'),
                'menu_name' => __('Videos')
            ),
            'menu_icon' => __(WP_PLUGIN_URL . '/kwik-videos/lib/images/video.png'),
            'menu_position' => 7,
            
            'supports' => array(
                'title',
                'editor',
                'thumbnail',
                'comments'
            ),
            'public' => true,
			'publicly_queryable' => true,
            'has_archive' => true,
            'rewrite' => array(
                "slug" => "videos"
            ),
            'taxonomies' => array(
                'video_cats',
				'video_types',
                'post_tag'
            ),
            'register_meta_box_cb' => 'add_videos_metaboxes'
        ));
		
        add_theme_support('post-thumbnails');
		if(!empty($kv_thumb_size)) add_image_size( 'vid_thumb', $kv_thumb_size[0], $kv_thumb_size[1], true );
        
        
    } // kwik_videos_setup
endif;
foreach (glob(WP_PLUGIN_DIR . "/kwik-videos/widgets/*.php") as $inc_filename) {
    include $inc_filename;
}
include WP_PLUGIN_DIR . "/kwik-videos/lib/kv_utilities.php";
function add_kv_player($content)
{
    global $post;
    
    if (is_singular() && is_main_query() && get_post_type() == 'videos') $content = kwik_player($post->ID) . $content;
    return $content;
}
add_filter('the_content', 'add_kv_player', 4);
add_action('init', 'add_video_tags');
function add_video_tags()
{
    register_taxonomy_for_object_type('post_tag', 'videos');
}
add_filter('gettext', 'kv_featured_image_text', 10, 4);
function kv_featured_image_text($translation, $text, $domain)
{
    global $post;
    if (get_post_type() == 'videos') {
        $translations = get_translations_for_domain($domain);
        if ($text == 'Featured Image')
            return $translations->translate('Video Cover Image');
        if ($text == 'Set featured image')
            return $translations->translate('Change cover image');
    }
    return $translation;
}
add_action('restrict_manage_posts', 'kv_taxonomy_filter');
function kv_taxonomy_filter()
{
    global $typenow;
    $post_types = get_post_types(array(
        '_builtin' => false
    ));
    if (in_array($typenow, $post_types)) {
        $filters = get_object_taxonomies($typenow);
        foreach ($filters as $tax_slug) {
            $tax_obj = get_taxonomy($tax_slug);
            wp_dropdown_categories(array(
                'show_option_all' => __('Show All ' . $tax_obj->label),
                'taxonomy' => $tax_slug,
                'name' => $tax_obj->name,
                'orderby' => 'name',
                'selected' => $_GET[$tax_slug],
                'hierarchical' => $tax_obj->hierarchical,
                'show_count' => false,
                'hide_empty' => true
            ));
        }
    }
}
add_filter('parse_query', 'kv_taxonomy_filter_request');
function kv_taxonomy_filter_request($query)
{
    global $pagenow, $typenow;
    if ('edit.php' == $pagenow) {
        $filters = get_object_taxonomies($typenow);
        foreach ($filters as $tax_slug) {
            $var =& $query->query_vars[$tax_slug];
            if (isset($var)) {
                $term = get_term_by('id', $var, $tax_slug);
                $var  = $term->slug;
            }
        }
    }
}


/*
function kv_filter_search($query) {
    if ($query->is_search) {
	$query->set('post_type', array('post', 'video'));
    };
    return $query;
};
add_filter('pre_get_posts', 'kv_filter_search');





*/








add_filter("manage_videos_posts_columns", "kv_change_columns");
function kv_change_columns($cols)
{
    $cols = array(
        'cb' => '<input type="checkbox" />',
        'cover' => __('Cover', 'kwik'),
        'title' => __('Title', 'kwik'),
        'tags' => __('Tags', 'kwik'),
        'video_cats' => __('Categories', 'kwik'),
        'date' => __('Date', 'kwik')
    );
    return $cols;
}
add_action("manage_posts_custom_column", "kv_custom_videos_columns", 10, 2);
function kv_custom_videos_columns($column, $post_id)
{
    switch ($column) {
        case "video_cats":
            $video_cats = get_the_terms($post_id, 'video_cats');
            if ($video_cats) {
                $results = '';
                foreach ($video_cats as $video_cat) {
                    $results .= '<a href="edit.php?post_type=videos&amp;video_cats=' . $video_cat->term_id . '">' . $video_cat->name . '</a>, ';
                }
                $results = rtrim($results, ', ');
                echo $results;
            }
            break;
        case "cover":
            if (has_post_thumbnail()) {
                the_post_thumbnail(array(
                    75,
                    75
                ));
            } else {
            }
            break;
    }
}
add_filter("manage_edit-videos_sortable_columns", "kv_sortable_videos_columns");
function kv_sortable_videos_columns()
{
    return array(
        'title' => 'title',
        'date' => 'date'
    );
}
function kv_add_videos_to_feed($qv)
{
    if (isset($qv['feed']) && !isset($qv['post_type']))
        $qv['post_type'] = array(
            'post',
            'videos'
        );
    return $qv;
}
add_filter('request', 'kv_add_videos_to_feed');
function kv_videos_in_right_now()
{
    $post_type = 'videos';
    
    if (!post_type_exists($post_type)) {
        return;
    }
    $num_posts = wp_count_posts($post_type);
    echo '';
    $num  = number_format_i18n($num_posts->publish);
    $text = _n('Video', 'Videos', $num_posts->publish);
    if (current_user_can('edit_posts')) {
        $num  = '<a href="edit.php?post_type=' . $post_type . '">' . $num . '</a>';
        $text = '<a href="edit.php?post_type=' . $post_type . '">' . $text . '</a>';
    }
    echo '<td class="first b b-videos">' . $num . '</td>';
    echo '<td class="t videos">' . $text . '</td>';
    if ($num_posts->pending > 0) {
        $num  = number_format_i18n($num_posts->pending);
        $text = _n('Video Pending', 'Videos Pending', intval($num_posts->pending));
        if (current_user_can('edit_posts')) {
            $num  = '<a href="edit.php?post_status=pending&post_type=' . $post_type . '">' . $num . '</a>';
            $text = '<a href="edit.php?post_status=pending&post_type=' . $post_type . '">' . $text . '</a>';
        }
        echo '<td class="first b b-videos">' . $num . '</td>';
        echo '<td class="t videos">' . $text . '</td>';
    }
    
    echo '</tr>';
}
add_action('right_now_content_table_end', 'kv_videos_in_right_now');
add_action('init', 'kv_create_gallery_taxonomies', 0);
function kv_create_gallery_taxonomies()
{
    $video_cats_labels = array(
        'name' => _x('Video Categories', 'taxonomy general name'),
        'menu_name' => __('Categories'),
        'singular_name' => _x('Category', 'taxonomy singular name'),
        'search_items' => __('Search Categories'),
        'all_items' => __('All Categories'),
        'parent_item' => __('Parent Category'),
        'parent_item_colon' => __('Parent Category:'),
        'edit_item' => __('Edit Category'),
        'update_item' => __('Update Category'),
        'add_new_item' => __('Add New Category'),
        'new_item_name' => __('New Category Name')
    );
    $video_types_labels = array(
        'name' => _x('Video Types', 'taxonomy general name'),
        'menu_name' => __('Types'),
        'singular_name' => _x('Type', 'taxonomy singular name'),
        'search_items' => __('Search Types'),
        'all_items' => __('All Types'),
        'parent_item' => __('Parent Type'),
        'parent_item_colon' => __('Parent Type:'),
        'edit_item' => __('Edit Type'),
        'update_item' => __('Update Type'),
        'add_new_item' => __('Add New Type'),
        'new_item_name' => __('New Type Name')
    );
    
    register_taxonomy('video_cats', array(
        'videos'
    ), array(
        'hierarchical' => true,
        'labels' => $video_cats_labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'video-categories'
        ),
        'with_front' => true
    ));
	
	
    register_taxonomy('video_type', array(
        'videos'
    ), array(
        'hierarchical' => false,
        'labels' => $video_types_labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array(
            'slug' => 'video-type'
        ),
        'with_front' => true
    ));
}
add_filter('post_class', 'kv_post_class', 10, 3);
function kv_post_class($classes, $class, $ID)
{
    $video_taxonomy = 'video_cats';
    $terms          = get_the_terms((int) $ID, $video_taxonomy);
    if (!empty($terms)) {
        foreach ((array) $terms as $order => $term) {
            if (!in_array($term->slug, $classes)) {
                $classes[] = $term->slug;
            }
        }
    }
    return $classes;
}













function kv_video_source($post_id)
{
	$video_sources = get_post_meta($post_id, 'kv_source', false);
	$video_sources = $video_sources[0];
    
    $size_count     = count($video_sources);
    $kv_video_sources = '<div id="video_sources_wrap">';
    $kv_video_sources .= '<ul id="video_sources">';
    
    //var_dump($videos_types);							
    if (is_array($video_sources)) {
        $i = 1;
        foreach ($video_sources as $video_source):
            $kv_video_sources .= '
				<li class="video_source">
				<input type="text" name="kv_source[]" class="widefat" value="' . $video_source . '" />
				<span class="move_box"></span>
				</li>';
            $i++;
        endforeach;
    } else {
        $kv_video_sources .= '
	<li class="video_source">
	<input type="text" name="kv_source[]" class="widefat" value="' . $video_sources . '" />
	<span class="move_box"></span>
	</li>';
    }
    $kv_video_sources .= '</ul>';
    $kv_video_sources .= '<input type="button" value="+" title="add source" id="add_source" />';
    if ($size_count > 1)
        $kv_video_sources .= '<input type="button" value="-" title="remove sources" id="remove_source" />';
    $kv_video_sources .= '</div>';
    
    return $kv_video_sources;
    
}








// Add the Videos meta box
function add_videos_metaboxes()
{
    add_meta_box('kv_video_details', 'Video Details', 'kv_video_details', 'videos', 'side', 'default');
	//add_meta_box('kv_video_types_meta_box', 'Video Types', 'kv_video_types_meta_box', 'videos', 'side', 'default');
}
// The Video types meta box
function kv_video_types_meta_box(){
	global $post;
	$kv_options = get_option('kwik_videos_options');
	$gi = get_post_meta($post->ID, 'kv_gi', true);
	$video_types = $kv_options['video_types'];
	$video_types_array  = get_post_meta($post->ID, 'video_types_array', false);
	
	$kv_video_types = '';
	
	

		
	// Output the types setup on the admin page
    $kv_video_types .= '<ul>';    
    $video_types_array = $video_types_array[0];	
    
	foreach ($video_types as $video_type):
		$varred = ereg_replace("[^A-Za-z0-9 ]", "", str_replace(" ","_",strtolower($video_type)));
		$cur_type_enabled = $video_types_array['video_type_'.$varred.'_enabled'];
		$kv_video_types .= '<li>';
		if(!empty($cur_type_enabled)) {	
			$kv_video_types .= '<label><input type="checkbox" checked="checked" name="video_type_'.$varred.'_enabled" value="' . $video_type . '" />'.$video_type.'</label>';
		} else {
			$kv_video_types .= '<label><input type="checkbox" name="video_type_'.$varred.'_enabled" value="' . $video_type . '" />'.$video_type.'</label>';	
			}
		$kv_video_types .= '</li>';
		$i++;
	endforeach;
		
    $kv_video_types .= '</ul>';
	echo $kv_video_types;
	
	
}
// The Video details meta box
function kv_video_details()
{
    global $post;
    
	$kv_video_details = '';
    // Noncename for security check on data origin

    $kv_video_details .= '<input type="hidden" name="videometa_noncename" id="videometa_noncename" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
    
    // Get the current data	
    $credit       = get_post_meta($post->ID, 'kv_credit', true);
    $credit_link  = get_post_meta($post->ID, 'kv_credit_link', true);
    $runtime      = get_post_meta($post->ID, 'kv_runtime', true);
    $video_source = get_post_meta($post->ID, 'kv_source', false);
	$gi = get_post_meta($post->ID, 'kv_gi', true);
	
	
  
    // all these echos will be cleaned up, promise!
    $kv_video_details .= '<span id="checkvars" style="display:none">' . WP_PLUGIN_URL . '/kwik-videos/lib/upload_s3.php</span>';
    $kv_video_details .= '
		<div id="video_upload_wrap">
		<label>Upload Video(s):</label>
		    <input type="file" name="upload_videos" multiple="" id="upload_videos" />
			<div id="upload_progress_wrap"><span class="loading"></span><progress value="0" max="100" id="upload_progress"></progress><span id="upload_progress_percent"></span></div>
			<div id="response"></div>
		</div>
		';

	$kv_video_details .= '<label>Video Source(s):</label>';
	$kv_video_details .= kv_video_source($post->ID);
	
    $kv_video_details .= '<label>Video Credit:</label>';
    $kv_video_details .= '<input type="text" name="kv_credit" value="' . $credit . '" class="widefat" />';

    
    $kv_video_details .= '<label>Video Credit Link:</label>';
    $kv_video_details .= '<input type="text" name="kv_credit_link" value="' . $credit_link . '" class="widefat" />';
    
    $kv_video_details .= '<label>Runtime:</label>';
    $kv_video_details .= '<input type="text" name="kv_runtime" value="' . $runtime . '" id="kv_runtime" class="widefat" />';
	
		// Gi or No-Gi?
	$kv_video_details .= '<div id="gi_wrap">';
	$kv_video_details .= '
	<label for="gi">Gi <input type="checkbox" name="kv_gi[]" '.checked( $gi[0], '1', false ).' value="1" id="gi"></label>
	<label for="no-gi">No-Gi <input type="checkbox" name="kv_gi[]" '.checked( $gi[1], '1', false ).' id="no-gi" value="1"></label>';
	$kv_video_details .= '</div>';
	
	echo $kv_video_details;

    
}
// Save the Metabox Data 
function kv_save_video_meta($post_id, $post){
	
	$kv_options = get_option('kwik_videos_options');
	$video_types = $kv_options['video_types'];	
	
    // make sure there is no conflict with other post save function and verify the noncename
    if (!wp_verify_nonce($_POST['videometa_noncename'], plugin_basename(__FILE__))) {
        return $post->ID;
    }
    
    // Is the user allowed to edit the post or page?
    if (!current_user_can('edit_post', $post->ID))
        return $post->ID;
		
	$video_types_array = array();
	foreach ($video_types as $video_type):
		$varred = ereg_replace("[^A-Za-z0-9 ]", "", str_replace(" ","_",strtolower($video_type)));
		$video_types_array['video_type_'.$varred.'_enabled'] = trim($_POST['video_type_'.$varred.'_enabled']);
	endforeach;

    $videos_meta = array(
		'video_types_array' => $video_types_array,
		'kv_gi' => $_POST['kv_gi'],
        'kv_source' => $_POST['kv_source'],
        'kv_credit' => $_POST['kv_credit'],
        'kv_credit_link' => $_POST['kv_credit_link'],
        'kv_runtime' => $_POST['kv_runtime']
    );
    
    // Add values of $videos_meta as custom fields 
    foreach ($videos_meta as $key => $value) {
        if( $post->post_type == 'revision' ) return;
        kv_update_post_meta( $post->ID, $key, $value );
    }
    
}
add_action('save_post', 'kv_save_video_meta', 1, 2);



// Auto-set the featured image
function videos_autoset_featured()
{
    global $post;
    $already_has_thumb = has_post_thumbnail($post->ID);
    if (!$already_has_thumb) {
        $attached_image = get_children("post_parent=$post->ID&post_type=attachment&post_mime_type=image&numberposts=1");
        if ($attached_image) {
            foreach ($attached_image as $attachment_id => $attachment) {
                set_post_thumbnail($post->ID, $attachment_id);
            }
        }
    }
} //end function
add_action('the_post', 'videos_autoset_featured');
add_action('save_post', 'videos_autoset_featured');
add_action('draft_to_publish', 'videos_autoset_featured');
add_action('new_to_publish', 'videos_autoset_featured');
add_action('pending_to_publish', 'videos_autoset_featured');
add_action('future_to_publish', 'videos_autoset_featured');


function kv_neat_trim($str, $n, $delim = '&hellip;')
{
    $len = strlen($str);
    if ($len > $n) {
        preg_match('/(.{' . $n . '}.*?)\b/', $str, $matches);
        return rtrim($matches[1]) . $delim;
    } else {
        return $str;
    }
}
add_filter("attachment_fields_to_edit", "kv_edit_attachment_fields", null, 2);
function kv_edit_attachment_fields($form_fields, $post)
{
    $form_fields["photo_credit"] = array(
        "label" => __("Photo Credit"),
        "input" => "text", // default
        "value" => get_post_meta($post->ID, "photo_credit", true)
    );
    return $form_fields;
}
add_filter("attachment_fields_to_save", "kv_save_attachment_fields", null, 2);
function kv_save_attachment_fields($post, $attachment)
{
    if (isset($attachment['photo_credit'])) {
        update_post_meta($post['ID'], 'photo_credit', $attachment['photo_credit']);
    }
    return $post;
}


function kv_settings_sections($page)
{
    global $wp_settings_sections, $wp_settings_fields;
    $kv_options = get_option('kwik_videos_options');
    
    if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$page]))
        return;
    echo '<div id="kv_settings_wrap">
      <div id="kv_settings_index_wrap">
        <ul id="kv_settings_index">
        </ul>
		<p class="kv_submit">
        <input type="submit" value="Save Changes" class="button-primary" name="Submit">
		</p>
      </div>';
    
    echo '<div id="kv_settings">';
    foreach ((array) $wp_settings_sections[$page] as $section) {
        if ($section['title'])
            if (isset($wp_settings_fields[$page][$section['id']]))
                echo '<div id="' . $section['id'] . '" class="kv_options_panel">';
        echo "<h3>{$section['title']}</h3>\n";
        call_user_func($section['callback'], $section);
        if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']]))
            continue;
        echo '<table class="form-table">';
        kv_settings_fields($page, $section['id']);
        echo '</table>';
        if (isset($wp_settings_fields[$page][$section['id']]))
            echo "</div>\n";
    }
    echo '</div></div>';
}



function kv_settings_fields($page, $section)
{
    global $wp_settings_fields;
    $kv_options = get_option('kwik_videos_options');
    
    
    
    if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section]))
        return;
    
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
        if (
		($field['id'] == 's3_bucket_dropdown' && ($kv_options['s3access_string'] == '' || $kv_options['s3secret_string'] == ''))
		||
		$field['id'] == 's3_access_key' && !isset($kv_options['use_amazon_s3'])
		||
		$field['id'] == 's3_secret_key' && !isset($kv_options['use_amazon_s3'])
		||
		$field['id'] == 's3_bucket_dropdown' && !isset($kv_options['use_amazon_s3'])
		|| 
		$field['id'] == 'video_types' && !isset($kv_options['enable_video_types'])
		) $display = 'style="display:none;"';
        
        echo '<tr valign="top" class="'.$field['id'].'_wrap" ' . $display . '>';
        
        if (!empty($field['args']['label_for']))
            echo '<th scope="row"><label for="' . $field['args']['label_for'] . '">' . $field['title'] . '</label></th>';
        else
            echo '<th scope="row">' . $field['title'] . '</th>';
        if ($field['id'] == 'num_cols')
            echo '<td class="' . $field['id'] . ' grid_cols_' . $kv_options['num_cols'] . '">';
        else
            echo '<td class="' . $field['id'] . '">';
        
        call_user_func($field['callback'], $field['args']);
        echo '</td>';
        echo '</tr>';
    }
}



function kv_runTime($file)
{
    if (!class_exists('getID3'))
        require_once('lib/getid3/getid3.php');
    $getID3     = new getID3;
    $video_info = $getID3->analyze($file);
    //return $video_info['playtime_string'];
    return $video_info['playtime_string'];
}

function kv_mimeType($filename)
{
    $fileext = substr(strrchr($filename, '.'), 1);
    if (empty($fileext))
        return (false);
    $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
    $lines = file(WP_PLUGIN_DIR . '/kwik-videos/lib/mime_types.txt');
    foreach ($lines as $line) {
        if (substr($line, 0, 1) == '#')
            continue; // skip comments
        $line = rtrim($line) . " ";
        if (!preg_match($regex, $line, $matches))
            continue; // no match to the extension
        return ($matches[1]);
    }
    return (false); // no match at all 
}


add_filter('upload_mimes', 'add_custom_mime_types');
function add_custom_mime_types($existing_mimes = array())
{
    $existing_mimes['ogv']  = 'video/ogg';
    $existing_mimes['webm'] = 'video/webm';
    unset($existing_mimes['exe']);
    return $existing_mimes;
}




function kv_update_post_meta( $post_id, $field_name, $value = '' )
{
    if ( empty( $value ) OR ! $value )
    {
        delete_post_meta( $post_id, $field_name );
    }
    elseif ( ! get_post_meta( $post_id, $field_name ) )
    {
        add_post_meta( $post_id, $field_name, $value );
    }
    else
    {
        update_post_meta( $post_id, $field_name, $value );
    }
}