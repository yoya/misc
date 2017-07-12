/*
  gcc webp_dump.c -lwebp -lwebpmux -W -Wall
*/
#include <stdio.h>
#include <stdlib.h>
#include <sys/stat.h>
#include <unistd.h>
#include <ctype.h>

#include <webp/mux.h>
#include <webp/demux.h>
#include <webp/decode.h>

int myfread_WebPData(char *filename, WebPData *data) {
  struct stat filestat;
  if(stat(filename, &filestat)) {
    perror("stat webp file");
    return EXIT_FAILURE;
  }
  data->size = filestat.st_size;
  data->bytes = malloc(data->size);
  FILE *fp = fopen(filename, "rb");
  if (! fp) {
    free((void *) data->bytes);
    return 1;
  }
  fread((uint8_t *) data->bytes, 1, data->size, fp);
  fclose(fp);
  return 0;
}

void mydump_WebPData(WebPData *data) {
  size_t i, j;
  for (i = 0 ; i < data->size ; i++) {
    printf("%02x ", data->bytes[i]);
    if ((i % 8) == (8 - 1)) { printf(" "); }
    if ((i % 16) == (16 - 1) ||	(i == (data->size - 1))) {
      for (j = i%16 ; j < 16 ; j++) {
	printf("   ");
	if (j == (8 - 1)) { printf(" "); }
      }
      for (j = i - (i%16) ; j < i ; j++) {
	unsigned char  c = data->bytes[j];
	printf("%c", isgraph(c)?c:' ');
	if ((j % 8) == (8 - 1)) { printf(" "); }
      }
      printf("\n");
    }
  }
}

void mydump_WebPDecBuffer(WebPDecBuffer *buffer) {
  int nComp;
  int cs = buffer->colorspace;
  if (! WebPIsRGBMode(cs)) {
    fprintf(stderr, "Can't dump non-RGB(A) colorspace:%d", buffer->colorspace);
    return ;
  }
  if ((cs == MODE_RGBA_4444) || (cs == MODE_RGB_565) || (cs == MODE_rgbA_4444)) {
    nComp = 2;
  } else {
    nComp = (WebPIsAlphaMode(cs))?4:3;
  }
  uint8_t* line = buffer->u.RGBA.rgba;
  for (int y = 0; y < buffer->height; ++y, line += buffer->u.RGBA.stride) {
    uint8_t* v = line;
    for (int x = 0; x < buffer->width ; ++x, v += nComp) {
      for (int c = 0 ; c < nComp ; c++) {
	printf("%02x", v[c]&0xff);
      }
      printf(" ");
    }
    printf("\n");
  }
}

int main(int argc, char **argv) {
  if (argc != 2) {
    char *program = argv[0];
    fprintf(stderr, "Usage: %s <webp_file>\n", program);
        return EXIT_FAILURE;
  }
  /*
   * Initialize
   */
  WebPData webp_data;
  WebPDecoderConfig config;
  WebPDataInit(&webp_data);
  WebPInitDecoderConfig(&config);
  /*
   * File Read
   */
  if (myfread_WebPData(argv[1], &webp_data)) {
    fprintf(stderr, "Can't open file(%s)\n", argv[1]);
    return EXIT_FAILURE;
  }
  /*
   * WebP Parse
   */
  int copy_data = 0;
  WebPMux *mux = WebPMuxCreate(&webp_data, copy_data);
  /*
   * ICC Profile
   */
  WebPData chunk_data;
  WebPMuxGetChunk(mux, "ICCP", &chunk_data);
  if (chunk_data.size > 0) {
    printf("ICC Profile:\n");
    mydump_WebPData(&chunk_data);
  }
  /*
   * Image Decode
   */
  WebPMuxFrameInfo frame;
  WebPMuxGetFrame(mux, 1, &frame);
  if (WebPDecode(frame.bitstream.bytes, frame.bitstream.size, &config) != VP8_STATUS_OK) {
    fprintf(stderr, "decode failed\n");
    return EXIT_FAILURE;
  }
  printf("ImageData:\n");
  mydump_WebPDecBuffer(&config.output);

  // Terminate
  WebPMuxDelete(mux);
  return EXIT_SUCCESS;
}
