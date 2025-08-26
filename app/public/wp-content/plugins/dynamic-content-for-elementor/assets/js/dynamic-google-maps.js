(function ($) {
	var DyncontEl_GoogleMapsHandler = function ($scope, $) {
		const initMap = () => {
			var $map = $scope.find(".map");
			var map = $map[0];
			var id_scope = $scope.attr("data-id");
			var bounds;
			var infoWindows = [];
			
			// Positions
			if (!$map[0]) {
				console.error("Missing params");
				return;
			}
			let positions = $map[0].dataset.positions;
			try {
				positions = JSON.parse(positions);
			} catch (err) {
				console.error(err);
				positions = [];
			}

			// Exit if the map doesn't have positions
			if (!positions.length) {
				return;
			}

			let marker;
			var latitude = parseFloat(positions[0].lat) || 0;
			var longitude = parseFloat(positions[0].lng) || 0;
			var elementSettings = dceGetElementSettings($scope);
			var mapId = id_scope;
			var newMarkerObject = Boolean(elementSettings.new_api);
			var markerWidth = elementSettings.marker_width || 20;
			var markerHeight = elementSettings.marker_height || 20;
			var zoom = $map.data("zoom") || 10;
			var imageMarker = positions[0].custom_marker_image || "";
			var infoWindow_panel_maxwidth =
				elementSettings.infoWindow_panel_maxwidth;

			if (markerWidth && markerHeight && imageMarker) {
				imageMarker = {
					url: imageMarker,
					scaledSize: new google.maps.Size(markerWidth, markerHeight),
				};
			}

			// Map Parameters
			var mapParams = {
				zoom: zoom,
				scrollwheel: Boolean(elementSettings.prevent_scroll),
				mapTypeControl: Boolean(elementSettings.maptypecontrol),
				panControl: Boolean(elementSettings.pancontrol),
				rotateControl: Boolean(elementSettings.rotaterontrol),
				scaleControl: Boolean(elementSettings.scalecontrol),
				streetViewControl: Boolean(elementSettings.streetviewcontrol),
				zoomControl: Boolean(elementSettings.zoomcontrol),
				fullscreenControl: Boolean(elementSettings.fullscreenControl),
				center: {
					lat: latitude,
					lng: longitude,
				},
			};
			if (newMarkerObject) {
				mapParams["mapId"] = mapId;
			}

			// Map Type (Roadmap, satellite, etc.)
			if (
				elementSettings.map_type &&
				elementSettings.map_type !== "acfmap"
			) {
				mapParams["mapTypeId"] = elementSettings.map_type;
			}

			// Zoom Minimum and Maximum
			if (elementSettings.zoom_custom) {
				minZoom = elementSettings.zoom_minimum.size || 0;
				maxZoom = elementSettings.zoom_maximum.size || 20;
				if (minZoom > maxZoom) {
					minZoom = maxZoom;
				}
				mapParams["minZoom"] = minZoom;
				mapParams["maxZoom"] = maxZoom;
			}

			if (elementSettings.style_select === "prestyle") {
				let fileStyle = elementSettings.snazzy_select;
				let jsonUrl;

				// Check if fileStyle starts with 'http' (compatibility with old settings that contains DCE_URL/json_file without .json extension)
				if (fileStyle.startsWith("http")) {
					jsonUrl = fileStyle + ".json"; // Append '.json' extension to the URL
				} else {
					// New settings contains only the file name
					let dceUrl = elementSettings.dce_url; // Get DCE_URL from elementSettings
					jsonUrl =
						dceUrl + "assets/maps_style/" + fileStyle + ".json"; // Construct the full URL
				}

				$.getJSON(jsonUrl, function (json) {
					mapParams["styles"] = json;
					_initMap(map, mapParams);
				});
			} else {
				if (elementSettings.style_select === "custom") {
					mapParams["styles"] = JSON.parse(elementSettings.style_map);
				}
				_initMap(map, mapParams);
			}

			function _initMap(mapElement, mapParameters) {
				map = new google.maps.Map(mapElement, mapParameters);
				var markers = [];
				var mapDataType = elementSettings.map_data_type;

				// Geolocation
				if (elementSettings.geolocation == "yes") {
					const locationButton = document.createElement("button");
					locationButton.textContent =
						elementSettings.geolocation_button_text;
					locationButton.classList.add("custom-map-control-button");
					map.controls[google.maps.ControlPosition.TOP_CENTER].push(
						locationButton,
					);
					locationButton.addEventListener("click", () => {
						// HTML5 geolocation
						if (navigator.geolocation) {
							navigator.geolocation.getCurrentPosition(
								(position) => {
									const pos = {
										lat: position.coords.latitude,
										lng: position.coords.longitude,
									};
									map.setCenter(pos);
									if (
										elementSettings.geolocation_change_zoom
									) {
										map.setZoom(
											elementSettings.geolocation_zoom
												.size || 10,
										);
									}
								},
								() => {
									handleLocationError(
										true,
										new google.maps.InfoWindow(),
										map.getCenter(),
									);
								},
							);
						} else {
							// Browser doesn't support Geolocation
							handleLocationError(
								false,
								new google.maps.InfoWindow(),
								map.getCenter(),
							);
						}
					});
				}

				function createAndAddMarker(
					position,
					map,
					icon,
					animation,
					index = null,
				) {
					const markerElement = document.createElement("div");
					if (icon) {
						markerElement.innerHTML = `<img src="${icon.url}" alt="marker icon" style="width: ${icon.scaledSize.width}px; height: ${icon.scaledSize.height}px;">`;
					}
					if (
						!newMarkerObject ||
						typeof (google.maps.marker && google.maps.marker.AdvancedMarkerElement) === "undefined"
					) {
						marker = new google.maps.Marker({
							position,
							map,
							icon,
							animation,
						});
					} else {
						marker = new google.maps.marker.AdvancedMarkerElement({
							position,
							map,
							content: icon ? markerElement : undefined,
						});
						if (animation) {
							markerElement.style.animation = animation;
						}
					}

					if (elementSettings.enable_infoWindow) {
						if (index !== null) {
							createInfoWindow(marker, index);
						} else if (index === null) {
							createInfoWindow(marker);
						}
					}

					return marker;
				}

				function getImageMarkerList(
					imageMarkerList,
					markerWidth,
					markerHeight,
				) {
					if (markerWidth && markerHeight && imageMarkerList) {
						return {
							url: imageMarkerList,
							scaledSize: new google.maps.Size(
								markerWidth,
								markerHeight,
							),
						};
					}
					return imageMarkerList;
				}

				if (mapDataType === "address") {
					let address = positions[0].address || "";
					let geocoder = new google.maps.Geocoder();
					geocoder.geocode(
						{
							address: address,
						},
						function (results, status) {
							if (status === "OK") {
								map.setCenter(results[0].geometry.location);
								createAndAddMarker(
									results[0].geometry.location,
									map,
									imageMarker,
									google.maps.Animation.DROP,
								);
							}
						},
					);
				} else if (
					mapDataType === "latlng" ||
					(!elementSettings.use_query &&
						(mapDataType === "acfmap" ||
							mapDataType === "metabox_google_maps"))
				) {
					const latLng = new google.maps.LatLng(latitude, longitude);
					map.panTo(latLng);
					createAndAddMarker(
						latLng,
						map,
						imageMarker,
						google.maps.Animation.DROP,
					);
				} else {
					// Query
					const bounds = new google.maps.LatLngBounds();
					positions.forEach((position, index) => {
						const latLng = new google.maps.LatLng(
							position.lat,
							position.lng,
						);
						map.panTo(latLng);
						const imageMarkerList = getImageMarkerList(
							position.custom_marker_image,
							elementSettings.marker_width,
							elementSettings.marker_height,
						);
						
						let markerPosition = latLng;

						if (elementSettings.prevent_marker_overlapping == "yes") {
							// Adjust marker position to prevent overlapping with other markers
							const adjustedPosition = adjustCoordinates(position.lat, position.lng, index, positions.length);
							markerPosition = new google.maps.LatLng(adjustedPosition.lat, adjustedPosition.lng);
						}

						const marker = createAndAddMarker(
							markerPosition,
							map,
							imageMarkerList,
							google.maps.Animation.DROP,
							index,
						);
						markers.push(marker);
						bounds.extend(marker.position);
					});

					map.fitBounds(bounds);
					if (!elementSettings.auto_zoom) {
						google.maps.event.addListenerOnce(
							map,
							"idle",
							function () {
								map.setZoom(zoom);
							},
						);
					}

					if (elementSettings.markerclustererControl) {
						new markerClusterer.MarkerClusterer({
							map,
							markers,
						});
					}
				}
			}

			 // Adjust coordinates to prevent overlapping markers
            function adjustCoordinates(lat, lng, index, totalPoints) {
            if (index === 0) {
                return { lat, lng };
            }
            const radiusInMeters = 1.5; 
            const metersToDegrees = 0.000009;
            const radius = radiusInMeters * metersToDegrees;
            // Angle calculation to distribute points around the center
            const angle = (2 * Math.PI / (totalPoints - 1)) * (index - 1);
            const dLat = radius * Math.cos(angle);
            const dLng = radius * Math.sin(angle);
                return {
                    lat: lat + dLat,
                    lng: lng + dLng,
                };
        }

			function createInfoWindow(marker, index = 0) {
				let infoWindowData = positions[index].infowindow;
				let maxWidth;

				if (typeof infoWindow_panel_maxwidth !== "undefined") {
					maxWidth = infoWindow_panel_maxwidth.size;
				}

				marker.addListener("click", function () {
					if (elementSettings.infoWindow_one_at_the_time) {
						infoWindows.forEach(function (infoWindow) {
							infoWindow.close();
						});
					}

					if (positions[index].link) {
						return (window.location = positions[index].link);
					}
					if (
						"text" === infoWindowData["type"] &&
						!infoWindowData["content"]
					) {
						return;
					}
					if ("text" === infoWindowData["type"]) {
						if (!marker.infoWindow) {
							marker.infoWindow = new google.maps.InfoWindow({
								content: infoWindowData["content"],
								maxWidth: maxWidth,
							});
						} else {
							marker.infoWindow.setContent(
								infoWindowData["content"],
							);
						}
						marker.infoWindow.open(map, marker);
						infoWindows.push(marker.infoWindow);
						return;
					}
					let loadingContent =
						elementSettings.infoWindow_loading_text || "Loading...";
					var loadingInfoWindow = new google.maps.InfoWindow({
						content: loadingContent,
						disableAutoPan: true,
					});

					loadingInfoWindow.open(map, marker);

					let templateId = infoWindowData["template-id"] || 0;
					let postId = infoWindowData["post-id"] || 0;

					// Load template content if not already done
					if (!marker.templateContent) {
						var ajaxData = {
							template_id: templateId,
							post_id: postId,
						};

						wp.ajax
							.post("load_elementor_template_content", ajaxData)
							.done(function (response) {
								loadingInfoWindow.close();
								marker.templateContent = response;
								if (!marker.infoWindow) {
									marker.infoWindow =
										new google.maps.InfoWindow({
											maxWidth: maxWidth,
											content: response,
										});
								}
								infoWindows.push(marker.infoWindow);
								marker.infoWindow.open(map, marker);
							})
							.fail(function (error) {
								console.error("Ajax Error:", error);
								loadingInfoWindow.close();
							});
					} else {
						loadingInfoWindow.close();
						marker.infoWindow.setContent(marker.templateContent);
						marker.infoWindow.open(map, marker);
					}
				});

				// Close InfoWindow when clicking outside of maps
				if (elementSettings.infoWindow_click_outside) {
					document.body.addEventListener("click", function (e) {
						var isClickOutsideMap = !e.target.closest(".map");
						if (marker.infoWindow && isClickOutsideMap) {
							marker.infoWindow.close();
						}
					});
				}
			}

			function handleLocationError(
				browserHasGeolocation,
				infoWindow,
				pos,
			) {
				infoWindow.setPosition(pos);
				infoWindow.setContent(
					browserHasGeolocation
						? "Error: The Geolocation service failed."
						: "Error: Your browser doesn't support geolocation.",
				);
				infoWindow.open(map);
			}
		};
		// google api might loaded before or after this script based on third
		// party plugins. So we take both cases into account:
		if (typeof google !== "undefined") {
			initMap();
		} else {
			window.addEventListener("dce-google-maps-api-loaded", initMap);
		}
	};

	const reInitElementorWidgets = (domElement) => {
		if (
			!domElement ||
			!window.elementorFrontend ||
			!window.elementorFrontend.elementsHandler ||
			!window.elementorFrontend.elementsHandler.runReadyTrigger
		) {
			return;
		}
		const runReadyTrigger =
			window.elementorFrontend.elementsHandler.runReadyTrigger;

		runReadyTrigger($(domElement));

		$(domElement)
			.find(".elementor-widget")
			.each(function () {
				runReadyTrigger($(this));
			});

		$(domElement)
			.find(
				".elementor-widget-dyncontel-acf-google-maps, .elementor-widget-dce-metabox-google-maps",
			)
			.each(function () {
				DyncontEl_GoogleMapsHandler($(this), $);
			});
	};

	const fadeElements = (queryObject, enabled = false) => {
		let attributes = queryObject.getAttributes();

		const defaults = {
			enabled: enabled,
			duration: 200,
		};
		if (!defaults.enabled) {
			$(attributes.queryContainer).css("opacity", 0).animate(
				{
					opacity: 1,
				},
				defaults.duration,
			);
			$(attributes.dynamicSections).css("opacity", 0).animate(
				{
					opacity: 1,
				},
				defaults.duration,
			);
			return;
		}
		[attributes.queryContainer, attributes.dynamicSections].forEach(
			(selector) => {
				const $element = $(selector);
				if ($element.length) {
					$element.css("opacity", 1).animate(
						{
							opacity: 0,
						},
						defaults.duration,
					);
				}
			},
		);
	};

	const initSearchAndFilterProQueryListeners = (query) => {
		const supportedIntegrations = [
			"dynamicooo/dynamic-content-for-elementor",
		];

		if (query.getAttribute("resultsDynamicUpdate") !== "yes") {
			return;
		}

		if (
			!supportedIntegrations.includes(
				query.getAttribute("queryIntegration"),
			)
		) {
			return;
		}

		query.on("get-results/finish", function (queryObject) {
			const queryContainer = queryObject.getQueryContainer();
			reInitElementorWidgets(queryContainer);
			const dynamicSectionsSelector =
				query.getAttribute("dynamicSections");
			const dynamicSectionsArea = document.querySelector(
				dynamicSectionsSelector,
			);
			reInitElementorWidgets(dynamicSectionsArea);
			fadeElements(queryObject, false);
		});
		query.on("get-results/start", function (queryObject) {
			fadeElements(queryObject, true);
		});
	};

	// The dynamicooo/google-maps/init event is for GDPR plugins like Borlabs:
	$(window).on(
		"elementor/frontend/init dynamicooo/google-maps-init",
		function () {
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/dyncontel-acf-google-maps.default",
				DyncontEl_GoogleMapsHandler,
			);
			elementorFrontend.hooks.addAction(
				"frontend/element_ready/dce-metabox-google-maps.default",
				DyncontEl_GoogleMapsHandler,
			);
		},
	);
	$(window).on("elementor/frontend/init", () => {
		if (window.searchAndFilter?.frontend?.queries) {
			const queries = window.searchAndFilter.frontend.queries.getAll();

			for (let i = 0; i < queries.length; i++) {
				const query = queries[i];
				initSearchAndFilterProQueryListeners(query);
			}
		}
	});
})(jQuery);

// Re init layout after ajax request on Search&Filter Pro
(function ($) {
	"use strict";
	$(function () {
		// Support for Search & Filter Pro v2
		$(document).on("sf:ajaxfinish", ".searchandfilter", function (e, data) {
			if (elementorFrontend) {
				if (
					elementorFrontend.elementsHandler.runReadyTrigger &&
					SF_LDATA.extensions.indexOf("search-filter-elementor") < 0
				) {
					elementorFrontend.elementsHandler.runReadyTrigger(
						$(data.targetSelector),
					);
				}
			}
		});
	});
})(jQuery);
