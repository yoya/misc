"use strict";
/*
 (c) 2016/11/13 - yoya@awm.jp
*/
function cssColor(r,g,b) {
    if (r instanceof Array) {
	[r, g, b] = r;
    }
    return "rgb("+(r|0)+","+(g|0)+","+(b|0)+")";
}
function rgbMorph(rgb1, rgb2, ratio) {
    var [r1, g1, b1] = rgb1;
    var [r2, g2, b2] = rgb2;
    var rgb3 = [r1*(1-ratio)+r2*(ratio),
		g1*(1-ratio)+g2*(ratio),
		b1*(1-ratio)+b2*(ratio)];
    return rgb3;
}
function drawLine(ctx, x1, y1, x2, y2, color) {
    ctx.lineWidth=3;
    ctx.strokeStyle=color;
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.stroke();
}

var ribbon = document.getElementById("ribbon")
var graph  = document.getElementById("graph")

var ribbonCtx = ribbon.getContext("2d");
var graphCtx  = graph.getContext("2d");
graphCtx.globalCompositeOperation = "lighter";

var ribbonGrad = ribbonCtx.createLinearGradient(0, 0, ribbon.width, 0);
var x = 0;
var color = null;

var [rgb1, rgb2, rgb3] = [[255, 0, 0], [255, 255, 0], [0, 255, 0]];
for (var i=0 ; i < 256; i++, x++) {
    color = rgbMorph(rgb1, rgb2, i / 256);
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
}
for (var i=0 ; i < 256; i++, x++) {
    color = rgbMorph(rgb2, rgb3, i/ 256);
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
}
drawLine(graphCtx, 0, 1, 256, 1, cssColor(rgb1));
drawLine(graphCtx, 256, 0, 256*2, graph.height, cssColor(rgb1));
drawLine(graphCtx, 0, graph.height, 256, 0, cssColor(rgb3));
drawLine(graphCtx, 256, 1, 256*2, 1, cssColor(rgb3));

//

var [rgb1, rgb2, rgb3] = [[0, 255, 0], [0, 255, 255], [0, 0, 255]];
for (var i=0 ; i < 256; i++, x++) {
    color = rgbMorph(rgb1, rgb2, i / 256);
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
}
for (var i=0 ; i < 256; i++, x++) {
    color = rgbMorph(rgb2, rgb3, i/ 256);
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
}
drawLine(graphCtx, 256*2, 1, 256*3, 1, cssColor(rgb1));
drawLine(graphCtx, 256*3, 0, 256*4, graph.height, cssColor(rgb1));
drawLine(graphCtx, 256*2, graph.height, 256*3, 0, cssColor(rgb3));
drawLine(graphCtx, 256*3, 1, 256*4, 1, cssColor(rgb3));

var [rgb1, rgb2, rgb3] = [[0, 0, 255], [255, 0, 255], [255, 0, 0]];
for (var i=0 ; i < 256; i++, x++) {
    color = rgbMorph(rgb1, rgb2, i / 256);
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
}
for (var i=0 ; i < 256; i++, x++) {
    color = rgbMorph(rgb2, rgb3, i/ 256);
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
}
drawLine(graphCtx, 256*4, 1, 256*5, 1, cssColor(rgb1));
drawLine(graphCtx, 256*5, 0, 256*6, graph.height, cssColor(rgb1));
drawLine(graphCtx, 256*4, graph.height, 256*5, 0, cssColor(rgb3));
drawLine(graphCtx, 256*5, 1, 256*6, 1, cssColor(rgb3));


//

ribbonCtx.beginPath();
ribbonCtx.fillStyle = ribbonGrad;
ribbonCtx.rect(0, 0, ribbon.width, ribbon.height);
ribbonCtx.fill();
