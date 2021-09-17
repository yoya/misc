#ifndef C___NPY_NPY_HPP_
#define C___NPY_NPY_HPP_
/*
 * Copyright 2019/08/14- yoya@awm.jp
 */

#include <string>
#include <vector>

#define NPY_FILE_SIG "\x93NUMPY"
#define NPY_FILE_SIG_LEN 6

struct NPYheader_t {
  std::vector<int> shape;
  std::string datatype;  // |u1(uint8) or <f4(float32)
};

extern struct NPYheader_t readNPYheader(std::ifstream &fin);
extern void writeNPYheader(std::ofstream &fin, const struct NPYheader_t &nh);

template<typename T>
void readNPYdata(std::ifstream &fin, const struct NPYheader_t &nh,
                 T *data);
template<typename T>
void writeNPYdata(std::ofstream &fout, const struct NPYheader_t &nh,
                  T *data);

#endif  // C___NPY_NPY_HPP_
