import sys

def detectEncoding(filename):
    detect = { "utf-8": False, "sjis": False }
    for encoding in detect:
        try:
            with open(filename, 'tr', encoding=encoding) as f:
                f.read()
                detect[encoding] = True
                f.close()
        except UnicodeDecodeError:
            pass
    if detect["utf-8"] and detect["sjis"]:
        return "ascii"
    if detect["utf-8"]:
        return "utf-8"
    elif detect["sjis"]:
        return "sjis"
    return "binary"

def convertSJISToUTF8(filename):
    print("convertSJISToUTF8", filename)
    with open(filename, 'tr', encoding="sjis") as r:
        line = r.read()
        r.close()
        with open(filename, 'tw', encoding="utf-8") as w:
            w.write(line)
            w.close()

if __name__ == '__main__':
    filenames = sys.argv[1:]
    for filename in filenames:
        encoding = detectEncoding(filename)
        print("({}){}".format(encoding, filename))
        if encoding == "sjis":
            convertSJISToUTF8(filename)
