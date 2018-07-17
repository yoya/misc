/*
  (c) yoya@awm.jp 2018/07/17-
  libpng sample
  ref) 
  - http://www.libpng.org/pub/png/libpng-1.2.5-manual.html
  - http://www.amy.hi-ho.ne.jp/jbaba/png2dib.c
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h> // memcpy
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <png.h>
#include "bitstream.h"

int main(int argc, char **argv) {
    char *png_filename = NULL;
    FILE *fp = NULL;
    png_uint_32 png_width, png_height;
    int bpp, color_type;
    png_bytepp image_data;
    png_uint_32 x, y;
    png_color *palette = NULL;
    int palette_num = 0;
    png_bytep trans = NULL;
    int num_trans;
    png_color_16p trans_values = NULL;
    int palette_dump_unit;
    if (argc != 2) {
        char *program_filename = argv[0];
        fprintf(stderr, "Usage: %s <png_filename>\n", program_filename);
        return EXIT_FAILURE;
    }
    png_filename = argv[1];
//    printf("png_filename=%s\n", png_filename);

    fp = fopen(png_filename, "rb");
    if (! fp) {
        fprintf(stderr, "Can't open file(%s)\n", png_filename);
        return EXIT_FAILURE;
    }

    png_structp png_ptr = png_create_read_struct
        (PNG_LIBPNG_VER_STRING, NULL,NULL,NULL);
    if (! png_ptr) {
      fprintf(stderr, "can't create read_struct\n");
        return EXIT_FAILURE;
    }
    png_init_io(png_ptr, fp);

    png_infop png_info_ptr = png_create_info_struct(png_ptr);
    if (! png_info_ptr) {
        fprintf(stderr, "can't create info_struct\n");
        png_destroy_read_struct (&png_ptr, NULL, NULL);
        return EXIT_FAILURE;
    }
    png_read_info(png_ptr, png_info_ptr);
    png_get_IHDR(png_ptr, png_info_ptr,
                 &png_width, &png_height, &bpp, &color_type,
                 NULL, NULL, NULL);
    printf("(width, height)=(%u,%u) bpp=%d",
           png_width, png_height, bpp);
    printf(" color_type=%d", color_type);
    switch(color_type) {
      case PNG_COLOR_TYPE_GRAY:
	  printf("(GRAY)");
	  if (! png_get_tRNS(png_ptr, png_info_ptr, &trans, &num_trans,
			     &trans_values)) {
	      num_trans = 0;
	  }
          printf(" num_trans=%d", num_trans);
	  break;
      case PNG_COLOR_TYPE_GRAY_ALPHA:
	  printf("(GRAY_ALPHA)");
	  break;
      case PNG_COLOR_TYPE_RGB:
	  printf("(RGB)");
	if (png_get_PLTE(png_ptr, png_info_ptr, &palette, &palette_num)) {
	    printf(" palette_num=%d", palette_num);
	}
	  if (! png_get_tRNS(png_ptr, png_info_ptr, &trans, &num_trans,
			     &trans_values)) {
	      num_trans = 0;
	  }
          printf(" num_trans=%d", num_trans);
	  break;
      case PNG_COLOR_TYPE_RGB_ALPHA:
        printf("(RGB_ALPHA)");
	if (png_get_PLTE(png_ptr, png_info_ptr, &palette, &palette_num)) {
	    printf(" palette_num=%d", palette_num);
	}

	//        if (png_get_valid(png_ptr, png_info_ptr, PNG_INFO_tRNS))
	// png_set_tRNS_to_alpha(png_ptr);
        break;
      case PNG_COLOR_TYPE_PALETTE:
          printf("(PALETTE)");
          png_get_PLTE(png_ptr, png_info_ptr, &palette, &palette_num);
          printf(" palette_num=%d", palette_num);
          if (! png_get_tRNS(png_ptr, png_info_ptr, &trans, &num_trans,
                           &trans_values)) {
              num_trans = 0;
          }
          printf(" num_trans=%d", num_trans);
          break;
      default:
           printf("color_type(%d) not implement yet.\n",
                  color_type);
    }
    printf("\n");
    /*
      meta data
    */
    double file_gamma;
    if (png_get_gAMA(png_ptr, png_info_ptr, &file_gamma)) {
      printf("gamma: %f\n", file_gamma);
    }
    /*
      image
     */
    image_data = (png_bytepp) malloc(png_height * sizeof(png_bytep));
    for (y=0; y < png_height; y++) {
        image_data[y] = (png_bytep) malloc(png_get_rowbytes(png_ptr, png_info_ptr));
    }
    png_read_image(png_ptr, image_data);
    if (num_trans == 0) {
        palette_dump_unit = 8;
    } else {
        palette_dump_unit = 4;
    }
    if (color_type == PNG_COLOR_TYPE_PALETTE) {
        int i, j;
        for (i = 0 ; i < palette_num ; i+=palette_dump_unit ) {
            printf("[%03d]", i);
            for (j = 0 ; (j < palette_dump_unit) && ((i+j) < palette_num) ; j++) {
                if ((i+j) < num_trans) {
                    printf(" #%02x%02x%02x(%02x)",
                           palette[i+j].red,
                           palette[i+j].green,
                           palette[i+j].blue,
                           trans[i+j] & 0xff);
                } else {
                    printf(" #%02x%02x%02x",
                           palette[i+j].red,
                           palette[i+j].green,
                           palette[i+j].blue);
                }
            }
            printf("\n");
        }
        for (y=0; y < png_height; y++) {
            unsigned char *linedata = image_data[y];
            bitstream_t *bs = bitstream_open();
            bitstream_input(bs, linedata, png_get_rowbytes(png_ptr, png_info_ptr));
            printf("y=%u: ", y);
            for (x=0; x < png_width; x++) {
                int colorindex = bitstream_getbits(bs, bpp);
                printf("%02x  ", colorindex);
            }
            bitstream_close(bs);
            printf("\n");
        }
    } else {
        for (y=0; y < png_height; y++) {
            printf("y=%u: ", y);
            for (x=0; x < png_width; x++) {
                switch(color_type) {
                case PNG_COLOR_TYPE_GRAY:
                    printf("%02x ",
                           image_data[y][x]);
                    break;
                case PNG_COLOR_TYPE_GRAY_ALPHA:
                    printf("%02x(%02x) ",
                           image_data[y][2*x], image_data[y][2*x+1]);
                    break;
                case PNG_COLOR_TYPE_RGB:
                    printf("%02x%02x%02x  ",
                           image_data[y][3*x],
                           image_data[y][3*x+1],
                           image_data[y][3*x+2]);
                    break;
                case PNG_COLOR_TYPE_RGB_ALPHA:
                    printf("%02x%02x%02x(%02x)  ",
                           image_data[y][4*x],
                           image_data[y][4*x+1],
                           image_data[y][4*x+2],
                           image_data[y][4*x+3]);

                    break;
                default:
                    break;
                }
            }
            printf("\n");
        }
    }
    /*
     * finish
     */
    for (y=0; y < png_height; y++) {
        free(image_data[y]);
    }
    free(image_data);
    png_destroy_read_struct(&png_ptr, &png_info_ptr, NULL);
    return EXIT_SUCCESS;
}
