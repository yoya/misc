/*
 * bit stream routine
 *                     (C) 2008/03/09- yoya@awm.jp
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "bitstream.h"

static void bitstream_clear(bitstream_t *bs);

bitstream_t *
bitstream_open(void) {
    bitstream_t *bs = (bitstream_t *) calloc(sizeof(*bs), 1);
    bs->data = NULL;
    bs->data_len = 0;
    bs->data_alloc_len = 0;
    bs->byte_offset = 0;
    bs->bit_offset = 0;
    return bs;
}

void
bitstream_close(bitstream_t * bs) {
    if (bs->data) {
        free(bs->data);
    }
    free(bs);
    return ;
}

static void
bitstream_clear(bitstream_t *bs) {
    if (bs->data) {
        free(bs->data);
        bs->data = NULL;
    }
    bs->data_len = 0;
    bs->data_alloc_len = 0;
    bs->byte_offset = 0;
    bs->bit_offset = 0;
    return ;
}

int
bitstream_realloc(bitstream_t *bs) {
    unsigned char *data;
    bs->data_alloc_len *= 2;
    if (bs->data_alloc_len < BITSTREAM_DATA_LEN_MIN) {
        bs->data_alloc_len = BITSTREAM_DATA_LEN_MIN;
    }
    data = (unsigned char *) realloc(bs->data, bs->data_alloc_len);
    if (! data) {
        fprintf(stderr, "bitstream_realloc: Can't realloc memory (%p, %ld)\n",
                data, bs->data_alloc_len);
        return 1;
    }
    bs->data = data;
    return 0;
}

int
bitstream_input(bitstream_t *bs, unsigned char *data,
                   unsigned long data_len) {
    bitstream_clear(bs);
    bs->data_alloc_len = data_len;
    bs->data = malloc(bs->data_alloc_len);
    memcpy(bs->data, data, data_len);
    bs->data_len = data_len;
    return 0;
}

unsigned char *
bitstream_steal(bitstream_t *bs, unsigned long *length) {
    unsigned char *data, *tmp;
    *length = 0;
    if (! bs) {
        return NULL;
    }
    data = bs->data;
    *length = bs->data_len;
    if ((tmp = realloc(data, *length)) == NULL) {
        fprintf(stderr, "Can't realloc\n");
    }
    bs->data = NULL;
    bs->data_len = 0;
    bs->data_alloc_len = 0;
    return data;
}

unsigned char *
bitstream_output_sub(bitstream_t *bs, unsigned long offset, unsigned long length) {
    unsigned char *data;
    if (! bs) {
        fprintf(stderr, "bs == NULL");
        return NULL;
    }
    if (bs->data_len < offset + length ) {
        fprintf(stderr, "bitstream_output_sub: bs->data_len(%lu) < offset(%lu)+length(%lu)\n",
                bs->data_len, offset, length);
        return NULL;
    }
    data = malloc(length);
    if (data == NULL) {
        fprintf(stderr, "bitstream_output_sub: Can't malloc\n");
        return NULL;
    }
    memcpy(data, bs->data + offset, length);
    return data;
}

/*
 * byte stream
 */

int
bitstream_putbyte(bitstream_t *bs, int byte) {
    bitstream_align(bs);
    if (bs->data_len < bs->byte_offset ) {
        return 1;
    }
    if (bs->data_len == bs->byte_offset ) {
        if (bs->data_alloc_len <= bs->byte_offset ) {
            bitstream_realloc(bs);
        }
        bs->data_len ++;
    }
    byte &= 0xff;
    bs->data[bs->byte_offset] = (unsigned char) byte ;
    bs->byte_offset++;
    return 0;
}

int
bitstream_getbyte(bitstream_t *bs) {
    int byte;
    bitstream_align(bs);
    if (bs->data_len <= bs->byte_offset) {
        return -1; /* End of Stream */
    }
    byte = bs->data[bs->byte_offset] & 0xff;
    bs->byte_offset++;
    return byte;
}

int
bitstream_putstring(bitstream_t *bs,
                               unsigned char *data, signed long data_len) {
    bitstream_align(bs);
    if (bs->data_len < bs->byte_offset ) {
        return 1;
    }
    while(bs->data_alloc_len < bs->byte_offset + data_len) {
        bitstream_realloc(bs);
    }
    bs->data_len = bs->byte_offset + data_len;
    memcpy(bs->data + bs->byte_offset, data, data_len);
    bs->byte_offset += data_len;
    return 0;
}

int
bitstream_getstring(bitstream_t *bs,
                               unsigned char *data, signed long data_len) {
    bitstream_align(bs);
    if (bs->data_len < bs->byte_offset + data_len) {
        return -1; /* End of Stream */
    }
    memcpy(data, bs->data + bs->byte_offset, data_len);
    bs->byte_offset += data_len;
    return 0;
}

