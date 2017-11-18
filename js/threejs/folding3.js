"use strict";
var camera, scene, renderer;
var material, material_back;

init();
animate();

/*
 * ファイルがドロップされた時の処理
 */
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
		var texture = new THREE.Texture(canvas);
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
    
    var geometry = new THREE.PlaneGeometry( 2, 1, 3, 1);
    console.log(geometry.vertices);
    geometry.vertices[0].x = geometry.vertices[4].x = -0.5;
    geometry.vertices[0].z = geometry.vertices[4].z = 0.5;
    geometry.vertices[3].x = geometry.vertices[7].x = 0.5;
    geometry.vertices[3].z = geometry.vertices[7].z = 0.5;

    /*
    var texLoader = new THREE.TextureLoader();
    texLoader.load('front.jpg', texture => { // onload
	material = new THREE.MeshBasicMaterial( { map: texture } )
	scene.add( new THREE.Mesh( geometry, material) );
    });
    texLoader.load('back.jpg', texture => { // onload
	var material = new THREE.MeshBasicMaterial( { map: texture,
						      side: THREE.BackSide} );
	scene.add( new THREE.Mesh( geometry, material) );
    });
    */
    var map = THREE.ImageUtils.loadTexture( 'front.jpg' );
    material = new THREE.MeshBasicMaterial( { map: map } )
    scene.add( new THREE.Mesh( geometry, material) );

    map = THREE.ImageUtils.loadTexture( 'back.jpg' );
     material_back = new THREE.MeshBasicMaterial( { map: map,
					    side:  THREE.BackSide} );
    scene.add( new THREE.Mesh( geometry, material_back) );

    scene.add( new THREE.AxesHelper(100) );
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

