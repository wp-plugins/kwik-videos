jQuery(document).ready(function($) { 

// 	Toggle Amazon S3 Settings
	$('#use_amazon_s3').click(function(){
	  $('#kv_s3_settings_section').slideDown(300);			
	});
		
	$('#use_amazon_s3').change(function(){
    if(this.checked){
	  $('#kv_s3_settings_section').slideDown(300);
	} else {
	  $('#kv_s3_settings_section').slideUp(300);	
	}
	});
	
	if($('#use_amazon_s3').prop("checked")){
	  $('#kv_s3_settings_section').show();
	} else {
	  //$('#kv_s3_settings_section').hide();
	}
	
	
	

var url = jQuery('#checkvars').text();
var input = document.getElementById("images"), formdata = false;
		

	if (window.FormData) {
  		formdata = new FormData();
	}
	
 	input.addEventListener("change", function (evt) {
 		document.getElementById("response").innerHTML = "<span class='loading'>Uploading &hellip;</span>"
		jQuery('#video_source').val('');
		jQuery('#_runtime').val('');
 		var i = 0, len = this.files.length, img, reader, file;		
	
		for ( ; i < len; i++ ) {
			file = this.files[i];
	
			//if (!!file.type.match(/video.*/)) {
				if ( window.FileReader ) {
					reader = new FileReader();
					reader.onloadend = function (e) { 
						//showUploadedItem(e.target.result, file.fileName);	
					};
					reader.readAsDataURL(file);
					
				} else {					
					alert('This is not a video file.');
					formdata = false;
					}
				if (formdata) {
					formdata.append("images[]", file);
					formdata.append("post_ID", jQuery('#post_ID').val());	
				}
			//}	
		}
	
		if (formdata) {
			
			jQuery.ajax({
				url: url,
				type: "POST",
				data: formdata,
				processData: false,
				contentType: false,
				dataType: 'JSON',
				success: function(data){
					jQuery.each(data.posts,function(i,post){
					document.getElementById("response").innerHTML = post.msg;
					jQuery('#video_source').val(post.furl);	
					jQuery('#_runtime').val(post.duration);
						 
						 }); 
				}
			});
		}
	}, false);
});
