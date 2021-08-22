(function( $ ) {
	'use strict';
	$(function() {
		
		$('.wf-tab-head .nav-tab[href="#wt-mgdp-backups"]').click(function(){
			/*if($('div[data-id="wt-mgdp-backups"]').attr('data-visible')==0)
			{ */
				$('.wt_mgdp_backup_list_table tbody').html('<tr><td colspan="6" align="center">'+wp_migration_duplicator_backups.labels.connecting+'</td></tr>');
				$.ajax({
					success:function(data)
					{
						var temp_elm=$('<div />').html(data);
						var tbodyht=temp_elm.find('.wt_mgdp_backup_list_table tbody').html();
						var otherzp=temp_elm.find('.wt_mgdp_other_backupfiles').html();
						$('.wt_mgdp_backup_list_table tbody').html(tbodyht);
						$('.wt_mgdp_other_backupfiles').html(otherzp);
					}
				});
			/* } */
		});

		$(document).on('click','.wt_mgdp_delete_backup',function(){
			if(confirm(wp_migration_duplicator_backups.labels.sure))
			{	
				var export_id=$(this).attr('data-id');
				var tr=$(this).parents('tr');
				tr.css({'opacity':.5});

				var data={
					_wpnonce:wp_migration_duplicator_backups.nonces.main,
		            action:"wt_mgdp_backups",
		            sub_action:'delete',
		            export_id:export_id,
				};

				$.ajax({
					url:wp_migration_duplicator_backups.ajax_url,
					type:'post',
					data:data,
	        		dataType:'json',
					success:function(data)
					{
						if(data.status)
						{
							tr.remove();
							if($('.wt_mgdp_backup_list_table tbody tr').length==0)
							{
								$('.wt_mgdp_backup_list_table tbody').html(wt_mgdp_no_bckup_html);
							}
						}else
						{
							alert(data.msg);
						}
					},
					error:function()
            		{
            			alert(wp_migration_duplicator_backups.labels.error);
            		}
				});
			}			
		});

	});
})(jQuery);