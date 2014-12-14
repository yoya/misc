#include <stdio.h>
#include <stdlib.h>
#include <string.h>

/*
  RIFF WAVE Linear PCM audio cutter
  (c) 2014/12/14- yoya@awm.jp
 */

#define RIFF_WAVE_LPCM_HEADER_SIZE 0x24

// http://www.kk.iij4u.or.jp/~kondo/wave/
typedef struct {
    unsigned long  riffSize;
    unsigned long  fmtSize;
    unsigned short formatId;
    unsigned short nChannel;
    unsigned long  sampleRate;
    unsigned long  bytePerSecs;
    unsigned short blockSize;
    unsigned short sampleBits;
} riff_wave_header, riff_wave_header_t;

// must be unsigned char
#define READ_LE2BYTE(b, o) (b[o]+0x100*(b[o+1]))
#define READ_LE4BYTE(b, o) (b[o]+0x100*(b[o+1]+0x100*(b[o+2]+(0x100*b[o+3]))))

void WRITE_LE4BYTE(unsigned char *b, int o, unsigned long v) {
    b[o++] = v & 0xff; v >>= 8;
    b[o++] = v & 0xff; v >>= 8;
    b[o++] = v & 0xff; v >>= 8;
    b[o] = v & 0xff;
}

void printRIFFWaveHeader(riff_wave_header_t header) {
    printf("riffSize=%lu\n", header.riffSize);
    printf("fmtSize=%lu\n", header.fmtSize);
    printf("formatId=%hu\n", header.formatId);
    printf("nChannel=%hu\n", header.nChannel);
    printf("bytePerSecs=%lu\n", header.bytePerSecs);
    printf("blockSize=%hu\n", header.blockSize);
    printf("sampleBits=%hu\n", header.sampleBits);

}

int readRIFFWaveHeader(riff_wave_header_t *header, unsigned char *rawdata) {
    if (strncmp((char *) rawdata, "RIFF", 4)) {
        fprintf(stderr, "not RIFF header\n");
        return 1; // FAILURE
    }
    header->riffSize = READ_LE4BYTE(rawdata, 4);
    if (strncmp((char *) rawdata + 8, "WAVEfmt ", 7)) {
        fprintf(stderr, "not WAVEfmt header\n");
        return 1; // FAILURE
    }
    header->fmtSize = READ_LE4BYTE(rawdata, 0x10);
    header->formatId = READ_LE2BYTE(rawdata, 0x14);
    header->nChannel = READ_LE2BYTE(rawdata, 0x16);
    header->sampleRate = READ_LE2BYTE(rawdata, 0x18);
    header->bytePerSecs = READ_LE4BYTE(rawdata, 0x1c);
    header->blockSize = READ_LE2BYTE(rawdata, 0x20);
    header->sampleBits = READ_LE2BYTE(rawdata, 0x22);
//    printRIFFWaveHeader(*header);
    return 0; // SUCCESS
}

int main(int argc, char **argv) {
    FILE *fp = NULL;
    char *filename = NULL;
    unsigned char headerdata[RIFF_WAVE_LPCM_HEADER_SIZE];
    unsigned char rawdata[BUFSIZ];
    int ret = 0;
    riff_wave_header_t riff_wave_header;
    int data_chunk_found = 0;
    unsigned long data_payload_size = 0;
    double  start_time = 0;
    double  end_time = -1;
    unsigned long start_offset = 0;
    unsigned long end_offset = 0;
    unsigned long need_payload_size = 0;
    // argument check
    if ((argc < 3) || (4 < argc)) {
        fprintf(stderr, "Usage: wavlpcmcut <file> <start> [<end>]\n");
        fprintf(stderr, "ex) wavlpcmcut yoya.wav 100 # 100secs to last\n");
        fprintf(stderr, "ex) wavlpcmcut yoya.wav 10 100 # 10 to 100secs\n");
        return EXIT_FAILURE;
    }
    //
    filename = argv[1];
    start_time = atof(argv[2]);
    if (argc >= 4) {
        end_time = atof(argv[3]);
    }
//    printf("start_time=%f, end_time=%f\n", start_time, end_time);
    fp = fopen(filename, "rb");
    if (! fp) {
        fprintf(stderr, "Can't open file(%s)\n", filename);
        return EXIT_FAILURE;
    }
    // riff wave header reading
    ret = fread(headerdata, RIFF_WAVE_LPCM_HEADER_SIZE, 1, fp);
    if (ret != 1) {
        fprintf(stderr, "ERROR: too small size for riff wave format\n");
        fclose(fp);
        return EXIT_FAILURE;
    }
    ret = readRIFFWaveHeader(&riff_wave_header, headerdata);
    if (ret) {
        fprintf(stderr, "ERROR: readRIFFWaveHeader failed (ret=%d)\n", ret);
        return EXIT_FAILURE;
    }
    do {
        ret = fread(rawdata, 8, 1, fp);
        if (ret != 1) {
            fprintf(stderr, "ERROR: not fount data chunk\n");
            return EXIT_FAILURE;
        }
        if (strncmp((char *)rawdata, "data", 4) == 0) {
            data_payload_size = READ_LE4BYTE(rawdata, 4);
//            printf("data_payload_size=%lu\n", data_payload_size);
            data_chunk_found = 1;
        } else { // maybe FLLR chunk (extend information)
            int payloadSize = READ_LE4BYTE(rawdata, 4);
            fseek(fp, payloadSize, SEEK_CUR); // skip chunk payload
        }
    } while (! data_chunk_found);
    start_offset = start_time * riff_wave_header.sampleRate * riff_wave_header.blockSize;
    if (end_time >= 0)  {
        end_offset = end_time * riff_wave_header.sampleRate * riff_wave_header.blockSize;
    } else {
        end_offset = data_payload_size - 1; // last byte
    }
    need_payload_size = end_offset - start_offset + 1;
    if (riff_wave_header.blockSize == 4) {
        need_payload_size &= 0xfffffffc;
    } else if (riff_wave_header.blockSize == 2) {
        need_payload_size &= 0xfffffffe;
    } else {
        // nothing to do;
    }
    
//    printf("need_payload_size=%lu\n", need_payload_size);
    WRITE_LE4BYTE(headerdata, 4, 0x24 + need_payload_size);
    fwrite(headerdata, RIFF_WAVE_LPCM_HEADER_SIZE, 1, stdout);
    strncpy((char *)rawdata, "data", 4);
    WRITE_LE4BYTE(rawdata, 4, need_payload_size);
    fwrite(rawdata, 8, 1, stdout);
    if (start_offset > 0) {
        fseek(fp, start_offset, SEEK_CUR);
    }
    while (need_payload_size > 0) {
        int readsize = (BUFSIZ<need_payload_size)?BUFSIZ:need_payload_size;
//        printf("readsize=%d\n", readsize);
        ret = fread(rawdata, readsize, 1, fp);
        if (ret == 0) {
            fprintf(stderr, "ERROR: can't read data\n");
            return EXIT_FAILURE;
        }
        fwrite(rawdata, readsize, 1, stdout);
        need_payload_size -= readsize;
    }
    //
    fclose(fp);
    return EXIT_SUCCESS;
}
