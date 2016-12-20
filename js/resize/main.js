// require parameter.js
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
    for (id in selectBindingList) {
	type = selectBindingList[id];
	selectBinding(valueTable, id);
	valueTable[id] = getById(id).value;
    }
    for (id in rangeBindingList) {
	type = rangeBindingList[id];
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
	// resize
    }
}
