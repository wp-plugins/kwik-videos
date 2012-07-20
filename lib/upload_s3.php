<?php
$json = array();
$data = array();
$furl  = array();


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
    $count_files = count($_FILES['images']);
    
    // foreach file uploaded do the upload
    foreach (range(0, $count_files) as $i) {
        // create an array of the $_FILES for each file
        $file_array = array(
            'name' => $_FILES['images']['name'][$i],
            'type' => $_FILES['images']['type'][$i],
            'tmp_name' => $_FILES['images']['tmp_name'][$i],
            'error' => $_FILES['images']['error'][$i],
            'size' => $_FILES['images']['size'][$i]
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
                $furl[] = $uploaded_file['url'];
				$data['duration'] = kv_runTime($filename);
                
            } else {
				// was not success
				$data['msg']  = "Sorry but this is not an allowed file type. MP4, FLV, WebM, or OGV only";
            }
            
        }
    }
    
} else {
	

	
if($_FILES['images']) {
	
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
    $fileName     = $_FILES['images']['name'];
    $fileTempName = $_FILES['images']['tmp_name'];
    
    foreach ($_FILES["images"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $fileName     = $_FILES["images"]["name"][$key];
            $fileTempName = $_FILES["images"]["tmp_name"][$key];
			
			$fileType = wp_check_filetype(basename(fileName), null);
            
            //move the file
            if ($s3->putObjectFile($fileTempName, s3bucket, $fileName, S3::ACL_PUBLIC_READ)) {
                //$contents = $s3->getBucket($s3bucket);							
                //$video_name = urlencode(array_shift(array_keys($contents)));
                
                $fname = str_replace(' ', '%20', $fileName);
                $furl[]  = "http://" . $s3bucket . ".s3.amazonaws.com/" . $fname;
                
                //$data['keys'] = $contents[0][0];
                $data['msg']  = "The file $fileName was successfully uploaded to S3";
				$data['duration'] = kv_runTime($fileTempName);
                
                
            } else {
                $data['msg'] = "The file $fileName, was not successfully uploaded to S3";
            }
        }
    }
}// END if(isset($_FILES['images']) && ($_FILES['images']['size'] > 0)) 
    
} // END if isset($use_amazon_s3)	
$data['furl'] = $furl;
$json['posts'][] = $data;
header('Content-type: application/json;');
echo json_encode($json);