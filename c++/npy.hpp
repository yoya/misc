/*
 * Copyright 2019/08/14- yoya@awm.jp
 */

struct NPYheader_t {
  int depth;
  int width, height;
  int channels;
};

extern struct NPYheader_t readNPYheader(std::ifstream &fin);

template<typename T>
void readNPYimagedata(std::ifstream &fin, const struct NPYheader_t &nh,
                      T *imagedata);
