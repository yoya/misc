function selectBinding(valueTable, id) {
    var elem = getById(id);
    if (! elem) {
	console.error(id+" not found");
    }
    elem.addEventListener("change", function(e) {
	valueTable[id] = elem.value;
	update(valueTable, true);
    });
}

function rangeBinding(valueTable, id, type) {
    var elem = getById(id);
    var elemText = getById(id + "Value");
    // Range to Text
    var range2text = function(e) {
	if (type === "log") {
	    var value = Math.pow(10, elem.value);
	    elemText.value = Math.round(value * 100) / 100;
	} else { // "direct"
	    elemText.value = elem.value;
	}
	valueTable[id] = elemText.value;
	update(valueTable, false);
    }
    elem.addEventListener("input", range2text);
    elem.addEventListener("change", function(e) {
	range2text(e);
	update(valueTable, true);
    });
    // Text to Range
    var text2range = function(e) {
	if (type === "log") {
	    elem.value = Math.log10(elemText.value);
	} else { // "direct"
	    elem.value = elemText.value;
	}
	valueTable[id] = elemText.value;
	update(valueTable, false);
    }
    elemText.addEventListener("input", text2range);
    elemText.addEventListener("change", function(e) {
	text2range(e);
	update(valueTable, true);
    });
}
