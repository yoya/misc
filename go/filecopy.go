package main

import (
	"flag"
	"io"
	"os"
)

// http://stackoverflow.com/questions/1821811/how-to-read-write-from-to-file-using-golang
func FileCopy(inFile, outFile string) (int64, error) {
	r, err := os.Open(inFile)
	if err != nil {
		return 0, err
	}
	defer r.Close()

	w, err := os.Create(outFile)
	if err != nil {
		return 0, err
	}
	defer w.Close()

	// do the actual work
	n, err := io.Copy(w, r)
	if err != nil {
		return n, err
	}
	return n, nil
}

func main() {
	flag.Parse()
	if flag.NArg() < 2 {
		panic("Usage: filecopy <infile> <outfile> ")
	}
	inFile, outFile := flag.Arg(0), flag.Arg(1)
	_, err := FileCopy(inFile, outFile)
	if err != nil {
		panic(err)
	}
}
