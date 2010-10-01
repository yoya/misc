#! /usr/local/bin/ruby

require File.dirname(__FILE__)+'/BitIO.rb'
require File.dirname(__FILE__)+'/SWF/Header.rb'
require File.dirname(__FILE__)+'/SWF/Tag/Factory.rb'

class SWF
  def initialize(data = '')
    @bit_in = BitIO.new(data)
    @Header = SWF_Header.new(@bit_in);
    @TagList = Array.new()
    i = 0
    while true
      factory = SWF_Tag_Factory.new()
      tag = factory.parse(@bit_in)
      @TagList[i] = tag
      i = i + 1;
      if (tag.getType() == 0)
          break
      end
    end
  end
  def dump()
    @Header.dump()
    @TagList.each { | tag |
      tag.dump();
    }
  end
end


