class SWF_RECT
  def initialize(bit_in)
    @Nbits = bit_in.get_bits!(5)
    @Xmin  = bit_in.get_bits_signed!(@Nbits)
    @Xmax  = bit_in.get_bits_signed!(@Nbits)
    @Ymin  = bit_in.get_bits_signed!(@Nbits)
    @Ymax  = bit_in.get_bits_signed!(@Nbits)
  end
  def dump
    printf("RECT: (bits=%d) (%d, %d) - (%d, %d)\n",
           @Nbits,
           @Xmin/20, @Ymin/20, @Xmax/20, @Ymax/20)
  end
end
