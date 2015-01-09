function setGooglePlus(){
var googleplus = '<g:plusone size="medium"></g:plusone>';
$('#social_btns #googleplus_button').empty().append(googleplus);
(function() {
var e = document.createElement('script'); e.async = true;
e.src = 'https://apis.google.com/js/plusone.js';
document.getElementById('googleplus_button').appendChild(e);
}());
}


function setFacebookLike() {
$('html').attr("xmlns:og","http://ogp.me/ns#").attr("xmlns:fb","http://www.facebook.com/2008/fbml");
// Remove previously created FB like elements -- if they exist -- so they can be re-added after AJAX pagination
$('.fb-recommend').remove();
$('#fb-root').remove();

// Build and inject Like button




var fb_url = window.location;

var esc_fb_url = escape(fb_url);

var fb_count = ["http://graph.facebook.com/?id="+esc_fb_url];





$.getJSON( fb_count, function(data) {
	
	if (typeof data.id != "undefined") { facebook_count = '0';} else { facebook_count = data.shares;}	
	
var fb_like = '<fb:like href="'+fb_url+'" layout="button" show_faces="false" action="like" data-width="100" width="100" colorscheme="dark"></fb:like><span class="fb_btn">LIKE</span><span class="fb_count">'+facebook_count+'</span>';

$('#social_btns #facebook_like').empty().append(fb_like);	
	
	
	// Load in FB javascript SDK
$('body').append('<div id="fb-root"></div>');
window.fbAsyncInit = function() {
FB.init({appId: '302948373078551', status: true, cookie: true, xfbml: true});				
};
(function() {
var e = document.createElement('script'); e.async = true;
e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
document.getElementById('fb-root').appendChild(e);


FB.Event.subscribe('edge.create', function(href, widget) {
    //alert('You just liked the page!');
	var cur_count = $('#facebook_like .fb_count').text();
	var cur_count = parseInt(cur_count);
	$('#facebook_like .fb_count').text(cur_count+1);
	
  });


}());

	
});





}





		
function setTwitterBtn() {

	  var imageTitle = $photo_title.data("imageTitle");
	  
	  $('#retweet_button').empty();
	  
	  var standard = '<a class="tweets" href="{{allTweetsURL}}" title="View Tweets">{{count}}</a>' + '<a onclick="window.open(\'{{retweetURL}}\',\'Twitter\',\'height=398, width=598,scrollbars=no,toolbar=no\')" title="Tweet this photo!" href="javascript:void(0)" class="retweet">TWEET</a>';
	  
	  $('#retweet_button').customRetweet({
		  url: encodeURIComponent(window.location.href),
		  retweetTemplate: '@{{account}} | {{title}} | {{shortURL}}',
		  template: standard
	  });

}




















function documentsGrid(){

$myRegExp = new RegExp("([^(\?#)]*)");
thisUrl = $myRegExp.exec(window.location)[0];
	
	var offset = 0;
	var category = getUrlVars()["video_cat"];
	
	var no_results = new Boolean();
if (company) {
	$("#company_filter").val(company);
}
if (category) {
	$("#doc_type_filter").val(category);
}

		$("#grid_filter").change(function(e) {
			offset = 0;

			var varstr = "";
          $("#grid_filter select").each(function() {
                varstr += $(this).attr('name')+'=';
				varstr += $(this).val()+'&';				
              });
			  var urlString = varstr.substr(0, varstr.length-1); 
			  new_url = thisUrl + "?"+urlString;
	
			  if ($.browser.msie) {
				 History.pushState({path:new_url},'',new_url);
			  } else {
				 window.history.pushState({path:new_url},'',new_url);
				  }
	  
			 e.preventDefault();			 
			 filterDocuments();			 	   
		});
		
		
		$(".tag-links a").live("click", function(e){
			offset = 0;
			var tag_url = $(this).attr('href');
			var tag_url_trimmed = tag_url.substr(0, tag_url.length-1);
			var tag = tag_url_trimmed.split('tag/')[1];
			$('#keyword_filter').val(tag);
			var varstr = "";

          $("#grid_filter select").each(function() {
                varstr += $(this).attr('name')+'=';
				varstr += $(this).val()+'&';				
              });

			  var urlString = varstr.substr(0, varstr.length-1); 
			  new_url = thisUrl + "?"+urlString;
			  window.history.pushState({path:new_url},'',new_url);		  
			 e.preventDefault();
			 filterDocuments();
		});	 

$('#documents_wrap').tinyscrollbar();


// fancy detect end of scroll (lol)
$('#documents_wrap .overview').watch('top', function(){
       if(parseFloat($(this).css('top')) <= -($('#documents_wrap .overview').height()-895)){
		  if(offset == 0) {offset = 16;} else{
			  offset = offset+8;
			  }
		  console.log(offset);
		  filterDocuments(offset);
        }
 });

}


