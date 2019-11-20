/*
 * Copyright 2019/08/14- yoya@awm.jp
 */

#include <iostream>
#include <fstream>
#include <string>
#include <map>
#include <vector>
#include <sstream>
#include "npyread.hpp"

/*
  read & dump numpy array file.
  % g++ npydump.cpp npyread.cpp -std=c++11 -Wall -Wextra
 */

void usage() {
  std::cerr << "Usage: npydump <npyfile>" << std::endl;
}

int main(int argc, char **argv) {
  if (argc < 2) {
    usage();
    std::exit(1);
  }
  char *infile = argv[1];
  std::ifstream fin(infile, std::ios::in | std::ios::binary);
  if (!fin) {
    std::cerr << "Cant' open file:" << infile << std::endl;
    std::exit(1);
  }
  struct NPYheader_t nh;
  try  {
    nh = readNPYheader(fin);
  } catch (std::runtime_error e) {
    std::cerr << e.what() << std::endl;
    std::exit(1);
  }
  std::cerr << "depth:" << nh.depth << "width:" << nh.width <<
    " height:" << nh.height << " channels:" << nh.channels << std::endl;

  std::vector<uint8_t> imagedata(nh.width * nh.height * nh.channels);
  readNPYimagedata(fin, nh, imagedata.data());

  int i = 0;
  for (int y = 0 ; y < nh.height ; y++) {
    for (int x = 0 ; x < nh.width ; x++) {
      for (int c = 0 ; c < nh.channels ; c++) {
        std::cout << std::hex << static_cast<int>(imagedata[i++]);
      }
      std::cout << " ";
    }
    std::cout << std::endl;
  }
  std::cerr << "OK" << std::endl;
}
