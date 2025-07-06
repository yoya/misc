import wx

class MyFrame(wx.Frame):
    def __init__(self):
        wx.Frame.__init__(self, None, -1, "Title", size=(400,300))
        panel = wx.Panel(self)
        button = wx.Button(panel, wx.ID_ANY, "Exit")
        button.Bind(wx.EVT_BUTTON, self.OnExit)
        target = MyFileDropTarget()
        self.SetDropTarget(target)
    def OnExit(self, event):
        wx.Exit()

class MyFileDropTarget(wx.FileDropTarget):
    def OnDropFiles(self, x, y, filenames):
        for filename in filenames:
            encoding = detectEncoding(filename)
            print("({}){}".format(encoding, filename))
            if encoding in ["ascii", "utf-8", "sjis"]:
                convertToCRLF(filename, encoding)
        return True

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

def convertToCRLF(filename, encoding):
    print("convertToCRLF", filename)
    with open(filename, 'tr', encoding=encoding) as r:
        line = r.read()
        r.close()
        with open(filename, 'tw', encoding=encoding) as w:
            w.write(line)
            w.close()

if __name__ == '__main__':
    app = wx.App()
    MyFrame().Show()
    app.MainLoop()
