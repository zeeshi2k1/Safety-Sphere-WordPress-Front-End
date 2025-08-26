// Store references to each signature pad
let dceSignaturePads = [];

function initializeSignaturePad(wrapper, $scope, $) {
  const canvas = wrapper.querySelector("canvas");
  const hiddenInput = wrapper.querySelector("input");
  const clearButton = wrapper.querySelector("[data-action=clear]");
  const aspectRatio = parseFloat(canvas.getAttribute("data-aspect-ratio")) || 2;
  const useJPEG = canvas.getAttribute("data-jpeg") === "yes";

  // The width of a canvas, and the CSS style `width` of a canvas,
  // are two different things. If they diverge, the pen will write at an offset.
  // The following solves the issue by matching the "internal" size (canvas.width)
  // to the displayed width in the DOM:
  let rect = wrapper.getBoundingClientRect();
  let actualWidth = Math.round(rect.width);
  let actualHeight = Math.round(actualWidth / aspectRatio);

  // Set the canvas's physical (internal) size => no blur, no offset
  canvas.width = actualWidth;
  canvas.height = actualHeight;

  let signaturePad = new SignaturePad(canvas, {
    penColor: canvas.getAttribute("data-pen-color"),
    backgroundColor: canvas.getAttribute("data-background-color"),
  });

  // Function to store the signature data in the hidden input
  const updateValue = () => {
    if (!signaturePad.isEmpty()) {
      hiddenInput.value = signaturePad.toDataURL(
        `image/${useJPEG ? 'jpeg' : 'png'}`
      );
    } else {
      hiddenInput.value = "";
    }
  };

  // Update the hidden input whenever a stroke is finished
  signaturePad.addEventListener("afterUpdateStroke", updateValue);

  // If the responsive width is larger than default, signaturePad.clear()
  // only partially clears the canvas. So do it manually:
  clearButton.addEventListener("click", function () {
    const context = canvas.getContext("2d");
    context.fillStyle = canvas.getAttribute("data-background-color");
    context.fillRect(0, 0, canvas.width, canvas.height);
    signaturePad.clear();
    hiddenInput.value = "";
  });

  // Store references for potential resize handling
  dceSignaturePads.push({
    wrapper,
    canvas,
    signaturePad,
    hiddenInput,
    aspectRatio,
    useJPEG
  });
}

/**
 * On window resize, save the signature data, resize the canvas, and restore it.
 * This prevents losing the signature on viewport change.
 */
function onWindowResize() {
	// Filter out signature objects whose wrapper is no longer in the DOM
	dceSignaturePads = dceSignaturePads.filter(obj => document.body.contains(obj.wrapper));

  dceSignaturePads.forEach((obj) => {
    let { wrapper, canvas, signaturePad, hiddenInput, aspectRatio, useJPEG } = obj;

    // Check if the signature field is currently visible
    // Skip resize if element is hidden (e.g., in inactive form steps)
    let rect = wrapper.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) {
      // Element is hidden, skip resize to prevent 0x0 canvas
      return;
    }

    // Save the existing strokes as vector data
    const oldData = signaturePad.toData();
    const oldWidth = canvas.width;
    const oldHeight = canvas.height;

    // Calculate new size
    let newWidth = Math.round(rect.width);
    let newHeight = Math.round(newWidth / aspectRatio);

    // If there's no change in width, do nothing
    if (newWidth === oldWidth) {
		return;
	}

    // Reassign the internal size
    canvas.width = newWidth;
    canvas.height = newHeight;

    // Re-initialize SignaturePad
    signaturePad.off(); // remove old event listeners
    signaturePad = new SignaturePad(canvas, {
      penColor: canvas.getAttribute("data-pen-color"),
      backgroundColor: canvas.getAttribute("data-background-color"),
    });
    obj.signaturePad = signaturePad;

    // Scale the stored points to match the new size
    const scaleX = newWidth / oldWidth;
    const scaleY = newHeight / oldHeight;
    oldData.forEach((stroke) => {
      stroke.points.forEach((point) => {
        point.x *= scaleX;
        point.y *= scaleY;
        // keep line thickness identical
        point.pressure *= scaleX;
      });
    });

    // Restore the strokes
    signaturePad.fromData(oldData);

    // Re-bind the hidden input logic
    const updateValue = () => {
      if (!signaturePad.isEmpty()) {
        hiddenInput.value = signaturePad.toDataURL(
          `image/${useJPEG ? 'jpeg' : 'png'}`
        );
      } else {
        hiddenInput.value = "";
      }
    };
	
    signaturePad.addEventListener("afterUpdateStroke", updateValue);
  });
}

function WidgetElements_FormSignature($scope, $) {
	dceSignaturePads = [];

  	let wrappers = $scope.find(".dce-signature-wrapper");
  	wrappers.each((_, wrapper) => initializeSignaturePad(wrapper, $scope, $));
}

jQuery(window).on("elementor/frontend/init", function () {
  elementorFrontend.hooks.addAction(
    "frontend/element_ready/form.default",
    WidgetElements_FormSignature
  );
});

let resizeTimeout;
window.addEventListener("resize", function () {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(onWindowResize, 250);
});
