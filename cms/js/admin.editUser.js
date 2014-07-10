/* 
 * admin.editUser.js
 * 
 * For: The admin edit user page
 */

$(document).ready(function () {
	showHideBanExpiry();
    
	$(".banType").click(function () {
		showHideBanExpiry();
	});
    
	function showHideBanExpiry() {
		if ($("#banTypeTemp").is(":checked")) { // If the temporary ban type radio button is checked
			$("#banExpiry_opts").show();
		} else {
			$("#banExpiry_opts").hide();
		}
	}
    
});


