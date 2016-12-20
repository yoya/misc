;
function imageBinding(valueTable) {
    var srcCanvas = getById("srcCanvas");
    var srcCtx = srcCanvas.getContext("2d");
    var dstCanvas = getById("dstCanvas");
    // console.log(srcCanvas);
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
		var scale = parseInt(valueTable["logScale"], 10);
		console.log(valueTable);
		var width = img.width, height = img.height;
		srcCanvas.width  = width;
		srcCanvas.height = height;
		srcCtx.drawImage(img, 0, 0, width, height);
		resizeFunc(srcCanvas, dstCanvas, valueTable);
            }
            img.src = evt.target.result;
	    valueTable["img"] = img;
	}
	// console.log(e.dataTransfer.files[0]);
	reader.readAsDataURL(e.dataTransfer.files[0]);
	return false;
    }, false);
}

function resizeFunc(srcCanvas, dstCanvas, valueTable) {
    var scale = parseInt(valueTable["logScale"], 10);
    console.log("convertFunc");
    var srcWidth  = srcCanvas.width;
    var srcHeight = srcCanvas.height;
    var dstWidth  = scale * srcWidth;
    var dstHeight = scale * srcHeight;
    dstCanvas.width  = dstWidth;
    dstCanvas.height = dstHeight;
    // console.log(dstWidth, dstHeight);
    var srcCtx = srcCanvas.getContext("2d");
    var dstCtx = dstCanvas.getContext("2d");
    var srcImageData = srcCtx.getImageData(0, 0, srcWidth, srcHeight);
    var dstImageData = dstCtx.createImageData(srcImageData);
    var srcData = srcImageData.data;
    var dstData = dstImageData.data;
    for (var dstY = 0 ; dstY < dstHeight; dstY++) {
	for (var dstX = 0 ; dstX < dstWidth; dstX++) {
	    var dstOffset = 4 * (dstX + dstY * dstWidth);
	    var [r,g,b,a] = resizePixel(dstX, dstY, srcImageData, filterType);
	    dstData[dstOffset]   = r;
	    dstData[dstOffset+1] = g;
	    dstData[dstOffset+2] = b;
	    dstData[dstOffset+3] = a;
	}
    }
    dstCtx.putImageData(dstImageData, 0, 0);
    //
}

function resizePixel(dstX, dstY, srcImageData, filterType) {
    var srcData = srcImageData.data;
    var srcWidth  = srcImageData.width;
    var srcHeight = srcImageData.height;
    var srcX = dstX;
    var srcY = dstY;
    var srcOffset = 4 * (srcX + srcY * srcWidth);
    r = srcData[srcOffset];
    g = srcData[srcOffset+1];
    b = srcData[srcOffset+2];
    a = srcData[srcOffset+3];
    return [r, g, b, a];
}
