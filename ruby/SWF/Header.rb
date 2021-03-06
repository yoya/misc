require File.dirname(__FILE__)+'/RECT.rb'

class SWF_Header
  def initialize(bit_in)
    @Signature   = bit_in.get_string!(3)
    @Version     = bit_in.get_bits!(8)
    @FileLength = bit_in.get_bytesLE!(4)
    @FrameSize  = SWF_RECT.new(bit_in)
    bit_in.align!();
    @FrameRate_Decimal  = bit_in.get_bytesLE!(1)
    @FrameRate_Integral = bit_in.get_bytesLE!(1)
    @FrameCount = bit_in.get_bytesLE!(2)
  end
  def dump()
    printf("Signature=%s Version=%d FileLength=%d\n",
           @Signature, @Version, @FileLength)
    @FrameSize.dump()
    printf("FrameRate=%d.%d FrameCount=%d\n",
           @FrameRate_Integral, @FrameRate_Decimal, @FrameCount)
  end
end
 
