#! /usr/bin/env python
import sys, math

argc = len(sys.argv)

if argc != 3 and argc != 4:
    print("Usage: aspect.py <num1> <num2>")
    print("Usage: aspect.py <num1> <x> <y>>")
    print("ex) aspect.py 960 720")
    print("ex) aspect.py 960 4 3")
    exit(1)

def prediction(num1, num2):
    ratio = num1 / num2
    for x in range(1, 16):
        for y in range(1, 16):
            if math.gcd(x, y) == 1 and math.fabs(ratio - x / y) < 0.01:
                d = num2 - num1 / x * y
                if math.fabs(d) < 1:
                    print("{}x{} => ratio {}:{}".format(num1, num2, x, y))
                else:
                    print("{}x{} => ratio {}:{} diff {}".format(num1, num2, x, y, d))

if argc == 3:
    num1 = float(sys.argv[1])
    num2 = float(sys.argv[2])
    prediction(num1, num2)
elif argc == 4:
    num1 = float(sys.argv[1])
    x = float(sys.argv[2])
    y = float(sys.argv[3])
    print("{} ratio {}:{} => {}".format(num1, x, y, num1 / x * y))
else:
    print("Internal error: wrong arg number:{}".format(argc))
