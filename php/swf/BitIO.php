<?php


  /*
   * 2010/07/28- (c) yoya@awm.jp
   */

class BitIO {
    var $_data; // input_data
    var $_byte_offset;
    var $_bit_offset;

    function input($data) {
        $this->_data = $data;
        $this->_byte_offset = 0;
        $this->_bit_offset = 0;
    }
    function setOffset($byte_offset, $bit_offset) {
        $this->_byte_offset = $byte_offset;
        $this->_bit_offset  = $bit_offset;
    }
    function getOffset() {
        return array($this->_byte_offset, $this->_bit_offset);
    }
    function getLength(){
        return strlen($this->_data);
    }       
    function byteAlign() {
        if ($this->_bit_offset > 0) {
            $this->_byte_offset ++;
            $this->_bit_offset = 0;
        }
    }
    function getData($length, $offset = null) {
        $this->byteAlign();
        if (! is_null($offset)) {
            $this->_byte_offset = $offset;
        }
        $data = substr($this->_data, $this->_byte_offset, $length);
        $data_len = strlen($data);
        $this->_byte_offset += $data_len;
        return $data;
    }
    function getUI8($offset = null) {
        $this->byteAlign();
        if (! is_null($offset)) {
            $this->_byte_offset = $offset;
        }
        $value = ord($this->_data{$this->_byte_offset});
        $this->_byte_offset += 1;
        return $value;
    }
    function getUI16LE($offset = null) {
        $this->byteAlign();
        if (! is_null($offset)) {
            $this->_byte_offset = $offset;
        }
        $ret = unpack('v', substr($this->_data, $this->_byte_offset, 2));
        $this->_byte_offset += 2;
        return $ret[1];
    }
    function getUI32LE($offset = null) {
        $this->byteAlign();
        if (! is_null($offset)) {
            $this->_byte_offset = $offset;
        }
        $ret = unpack('V', substr($this->_data, $this->_byte_offset, 4));
        $this->_byte_offset += 4;
        return $ret[1];
    }
    function getUIBit() {
        $value = ord($this->_data{$this->_byte_offset});
        $value >>= 7 - $this->_bit_offset;
        $this->_bit_offset ++;
        if (8 <= $this->_bit_offset) {
            $this->_byte_offset++;
            $this->_bit_offset = 0;
        }
        return $value & 1;
    }
    function getUIBits($width) {
        $value = 0;
        for ($i = 0 ; $i < $width ; $i++) {
            $value <<= 1;
            $value |= $this->getUIBit();
        }
        return $value;
    }
    function getSIBits($width) {
        $value = $this->getUIBits($width);
        $msb = $value & (1 << ($width - 1));
        if ($msb) {
            // 2の補数処理
            $bitmask = (1 << $width) - 1;
            return -($value ^ bitmask) - 1;
        }
        return $value;
    }
    function putUI8($value, $offset) {
        $this->_data{$offset} = chr($value); // UI8
    }
    function putUI16LE($value, $offset) {
        $data = pack('v', $value); // UI16LE
        $this->_data{$offset    } = $data{0};
        $this->_data{$offset + 1} = $data{1};
    }
    function putUI32LE($value, $offset) {
        $data = pack('V', $value); // UI32LE
        $this->_data{$offset    } = $data{0};
        $this->_data{$offset + 1} = $data{1};
        $this->_data{$offset + 2} = $data{2};
        $this->_data{$offset + 3} = $data{3};
    }
    function toUI8($value) {
        return chr($value);
    }
    function toUI16LE($value) {
        return pack('v', $value);
    }
    function toUI32LE($value) {
        return pack('V', $value);
    }
    /*
     * general purpose hexdump routine
     */
    function hexdump($offset, $length, $limit = null) {
        printf("             0  1  2  3  4  5  6  7   8  9  a  b  c  d  e  f  0123456789abcdef\n");
        $dump_str = '';
        if ($offset % 0x10) {
            printf("0x%08x ", $offset - ($offset % 0x10));
            $dump_str = str_pad(' ', $offset % 0x10);
        }
        for ($i = 0; $i < $offset % 0x10; $i++) {
            if ($i == 0) {
                echo(' ');
            }
            if ($i == 8) {
                echo(' ');
            }
            echo('   ');
        }
        for ($i = $offset ; $i < $offset + $length; $i++) {
            if ((! is_null($limit)) && ($i >= $offset + $limit)) {
                break;
            }
            if (($i % 0x10) == 0) {
                printf("0x%08x  ", $i);
            }
            if ($i%0x10 == 8) {
                echo(' ');
            }
            if (isset($this->_data[$i])) {
                $value = $this->getUI8($i);
                if ((0x20 < $value) && ($value < 0x7f)) { // XXX: printable
                    $dump_str .= $this->_data{$i};
                } else {
                    $dump_str .= ' ';
                }
                printf("%02x ", $value);
            } else {
                $dump_str .= ' ';
                echo '   ';
            }
            if (($i % 0x10) == 0x0f) {
                echo " ";
                echo $dump_str;
                echo PHP_EOL;
                $dump_str = '';
            }
        }
        if (($i % 0x10) != 0) {
            echo str_pad(' ', 3 * (0x10 - ($i % 0x10)));
            if ($i < 8) {
                echo ' ';
            }
            echo " ";
            echo $dump_str;
            echo PHP_EOL;
        }
        if ((! is_null($limit)) && ($i >= $offset + $limit)) {
            echo "...(truncated)...".PHP_EOL;
        }
    }
}
