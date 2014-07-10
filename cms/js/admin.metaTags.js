/* 
 * admin.metaTags.js
 * 
 * For: The admin meta tag editing page
 */

$(document).ready(function () {
	$(".deleteMetaTag").click(function () {
		var id = $(this).attr("rel");
		var doDelete = confirm("Are you sure you want to delete this meta tag?");
	
		if (doDelete) { // If they clicked okay on the confirm dialog
			// Use the id to send an ajax request to delete the meta tag
			$.ajax({
				type: "GET",
				url: "../admin/ajax.php",
				data: {
					req: "deleteMetaTag", 
					tagID: id
				},
				success: function(data) {
					var json = jQuery.parseJSON(data);
		    
					if (json.status == "success") {
						$("#tag_"+id).hide();
					}
				}
			});
		}
	});
});


