function Object3DMaker()  {
    this.object_tree = {child:[]};
}

Object3DMaker.prototype = {
    make: function(tree_params) {
	var obj = this._make(tree_params, this.object_tree.child);
	this.object_tree.obj = obj;
	return obj;
    },
    _make: function(tree_params, object_tree_child) {
	var group = new THREE.Object3D();
	for (var i = 0, n = tree_params.length; i < n ; i++) {
	    var param = tree_params[i];
	    object_tree_child[param.name] = {};
	    if ("group" in param) {
		object_tree_child[param.name].child = {};
	        var obj = this._make(param.group, object_tree_child[param.name]);
	     } else {
		switch (param.type) {
		case 'sphere':
		    var geometry = new THREE.SphereGeometry(param.width, param.height);
		    break;
		case 'plane':
		    var geometry = new THREE.PlaneGeometry(param.size, 24, 24);
		    break;
		case 'polygon':
		    var shape = new THREE.Shape();
		    shape.moveTo(param.edges[0][0], param.edges[0][1], 0);
		    for (var j = 1 ; j < param.edges.length ; j++) {
			shape.lineTo(param.edges[j][0], param.edges[j][1], 0);
		    }
		    var amount = param.amount ? param.amount : 0;
		    var extrudeSettings = { amount: amount,  bevelSegments: 0 };
		    var centerStreet3d = shape.extrude( extrudeSettings );
		    var centerStreetPoints = shape.createPointsGeometry();
		    var centerStreetSpacedPoints = shape.createSpacedPointsGeometry();
		    geometry = centerStreet3d;
		    break;
		default:
		    console.error("Unknown type:"+param.type);
		    break;
		}
        var map = null;
        var material = param.material;
        if ("texture" in param) {
            var texture = param.texture;
            map = new THREE.ImageUtils.loadTexture(texture.imgsrc);
	    var toff = texture.offset;
	    map.offset.set(toff[0], toff[1], toff[2]);
            material = new THREE.MeshPhongMaterial({map: map});
        }
		if ("color" in param) {
	  	//材質オブジェクトの宣言と生成
                  var material_basic = new THREE.MeshBasicMaterial({color:param.color});
		  material = new THREE.MeshFaceMaterial([material_basic]);
		}
	        //立方体オブジェクトの生成
		var obj;
		if (param.type === 'polygon') {
		    console.log(param.color);
		    obj = THREE.SceneUtils.createMultiMaterialObject(geometry, [ new THREE.MeshLambertMaterial( { color: param.color, map: map } ), new THREE.MeshBasicMaterial( { color: 0x00FF00, wireframe: false, transparent: false } ) ] );
		} else {
		    obj = new THREE.Mesh(geometry, material);
		}
		var posi = param.posi;
	        obj.position.set( posi[0], posi[1], posi[2]);
	        // 立方体オブジェクトのシーンへの追加
	    }
	    group.add(obj);
	    object_tree_child[param.name].obj = obj;
	}
	return group;
    },
    query: function(path) {
	if (path === '') {
	    return this.object_tree.obj;
	}
    }
};

