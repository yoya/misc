"use strict";
var camera, scene, renderer;
var geometry, geometry_back;
var map = null, map_back;
var material,material_back;
var mesh = null, mesh_back;

var foldingTypeSelect = document.getElementById("foldingTypeSelect");
var reverseDirectionSelect = document.getElementById("reverseDirectionSelect");

var canvas = document.getElementById("canvas3d");

init();
updateTexture();
animate();

function init() {
    var [windowWidth, windowHeight] = [window.innerWidth, window.innerHeight];
    var aspect = 1.3;
    if (windowWidth  >  (windowHeight * aspect))  {
	canvas.width = windowHeight *  aspect;
	canvas.height = windowHeight;
    } else {
	canvas.width = windowWidth;
	canvas.height = windowWidth / aspect;
    }
    camera = new THREE.PerspectiveCamera( 70, aspect, 0.01, 10 );
    camera.position.z = 1.5;
    scene = new THREE.Scene();
    scene.add( new THREE.AxesHelper(100) );
    
    renderer = new THREE.WebGLRenderer( { canvas:canvas, antialias: false } );
    // renderer.setClearColor( 0x000000, 0 );
    // document.body.appendChild( renderer.domElement );
    new THREE.OrbitControls( camera, renderer.domElement );
}

function resize() {
    var [windowWidth, windowHeight] = [window.innerWidth, window.innerHeight];
    var aspect = 1.3;
    if (windowWidth  >  (windowHeight * aspect))  {
	canvas.width = windowHeight *  aspect;
	canvas.height = windowHeight;
    } else {
	canvas.width = windowWidth;
	canvas.height = windowWidth / aspect;
    }
    // http://gupuru.hatenablog.jp/entry/2014/01/04/223708
    renderer.setSize(canvas.width, canvas.height);
    camera.aspect = aspect;
    camera.updateProjectionMatrix();
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
    geometry = new THREE.PlaneGeometry( 2, 1.5, planeRows, planeCols );
    geometry_back = new THREE.PlaneGeometry( 2, 1.5, planeRows, planeCols );
    console.log(geometry.vertices);
    foldingGeometry(foldingType, reverseDirection, 0.5);

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

// degree 0 => 1
function foldingGeometry(foldingType, reverseDirection, degree) {
    // folding geometry
    if (foldingType === "fold2") {
	/*
	  0:(-1, 0.5) 1:(0, 0.5) 2:(1, 0.5)
	  3:(-1,-0.5) 4:(0,-0.5) 5:(1,-0.5)
	  const 1, 4
	*/
	var v_0 = new THREE.Vector3(-1, 0, 0 );
	var v_2 = new THREE.Vector3( 1, 0, 0 );
	var v_3 = new THREE.Vector3(-1, 0, 0 );
	var v_5 = new THREE.Vector3( 1, 0, 0 );
	console.log([v_0, v_2, v_3, v_5]);
	var e_r = new THREE.Euler( 0, -Math.PI / 2 * degree, 0 );
	var e_l = new THREE.Euler( 0, Math.PI / 2 * degree, 0 );
	console.log("e_r, e_l:", e_r, e_l);
	v_0.applyEuler(e_l);  v_2.applyEuler(e_r);
	v_3.applyEuler(e_l);  v_5.applyEuler(e_r);
	console.log("after euler:", [v_0, v_2, v_3, v_5]);
	v_0.add(geometry.vertices[1]);
	v_2.add(geometry.vertices[1]);
	v_3.add(geometry.vertices[4]);
	v_5.add(geometry.vertices[4]);
	console.log("after add:", [v_0, v_2, v_3, v_5]);
	geometry.vertices[0] = v_0;  geometry.vertices[2] = v_2;
	geometry.vertices[3] = v_3;  geometry.vertices[5] = v_5;
	console.log(geometry.vertices);
    } else if (foldingType === "fold3curl") {
	/*
	  0:(-1, 0.5) 1:(-0.333, 0.5) 2:(0.333, 0.5) 3:(1,-0.5)
	  4:(-1,-0.5) 5:(-0.333,-0.5) 6:(0.333,-0.5) 7:(1:-0.5)
	  const 1, 2, 5, 6
	*/
	var v_0 = new THREE.Vector3(-(1/3), 0, 0 );
	var v_3 = new THREE.Vector3( (1/3), 0, 0 );
	var v_4 = new THREE.Vector3(-(1/3), 0, 0 );
	var v_7 = new THREE.Vector3( (1/3), 0, 0 );
	var e_r = new THREE.Euler( 0, -Math.PI / 2 * degree, 0 );
	var e_l = new THREE.Euler( 0, Math.PI  * degree, 0 );
	console.log("e_r, e_l:", e_r, e_l);
	v_0.applyEuler(e_l);  v_3.applyEuler(e_r);
	v_4.applyEuler(e_l);  v_7.applyEuler(e_r);
	console.log("after euler:", [v_0, v_3, v_4, v_7]);
	v_0.add(geometry.vertices[0]);
	v_3.add(geometry.vertices[3]);
	v_4.add(geometry.vertices[4]);
	v_7.add(geometry.vertices[7]);
	console.log("after add:", [v_0, v_3, v_4, v_7]);
	geometry.vertices[0] = v_0;  geometry.vertices[3] = v_3;
	geometry.vertices[4] = v_4;  geometry.vertices[7] = v_7;
	console.log(geometry.vertices);
    } else {
	/*
	  0:(-1, 0.5) 1:(-0.333, 0.5) 2:(0.333, 0.5) 3:(1,-0.5)
	  4:(-1,-0.5) 5:(-0.333,-0.5) 6:(0.333,-0.5) 7:(1:-0.5)
	  const 1, 2, 5, 6
	*/
	var v_0 = new THREE.Vector3(-(1/3), 0, 0 );
	var v_3 = new THREE.Vector3( (1/3), 0, 0 );
	var v_4 = new THREE.Vector3(-(1/3), 0, 0 );
	var v_7 = new THREE.Vector3( (1/3), 0, 0 );
	var e_r = new THREE.Euler( 0, Math.PI * degree, 0 );
	var e_l = new THREE.Euler( 0, Math.PI * degree, 0 );
	console.log("e_r, e_l:", e_r, e_l);
	v_0.applyEuler(e_l);  v_3.applyEuler(e_r);
	v_4.applyEuler(e_l);  v_7.applyEuler(e_r);
	console.log("after euler:", [v_0, v_3, v_4, v_7]);
	v_0.add(geometry.vertices[0]);
	v_3.add(geometry.vertices[3]);
	v_4.add(geometry.vertices[4]);
	v_7.add(geometry.vertices[7]);
	console.log("after add:", [v_0, v_3, v_4, v_7]);
	geometry.vertices[0] = v_0;  geometry.vertices[3] = v_3;
	geometry.vertices[4] = v_4;  geometry.vertices[7] = v_7;
	console.log(geometry.vertices);
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
    geometry.verticesNeedUpdate = true;
    geometry.elementsNeedUpdate = true;
    geometry.morphTargetsNeedUpdate = true;
    geometry.uvsNeedUpdate = true;
    geometry.normalsNeedUpdate = true;
    geometry.colorsNeedUpdate = true;
    geometry.tangentsNeedUpdate = true;
    geometry_back.verticesNeedUpdate = true;
    geometry_back.elementsNeedUpdate = true;
    geometry_back.morphTargetsNeedUpdate = true;
    geometry_back.uvsNeedUpdate = true;
    geometry_back.normalsNeedUpdate = true;
    geometry_back.colorsNeedUpdate = true;
    geometry_back.tangentsNeedUpdate = true;
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
 * resize
 */

window.addEventListener("resize" , function(e) {
    resize();
});

/*
 * ImageFile drop handler
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
		var texture = new THREE.Texture(image);
		 texture.needsUpdate = true;
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
