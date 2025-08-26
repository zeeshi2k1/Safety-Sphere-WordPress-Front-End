"use strict";

jQuery(function () {
	jQuery(".js-dce-select").select2();
});

// Checkbox for all features on Dashboard
jQuery(document).on("click", "#dce-feature-activate-all", function (event) {
	jQuery(this)
		.closest(".dce-container")
		.find("input.dce-checkbox")
		.prop("checked", true);
	event.preventDefault();
	this.closest(".dce-container").scrollIntoView({
		behavior: "smooth",
	});
});
jQuery(document).on("click", "#dce-feature-deactivate-all", function (event) {
	jQuery(this)
		.closest(".dce-container")
		.find("input.dce-checkbox")
		.prop("checked", false);
	event.preventDefault();
	this.closest(".dce-container").scrollIntoView({
		behavior: "smooth",
	});
});

// Checkbox for groups on Dashboard
jQuery(document).on("click", ".dce-group-activate-all", function (event) {
	jQuery(this)
		.closest(".dce-feature-group")
		.find("input.dce-checkbox")
		.prop("checked", true);
	event.preventDefault();
	this.closest(".dce-feature-group").scrollIntoView({
		behavior: "smooth",
	});
});
jQuery(document).on("click", ".dce-group-deactivate-all", function (event) {
	jQuery(this)
		.closest(".dce-feature-group")
		.find("input.dce-checkbox")
		.prop("checked", false);
	event.preventDefault();
	this.closest(".dce-feature-group").scrollIntoView({
		behavior: "smooth",
	});
} );
