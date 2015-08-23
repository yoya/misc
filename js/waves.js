"use strict";
// https://twitter.com/rabihalameddine/status/634491712149291009
var audio_ctx;

var fps = 24;
var round_speed = 1/3;
// var fps = 0.2;
// var round_speed = 1/40;

var wave_length = 1024;
var sampleRate = 44100;

var wave_list = [
    { canvas_id:'circle', type:'circle', color:"red",
      width:800, height:200,
      unit_center_x:100, unit_center_y:100, unit_radius:80,
    },
    { canvas_id:'rectangle', type:'rectangle', color:"blue",
      width:800, height:200,
      unit_center_x:100, unit_center_y:100, unit_radius:80 },
    { canvas_id:'hexagon', type:"hexagon", color:"#0c0",
      width:800, height:200,
      unit_center_x:100, unit_center_y:100, unit_radius:80,
    },
    { canvas_id:'triangle', type:"triangle", color:"#d0d",
      width:800, height:200,
      unit_center_x:100, unit_center_y:100, unit_radius:80,
    },
];

function draw_clear(ctx, x, y, width, height) {
    ctx.clearRect(x, y, width, height);
}

var scroll_speed = 4;
var scroll_x = 0;

function draw_scroll(ctx, wave, y) {
    ctx.save(); // save lineWidth
    var scroll_length = wave.width - wave.center_x * 2;
    if (scroll_x < 1) {
	wave.scrollData[0] = y;
    } else {
	do {
	    wave.scrollData.unshift(y);
	    if (wave.scrollData.length > scroll_length) {
		wave.scrollData.pop();
	    }
	    scroll_x -= 1;
	    y = undefined;
	} while (scroll_x >= 1)
    }
    scroll_x += scroll_speed;
    // draw
    ctx.strokeStyle = wave.color;
    ctx.lineWidth = 2;
    ctx.beginPath();
    
    for (var i = 0, n = wave.scrollData.length; i < n ; i++) {
	var x = wave.unit_center_x * 2 + 1 + 0.5 + i;
	var y = wave.scrollData[i];
	if (i === 0) {
	    ctx.moveTo(x, y);
	} else {
	    if (y !== undefined) {
		ctx.lineTo(x, y);
	    }
	}
    }
//    ctx.closePath();
    ctx.stroke();
    ctx.restore(); // restore lineWidth
}

function draw_line(ctx, x1, y1, x2, y2, color) {
    x1+= 0.5;
    y1+= 0.5;
    x2+= 0.5;
    y2+= 0.5;
    ctx.strokeStyle = color;
    ctx.beginPath();
    ctx.moveTo(x1, y1);
    ctx.lineTo(x2, y2);
    ctx.closePath();
    ctx.stroke();
}

function draw_polyline(ctx, x_a, y_a, color) {
    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(x_a[0] + 0.5, y_a[0] + 0.5);
    for (var i = 1, n = x_a.length ; i < n ; i++) {
	ctx.lineTo(x_a[i] + 0.5, y_a[i] + 0.5);
    }
    ctx.closePath();
    ctx.stroke();
    ctx.restore();
}

function draw_grid(ctx, x_a, y_a, color) {
    var x_len = x_a.length;
    var y_len = y_a.length;
    for (var i = 0 ; i < x_len ; i++) {
	var x = x_a[i];
	var y_first = y_a[0];
	var y_last  = y_a[y_len - 1];
	draw_line(ctx, x, y_first, x, y_last, color);
    }
    for (var i = 0 ; i < y_len ; i++) {
	var y = y_a[i];
	var x_first = x_a[0];
	var x_last  = x_a[x_len - 1];
	draw_line(ctx, x_first, y, x_last, y, color);
    }
}

function draw_rect(ctx, x, y, width, height, color) {
    ctx.save();
    x+= 0.5;
    y+= 0.5;
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.lineTo(x + width, y);
    ctx.lineTo(x + width, y + height);
    ctx.lineTo(x, y + height);
    ctx.lineTo(x, y);
//    ctx.closePath();
    ctx.stroke();
    ctx.restore();
}

