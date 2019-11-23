/*
 * Copyright 2019/11/20- yoya@awm.jp
 */

#include <iostream>
#include <fstream>
#include <iostream>
#include <string>
#include <vector>
//#include <utility>
#include <numeric>
#include "npy.hpp"

/*
  copy (just read & write) array file.
  % g++ -o npycopy npycopy.cpp npyread.cpp npywrite.cpp -std=c++11 -Wall -Wextra
 */

void usage() {
  std::cerr << "Usage: npycopy <npyfile>" << std::endl;
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
  int n = std::accumulate(nh.shape.begin(), nh.shape.end(), 1, std::multiplies<int>());
  std::vector<uint8_t> imagedata(n);
  readNPYdata(fin, nh, imagedata.data());

  std::ofstream fout("output.npy", std::ios::out | std::ios::trunc | std::ios::binary);

  writeNPYheader(fout, nh);
  writeNPYdata(fout, nh, imagedata.data());
  
  std::cerr << "OK" << std::endl;
}
