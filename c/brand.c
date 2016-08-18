#include <stdio.h>
#include <string.h>
#include <stdlib.h>

void usage(char *program_name) {
    fprintf(stderr, "usage: %s ファイル名 開始位置 [終了位置]\n",
	    program_name);
}


#define END_OF_FILE (-1)
#define HEX_PREFIX "0x"

int hex2dec(char *hex) {
    int i, dec, d;
    char c;
    dec = 0;
    while ((c = *hex) != '\0') {
	if ('0' <= c && c <='9')
	    dec = dec*16 + (c - '0');
	else if ('a' <= c && c <='f')
	    dec = dec*16 + (c - 'a' + 10);
	else if ('A' <= c && c <='F')
	    dec = dec*16 + (c - 'A' + 10);
	hex++;
    }
    return dec;
}

int my_atoi(char *str) {
    int i;
    char *s;
    if (s = strstr(str, HEX_PREFIX)) {
	i = hex2dec(s+strlen(HEX_PREFIX));
    } else {
	i = atoi(str);
    }
    return i;
}

int main(int argc, char **argv) {
    int i, c;
    int start, end;
    FILE *fp;

    // デフォルト値の設定
    start = 0;
    end = END_OF_FILE;
    fp = stdin;
    // 引数の処理
    if (argc > 4) {
	fprintf(stderr, "引数が多すぎます。(%d > 4)\n",argc);
	return 1;
    }
    if (argc > 1) {
	// １つ目の引数(ファイル名)の処理
	char *filename = argv[1];
	if (strcmp(filename, "-")) {
	    fp = fopen(filename, "rb");
	    if (!fp) {
		fprintf(stderr, "ファイル(%s)が開けません。\n",
			filename);
		usage(argv[0]);
		return 2;
	    }
	}
	if (argc > 2) {
		// ２つ目の引数(開始位置)の処理
	    start = my_atoi(argv[2]);
	    if (argc > 3) {
		// ３つ目の引数(終了位置)の処理
		end = my_atoi(argv[3]);
	    }
	}
    } else {
	/* argc が 1 の時 */
	usage(argv[0]);
	return 1;
    }
    // 開始位置まで、そのまま出力
    for(i=0; i<start; i++) {
	if ((c = fgetc(fp)) == EOF) {
	    fprintf(stderr,
		    "開始位置(0x%x)がファイルサイズ(0x%x)を超えています。\n",
		    start, i);
	    usage(argv[0]);
	    return 3;
	}
	fputc(c, stdout);
    }
    // 終了位置までランダムにして掃き出す
    for(; fgetc(fp) != EOF; i++) {
	c = random() % 0x100;
	fputc(c, stdout);
	if ((end != END_OF_FILE) && (i>=end)) {
	    break;
	}
    }

    // 終了位置まで、そのまま出力
    while ((c = fgetc(fp)) != EOF) {
	fputc(c, stdout);
    }

    if (fp != stdin) {
	fclose(fp);
    }

    return 0;
}
