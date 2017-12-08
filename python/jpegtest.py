import sys
from io_jpeg import IO_JPEG

jpeg = IO_JPEG()
jpeg.parse(open(sys.argv[1]).read())
jpeg.dump()
