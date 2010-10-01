#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <gif_lib.h>


int myDGifFileCopy(GifFileType *GifFileIn, GifFileType *GifFileOut) {
    if (! (GifFileIn && GifFileOut)) {
        return GIF_ERROR;
    }
    GifFileOut->SWidth  = GifFileIn->SWidth;
    GifFileOut->SHeight = GifFileIn->SHeight;
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

int getTransparentIndex(SavedImage Image) {
    int i;
    unsigned char transparent_index = -1;
    if ((Image.ExtensionBlockCount == 0) || (Image.ExtensionBlocks == NULL)) {
        return -1;
    }
    for (i = 0 ; i < Image.ExtensionBlockCount ; i++ ) {
        ExtensionBlock Block = Image.ExtensionBlocks[i];
        if (Block.Function == GRAPHICS_EXT_FUNC_CODE){
            int gcntl_flag = Block.Bytes[0];
            if (gcntl_flag & 0x01) {
                transparent_index = Block.Bytes[3];
                return transparent_index;
            }
        }
    }
    return transparent_index;
}

/* under construction : index=0xff fixed. failed no extension block */

int setTransparentIndex(SavedImage *Image) {
    int i;
    int transparent_index = -1;
    if ((Image->ExtensionBlockCount > 0) && (Image->ExtensionBlocks)) {
        for (i = 0 ; i < Image->ExtensionBlockCount ; i++ ) {
            ExtensionBlock *Block = & Image->ExtensionBlocks[i];
            if (Block->Function == GRAPHICS_EXT_FUNC_CODE){
                transparent_index = 0xff;
                Block->Bytes[0] |= 0x01;
                Block->Bytes[3]= transparent_index;
                return transparent_index;
            }
        }
    }
    return transparent_index;
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
    char *gif_filename, *mask_filename;
    struct stat filestat;
    FILE *fp = NULL;
    my_gif_buffer gif_buff_in, gif_buff_out;
    GifFileType *GifFileIn = NULL, *GifFileOut = NULL;
    int i, x, y;
    int transparent_index;
    int main_result_code = EXIT_SUCCESS;
    /*
     * argument
     */
    if (argc != 3) {
        char *program_filename = argv[0];
        fprintf(stderr, "Usage: %s <gif_file> <mask_file>\n", program_filename);
        return EXIT_FAILURE;
    }
    gif_filename = argv[1];
    mask_filename = argv[2];

    /*
     * file read
     */
    if(stat(gif_filename, &filestat)) {
        perror("stat gif_file");
        return EXIT_FAILURE;
    }
    gif_buff_in.data_len = filestat.st_size;
    gif_buff_in.data_offset = 0;
    fp = fopen(gif_filename, "rb");
    if (! fp) {
        fprintf(stderr, "Can't open file(%s)\n", gif_filename);
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
    // Decode
    DGifSlurp(GifFileIn);
    if(stat(mask_filename, &filestat)) {
        perror("stat mask_file");
        main_result_code = EXIT_FAILURE;
        goto finish;
    }
    if (filestat.st_size != GifFileIn->SWidth * GifFileIn->SHeight) {
        fprintf(stderr, "mask file size(%ld) != SWidth(%d) * SHeight(%d)\n",
                filestat.st_size, GifFileIn->SWidth, GifFileIn->SHeight);
        main_result_code = EXIT_FAILURE;
        goto finish;
    }
    
    // Copy
    myDGifFileCopy(GifFileIn, GifFileOut);

    // Transparent Masking
    SavedImage Image = GifFileOut->SavedImages[0];
    SavedImage *Image_p = & GifFileOut->SavedImages[0];
    //   - get Transparent Index from Extention block
    transparent_index = getTransparentIndex(Image);
    if (transparent_index < 0) {
        transparent_index = setTransparentIndex(Image_p);
    }
    if (transparent_index < 0) {
        fprintf(stderr, "no transparent index\n");
        main_result_code = EXIT_FAILURE;
        goto finish;
    }
    //   - scan and fill masking data
    fp = fopen(mask_filename, "rb");
    i = 0;
    for ( y = 0 ; y < Image.ImageDesc.Height ; y++ ) {
        for ( x = 0 ; x < Image.ImageDesc.Width ; x++) {
            int alpha = fgetc(fp);
            if (alpha < 128) { // XXX
                Image.RasterBits[i] = transparent_index;
            }
            i++;
        }
    }
    fclose(fp);
    // Encode
    
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
