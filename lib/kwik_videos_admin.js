(function ($) {
$(document).ready(function() { 


        $('#kv_settings').cycle({
            speed: 500,
            fx: 'slideX',
            sync: true,
            pager: '#kv_settings_index',
            pagerEvent: 'click',
            pauseOnPagerHover: 'true',
            allowPagerClickBubble: true,
            timeout: 0,
            pagerAnchorBuilder: function (idx, slide) {
                var ttl = $('h3', slide).text();
                return '<li><span>' + ttl + '</span></li>';
            }
        });
		
		
		
  $('#num_cols').change(function(){
	  var selected = $(this).find('option:selected');	
	  var selected_val = "grid_cols_"+$(selected).val();
	  $('td.num_cols').removeClass('grid_cols_2').removeClass('grid_cols_3').removeClass('grid_cols_4');
	  $('td.num_cols').addClass(selected_val);	
	  });
	  

// Toggle video types
	$('#enable_video_types').change(function(){
    if(this.checked){
	  $('.video_types_wrap').show(300);
	} else {
	  $('.video_types_wrap').hide(300);	
	}
	});
	
	if($('#enable_video_types').prop("checked")){
	  $('.video_types_wrap').show();
	}
	
			
		

// 	Toggle Amazon S3 Settings
	$('#use_amazon_s3').click(function(){
	  $('.s3_access_key_wrap, .s3_secret_key_wrap, .s3_bucket_dropdown_wrap').show(300);			
	});
		
	$('#use_amazon_s3').change(function(){
    if(this.checked){
	  $('.s3_access_key_wrap, .s3_secret_key_wrap, .s3_bucket_dropdown_wrap').show(300);
	} else {
	  $('.s3_access_key_wrap, .s3_secret_key_wrap, .s3_bucket_dropdown_wrap').hide(300);	
	}
	});
	

	
	if($('#use_amazon_s3').prop("checked")){
	  $('#kv_s3_settings_section').show();
	} else {
	  //$('#kv_s3_settings_section').hide();
	}
	
	
	
	// manage video types
	$('#add_type').click(function(){
		var cur_type_num = $('#video_types label:last').text();
		var new_type_num = parseInt(cur_type_num)+1;
		$('#video_types .video_type:last').clone().val('').appendTo('#video_types');
		$('#video_types label:last').text(new_type_num);
		$("#video_types").find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
			e.stopImmediatePropagation();
		});  
	});
	$('#add_source').click(function(){
		$('#video_sources .video_source:last').clone().val('').appendTo('#video_sources');
		$("#video_sources").find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
			e.stopImmediatePropagation();
		});  
	});
	$('#add_type, #add_source').one("click", function(){
		if($('#remove_type').length == 0){
		$(this).clone().appendTo('#video_types_wrap').attr('id', 'remove_type').val('-');
		}
		if($('#remove_source').length == 0){
		$(this).clone().appendTo('#video_sources_wrap').attr('id', 'remove_source').val('-');
		}
	});
	$('#remove_type').live("click", function(){
		$('#video_types .video_type:last').remove();
	});
	$('#remove_source').live("click", function(){
		if($('#video_sources .video_source').length > 1){
		$('#video_sources .video_source:last').remove();
		}
		
	});
	
$( "#video_types, #video_sources" ).sortable({
  stop: function () {
    // enable text select on inputs
    $("#video_types, #video_sources").find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
      e.stopImmediatePropagation();
    });
  }
}).disableSelection();



// enable text select on inputs
$("#video_types, #video_sources").find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) {
  e.stopImmediatePropagation();
});


	
function uploadProgress(e) {
    if (e.lengthComputable) {
      var percentComplete = Math.round((e.loaded / e.total)*100); 
	 document.getElementById("upload_progress_percent").innerHTML = percentComplete.toString() + '%';
	  $('#upload_progress').progressbar( {value: percentComplete });
    }
    else {
      document.getElementById('upload_progress_percent').innerHTML = 'unable to compute';
    }

  }

  function uploadFailed(e) {
    alert("There was an error attempting to upload the file.");
  }

  function uploadCanceled(e) {
    alert("The upload has been canceled by the user or the browser dropped the connection.");
  }


var url = jQuery('#checkvars').text();
var input = document.getElementById("upload_videos"), formdata = false;
		

	if (window.FormData) {
  		formdata = new FormData();
	}
	
 	input.addEventListener("change", function (evt) {
 		document.getElementById("response").innerHTML = "Uploading &hellip;"
		$('.video_source input[type="text"]').val('');
		$('#kv_runtime').val('');
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
					formdata.append("upload_videos[]", file);
					formdata.append("post_ID", jQuery('#post_ID').val());
				}
			//}	
		}
	
		if (formdata) {
			$('#upload_progress_wrap').slideDown(250);
			$.ajax({
				url: url,
				type: "POST",
				xhr: function() {  // custom xhr
					myXhr = $.ajaxSettings.xhr();			
					if ( myXhr.upload ) {
						myXhr.addEventListener('progress', uploadProgress, false);
						myXhr.addEventListener('error', uploadFailed, false);
						myXhr.addEventListener('abort', uploadCanceled, false);
					}
					return myXhr;					
				},
				data: formdata,
				processData: false,
				contentType: false,
				dataType: 'JSON',
				success: function(data){
					//data = $.parseJSON(data.posts);
					//console.log(data.posts);
					//data = data.posts;
					
					$.each(data.videos,function(i,video){
						if(i == 0) {
							$('#video_sources .video_source input[type="text"]').val(video.furl);
						} else{
							$('#video_sources .video_source:last').clone().appendTo('#video_sources');
							$('#video_sources .video_source:last input[type="text"]').val(video.furl);
						}
						$("#video_sources").find("input").bind('mousedown.ui-disableSelection selectstart.ui-disableSelection', function(e) { e.stopImmediatePropagation(); });	
						if(video.duration != '') $('#kv_runtime').val(video.duration);
						console.log(video.duration);
					});
				},
				complete: function(){
					if($('#remove_source').length == 0) $('#add_source').clone().appendTo('#video_sources_wrap').attr('id', 'remove_source').val('-');
					$("#response").hide();	
					$('#upload_progress_wrap').slideUp(250);									
				},
			error: function(error){
				console.log(error);
				}
			});
		}
	}, false);
});
}(jQuery));