/* 
 * ajaxLogin.js
 * 
 * For: Handling AJAX login requests
 */

$(document).ready(function () {
	$("#ajaxLoginButton").click(function () {
		loginHandler();
	});
   
	$("#ajaxLogout").click(function () {
		logoutHandler();
	});
	
	$("#ajaxLoginTable").bind("keyup", function (e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code === 13) { // If the enter key has been pressed
			loginHandler();
		}
	});
   
	function loginHandler() {
		// Send Login AJAX Request
		$.ajax({
			type: "POST",
			url: "./cms/ajax.php",
			data: {
				req: "login", 
				user: $("#ajaxLoginUser").val(), 
				pass: $("#ajaxLoginPassword").val()
				},
			success: function (data) {
				// Get a JSON object of the returned data and use it to determine whether the login attempt was successful
				var json = jQuery.parseJSON(data);
	       
				if (json.status === "success") {
		    
					var profileLinkName = encodeURIComponent(json.user.name);
					$("#ajaxLoginBox").html("<div style=\"padding-left: 20px; padding-top: 10px;\">\n\n\
		     <table id=\"ajaxLoginTable\"><tr>\n\
		     <td rowspawn=\"3\">"+json.user.avatar+"</td>\n\
		     <td>Welcome <a href=\"./?p=Profile&u="+profileLinkName+"\">"+json.user.name+"</a><br />\n\n\
		     <a href=\"./?p=Account\">My Account</a><br />\n\n\
		     <a href=\"#\" id=\"ajaxLogout\">Logout</a><br />\n\
		     </td></tr>\n\
		     </div>\n"); // Set new text in login box
		   
					$("#ajaxLogout").click(function () {
						logoutHandler();
					});
		    
				} else if (json.status === "error") {
					console.log("Login error: "+json.errormsg);
				} else if (json.status === "failed") {
					console.log("Login failed");
				}
			}
		});
	}
   
	function logoutHandler() {
		// Send Logout AJAX Request
		$.ajax({
			type: "POST",
			url: "./cms/ajax.php",
			data: {
				req: "logout"
			},
			success: function (data) {
				// Get a JSON object of the returned data and use it to determine whether the login attempt was successful
				var json = jQuery.parseJSON(data);

				if (json.status === "success") {
					$("#ajaxLoginBox").html("<div style=\"padding-left: 20px; padding-top: 10px;\">\n\
			<table id=\"ajaxLoginTable\">\n\
			<tr><td class=\"right\">Username <input type=\"text\" id=\"ajaxLoginUser\" name=\"ajaxLoginUser\" size=\"7\" /></td></tr>\n\
		    <tr><td class=\"right\">Password <input type=\"password\" id=\"ajaxLoginPassword\" name=\"ajaxLoginUser\" size=\"7\" /></td></tr>\n\
		    <tr><td style=\"padding-left: 10px;\"><button id=\"ajaxLoginButton\">Login</button> <a href=\"./?p=Register\">Register</a></td></tr>\n\
			</table>\n\
		    </div>\n"); // Set new text in login box
		   
					$("#ajaxLoginButton").click(function () {
						loginHandler();
					});
		   
				} else if (json.status === "error") {
					console.log("Logout error: "+json.errormsg);
				} else if (json.status === "failed") {
					console.log("Logout failed");
				}
			}
		});
	}
});