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
  jsonss << "{'descr': '";
  if ((nh.valuetype != "|u1") && (nh.valuetype != "<f4")) {
    throw std::runtime_error("valuetype(descr):" + nh.valuetype + ", must be |u1 or <f4");;
  }
  jsonss << nh.valuetype;
  jsonss << "', 'fortran_order': False, 'shape': (";
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
  fout << jsonss.str();
}

template<typename T>
void writeNPYdata(std::ofstream &fout, const struct NPYheader_t &nh,
                       T *data) {
  if (data == NULL) {
    throw std::runtime_error("data == NULL");
  }
  if ((nh.valuetype != "|u1") && (nh.valuetype != "<f4")) {
    throw std::runtime_error("valuetype(descr):" + nh.valuetype + ", must be |u1 or <f4");
  }
  int n = std::accumulate(nh.shape.begin(), nh.shape.end(), 1, std::multiplies<int>());
  if (nh.valuetype == "|u1") {
    for (int i = 0 ; i < n ; ++i) {
      fout.put(data[i]);
    }
  } else if (nh.valuetype == "<f4") {
    char fvalue_char[4];
    for (int i = 0 ; i < n ; ++i) {
      *(reinterpret_cast<float*>(fvalue_char)) = data[i];
      fout.write(fvalue_char, 4);
    }
  } else {
    throw std::runtime_error("unsupported type:"+nh.valuetype);
  }
}

// dummy function for template.
void npywrite_dummy_uchar() {
  std::ofstream fout;
  struct NPYheader_t nh;
  unsigned char *data = NULL;
  writeNPYdata(fout, nh, data);
}

// dummy function for template.
void npywrite_dummy_float() {
  std::ofstream fout;
  struct NPYheader_t nh;
  float *data = NULL;
  writeNPYdata(fout, nh, data);
}
