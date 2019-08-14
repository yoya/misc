#include <iostream>
#include <fstream>
#include <string>
#include <map>
#include <vector>

/*
  read & dump numpy array file.
  % g++ npydump.cc -std=c++11 -Wall -Wextra
 */

void usage() {
  std::cerr << "Usage: npydump <npyfile>" << std::endl;
}

// " ABC " => "ABC"
static std::string trim(const std::string str) {
  const char *trimChara = " \t\r\n";
  std::string::size_type left = str.find_first_not_of(trimChara);
  if (left  == std::string::npos) {
    return "";
  }
  std::string::size_type right = str.find_last_not_of(trimChara);
  return str.substr(left, right - left + 1);
}

// "{...}" => "..."
std::string extractInner(std::string strdata, std::string leftSep, std::string rightSep) {
  auto braseFirstPos = strdata.find_first_of(leftSep);
  auto braseLastPos = strdata.find_last_of(rightSep);
  if ((braseFirstPos == std::string::npos) ||
      (braseLastPos == std::string::npos)) {
    return "";
  }
  return strdata.substr(braseFirstPos + 1, braseLastPos - braseFirstPos - 1);
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
          std::cerr << "can't closing bracket" << std::endl;
          break;
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
    return std::pair<std::string, std::string>("", "");
  }
  std::string key = strdata.substr(0, pos);
  std::string value = strdata.substr(pos + 1, strdata.size() - pos);
  key  = trim(key);
  value = trim(value);
  auto tmp = extractInner(key, "'", "'");
  if (tmp.size() > 0) {
    key = tmp;
  }
  tmp = extractInner(value, "'", "'");
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
      std::pair<std::string, std::string> keyvalue = jsonKeyValueSplit(jsonElem);
      if (keyvalue.first.size() > 0) {
        jsonMap[keyvalue.first] = keyvalue.second;
      }
  }
  return jsonMap;
}

int readNPYheader(std::ifstream &fin, int &bitdepth,
                  int &width, int &height, int &channels) {
  char sig[6];
  uint16_t ver;
  unsigned short jsonlen;
  fin.read(sig, 6);
  if (std::memcmp(sig, "\x93NUMPY", 6) != 0) {
    std::cerr << "wrong npy signature" << sig << std::endl;
    return 1;
  }
  fin.read((char *) &ver, sizeof(uint16_t));
  // ver = (ver << 8) | (ver >> 8); // big => little endian
  fin.read((char *) &jsonlen, sizeof(uint16_t));
  // jsonlen = (jsonlen << 8) | (jsonlen >> 8); // big => little endian
  if (0x80 < (0x0a + jsonlen)) {
    std::cerr << "too long json length" << jsonlen << std::endl;
    return 1;
  }
  std::string jsondata(jsonlen, '\0');
  if (! fin.read((char *)&(jsondata[0]), jsonlen)) {
    std::cerr << "too short file" << jsonlen << std::endl;
    return 1;
  }
  // std::cerr << jsondata << std::endl;
  // {'descr': '|u1', 'fortran_order': False, 'shape': (46, 70, 3), }
  std::map<std::string, std::string> jsonMap = parseJson(jsondata);
  for (auto itr = jsonMap.begin(); itr != jsonMap.end(); ++itr) {
    std::string key = itr->first, value = itr->second;
    if (key == "descr") {
      if (value != "|u1") {
        std::cerr << "descr must be lu1" << std::endl;
        return 1;
      }
      bitdepth = 8;
    } else if (key =="fortran_order") {
      if (value != "False") {
        std::cerr << "fortran_order must be False" << std::endl;
        std::exit (1);
      }
    } else if (key == "shape") {
      value = extractInner(value, "(", ")");
      std::vector<std::string> numstrList = jsonCommaSplit(value);
      if (numstrList.size() != 3) {
        std::cerr << "Wrong shape size:" << numstrList.size() << std::endl;
        return 1;
      }
      height = std::stoi(numstrList[0]);
      width = std::stoi(numstrList[1]);
      channels = std::stoi(numstrList[2]);
    } else {
      std::cerr << "Unknown json keye:" << key << std::endl;
        return 1;
    }
  }
  if (channels != 3) {
    std::cerr << "Wrong channels:" << channels << std::endl;
    return 1;
  }
  return 0;
}

int main(int argc, char **argv) {
  int bitdepth = 0, width = 0, height = 0, channels = 0;
  if (argc < 2) {
    usage();
    std::exit (1);
  }
  char *infile = argv[1];
  std::ifstream fin( infile, std::ios::in | std::ios::binary );
  if (!fin){
    std::cerr << "Cant' open file:" << infile << std::endl;
    std::exit (1);
  }
  int ret = readNPYheader(fin, bitdepth, width, height, channels);
  if (ret != 0) {
    std::cerr << "Can't read NPY header" << std::endl;
    std::exit (1);
  }
  std::cerr << "width:" << width << " height:" << height << " channels:" << channels << std::endl;
  for (int y = 0 ; y < height ; y++) {
    for (int x = 0 ; x < width ; x++) {
      for (int c = 0 ; c < channels ; c++) {
        int cc = fin.get();
        if (cc < 0) {
          std::cerr << "incompleted rgb-datae:" << std::endl;
          std::exit (1);
        }
        std::cout <<  std::hex << cc;
      }
      std::cout << " ";
    }
    std::cout << std::endl;
  }
  std::cerr << "OK" << std::endl;
}
