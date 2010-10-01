/******* ******* ******* ******* ******* ******* *******
  ファイルダンプ、ツール
  バイナリとテキストを同時に表示してみた。
  オプションも真面目に解釈できるようにした。そんだけ
  made by yoya
 ******* ******* ******* ******* ******* ******* *******/
/*******
  必要モジュール
  ebcdic2ascii.c
 *******/
extern char ebcdic2ascii[];

#include <stdio.h>
#include <string.h>

// デバッグ用定義
#define  debug_printf
#define  debug_puts
//#define  debug_printf   printf
//#define  debug_puts     puts

// 定数定義
#define  OPT_NONE       0
#define  OPT_TEXT       1
#define  OPT_BINARY     2
#define  OPT_HELP       4
#define  OPT_ASCII      8
#define  OPT_EBCDIC  0x10
int opt_flag; // スイッチオプションの解析結果


#define  ST_NONE       0
#define  ST_START      1
#define  ST_END        2
#define  ST_LENGTH     4
#define  ST_INPUT      8
#define  ST_OUTPUT  0x10
#define  ST_CODE    0x20

#define ENDLESS -1

/* 設定 */

#define FDMP_UNIT 0x10
#define CHAR_FOR_BLANK ' '

int print_fdmp(FILE *fp, int c, int posi, int is_finish);

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

#define HEX_PREFIX "0x"

int my_atoi(char *str) {
	int i;
	char *s;
	if ((s = strstr(str, HEX_PREFIX)) != 0) {
		i = hex2dec(s+strlen(HEX_PREFIX));
	} else {
		i = atoi(str);
	}
	return i;
}

void usage(char *program_name) {
	fprintf(stderr, "Usage: %s [option...] [ファイル名]\n",
		program_name);
}

int main(int argc, char **argv) {
	//
	char *fname_out = NULL;
	char *fname_in = NULL;
	FILE *fp_in = stdin;
	FILE *fp_out = stdout;
	int start = 0;
	int end = ENDLESS;
	//
	char *exec_fname = *argv;
	// 一時変数
	int posi;
	int c;
	// 引数解析用変数
	char *arg_p;
	int opt_status;
	// オプション解析
	opt_flag =  OPT_TEXT | OPT_BINARY | OPT_ASCII;
	opt_status = ST_NONE;
	for(argv++; *argv ; argv++) {
		arg_p = *argv;
//		debug_printf("arg_p = %s\n", arg_p);
		if (*arg_p == '-') {
			// オプションスイッチ
			for(arg_p++; *arg_p; arg_p++) {
				switch(*arg_p) {
				      case 'h':
//					debug_puts("h を受理");
					opt_flag |= OPT_HELP;
					break;
				      case 'H':
//					debug_puts("H を受理");
					opt_flag &= ~OPT_HELP;
					break;
				      case 'B':
					opt_flag &= ~OPT_BINARY;
					break;
				      case 'T':
					opt_flag &= ~OPT_TEXT;
					break;
				      case 's':
					opt_status |= ST_START;
					break;
				      case 'e':
					opt_status &= ~ST_LENGTH;
					opt_status |= ST_END;
					break;
				      case 'l':
					opt_status &= ~ST_END;
					opt_status |= ST_LENGTH;
					break;
				      case 'i':
					opt_status |= ST_INPUT;
					break;
				      case 'o':
					opt_status |= ST_OUTPUT;
					break;
				      case 'c':
					opt_status |= ST_CODE;
					break;
				      default:
					debug_puts("不正なスイッチを検出");
				}
			}
		} else {
			//文字列指定
			debug_printf("文字列<%s>を受理\n", arg_p);
			if (opt_status == ST_NONE) { // ファイル名指定
				fname_in = arg_p;
			} else if (opt_status & ST_START) {
				start = my_atoi(arg_p);
				opt_status &= ~ST_START;
			} else if (opt_status & ST_END) {
				end = my_atoi(arg_p);
				opt_status &= ~ST_END;
			} else if (opt_status & ST_LENGTH) {
				end = start + my_atoi(arg_p) - 1;
				opt_status &= ~ST_LENGTH;
			} else if (opt_status & ST_INPUT) {
				fname_in = arg_p;
				opt_status &= ~ST_INPUT;
			} else if (opt_status & ST_OUTPUT) {
				fname_out = arg_p;
				opt_status &= ~ST_OUTPUT;
			} else if (opt_status & ST_CODE) {
				// コード種選択
				debug_printf("コード種選択:");
				debug_printf("flag=%x, *arg_p=%c\n",
					     opt_flag, *arg_p);
				switch(*arg_p) {
				      case 'a':
				      case 'A':
				      default:
					opt_flag &= ~OPT_EBCDIC;
					opt_flag |= OPT_ASCII;

					break;
				      case 'e':
				      case 'E':
					opt_flag &= ~OPT_ASCII;
					opt_flag |= OPT_EBCDIC;
					break;
				}
				opt_status &= ~ST_CODE;
			}
		}
	}
	// ヘルプメッセージの表示
	if (opt_flag & OPT_HELP) {
		usage(exec_fname);
		return 0;
	}
	// オプション解析結果の表示
	if (fname_in)
		fprintf(stderr, "入力ファイル名=%s\n", fname_in);
	else
		fprintf(stderr, "標準入力\n", fname_in);
	if (fname_out)
		fprintf(stderr, "出力ファイル名=%s\n", fname_out);
	else
		fprintf(stderr, "標準出力\n", fname_out);
	if (end != ENDLESS) {
		fprintf(stderr, "範囲 0x%x 〜 0x%x\n", start, end);
	} else {
		fprintf(stderr, "範囲 0x%x 〜 <ENDLESS>\n", start);
	}
	if (opt_flag & OPT_ASCII) {
		fprintf(stderr, "入力を ASCII コードとして解釈します\n");
	} else if (opt_flag & OPT_EBCDIC) {
		fprintf(stderr, "入力を EBCDIC コードとして解釈します\n");
	}
		
	if (fname_in) {
		fp_in = fopen(fname_in, "r");
		if (!fp_in) {
			fprintf(stderr, "指定された入力ファイル<%s>",
				fname_in);
			fprintf(stderr, "は見付かりません\n");
			return 1;
		}
	}
	if (fname_out) {
		fp_out = fopen(fname_out, "w");
		if (!fp_out) {
			fprintf(stderr, "指定された出力ファイル<%s>",
				fname_out);
			fprintf(stderr, "は見付かりません\n");
			return 1;
		}
	}
	for(posi = 0; ((c=fgetc(fp_in)) != EOF) ; posi++) {
		if ((end != ENDLESS) && ( posi>end))
			break;
		if (posi < start)
			continue;
//		putchar(c);
		print_fdmp(fp_out, c, posi, 0);
	}
	print_fdmp(fp_out, 0, posi, 1);
	return 0;
}