function draw_circle(ctx, x, y, radius, color, isFill) {
    ctx.save(); // save lineWidth
    x+= 0.5;
    y+= 0.5;
    if (isFill) {
	ctx.fillStyle = color;
    } else {
	ctx.strokeStyle = color;
	ctx.lineWidth = 2;
    }
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, 2 * Math.PI, false);
    ctx.closePath();
    if (isFill) {
	ctx.fill();
    } else {
	ctx.stroke();
    }
    ctx.restore(); // restore lineWidth
}

function make_wavetable_circle(waveDataX, waveDataY) {
    var theta = 0;
    var theta_delta = 2 * Math.PI / wave_length;
    for (var i = 0 ; i < wave_length ; i++) {
	waveDataX[i] = Math.cos(theta);
	waveDataY[i] = Math.sin(theta);
	theta += theta_delta;
    }
}

function make_wavetable_rectangle(waveDataX, waveDataY) {
    var theta = 0;
    var theta_delta = 2 * Math.PI / wave_length;
    var theta_8 = 2 * Math.PI / 8;
    var x, y;
    for (var i = 0 ; i < wave_length; i++) {
	if ((theta < theta_8) || (theta > theta_8*7)) {
	    // right
	    x = 1;
	    y = Math.tan(theta);
	} else if (theta < theta_8 * 3) {
	    // top
	    x = - Math.tan(theta - theta_8*2);
	    y = 1;
	} else if (theta < theta_8 * 5) {
	    // left
	    x = - 1;
	    y = - Math.tan(theta - theta_8*4);
	} else {
	    // bottom
	    x = Math.tan(theta - theta_8*6);
	    y = - 1;
	}
	waveDataX[i] = x;
	waveDataY[i] = y;
	theta += theta_delta;
    }
}
function make_wavetable_hexagon(waveDataX, waveDataY) {
    var theta = 0;
    var theta_delta = 2 * Math.PI / wave_length;
    var theta_12 = 2 * Math.PI / 12;
    for (var i = 0 ; i < wave_length; i++) {
	var x, y;
	if ((theta < theta_12) || (theta > theta_12*11)) {
	    // right
	    x = 1;
	    y = Math.tan(theta);
	} else if (theta < theta_12 * 3) {
	    x = 1;
	    y = Math.tan(theta - theta_12*2);
	    var xy = rotateXY(x, y, theta_12*2);
	    x = xy[0];
	    y = xy[1];
	} else if (theta < theta_12 * 5) {
	    // left-top
	    x = 1;
	    y = Math.tan(theta - theta_12*4);
	    var xy = rotateXY(x, y, theta_12*4);
	    x = xy[0]
	    y = xy[1];
	} else if (theta < theta_12 * 7) {
	    // left
	    x = 1;
	    y = Math.tan(theta - theta_12*6);
	    var xy = rotateXY(x, y, theta_12*6);
	    x = xy[0]
	    y = xy[1];
	} else if (theta < theta_12 * 9) {
	    // left-bottom
	    x = 1;
	    y = Math.tan(theta - theta_12*8);
	    var xy = rotateXY(x, y, theta_12*8);
	    x = xy[0]
	    y = xy[1];
	} else {
	    // right-bottom
	    x = 1;
	    y = Math.tan(theta - theta_12*10);
	    var xy = rotateXY(x, y, theta_12*10);
	    x = xy[0]
	    y = xy[1];
	}
	waveDataX[i] = x;
	waveDataY[i] = y;
	theta += theta_delta;
    }
}
function make_wavetable_triangle(waveDataX, waveDataY) {
    var theta = 0;
    var theta_delta = 2 * Math.PI / wave_length;
    var theta_12 = 2 * Math.PI / 12;
    for (var i = 0 ; i < wave_length; i++) {
	var x, y;
	if ((theta < theta_12 * 3) || (theta > theta_12 * 11)) {
	    // right-trop
	    x = - 1 / Math.tan(theta - theta_12*4);
	    y = -1;
	    var xy = rotateXY(x, y, theta_12*4);
	    x = xy[0];
	    y = xy[1];
	} else if (theta < theta_12 * 7) {
	    x = - 1 / Math.tan(theta - theta_12*8);
	    y = -1;
	    var xy = rotateXY(x, y, theta_12*8);
	    x = xy[0];
	    y = xy[1];
	} else {
	    // bottom
	    x = - 1 / Math.tan(theta);
	    y = -1;
	}
	waveDataX[i] = x;
	waveDataY[i] = y;
	theta += theta_delta;
    }
}
function make_wavetable(wave) {
    var waveDataX = new Float32Array(wave_length);
    var waveDataY = new Float32Array(wave_length);
    switch (wave.type) {
    case 'circle':
	make_wavetable_circle(waveDataX, waveDataY);
	break;
    case 'rectangle':
	make_wavetable_rectangle(waveDataX, waveDataY);
	break;
    case 'hexagon':
	make_wavetable_hexagon(waveDataX, waveDataY);
	break;
    case 'triangle':
	make_wavetable_triangle(waveDataX, waveDataY);
	break;
    }
    wave.waveDataX = waveDataX;
    wave.waveDataY = waveDataY;
}

