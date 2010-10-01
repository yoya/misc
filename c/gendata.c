#include <stdio.h>
#include "my_atoi.c"

#define BUF_MAXSIZ 0x10000

void usage(char *program_name) {
	fprintf(stderr, "Usage: %s size [ascii_code]\n", program_name);
}

/* dat_size ¤Ï 1 °Ê¾å */
void gendata(int total_size, unsigned char *buffer, int dat_size) {
	int i, j;
	for(i=0, j=0; i<total_size; i++, j++) {
		if (j >= dat_size)
			j = 0;
		putchar(buffer[j]);
	}
}

unsigned char buf[BUF_MAXSIZ];

int main(int argc, char **argv) {
	int i, dat_size, total_size;
	char *program_name = argv[0];
	switch(argc) {
	      case 0:
	      case 1:
		fprintf(stderr, "too few arguments\n");
		usage(program_name);
		return 1;
	      case 2:
		dat_size = 1;
		buf[0] = 0xff;
	      default:
		if (argc-1 > BUF_MAXSIZ) {
			fprintf(stderr, "size is too many size\n");
			usage(program_name);
			return 1;
		}
		for(i=0; i<argc-2; i++) {
			buf[i] = my_atoi(argv[2+i]);
		}
		dat_size = i;
		/* no break; */
		break;
	}
	total_size = my_atoi(argv[1]);
	if (total_size==0)
		total_size = dat_size;
	gendata(total_size, buf, dat_size);
	return 0;
}
