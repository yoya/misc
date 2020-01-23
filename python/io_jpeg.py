from __future__ import print_function
import sys
import math
from collections import OrderedDict
from pprint import pprint
from io_bit import IO_Bit

class IO_JPEG :
    def __init__(self):
         self.chunks = []

    def parse(self, jpegdata):
         self._jpegdata = jpegdata
         reader = IO_Bit()
         reader.input(jpegdata)
         while reader.hasNextData(2):
             chunk = self._parseChunk(reader)
             self.chunks.append(chunk)

    def _parseChunk(self, reader):
        chunk = {}
        while reader.hasNextData(2):
            if reader.getUI8() == 0xFF:
               break
        marker = reader.getUI8()
        chunk['marker'] = marker
        if marker == 0xD8 or marker == 0xD9: # SOI,EOI
            chunk['data'] = ''
            return chunk
        elif marker == 0xDA or 0xD0 <= marker <= 0xD7: # SOS.RSTn
            dataOffset, dummy = reader.getOffset();
            prevByte = False
            while True:
                currByte = reader.getUI8()
                if prevByte == 0xFF and currByte != 0x00:
                    break
                prevByte = currByte
            nextOffset_plus2, dummy = reader.getOffset()
            reader.setOffset(dataOffset, 0)
            data = reader.getData(nextOffset_plus2 - 2 - dataOffset)
            chunk['data'] = data
            return chunk
        else:
            length = reader.getUI16BE() # etc, SOFn,DQT,DHT,APP...
            data = reader.getData(length - 2)
            chunk['data'] = data
            return chunk

    def dump(self, fp = sys.stdout, opts = {}):
        for key, value in enumerate(self.chunks):
            fp.write("FF{:02X}\n".format(value['marker']))
