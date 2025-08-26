const maybeUploadFileToImageURL = (value) => {
	if (typeof value !== "object") {
		return value;
	}
	if (value.constructor.name !== "File") {
		return value;
	}
	let isImage = value.type.startsWith("image");
	return {
		url: URL.createObjectURL(value),
		name: value.name,
		is_image: isImage,
	};
};

// Map file uploads to url blobs. So that if they are images they can be used
// inside img tags.
const allUploadFilesToImageURLs = (value) => {
	if (!Array.isArray(value)) {
		return maybeUploadFileToImageURL(value);
	}
	return value.map((v) => maybeUploadFileToImageURL(v));
};

// Generate an object with as key the field names of the form and as value their
// current value.
const getFields = (form) => {
	let data = new FormData(form);
	let out = {};
	for (let [k, v] of data.entries()) {
		let matches = k.match(/^form_fields\[([^\]]+)\](.*)/);
		if (matches) {
			let value;
			// if field key has [] at the end. So it can contain multiple choices:
			if (matches[2] === "[]") {
				value = data.getAll(k);
			} else {
				value = data.get(k);
			}
			value = allUploadFilesToImageURLs(value);
			let fieldName = matches[1];
			out[fieldName] = value;
		}
	}
	return out;
};

function renderLiveHTML(form, code) {
	let formData = getFields(form);
	return Mustache.render(code, { form: formData });
}

window.renderLiveHTML = renderLiveHTML;

function initializeLiveHtml(wrapper, widget) {
	let div = wrapper.getElementsByTagName("div")[0];
	let form = widget.getElementsByTagName("form")[0];
	let code = div.dataset.code;
	let realTime = div.dataset.realTime === "yes";
	let onChange = () => {
		div.innerHTML = renderLiveHTML(form, code);
	};
	onChange();
	form.addEventListener(realTime ? "input" : "change", onChange);
}

function initializeAllLiveHtmlFields($scope) {
	$scope
		.find(".elementor-field-type-dce_live_html")
		.each((_, w) => initializeLiveHtml(w, $scope[0]));
}

jQuery(window).on("elementor/frontend/init", function () {
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/form.default",
		initializeAllLiveHtmlFields,
	);
});
