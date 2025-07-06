#!/usr/bin/python3
# (c) 2024/02/14- yoya@awm.jp

import os, sys, subprocess

progDir = os.path.dirname(__file__)
requirementsFile = os.path.join(progDir, "requirements.txt")

subprocess.run([sys.executable, "-m",
                "pip", "install", "-r", requirementsFile])

def windows_icon_click():
    progdir = os.path.dirname(os.path.abspath(sys.argv[0]))
    currdir = os.getcwd()
    return progdir != currdir

if windows_icon_click():
    input("hit enter key")

