#! /usr/bin/env python

import sys
from PIL import Image
import numpy

im = Image.open(sys.argv[1])
arr = numpy.asarray(im)
#arr.tofile(sys.argv[2])
#arr = arr.astype("float32");
numpy.save(sys.argv[2], arr)
