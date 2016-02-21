
window.skizleby = window.skizleby || {};

jQuery(document).ready(function($){
	var emAdmin = "admin@skizleby.cz",
		emInfo = "info@skizleby.cz",
		mailtoAdmin = document.getElementById("mailtoAdmin");
		mailtoInfo = document.getElementById("mailtoInfo");
	mailtoAdmin.href = "mailto:" + emAdmin;
	mailtoAdmin.innerHTML = emAdmin;
	mailtoInfo.href = "mailto:" + emInfo;
	mailtoInfo.innerHTML = emInfo;
});
