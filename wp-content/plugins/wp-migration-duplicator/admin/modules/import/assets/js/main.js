(function( $ ) {
	'use strict';
	$(function() {
		
		$('#upload-btn').click(function (e) {
            e.preventDefault();
            var image = wp.media({
                title: 'Upload backup file',
                multiple: false
            }).open()
                .on('select', function (e) {
                    var uploaded_image = image.state().get('selection').first();
                    var attachment_url = uploaded_image.toJSON().url;
                    var ext_arr=attachment_url.split('.');
                    var ext=ext_arr[ext_arr.length-1];
                    if(ext!="zip")
                    {
                    	alert(wp_migration_duplicator_import.labels.onlyzipfile);
                    	return false;
                    }
                    $('.wt_mgdp_import_attachment_url').html(attachment_url);
                    $('[name="attachment_url"').val(attachment_url);
                    $('.wt_mgdp_import_er').hide().find('td').html('');
                });
        });

	    var wt_import=
	    {
	    	onPrg:0,
	    	Set:function()
	    	{
	    		$('[name="wt_mgdp_import_btn"]').click(function(){					
	    			if(wt_import.onPrg==1){ return false; }
	    			if($.trim($('[name="attachment_url"]').val())=='')
	    			{
	    				$('.wt_mgdp_import_er').show().find('td').html(wp_migration_duplicator_import.labels.backupfilenotempty);
	    				return false;
	    			}
	    			wt_import.onPrg=1;
	    			$('.spinner').css({'visiblity':'visible'});
	    			$('[name="wt_mgdp_import_btn"]').css({'opacity':'.5','cursor':'not-allowed'});
					$('.wt_mgdp_import_log_main, .wt_mgdp_import_loglist_main').show();
					$('.wt_mgdp_import_form, .wt_info_box').hide();
					$('.wt_mgdp_import_loglist_inner').html('');
					wt_import.updateLog(wp_migration_duplicator_import.labels.connecting,wp_migration_duplicator_import.labels.connecting);
					wt_import.startImport('start_import');
				});

				$('.wt_mgdp__start_new_import').click(function(){
					$('.wt_mgdp_import_attachment_url, .wt_mgdp_import_loglist_inner').html('');
					$('.wt_mgdp_import_log_main, .wt_mgdp_import_loglist_main, .wt_mgdp__start_new_import').hide();
					$('.wt_mgdp_import_form, .wt_info_box').show();
					$('[name="wt_mgdp_import_btn"]').css({'opacity':1,'cursor':'pointer'}).show();
				});
	    	},
	    	updateLog:function(label,sub_label)
	    	{
	    		$('.wt_mgdp_import_log_main').html(label);
	    		$('.wt_mgdp_import_loglist_inner').append(sub_label);
	    	},
	    	restoreImportScreen:function()
	    	{
	    		wt_import.onPrg=0;
				$('.wt_mgdp__start_new_import').show();
				$('[name="wt_mgdp_import_btn"]').hide();
				$('.spinner').css({'visiblity':'hidden'});
	    	},
	    	startImport:function(sub_action)
	    	{
	    		var data={
					_wpnonce:wp_migration_duplicator_import.nonces.main,
		            action:"wt_mgdp_import",
		            sub_action:sub_action,
		            attachment_url:$('[name="attachment_url"').val()
				};
				$.ajax({
					url:wp_migration_duplicator_import.ajax_url,
					type:'post',
					data:data,
            		dataType:'json',
            		success:function(data)
            		{
            			wt_import.updateLog(data.label,data.sub_label);
            			if(data.status)
            			{
            				if(data.finished==0)
            				{
            					wt_import.startImport(data.step);
            				}else
            				{	
            					wt_import.restoreImportScreen();
            				}
            			}else
            			{
            				alert(data.msg);
            				wt_import.restoreImportScreen();
            			}
            		},
            		error:function()
            		{
            			wt_import.restoreImportScreen();
            			alert(wp_migration_duplicator_export.labels.error);
            		}
				});
	    	}
	    }
	   	wt_import.Set(); 
	});
})(jQuery);