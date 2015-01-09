<?php
header('Content-type: application/json;');
$json = array();
$data = array();
ini_set('post_max_size', '200M');
ini_set('upload_max_filesize', '200M');
define('WP_USE_THEMES', false);
require $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/image.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/file.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-admin/includes/media.php');

$kv_options    = get_option('kwik_videos_options');
$s3key         = $kv_options["s3access_string"];
$s3secret      = $kv_options["s3secret_string"];
$s3bucket      = $kv_options["s3_bucket_dropdown"];
$use_amazon_s3 = $kv_options["use_amazon_s3"];
	
if (!isset($use_amazon_s3)) {
	
	$allowed_file_types = array(
	'ogv' =>'video/ogg',
	'webm' =>'video/webm',
	'mp4' => 'video/mp4',
	'flv' => 'video/x-flv'
	);
	
    // required for wp_handle_upload() to upload the file
    $upload_overrides = array(
        'test_form' => FALSE,
		'mimes' => $allowed_file_types
    );
    
    // count how many files were uploaded
    $count_files = count($_FILES['upload_videos']);
	
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$post_max = (int)(ini_get('post_max_size'));	
	$posted_size = $_SERVER['CONTENT_LENGTH']/1048576;

	
	if($posted_size > $max_upload) 
	{
		$data['msg']  = "Your max server's max upload size is ".$max_upload."MB. You need to change your php.ini or upload a smaller file.";
		
	} elseif($posted_size > $post_max) 
	{
		$data['msg']  = "Your max server's post size is ".$post_max."MB. You need to change your php.ini or upload a smaller file.";
		
	} else {
    
    // foreach file uploaded do the upload
    foreach (range(0, $count_files) as $i) {
        // create an array of the $_FILES for each file
        $file_array = array(
            'name' => $_FILES['upload_videos']['name'][$i],
            'type' => $_FILES['upload_videos']['type'][$i],
            'tmp_name' => $_FILES['upload_videos']['tmp_name'][$i],
            'error' => $_FILES['upload_videos']['error'][$i],
            'size' => $_FILES['upload_videos']['size'][$i]
        );
        
        // check to see if the file name is not empty
        if (!empty($file_array['name'])) {
            // upload the file to the server
            $uploaded_file = wp_handle_upload($file_array, $upload_overrides);
            
            if ($uploaded_file) {
                // checks the file type and stores it in a variable
                $wp_filetype = wp_check_filetype(basename($uploaded_file['file']), null);
				$wp_upload_dir = wp_upload_dir();
				$filename = $uploaded_file['file'];
				
				$attachment = array(
				   'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $filename ), 
				   'post_mime_type' => $wp_filetype['type'],
				   'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
				   'post_content' => '',
				   'post_status' => 'inherit'
				);
				
				$attach_id = wp_insert_attachment( $attachment, $filename, $_POST['post_ID'] );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
                

                $data['msg']  = "The file " . preg_replace('/\.[^.]+$/', '', basename($filename)) . ", was successfully uploaded.";
                $data['furl']  = $uploaded_file['url'];
				$data['duration'] = kv_runTime($filename);
				//$data['duration'] = var_export($uploaded_file);
                
            } else {
				// was not success
				$data['msg']  = "Sorry but this is not an allowed file type. MP4, FLV, WebM, or OGV only";
            }
			$json['videos'][] = $data;
		}
            
        } // end foreach
    }
    
} else {
	

	
if($_FILES['upload_videos']) {
	
    //include the S3 class
    if (!class_exists('S3'))
        require_once('S3.php');
    
    //create a new bucket
    //$s3->putBucket("$s3bucket", S3::ACL_PUBLIC_READ);
    
    //AWS access info
    if (!$s3key) {
        echo '<p><strong>Access Key Missing</strong><br/>Before you can start uploading files, you need to <a href="">define your Amazon S3 Settings</a>.';
        die();
    } else {
        define('awsAccessKey', $s3key);
    }
    
    if (!$s3secret) {
        echo '<p><strong>Secret Key Missing</strong><br/>Before you can start uploading files, you need to <a href="">define your Amazon S3 Settings</a>.';
        die();
    } else {
        define('awsSecretKey', $s3secret);
    }
    
    if (!$s3bucket) {
        echo '<p><strong>No Bucket Selected</strong><br/>Before you can start uploading files, you need to <a href="">define your Amazon S3 Settings</a>.';
        echo $s3bucket;
        die();
    } else {
        define('s3bucket', $s3bucket);
    }
    
    
    //instantiate the class
    $s3 = new S3(awsAccessKey, awsSecretKey);
    
    //retreive post variables
    $fileName     = $_FILES['upload_videos']['name'];
    $fileTempName = $_FILES['upload_videos']['tmp_name'];
    
    foreach ($_FILES["upload_videos"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $fileName     = $_FILES["upload_videos"]["name"][$key];
            $fileTempName = $_FILES["upload_videos"]["tmp_name"][$key];
			
			$fileType = wp_check_filetype(basename(fileName), null);
            
            //move the file
            if ($s3->putObjectFile($fileTempName, s3bucket, $fileName, S3::ACL_PUBLIC_READ)) {
                //$contents = $s3->getBucket($s3bucket);							
                //$product_name = urlencode(array_shift(array_keys($contents)));
                
                $fname = str_replace(' ', '%20', $fileName);

				$data['furl']  = "http://" . $s3bucket . ".s3.amazonaws.com/" . $fname;
                
                //$data['keys'] = $contents[0][0];
                $data['msg']  = "The file $fileName was successfully uploaded to S3";
				$data['duration'] = kv_runTime($fileTempName);

				
                
                
            } else {
                $data['msg'] = "The file $fileName, was not successfully uploaded to S3";
            }
			$json['videos'][] = $data;
        }
		
    }
}// END if(isset($_FILES['upload_videos']) && ($_FILES['upload_videos']['size'] > 0)) 
    
} // END if isset($use_amazon_s3)	


echo json_encode($json);