"use strict";
/*
 * 2018/04/27- (c) yoya@awm.jp
 */


document.addEventListener("DOMContentLoaded", function(event) {
    main();
});

function main() {
    console.debug("main");
    var container = document.getElementById("editor-render-0");
    var d1 = [], d2 = [];
    var dx = 0.01;
    d1 = [[0, 0], [1, 1]];
    for (var x = 0 ; x <= 1.0 ; x += dx) {
	var y = gamma_liner2sRGB(x);
	d2.push([x, y]);
    }
    
    // Draw Graph
    Flotr.draw(container, [ d1, d2 ], {
	colors: ["#888", "red"],
	shadowSize: 3,
	xaxis: {
	    minorTickFreq: 0.1,
	    min: -0.0, max: 1.0,
	    ticks: ["0.0", 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, "1.0"],
	},
	yaxis: {
	    ticks: [0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, "1.0"],
	    min: -0.0, max: 1.0,
	},
	grid: {
	    minorVerticalLines: true

	},
    });
}

function gamma_liner2sRGB(lv) {
    if (lv < 0.0031308) {
	var v = 12.92 * lv;
    } else {
	var v = 1.055 * Math.pow(lv, 1/2.4) - 0.055;
    }
    return v;
}
