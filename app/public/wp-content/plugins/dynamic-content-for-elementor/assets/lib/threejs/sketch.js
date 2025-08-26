class Sketch {
	constructor(opts) {
	  this.scene = new THREE.Scene();
	  this.vertex = `varying vec2 vUv;
		void main() {
		  vUv = uv;
		  gl_Position = projectionMatrix * modelViewMatrix * vec4( position, 1.0 );
		}`;
	  this.fragment = opts.fragment;
	  this.uniforms = opts.uniforms;
	  
	  this.renderer = new THREE.WebGLRenderer({ alpha: true });
	  this.width = window.innerWidth;
	  this.height = window.innerHeight;
	  this.renderer.setPixelRatio(window.devicePixelRatio);
	  this.renderer.setSize(this.width, this.height);
	  this.renderer.setClearColor(0xeeeeee, 0);
  
	  this.duration = opts.duration || 1;
	  this.easing = opts.easing || 'easeInOutQuad'; 
	  
	  this.debug = opts.debug || false;
	  this.clicker = opts.content;
	  this.container = opts.container;
  
	  this.oldcontainertWidth = 0;
	  this.newcontainertWidth = 0;
	  this.oldcontainertHeight = 0;
	  this.newcontainertHeight = 0;
  
	  this.images = JSON.parse(this.container.getAttribute('data-images')) || [];
	  this.disp = this.container.getAttribute('data-disp');
	  this.width = this.container.offsetWidth;
	  this.height = this.container.offsetHeight;
  
	  this.container.appendChild(this.renderer.domElement);
  
	  this.camera = new THREE.PerspectiveCamera(
		70,
		this.width / this.height,
		0.001,
		1000
	  );
	  this.camera.position.set(0, 0, 2);
  
	  this.time = 0;
	  this.current = 0;
	  this.textures = [];
  
	  this.paused = true;
  
	  this.initiate(() => {
		this.setupResize();
		this.settings();
		this.addObjects();
		this.resize();
		this.hoverEvent();
		this.play();
	  });
	}
  
	initiate(cb) {
	  const promises = [];
	  let that = this;
	  this.images.forEach((url, i) => {
		let promise = new Promise((resolve) => {
		  that.textures[i] = new THREE.TextureLoader().load(url, resolve);
		});
		promises.push(promise);
	  });
  
	  Promise.all(promises).then(() => {
		cb();
	  });
	}
	
	hoverEvent() {
		
		this.progressAnim = anime({
			targets: this.material.uniforms.progress,
			value: [0, 1],
			duration: this.duration * 1000,  
			easing: this.easing,        
			autoplay: false, 
		});
		
		this.clicker.addEventListener('mouseenter', () => {
			if (this.progressAnim.reversed) {
			this.progressAnim.reverse();
			}
			this.progressAnim.play();
		});
		
		this.clicker.addEventListener('mouseleave', () => {
			if (!this.progressAnim.reversed) {
			this.progressAnim.reverse();
			}
			this.progressAnim.play();
		});
	}
	  
  
	settings() {
	  let that = this;
	  if (this.debug) this.gui = new dat.GUI();
	  this.settings = { progress: 0.5 };
	  
	  Object.keys(this.uniforms).forEach((item) => {
		this.settings[item] = this.uniforms[item].value;
		if (this.debug) {
		  this.gui.add(
			this.settings,
			item,
			this.uniforms[item].min,
			this.uniforms[item].max,
			0.01
		  );
		}
	  });
	}
  
	setupResize() {
	  window.addEventListener('resize', this.resize.bind(this));
	}
  
	resize() {
	  this.width = this.container.offsetWidth;
	  this.height = this.container.offsetHeight;
	  this.renderer.setSize(this.width, this.height);
	  this.camera.aspect = this.width / this.height;
  
	  this.imageAspect = this.textures[0].image.height / this.textures[0].image.width;
	  let a1, a2;
	  if (this.height / this.width > this.imageAspect) {
		a1 = (this.width / this.height) * this.imageAspect;
		a2 = 1;
	  } else {
		a1 = 1;
		a2 = (this.height / this.width) / this.imageAspect;
	  }
  
	  this.material.uniforms.resolution.value.x = this.width;
	  this.material.uniforms.resolution.value.y = this.height;
	  this.material.uniforms.resolution.value.z = a1;
	  this.material.uniforms.resolution.value.w = a2;
  
	  const dist = this.camera.position.z;
	  const height = 1;
	  this.camera.fov = 2 * (180 / Math.PI) * Math.atan(height / (2 * dist));
	  this.plane.scale.x = this.camera.aspect;
	  this.plane.scale.y = 1;
	  this.camera.updateProjectionMatrix();
	}
  
	addObjects() {
	  this.material = new THREE.ShaderMaterial({
		extensions: {
		  derivatives: '#extension GL_OES_standard_derivatives : enable'
		},
		side: THREE.DoubleSide,
		uniforms: {
		  time:        { type: 'f', value: 0 },
		  progress:    { type: 'f', value: 0 },
		  border:      { type: 'f', value: 0 },
		  intensity:   { type: 'f', value: 0 },
		  scaleX:      { type: 'f', value: 40 },
		  scaleY:      { type: 'f', value: 40 },
		  transition:  { type: 'f', value: 40 },
		  swipe:       { type: 'f', value: 0 },
		  width:       { type: 'f', value: 0 },
		  radius:      { type: 'f', value: 0 },
		  texture1:    { type: 'f', value: this.textures[0] },
		  texture2:    { type: 'f', value: this.textures[1] || this.textures[0] },
		  displacement:{ type: 'f', value: new THREE.TextureLoader().load(this.disp) },
		  resolution:  { type: 'v4', value: new THREE.Vector4() }
		},
		vertexShader: this.vertex,
		fragmentShader: this.fragment
	  });
  
	  this.geometry = new THREE.PlaneGeometry(1, 1, 2, 2);
	  this.plane = new THREE.Mesh(this.geometry, this.material);
	  this.scene.add(this.plane);
	}
  
	stop() {
	  this.paused = true;
	}
  
	play() {
	  this.paused = false;
	  this.render();
	}
  
	destroy() {
	  alert('destroy');
	}
  
	resizeControl() {
	  this.oldcontainertWidth = this.newcontainertWidth;
	  this.oldcontainertHeight = this.newcontainertHeight;
	  if (
		this.oldcontainertWidth != this.container.offsetWidth ||
		this.oldcontainertHeight != this.container.offsetHeight
	  ) {
		this.resize();
	  }
	  this.newcontainertWidth = this.container.offsetWidth;
	  this.newcontainertHeight = this.container.offsetHeight;
	}
  
	render() {
	  if (this.paused) return;
	  this.time += 0.05;
	  this.material.uniforms.time.value = this.time;
  
	  Object.keys(this.uniforms).forEach((item) => {
		this.material.uniforms[item].value = this.settings[item];
	  });
  
	  if (elementorFrontend.isEditMode()) {
		this.resizeControl();
	  }
  
	  requestAnimationFrame(this.render.bind(this));
	  this.renderer.render(this.scene, this.camera);
	}
  }
  