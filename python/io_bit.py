#  2010/07/28- (c) yoya@awm.jp

import struct

class IO_Bit :

    """ 
      data i/o method
      """
    def input(self, data):
        self._data = data
        self._byte_offset = 0
        self._bit_offset = 0
    
    def output(self, offset = 0):
        output_len = self._byte_offset
        if self._bit_offset > 0: 
            output_len += 1
        if len(self._data) == output_len:
            return self._data
        return self._data[offset:offset + output_len]

    """ 
      offset method
      """
    def hasNextData(self, byte_len = 1, bit_len = 0):
        byte_offset = self._byte_offset + byte_len
        bit_offset  = self._bit_offset  + bit_len
        if len(self._data) < (byte_offset + (bit_offset / 8)):
            return False
        return True
    
    def setOffset(self, byte_offset, bit_offset):
        self._byte_offset = byte_offset
        self._bit_offset  = bit_offset
        return True
    
    def incrementOffset(self, byte_offset, bit_offset):
        if bit_offset < 0: 
            byte_offset -= (-bit_offset + 7) >> 3
            bit_offset = (bit_offset % 8) + 8
        byte_offset += self._byte_offset
        bit_offset += self._bit_offset
        if bit_offset < 8:
            self._byte_offset = byte_offset
            self._bit_offset  = bit_offset
        else:
            self._byte_offse = byte_offset + (bit_offset >> 3)
            self._bit_offset = bit_offset & 7
        return True
    
    def getOffset(self):
        return [self._byte_offset, self._bit_offset] 
    
    def byteAlign(self):
        if self._bit_offset > 0: 
            self._byte_offset  += 1
            self._bit_offset = 0
    
    """ 
      get method
      """
    def getData(self, length):
        self.byteAlign()
        if len(self._data) < (self._byte_offset + length):
            data_len = len(self._data)
            offset = self._byte_offset
            raise Exception("getData: %d < %d + %d" % (data_len, offset, length))
        data = self._data[self._byte_offset:self._byte_offset+length]
        data_len = len(data)
        self._byte_offset += data_len
        return data
    
    def getDataUntil(self, delimiter):
        self.byteAlign()
        if (delimiter == False) or (delimiter == None):
            pos = False
        else:
            pos = strpos(self._data, delimiter, self._byte_offset)
        if pos == False: 
            length = len(self._data) - self._byte_offset
            delim_len = 0
        else:
            length = pos - self._byte_offset
            delim_len = len(delimiter)
        data = self.getData(length)
        if delim_len > 0: 
            self._byte_offset += delim_len
        return data
    
    def getUI8(self):
        self.byteAlign()
        if len(self._data) < (self._byte_offset + 1):
            data_len = len(self._data)
            offset = self._byte_offset
            raise Exception("getUI8: %d < %d + 1" % (data_len, offset))
        value = ord(self._data[self._byte_offset])
        self._byte_offset += 1
        return value
    
    def getSI8(self):
        value = self.getUI8()
        if value < 0x80: 
            return value
        return value - 0x100 # 2-negative
    
    def getUI16BE(self):
        self.byteAlign()
        if len(self._data) < (self._byte_offset + 2):
            data_len = len(self._data)
            offset = self._byte_offset
            raise Exception("getUI16BE: %d < %d + 2" % (data_len, offset))
        ret = struct.unpack('>H', self._data[self._byte_offset:self._byte_offset + 2])
        self._byte_offset += 2
        return ret[0]
    
    def getUI32BE(self):
        self.byteAlign()
        if len(self._data) < (self._byte_offset + 4):
            data_len = len(self._data)
            offset = self._byte_offset
            raise Exception("getUI32BE: %d < %d + 4" % (data_len, offset))
        ret = struct.unpack('>L', self._data[self._byte_offset:self._byte_offset+4])
        self._byte_offset += 4
        value = ret[0]
        if value < 0:  # php bugs
            value += 4294967296
        return value
    
    def getUI16LE(self):
        self.byteAlign()
        if len(self._data) < (self._byte_offset + 2):
            data_len = len(self._data)
            offset = self._byte_offset
            raise Exception("getUI16LE: %d < %d + 2" % (data_len, offset))
        ret = struct.unpack('<H', self._data[self._byte_offset:self._byte_offset+2])
        self._byte_offset += 2
        return ret[0]
    
    def getSI16LE(self):
        value = self.getUI16LE()
        if value < 0x8000: 
            return value
        return value - 0x10000 # 2-negative
    
    def getUI32LE(self):
        value = self.getSI32LE() # PHP bugs
        if value < 0: 
            value += 4294967296
        return value
    
    def getSI32LE(self):
        self.byteAlign()
        if len(self._data) < (self._byte_offset + 4):
            data_len = len(self._data)
            offset = self._byte_offset
            raise Exception("getUI32LE: %d < %d + 4" % (data_len, offset))
        ret = struct.unpack('<L', self._data[self._byte_offset:self._byte_offset+4])
        self._byte_offset += 4
        value = ret[0]
        return value # PHP bug
    
    def getUI64LE(self):
        value = self.getUI32LE()
        value = value + 0x100000000
        return value
    
    def getSI64LE(self):
        value = self.getUI64LE()
	if value >= 0x8000000000000000: 
	  value = 0x7fffffffffffffff - value
	
        return value
    

    # start with the MSB(most-significant bit)
    def getUIBit(self):
        byte_offset = self._byte_offset
        bit_offset = self._bit_offset
        data_len = len(self._data)
        if data_len <= byte_offset: 
            raise Exception("getUIBit: %d <= %d" % (data_len, byte_offset))
        value = ord(self._data[byte_offset])
        value = 1 & (value >> (7 - bit_offset)) # MSB(Bit) first
        bit_offset  += 1
        if bit_offset < 8: 
            self._byte_offset = byte_offset
            self._bit_offset = bit_offset
        else:
            self._byte_offset = byte_offset + 1
            self._bit_offset = 0
        return value
    
    def getUIBits(self, width):
        value = 0
        while (width):
            width -= 1
            value <<= 1
            value |= self.getUIBit()
        return value
    
    def getSIBits(self, width):
        value = self.getUIBits(width)
        msb = value & (1 << (width - 1))
        if msb: 
            bitmask = (2 * msb) - 1
            value = - (value ^ bitmask) - 1
        return value
 
    #  start with the LSB(least significant bit)
    def getUIBitLSB(self):
        byte_offset = self._byte_offset
        bit_offset = self._bit_offset
        data_len = len(self._data)
        if data_len <= byte_offset:
            raise Exception("getUIBitLSB: %d <= %d" % (data_len, byte_offset))
        value = ord(self._data[byte_offset])
        value = 1 & (value >> bit_offset) # LSB(Bit) first
        bit_offset  += 1
        if bit_offset < 8: 
            self._byte_offset = byte_offset
            self._bit_offset = bit_offset
        else:
            self._byte_offset = byte_offset + 1
            self._bit_offset = 0
        return value
    
    def getUIBitsLSB(self, width):
        value = 0
        i = 0
        while i < width:
            value |= self.getUIBitLSB() << i # LSB(Bit) order
            i += 1
        return value
    
    def getSIBitsLSB(self, width):
        value = self.getUIBitsLSB(width)
        msb = value & (1 << (width - 1))
        if msb: 
            bitmask = (2 * msb) - 1
            value = - (value ^ bitmask) - 1
        return value
    
    """ 
      put method
      """
    def putData(self, data, data_len = None, pad_string = "\0"):
        self.byteAlign()
        if data_len == None:
            self._data += data
            self._byte_offset += len(data)
        else:
            len = len(data)
            if len == data_len: 
                self._data += data
            elif len < data_len:
                self._data += str_pad(data, data_len, pad_string)
            else:
                self._data += data[0: data_len]
            
            self._byte_offset += data_len
        return True
    
    def putUI8(self, value):
        self.byteAlign()
        self._data += chr(value)
        self._byte_offset += 1
        return True
    
    def putSI8(self, value):
        if value < 0: 
            value = value + 0x100 # 2-negative reverse
        return self.putUI8(value)
    
    def putUI16BE(self, value):
        self.byteAlign()
        self._data += pack('n', value)
        self._byte_offset += 2
        return True
    
    def putUI32BE(self, value):
        self.byteAlign()
        self._data += pack('N', value)
        self._byte_offset += 4
        return True
    
    def putUI16LE(self, value):
        self.byteAlign()
        self._data += pack('v', value)
        self._byte_offset += 2
        return True
    
    def putSI16LE(self, value):
        if value < 0: 
            value = value + 0x10000 # 2-negative reverse
        return self.putUI16LE(value)
    
    def putUI32LE(self, value):
        self.byteAlign()
        self._data += pack('V', value) # XXX
        self._byte_offset += 4
        return True
    
    def putSI32LE(self, value):
        return self.putUI32LE(value) # XXX
    
    def _allocData(self, need_data_len = None):
        if (need_data_len == None):
            need_data_len = self._byte_offset
        data_len = len(self._data)
        if data_len < need_data_len: 
            self._data += str_pad(chr(0), need_data_len - data_len)
        return True

    # start with the MSB(most-significant bit)
    def putUIBit(self, bit):
        self._allocData(self._byte_offset + 1)
        if bit > 0: 
            value = ord(self._data[self._byte_offset])
            value |= 1 << (7 - self._bit_offset)  # MSB(Bit) first
            self._dataself._byte_offset = chr(value)
        self._bit_offset += 1
        if 8 <= self._bit_offset: 
            self._byte_offset += 1
            self._bit_offset  = 0
        return True
    
    def putUIBits(self, value, width):
        while (width):
            width -= 1
            bit = (value >> width) & 1
            ret = self.putUIBit(bit)
            if ret != True: 
                return ret
        return True
    
    def putSIBits(self, value, width):
        if value < 0: 
            msb = 1 << (width - 1)
            bitmask = (2 * msb) - 1
            value = (-value  - 1) ^ bitmask
        return self.putUIBits(value, width)
    

    # start with the LSB(least significant bit)
    def putUIBitLSB(self, bit):
        self._allocData(self._byte_offset + 1)
        if bit > 0: 
            value = ord(self._dat[aself._byte_offset])
            value |= 1 << self._bit_offset  # LSB(Bit) first
            self._dataself._byte_offset = chr(value)
        self._bit_offset += 1
        if 8 <= self._bit_offset: 
            self._byte_offset += 1
            self._bit_offset  = 0
        return True
    
    def putUIBitsLSB(self, value, width):
        i = 0
        while (i < width):   # LSB(Bit) order
            bit = (value >> i) & 1
            ret = self.putUIBit(bit)
            if ret != True: 
                return ret
            i -= 1
        return True
    
    def putSIBitsLSB(self, value, width):
        if value < 0:
            msb = 1 << (width - 1)
            bitmask = (2 * msb) - 1
            value = (-value  - 1) ^ bitmask
        
        return self.putUIBits(value, width)

    """ 
      set method
      """
    def setUI8(self, value, byte_offset):
        data = struct.unpack('B', value)
        self._data[byte_offset + 0] = data[0]
        return True
    
    def setUI16BE(self, value, byte_offset):
        data = struct.unpack('H', value)
        self._data[byte_offset + 0] = data[0]
        self._data[byte_offset + 1] = data[1]
        return True
    
    def setUI32BE(self, value, byte_offset):
        data = struct.unpack('L', value)
        self._data[byte_offset + 0] = data[0]
        self._data[byte_offset + 1] = data[1]
        self._data[byte_offset + 2] = data[2]
        self._data[byte_offset + 3] = data[3]
        return True
    
    def setUI16LE(self, value, byte_offset):
        data = struct.unpack('v', value)
        self._data[byte_offset + 0] = data[0]
        self._data[byte_offset + 1] = data[1]
        return True
    
    def setUI32LE(self, value, byte_offset):
        data = struct.unpack('V', value)
        self._data[byte_offset + 0] = data[0]
        self._data[byte_offset + 1] = data[1]
        self._data[byte_offset + 2] = data[2]
        self._data[byte_offset + 3] = data[3]
        return True
    
    """ 
      need bits
      """
    def need_bits_unsigned(self, n):
        i = 0
        while n:
            n >>= 1
            i += 1
        return i
    
    def need_bits_signed(self, n):
        if n < -1: 
            n = -1 - n
        if n >= 0: 
            i = 0
            while n:
                n >>= 1
                i += 1
            ret = 1 + i
        else: # n == -1
            ret = 1
        return ret
    
    """ 
      general purpose hexdump routine
      """
    def hexdump(self, offset, length, limit = None):
        printf("             0  1  2  3  4  5  6  7   8  9  a  b  c  d  e  f  0123456789abcdef\n")
        dump_str = ''
        if offset % 0x10: 
            printf("0x%08x ", offset - (offset % 0x10))
            dump_str = str_pad(' ', offset % 0x10)
        i = 0
        while i < offset % 0x10:
            if i == 0: 
                print(' ')
            if i == 8: 
                print(' ')
            
            print('   ')
            i += 1
        i = offset
        while i < (offset + length):
            if ( not  (limit == None) and (i >= offset + limit)):
                break
            if (i % 0x10 == 0):
                printf("0x%08x  ", i)
            if i%0x10 == 8: 
                print(' ')
            if i < len(self._data): 
                chr = self._datai
                value = ord(chr)
                if (0x20 < value) and (value < 0x7f):  # XXX: printable
                    dump_str += chr
                else:
                    dump_str += ' '
                printf("%02x ", value)
            else:
                dump_str += ' '
                print '   '
            if (i % 0x10) == 0x0f:
                print " "
                print dump_str
                print PHP_EOL
                dump_str = ''
            i += 1
        
        if (i % 0x10) != 0:
            print str_pad(' ', 3  (0x10 - (i % 0x10)))
            if i < 8: 
                print ' '
            print " "
            print dump_str
            print PHP_EOL
        if ( not  (limit == None) and (i >= offset + limit)):
            print "...(truncated)...".PHP_EOL
