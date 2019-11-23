/*
 * Copyright 2019/11/20- yoya@awm.jp
 */

#include <fstream>
#include <string>
#include <map>
#include <vector>
#include <sstream>
#include <utility>
#include <numeric>
#include <functional>
#include "npy.hpp"

void writeNPYheader(std::ofstream &fout,
                     const struct NPYheader_t &nh) {
  std::stringstream jsonss;
  fout << NPY_FILE_SIG;
  // version: 1 (little-endian)
  fout.write(reinterpret_cast<const char *>("\1\0"), 2);
  jsonss << "{'descr': '|u1', 'fortran_order': False, 'shape': (";
  for (auto itr = nh.shape.begin(); itr != nh.shape.end(); ++itr) {
    if (itr != nh.shape.begin()) {
      jsonss << ", ";
    }
    jsonss << *itr;
  }
  jsonss << "), }";
  jsonss.seekg(0, std::ios::end);
  uint16_t jsonlen = jsonss.tellg();
  for (int i = 10 + jsonlen; i < 0x80 ; i++) {
    jsonss << " ";
  }
  jsonss.seekg(0, std::ios::end);
  jsonlen = jsonss.tellg();
  fout.write(reinterpret_cast<char *>(&jsonlen), sizeof(uint16_t));
  fout << jsonss.str() ;
}

template<typename T>
void writeNPYdata(std::ofstream &fout, const struct NPYheader_t &nh,
                       T *imagedata) {
  if (imagedata == NULL) {
    throw std::runtime_error("imagedata == NULL");
  }
  int n = std::accumulate(nh.shape.begin(), nh.shape.end(), 1, std::multiplies<int>());
  char fvalue_char[sizeof(T)];
  for (int i = 0 ; i < n ; ++i) {
    *(reinterpret_cast<T*>(fvalue_char)) = imagedata[i];
    fout.write(fvalue_char, sizeof(T));
  }
}

// dummy function for template.
void npywrite_dummy_uchar() {
  std::ofstream fout;
  struct NPYheader_t nh;
  unsigned char *imagedata = NULL;
  writeNPYdata(fout, nh, imagedata);
}

// dummy function for template.
void npywrite_dummy_float() {
  std::ofstream fout;
  struct NPYheader_t nh;
  float *imagedata = NULL;
  writeNPYdata(fout, nh, imagedata);
}
