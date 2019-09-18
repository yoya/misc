/*
 * Copyright 2019/08/14- yoya@awm.jp
 */

#include <iostream>
#include <fstream>
#include <string>
#include <map>
#include <vector>
#include <sstream>

/*
  read & dump numpy array file.
  % g++ npydump.cc -std=c++11 -Wall -Wextra
 */

struct NPYheader_t {
  int depth;
  int width, height;
  int channels;
};

extern struct NPYheader_t readNPYheader(std::ifstream &fin);
extern void readNPYimagedata(std::ifstream &fin, const struct NPYheader_t &nh,
                             uint8_t *imagedata);

void usage() {
  std::cerr << "Usage: npydump <npyfile>" << std::endl;
}

// " ABC " => "ABC"
static std::string trim(const std::string str) {
  const char *trimChara = " \t\r\n";
  auto left = str.find_first_not_of(trimChara);
  if (left  == std::string::npos) {
    return "";
  }
  auto right = str.find_last_not_of(trimChara);
  return str.substr(left, right - left + 1);
}

// "{...}" => "..."
std::string extractInner(std::string strdata,
                         std::string leftSep, std::string rightSep) {
  auto braseFirstPos = strdata.find_first_of(leftSep);
  auto braseLastPos = strdata.find_last_of(rightSep);
  if ((braseFirstPos == std::string::npos) ||
      (braseLastPos == std::string::npos)) {
    return "";
  }
  return strdata.substr(braseFirstPos + 1, braseLastPos - braseFirstPos - 1);
}
std::string extractInner(std::string strdata, std::string sep) {
  return extractInner(strdata, sep, sep);
}

// "A,B,(C,D,E)" => "A","B","(C,E,E)"
std::vector<std::string> jsonCommaSplit(std::string strdata) {
  std::vector<std::string> jsonVector;
  std::string::size_type cur = 0, pos = 0;
  for (cur = 0 ; cur < strdata.size() && (pos != std::string::npos) ; cur++) {
    pos = strdata.find_first_of(",", cur);
    if (pos == std::string::npos) {
      pos = strdata.size();
    }
    auto bCur = strdata.find_first_of("(", cur);
    if (bCur != std::string::npos) {
      if (bCur < pos) {
        auto bCur2 = strdata.find_first_of(")", bCur + 1);
        if (bCur2 != std::string::npos) {
          auto bCur3 = strdata.find_first_of(",", bCur2 + 1);
          pos = bCur3;
        } else {
          throw std::runtime_error("can't closing bracket");
        }
      }
    }
    if (cur !=  pos) {
      auto value = strdata.substr(cur, pos - cur);
      jsonVector.push_back(trim(value));
    }
    cur = pos;
  }
  return jsonVector;
}
// "'A': B" =>  "A" => "B"
std::pair<std::string, std::string> jsonKeyValueSplit(std::string strdata) {
  auto pos = strdata.find_first_of(":", 0);
  if (pos == std::string::npos) {
    return std::pair<std::string, std::string>("", "");  // failed
  }
  std::string key = strdata.substr(0, pos);
  std::string value = strdata.substr(pos + 1, strdata.size() - pos);
  key  = trim(key);
  value = trim(value);
  auto tmp = extractInner(key, "'");
  if (tmp.size() > 0) {
    key = tmp;
  }
  tmp = extractInner(value, "'");
  if (tmp.size() > 0) {
    value = tmp;
  }
  return std::pair<std::string, std::string>(key, value);
}

// support only flat-1d assoc-array
std::map<std::string, std::string> parseJson(std::string jsondata) {
  std::map<std::string, std::string> jsonMap;
  std::string innerBracesData = extractInner(jsondata, "{", "}");
  // std::cerr << innerBracesData << std::endl;
  std::vector<std::string> jsonList = jsonCommaSplit(innerBracesData);
  for (auto jsonElem : jsonList) {
    auto  keyvalue = jsonKeyValueSplit(jsonElem);
      if (keyvalue.first.size() > 0) {
        jsonMap[keyvalue.first] = keyvalue.second;
      }
  }
  return jsonMap;
}

struct NPYheader_t readNPYheader(std::ifstream &fin) {
  char sig[6];
  uint16_t ver;
  uint16_t jsonlen;
  std::stringstream ss;
  NPYheader_t header;
  fin.read(sig, 6);
  if (std::memcmp(sig, "\x93NUMPY", 6) != 0) {
    ss << "wrong npy signature:" << sig;
    throw std::runtime_error(ss.str());
  }
  fin.read(reinterpret_cast<char *>(&ver), sizeof(uint16_t));
  fin.read(reinterpret_cast<char *>(&jsonlen), sizeof(uint16_t));
  if ((* reinterpret_cast<const uint16_t*>("\1\0")) != 1) {
    // native order = big endian
    ver = (ver << 8) | (ver >> 8);
    jsonlen = (jsonlen << 8) | (jsonlen >> 8);
  }
  if (0x80 < (0x0a + jsonlen)) {
    ss << "too long json length:" << jsonlen;
    throw std::runtime_error(ss.str());
  }
  std::string jsondata(jsonlen, '\0');
  if (!fin.read(reinterpret_cast<char *>(&(jsondata[0])), jsonlen)) {
    ss << "too short file for jsonlen:" << jsonlen;
    throw std::runtime_error(ss.str());
  }
  // std::cerr << jsondata << std::endl;
  // {'descr': '|u1', 'fortran_order': False, 'shape': (46, 70, 3), }
  std::map<std::string, std::string> jsonMap = parseJson(jsondata);
  for (auto itr = jsonMap.begin(); itr != jsonMap.end(); ++itr) {
    std::string key = itr->first, value = itr->second;
    if (key == "descr") {
      if (value != "|u1") {
        ss << "descr:" << value << ", must be lu1";
        throw std::runtime_error(ss.str());
      }
      header.depth = 8;
    } else if (key =="fortran_order") {
      if (value != "False") {
        throw std::runtime_error("fortran_order must be False");
      }
    } else if (key == "shape") {
      value = extractInner(value, "(", ")");
      std::vector<std::string> numstrList = jsonCommaSplit(value);
      if (numstrList.size() != 3) {
        ss << "Wrong shape size:" << numstrList.size();
        throw std::runtime_error(ss.str());
      }
      header.height = std::stoi(numstrList[0]);
      header.width = std::stoi(numstrList[1]);
      header.channels = std::stoi(numstrList[2]);
    } else {
      ss << "Unknown json keye:" << key;
      throw std::runtime_error(ss.str());
    }
  }
  if (header.channels != 3) {
    ss << "Wrong channels:" << header.channels;
    throw std::runtime_error(ss.str());
  }
  return header;
}

void readNPYimagedata(std::ifstream &fin, const struct NPYheader_t &nh,
                      uint8_t *imagedata) {
  if (imagedata == NULL) {
    throw std::runtime_error("imagedata == NULL");
  }
  int n = nh.width * nh.height * nh.channels;
  for (int i = 0 ; i < n ; ++i) {
    int cc = fin.get();
    if (cc < 0) {
      throw std::runtime_error("incompleted rgb-data");
    }
    imagedata[i] = cc;
  }
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
