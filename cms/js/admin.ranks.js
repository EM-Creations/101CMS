/* 
 * admin.ranks.js
 * 
 * For: The admin rank editing page
 */

$(document).ready(function () {
	$(".deleteRankButton").click(function () {
		var id = $(this).attr("id").substring(7); // Get this rank's ID

		var doDelete = confirm("Are you sure you want to delete this rank?");
	
		if (doDelete) { // If they clicked okay on the confirm dialog
			$.ajax({
				type: "GET",
				url: "../admin/ajax.php",
				data: {
					req: "deleteRank", 
					rankID: id
				},
				success: function(data) {
					var json = jQuery.parseJSON(data);
		    
					if (json.status == "success") {
						$("#rank_"+id).hide();
					}
				}
			});
		}
	});
    
	$(".editRankButton").click(function () {
		var id = $(this).attr("id").substring(5); // Get this rank's ID
	
		// Redirect user to the relevant page for editing this rank
		window.location = "./?p=Ranks&rank="+id;
	});
});


