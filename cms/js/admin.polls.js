/* 
 * admin.polls.js
 * 
 * For: The admin polls editing page
 */

$(document).ready(function () {
	showHideExpiry(); // Show or hide the expiry field when the page is first loaded
	showHideLock(); // Show or hide the lock field when the page is first loaded

	/* Listeners */
	$("#pollExpiryType").change(function () {
		showHideExpiry();
	});
    
	$("#pollLockType").change(function () {
		showHideLock();
	});
	
	$(".editPollButton").click(function () { // Called whenever a edit poll button is clicked
		var id = $(this).attr("id").substring(5);
		var link = "./?p=Polls&poll="+escape(id);
		window.location.href = link;
	});
	
	$(".deletePollButton").click(function () { // Called whenever a delete poll button is clicked
		var id = $(this).attr("id").substring(7);
		var doDelete = confirm("Are you sure you want to delete this poll?");
	
		if (doDelete) { // If they clicked okay on the confirm dialog
			// Use the id to send an ajax request to delete the poll
			$.ajax({
				type: "GET",
				url: "../admin/ajax.php",
				data: {
					req: "deletePoll", 
					id: id
				},
				success: function(data) {
					var json = jQuery.parseJSON(data);
		    
					if (json.status == "success") {
						$("#poll_"+id).hide();
					}
				}
			});
		}
	});

	/* Functions */
	function showHideExpiry() {
		if ($("#pollExpiryType").val() == "date") { // If the "Date" option is selected
			$("#pollExpiryOpts").show();
		} else if ($("#pollExpiryType").val() == "never") {
			$("#pollExpiryOpts").hide();
		}
	}
    
	function showHideLock() {
		if ($("#pollLockType").val() == "none") {
			$("#pollLockTypeRankOpts").hide();
			$("#pollLockTypePermissionOpts").hide();
		} else if ($("#pollLockType").val() == "rank") {
			$("#pollLockTypeRankOpts").show();
			$("#pollLockTypePermissionOpts").hide();
		} else if ($("#pollLockType").val() == "permission") {
			$("#pollLockTypeRankOpts").hide();
			$("#pollLockTypePermissionOpts").show();
		}
	}
    
});


