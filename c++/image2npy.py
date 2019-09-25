import sys
from PIL import Image
import numpy

im = Image.open(sys.argv[1])
arr = numpy.asarray(im)
#arr.tofile(sys.argv[2])
numpy.save(sys.argv[2], arr)
