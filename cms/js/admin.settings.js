/*
 * admin.settings.js
 * For: The admin settings page.
 * 
 */
$(document).ready(function() {
	/*** Functions to run when the page initially loads ***/
	showHideCustomDateFormat();
	showHideGoogleReCAPTCHA();
	showHideSolveMediaCaptcha();
	
	/* Social Buttons */
	showHideGooglePlusURL();
	showHideFacebookButtonURL();
	showHideTwitterFollowButtonURL();
	
	/* Listeners */
	$("#setting_dateFormat").change(function () {
		showHideCustomDateFormat();
	});
	
	$(".setting_googlePlusButton_radio").click(function () {
		showHideGooglePlusURL(); 
	});
	
	$(".setting_facebookButton_radio").click(function () {
		showHideFacebookButtonURL(); 
	});
	
	$(".setting_twitterFollowButton_radio").click(function () {
		showHideTwitterFollowButtonURL(); 
	});
	
	$(".setting_recaptcha_radio").click(function () {
		showHideGoogleReCAPTCHA();
	});
	
	$(".setting_solveMediaCaptcha_radio").click(function () {
		showHideSolveMediaCaptcha();
	});
	
	/* Functions */
	function showHideCustomDateFormat() {
		if ($("#setting_dateFormat").val() === "null") {
			$("#customDateFormatOpts").show();
		} else {
			$("#customDateFormatOpts").hide();
		}
	}
	
	function showHideGooglePlusURL() {
		if ($("#setting_googlePlusButtonYes").is(":checked")) {
			$("#setting_googlePlusButton_opts").show();
		} else {
			$("#setting_googlePlusButton_opts").hide();
		}
	}
	
	function showHideFacebookButtonURL() {
		if ($("#setting_facebookButtonYes").is(":checked")) {
			$("#setting_facebookButton_opts").show();
		} else {
			$("#setting_facebookButton_opts").hide();
		}
	}
	
	function showHideTwitterFollowButtonURL() {
		if ($("#setting_twitterFollowButtonYes").is(":checked")) {
			$("#setting_twitterFollowButton_opts").show();
		} else {
			$("#setting_twitterFollowButton_opts").hide();
		}
	}
	
	function showHideGoogleReCAPTCHA() {
		if ($("#setting_recaptchaYes").is(":checked")) {
			$(".recaptcha_opts").show();
		} else {
			$(".recaptcha_opts").hide();
		}
	}
	
	function showHideSolveMediaCaptcha() {
		if ($("#setting_solveMediaCaptchaYes").is(":checked")) {
			$(".solveMedia_opts").show();
		} else {
			$(".solveMedia_opts").hide();
		}
	}
});