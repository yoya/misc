"use strict";
var camera, scene, renderer;
var material, material_back;

init();
animate();

var cancelEvent = function(e) {
    e.preventDefault();
    e.stopPropagation();
    return false;
};
document.addEventListener("dragover" , cancelEvent, false);
document.addEventListener("dragenter", cancelEvent, false);
document.addEventListener("drop" , function(e) {
    e.preventDefault();
    var file = e.dataTransfer.files[0];
    if (file) {
	var reader = new FileReader();
        reader.onload = function(e) {
	    var dataURL = e.target.result;
            var image = new Image();
            image.onload = function() {
		var canvas = document.createElement("canvas");
		canvas.width = image.width;
		canvas.height = image.height;
		var ctx = canvas.getContext("2d");
		ctx.drawImage(image, 0, 0, image.width, image.height,
			      0, 0, canvas.width, canvas.height);
		//var texture = new THREE.Texture(canvas);
		var texture = new THREE.Texture(image);
		texture.needsUpdate = true;
		// console.log(camera.position);
		if (camera.position.z > 0) {
		    material.map = texture;
		} else {
		    material_back.map = texture;
		}
            }
            image.src = dataURL;
        }
        reader.readAsDataURL(file);
    }
    return false;
}, false);


    
function init() {
    camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
    camera.position.z = 1.5;
    scene = new THREE.Scene();
    scene.add( new THREE.AxesHelper(100) );
    
    var geometry = new THREE.PlaneGeometry( 2, 1, 3, 1);
    var geometry_back = new THREE.PlaneGeometry( 2, 1, 3, 1);
    console.log(geometry.vertices);
    geometry.vertices[0].x = geometry.vertices[4].x = -0.5;
    geometry.vertices[0].z = geometry.vertices[4].z = 0.5;
    geometry.vertices[3].x = geometry.vertices[7].x = 0.5;
    geometry.vertices[3].z = geometry.vertices[7].z = 0.5;
    // reverse geometry
    for (var i = 0, l = geometry.vertices.length ; i < l/2; i++) {
	geometry_back.vertices[      i] = geometry.vertices[l/2 - i - 1];
	geometry_back.vertices[l/2 + i] = geometry.vertices[l   - i - 1];
    }

    var texLoader = new THREE.TextureLoader();
    
    texLoader.load('front.jpg', function(texture) { // onload
	material = new THREE.MeshBasicMaterial( { map: texture } )
	scene.add( new THREE.Mesh( geometry, material) );
    });
    texLoader.load('back.jpg', function(texture) { // onload
	material_back = new THREE.MeshBasicMaterial( { map: texture } );
	scene.add( new THREE.Mesh( geometry_back, material_back) );
    });

    //
    renderer = new THREE.WebGLRenderer( { antialias: true, alpha: true } );
    renderer.setClearColor( 0x000000, 0 );
    renderer.setSize( window.innerWidth, window.innerHeight );
    document.body.appendChild( renderer.domElement );
    new THREE.OrbitControls( camera, renderer.domElement );
}

function animate() {
	requestAnimationFrame( animate );
	renderer.render( scene, camera );
}

