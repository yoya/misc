#! /usr/local/bin/ruby

class BitIO
  def initialize(data = '')
    @data = data
    @byte_offset = 0
    @bit_offset = 0
    @byte_alloc = data.length; 
    @bit_alloc = 0;
  end
  def rewind!()
    @byte_offset = 0
    @bit_offset = 0
  end
  def align!() 
    if (@bit_offset != 0)
      @byte_offset = @byte_offset + 1
      @bit_offset = 0
    end
  end
  def set_offset!(byte_offset, bit_offset)
    @byte_offset = byte_offset
    @bit_offset  = bit_offset
  end
  def incr_offset!(byte_width, bit_width)
    @byte_offset = @byte_offset + byte_width
    @bit_offset  = @bit_offset  + bit_width
    while (7 < @bit_offset)
      @byte_offset = @byte_offset + 1;
      @bit_offset  = @bit_offset  - 8;
    end
  end
  # peek bit
  def get_bit(byte_offset=@byte_offset, bit_offset=@bit_offset)
    if (@data.length <= byte_offset)
      return nil
    end
    if RUBY_VERSION >= '1.9.0' # >_<;
      byte = @data[byte_offset].ord
    else
      byte = @data[byte_offset]
    end
    (byte >> (7 - @bit_offset)) & 1
  end
  # get and incr bit
  def get_bit!
    bit = get_bit()
    if (bit.nil?) # nil check
    else
      incr_offset!(0, 1)
    end
    bit
  end
  # get bits value
  def get_bits!(bit_width)
    byte = 0
    (1..bit_width).each { |i|
      bit = get_bit!()
      if (bit.nil?)
        if (i == 1)
          return nil
        else
          raise "incomplete bits(#{@byte_offset}, #{@bit_offset})"
        end
      else
        byte = byte << 1
        byte = byte | bit
      end
    }
    byte
  end
  def get_bits_signed!(bit_width)
    bits = get_bits!(bit_width)
    if (bits.nil?)
      return nil
    end
    sig_b = 1 << (bit_width - 1)
    if ((sig_b & bits) != 0) # negaive
      bit_mask = (sig_b << 1) - 1
      bits = bits ^ bit_mask; # bit reverse
      bits = - (bits + 1)
    end
    bits
  end
  def get_bytesLE!(byte_num)
    align!()
    bytes = 0;
    (1..byte_num).each { |i|
      bits = get_bits!(8)
      if (bits.nil?)
        return nil
      end
      bits = bits << (8 * (i-1))
      bytes = bytes + bits
    }
    bytes
  end
  def get_string!(byte_num)
    bytes = "";
    (1..byte_num).each { |i|
      bits = get_bits!(8)
      if (bits == nil)
        return nil
      end
      bytes = bytes + bits.chr
    }
    bytes
  end
  def each(bit_width=1)
    byte_offset = @byte_offset # multithread unsafe maybe
    bit_offset  = @bit_offset
    @byte_offset = 0
    @bit_offset  = 0
    begin
      while (bits = get_bits!(bit_width))
        yield(bits)
      end
    rescue
      raise $! # throw exception
    ensure
      @byte_offset = byte_offset
      @bit_offset  = bit_offset
    end
  end
  # debug
  def debugPrint()
    printf("DEBUG: byte_offset=%d bit_offset=%d\n",
           @byte_offset, @bit_offset)
  end
end