function filterDocuments(offset){
	
	  var the_container = $("#documents_wrap .overview");
	  var doc_type = $("#doc_type_filter").val();
	  var keyword = $("#keyword_filter").val();
	  var year = $("#publish_year").val();
	  var company = $("#company_filter").val();
	  var sortby = $("#grid_sort").val();
	  var order = $("#grid_order").val();
	  if(!offset) var offset = 0;
	  var no_results = new Boolean();
	  
	

		 jQuery.ajax({
			  url: 'https://tgstudent.com/wp-admin/admin-ajax.php',
			  data:{
				   'action':'do_ajax',
				   'fn':'filter_documents_grid',
						   'company': company,
						   'doc_type': doc_type,
						   'keyword': keyword,					   
						   'year': year,
						   'sortby': sortby,
						   'order': order,
						   'offset': offset
				   },
			  dataType: 'JSON',
			  success:function(data){			  
				  data = $.parseJSON(data);
				  if(offset == 0){
				  $('#documents_wrap .overview').addClass('results').empty();
				  } else {
					$('#documents_wrap .overview').addClass('results');					  
				  }
				  if(jQuery.isEmptyObject(data)) {
					  $('#documents_wrap .overview').append('<div>No entries match your search criteria</div>');
					  no_results = true;
				  } else {
					  if(offset == 0){
					  $.scrollTo('#grid_filter', 500);
					  }

					 $.each(data.posts,function(i,post){
						 var elem = "<article class=\""+post.classes+"\">"+post.featured+"<div class=\"inside\"><header class=\"entry-header\">"+post.icon+"<div itemprop=\"category\" class=\"category\">"+post.cat+"</div>"+post.image+"<h3 class=\"entry-title\"><a rel=\"bookmark\" title=\"Link to "+post.title+"\" href=\""+post.permalink+"\">"+post.title+"</a></h3></header>"+post.company+post.date+"<br/>"+post.tags+post.button;
						 $('#documents_wrap .overview').append(elem);
									  
					  });
					  
				  }
	
			 },
			  error: function(errorThrown){
				   console.log(errorThrown);
			  }
	 
		 }).done(function( msg ) {
			i = 0;		  
		  (function() {
			  $($('.hentry')[i++]).animate({'opacity':1}, 100, arguments.callee);
		  })();	
				  if(offset == 0){
					  $('#documents_wrap').tinyscrollbar_update();
				  } else {
					  if(no_results != true) $('#documents_wrap').tinyscrollbar_update(($('#documents_wrap .overview').height()-1481));
				  }
			  });
	
	}
















jQuery(document).ready(function($) {

var ul = $('ul#video_categories');

/*
if (ul.length) {
    ul.children('li').hover(function() {
        $(this).stop().children('ul').slideDown(250);
    }, function() {
        $(this).stop().children('ul').slideUp(250);
    }).children('ul').hide().parent().addClass('has_subs');
}
*/


	// third example
	$(ul).treeview({
		animated: "fast",
		collapsed: true,
		unique: true,
		persist: "cookie",
		toggle: function() {
			window.console && console.log("%o was toggled", this);
		}
	});

//$("ul").has("li").addClass("full");


$('.vid_order').click(function(){
	
	var the_value = $(this).attr('title');
	$('#video_filter #order').val(the_value);
	filter_videos();	
	
});

$('.vid_sort').click(function(){
	
	var the_value = $(this).attr('title');
	$('#video_filter #sortby').val(the_value);
	filter_videos();	
	
});

$("#video_filter").change(function(e) {
	filter_videos();	 
});


function filter_videos(){
	
	$myRegExp = new RegExp("([^(\?#)]*)");
	thisUrl = $myRegExp.exec(window.location)[0];
	
	var varstr = "";
	$("#video_filter select").each(function() {
		var the_name = $(this).val();
		if(the_name != 'all'){
		varstr += $(this).attr('name')+'=';
		varstr += $(this).val()+'&';
		}
	});
	
	$("#video_filter input").each(function() {
		varstr += $(this).attr('name')+'=';
		varstr += $(this).val()+'&';				
	});
	
	var urlString = varstr.substr(0, varstr.length-1); 
	new_url = thisUrl + "?"+urlString;
	
	window.location = new_url;
			  
}

});


