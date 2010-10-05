<?php

  /*
   * 2010/07/24- (c) yoya@awm.jp
   */

require_once dirname(__FILE__).'/BitIO.php';

class YSwf {
    //
    var $_bio = null;
    var $_header_info = null;
    var $_movie_info_list = array(); // offset, length, ...
    //
    var $_tagName; // all known tags
    var $_tagHasId;
    var $_tagMisc = array(
        0  => 'End',
        1  => 'ShowFrame',
        8  => 'JPEGTables',
        9  => 'SetBackgroundColor',
        12 => 'DoAction',
        28 => 'RemoveObject2',
        34 => 'DefineButton2',
        43 => 'FrameLabel',
        );
    var $_tagSprite = array(
        39 => 'DefineSprite',
        );
    var $_tagJpeg = array(
        6  => 'DefineBitsJPEG',
        21 => 'DefineBitsJPEG2',
        35 => 'DefineBitsJPEG3',
        );
    var $_tagLossless = array(
        20 => 'DefineLossless',
        36 => 'DefineLossless2',
        );
    var $_tagShape = array(
        2  => 'DefineShape',
        22 => 'DefineShape2',
        32 => 'DefineShape3',
        46 => 'DefineMorphShape',
        );
    var $_tagPlace = array(
        4  => 'PlaceObject',
        26 => 'PlaceObject2',
        );
    
    // constructor
    function YSwf() {
        $this->_tagName = $this->_tagSprite + $this->_tagShape +
            $this->_tagPlace +
            $this->_tagJpeg + $this->_tagLossless + $this->_tagMisc;
        $this->_tagHasId = $this->_tagSprite + $this->_tagShape +
            $this->_tagJpeg + $this->_tagLossless;
    }
    
    
    /*
     * input swf data
     */
    function input($data) {
        if (substr($data, 0, 3) === 'CWS') { // XXX
            $head8byte = substr($data, 0, 8);
            $data = $head8byte.gzuncompress(substr($data, 8));
        }
        $this->_bio = new BitIO();
        $this->_bio->input($data);
        // header
        $ret = $this->_validate_header($data);
        $ret = $this->_get_header_length($data);
        if ($ret === false) {
            trigger_error("get_header_length failed");
            return $ret;
        }
        $this->_header_info = array('length' => $ret);
        // movie
        $offset = $ret;
        $ret = $this->_input_movie($data, $offset, strlen($data) - $offset, $this->_movie_info_list);
        return $ret;
    }
    function _input_movie($data, $offset, $length, &$movie_info_list) {
        while (true) {
            $ret = $this->_get_movie_info($data, $offset);
            if ($ret === false) {
                trigger_error("get_movie_info failed");
                return $ret;
            }
            $movie_info = $ret;
            $tag = $movie_info['tag'];
            $length = $movie_info['length'];
            $tag_and_length_size = $movie_info['tag_and_length_size'];
            if ($tag == 39) { // SpriteTag (MovieClip)
                // Sprite is nest movie tag
                $movie_info['id'] = $this->_bio->getUI16LE($offset + $tag_and_length_size);
                $movie_info['frame_count'] = $this->_bio->getUI16LE();
                $movie_offset = $tag_and_length_size + 2 + 2;
                $ret = $this->_input_movie($data, $offset + $movie_offset, $length - $movie_offset, $movie_info['movie']);
                if ($ret === false) {
                    return false;
                }
            }
            $movie_info_list[] = $movie_info;
            if ($tag == 0) { // End Tag
                break;
            }
            if ($length <= 0) { // Error
                trigger_error("movie_info length <= 0");
                break;
            }
            $offset += $length;
        }
    }
    function _validate_header($data) {
        // validate
        $magic = $this->_bio->getData(3, 0);
        if (($magic != 'FWS') && ($magic != 'CWS')) {
            trigger_error("no FWS,CWS($magic)");
            return false;
        }
        $version = $this->_bio->getUI8(3);
        if ($version > 10) {
            trigger_error("version($version) must be le 10");
            return false;
        }
        $file_length = $this->_bio->getUI32LE(4);
        $data_length = $this->_bio->getLength();
        if ($file_length != $data_length) {
            trigger_error("file_length($file_length) != data length($data_length");
            return false;
        }
        return true;
    }
    function _get_header_length($data) {
        $rect_field_bit_width = 5 + (4 * ($this->_bio->getUI8(8) >> 3));
        // bit width => byte length
        $rect_field_length  = ceil($rect_field_bit_width / 8);
        return 8 + $rect_field_length + 4;
    }
    function _get_movie_info($data, $offset) {
        $id = null;
        $id_ref = null;
        $tag_and_length = $this->_bio->getUI16LE($offset);
        $tag = ($tag_and_length >> 6) & 0x3ff;
        $length = $tag_and_length & 0x3f;
        if ($length < 0x3f) {
            $tag_and_length_size = 2;
        } else {
            $length = $this->_bio->getUI32LE();
            $tag_and_length_size = 2 + 4;
        }
        $length = $tag_and_length_size + $length;
        if (isset($this->_tagHasId[$tag])) {
                $id = $this->_bio->getUI16LE($offset + $tag_and_length_size);
        } else {
            switch ($tag) {
              case 4:  // PlaceObject
                $id_ref = $this->_bio->getUI16LE($offset + $tag_and_length_size);
                break;
              case 26: // PlaceObject2
                $flag = $this->_bio->getUI8($offset + $tag_and_length_size);
                if ($flag & 2) { // place_has_id_ref 
                    $id_ref = $this->_bio->getUI16LE($offset + $tag_and_length_size + 3);
                }
                break;
              default:
                break;
            }
        }
        $movie_info = array('tag' => $tag,
                            'offset' => $offset, 'length' => $length,
                            'tag_and_length_size' => $tag_and_length_size);
        if (! is_null($id)) {
            $movie_info['id'] = $id;
        }
        if (! is_null($id_ref)) {
            $movie_info['id_ref'] = $id_ref;
        }
        return $movie_info;
    }

