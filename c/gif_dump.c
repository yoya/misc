#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <unistd.h>
#include <gif_lib.h>

#define print_indent(num) if (num) { printf("%*s", num, " "); }

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

void print_colormap(ColorMapObject *ColorMap, int indent_num) {
    int i, j;
    int count;
    if (ColorMap == NULL) {
        print_indent(indent_num);
        printf("No ColorMap\n");
        return ;
    }
    count = ColorMap->ColorCount;
    print_indent(indent_num);
    printf("ColorMapObject:\n");
    print_indent(indent_num);
    printf("    ColorCount=%d  BitsPerPixel=%d\n",
           count, ColorMap->BitsPerPixel);
    print_indent(indent_num);
    printf("    Colors:\n");
    for (i = 0 ; i < count ; i+=8 ) {
        print_indent(indent_num);
        printf("        [%03d]", i);
        for (j = 0 ; (j < 8) & ((i+j) < count) ; j++) {
            printf(" #%02x%02x%02x",
                   ColorMap->Colors[i+j].Red,
                   ColorMap->Colors[i+j].Green,
                   ColorMap->Colors[i+j].Blue);
        }
        printf("\n");
    }
}

void print_image_desc(GifImageDesc ImageDesc) {
    printf("ImageDesc\n");
    printf("    (Left, Top)=(%d, %d)  (Width, Height)=(%d, %d)  Interlace=%d\n",
           ImageDesc.Left, ImageDesc.Top,
           ImageDesc.Width, ImageDesc.Height,
           ImageDesc.Interlace);
    print_colormap(ImageDesc.ColorMap, 4);
}

int main(int argc, char **argv) {
    char *gif_filename;
    struct stat filestat;
    FILE *fp;
    my_gif_buffer gif_buff;
    int i;
    int ImageCount;
    GifFileType *GifFile;
    if (argc != 2) {
        char *program_filename = argv[0];
        fprintf(stderr, "Usage: %s <gif_filename>\n", program_filename);
        return EXIT_FAILURE;
    }
    /*
     * file read
     */
    gif_filename = argv[1];

    if(stat(gif_filename, &filestat)) {
        perror("stat giffile");
        return EXIT_FAILURE;
    }
    gif_buff.data_len = filestat.st_size;
    gif_buff.data_offset = 0;
    fp = fopen(gif_filename, "rb");
    if (! fp) {
        fprintf(stderr, "Can't open file(%s)\n", gif_filename);
        return EXIT_FAILURE;
    }
    gif_buff.data = calloc(gif_buff.data_len, 1);
    fread(gif_buff.data, 1, gif_buff.data_len, fp);
    fclose(fp);
    /*
     * giflib
     */
    GifFile = DGifOpen(& gif_buff, gif_data_read_func);
    if (GifFile == NULL) {
        fprintf(stderr, "DGifOpen failed\n");
        return EXIT_FAILURE;
    }
    if (DGifSlurp(GifFile) == GIF_ERROR) {
        fprintf(stderr, "DGifSlurp failed\n");
        return EXIT_FAILURE;
    }
    /*
     * dump gif infomation
     */
    printf("Screen Infomation:\n");
    printf("    (Width, Height)=(%d, %d)  ",
           GifFile->SWidth, GifFile->SHeight);
    printf("ColorResolution=%d  ", GifFile->SColorResolution);
    printf("BackGroundColor=%d\n", GifFile->SBackGroundColor);
    print_colormap(GifFile->SColorMap, 0);
    ImageCount= GifFile->ImageCount;
    printf("ImageCount=%d\n", ImageCount);
    for (i=0 ; i < ImageCount ; i++) {
        int ii, BlockCount;
        printf("# Image No. %d\n", i);
        struct SavedImage image = GifFile->SavedImages[i];
        print_image_desc(image.ImageDesc);
        BlockCount = image.ExtensionBlockCount;
        printf("ExtensionBlockCount=%d\n", BlockCount);
        for (ii=0; ii< BlockCount; ii++) {
            int iii;
            ExtensionBlock block = image.ExtensionBlocks[ii];
            int Function = block.Function;
            int ByteCount = block.ByteCount;
            unsigned char *Bytes = (unsigned char*) block.Bytes;
            printf("    Function=0x%x  ByteCount=%d  ",
                   Function, ByteCount);
            printf("Bytes:");
            for ( iii=0 ; iii < ByteCount ; iii++ ) {
                printf(" %02x", Bytes[iii]);
            }
            printf("\n");
            switch(Function) {
                int gcntl_flag;
              case COMMENT_EXT_FUNC_CODE:  /* 0xfe */
                printf("    Comment\n");
                break;
              case GRAPHICS_EXT_FUNC_CODE: /* 0xf9 */
                printf("    Graphic Control\n");
                gcntl_flag = block.Bytes[0];
                printf("      DisposalMethod=%d ", (gcntl_flag >>2) & 0x7);
                switch ((gcntl_flag >>2) & 0x7) {
                  case 0:
                    printf("(No disposal specified)");
                    break;
                  case 1:
                    printf("(Do not dispose)");
                    break;
                  case 2:
                    printf("(Restore to background color)");
                    break;
                  case 3:
                    printf("(Restore to previous)");
                    break;
                  default:
                    printf("(To be defines)");
                    break;
                }

                
                printf("\n");
                printf("      UserInputFlag=%d  TransparentColorFlag=%d\n",
                       (gcntl_flag>>1) & 0x1 , gcntl_flag & 0x01);
                if (gcntl_flag & 0x02) { // user input flag
                    int delay_time = 0x100 * block.Bytes[1] + block.Bytes[2];
                    printf("      delay_time=%d\n", delay_time);
                }
                if (gcntl_flag & 0x01) { // transparent color flag
                    int transparent_index = Bytes[3];
                    ColorMapObject *ColorMap;
                    if (image.ImageDesc.ColorMap) {
                        ColorMap = image.ImageDesc.ColorMap;
                    } else if (GifFile->SColorMap) {
                        ColorMap = GifFile->SColorMap;
                    } else {
                        fprintf(stderr, "Not Found ColorMap\n");
                        DGitCloseFile(GifFile);
                        free(gif_buff.data);
                        return  EXIT_FAILURE;
                    }
                    if (ColorMap->ColorCount < transparent_index) {
                        fprintf(stderr, "ColorMap->ColorCount(%d) < transparent_index(%d)",
                                ColorMap->ColorCount, transparent_index);
                        DGitCloseFile(GifFile);
                        free(gif_buff.data);
                        return  EXIT_FAILURE;
                    }
                    GifColorType ct = ColorMap->Colors[transparent_index];
                    printf("\tTransparent=%d (= #%02x%02x%02x)\n",
                           transparent_index, ct.Red, ct.Green, ct.Blue);
                }
                break;
              case PLAINTEXT_EXT_FUNC_CODE: /* 0x01 */
                printf("    PlainText\n");
                break;
              case APPLICATION_EXT_FUNC_CODE: /* 0xff */
                printf("    Application Block\n");
                break;
            }
        }
        if (image.RasterBits) {
            GifImageDesc desc = image.ImageDesc;
            int x, y;
            ii = 0;
            printf("RasterBits:\n");
            for (y = 0 ; y < desc.Height ; y++) {
                printf("    [%03d]", y + desc.Top);
                for(x = 0 ; x < desc.Width ; x++) {
                    printf(" %02x", image.RasterBits[ii] & 0xff);
                    ii++;
                }
                printf("\n");
            }
        } else {
            printf("No RasterBits\n");
        }
    }
    DGifCloseFile(GifFile);
    free(gif_buff.data);
    return EXIT_SUCCESS;
}
