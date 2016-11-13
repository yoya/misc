function cssColor(r,g,b) { return "rgb("+r+","+g+","+b+")"; }
function drawLine(ctx, x1, y1, x2, y2, color) {
    ctx.strokeWidth=1;
    ctx.strokeStyle=color
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

ribbonCtx.beginPath();
var ribbonGrad = ribbonCtx.createLinearGradient(0, 0, ribbon.width, 0);
x = 0;
r = 255;

for (var g=0 ; g <= 255; g++, x++) {
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(r, g, 0));
}
drawLine(graphCtx, 0, 1, x, 1, "rgb(255, 0, 0)");
drawLine(graphCtx, 0, graph.height, x, 0, "rgb(0, 255, 0)");

for (var r=255 ; r >= 0; r--, x++) {
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(r, g, 0));
}
drawLine(graphCtx, 256, 0, 256*2, graph.height, "rgb(255, 0, 0)");
drawLine(graphCtx, 256, 1, 256*2, 1, "rgb(0, 255, 0)");

g = 255;
for (var b=0 ; b <= 255; b++, x++) {
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(0, g, b));
}
drawLine(graphCtx, 256*2, 1, 256*3, 1, "rgb(0, 255, 0)");
drawLine(graphCtx, 256*2, graph.height, 256*3, 0, "rgb(0, 0, 255)");

b = 255;
for (var g=255 ; g >= 0; g--, x++) {
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(0, g, b));
}
drawLine(graphCtx, 256*3, 1, 256*4, 1, "rgb(0, 0, 255)");
drawLine(graphCtx, 256*3, 0, 256*4, graph.height, "rgb(0, 255, 0)");

b = 255;
for (var r=0 ; r <= 255; r++, x++) {
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(r, 0, b));
}
drawLine(graphCtx, 256*4, 1, 256*5, 1, "rgb(0, 0, 255)");
drawLine(graphCtx, 256*4, graph.height, 256*5, 0, "rgb(255, 0, 0)");

r = 255;
for (var b=255 ; b >= 0; b--, x++) {
    ribbonGrad.addColorStop(x/ribbon.width, cssColor(r, 0, b));
}
drawLine(graphCtx, 256*5, 1, 256*6, 1, "rgb(255, 0, 0)");
drawLine(graphCtx, 256*5, 0, 256*6, graph.height, "rgb(0, 0, 255)");

//

ribbonCtx.fillStyle = ribbonGrad;
ribbonCtx.rect(0, 0, ribbon.width, ribbon.height);
ribbonCtx.fill();
