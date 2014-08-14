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
	        var geometry = new THREE.SphereGeometry(param.size, 24, 24);
	        var map = null;
	        var color = param.color;
	        if ("texture" in param) {
	            var texture = param.texture;
	            map = new THREE.ImageUtils.loadTexture(texture.imgsrc);
		    var toff = texture.offset;
		    map.offset.set(toff[0], toff[1], toff[2]);
	            color = new THREE.MeshPhongMaterial({map: map});
	        }
	        //立方体オブジェクトの生成
	        var obj = new THREE.Mesh(geometry, color);
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

