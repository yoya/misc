#include <lcms2.h>
// gcc lcms2profile.c -llcms2

int main(void) {
    cmsHPROFILE hProfile = cmsCreate_sRGBProfile();
    cmsSaveProfileToFile(hProfile, "sRGB_LittleCMS2.icc");
}