function get_wave(wave, t) {
    var i = wave_length * t / (2*Math.PI);
    i = (i | 0) % wave_length;
    var x = wave.waveDataX[i];
    var y = wave.waveDataY[i];
    return [x,y];
}

function getToneRatio(x, wave) {
    var tone = x * 2 + 20;
    return tone / (sampleRate / wave_length)
}
function getVolume(y, wave) {
    return (wave.height - y) / wave.height / 1000;
}

function startHandler(e) {
    var target = e.target;
    var wave = target.wave;
    var toneRatio = getToneRatio(e.offsetX, wave);
    var volume = getVolume(e.offsetY, wave);
    wave.canvas.style.backgroundColor = "#fee";

    if (wave.audioNote !== null) {
	wave.audioNote.stop();
	wave.audioNote = null;
	wave.audioGain = null;
    }
//
    var src = audio_ctx.createBufferSource();
    var gain = audio_ctx.createGain();
    src.buffer = wave.audioBuffer;
    src.loop = true;
    src.playbackRate.value = toneRatio;
    gain.gain.value = volume;
    src.connect(gain);
    gain.connect(audio_ctx.destination);
    src.start(0);
    wave.audioNote = src;
    wave.audioGain = gain;
}

function moveHandler(e) {
    var target = e.target;
    var wave = target.wave;
    if (wave.audioNote === null) {
	return ;
    }
    if (wave.audioNote.playbackRate)
    var target = e.target;
    var wave = target.wave;
    var toneRatio = getToneRatio(e.offsetX, wave);
    var volume = getVolume(e.offsetY, wave);
    wave.audioNote.playbackRate.value = toneRatio;
    wave.audioGain.gain.value = volume;
}

function stopHandler(e) {
    var target = e.target;
    var wave = target.wave;
    if (wave.audioNote === null) {
	return ;
    }
    wave.canvas.style.backgroundColor = "#fff";
    wave.audioNote.stop(0);
    wave.audioNote.disconnect(wave.audioGain);
    wave.audioGain.disconnect(audio_ctx.destination);
    wave.audioNote = null;
    wave.audioGain = null;
}

