/*
 * Copyright 2017/11/11- yoya@awm.jp All rights reserved.
 * % gcc jpeg_dump.c -ljpeg -W -Wall -Wextra
 */
#include <stdio.h>
#include <stdlib.h>
#include <jpeglib.h>

void usage() {
    fprintf(stderr, "Usage: jpeg_dump <jpegfile> <mode> # mode 1:rgb, 2:ycbcr 3:coef\n");
}

static int padding(int n, int p) {
    return n + (p - (n%p));
}

void dump_jpeg_component_info(jpeg_component_info *comp_info, int info_index);

char *string_J_COLOR_SPACE(J_COLOR_SPACE color_space) {
    switch (color_space) {
    case JCS_UNKNOWN:   return "JCS_UNKNOWN";
    case JCS_GRAYSCALE: return "JCS_GRAYSCALE";
    case JCS_RGB:       return "JCS_RGB";
    case JCS_YCbCr:     return "JCS_YCbCr";
    case JCS_CMYK:      return "JCS_CMYK";
    case JCS_YCCK:      return "JCS_YCCK";
    case JCS_BG_RGB:    return "JCS_BG_RGB";
    case JCS_BG_YCC:    return "JCS_BG_YCC";
    }
    return "(Wrong)";
}

char * string_J_DCT_METHOD(J_DCT_METHOD method) {
    switch(method) {
    case JDCT_ISLOW: return "JDCT_ISLOW";
    case JDCT_IFAST: return "JDCT_IFAST";
    case JDCT_FLOAT: return "JDCT_FLOAT";
    }
    return "(Wrong)";
} 


void dump_jpeg_dinfo(struct jpeg_decompress_struct *dinfo) {
    printf("dinfo:\n");
    printf("    jpeg_common_fieid:\n");
    printf("        is_decompressor:%d global_state:%d\n",
	   dinfo->is_decompressor, dinfo->global_state);
    //
    printf("    image_(width|height):(%d,%d) num_components:%d jpeg_color_space:%d(%s)\n",
	   dinfo->image_width, dinfo->image_height, dinfo->num_components,
	   dinfo->jpeg_color_space,
	   string_J_COLOR_SPACE(dinfo->jpeg_color_space));

    printf("--- must be set before jpeg_start_decompress(), jpeg_read_header() init\n");
    printf("    out_color_space:%d(%s) scale_(num|denom)=(%d,%d) output_gamma:%f\n",
	   dinfo->out_color_space,
	   string_J_COLOR_SPACE(dinfo->out_color_space),
	   dinfo->scale_num, dinfo->scale_denom, dinfo->output_gamma);
    printf("    buffered_image:%d raw_data_out:%d \n",
	   dinfo->buffered_image, dinfo->raw_data_out);
    printf("    dct_method:%d(%s) do_fancy_upsampling:%d do_block_smoothing:%d\n",
	   dinfo->dct_method, string_J_DCT_METHOD(dinfo->dct_method),
	   dinfo->do_fancy_upsampling, dinfo->do_block_smoothing);

    printf("    quantize_colors:%d\n", dinfo->quantize_colors);

    if (dinfo->quantize_colors) {
	printf("        dither_mode:%d two_pass_quantize:%d desired_number_of_colors:%d\n",
	       dinfo->dither_mode,
	       dinfo->two_pass_quantize, dinfo->desired_number_of_colors);
	printf("        enable_(1pass|externa|2pass)_quant:(%d,%d,%d)\n",
	       dinfo->enable_1pass_quant, dinfo->enable_external_quant,
	       dinfo->enable_2pass_quant);
    }

    printf("--- computed by jpeg_start_decompress\n");
    printf("    output_width:%d output_height:%d out_color_components:%d output_components:%d \n",
	   dinfo->output_width, dinfo->output_height,
	   dinfo->out_color_components, dinfo->output_components);
    printf("    rec_outbuf_height:%d \n",
	   dinfo->rec_outbuf_height);
    printf("    actual_number_of_colors:%d \n",
	   dinfo->actual_number_of_colors);
    // colormap;
    printf("--- Row index of next scanline\n");
    printf("    output_scanline:%d\n",
	   dinfo->output_scanline);
    printf("--- current input/output scan number & iMCU rows completed\n");
    printf("    input_scan_number:%d input_iMCU_row:%d\n",
	   dinfo->input_scan_number, dinfo->input_iMCU_row);
    printf("    outputt_scan_number:%d output_iMCU_row:%d\n",
	   dinfo->output_scan_number, dinfo->output_iMCU_row);
    // coef_bits
    // quant_tbl_ptrs
    // dc_huff_tbl_ptrs,  ac_huff_tbl_ptrs
    printf("--- given in SOF/SOS , reset by SOI\n");

    printf("    data_precision:%d \n",
	   dinfo->data_precision);
    if (dinfo->comp_info) {
	int i;
	for (i = 0 ; i < dinfo->num_components ; i++) {
	    dump_jpeg_component_info(dinfo->comp_info, i);
	}
    }
    printf("    is_baseline:%d progressive_mode:%d arith_code:%d\n",
	   dinfo->is_baseline, dinfo->progressive_mode,
	   dinfo->arith_code);
    // arith_dc_L, arith_dc_U, arith_ac_K
    printf("    restart_interval:%d \n", dinfo->restart_interval);
    printf("--- optional markers\n");
    /*
    printf("    :%d \n",
	   );

    */
}
    
