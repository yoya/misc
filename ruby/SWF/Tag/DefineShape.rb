class SWF_Tag_DefineShape < SWF_Tag
  def initialize(bit_in, length)
    @Content = bit_in.get_string!(length)
  end
end
