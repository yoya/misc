/*
 * Copyright 2019/08/14- yoya@awm.jp
 */

#define NPY_FILE_SIG "\x93NUMPY"

struct NPYheader_t {
  std::vector<int> shape;
  std::string valuetype; // u1(uchar)
};

extern struct NPYheader_t readNPYheader(std::ifstream &fin);

template<typename T>
void readNPYdata(std::ifstream &fin, const struct NPYheader_t &nh,
                 T *imagedata);