void dump_jpeg_component_info(jpeg_component_info *comp_info, int info_index) {
    jpeg_component_info *info = &(comp_info[info_index]);
    printf("    comp_info[%d]:\n", info_index);
    printf("        component_id:%d component_index:%d\n",
	   info->component_id, info->component_index);
    printf("        {h|v}_samp_factor:{%d, %d}\n",
	   info->h_samp_factor, info->v_samp_factor);
    //
    printf("        downsampled_{width|height}:{%d, %d}\n",
	   info->downsampled_width, info->downsampled_height);
}

int main(int argc, char **argv) {
    char *input_filename = NULL;
    int dump_mode = 0;
    FILE *input_fp = NULL;
    struct jpeg_decompress_struct dinfo;
    struct jpeg_error_mgr jerr;
    int retcode;
    JSAMPARRAY jsampleArr[3];
    JSAMPARRAY strideArr[3];
    JSAMPARRAY heightArr[3];
    int num_scanlines;
    if (argc < 3) {
	usage();
    } else {
	input_filename = argv[1];
	input_fp = fopen(input_filename, "rb");
	if (! input_fp) {
	    fprintf(stderr, "Can't open file:%s\n", input_filename);
	    usage();
	    return EXIT_FAILURE;
	}
	dump_mode = strtol(argv[2], NULL, 10);
    }
    dinfo.err = jpeg_std_error(&jerr);
    if (dump_mode == 0) {
	    usage();
	    return EXIT_FAILURE;
    } else if (dump_mode == 1) {
	;
    } else { // 2, 3
	dinfo.raw_data_out = TRUE;
	dinfo.do_fancy_upsampling = FALSE;
    }
    jpeg_create_decompress(&dinfo);
    jpeg_stdio_src(&dinfo, input_fp);
    retcode = jpeg_read_header(&dinfo, TRUE);
    if (retcode != JPEG_HEADER_OK) {
	fprintf(stderr, "Illegal jpeg header retcode:%d\n", retcode);
	usage();
	return EXIT_FAILURE;	 
    }
    int num_components = dinfo.num_components;
    if (num_components != 3) {
	fprintf(stderr, "jpeg num_components:%d != 3\n", num_components);
	usage();
	return EXIT_FAILURE;
    }
    jpeg_start_decompress(&dinfo);
    dump_jpeg_dinfo(&dinfo);

    if (dump_mode == 1) { // 1:rgb
	;
    } else if (dump_mode == 2) { // 2:ycbcr
	// jpeg_read_raw_data(dinfo, )
	/*
	  while (cinfo.output_scanline < cinfo.output_height) {
	  num_scanlines = jpeg_read_scanlines(&cinfo, dest_mgr->buffer,
	  dest_mgr->buffer_height);
	  (*dest_mgr->put_pixel_rows) (&cinfo, dest_mgr, num_scanlines);
	  }
	*/
    } else { // 3:coef
	
    }
    return EXIT_SUCCESS;
}


