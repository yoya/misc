#include <stdio.h>
#include <stdlib.h>

int main(int argc, char **argv) {
    int c, i, n;
    if (argc < 2) {
        fprintf(stderr, "Usage: btimes <ntime>\n");
        return EXIT_FAILURE;
    }
    n = atoi(argv[1]);
    while ((c = getchar()) != EOF) {
        for ( i = 0; i < n ; i++ ) {
            putchar(c);
        }
    }
    return EXIT_SUCCESS;
}
