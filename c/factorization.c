#include <stdio.h>
#include <math.h>

int main(void) {
    int num, n, f;
    for (num = 2 ; num < 150 ; num++) {
        printf ("%d:", num);
        n = num;
        for (f = 2 ; f <= sqrt(n) ; f++) {
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
