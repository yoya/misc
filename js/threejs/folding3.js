"use strict";
var camera, scene, renderer;
var geometry, geometry_back;
var map = null, map_back;
var material,material_back;
var mesh = null, mesh_back;

var foldingTypeSelect = document.getElementById("foldingTypeSelect");
var reverseDirectionSelect = document.getElementById("reverseDirectionSelect");

init();
updateTexture();
animate();

<<<<<<< HEAD
function init() {
    camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
    camera.position.z = 1.5;
    scene = new THREE.Scene();
    scene.add( new THREE.AxesHelper(100) );
    
    var canvas = document.getElementById("canvas3d");
    var [windowWidth, windowHeight] = [window.innerWidth, window.innerHeight]
    var aspect = 1.3;
    if (windowWidth  >  (windowHeight * aspect))  {
	canvas.width = windowHeight *  aspect;
	canvas.height = windowHeight;
    } else {
	canvas.width = windowWidth;
	canvas.height = windowWidth / aspect;
    }
    renderer = new THREE.WebGLRenderer( { canvas:canvas, antialias: false } );
    // renderer.setClearColor( 0x000000, 0 );
    // document.body.appendChild( renderer.domElement );
    new THREE.OrbitControls( camera, renderer.domElement );
}

function updateTexture() {
    var foldingType = foldingTypeSelect.value;
    var reverseDirection = reverseDirectionSelect.value;
    console.log("updateTexture("+foldingType+","+reverseDirection+")");
    if (mesh) {
	scene.remove( mesh );
	material.dispose();
	// map.dispose();
	geometry.dispose();
	scene.remove( mesh_back );
	material_back.dispose();
	// map_back.dispose();
	geometry_back.dispose();
    }
    if (foldingType === undefined) {
	foldingType = "fold3curl";
    }
    var [planeRows, planeCols] = [1, 1]
    if (foldingType === "fold2") {
	[planeRows, planeCols] = [2, 1];
    } else {
	[planeRows, planeCols] = [3, 1];
    }
    geometry = new THREE.PlaneGeometry( 2, 1, planeRows, planeCols );
    geometry_back = new THREE.PlaneGeometry( 2, 1, planeRows, planeCols );
    console.log(geometry.vertices);
    // folding geometry
    if (foldingType === "fold2") {
	geometry.vertices[0].x = geometry.vertices[3].x = -0.5;
	geometry.vertices[0].z = geometry.vertices[3].z = 0.5;
	geometry.vertices[2].x = geometry.vertices[5].x = 0.5;
	geometry.vertices[2].z = geometry.vertices[5].z = 0.5;
    } else if (foldingType === "fold3curl") {
	geometry.vertices[0].x = geometry.vertices[4].x = -0.5;
	geometry.vertices[0].z = geometry.vertices[4].z = 0.5;
	geometry.vertices[3].x = geometry.vertices[7].x = 0.5;
	geometry.vertices[3].z = geometry.vertices[7].z = 0.5;
    } else {
	geometry.vertices[0].x = geometry.vertices[4].x = -0.5;
	geometry.vertices[0].z = geometry.vertices[4].z = 0.5;
	geometry.vertices[3].x = geometry.vertices[7].x = 0.5;
	geometry.vertices[3].z = geometry.vertices[7].z = -0.5;
    }
    // reverse geometry
    if (reverseDirection === "horizontal") {
	for (var i = 0, l = geometry.vertices.length ; i < l/2; i++) {
	    geometry_back.vertices[      i] = geometry.vertices[l/2 - i - 1];
	    geometry_back.vertices[l/2 + i] = geometry.vertices[l   - i - 1];
	}
    } else {
	for (var i = 0, l = geometry.vertices.length ; i < l/2; i++) {
	    geometry_back.vertices[i] = geometry.vertices[l/2 + i];
	    geometry_back.vertices[l/2 + i] = geometry.vertices[i];
	}
    }
    
    if (map === null) {
	var texLoader = new THREE.TextureLoader();
	texLoader.load('front.jpg', function(texture) { // onload
	    console.log("map", map);
	    map = texture;
	    material = new THREE.MeshBasicMaterial( { map: map } )
	    mesh = new THREE.Mesh( geometry, material);
	    scene.add( mesh );
	});
	texLoader.load('back.png', function(texture) { // onload
	    map_back = texture;
	    material_back = new THREE.MeshBasicMaterial( { map: map_back } );
	    mesh_back = new THREE.Mesh( geometry_back, material_back);
	    scene.add( mesh_back );
	});
    } else {
	material = new THREE.MeshBasicMaterial( { map: map } )
	mesh = new THREE.Mesh( geometry, material);
	scene.add( mesh );
	material_back = new THREE.MeshBasicMaterial( { map: map_back } );
	mesh_back = new THREE.Mesh( geometry_back, material_back);
	scene.add( mesh_back );
    }
}

function animate() {
	requestAnimationFrame( animate );
	renderer.render( scene, camera );
}

/*
 * Select Handler
 */
foldingTypeSelect.addEventListener("change", function(e) {
    updateTexture();
});
reverseDirectionSelect.addEventListener("change", function(e) {
    updateTexture();
});

/*
 * ImageFile drop handler
*/
=======
>>>>>>> 80411ac9d2798ef92be2ed48c85af767b771750b
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
		var texture = new THREE.Texture(image);
<<<<<<< HEAD
		 texture.needsUpdate = true;
=======
		texture.needsUpdate = true;
>>>>>>> 80411ac9d2798ef92be2ed48c85af767b771750b
		// console.log(camera.position);
		if (camera.position.z > 0) {
		    if (map) {
			map.dispose();
		    }
		    map = texture;
		} else {
		    if (map_back) {
			map_back.dispose();
		    }
		    map_back = texture;
		}
		updateTexture();
            }
            image.src = dataURL;
        }
        reader.readAsDataURL(file);
    }
    return false;
}, false);
<<<<<<< HEAD
=======


    
function init() {
    camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
    camera.position.z = 1.5;
    scene = new THREE.Scene();
    scene.add( new THREE.AxesHelper(100) );
    
    var geometry = new THREE.PlaneGeometry( 2, 1, 3, 1);
    var geometry_back = new THREE.PlaneGeometry( 2, 1, 3, 1);
    console.log(geometry.vertices);
    // folding geometry
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

    renderer = new THREE.WebGLRenderer( { antialias: false, alpha: true } );
    renderer.setClearColor( 0x000000, 0 );
    renderer.setSize( window.innerWidth, window.innerHeight );
    document.body.appendChild( renderer.domElement );
    new THREE.OrbitControls( camera, renderer.domElement );
}

function animate() {
	requestAnimationFrame( animate );
	renderer.render( scene, camera );
}
>>>>>>> 80411ac9d2798ef92be2ed48c85af767b771750b
