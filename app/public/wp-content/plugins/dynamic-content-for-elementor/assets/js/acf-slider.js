(function ($) {
	var WidgetElements_ACFSliderHandler = function ($scope, $) {
		let elementSettings = dceGetElementSettings($scope);
		let swiper_class = dceIsSwiperLatest()
			? ".swiper"
			: ".swiper-container";
		let elementSwiper = $scope.find(swiper_class);
		let id_scope = $scope.attr("data-id");
		let id_post = $scope.closest(".elementor").attr("data-post-id");
		let interleaveOffset = -0.5;
		let interleaveEffect = {
			onProgress: function (swiper, progress) {
				for (var i = 0; i < swiper.slides.length; i++) {
					var slide = swiper.slides[i];
					var translate, innerTranslate;
					progress = slide.progress;

					if (progress > 0) {
						translate = progress * swiper.width;
						innerTranslate = translate * interleaveOffset;
					} else {
						innerTranslate =
							Math.abs(progress * swiper.width) *
							interleaveOffset;
						translate = 0;
					}

					$(slide).css({
						transform: "translate3d(" + translate + "px,0,0)",
					});

					$(slide)
						.find(".slide-inner")
						.css({
							transform:
								"translate3d(" + innerTranslate + "px,0,0)",
						});
				}
			},

			onTouchStart: function (swiper) {
				for (var i = 0; i < swiper.slides.length; i++) {
					$(swiper.slides[i]).css({ transition: "" });
				}
			},

			onSetTransition: function (swiper, speed) {
				for (var i = 0; i < swiper.slides.length; i++) {
					$(swiper.slides[i])
						.find(".slide-inner")
						.andSelf()
						.css({ transition: speed + "ms" });
				}
			},
		};

		let swpEffect = elementSettings.effects || "slide";
		let centeredSlides = Boolean(elementSettings.centeredSlides);

		var swiperOptions = {
			direction: "horizontal",
			speed: Number(elementSettings.speedSlide) || 300,
			autoHeight: Boolean(elementSettings.autoHeight),
			roundLengths: Boolean(elementSettings.roundLengths),
			nested: Boolean(elementSettings.nested),
			grabCursor: Boolean(elementSettings.grabCursor),
			watchSlidesProgress: Boolean(elementSettings.watchSlidesProgress),
			watchSlidesVisibility: Boolean(
				elementSettings.watchSlidesVisibility,
			),
			freeMode: Boolean(elementSettings.freeMode),
			freeModeMomentum: Boolean(elementSettings.freeModeMomentum),
			freeModeMomentumRatio:
				Number(elementSettings.freeModeMomentumRatio) || 1,
			freeModeMomentumVelocityRatio:
				Number(elementSettings.freeModeMomentumVelocityRatio) || 1,
			freeModeMomentumBounce: Boolean(
				elementSettings.freeModeMomentumBounce,
			),
			freeModeSticky: Boolean(elementSettings.freeModeSticky),
			effect: swpEffect,
			fadeEffect: {
				crossFade: true,
			},
			centerInsufficientSlides: true,
			watchOverflow: true,
			centeredSlides: centeredSlides,
			spaceBetween: Number(elementSettings.spaceBetween.size) || 0,
			slidesPerView: Number(elementSettings.slidesPerView) || "auto",
			slidesPerGroup: Number(elementSettings.slidesPerGroup) || 1,
			keyboard: Boolean(elementSettings.keyboardControl),
			mousewheel: Boolean(elementSettings.mousewheelControl),
			pagination: {
				el: id_post
					? ".dce-elementor-post-" +
						id_post +
						" .elementor-element-" +
						id_scope +
						" .pagination-" +
						id_scope
					: ".pagination-" + id_scope, //'.swiper-pagination', //'.pagination-acfslider-'+id_scope,
				clickable: true,
				type: String(elementSettings.pagination_type) || "bullets",
				dynamicBullets: true,
				renderFraction: function (currentClass, totalClass) {
					return (
						'<span class="' +
						currentClass +
						'"></span>' +
						'<span class="separator">' +
						String(elementSettings.fraction_separator) +
						"</span>" +
						'<span class="' +
						totalClass +
						'"></span>'
					);
				},
			},
			// *********************************************************************************************
			// Navigation arrows
			spaceBetween: Number(elementSettings.slidesPerView) || 0,
			navigation: {
				nextEl: $scope.find(".swiper-button-next")[0],
				prevEl: $scope.find(".swiper-button-prev")[0],
			},
			// And if we need scrollbar
			scrollbar: {
				el: ".swiper-scrollbar",
			},
		};
		if (dceIsSwiperLatest()) {
			swiperOptions = $.extend(swiperOptions, {
				grid: {
					rows: Number(elementSettings.slidesColumn),
					fill: "row",
				},
			});
		} else {
			swiperOptions = $.extend(swiperOptions, {
				slidesPerColumn: Number(elementSettings.slidesColumn) || 1,
				slidesPerColumnFill: "row",
			});
		}

		if (1 == elementSettings.slidesColumn) {
			swiperOptions = $.extend(swiperOptions, {
				loop: Boolean(elementSettings.loop),
			});
		}

		if (elementSettings.useAutoplay) {
			//default
			swiperOptions = $.extend(swiperOptions, { autoplay: true });
			var autoplayDelay = Number(elementSettings.autoplay) || 3000;
			swiperOptions = $.extend(swiperOptions, {
				autoplay: {
					delay: autoplayDelay,
					disableOnInteraction: Boolean(
						elementSettings.autoplayDisableOnInteraction,
					),
					stopOnLastSlide: Boolean(
						elementSettings.autoplayStopOnLast,
					),
				},
			});
		}

		// Responsive Params
		swiperOptions.breakpoints = dynamicooo.makeSwiperBreakpoints(
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
					default_value: 0,
				},
				slidesPerColumn: {
					elementor_key: "slidesColumn",
					default_value: 1,
				},
			},
			elementSettings,
		);

		if (elementSettings.effects == "custom1") {
			swiperOptions = $.extend(swiperOptions, interleaveEffect);
		}

		if ($scope.find(".swiper-slide").length > 1) {
			const asyncSwiper = elementorFrontend.utils.swiper;

			new asyncSwiper(elementSwiper, swiperOptions)
				.then((newSwiperInstance) => {
					dce_swiper = newSwiperInstance;
				})
				.catch((error) => console.log(error));
		}
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dyncontel-acfslider.default",
			WidgetElements_ACFSliderHandler,
		);
	});
})(jQuery);
