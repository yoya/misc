// ref) https://github.com/yoya/IO_GIF/blob/master/IO/GIF.php

package main

import (
	"encoding/binary"
	"fmt"
	"io/ioutil"
	"math"
	"os"
)

func printColorTable(bytes []byte, offset int, colorTableSize int) {
	i := 0
	for ; i < colorTableSize; i++ {
		red, green, blue := bytes[offset], bytes[offset+1], bytes[offset+2]
		offset += 3
		if i%8 == 0 {
			fmt.Printf("    0x%02x:", i)
		}
		fmt.Printf(" #%02x%02x%02x", red, green, blue)
		if i%8 == 7 {
			fmt.Printf("\n")
		}
	}
	if i%8 != 0 {
		fmt.Printf("\n")
	}
}

func main() {
	var err error
	if len(os.Args) < 2 {
		fmt.Println("Too few arguments")
		return
	}
	// data read
	f, err := os.Open(os.Args[1])
	if err != nil {
		panic(err)
	}
	defer f.Close()
	bytes, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	// parse
	bs := binary.LittleEndian
	//
	sig, ver := bytes[0:3], bytes[3:6]
	width, height := bs.Uint16(bytes[6:]), bs.Uint16(bytes[8:])
	fmt.Printf("Signature:%s Version:%s ", sig, ver)
	fmt.Printf("Width:%d Height:%d\n", width, height)
	//
	flags := bytes[10]
	globalColorTableFlag := (flags & 0x80) >> 7
	colorResolution := (flags & 0x70) >> 4
	sortFlag := (flags & 0x08) >> 3
	sizeOfGlobalColorTable := (flags & 0x07)
	fmt.Printf("GlobalColorTableFlag:%d ColorResolution:%d SortFlag:%d SizeOfGlobalColorTable:%d\n", globalColorTableFlag, colorResolution, sortFlag, sizeOfGlobalColorTable)
	//
	backgroundColorIndex := bytes[11]
	pixelAspectRatio := bytes[12]
	fmt.Printf("BackgroundColorIndex:%d PixelAspectRatio:%d\n", backgroundColorIndex, pixelAspectRatio)
	var offset = 13
	if globalColorTableFlag != 0 {
		colorTableSize := int(math.Pow(2, float64(sizeOfGlobalColorTable+1)))
		fmt.Printf("GlobalColorTable(%d)\n", colorTableSize)
		printColorTable(bytes, offset, colorTableSize)
		offset += 3 * colorTableSize
	}
	for {
		fmt.Printf("# -- offset:%x\n", offset)
		separator := bytes[offset]
		offset++
		switch separator {
		case 0x3B: // Trailer
			// nothing to do
			fmt.Printf("# Trailer\n")
		case 0x21: // Extention
			fmt.Printf("# Extention\n")
			extensionBlockLabel := bytes[offset]
			extensionDataSize := bytes[offset+1]
			fmt.Printf("extensionBlockLabel:%02x extensionDataSize:%d\n", extensionBlockLabel, extensionDataSize)
			offset += 2 + int(extensionDataSize)
			if extensionBlockLabel == 0xff { // Application Extension
				for {
					subBlockSize := bytes[offset]
					offset++
					if subBlockSize == 0 {
						break
					}
					offset += int(subBlockSize)
				}
			} else {
				offset++ // extensionBlock Trailer
			}
		case 0x2C: // Image
			fmt.Printf("# Image\n")
			left, top, width, height :=
				bs.Uint16(bytes[offset:]),
				bs.Uint16(bytes[offset+2:]),
				bs.Uint16(bytes[offset+4:]),
				bs.Uint16(bytes[offset+6:])
			fmt.Printf("Left:%d Top:%d Width:%d Height:%d\n", left, top, width, height)
			flags := bytes[offset+8]
			localColorTableFlag := (flags & 0x80) >> 7
			interlaceFlag := (flags & 0x40) >> 6
			sortFlag := (flags & 0x20) >> 5
			sizeOfLocalColorTable := (flags & 0x07)
			offset += 9
			fmt.Printf("LocalColorTableFlag:%d InterlaceFlag:%d SortFlag:%d SizeOfLocalColorTable:%d\n", localColorTableFlag, interlaceFlag, sortFlag, sizeOfLocalColorTable)
			if localColorTableFlag != 0 {
				colorTableSize := int(math.Pow(2, float64(sizeOfLocalColorTable+1)))
				fmt.Printf("LocalColorTable(%d)\n", colorTableSize)
				printColorTable(bytes, offset, colorTableSize)
				offset += 3 * colorTableSize
			}
			LZWMinimumCodeSize := bytes[offset]
			fmt.Printf("LZWMinimumCodeSize:%d\n", LZWMinimumCodeSize)
			offset++
			for {
				subBlockSize := bytes[offset]
				fmt.Printf("subBlockSize:%d", subBlockSize)
				offset++
				if subBlockSize == 0 {
					break
				}
				offset += int(subBlockSize)
			}
			fmt.Printf("\n")
		default:
			fmt.Fprintf(os.Stderr, "Illegal separator:%02x", separator)
		}
		if separator == 0x3B {
			break
		}
	}
}
