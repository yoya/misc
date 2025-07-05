import wx

class MyFrame(wx.Frame):
    def __init__(self):
        wx.Frame.__init__(self, None, -1, "Title", size=(400,300))
        panel = wx.Panel(self)
        button = wx.Button(panel, wx.ID_ANY, "Exit")
        button.Bind(wx.EVT_BUTTON, self.OnExit)
    def OnExit(self, event):
        wx.Exit()

if __name__ == '__main__':
    app = wx.App()
    MyFrame().Show()
    app.MainLoop()
