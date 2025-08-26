(function ($) {
	var WidgetElements_BGCanvasHandler = function ($scope, $) {
		var elementSettings = dceGetElementSettings($scope);
		var id_scope = $scope.attr("data-id");

		class Scene {
			constructor() {
				this.bindMethods();

				this.vert = `
            varying vec2 vUv;
            void main() {
              vUv = uv;
              gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
            }
            `;

				this.frag = `
            // Shoutout too https://gist.github.com/statico/df64c5d167362ecf7b34fca0b1459a44

            uniform sampler2D texture;

            uniform vec2 uScreenSize;
            uniform vec2 uTextureSize;

            varying vec2 vUv;

            void main() {

              float ratioScreen = uScreenSize.x / uScreenSize.y;
              float ratioTexture = uTextureSize.x / uTextureSize.y;

              vec2 new = ratioScreen < ratioTexture
              ? vec2(uTextureSize.x * uScreenSize.y / uTextureSize.y, uScreenSize.y)
              : vec2(uScreenSize.x, uTextureSize.y * uScreenSize.x / uTextureSize.x);

              vec2 offset = (ratioScreen < ratioTexture
              ? vec2((new.x - uScreenSize.x) / 2.0, 0.0)
              : vec2(0.0, (new.y - uScreenSize.y) / 2.0)) / new;

              vec2 newUv = vUv * uScreenSize / new + offset;

              gl_FragColor = texture2D(texture, newUv);
            }
            `;
				this.container = $scope.find(".dce-container-bgcanvas");
				this.el = $scope.find(".js-scene")[0];

				this.image = this.container.attr("data-bgcanvasimage");
				this.video = null;
				this.texture = null;

				// PostProcessing
				this.composer = null;
				this.renderPass = null;
				this.copyPass = null;

				//Effect
				this.effectAscii = null;
				// Render Time
				this.clock = null;
				this.delta = 0.01;

				// START
				this.init();
				//
				let then = 0;
			}

			bindMethods() {
				["render"].forEach((fn) => (this[fn] = this[fn].bind(this)));
			}

			cameraSetup() {
				this.camera = new THREE.OrthographicCamera(
					this.el.offsetWidth / -2,
					this.el.offsetWidth / 2,
					this.el.offsetHeight / 2,
					this.el.offsetHeight / -2,
					1,
					1000,
				);

				this.camera.lookAt(this.scene.position);
				this.camera.position.z = 1;
			}

			setup() {
				this.renderer = new THREE.WebGLRenderer({ alpha: true });
				this.renderer.setPixelRatio(window.devicePixelRatio);
				this.renderer.setSize(
					this.el.offsetWidth,
					this.el.offsetHeight,
				);

				this.el.appendChild(this.renderer.domElement);

				// ---------------- Ascii
				var filter_ascii = false; //Boolean(elementSettings.postprocessing_ascii);
				if (filter_ascii) {
					this.effectAscii = new THREE.AsciiEffect(
						this.renderer,
						" .:-+*=%@#",
						{ invert: true },
					);
					this.effectAscii.setSize(
						this.container.width(),
						this.container.height(),
					);
					this.effectAscii.domElement.style.color = "white";
					this.effectAscii.domElement.style.backgroundColor = "black";

					this.el.appendChild(this.effectAscii.domElement);
				} else {
					this.el.appendChild(this.renderer.domElement);
				}

				this.scene = new THREE.Scene();
				this.clock = new THREE.Clock(true);
			}

			loadTextures() {
				const loader = new THREE.TextureLoader();
				loader.crossOrigin = "";

				this.texture = loader.load(this.image, (texture) => {
					this.mat.uniforms.uTextureSize.value.set(
						texture.image.width,
						texture.image.height,
					);
				});

				this.texture.wrapS = THREE.ClampToEdgeWrapping;
				this.texture.wrapT = THREE.ClampToEdgeWrapping;
				this.texture.minFilter = THREE.LinearFilter;
			}

			createMesh() {
				this.mat = new THREE.ShaderMaterial({
					uniforms: {
						uScreenSize: {
							type: "v2",
							value: new THREE.Vector2(
								this.container.width(),
								this.container.height(),
							),
						}, //uScreenSize: { type: 'v2', value: new THREE.Vector2(500, 500) },
						uTextureSize: {
							type: "v2",
							value: new THREE.Vector2(1, 1),
						},
						width: {
							type: "f",
							value: this.container.width(),
						},
						height: {
							type: "f",
							value: this.container.height(),
						},
						texture: {
							type: "t",
							value: this.texture,
						},
					},

					transparent: true,
					vertexShader: this.vert,
					fragmentShader: this.frag,
				});

				const geometry = new THREE.PlaneBufferGeometry(1, 1, 1);

				const mesh = new THREE.Mesh(geometry, this.mat);
				mesh.scale.set(
					this.container.width(),
					this.container.height(),
					1,
				);
				this.mesh = mesh;

				this.scene.add(mesh);
			}

			initPostProcessing() {
				// postprocessing
				this.renderPass = new THREE.RenderPass(this.scene, this.camera);

				this.copyPass = new THREE.ShaderPass(THREE.CopyShader);
				this.copyPass.renderToScreen = true;

				this.composer = new THREE.EffectComposer(this.renderer);

				var postprocessing_film = Boolean(
					elementSettings.postprocessing_film,
				);
				var postprocessing_halftone = Boolean(
					elementSettings.postprocessing_halftone,
				);
				var postprocessing_rgbShiftShader = Boolean(
					elementSettings.postprocessing_rgbShiftShader,
				);
				var postprocessing_glitch = Boolean(
					elementSettings.postprocessing_glitch,
				);
				var postprocessing_dot = Boolean(
					elementSettings.postprocessing_dot,
				);
				var postprocessing_pixels = Boolean(
					elementSettings.postprocessing_pixels,
				);

				// ---------------- Halftone
				if (postprocessing_halftone) {
					var params = {
						shape:
							Number(
								elementSettings.postprocessing_halftone_shape,
							) || 1,
						radius:
							Number(
								elementSettings.postprocessing_halftone_radius
									.size,
							) || 80,
						rotateR: Math.PI / 12,
						rotateB: (Math.PI / 12) * 2,
						rotateG: (Math.PI / 12) * 3,
						scatter: 0,
						blending: 1,
						blendingMode: 1,
						greyscale:
							Boolean(
								elementSettings.postprocessing_halftone_grayscale,
							) || false,
						disable: false,
					};
					var halftonePass = new THREE.HalftonePass(
						this.container.width(),
						this.container.height(),
						params,
					);
				}
				if (postprocessing_rgbShiftShader) {
					var rgbEffect = new THREE.ShaderPass(THREE.RGBShiftShader);
					rgbEffect.uniforms["amount"].value =
						Number(
							elementSettings.postprocessing_rgbshift_amount.size,
						) / 100 || 0.015;
					rgbEffect.renderToScreen = true;
				}

				// ---------------- Film
				if (postprocessing_film) {
					var effectFilm = new THREE.FilmPass(); //noiseIntensity, scanlinesIntensity, scanlinesCount,
					// grayscale
					effectFilm.uniforms["grayscale"].value =
						Boolean(
							elementSettings.postprocessing_film_grayscale,
						) || false; //grayscale
					effectFilm.uniforms["nIntensity"].value =
						Number(
							elementSettings.postprocessing_film_noiseIntensity
								.size,
						) || 0.35; //noiseIntensity;
					effectFilm.uniforms["sIntensity"].value =
						Number(
							elementSettings
								.postprocessing_film_scanlinesIntensity.size,
						) || 0.025; //scanlinesIntensity;
					effectFilm.uniforms["sCount"].value =
						Number(
							elementSettings.postprocessing_film_scanlinesCount
								.size,
						) || 648; //scanlinesCount;
				}

				// ---------------- DotScreen
				if (postprocessing_dot) {
					var effectDotScreen = new THREE.DotScreenPass(
						new THREE.Vector2(0, 0),
						0.5,
						0.8,
					);
					effectDotScreen.uniforms["scale"].value =
						Number(elementSettings.postprocessing_dot_scale.size) ||
						1;
					effectDotScreen.uniforms["angle"].value =
						Number(elementSettings.postprocessing_dot_angle.size) ||
						0.5;
				}

				// ---------------- Glitch
				if (postprocessing_glitch) {
					var glitchPass = new THREE.GlitchPass(64);
					glitchPass.renderToScreen = true;
				}
				if (postprocessing_pixels) {
					var pixelPass = new THREE.ShaderPass(THREE.PixelShader);
					pixelPass.uniforms["resolution"].value = new THREE.Vector2(
						this.container.width(),
						this.container.height(),
					);
					pixelPass.uniforms["resolution"].value.multiplyScalar(
						window.devicePixelRatio,
					);
					pixelPass.uniforms["pixelSize"].value =
						elementSettings.postprocessing_pixels_size.size || 16;
				}

				this.composer.addPass(this.renderPass);
				//
				//
				//

				if (postprocessing_dot) {
					this.composer.addPass(effectDotScreen);
				}
				if (postprocessing_rgbShiftShader) {
					this.composer.addPass(rgbEffect);
				}
				if (postprocessing_halftone) {
					this.composer.addPass(halftonePass);
				}
				if (postprocessing_glitch) {
					this.composer.addPass(glitchPass);
				}
				if (postprocessing_film) {
					this.composer.addPass(effectFilm);
				}
				if (postprocessing_pixels) {
					this.composer.addPass(pixelPass);
				}
			}

			addListeners() {
				this.animation = anime({
					duration: Infinity,
					update: () => {
						this.render();
					},
				});
				window.addEventListener("resize", this.onResize.bind(this));
			}

			render() {
				var delta = this.clock.getDelta();
				this.renderer.render(this.scene, this.camera);
				this.composer.render(this.delta);
			}

			init() {
				this.setup();
				this.cameraSetup();
				this.loadTextures();
				this.createMesh();
				this.initPostProcessing();
				this.addListeners();
			}

			onResize() {
				this.mat.uniforms.uScreenSize.value.set(
					this.container.width(),
					this.container.height(),
				);
				this.mesh.scale.set(
					this.container.width(),
					this.container.height(),
					1,
				);

				this.camera.left = -this.container.width() / 2;
				this.camera.right = this.container.width() / 2;
				this.camera.top = this.container.height() / 2;
				this.camera.bottom = -this.container.height() / 2;
				this.camera.updateProjectionMatrix();

				this.composer.render();

				this.renderer.setSize(
					this.container.width(),
					this.container.height(),
				);
				this.composer.setSize(
					this.container.width(),
					this.container.height(),
				);

				// effects
			}
		} // end class Scene

		const scene = new Scene();
	};

	$(window).on("elementor/frontend/init", function () {
		elementorFrontend.hooks.addAction(
			"frontend/element_ready/dyncontel-bgcanvas.default",
			WidgetElements_BGCanvasHandler,
		);
	});
})(jQuery);
