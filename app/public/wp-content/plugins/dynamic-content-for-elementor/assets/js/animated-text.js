(function ($) {
	function mapEasing(easeEquation, easeType) {
		var eqMap = {
			Power1: "Quad",
			Power2: "Cubic",
			Power3: "Quart",
			Power4: "Quint",
			Back: "Back",
			Elastic: "Elastic",
		};
		var typeMap = {
			easeIn: "easeIn",
			easeOut: "easeOut",
			easeInOut: "easeInOut",
		};

		var eq = eqMap[easeEquation] || "Quart";
		var t = typeMap[easeType] || "easeInOut";
		var animeEasing = t + eq;
		return animeEasing;
	}

	function splitText($element, splitType) {
		var text = $element.text() || "";
		var splitted = [];
		$element.empty();

		if (splitType === "lines") {
			var lines = text.split(/\r?\n/);
			lines.forEach(function (line) {
				var $lineWrap = $('<div class="dce-split-line"></div>');
				$lineWrap.text(line);
				$element.append($lineWrap);
				splitted.push($lineWrap[0]);
			});
		} else if (splitType === "words") {
			var words = text.split(/\s+/);
			words.forEach(function (w, index) {
				var $wordWrap = $('<span class="dce-split-word"></span>');
				$wordWrap.text(w + (index < words.length - 1 ? " " : ""));
				$element.append($wordWrap);
				splitted.push($wordWrap[0]);
			});
		} else {
			for (var i = 0; i < text.length; i++) {
				var c = text.charAt(i);
				var $charWrap = $('<span class="dce-split-char"></span>');
				$charWrap.text(c);
				$element.append($charWrap);
				splitted.push($charWrap[0]);
			}
		}
		return splitted;
	}

	function random(min, max) {
		return Math.random() * (max - min) + min;
	}

	var WidgetElements_AnimateTextHandler = function ($scope, $) {
		var elementSettings = dceGetElementSettings($scope);

		var target = $scope.find(".dce-animated-text");
		var splitType = elementSettings.animatetext_splittype;
		var repeaterWords = elementSettings.words;

		var texts = [];
		var ids = [];
		var words;

		if (elementorFrontend.isEditMode()) {
			words = repeaterWords.models;
		} else {
			words = repeaterWords;
		}

		$.each(words, function (index, word) {
			if (elementorFrontend.isEditMode()) {
				word = repeaterWords.models[index].attributes;
			} else {
				word = repeaterWords[index];
			}
			texts.push(word.text_word);
			ids.push(word._id);
		});

		var animTextRepeat = elementSettings.animatetext_repeat;
		var effectIn = elementSettings.animatetext_animationstyle_in;
		var splitOrigin_in = elementSettings.animatetext_splitorigin_in;
		var speed_in = (elementSettings.speed_animation_in.size || 0.7) * 1000;
		var amount_in = elementSettings.amount_speed_in.size || 1;
		var delaySteps_in =
			(elementSettings.delay_animation_in.size || 0) * 1000;
		var anim_easing_in = mapEasing(
			elementSettings.animFrom_easing_ease_in,
			elementSettings.animFrom_easing_in
		);

		var effectOut = elementSettings.animatetext_animationstyle_out;
		var splitOrigin_out = elementSettings.animatetext_splitorigin_out;
		var speed_out =
			(elementSettings.speed_animation_out.size || 0.7) * 1000;
		var amount_out = elementSettings.amount_speed_out.size || 1;
		var delaySteps_out =
			(elementSettings.delay_animation_out.size || 3) * 1000;
		var anim_easing_out = mapEasing(
			elementSettings.animFrom_easing_ease_out,
			elementSettings.animFrom_easing_out
		);

		var oldTextIndex = -1;
		var currentTextIndex = 0;
		var cycle = 1;
		var isLastTextCycle = false;

		function changeText() {
			if (currentTextIndex < texts.length - 1) {
				oldTextIndex = currentTextIndex;
				currentTextIndex++;
				target.removeClass("elementor-repeater-item-" + ids[oldTextIndex]);
				target.html(texts[currentTextIndex]);
				target.addClass("elementor-repeater-item-" + ids[currentTextIndex]);
			} else {
				oldTextIndex = -1;
				currentTextIndex = 0;
				if (animTextRepeat > -1 && cycle >= animTextRepeat) {
					return;
				}
				cycle++;
			}

			if (
				cycle === parseInt(animTextRepeat) &&
				currentTextIndex === texts.length - 1
			) {
				isLastTextCycle = true;
			}

			animSplitText();
		}

		function animSplitText() {
			if (oldTextIndex < 0) {
				target.html(texts[0]);
				target.addClass("elementor-repeater-item-" + ids[0]);
			}

			var splitted = splitText(target, splitType);

			var tl = anime.timeline({
				autoplay: false,
			});

			// base props (common to all effects, then customized in switch)
			var inProps = {
				targets: splitted,
				easing: anim_easing_in,
				duration: speed_in,
				delay: anime.stagger(
					Math.floor(splitted.length / 2) * (amount_in / 100) * 100,
					{
						from: mapSplitOrigin(splitOrigin_in),
						start: delaySteps_in,
					}
				),
			};

			switch (effectIn) {
				case "fading":
					inProps.opacity = [0, 1];
					break;

				case "from_left":
					inProps.translateX = [-100, 0];
					inProps.opacity = [0, 1];
					break;

				case "from_right":
					inProps.translateX = [100, 0];
					inProps.opacity = [0, 1];
					break;

				case "from_top":
					inProps.translateY = [-100, 0];
					inProps.opacity = [0, 1];
					break;

				case "from_bottom":
					inProps.translateY = [100, 0];
					inProps.opacity = [0, 1];
					break;

				case "zoom_front":
					inProps.scale = [1.6, 1];
					inProps.opacity = [0, 1];
					break;

				case "zoom_back":
					inProps.scale = [0.1, 1];
					inProps.opacity = [0, 1];
					break;

				case "random_position":
					inProps.opacity = [0, 1];
					inProps.scale = [function () { return random(0.1, 3); }, 1];
					inProps.translateX = [function () { return random(-500, 500); }, 0];
					inProps.translateY = [function () { return random(-500, 500); }, 0];
					inProps.rotate = [function () { return random(-120, 120); }, 0];
					break;

				default:
					inProps.opacity = [0, 1];
					break;
			}

			tl.add(inProps);

			if (!isLastTextCycle) {
				var outProps = {
					targets: splitted,
					duration: speed_out,
					easing: anim_easing_out,
					delay: anime.stagger(
						Math.floor(splitted.length / 2) * (amount_out / 100) * 100,
						{
							from: mapSplitOrigin(splitOrigin_out),
							start: delaySteps_out,
						}
					),
					complete: changeText,
				};

				switch (effectOut) {
					case "fading":
						outProps.opacity = [1, 0];
						break;

					case "to_left":
						outProps.opacity = [1, 0];
						outProps.translateX = [0, -100];
						break;

					case "to_right":
						outProps.opacity = [1, 0];
						outProps.translateX = [0, 100];
						break;

					case "to_top":
						outProps.opacity = [1, 0];
						outProps.translateY = [0, -100];
						break;

					case "to_bottom":
						outProps.opacity = [1, 0];
						outProps.translateY = [0, 100];
						break;

					case "zoom_front":
						outProps.opacity = [1, 0];
						outProps.scale = [1, 1.6];
						break;

					case "zoom_back":
						outProps.opacity = [1, 0];
						outProps.scale = [1, 0.1];
						break;

					case "random_position":
						outProps.opacity = [1, 0];
						outProps.scale = [1, function () { return random(0.1, 3); }];
						outProps.translateX = [0, function () { return random(-500, 500); }];
						outProps.translateY = [0, function () { return random(-500, 500); }];
						outProps.rotate = [0, function () { return random(-120, 120); }];
						break;

					case "elastic":
						outProps.opacity = [1, 0];
						outProps.easing = "easeOutElastic";
						break;

					default:
						outProps.opacity = [1, 0];
						break;
				}

				tl.add(outProps);
			}

			if (elementSettings.animatetext_trigger === "animation") {
				tl.play();
			} else if (elementSettings.animatetext_trigger === "rollover") {
				target.on("mouseover", function () {
					tl.play();
				});
				target.on("mouseout", function () {
					tl.pause();
				});
			} else if (elementSettings.animatetext_trigger === "scroll") {
				analyzeActiveScroll(target, tl);
			}
		}

		function analyzeActiveScroll($el, timeline) {
			if (!$el.length) return;

			var observerOptions = {
				root: null,
				rootMargin: "0px",
				threshold: 1.0,
			};

			var runAnim = function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						timeline.play();
					} else {
						timeline.pause();
					}
				});
			};

			var observer = new IntersectionObserver(runAnim, observerOptions);
			observer.observe($el[0]);
		}

		function mapSplitOrigin(origin) {
			switch (origin) {
				case "center":
					return "center";
				case "end":
					return "last";
				default:
					return "first";
			}
		}

		animSplitText();
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dyncontel-animateText.default",
			WidgetElements_AnimateTextHandler
		);
	});
})(jQuery);
