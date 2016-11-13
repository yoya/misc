"use strict";
/*
 (c) 2016/11/13 - yoya@awm.jp
*/
function cssColor(rgb) {
    var [r, g, b] = rgb;
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

var rgbRainbow = [[255, 0, 0], [255, 255, 0], [0, 255, 0], [0, 255, 255], [0, 0, 255], [255, 0, 255], [255, 0, 0]];
var widthUnit = 256;

var ribbon = document.getElementById("ribbon");
var graph  = document.getElementById("graph");
var ribbonCtx = ribbon.getContext("2d");
var graphCtx  = graph.getContext("2d");
ribbon.width = graph.width = widthUnit * 6;
graphCtx.globalCompositeOperation = "lighter";

var ribbonGrad = ribbonCtx.createLinearGradient(0, 0, ribbon.width, 0);

var x = 0;
for (var i = 0 ; i < 3 ; i++) {
    var [rgb1, rgb2, rgb3] = rgbRainbow.slice(i*2, i*2+3);
    drawLine(graphCtx, x, 1, x+widthUnit, 1, cssColor(rgb1));
    drawLine(graphCtx, x, graph.height, x+widthUnit, 0, cssColor(rgb3));
    for (var j=0 ; j < widthUnit; j++, x++) {
	var color = rgbMorph(rgb1, rgb2, j / widthUnit);
	ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
    }
    drawLine(graphCtx, x, 0, x+widthUnit, graph.height, cssColor(rgb1));
    drawLine(graphCtx, x, 1, x+widthUnit, 1, cssColor(rgb3));
    for (var j=0 ; j < widthUnit; j++, x++) {
	var color = rgbMorph(rgb2, rgb3, j/ widthUnit);
	ribbonGrad.addColorStop(x/ribbon.width, cssColor(color));
    }
}

ribbonCtx.beginPath();
ribbonCtx.fillStyle = ribbonGrad;
ribbonCtx.rect(0, 0, ribbon.width, ribbon.height);
ribbonCtx.fill();
