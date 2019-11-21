/*
 * Copyright 2019/08/14- yoya@awm.jp
 */

#include <iostream>
#include <fstream>
#include <string>
#include <vector>
#include <numeric>
#include "npy.hpp"

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
  //std::cerr << "depth:" << nh.depth << "width:" << nh.width <<
  //" height:" << nh.height << " channels:" << nh.channels << std::endl;
  std::cerr << "shape:";
  int height = nh.shape[0];
  int width = nh.shape[1];
  int channels = nh.shape[2];
  if (channels != 3) { // channels
    std::cerr << "wrong channels:" << channels << std::endl;
    std::exit(1);
  }
  for (auto itr = nh.shape.begin(); itr != nh.shape.end(); ++itr) {
    std::cerr << *itr << ", ";
  }
  //
  int imagedata_size = std::accumulate(nh.shape.begin(), nh.shape.end(), 1, std::multiplies<int>());
  std::vector<uint8_t> imagedata(imagedata_size);
  //std::vector<float> imagedata(imagedata_size);
  readNPYdata(fin, nh, imagedata.data());
  int i = 0;
  for (int y = 0 ; y < height ; y++) {
    for (int x = 0 ; x < width ; x++) {
      for (int c = 0 ; c < channels ; c++) {
        std::cout << std::hex << static_cast<int>(imagedata[i++]);
      }
      std::cout << " ";
    }
    std::cout << std::endl;
  }
  std::cerr << "OK" << std::endl;
}
