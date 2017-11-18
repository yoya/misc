"use strict";

var camera, scene, renderer;

init();
animate();
    
function init() {
    camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
    camera.position.z = 1.5;
    scene = new THREE.Scene();
    
    var geometry = new THREE.PlaneGeometry( 2, 1, 2, 1);
    console.log(geometry.verteces);
    geometry.vertices[0].x = geometry.vertices[3].x = -0.5;
    geometry.vertices[0].z = geometry.vertices[3].z = 0.5;
    geometry.vertices[2].x = geometry.vertices[5].x = 0.5;
    geometry.vertices[2].z = geometry.vertices[5].z = 0.5;

    var map = THREE.ImageUtils.loadTexture( 'front.jpg' );
    var material = new THREE.MeshBasicMaterial( { map: map } )
    scene.add( new THREE.Mesh( geometry, material) );

    map = THREE.ImageUtils.loadTexture( 'back.jpg' );
    material = new THREE.MeshBasicMaterial( { map: map,
					    side:  THREE.BackSide} );
    scene.add( new THREE.Mesh( geometry, material) );
    
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
