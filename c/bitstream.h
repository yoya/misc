#ifndef __BITSTREAM_H__
#define __BITSTREAM_H__

/*
 * bit stream routine
 *                     (C) 2008/03/09- yoya@awm.jp
 */

typedef struct bitstream_ {
    /* raw data */
    unsigned char *data;
    unsigned long data_len;
    unsigned long data_alloc_len;
    /* seek position */
    unsigned long byte_offset;
    unsigned long bit_offset;
} bitstream_t;

#define BITSTREAM_DATA_LEN_MIN 0x100

extern bitstream_t *bitstream_open(void);
extern void bitstream_close(bitstream_t * bs);

/* load/save */
extern int bitstream_input(bitstream_t *bs, unsigned char *data,
			  unsigned long data_len);
extern unsigned char *bitstream_steal(bitstream_t *bs, unsigned long *length);
extern unsigned char *bitstream_output_sub(bitstream_t *bs, unsigned long offset, unsigned long length);

/* put/get */
extern int bitstream_putbyte(bitstream_t *bs, int byte);
extern int bitstream_getbyte(bitstream_t *bs);
extern int bitstream_putstring(bitstream_t *bs,
                               unsigned char *data, signed long data_len);
extern int bitstream_getstring(bitstream_t *bs,
                               unsigned char *data, signed long data_len);
extern unsigned char *bitstream_outputstring(bitstream_t *bs);

extern int bitstream_putbytesLE(bitstream_t *bs, unsigned long bytes, int byte_width);
extern int bitstream_putbytesBE(bitstream_t *bs, unsigned long bytes, int byte_width);
extern unsigned long bitstream_getbytesLE(bitstream_t *bs, int byte_width);
extern unsigned long bitstream_getbytesBE(bitstream_t *bs, int byte_width);
extern int bitstream_putbit(bitstream_t *bs, int bit);
extern int bitstream_getbit(bitstream_t *bs);
extern int bitstream_putbits(bitstream_t *bs, unsigned long bits, int bit_width);
extern unsigned long bitstream_getbits(bitstream_t *bs, int bit_width);
extern void bitstream_align(bitstream_t *bs);

/* seeking */
extern int bitstream_incrpos(bitstream_t *bs, signed long byte_incr,
                             unsigned long bit_incr);
extern int bitstream_setpos(bitstream_t *bs, unsigned long byte_offset,
			    unsigned long bit_offset);
extern unsigned long bitstream_getbytepos(bitstream_t *bs);

extern int bitstream_realloc(bitstream_t *bs);

/* direct access */
extern unsigned char *bitstream_buffer(bitstream_t *bs, unsigned long byte_offset);
extern unsigned long bitstream_length(bitstream_t *bs);

#endif /* __BITSTREAM_H__ */
