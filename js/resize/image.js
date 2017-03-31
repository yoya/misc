"use strict";

function imageBinding(valueTable) {
    var srcCanvas = getById("srcCanvas");
    var srcCtx = srcCanvas.getContext("2d");
    var dstCanvas = getById("dstCanvas");
    var cancelEvent = function(e) {
	e.preventDefault();
	e.stopPropagation();
	return false;
    };
    document.addEventListener("dragover" , cancelEvent, false);
    document.addEventListener("dragenter", cancelEvent, false);
    document.addEventListener("drop"     , function(e) {
	e.preventDefault();
	var reader = new FileReader();
	reader.onload = function (evt) {
            var img = new Image();
            img.onload = function() {
		var width = img.width, height = img.height;
		srcCanvas.width  = width;
		srcCanvas.height = height;
		srcCtx.drawImage(img, 0, 0, width, height);
		resizeFunc(srcCanvas, dstCanvas, valueTable);
            }
            img.src = evt.target.result;
	    valueTable["img"] = img;
	}
	reader.readAsDataURL(e.dataTransfer.files[0]);
	return false;
    }, false);
}

function resizeFunc(srcCanvas, dstCanvas, valueTable) {
    var scale = parseFloat(valueTable["logScale"]);
    var srcWidth  = srcCanvas.width;
    var srcHeight = srcCanvas.height;
    var dstWidth  = Math.round(scale * srcWidth);
    var dstHeight = Math.round(scale * srcHeight);
    dstCanvas.width  = dstWidth;
    dstCanvas.height = dstHeight;
    var srcCtx = srcCanvas.getContext("2d");
    var dstCtx = dstCanvas.getContext("2d");
    var srcImageData = srcCtx.getImageData(0, 0, srcWidth, srcHeight);
    var dstImageData = dstCtx.createImageData(dstWidth, dstHeight);
    var dstData = dstImageData.data;
    for (var dstY = 0 ; dstY < dstHeight; dstY++) {
	for (var dstX = 0 ; dstX < dstWidth; dstX++) {
	    var dstOffset = 4 * (dstX + dstY * dstWidth);
	    var srcX = dstX / scale;
	    var srcY = dstY / scale;
	    var [r,g,b,a] = resizePixel(srcX, srcY, srcImageData, filterType);
	    dstData[dstOffset]   = r;
	    dstData[dstOffset+1] = g;
	    dstData[dstOffset+2] = b;
	    dstData[dstOffset+3] = a;
	}
    }
    dstCtx.putImageData(dstImageData, 0, 0);
}

function getPixel(imageData, x, y) {
    x = Math.round(x);
    y = Math.round(y);
    var data = imageData.data;
    var width  = imageData.width;
    var height = imageData.height;
    if (x < 0) {
	x = 0;
    } else if (width <= x) {
	x = width - 1;
    }
    if (y < 0) {
	y = 0;
    } else if (height <= y) {
	y = height - 1;
    }
    var offset = 4 * (x + y * width);
    return data.slice(offset, offset+4); // [r,g,b,a]
}

function resizePixel(srcX, srcY, srcImageData, filterType) {
    var rgba = getPixel(srcImageData, srcX, srcY);
    return rgba;
}
