(function( $ ) {
	'use strict';
	$(function() {
		var wf_tab_view=
	    {
	    	Set:function()
	    	{
	    		this.subTab();
	    		var wf_nav_tab=$('.wf-tab-head .nav-tab');
			 	if(wf_nav_tab.length>0)
			 	{
				 	wf_nav_tab.click(function(){ 
				 		var wf_tab_hash=$(this).attr('href');
				 		wf_nav_tab.removeClass('nav-tab-active');
				 		$(this).addClass('nav-tab-active');
				 		wf_tab_hash=wf_tab_hash.charAt(0)=='#' ? wf_tab_hash.substring(1) : wf_tab_hash;
				 		var wf_tab_elm=$('div[data-id="'+wf_tab_hash+'"]');
				 		$('.wf-tab-content').hide();
				 		if(wf_tab_elm.length>0 && wf_tab_elm.is(':hidden'))
				 		{	 			
				 			wf_tab_elm.stop(true,true).fadeIn();
				 		}
				 	});
				 	$(window).on('hashchange', function (e) {
					    var location_hash=window.location.hash;
					 	if(location_hash!="")
					 	{
					    	wf_tab_view.showTab(location_hash);
					    }
					}).trigger('hashchange');

				 	var location_hash=window.location.hash;
				 	if(location_hash!="")
				 	{
				 		wf_tab_view.showTab(location_hash);
				 	}else
				 	{
				 		wf_nav_tab.eq(0).click();
				 	}		 	
				}
	    	},
	    	showTab:function(location_hash)
	    	{
	    		var wf_tab_hash=location_hash.charAt(0)=='#' ? location_hash.substring(1) : location_hash;
		 		if(wf_tab_hash!="")
		 		{
		 			var wf_tab_hash_arr=wf_tab_hash.split('#');
		 			wf_tab_hash=wf_tab_hash_arr[0];
		 			var wf_tab_elm=$('div[data-id="'+wf_tab_hash+'"]');
			 		if(wf_tab_elm.length>0 && wf_tab_elm.is(':hidden'))
			 		{	 			
			 			$('a[href="#'+wf_tab_hash+'"]').click();
			 			if(wf_tab_hash_arr.length>1)
				 		{
				 			var wf_sub_tab_link=wf_tab_elm.find('.wf_sub_tab');
				 			if(wf_sub_tab_link.length>0) /* subtab exists  */
				 			{
				 				var wf_sub_tab=wf_sub_tab_link.find('li[data-target='+wf_tab_hash_arr[1]+']');
				 				wf_sub_tab.click();
				 			}
				 		}
			 		}
		 		}
	    	},
	    	subTab:function()
	    	{
	    		$('.wf_sub_tab li').click(function(){
					var trgt=$(this).attr('data-target');
					var prnt=$(this).parent('.wf_sub_tab');
					var ctnr=prnt.siblings('.wf_sub_tab_container');
					prnt.find('li a').css({'color':'#0073aa','cursor':'pointer'});
					$(this).find('a').css({'color':'#ccc','cursor':'default'});
					ctnr.find('.wf_sub_tab_content').hide();
					ctnr.find('.wf_sub_tab_content[data-id="'+trgt+'"]').fadeIn();
				});
				$('.wf_sub_tab').each(function(){
					var elm=$(this).children('li').eq(0);
					elm.click();
				});
	    	}
	    }
	    wf_tab_view.Set();

	});
})( jQuery );

var wf_progress_bar=
{
	Set:function(vl,elm,inner_text,no_animate)
	{		
		if(elm)
		{
			var bar_inner=elm.find('.wf_progress_bar_inner');
			if(inner_text)
			{
				elm.find('.wf_progress_bar_label').html(inner_text);
			}
		}else
		{
			var bar_inner=jQuery('.wf_progress_bar_inner');
		}
		bar_inner.parent('.wf_progress_bar').show();
		if(no_animate || vl==0)
		{
			bar_inner.css({'width':(vl+'%')}).html(vl+'%').attr({'data-val':vl});
		}else
		{
			bar_inner.stop(true,true).animate({'width':(vl+'%')},200).html(vl+'%').attr({'data-val':vl});
		}
	},
	updateLabel:function(elm,inner_text)
	{
		if(elm)
		{
			var bar_inner=elm.find('.wf_progress_bar_inner');
			if(inner_text)
			{
				elm.find('.wf_progress_bar_label').html(inner_text);
			}
		}
	},
	Reset:function(vl,elm,inner_text)
	{
		this.Set(vl,elm,inner_text,true);
	}
}