    /*
     * output swf data
     */
    function output() {
        // header
        if (isset($this->_header_info['replaced'])) {
            $header_data = $this->_header_info['data'];
        } else {
            $this->_bio->setOffset(0, 0);
            $header_data = $this->_bio->getData($this->_header_info['length']);
            if (strlen($header_data) != $this->_header_info['length']) {
                trigger_error("output: getData Failed");
                return false;
            }
        }
        $movie_data = $this->_output_movie($this->_movie_info_list);
        $file_length = strlen($header_data) + strlen($movie_data);
        $file_length_data = $this->_bio->toUI32LE($file_length);
        $header_data = substr_replace($header_data, $file_length_data, 4, 4);
        return $header_data . $movie_data;
    }
    function _output_movie($movie_info_list) {
        $data = '';
        foreach ($movie_info_list as $movie_info) {
            if (isset($movie_info['replaced'])) {
                $tag = $movie_info['tag'];
                if ($tag == 39) { // Sprite
                    $movie_data =  $this->_bio->toUI16LE($movie_info['id']);
                    $movie_data .= $this->_bio->toUI16LE($movie_info['frame_count']);
                    $movie_data .= $this->_output_movie($movie_info['movie']);
                    $length = strlen($movie_data);
                    if ($length < 0x3f) {
                        $tag_and_length = ($tag << 6) | $length;
                        $data .= $this->_bio->toUI16LE($tag_and_length);
                    } else {
                        $tag_and_length = ($tag << 6) | 0x3f;
                        $data .= $this->_bio->toUI16LE($tag_and_length);
                        $data .= $this->_bio->toUI32LE($length);
                    }
                    $data .= $movie_data;
                } else {
                    // Sprite 以外は只の連結
                    $data .= $movie_info['data'];
                }
            } else {
                // 編集していない部分は元のデータを連結
                $this->_bio->setOffset($movie_info['offset'], 0);
                $d = $this->_bio->getData($movie_info['length']);
                if (strlen($d) != $movie_info['length']) {
                    trigger_error("_output_movie: getData failed ($ret != ".$movie_info['length']);
                }
                $data .= $d;
            }
        }
        return $data;
    }
    /*
     * tag contents checksum
     */
    function _set_checksum_tag($tag_table) {
        foreach ($this->_movie_info_list as &$movie_info) {
            $tag = $movie_info['tag'];
            if (isset($tag_table[$tag])) {
                $offset = $movie_info['offset'];
                $length = $movie_info['length'];
                $tag_and_length_size = $movie_info['tag_and_length_size'];
                $this->_bio->setOffset($offset + $tag_and_length_size + 2, 0);
                $d = $this->_bio->getData($length - $tag_and_length_size - 2);
                if (strlen($d) != $length - $tag_and_length_size - 2) {
                    trigger_error("_set_checksum_tag: getData failed(".strlen($d)." != $length - $tag_and_length_size - 2)");
                    return false;
                }
                $movie_info['checksum'] = crc32($d);
            }
        }
    }
    // image_unique
    function set_image_checksum() {
        $this->_set_checksum_tag($this->_tagJpeg + $this->_tagLossless);
    }
    function set_shape_checksum() {
        $this->_set_checksum_tag($this->_tagShape);
    }
    function unique_image_by_checksum() {
        ; // edit id shape => jpeg or lossless
    }
    // shape unique
    function _make_uniq_shape_map($movie_info_list, &$shape_checksum_table, &$unique_shape_map) {
        foreach ($movie_info_list as $movie_info) {
            $tag = $movie_info['tag'];
            if ($tag == 39) { // Sprite
                $this->_make_uniq_shape_map($movie_info['movie'], $shape_checksum_table, $unique_shape_map);
            } else {
                if (isset($this->_tagShape[$tag])) {
                    $id = $movie_info['id'];
                    $checksum = $movie_info['checksum'];
                    if (isset($shape_checksum_table[$checksum])) {
                        // checksum が同じ id が前にある場合
                        $unique_shape_map[$id] = $shape_checksum_table[$checksum];
                    } else {
                        $shape_checksum_table[$checksum] = $id;
                    }
                }
            }
        }
        return $unique_shape_map;
    }
    function _unique_shape_by_map(&$movie_info_list, &$unique_shape_map) {
        $replaced = false;
        foreach ($movie_info_list as $idx => &$movie_info) {
            $tag = $movie_info['tag'];
            if (isset($this->_tagShape[$tag])) {
                // (重複する) Shape tag を削除する
                $id = $movie_info['id'];
                if (isset($unique_shape_map[$id])) {
                    unset($movie_info_list[$idx]);
                    $replaced = true;
                }
            } else if (isset($this->_tagPlace[$tag]) && isset($movie_info['id_ref'])) {
                // Place tag の (Shape が重複する) ref ID を入れ替える
                $id_ref = $movie_info['id_ref'];
                if (isset($unique_shape_map[$id_ref])) {
                    $tag_and_length_size = $movie_info['tag_and_length_size'];
                    $replaced_id_ref = $unique_shape_map[$id_ref];
                    switch ($tag) {
                      case 4:  // PlaceObject
                        $id_ref_offset = 0;
                        break;
                      case 26: // PlaceObject2
                        $id_ref_offset = 3;
                        break;
                    }
                    $movie_info['id_ref'] = $replaced_id_ref;
                    $this->_bio->putUI16LE($replaced_id_ref, $movie_info['offset'] + $tag_and_length_size + $id_ref_offset);
                    $replaced = true;
                }
            } else if ($tag == 39) { // Sprite
                $ret = $this->_unique_shape_by_map($movie_info['movie'], $unique_shape_map);
                if ($ret == true) {
                    // showframe は変化しないので frame_count は変化なし
                    $movie_info['replaced'] = true;
                    $replaced = true;
                }
            }
        }
        return $replaced;
    }
    function unique_shape_by_checksum($opts) {
        $shape_checksum_table = array();
        $unique_shape_map = array();
        $unique_shape_map = $this->_make_uniq_shape_map($this->_movie_info_list, $this->_movie_info_list, $shape_checksum_table, $unique_shape_map);
        if (! empty($opts['debug'])) {
            echo "unique_shape_map".PHP_EOL;
            if (count($unique_shape_map) == 0) {
                echo "  no data".PHP_EOL;
            } else {
                foreach ($unique_shape_map as $from => $to) {
                    echo "  $from => $to".PHP_EOL;
                }
            }

        }
        $this->_unique_shape_by_map($this->_movie_info_list, $unique_shape_map);
    }

