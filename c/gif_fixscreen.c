#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <gif_lib.h>

/*
  Fix GIF Screen Size.                 2009/02/16- (c) yoya@awm.jp
  gcc gif_fixscreen.c -lgif
 */

int myDGifFileCopy(GifFileType *GifFileIn, GifFileType *GifFileOut) {
    if (! (GifFileIn && GifFileOut)) {
        return GIF_ERROR;
    }
    GifFileOut->SWidth =  GifFileIn->SWidth;
    GifFileOut->SHeight =  GifFileIn->SHeight;
    GifFileOut->SColorResolution =  GifFileIn->SColorResolution;
    GifFileOut->SBackGroundColor =  GifFileIn->SBackGroundColor;
    if (GifFileIn->SColorMap) {
        GifFileOut->SColorMap =  MakeMapObject(GifFileIn->SColorMap->ColorCount,
                                              GifFileIn->SColorMap->Colors);
    }
    GifFileOut->Image =  GifFileIn->Image;
    GifFileOut->ImageCount =  GifFileIn->ImageCount;
    GifFileOut->SavedImages = GifFileIn->SavedImages;
    return GIF_OK;
}

typedef struct my_gif_buffer_ {
    unsigned char *data;
    unsigned long data_len;
    unsigned long data_offset;
} my_gif_buffer;

int gif_data_read_func(GifFileType* GifFile, GifByteType* buf, int count) {
    my_gif_buffer *gif_buff = (my_gif_buffer *) GifFile->UserData;
    if (gif_buff->data_offset + count <= gif_buff->data_len) {
        memcpy(buf, gif_buff->data + gif_buff->data_offset, count);
        gif_buff->data_offset += count;
    } else {
        fprintf(stderr, "! gif_buff->data_offset(%lu) + count(%d) <= gif_buff->data_len(%lu)\n",
                gif_buff->data_offset, count, gif_buff->data_len);
        return 0;
    }
    return count;
}

int gif_data_write_func(GifFileType* GifFile, const GifByteType* buf, int count) {
    my_gif_buffer *gif_buff = (my_gif_buffer *) GifFile->UserData;
    unsigned long new_data_len;
    if (gif_buff->data_offset + count > gif_buff->data_len) {
        new_data_len = 2 * gif_buff->data_len;
        if (gif_buff->data_offset + count > new_data_len) {
            new_data_len = gif_buff->data_offset + count;
        }
        gif_buff->data = realloc(gif_buff->data, new_data_len);
        if (gif_buff->data == NULL) {
            fprintf(stderr, "gif_data_write_func: can't realloc: new_data_len(%lu), data_len(%lu)\n",
                    new_data_len, gif_buff->data_len);
            return 0;
        }
        gif_buff->data_len = new_data_len;
    }
    memcpy(gif_buff->data + gif_buff->data_offset, buf, count);
    gif_buff->data_offset += count;
    return count;
}

int main(int argc, char **argv) {
    char *gif_filename_in;
    struct stat filestat;
    FILE *fp = NULL;
    my_gif_buffer gif_buff_in, gif_buff_out;
    GifFileType *GifFileIn = NULL, *GifFileOut = NULL;
    int main_result_code = EXIT_SUCCESS;
    int max_width = 0, max_height = 0;
    int i;
    /*
     * argument check
     */
    if (argc != 2) {
        char *program_filename = argv[0];
        fprintf(stderr, "Usage: %s <gif_file>\n", program_filename);
        return EXIT_FAILURE;
    }
    gif_filename_in = argv[1];

    /*
     * file read
     */
    if(stat(gif_filename_in, &filestat)) {
        perror("stat giffile");
        return EXIT_FAILURE;
    }
    gif_buff_in.data_len = filestat.st_size;
    gif_buff_in.data_offset = 0;
    fp = fopen(gif_filename_in, "rb");
    if (! fp) {
        fprintf(stderr, "Can't open file(%s)\n", gif_filename_in);
        return EXIT_FAILURE;
    }
    gif_buff_in.data = calloc(gif_buff_in.data_len, 1);
    fread(gif_buff_in.data, 1, gif_buff_in.data_len, fp);
    fclose(fp);
    gif_buff_out.data = NULL;
    gif_buff_out.data_len = 0;
    gif_buff_out.data_offset = 0;
    /*
     * giflib
     */
    GifFileIn = DGifOpen(& gif_buff_in, gif_data_read_func);
    if (GifFileIn == NULL) {
        fprintf(stderr, "DGifOpen failed at L%d\n", __LINE__);
        main_result_code = EXIT_FAILURE;
        goto finish;
    }
    GifFileOut = EGifOpen(& gif_buff_out, gif_data_write_func);
    if (GifFileOut == NULL) {
        fprintf(stderr, "EGifOpen failed at L%d\n", __LINE__);
        main_result_code = EXIT_FAILURE;
        goto finish;
    }
    DGifSlurp(GifFileIn);
    myDGifFileCopy(GifFileIn, GifFileOut);
    /*
     * --- fix screen size begin ---
     */
    max_width = GifFileOut->SWidth;
    max_height = GifFileOut->SHeight;
    for (i = 0 ; i < GifFileOut->ImageCount ; i++) {
        GifImageDesc ImageDesc = GifFileOut->SavedImages[i].ImageDesc;
        int width = ImageDesc.Left + ImageDesc.Width;
        int height = ImageDesc.Top + ImageDesc.Height;
        if (max_width < width) {
            max_width = width;
        }
        if (max_height < height) {
            max_height = height;
        }
    }
    /*
     * --- fix screen size end ---
     */
    
    GifFileOut->SWidth = max_width;
    GifFileOut->SHeight = max_height;
    
    EGifSpew(GifFileOut);
    /*
     * output
     */
    if (gif_buff_out.data) {
        fwrite(gif_buff_out.data, 1, gif_buff_out.data_len, stdout);
    } else {
        fprintf(stderr, "gif_buff_out.data == NULL\n");
    }
    /*
     * destructor
     */
finish:
    if (GifFileOut) {
        EGifCloseFile(GifFileOut);
    }
    if (GifFileIn) {
        DGifCloseFile(GifFileIn);
    }
    free(gif_buff_in.data);
    free(gif_buff_out.data);
    return main_result_code;
}
