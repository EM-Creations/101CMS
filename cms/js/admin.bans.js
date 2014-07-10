/* 
 * admin.bans.js
 * 
 * For: The admin bans editing page
 */

$(document).ready(function () {
    
	$(".unbanButton").click(function () {
		var id = $(this).attr("id").substring(6);
	
		$.ajax({
			type: "POST",
			url: "../admin/ajax.php",
			data: {
				req: "unban", 
				id: id
			},
			success: function(data) {
				var json = jQuery.parseJSON(data);
		    
				if (json.status == "success") {
					$("#ban_"+id).hide();
				}
			}
		});
	});
    
});