function init_waves_animation() {
    console.debug("start!!");
    window.AudioContext = window.AudioContext||window.webkitAudioContext;
    audio_ctx = new AudioContext();
    for (var i = 0, n = wave_list.length ; i < n ; i++) {
	var wave = wave_list[i];
	wave.canvas = document.getElementById(wave.canvas_id);
	wave.ctx = wave.canvas.getContext("2d");
	wave.scrollData = [];
	make_wavetable(wave);
	wave.canvas.addEventListener("mousedown", startHandler, false);
	wave.canvas.addEventListener("mousemove", moveHandler, false);
	wave.canvas.addEventListener("mouseup", stopHandler, false);
	wave.canvas.addEventListener("mouseleave", stopHandler, false);

	wave.canvas.wave = wave;
	//
	var buf = audio_ctx.createBuffer(1, wave_length, sampleRate);
	var data = buf.getChannelData(0);
	for (var j = 0; j < wave_length ; j++) {
	    data[j] = wave.waveDataY[j] * 1000;
	}
	wave.audioBuffer = buf;
	wave.audioNote = null;
	wave.audioGain = null;
    }
    console.debug(wave_list);
    //
    initial_draw();
}

function initial_draw() {
    for (var i = 0, n = wave_list.length ; i < n ; i++) {
	var wave = wave_list[i];
	var x2 = wave.width - 1;
	var y2 = wave.height - 1;
	var unit_x1 = wave.unit_center_x - wave.unit_radius;
	var unit_y1 = wave.unit_center_y - wave.unit_radius;
	var unit_x2 = wave.unit_center_x + wave.unit_radius;
	var unit_y2 = wave.unit_center_y + wave.unit_radius;
	var unit_center_x = wave.unit_center_x;
	var unit_center_y = wave.unit_center_y;
	var unit_x3 = wave.unit_center_x * 2;
	var unit_y3 = wave.unit_center_y * 2;
	draw_grid(wave.ctx,
		  [0, unit_x1, unit_center_x, unit_x2, unit_x3, x2],
		  [0, unit_y1, unit_center_y, unit_y2, unit_y3, y2],
		  "#ddd");
	switch(wave.type) {
	case 'circle':
	    var center_x = wave.unit_center_x;
	    var center_y = wave.unit_center_y;
	    draw_circle(wave.ctx,
			center_x, center_y, wave.unit_radius, wave.color);
	    break;
	case 'rectangle':
	    var x = wave.unit_center_x - wave.unit_radius;
	    var y = wave.unit_center_y - wave.unit_radius;
	    var width = wave.unit_radius * 2;
	    var height = wave.unit_radius * 2;
	    draw_rect(wave.ctx, x, y, width, height, wave.color);
	    break;
	case 'hexagon':
	    var hexagon_radius =  wave.unit_radius / Math.cos(2 * Math.PI / 12);
	    var x_a = [], y_a = [];
	    var center_x = wave.unit_center_x;
	    var center_y = wave.unit_center_y;
	    for (var j = 0 ; j < 6 ; j++) {
		var t = (2 * Math.PI / 12) + j * (2 * Math.PI / 6);
		var x = center_x + hexagon_radius * Math.cos(t)
		var y = center_y - hexagon_radius * Math.sin(t);
		x_a.push(x);
		y_a.push(y);
	    }
	    draw_polyline(wave.ctx, x_a, y_a, wave.color);
	    break;
	case 'triangle':
	    var triangle_radius =  wave.unit_center_x;
	    var x_a = [], y_a = [];
	    var center_x = wave.unit_center_x;
	    var center_y = wave.unit_center_y;
	    for (var j = 0 ; j < 3 ; j++) {
		var t = - (2 * Math.PI / 12) + j * (2 * Math.PI / 3);
		var x = center_x + triangle_radius * Math.cos(t)
		var y = center_y - triangle_radius * Math.sin(t);
		x_a.push(x);
		y_a.push(y);
	    }
	    draw_polyline(wave.ctx, x_a, y_a, wave.color);
	    break;
	}
    }
}

function start_waves_animation() {
    setInterval(tick_waves_animation, 1000/fps);
}

var theta_delta = round_speed * 2 * Math.PI / fps;
var theta = 0;