unsigned char *
bitstream_outputstring(bitstream_t *bs) {
    unsigned char *data;
    unsigned long data_len;
    bitstream_align(bs);
    data_len = strlen((char *) bs->data + bs->byte_offset);
    data_len += 1; // + '\0'
    if (bs->data_len < bs->byte_offset + data_len) {
        return NULL; /* End of Stream */
    }
    data = malloc(data_len);
    if (data == NULL) {
        fprintf(stderr, "bitstream_outputstring: can't malloc\n");
        return NULL;
    }
    memcpy(data, bs->data + bs->byte_offset, data_len);
    bs->byte_offset += data_len;
    return data;
}

int
bitstream_putbytesLE(bitstream_t *bs, unsigned long bytes, int byte_width) {
    int i;
    unsigned long byte;
    for (i=0; i < byte_width; i++) {
        byte = bytes & 0xff;
        bitstream_putbyte(bs, byte);
        bytes >>= 8;
    }
    return 0;
}

int
bitstream_putbytesBE(bitstream_t *bs, unsigned long bytes, int byte_width) {
    int i;
    unsigned long byte;
    for (i=0; i < byte_width; i++) {
        byte = bytes >> ( 8 * (byte_width - 1 - i));
        bitstream_putbyte(bs, byte & 0xff);
    }
    return 0;
}

unsigned long
bitstream_getbytesLE(bitstream_t *bs, int byte_width) {
    int i;
    unsigned long byte, bytes = 0;
    for (i=0; i < byte_width; i++) {
        byte = bitstream_getbyte(bs);
        byte <<= 8 * i;
        bytes |= byte;
    }
    return bytes;
}

unsigned long
bitstream_getbytesBE(bitstream_t *bs, int byte_width) {
    int i;
    unsigned long byte, bytes = 0;
    for (i=0; i < byte_width; i++) {
        bytes <<= 8;
        byte = bitstream_getbyte(bs);
        bytes |= byte;
    }
    return bytes;
}

/*
 * bit stream
 */

int
bitstream_putbit(bitstream_t *bs, int bit) {
    int byte;
    if (bs->data_len <= bs->byte_offset) {
//        fprintf(stderr, "bs->data_len(%ld) <= bs->byte_offset(%ld)\n",
//                bs->data_len, bs->byte_offset);
        if (bs->data_alloc_len <= bs->byte_offset) {
            fprintf(stderr, "bitstream_putbit: alloc_len=%lu\n", bs->data_alloc_len);
            bitstream_realloc(bs);
        }
        bs->data[bs->byte_offset] = 0;
        bs->data_len ++;
// return 1;
    }
    bit &= 1;
    byte = bs->data[bs->byte_offset];
    byte |= bit << (7 - bs->bit_offset);
    bs->data[bs->byte_offset] = byte;
    bitstream_incrpos(bs, 0, 1);
    return 0;
}
int
bitstream_getbit(bitstream_t *bs) {
    int bit, byte;
    if (bs->data_len <= bs->byte_offset) {
        fprintf(stderr, "bitstream_getbit: bs->data_len(%ld) <= bs->byte_offset(%ld)\n",
                bs->data_len, bs->byte_offset);
        return -1; /* End of Stream */
    }
    byte = bs->data[bs->byte_offset];
    bit = byte >> (7 - bs->bit_offset);
    bitstream_incrpos(bs, 0, 1);
    return bit & 1;
}

int
bitstream_putbits(bitstream_t *bs, unsigned long bits, int bit_width) {
    int i, bit;
    for (i=0; i < bit_width; i++) {
        bit = bits >> (bit_width - 1 - i);
        bit &= 1;
        bitstream_putbit(bs, bit);
    }
    return 0;
}
unsigned long
bitstream_getbits(bitstream_t *bs, int bit_width) {
    int i;
    unsigned long bit, bits = 0;
    for (i=0; i < bit_width; i++) {
        bit = bitstream_getbit(bs);
        bits |= bit << (bit_width - 1 - i);
    }
    return bits;
}

void
bitstream_align(bitstream_t *bs) {
    if (bs->bit_offset > 0) {
        bs->byte_offset++;
        bs->bit_offset = 0;
    }
}

/*
 * stream seek
 */

int
bitstream_incrpos(bitstream_t *bs, signed long byte_incr,
                             unsigned long bit_incr) {
    bs->byte_offset += byte_incr;
    bs->bit_offset += bit_incr;
    while (bs->bit_offset >= 8) {
        bs->bit_offset -= 8;
        bs->byte_offset ++;
    }
    return 0;
}

int
bitstream_setpos(bitstream_t *bs, unsigned long byte_offset,
		     unsigned long bit_offset) {
    if (bs->data_len <= byte_offset ) {
        fprintf(stderr, "bitstream_setpos: bs->data_len(%ld) <= byte_offset(%ld)\n",
                bs->data_len, byte_offset);
    }
    bs->byte_offset = byte_offset;
    bs->bit_offset = bit_offset;
    return 0;
}

unsigned long
bitstream_getbytepos(bitstream_t *bs) {
    return bs->byte_offset;
}

/*
 * stream info
 */

unsigned char *
bitstream_buffer(bitstream_t *bs, unsigned long byte_offset) {
    return bs->data + byte_offset;
}

unsigned long
bitstream_length(bitstream_t *bs) {
    return bs->data_len;
}
