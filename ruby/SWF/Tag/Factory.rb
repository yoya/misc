require File.dirname(__FILE__)+"/../Tag.rb"

class SWF_Tag_Factory
  TagTypeMap = {
    0 => 'End',
    1 => 'ShowFrame',
    2 => 'DefineShape',
    6 => 'DefineBitsJPEG',
    8 => 'JPEGTables',
    9 => 'SetBackgroundColor',
    26 => 'PlaceObject2',
  }
#  TagTypeMap = { }
  def parse(bit_in)
    parse_TagCodeAndLength!(bit_in)
    typeName = TagTypeMap[@Type]
    if (TagTypeMap.member?(@Type) == true)
      @TypeName = TagTypeMap[@Type]
    else
      @TypeName = 'Unknown'
    end
    klass = parse_TagContent!(bit_in)
    klass.setMetaInfo(@Type, @Length, @TypeName)
    return klass
  end
  def parse_TagCodeAndLength!(bit_in)
    tagCodeAndLength = bit_in.get_bytesLE!(2)
    @Type = tagCodeAndLength >> 6;
    @Length = tagCodeAndLength & 0x3f
    if (@Length == 0x3f)
      @Length = bit_in.get_bytesLE!(4)
    end
  end
  def parse_TagContent!(bit_in)
    require File.dirname(__FILE__)+"/#{@TypeName}.rb"
    tagClassName = "SWF_Tag_#{@TypeName}"
    length = @Length
    klass = Object.instance_eval("#{tagClassName}.new(bit_in, length)")
#    klass = SWF_Tag_Unknown.new(bit_in, length)
    return klass
  end
end
