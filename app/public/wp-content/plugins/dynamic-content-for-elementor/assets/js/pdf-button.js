"use strict";

function initializePdfButton($scope) {
	let button = $scope.find("a").first();
	let elementId = button.data("element-id");
	let postId = button.data("post-id");
	let queriedId = button.data("queried-id");
	let converter = button.data("converter");
	let ajaxUrl = button.data("ajax-url");
	let ajaxAction = button.data("ajax-action");
	let title = button.data("title");
	let preview = button.data("preview") === "yes";
	let newTab = button.data("new-tab") === "yes";
	if (converter !== "html") {
		return;
	}
	const fetchPdf = async function () {
		button.addClass("fetching-pdf");
		// // Backend might need current get parameters for setting the item value:
		let data = new FormData();
		data.set("queried_id", queriedId);
		data.set("post_id", postId);
		data.set("element_id", elementId);
		data.set("action", ajaxAction);
		let response;
		try {
			response = await fetch(ajaxUrl, {
				method: "POST",
				body: new URLSearchParams(data),
			});
		} catch (e) {
			console.error("PDF Button: " + e.message);
			return;
		}
		if (response.headers.get("Content-Type") !== "application/pdf") {
			const json = await response.json();
			console.error("PDF Button: " + json.data.message);
			return;
		}
		let blob = await response.blob();
		let link = document.createElement("a");
		link.href = window.URL.createObjectURL(blob);
		if (preview) {
			if (newTab) {
				link.target = "_blank";
			}
		} else {
			link.download = title;
		}
		link.click();
		button.removeClass("fetching-pdf");
	};
	button.on("click", () => {
		fetchPdf();
	});
}

jQuery(window).on("elementor/frontend/init", function () {
	if (elementorFrontend.isEditMode()) {
		return;
	}
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce_pdf_button.default",
		initializePdfButton,
	);
});
