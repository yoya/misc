package main

import (
	"flag"
	"fmt"
	"github.com/gographics/imagick/imagick"
	"os"
)

func main() {
	flag.Parse()

	imagick.Initialize()
	defer imagick.Terminate()
	mw := imagick.NewMagickWand()
	defer mw.Destroy()
	var err error

	if flag.NArg() < 1 {
		fmt.Println("Usage: imagick_profiles <imgfile> [<proftype>]\n")
		os.Exit(1)
	}
	basename := flag.Arg(0)
	err = mw.ReadImage(basename)
	if err != nil {
		fmt.Println("ReadImage Error:", err)
		os.Exit(1)
	}
	if flag.NArg() == 1 {
		profs := mw.GetImageProfiles("*")
		fmt.Println(profs)
	} else {
		proftype := flag.Arg(1)
		prof := mw.GetImageProfile(proftype)
		fmt.Print(prof)
	}
}
