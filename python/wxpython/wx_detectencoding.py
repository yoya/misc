import wx
from chardet import detect

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
        return True

def detectEncoding(filename):
    detect = { "utf-8": False, "sjis": False }
    for encoding in detect:
        try:
            with open(filename, 'tr', encoding=encoding) as f:
                f.read()
                detect[encoding] = True
        except UnicodeDecodeError:
            pass
    if detect["utf-8"] and detect["sjis"]:
        return "ascii"
    if detect["utf-8"]:
        return "utf-8"
    elif detect["sjis"]:
        return "sjis"
    return "binary"
        
if __name__ == '__main__':
    app = wx.App()
    MyFrame().Show()
    app.MainLoop()