char fdmp_line[FDMP_UNIT];
int print_fdmp(FILE *fp, int c, int posi, int is_finish) {
	static is_first = 1;
	int i, remain;
	if (!is_finish) {
		if (is_first) { // 始め
			if (opt_flag & (OPT_BINARY | OPT_TEXT)) {
				// ヘッダ表示
				fprintf(fp, "  addr :");
				if (opt_flag & OPT_BINARY) {
					for(i=0; i<FDMP_UNIT; i++) {
						if (i%2)
							fprintf(fp, "%2X", i);
						else 
							fprintf(fp, " %2X", i);
						fdmp_line[i] = (char) ' ';
					}
				}
				if (opt_flag & OPT_TEXT) {
					fprintf(fp, "  ");
					for(i=0; i<FDMP_UNIT; i++)
						fprintf(fp, "%X", i);
				}
				// ここまでヘッダ(一行目)
				fprintf(fp, "\n");
				fprintf(fp, "0x%05x:", posi);
				if (opt_flag & OPT_BINARY) {
					// 始めは空白
					for(i=0; i<(posi%FDMP_UNIT); i++)
						if (i%2)
							fprintf(fp, "..");
						else
							fprintf(fp, " ..");
				}
			} else {
				fprintf(fp, "0x%05x:", posi);
			}
			is_first = 0;
		} else	if ((posi % FDMP_UNIT) == 0) {
			// アドレス値表示
			fprintf(fp, "0x%05x:", posi);
		}
		if (opt_flag & OPT_BINARY) {  // データ表示 (バイナリ)
			if (posi%2)
				fprintf(fp, "%02x", c);
			else 
				fprintf(fp, " %02x", c);  // データ表示
		}
		fdmp_line[posi%FDMP_UNIT] = (char) c;
	} else { // 後始末
		if (!is_first) { // 始めじゃない場合
			remain = FDMP_UNIT - (posi % FDMP_UNIT);
			if (remain != FDMP_UNIT) {// 切りが悪い
				for(i=(posi%FDMP_UNIT); i<FDMP_UNIT; i++) {
					if (i%2)
						fprintf(fp, "..");
					else 
						fprintf(fp, " ..");
				}
			}
		}
	}
	if (((posi%FDMP_UNIT) == (FDMP_UNIT - 1)) || (is_finish)) {
//	if (((posi%FDMP_UNIT) != (FDMP_UNIT - 1)) || (is_finish)) {
		// UNIT の最後、もしくは最後のデータ。
		if (opt_flag & OPT_TEXT) { // テキスト表示
			if (posi % FDMP_UNIT) // ???
				fprintf(fp, "  ", c);
			for(i=0; i <= (posi%FDMP_UNIT); i++) {
				char c = fdmp_line[i];
				if (opt_flag & OPT_EBCDIC) {
					unsigned char e = c;
					c = (char) ebcdic2ascii[e];
				}
				if (!isgraph(c))
					c = CHAR_FOR_BLANK;
				fprintf(fp, "%c", c); // テキスト表示
			}
		}
		if (posi % FDMP_UNIT) // ???
			fprintf(fp, "\n");
	}
	return 0;

}
