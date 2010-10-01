<?php
  /*
   * (c) 2008/09/25- yoya@awm.jp
   */

class Y3D {
    private $distance_to_eye;
    private $obj_rotation;
    private $width, $height;
    private $canvas;
    private $color;
    function __construct($params) {
        $this->distance_to_eye = $params['distance_to_eye'];
        if (isset($params['obj_rotation'])) {
            $this->obj_rotation = $params['obj_rotation'];
        }
        $this->width  = $params['width'];
        $this->height = $params['height'];
        $this->canvas = imagecreatetruecolor($this->width, $this->height);
    }
    function rotate2D($x, $y, $degree) {
        $rad = 2 * pi() * $degree / 360;
        $x2 = $x * cos($rad) - $y * sin($rad);
        $y2 = $x * sin($rad) + $y * cos($rad);
        return array($x2, $y2);
    }
    function mapping3Dto2D($x, $y, $z) {
        // object rotation
        if (isset($this->obj_rotation)) {
            $r = $this->obj_rotation;
            if (isset($r['x'])) {
                list($y, $z) = $this->rotate2D($y, $z, $r['x']);
            }
            if (isset($r['y'])) {
                list($z, $x) = $this->rotate2D($z, $x, $r['y']);
            }
            if (isset($r['z'])) {
                list($x, $y) = $this->rotate2D($x, $y, $r['z']);
            }
        }
        // mapping to 2D
        $d = $this->distance_to_eye;
        $x3 = $d * $x / ($d - $z);
        $y3 = $d * $y / ($d - $z);
        //  centering
        $x3 += $this->width / 2;
        $y3 = ($this->height / 2) - $y3;
        return array($x3, $y3);
    }
    function setColor($r, $g, $b) {
        $this->color = imagecolorallocate($this->canvas, $r, $g, $b);
        
    }
    function drawPoint($x, $y, $z) {
        list($x2, $y2) = $this->mapping3Dto2D($x, $y, $z);
        imagesetpixel($this->canvas, $x2, $y2, $this->color);
    }
    function drawLine($x1, $y1, $z1, $x2, $y2, $z2) {
        $vector1 = $this->mapping3Dto2D($x1, $y1, $z1);
        $vector2 = $this->mapping3Dto2D($x2, $y2, $z2);
        imageline($this->canvas, $vector1[0], $vector1[1],
                  $vector2[0], $vector2[1], $this->color);
    }
    function outputpng($file = null) {
        if (is_null($file)) {
            imagepng($this->canvas);
        } else {
            imagepng($this->canvas, $file);
        }
    }
}
