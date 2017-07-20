/*
  0 fill to binary.
*/
#include <stdio.h>
#include <stdlib.h>

void usage(void) {
  fprintf(stderr, "Usage: bin0fill <file> <offset of 0 fill start>\n");
}

int main(int argc, char **argv) {
  char *filename ;
  FILE *fp;
  int i, offset;
  if (argc < 3) {
    usage();
    return EXIT_FAILURE;
  }
  filename  = argv[1];
  fp = fopen(filename, "rb");
  if (fp == NULL) {
    fprintf(stderr, "Can't open file:%s\n", filename);
    usage();
    return EXIT_FAILURE;
  }
  offset = strtol(argv[2], NULL, 10);
  //
  for (i = 0 ; i < offset ; i++) { // copy
    char c = fgetc(fp);
    if (c == EOF) {
      break;
    }
    fputc(c, stdout);
  }
  while (fgetc(fp) != EOF) { // 0 fill
    fputc('\0', stdout);
  }
  //
  fclose(fp);
  return EXIT_SUCCESS;
}
