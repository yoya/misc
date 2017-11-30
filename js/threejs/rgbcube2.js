"use strict";
var camera, scene, renderer;

init();
animate();
    
function init() {
    camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
    camera.position.z = 1.7;
    scene = new THREE.Scene();
    // scene.add( new THREE.AxesHelper(100) );
    
    for (var r = 0 ; r < 6 ; r++) {
	for (var g = 0 ; g < 6 ; g++) {
	    for (var b = 0 ; b < 6 ; b++) {
		var rr = 5 - r, gg = 5 - g, bb = 5 - b;
		var rx = (r * rr)?(1/5):(1/10);
		var ry = (g * gg)?(1/5):(1/10);
		var rz = (b * bb)?(1/5):(1/10);
		var geometry = new THREE.BoxGeometry(rx, ry, rz);
		//var geometry = new THREE.SphereGeometry(1/5/5, 1/5/5, 1/5/5);
		var color = new THREE.Color(r/5, g/5, b/5);
		var material = new THREE.MeshBasicMaterial( { color: color } )
		var mesh = new THREE.Mesh( geometry, material)
		var tx = (r * rr)?((r/5) - 0.5):
		    ( (r)? ((r/5) - 0.55) : ((r/5) - 0.45)); 
		var ty = (g * gg)?((g/5) - 0.5):
		    ( (g)? ((g/5) - 0.55) : ((g/5) - 0.45)); 
		var tz = (b * bb)?((b/5) - 0.5):
		    ( (b)? ((b/5) - 0.55) : ((b/5) - 0.45)); 
		mesh.position.set(tx, ty, tz);
		scene.add( mesh );
	    }
	}
    }

    //
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
