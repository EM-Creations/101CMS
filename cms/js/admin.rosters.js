/* 
 * admin.rosters.js
 * 
 * For: The admin rosters editing pages
 */

$(document).ready(function () {
	/* Edit Roster Page */
	$(".removeRosterMemberButton").click(function () {
		var id = $(this).attr("id").substring(7); // Get this rank's ID

		var doRemove = confirm("Are you sure you want to remove this roster member?");

		if (doRemove) { // If they clicked okay on the confirm dialog
			$.ajax({
				type: "GET",
				url: "../admin/ajax.php",
				data: {
					req: "removeRosterMember", 
					memberID: id
				},
				success: function(data) {
					var json = jQuery.parseJSON(data);
		    
					if (json.status == "success") {
						$("#member_"+id).hide();
					}
				}
			});
		}
	});
    
	/* Roster List Page */
	$(".deleteRosterButton").click(function () {
		var id = $(this).attr("id").substring(7); // Get this rank's ID

		var doDelete = confirm("Are you sure you want to delete this roster?");
	
		if (doDelete) { // If they clicked okay on the confirm dialog
			$.ajax({
				type: "GET",
				url: "../admin/ajax.php",
				data: {
					req: "deleteRoster", 
					rosterID: id
				},
				success: function(data) {
					var json = jQuery.parseJSON(data);
		    
					if (json.status == "success") {
						$("#roster_"+id).hide();
					}
				}
			});
		}
	});
    
	$(".editRosterButton").click(function () {
		var id = $(this).attr("id").substring(5); // Get this rank's ID

		// Redirect user to the relevant page for editing this rank
		window.location = "./?p=Rosters&roster="+id;
	});
});


