require File.dirname(__FILE__)+'/SWF.rb'

if ARGV.length != 1 
  print "Usage: SWFtest.rb <swf file>\n"
  exit 1;
end
data = IO.read(ARGV[0])
data.force_encoding("BINARY") if RUBY_VERSION >= '1.9'
swf = SWF.new(data);
swf.dump()
