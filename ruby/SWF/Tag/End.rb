class SWF_Tag_End < SWF_Tag
  def initialize(bit_in, length)
    @Content = bit_in.get_string!(length)
  end
end
