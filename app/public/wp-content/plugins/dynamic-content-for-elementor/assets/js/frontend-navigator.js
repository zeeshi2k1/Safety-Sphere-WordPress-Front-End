jQuery(function () {
	jQuery(window).on("load", function () {
		if (jQuery("#elementor-navigator").length) {
			// CLIPBOARD JS
			var copy_btn =
				"#elementor-navigator .elementor-navigator__element__infobox__copy, #elementor-navigator .elementor-navigator__element__infobox__copy_mini";
			new ClipboardJS(copy_btn);
			jQuery(copy_btn).on("click", function (e) {
				jQuery(copy_btn).addClass("animated").addClass("tada");
				setTimeout(function () {
					jQuery(copy_btn)
						.removeClass("animated")
						.removeClass("tada");
				}, 3000);
			});

			// TIPSY
			jQuery("#elementor-navigator .tooltip-target").tipsy({
				// `n` for down, `s` for up
				gravity: "s",
				title: function title() {
					return this.getAttribute("data-tooltip");
				},
			});

			jQuery("#elementor-navigator").fadeIn();
		}
	});

	jQuery("#wp-admin-bar-dce-frontend-navigator > .ab-item").on(
		"click",
		function () {
			if (jQuery("#elementor-navigator").length) {
				if (jQuery("#elementor-navigator").is(":visible")) {
					jQuery("#elementor-navigator__close").trigger("click");
				} else {
					jQuery("#elementor-navigator").toggle();
				}
				return false;
			}
		},
	);

	jQuery("#elementor-navigator__close").on("click", function () {
		if (
			jQuery("body").hasClass("admin-bar") &&
			jQuery("#wp-admin-bar-dce-frontend-navigator .ab-item").length
		) {
			jQuery("#elementor-navigator").hide();
		} else {
			jQuery("#elementor-navigator").toggleClass("elementor-closed");
			jQuery(this)
				.toggleClass("eicon-close")
				.toggleClass("eicon-preview-medium");
		}
		jQuery(
			"#elementor-navigator .elementor-navigator__element__infobox",
		).hide();
		jQuery("#elementor-navigator .elementor-editing").removeClass(
			"elementor-editing",
		);
		jQuery(".debug-bar-dce-highlight").removeClass(
			"debug-bar-dce-highlight",
		);
		return false;
	});

	jQuery("#elementor-navigator .elementor-navigator__close").on(
		"click",
		function () {
			jQuery(this)
				.closest(".elementor-navigator__element__infobox")
				.hide();
			jQuery(".debug-bar-dce-highlight").removeClass(
				"debug-bar-dce-highlight",
			);
			jQuery(
				"#elementor-navigator .elementor-navigator__item.elementor-editing",
			).removeClass("elementor-editing");
			return false;
		},
	);

	jQuery("#elementor-navigator__toggle-all").on("click", function () {
		jQuery(
			"#elementor-navigator .elementor-navigator__element__infobox",
		).hide();
		if (
			jQuery("#elementor-navigator .elementor-navigator__item + ul")
				.first()
				.is(":visible")
		) {
			jQuery(
				"#elementor-navigator .elementor-navigator__item + ul",
			).hide();
			jQuery(
				"#elementor-navigator .elementor-navigator__item.elementor-active",
			).removeClass("elementor-active");
		} else {
			jQuery(
				"#elementor-navigator .elementor-navigator__item + ul",
			).show();
			jQuery("#elementor-navigator .elementor-navigator__item").addClass(
				"elementor-active",
			);
		}
		return false;
	});

	jQuery("#elementor-navigator").on("mouseleave", function () {
		if (
			!jQuery(
				"#elementor-navigator .elementor-navigator__item.elementor-editing",
			).length
		) {
			jQuery(".debug-bar-dce-highlight").removeClass(
				"debug-bar-dce-highlight",
			);
		}
	});
	jQuery("#elementor-navigator .elementor-navigator__item").on(
		"hover",
		function () {
			if (
				!jQuery(
					"#elementor-navigator .elementor-navigator__item.elementor-editing",
				).length
			) {
				jQuery(".debug-bar-dce-highlight").removeClass(
					"debug-bar-dce-highlight",
				);
				jQuery(jQuery(this).data("target")).toggleClass(
					"debug-bar-dce-highlight",
				);
			}
		},
	);
	jQuery(
		"#elementor-navigator .elementor-navigator__element__title, #elementor-navigator .elementor-navigator__element__element-type",
	).on("click", function () {
		// hilight row
		if (
			!jQuery(this)
				.closest(".elementor-navigator__item")
				.hasClass("elementor-editing")
		) {
			jQuery("#elementor-navigator .elementor-editing").removeClass(
				"elementor-editing",
			);
		}
		jQuery(this)
			.closest(".elementor-navigator__item")
			.toggleClass("elementor-editing");

		// display element in page
		if (
			jQuery(this)
				.closest(".elementor-navigator__item")
				.find(".elementor-navigator__element__toggle .fa")
				.hasClass("fa-eye-slash")
		) {
			jQuery(this)
				.closest(".elementor-navigator__item")
				.find(".elementor-navigator__element__toggle")
				.trigger("click");
		}
		if (jQuery(jQuery(this).parent().data("target")).length) {
			if (jQuery(jQuery(this).parent().data("target")).offset().top) {
				jQuery("html, body").animate(
					{
						scrollTop:
							jQuery(
								jQuery(this).parent().data("target"),
							).offset().top - jQuery("#wpadminbar").height(),
					},
					1000,
				);
			}
		}

		// hilight element in page
		if (
			!jQuery(jQuery(this).parent().data("target")).hasClass(
				"debug-bar-dce-highlight",
			)
		) {
			jQuery(".debug-bar-dce-highlight").removeClass(
				"debug-bar-dce-highlight",
			);
		}
		if (
			jQuery(this)
				.closest(".elementor-navigator__item")
				.hasClass("elementor-editing")
		) {
			jQuery(
				jQuery(this)
					.closest(".elementor-navigator__item")
					.data("target"),
			).addClass("debug-bar-dce-highlight");
		} else {
			jQuery(
				jQuery(this)
					.closest(".elementor-navigator__item")
					.data("target"),
			).removeClass("debug-bar-dce-highlight");
		}

		// open element info box
		jQuery(this)
			.closest(".elementor-navigator__item")
			.find(".elementor-navigator__element__info")
			.trigger("click");

		return false;
	});

	jQuery(
		"#elementor-navigator .elementor-navigator__element__list-toggle",
	).on("click", function () {
		jQuery(
			"#elementor-navigator .elementor-navigator__element__infobox",
		).hide();
		jQuery(this).parent().toggleClass("elementor-active");
		jQuery(this)
			.parent()
			.siblings(".elementor-navigator__elements")
			.toggle();
		return false;
	});
	jQuery("#elementor-navigator .elementor-navigator__element__toggle").on(
		"click",
		function () {
			jQuery(
				jQuery(this)
					.closest(".elementor-navigator__item")
					.data("target"),
			).toggleClass("dce-visibility-element-hidden");
			var navigator__element = jQuery(this).closest(
				".elementor-navigator__element",
			);
			navigator__element.toggleClass(
				"elementor-navigator__element--hidden",
			);
			navigator__element
				.children(".elementor-navigator__item")
				.find(".fa")
				.toggleClass("fa-eye")
				.toggleClass("fa-eye-slash");
			navigator__element
				.children(".elementor-navigator__element__infobox")
				.find(".fa")
				.toggleClass("fa-eye")
				.toggleClass("fa-eye-slash");
			return false;
		},
	);
	jQuery(
		"#elementor-navigator .elementor-navigator__element__infobox__toggle",
	).on("click", function () {
		jQuery(this)
			.closest(".elementor-navigator__element")
			.find(".elementor-navigator__element__toggle")
			.trigger("click");
		return false;
	});
	jQuery("#elementor-navigator .elementor-navigator__element__info").on(
		"click",
		function () {
			if (
				!jQuery(this)
					.closest(".elementor-navigator__element")
					.children(".elementor-navigator__element__infobox:visible")
					.length
			) {
				jQuery(
					"#elementor-navigator .elementor-navigator__element__infobox",
				).hide();
			}
			jQuery(this)
				.closest(".elementor-navigator__element")
				.children(".elementor-navigator__element__infobox")
				.toggle();
			return false;
		},
	);
	jQuery(document).on("click", ".debug-bar-dce-highlight", function () {
		jQuery(this).removeClass("debug-bar-dce-highlight");
		return false;
	});
});
