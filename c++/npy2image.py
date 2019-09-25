import sys
from PIL import Image
import numpy

arr = numpy.load(sys.argv[1])
im = Image.fromarray(arr)
im.save(sys.argv[2])
