"use strict";
var camera, scene, renderer;

init();
animate();
    
function init() {
    camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
    camera.position.z = 1.5;
    scene = new THREE.Scene();
    scene.add( new THREE.AxesHelper(100) );
    
    var geometry = new THREE.PlaneGeometry( 2, 1, 2, 1);
    var geometry_back = new THREE.PlaneGeometry( 2, 1, 2, 1);
    console.log(geometry.vertices);
    // folding geometry
    geometry.vertices[0].x = geometry.vertices[3].x = -0.5;
    geometry.vertices[0].z = geometry.vertices[3].z = 0.5;
    geometry.vertices[2].x = geometry.vertices[5].x = 0.5;
    geometry.vertices[2].z = geometry.vertices[5].z = 0.5;
    // reverse geometry
    for (var i = 0, l = geometry.vertices.length ; i < l/2; i++) {
	geometry_back.vertices[      i] = geometry.vertices[l/2 - i - 1];
	geometry_back.vertices[l/2 + i] = geometry.vertices[l   - i - 1];
    }

    var map = THREE.ImageUtils.loadTexture( 'front.jpg' );
    var material = new THREE.MeshBasicMaterial( { map: map } )
    scene.add( new THREE.Mesh( geometry, material) );

    map = THREE.ImageUtils.loadTexture( 'back.jpg' );
    material = new THREE.MeshBasicMaterial( { map: map } );
    scene.add( new THREE.Mesh( geometry_back, material) );
    
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
