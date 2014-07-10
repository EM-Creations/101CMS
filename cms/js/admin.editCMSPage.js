/*
 * editCMSPage.js
 * For: Edit and new CMS page page.
 * 
 */
$(document).ready(function() {
	showHideMenuOptions();
	showHideLockOptions();
	showHideObjectOptions();
    
	/* Register Listeners */
	$("#pageDisplayInMenuYes").click(function () {
		showHideMenuOptions();
	});
    
	$("#pageDisplayInMenuNo").click(function () {
		showHideMenuOptions();
	});
    
	$("#pageLockType").change(function () {
		showHideLockOptions();
	});
	
	$("#objectType").change(function () {
		showHideObjectOptions();
	});
	
	$(".editPageButton").click(function () {
		var id = $(this).attr("id").substring(5);
		var link = "./?p=Pages&a=editPage&id="+id;
		
		window.location.href = link; // Redirect the user
	});
	
	$(".deletePageButton").click(function () {
		var id = $(this).attr("id").substring(7);
		
		$.ajax({
			type: "POST",
			url: "../admin/ajax.php",
			data: {
				req: "deletePage", 
				id: id
			},
			success: function(data) {
				var json = jQuery.parseJSON(data);

				if (json.status === "success") {
					$("#page_"+id).hide();
				}
			}
		});
	});
    
	/* Functions */
	function showHideMenuOptions() {
		if ($("#pageDisplayInMenuYes").is(":checked")) {
			$("#pageMenuOrderOpts").show();
		} else {
			$("#pageMenuOrderOpts").hide();
		}
	}
    
	function showHideLockOptions() {
		if ($("#pageLockType").val() === "none") {
			$("#pageLockTypeRankOpts").hide();
			$("#pageLockTypePermissionOpts").hide();
		} else if ($("#pageLockType").val() === "rank") {
			$("#pageLockTypeRankOpts").show();
			$("#pageLockTypePermissionOpts").hide();
		} else if ($("#pageLockType").val() === "permission") {
			$("#pageLockTypeRankOpts").hide();
			$("#pageLockTypePermissionOpts").show();
		}
	}
	
	function showHideObjectOptions() {
		if ($("#objectType").val() === "none") {
			$(".objectOpts").hide();
		} else if ($("#objectType").val() === "poll") {
			$(".objectOpts").hide();
			$("#pollObjectOpts").show();
		} else if ($("#objectType").val() === "roster") {
			$(".objectOpts").hide();
			$("#rosterObjectOpts").show();
		} else {
			$("#objectOpts").show();
		}
	}
    
});