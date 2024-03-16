#ifndef __WEBP_DUMP_H__
#define __WEBP_DUMP_H__

#include "webp/encode.h"

extern void PrintGifError(void);

void WebPDumpConfig(WebPConfig *config) {
  printf("WebPDumpConfig\n");
  printf("  lossless: %d  ", config->lossless);
  printf("  quality: %.3f  ", config->quality);
  printf("  method: %d\n", config->method);
  printf("  image_hint: %d  # default:%d, picture:%d, photo:%d, graph:%d\n",
         config->image_hint, WEBP_HINT_DEFAULT, WEBP_HINT_PICTURE, WEBP_HINT_PHOTO, WEBP_HINT_GRAPH);
  printf("  target_size: %d  ", config->target_size);

  printf("  target_PSNR: %.3f  ", config->target_PSNR);
  printf("  segments: %d\n", config->segments);
  printf("  sns_strength: %d  ", config->sns_strength);
  printf("  filter_strength: %d  ", config->filter_strength);
  printf("  filter_sharpness: %d\n", config->filter_sharpness);
  printf("  filter_type: %d  ", config->filter_type);
  printf("  autofilter: %d\n", config->autofilter);
  printf("  alpha_compression: %d  ", config->alpha_compression);
  printf("  alpha_filtering: %d (0:none, 1:fast, 2:best)\n", config->alpha_filtering);
  printf("  alpha_quality: %d\n", config->alpha_quality);
  printf("  pass: %d  ", config->pass);
  printf("  show_compressed: %d\n", config->show_compressed);
  printf("  preprocessing: 0x%x (0:none, 1=segment-smooth, 2=pseudo-random dithering)\n", config->preprocessing);
  printf("  partitions: %d  ", config->partitions);
  printf("  partition_limit: %d\n", config->partition_limit);
  printf("  emulate_jpeg_size: %d\n", config->emulate_jpeg_size);
  printf("  thread_level:%d  ", config->thread_level);
  printf("  low_memory:%d\n", config->low_memory);
  printf("  near_lossless:%d  ", config->near_lossless);
  printf("  exact:%d  ", config->exact);
  printf("  use_delta_palette:%d  ", config->use_delta_palette);
  printf("  use_sharp_yuv:%d\n", config->use_sharp_yuv);
}

#endif  // __WEBP_DUMP_H__
