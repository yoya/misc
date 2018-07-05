# (c) yoya@awm.jp 2018/07/05-
# ref) https://www.programcreek.com/python/example/89944/PIL.Image.frombytes
#      http://pillow.readthedocs.io/en/5.2.x/reference/Image.html#PIL.Image.Image.convert

from PIL import Image
import sys

def gamma_correct(im, gamma):
    if gamma == 1.0:
        return im
    table = [pow(x / 255.0 , gamma) * 255 for x in range(256)]
    return im.point(table * len(im.mode))

def grayscale(im):
    if im.mode == "L":
        return im
    if im.mode != "RGB": # mainly, care for mode=="P"
        im = im.convert("RGB")
    im = gamma_correct(im, 2.2) # to linear RGB
    rgb2xyz_rec709 = (
        0.412453, 0.357580, 0.180423, 0,
        0.212671, 0.715160, 0.072169, 0, # RGB mixing weight
        0.019334, 0.119193, 0.950227, 0 )
    im = im.convert("L", rgb2xyz_rec709)
    return  gamma_correct(im, 1.0/2.2) # from linear RGB

# main
im = Image.open(sys.argv[1])
im = grayscale(im)
im.show()
