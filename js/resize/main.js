"use strict";
// require func.js
// require bind.js
// require graph.js
// require image.js

main();

function getById(id) { return document.getElementById(id); }

function main() {
    var selectBindingList = {"filterType":null};
    var rangeBindingList  = {"logScale":"log",
			     "cubicB":"direct", "cubicC":"direct",
			     "lobe":"direct"};
    var valueTable = {};
    for (var id in selectBindingList) {
	var type = selectBindingList[id];
	selectBinding(valueTable, id);
	valueTable[id] = getById(id).value;
    }
    for (var id in rangeBindingList) {
	var type = rangeBindingList[id];
	rangeBinding(valueTable, id, type);
	var value = getById(id+"Value").value;
	valueTable[id] = value;
    }
    imageBinding(valueTable);
    update(valueTable, true);
}

function update(valueTable, heavy) {
    drawGraph(valueTable);
    if (heavy) {
	var srcCanvas = getById("srcCanvas");
	var dstCanvas = getById("dstCanvas");
	resizeFunc(srcCanvas, dstCanvas, valueTable);
    }
}