    /*
     * dump swf data structure
     * TODO: replaced 対応
     */
    function dump($opts = array()) {
        $header_length = $this->_header_info['length'];
        echo "head_length=".$header_length.PHP_EOL;
        if (isset($opts['hexdump'])) {
            $this->_bio->hexdump(0, $header_length);
            echo PHP_EOL; // for look attractive
        }
        $this->dump_movie($this->_movie_info_list, 0, $opts);
    }
    function dump_movie(&$movie_info_list, $indent, $opts) {
        foreach ($movie_info_list as $movie_info) {
            echo str_pad('', 2 * $indent, ' ');
            $tag = $movie_info['tag'];
            echo "tag=$tag";
            if (isset($this->_tagName[$movie_info['tag']])) {
                echo '('.$this->_tagName[$movie_info['tag']].')';
            }
            echo " length=".$movie_info['length'];
            if (isset($movie_info['id'])) {
                echo " id=".$movie_info['id'];
            }
            if (isset($movie_info['id_ref'])) {
                echo " id_ref=".$movie_info['id_ref'];
            }
            if (isset($movie_info['checksum'])) {
                echo " checksum=".$movie_info['checksum'];
            }
            echo PHP_EOL;
                    
            if (isset($movie_info['movie'])) {
                $this->_bio->hexdump($movie_info['offset'], $movie_info['tag_and_length_size'] + 4);
                $this->dump_movie($movie_info['movie'], $indent+1, $opts);
            } else {
                if (isset($opts['hexdump'])) {
                    $limit = null;
                    if (isset($this->_tagJpeg[$tag]) || isset($this->_tagLossless[$tag])) {
                        $limit = 0x20;
                    }
                    $this->_bio->hexdump($movie_info['offset'], $movie_info['length'], $limit);
                    echo PHP_EOL;
                }
            }
        }
    }
}
