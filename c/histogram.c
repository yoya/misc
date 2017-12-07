#include <stdio.h>
#include <stdlib.h>

int main(int argc, char **argv) {
    long histogram[256];
    int c;
    for (int i = 0 ; i < 256 ; i++) {
	histogram[i] = 0;
    }
    while ((c = fgetc(stdin)) != EOF) {
	histogram[c] += 1;
    }
    for (int i = 0 ; i < 256 ; i++) {
	if (histogram[i] > 0) {
	    printf("%02X %ld\n", i, histogram[i] );
	}
    }
    return 0;
}
