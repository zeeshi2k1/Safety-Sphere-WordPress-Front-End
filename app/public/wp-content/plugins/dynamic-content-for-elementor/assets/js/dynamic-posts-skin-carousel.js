(function () {
	var widgetHandler = function ($scope, $) {
		const fallback = $scope.find("> div > div > .dce-posts-fallback");
		if (fallback.length !== 0) {
			// no posts where found, nothing to do.
			return;
		}

		var elementSettings = dceGetElementSettings($scope);
		var id_scope = $scope.attr("data-id");
		var id_post = $scope.attr("data-post-id");
		var elementSwiper = $scope.find(
			".dce-posts-container.dce-skin-carousel",
		);
		let mainSwiper = null;
		var isCarouselEnabled = false;
		var centeredSlides = Boolean(
			elementSettings[dceDynamicPostsSkinPrefix + "centeredSlides"],
		);
		var centerInsufficientSlides = elementSettings[dceDynamicPostsSkinPrefix + "centerInsufficientSlides"] === 'yes';
		
		var infiniteLoop = Boolean(
			elementSettings[dceDynamicPostsSkinPrefix + "loop"],
		);
		var slideInitNum = 0;
		var slidesPerView = Number(
			elementSettings[dceDynamicPostsSkinPrefix + "slidesPerView"],
		);

		if (elementSettings.carousel_match_height) {
			if (elementSettings.style_items === "template") {
				if ($scope.find(".e-con")) {
					// select all top level containers:
					$scope
						.find(".e-con")
						.not($scope.find(".e-con .e-con"))
						.matchHeight();
				} else if (
					$scope.find(".dce-post-block .elementor-inner-section")
						.length
				) {
					$scope
						.find(".dce-post-block")
						.first()
						.find(".elementor-inner-section")
						.each((i) => {
							let $els = $scope
								.find(".dce-post-block")
								.map((_, $e) => {
									return jQuery($e).find(
										".elementor-inner-section",
									)[i];
								});
							$els.matchHeight();
						});
				} else {
					selector = ".dce-post-block .elementor-top-section";
					$scope.find(selector).matchHeight();
				}
			} else {
				selector = ".dce-post-block";
				$scope.find(selector).matchHeight();
			}
		}

		let nextElement = $scope.find(".swiper-button-right")[0];
		let prevElement = $scope.find(".swiper-button-left")[0];
		if (Boolean(elementSettings["rtl"])) {
			let temp = nextElement;
			nextElement = prevElement;
			prevElement = temp;
		}

		var mainSwiperOptions = {
			observer: true,
			observeParents: true,
			direction:
				String(
					elementSettings[
						dceDynamicPostsSkinPrefix + "direction_slider"
					],
				) || "horizontal", //vertical
			initialSlide: slideInitNum,
			reverseDirection: Boolean(elementSettings["rtl"]),
			speed:
				Number(
					elementSettings[dceDynamicPostsSkinPrefix + "speed_slider"],
				) || 300,
			autoHeight: Boolean(
				elementSettings[dceDynamicPostsSkinPrefix + "autoHeight"],
			), // Set to true and slider wrapper will adopt its height to the height of the currently active slide
			effect:
				elementSettings[dceDynamicPostsSkinPrefix + "effects"] ||
				"slide",
			cubeEffect: {
				shadow: Boolean(
					elementSettings[dceDynamicPostsSkinPrefix + "cube_shadow"],
				),
				slideShadows: Boolean(
					elementSettings[dceDynamicPostsSkinPrefix + "slideShadows"],
				),
				shadowOffset: 20,
				shadowScale: 0.94,
			},
			coverflowEffect: {
				rotate: 50,
				stretch:
					Number(
						elementSettings[
							dceDynamicPostsSkinPrefix + "coverflow_stretch"
						],
					) || 0,
				depth: 100,
				modifier:
					Number(
						elementSettings[
							dceDynamicPostsSkinPrefix + "coverflow_modifier"
						],
					) || 1,
				slideShadows: Boolean(
					elementSettings[dceDynamicPostsSkinPrefix + "slideShadows"],
				),
			},
			flipEffect: {
				rotate: 30,
				slideShadows: Boolean(
					elementSettings[dceDynamicPostsSkinPrefix + "slideShadows"],
				),
				limitRotation: true,
			},
			fadeEffect: {
				crossFade: true,
			},
			initialSlide:
				Number(
					elementSettings[dceDynamicPostsSkinPrefix + "initialSlide"],
				) || 0,
			slidesPerView: slidesPerView || "auto",
			slidesPerGroup:
				Number(
					elementSettings[
						dceDynamicPostsSkinPrefix + "slidesPerGroup"
					],
				) || 1, // Set numbers of slides to define and enable group sliding. Useful to use with slidesPerView > 1
			slidesPerColumn:
				Number(
					elementSettings[dceDynamicPostsSkinPrefix + "slidesColumn"],
				) || 1, // Number of slides per column, for multirow layout
			spaceBetween:
				Number(
					elementSettings[dceDynamicPostsSkinPrefix + "spaceBetween"],
				) || 0,
			slidesOffsetBefore:
				Number(
					elementSettings[
						dceDynamicPostsSkinPrefix + "slidesOffsetBefore"
					],
				) || 0, // Add (in px) additional slide offset in the beginning of the container (before all slides)
			slidesOffsetAfter:
				Number(
					elementSettings[
						dceDynamicPostsSkinPrefix + "slidesOffsetAfter"
					],
				) || 0, // Add (in px) additional slide offset in the end of the container (after all slides)
			slidesPerColumnFill:
				String(
					elementSettings[
						dceDynamicPostsSkinPrefix + "slidesPerColumnFill"
					],
				) || "row", // Could be 'column' or 'row'. Defines how slides should fill rows, by column or by row
			centerInsufficientSlides: centerInsufficientSlides,
			centeredSlides: centeredSlides,
			centeredSlidesBounds: Boolean(
				elementSettings[
					dceDynamicPostsSkinPrefix + "centeredSlidesBounds"
				],
			),
			grabCursor: Boolean(
				elementSettings[dceDynamicPostsSkinPrefix + "grabCursor"],
			),
			freeMode: Boolean(
				elementSettings[dceDynamicPostsSkinPrefix + "freeMode"],
			),
			freeModeMomentum: Boolean(
				elementSettings[dceDynamicPostsSkinPrefix + "freeModeMomentum"],
			),
			freeModeMomentumRatio:
				Number(
					elementSettings[
						dceDynamicPostsSkinPrefix + "freeModeMomentumRatio"
					],
				) || 1,
			freeModeMomentumVelocityRatio:
				Number(
					elementSettings[
						dceDynamicPostsSkinPrefix +
							"freeModeMomentumVelocityRatio"
					],
				) || 1,
			freeModeMomentumBounce: Boolean(
				elementSettings[
					dceDynamicPostsSkinPrefix + "freeModeMomentumBounce"
				],
			),
			freeModeMomentumBounceRatio:
				Number(elementSettings[dceDynamicPostsSkinPrefix + "speed"]) ||
				1,
			freeModeMinimumVelocity:
				Number(elementSettings[dceDynamicPostsSkinPrefix + "speed"]) ||
				0.02,
			freeModeSticky: Boolean(
				elementSettings[dceDynamicPostsSkinPrefix + "freeModeSticky"],
			),
			loop: infiniteLoop,
			navigation: {
				nextEl: nextElement,
				prevEl: prevElement,
			},
			pagination: {
				el: id_post
					? ".elementor-element-" +
						id_scope +
						'[data-post-id="' +
						id_post +
						'"] .pagination-' +
						id_scope
					: ".pagination-" + id_scope,
				clickable: true,
				type:
					String(
						elementSettings[
							dceDynamicPostsSkinPrefix + "pagination_type"
						],
					) || "bullets",
				dynamicBullets: Boolean(
					elementSettings[
						dceDynamicPostsSkinPrefix + "dynamicBullets"
					],
				),
				renderBullet: function (index, className) {
					var indexLabel =
						!Boolean(
							elementSettings[
								dceDynamicPostsSkinPrefix + "dynamicBullets"
							],
						) &&
						Boolean(
							elementSettings[
								dceDynamicPostsSkinPrefix + "bullets_numbers"
							],
						)
							? '<span class="swiper-pagination-bullet-title">' +
								(index + 1) +
								"</span>"
							: "";
					return (
						'<span class="' +
						className +
						'">' +
						indexLabel +
						"</span>"
					);
				},
				renderFraction: function (currentClass, totalClass) {
					if (!Boolean(elementSettings["rtl"])) {
						return (
							'<span class="' +
							currentClass +
							'"></span>' +
							'<span class="separator">' +
							String(
								elementSettings[
									dceDynamicPostsSkinPrefix +
										"fraction_separator"
								],
							) +
							"</span>" +
							'<span class="' +
							totalClass +
							'"></span>'
						);
					}
					return (
						'<span class="' +
						totalClass +
						'"></span>' +
						'<span class="separator">' +
						String(
							elementSettings[
								dceDynamicPostsSkinPrefix + "fraction_separator"
							],
						) +
						"</span>" +
						'<span class="' +
						currentClass +
						'"></span>'
					);
				},
				renderProgressbar: function (progressbarFillClass) {
					return '<span class="' + progressbarFillClass + '"></span>';
				},
			},
			scrollbar: {
				el: $scope.find(".swiper-scrollbar")[0],
				hide: Boolean(
					elementSettings[
						dceDynamicPostsSkinPrefix + "scrollbar_hide"
					],
				),
				draggable: Boolean(
					elementSettings[
						dceDynamicPostsSkinPrefix + "scrollbar_draggable"
					],
				),
				snapOnRelease: true, // Set to true to snap slider position to slides when you release scrollbar
			},
			mousewheel: elementSettings[
				dceDynamicPostsSkinPrefix + "mousewheelControl"
			]
				? {
						releaseOnEdges: true,
					}
				: false,
			keyboard: {
				enabled: Boolean(
					elementSettings[
						dceDynamicPostsSkinPrefix + "keyboardControl"
					],
				),
			},
		};

		if (elementSettings[dceDynamicPostsSkinPrefix + "useAutoplay"]) {
			mainSwiperOptions = $.extend(mainSwiperOptions, {
				autoplay: {
					delay:
						Number(
							elementSettings[
								dceDynamicPostsSkinPrefix + "autoplay"
							],
						) || 3000,
					disableOnInteraction: Boolean(
						elementSettings[
							dceDynamicPostsSkinPrefix +
								"autoplayDisableOnInteraction"
						],
					),
					stopOnLastSlide: Boolean(
						elementSettings[
							dceDynamicPostsSkinPrefix + "autoplayStopOnLast"
						],
					),
				},
			});
		}

		mainSwiperOptions.breakpoints = dynamicooo.makeSwiperBreakpoints(
			{
				slidesPerView: {
					elementor_key: "slidesPerView",
					default_value: "auto",
				},
				slidesPerGroup: {
					elementor_key: "slidesPerGroup",
					default_value: 1,
				},
				spaceBetween: {
					elementor_key: "spaceBetween",
					default_value: 1,
				},
				slidesPerColumn: {
					elementor_key: "slidesColumn",
					default_value: 1,
				},
				slidesOffsetBefore: {
					elementor_key: "slidesOffsetBefore",
					default_value: 0,
				},
				slidesOffsetAfter: {
					elementor_key: "slidesOffsetAfter",
					default_value: 0,
				},
			},
			elementSettings,
			dceDynamicPostsSkinPrefix,
		);

		if ("dualcarousel" === dceDynamicPostsSkin) {
			let dualCarouselSlidesPerView = Number(
				elementSettings[
					dceDynamicPostsSkinPrefix + "thumbnails_slidesPerView"
				],
			);

			let dualCarouselSwiperOptions = {
				spaceBetween:
					Number(
						elementSettings[
							dceDynamicPostsSkinPrefix + "dualcarousel_gap"
						],
					) || 0,
				slidesPerView: dualCarouselSlidesPerView || "auto",
				autoHeight: true,
				watchOverflow: true,
				watchSlidesProgress: true,
				centeredSlides: Boolean(elementSettings[dceDynamicPostsSkinPrefix + "thumbnails_centered"]),
				loop: Boolean(elementSettings[dceDynamicPostsSkinPrefix + "thumbnails_loop"]),
			};

			dualCarouselSwiperOptions.breakpoints =
				dynamicooo.makeSwiperBreakpoints(
					{
						slidesPerView: {
							elementor_key: "thumbnails_slidesPerView",
							default_value: "auto",
						},
						spaceBetween: {
							elementor_key: "dualcarousel_gap",
							default_value: 0,
						},
					},
					elementSettings,
					dceDynamicPostsSkinPrefix,
				);

			initSwiperThumbs(dualCarouselSwiperOptions);
		}

		function initSwiperThumbs(dualCarouselSwiperOptions) {
			let thumbsContainer = $scope.find(
				".dce-dualcarousel-gallery-thumbs",
			);
			let swiperThumbs;
			mainSwiperOptions.on = mainSwiperOptions.on || {};
			mainSwiperOptions.on.slideChange = function (e) {
				swiperThumbs.slideToLoop(this.realIndex);
			};
			const asyncSwiper = elementorFrontend.utils.swiper;

			new asyncSwiper(thumbsContainer, dualCarouselSwiperOptions)
				.then((newSwiperInstance) => {
					swiperThumbs = newSwiperInstance;
					mainSwiperOptions.thumbs = {
						swiper: swiperThumbs,
					};
					initSwiperCarousel();
				})
				.catch((error) => console.log(error));
		}

		function initSwiperCarousel() {
			const asyncSwiper = elementorFrontend.utils.swiper;
			new asyncSwiper(elementSwiper, mainSwiperOptions)
				.then((newSwiperInstance) => {
					mainSwiper = newSwiperInstance;
				})
				.catch((error) => console.log(error));
		}

		if (elementSwiper.length && "dualcarousel" !== dceDynamicPostsSkin) {
			initSwiperCarousel();
		}

		// Callback function executed when mutations occur
		var handleCarouselClassChange = function (mutationsList, observer) {
			for (var mutation of mutationsList) {
				if (
					mutation.type === "attributes" &&
					mutation.attributeName === "class" &&
					isCarouselEnabled
				) {
					mainSwiper.update();
				}
			}
		};

		dceObserveElement($scope[0], handleCarouselClassChange);
	};

	const addActions = () => {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamicposts-v2.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-products-cart.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-woo-products.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-woo-products-on-sale.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-product-upsells.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-product-crosssells.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-show-favorites.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-my-posts.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-archives.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-sticky-posts.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-search-results.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-products-variations.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamicposts-v2.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-products-cart.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-products-cart-on-sale.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-woo-products.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-products-variations.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-product-upsells.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-woo-product-crosssells.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-show-favorites.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-my-posts.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-dynamic-archives.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-sticky-posts.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-search-results.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-metabox-relationship.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-metabox-relationship.dualcarousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-acf-relationship.carousel",
			widgetHandler,
		);
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dce-acf-relationship.dualcarousel",
			widgetHandler,
		);
	};
	if (
		typeof elementorFrontend === "object" &&
		elementorFrontend.hasOwnProperty("hooks")
	) {
		addActions();
	} else {
		jQuery(window).on("elementor/frontend/init", addActions);
	}
})();
