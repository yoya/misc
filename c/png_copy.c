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
    int is_png;
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
    double file_gamma;
	
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

    // reader
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
    // writer
    png_structp png_write_ptr = png_create_write_struct(PNG_LIBPNG_VER_STRING, NULL,NULL,NULL);

    png_init_io(png_ptr, fp);
    png_init_io(png_write_ptr, stdout);
    
    png_read_info(png_ptr, png_info_ptr);

    /*
     * reading
     */
    
    png_get_IHDR(png_ptr, png_info_ptr,
                 &png_width, &png_height, &bpp, &color_type,
                 NULL, NULL, NULL);
    // printf("(width, height)=(%u,%u) bpp=%d", png_width, png_height, bpp);
    // printf(" color_type=%d", color_type);

    png_get_tRNS(png_ptr, png_info_ptr, &trans, &num_trans, &trans_values);
    png_get_PLTE(png_ptr, png_info_ptr, &palette, &palette_num);
    png_get_gAMA(png_ptr, png_info_ptr, &file_gamma);

    image_data = (png_bytepp) malloc(png_height * sizeof(png_bytep));
    for (y=0; y < png_height; y++) {
      image_data[y] = (png_bytep) malloc(png_get_rowbytes(png_ptr, png_info_ptr));
    }
    png_read_image(png_ptr, image_data);

    /*
     * edit something here.
     */

    /*
     * set meta information
     */
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
     * writing
     */
    png_write_info(png_write_ptr, png_info_ptr);
    png_write_image(png_write_ptr, image_data);
    png_write_end(png_write_ptr, png_info_ptr);

    /*
     * finish
     */
    for (y=0; y < png_height; y++) {
        free(image_data[y]);
    }
    free(image_data);
    png_destroy_read_struct(&png_ptr, &png_info_ptr, NULL);
    png_destroy_write_struct(&png_write_ptr, NULL);
    return EXIT_SUCCESS;
}
