#include <stdlib.h>
#include <string.h>

int hex2var(char *hex) {
	int var;
	char c;
	var = 0;
	while ((c = *hex) != '\0') {
		if ('0' <= c && c <='9')
			var = var*16 + (c - '0');
		else if ('a' <= c && c <='f')
			var = var*16 + (c - 'a' + 10);
		else if ('A' <= c && c <='F')
			var = var*16 + (c - 'A' + 10);
		hex++;
	}
	return var;
}


#define HEX_PREFIX "0x"

int my_atoi(char *str) {
	int i;
	char *s;
	if ((s = strstr(str, HEX_PREFIX))) {
		i = hex2var(s+strlen(HEX_PREFIX));
	} else {
		i = atoi(str);
	}
	return i;
}
