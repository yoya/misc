#! /usr/bin/env python

import sys
from PIL import Image
import numpy

arr = numpy.load(sys.argv[1])
// arr = arr.astype("uint8");
im = Image.fromarray(arr)
im.save(sys.argv[2])
