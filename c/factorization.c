#include <stdio.h>
#include <stdlib.h>

int main(int argc, char **argv) {
    int num, n, f;
    int num_max = 150;
    if (argc > 1) {
        num_max = atoi(argv[1]);
    }
    for (num = 2 ; num <= num_max ; num++) {
        printf ("%d:", num);
        n = num;
        for (f = 2 ; f*f <= n ; f++) {
            while ((n % f) == 0) {
                printf (" %d", f);
                n /= f;
            }
        }
        if (n > 1) {
            printf (" %d", n);
        }
        printf("\n");
    }
    return 0;
}