function tick_waves_animation() {
    for (var i = 0, n = wave_list.length ; i < n ; i++) {    
	var wave = wave_list[i];
	draw_clear(wave.ctx, 0, 0, wave.width, wave.height);
    }
    initial_draw();
    for (var i = 0, n = wave_list.length ; i < n ; i++) {
	var wave = wave_list[i];
	switch (wave.type) {
	    case 'circle':
	    tick_waves_animation_circle(wave);
	    break;
	    case 'rectangle':
	    tick_waves_animation_rectangle(wave);
	    break;
	    case 'hexagon':
	    tick_waves_animation_hexagon(wave);
	    break;
	    case 'triangle':
	    tick_waves_animation_triangle(wave);
	    break;
	}
    }
    theta += theta_delta;
    if (theta > 2 * Math.PI) {
	theta -= 2 * Math.PI;
    }
}

function tick_waves_animation_circle(wave) {
    var center_x = wave.unit_center_x;
    var center_y = wave.unit_center_y;
    var xy = get_wave(wave, theta);
    var x = center_x + wave.unit_radius * xy[0];
    var y = center_y - wave.unit_radius * xy[1];
    draw_line(wave.ctx, center_x, center_y, x, y, wave.color);
    draw_circle(wave.ctx, center_x, center_y, 3, wave.color, true);
    draw_circle(wave.ctx, x, y, 3, wave.color, true);
    //
    var unit_x3 = wave.unit_center_x * 2;
    draw_line(wave.ctx, x, y, unit_x3, y, wave.color);
    draw_circle(wave.ctx, unit_x3, y, 3, wave.color, true);
    draw_scroll(wave.ctx, wave, y);
}

function tick_waves_animation_rectangle(wave) {
    var center_x = wave.unit_center_x;
    var center_y = wave.unit_center_y;
    var xy = get_wave(wave, theta);
    var x = center_x + wave.unit_radius * xy[0];
    var y = center_y - wave.unit_radius * xy[1];
    draw_line(wave.ctx, center_x, center_y, x, y, wave.color);
    draw_circle(wave.ctx, center_x, center_y, 3, wave.color, true);
    draw_circle(wave.ctx, x, y, 3, wave.color, true);
    //
    var unit_x3 = wave.unit_center_x * 2;
    draw_line(wave.ctx, x, y, unit_x3, y, wave.color);
    draw_circle(wave.ctx, unit_x3, y, 3, wave.color, true);
    draw_scroll(wave.ctx, wave, y);
}
function rotateXY(x, y, t) {
    var x2 = x * Math.cos(t) - y * Math.sin(t);
    var y2 = x * Math.sin(t) + y * Math.cos(t);
    return [x2, y2];
}

function tick_waves_animation_hexagon(wave) {
    var center_x = wave.unit_center_x;
    var center_y = wave.unit_center_y;
    var xy = get_wave(wave, theta);
    var x = center_x + wave.unit_radius * xy[0];
    var y = center_y - wave.unit_radius * xy[1];
    draw_line(wave.ctx, center_x, center_y, x, y, wave.color);
    draw_circle(wave.ctx, center_x, center_y, 3, wave.color, true);
    draw_circle(wave.ctx, x, y, 3, wave.color, true);
    //
    var unit_x3 = wave.unit_center_x * 2;
    draw_line(wave.ctx, x, y, unit_x3, y, wave.color);
    draw_circle(wave.ctx, unit_x3, y, 3, wave.color, true);
    draw_scroll(wave.ctx, wave, y);
}

function tick_waves_animation_triangle(wave) {
    var center_x = wave.unit_center_x;
    var center_y = wave.unit_center_y;
    var xy = get_wave(wave, theta);
    var x = center_x + wave.unit_center_x/2 * xy[0];
    var y = center_y - wave.unit_center_x/2 * xy[1];
    draw_line(wave.ctx, center_x, center_y, x, y, wave.color);
    draw_circle(wave.ctx, center_x, center_y, 3, wave.color, true);
    draw_circle(wave.ctx, x, y, 3, wave.color, true);
    //
    var unit_x3 = wave.unit_center_x * 2;
    draw_line(wave.ctx, x, y, unit_x3, y, wave.color);
    draw_circle(wave.ctx, unit_x3, y, 3, wave.color, true);
    draw_scroll(wave.ctx, wave, y);
}
