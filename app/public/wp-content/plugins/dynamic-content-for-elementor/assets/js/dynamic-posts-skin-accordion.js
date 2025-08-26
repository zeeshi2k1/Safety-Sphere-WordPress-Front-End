var dceDynamicPostsAccordionHandler = function ($scope, $) {
	let elementSettings = dceGetElementSettings($scope);
	let wrapper = $scope.find("ul.dce-posts-wrapper");
	let accordionStart = elementSettings.accordion_start;
	let activeIndex;

	if (accordionStart === "none") {
		activeIndex = false;
	} else if (accordionStart === "first") {
		activeIndex = 1;
	} else if (accordionStart === "custom") {
		activeIndex = elementSettings.accordion_start_custom || 1;
	} else {
		let elements = $scope.find("ul.dce-posts-wrapper .dce-post").length;
		activeIndex = [];
		for (let i = 0; i <= elements; i++) {
			activeIndex[i] = i;
		}
	}

	// AccordionJS
	let accordionJs = function (
		wrapper,
		closeOtherSections,
		speed,
		activeIndex,
	) {
		wrapper.accordionjs({
			// Allow self close.(data-close-able)
			closeAble: true,

			// Close other sections.(data-close-other)
			closeOther: Boolean(closeOtherSections),

			// Animation Speed.(data-slide-speed)
			slideSpeed: speed,

			// The section open on first init. A number from 1 to X or false.(data-active-index)
			activeIndex: activeIndex,
			
			openSection: function(section) {
				$(section).find('.acc_button').attr('aria-expanded', 'true');
				$(section).find('.acc_content').attr('aria-hidden', 'false');
			},
			
			beforeOpenSection: function(section) {
				if (Boolean(closeOtherSections)) {
					wrapper.find('.acc_button').attr('aria-expanded', 'false');
					wrapper.find('.acc_content').attr('aria-hidden', 'true');
				}
			},
			
			closeSection: function(section) {
				$(section).find('.acc_button').attr('aria-expanded', 'false');
				$(section).find('.acc_content').attr('aria-hidden', 'true');
			}
		});
	};
	accordionJs(
		wrapper,
		elementSettings.accordion_close_other_sections,
		elementSettings.accordion_speed.size,
		activeIndex,
	);
};

jQuery(window).on("elementor/frontend/init", function () {
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-dynamicposts-v2.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-dynamic-archives.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-woo-products-cart.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-woo-products-cart-on-sale.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-woo-product-upsells.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-woo-product-crosssells.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-dynamic-woo-products.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-dynamic-show-favorites.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-my-posts.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-sticky-posts.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-search-results.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-metabox-relationship.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-acf-relationship.accordion",
		dceDynamicPostsAccordionHandler,
	);
	elementorFrontend.hooks.addAction(
		"frontend/element_ready/dce-woo-products-variations.accordion",
		dceDynamicPostsAccordionHandler,
	);
});
