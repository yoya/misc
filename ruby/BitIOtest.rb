#! /usr/local/bin/ruby

require 'BitIO.rb'

# test routine

data = "foobaabaz"
$io = BitIO.new(data)

v1= $io.get_bits!(5)
v2 = $io.get_bits_signed!(3)
printf("%d %d\n", v1, v2)
$io.set_offset!(0,0);

$io.each(3) { |b|
  printf("%d", b)
}
puts("\n");

data.each_byte { |b|
  (1..8).each {
    bit = $io.get_bit!()
    printf("%d", bit);
  }
  printf(" %c\n", b);
}
