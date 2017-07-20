/*
  (c) 2017/07/20 yoya@awm.jp
  ref) https://github.com/yoya/misc/blob/master/c/png_dump.c
*/

#include <stdio.h>
#include <stdlib.h>
#include <string.h> // memcpy
#include <sys/types.h>
#include <png.h>
#include "bitstream.h"

int main(int argc, char **argv) {
    char *png_filename = NULL;
    FILE *fp = NULL;
    png_uint_32 png_width, png_height;
    int bpp, color_type;
    int interlace, comptype, filtertype;
    png_bytep image_data;
    png_uint_32 y;
    png_color *palette = NULL;
    int palette_num = 0;
    png_bytep trans = NULL;
    int num_trans;
    png_color_16p trans_values = NULL;
    double file_gamma;
    int pass; // interlace pass phase
    if (argc != 2) {
        char *program_filename = argv[0];
        fprintf(stderr, "Usage: %s <png_filename>\n", program_filename);
        return EXIT_FAILURE;
    }
    png_filename = argv[1];
    fp = fopen(png_filename, "rb");
    if (! fp) {
        fprintf(stderr, "Can't open file(%s)\n", png_filename);
        return EXIT_FAILURE;
    }

    /*
     * png reader setup
     */
    png_structp png_ptr = png_create_read_struct(PNG_LIBPNG_VER_STRING, NULL,NULL,NULL);
    if (! png_ptr) {
      fprintf(stderr, "can't create read_struct\n");
        return EXIT_FAILURE;
    }
    png_infop png_info_ptr = png_create_info_struct(png_ptr);
    if (! png_info_ptr) {
        fprintf(stderr, "can't create info_struct\n");
        png_destroy_read_struct (&png_ptr, NULL, NULL);
        return EXIT_FAILURE;
    }
    png_init_io(png_ptr, fp);

    /*
     * png writer setup
     */
    png_structp png_write_ptr = png_create_write_struct(PNG_LIBPNG_VER_STRING, NULL,NULL,NULL);
    if (! png_write_ptr) {
      fprintf(stderr, "can't create write_struct\n");
        return EXIT_FAILURE;
    }
    png_init_io(png_write_ptr, stdout);

    /*
     * metadata reading
     */
    png_read_info(png_ptr, png_info_ptr);
    png_get_IHDR(png_ptr, png_info_ptr,
                 &png_width, &png_height, &bpp, &color_type,
                 &interlace, &comptype, &filtertype);
    // printf("(width, height)=(%u,%u) bpp=%d", png_width, png_height, bpp);
    // printf(" color_type=%d", color_type);

    image_data = (png_bytep) malloc(png_get_rowbytes(png_ptr, png_info_ptr));

    png_get_tRNS(png_ptr, png_info_ptr, &trans, &num_trans, &trans_values);
    png_get_PLTE(png_ptr, png_info_ptr, &palette, &palette_num);
    png_get_gAMA(png_ptr, png_info_ptr, &file_gamma);
    // TODO: cHRM, bKGD, tIME, tEXt

    /*
     * metadata setting.
     */
    png_write_info(png_write_ptr, png_info_ptr);
    png_set_IHDR(png_write_ptr, png_info_ptr,
                 png_width, png_height, bpp, color_type,
                 interlace, comptype, filtertype);

    if (png_get_valid(png_ptr, png_info_ptr, PNG_INFO_tRNS)) {
      png_set_tRNS(png_write_ptr, png_info_ptr, trans, num_trans,
		   trans_values);
    }
    if (png_get_valid(png_ptr, png_info_ptr, PNG_INFO_PLTE)) {
      png_set_PLTE(png_write_ptr, png_info_ptr, palette, palette_num);
    }

    if (png_get_valid(png_ptr, png_info_ptr, PNG_INFO_gAMA)) {
      png_set_gAMA(png_write_ptr, png_info_ptr, file_gamma);
    }

    /*
     * image read & writing each line
     */

    pass = png_set_interlace_handling(png_ptr);
    pass = png_set_interlace_handling(png_write_ptr);
    // fprintf(stderr, "pass:%d\n", pass);
    for (int p = 0 ; p < pass ; p++) {
      for (y = 0 ; y < png_height ; y++) {
	png_read_row(png_ptr, image_data, NULL);
	png_write_row(png_write_ptr, image_data);
      }
    }
    png_write_end(png_write_ptr, png_info_ptr);

    /*
     * finish
     */
    free(image_data);
    png_destroy_read_struct(&png_ptr, &png_info_ptr, NULL);
    png_destroy_write_struct(&png_write_ptr, NULL);
    return EXIT_SUCCESS;
}
