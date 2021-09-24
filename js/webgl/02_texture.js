"use strict";

main();

function main() {
    var image = new Image();
    image.src = "./netscape.png";  // 同じドメインじゃないとダメ！
    image.onload = function() {
        render(image);
  }
}

function render(image) {
    let canvas = document.querySelector("#canvas");
    let width = image.width, height = image.height;
    canvas.width = width;
    canvas.height = height;
    width = canvas.width  = canvas.clientWidth;
    height = canvas.height = canvas.clientHeight;
    let gl = canvas.getContext("webgl");
    if (!gl) {
        return;
    }
    let vertexShaderSource = document.querySelector("#vertex-shader-2d").text;
    let fragmentShaderSource = document.querySelector("#fragment-shader-2d").text;
    
    let vertexShader = createShader(gl, gl.VERTEX_SHADER, vertexShaderSource);
    let fragmentShader = createShader(gl, gl.FRAGMENT_SHADER, fragmentShaderSource);
    let program = createProgram(gl, vertexShader, fragmentShader);

  // look up where the vertex data needs to go.
    let positionLocation = gl.getAttribLocation(program, "a_position");
    let texcoordLocation = gl.getAttribLocation(program, "a_texCoord");
    
    // Create a buffer to put three 2d clip space points in
    let positionBuffer = gl.createBuffer();
    
    // Bind it to ARRAY_BUFFER (think of it as ARRAY_BUFFER = positionBuffer)
    gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);
    // Set a rectangle the same size as the image.
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
        0, 0,
        width, 0,
        0, height,
        0, height,
        width, 0,
        width, height,
    ]), gl.STATIC_DRAW);

    // provide texture coordinates for the rectangle.
    let texcoordBuffer = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, texcoordBuffer);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([
        0.0,  0.0,
        1.0,  0.0,
        0.0,  1.0,
        0.0,  1.0,
        1.0,  0.0,
        1.0,  1.0,
    ]), gl.STATIC_DRAW);

    // Create a texture.
    let texture = gl.createTexture();
    gl.bindTexture(gl.TEXTURE_2D, texture);

    // Set the parameters so we can render any size image.
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
    gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);

    // Upload the image into the texture.
    gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);

    // lookup uniforms
    let resolutionLocation = gl.getUniformLocation(program, "u_resolution");

    resizeCanvasToDisplaySize(gl.canvas);

    // Tell WebGL how to convert from clip space to pixels
    //gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
    gl.viewport(0, 0, width, height);

    // Clear the canvas
    gl.clearColor(0, 0, 0, 0);
    gl.clear(gl.COLOR_BUFFER_BIT);

    // Tell it to use our program (pair of shaders)
    gl.useProgram(program);

    // Turn on the position attribute
    gl.enableVertexAttribArray(positionLocation);

    // Bind the position buffer.
    gl.bindBuffer(gl.ARRAY_BUFFER, positionBuffer);

    // Tell the position attribute how to get data out of positionBuffer (ARRAY_BUFFER)
    let size = 2;          // 2 components per iteration
    let type = gl.FLOAT;   // the data is 32bit floats
    let normalize = false; // don't normalize the data
    let stride = 0;        // 0 = move forward size * sizeof(type) each iteration to get the next position
    let offset = 0;        // start at the beginning of the buffer
    gl.vertexAttribPointer(
        positionLocation, size, type, normalize, stride, offset);

    // Turn on the texcoord attribute
    gl.enableVertexAttribArray(texcoordLocation);

    // bind the texcoord buffer.
    gl.bindBuffer(gl.ARRAY_BUFFER, texcoordBuffer);

    // Tell the texcoord attribute how to get data out of texcoordBuffer (ARRAY_BUFFER)
    size = 2;          // 2 components per iteration
    type = gl.FLOAT;   // the data is 32bit floats
    normalize = false; // don't normalize the data
    stride = 0;        // 0 = move forward size * sizeof(type) each iteration to get the next position
    offset = 0;        // start at the beginning of the buffer
    gl.vertexAttribPointer(texcoordLocation, size, type, normalize, stride, offset);

    // set the resolution
    gl.uniform2f(resolutionLocation, width, height);

    // Draw the rectangle.
    let primitiveType = gl.TRIANGLES;
    offset = 0;
    let count = 6;
    gl.drawArrays(primitiveType, offset, count);
}

function createShader(gl, type, source) {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    const success = gl.getShaderParameter(shader, gl.COMPILE_STATUS);
    if (success) {
        return shader;
    }
    console.log(gl.getShaderInfoLog(shader));
    gl.deleteShader(shader);
}

function createProgram(gl, vertexShader, fragmentShader) {
    const program = gl.createProgram();
    gl.attachShader(program, vertexShader);
    gl.attachShader(program, fragmentShader);
    gl.linkProgram(program);
    const success = gl.getProgramParameter(program, gl.LINK_STATUS);
    if (success) {
        return program;
    }
    console.log(gl.getProgramInfoLog(program));
    gl.deleteProgram(program);
}

  function resizeCanvasToDisplaySize(canvas) {
      const width  = canvas.clientWidth;
      const height = canvas.clientHeight;
      if (canvas.width !== width ||  canvas.height !== height) {
          canvas.width  = width;
          canvas.height = height;
          return true;
      }
      return false;
  }
