package BitIO;

sub new {
    my $class = shift;
    my $self = {data => 0, byte_offset => 0, bit_offset => 0};
    return bless $self, $class; 
}
sub input {
    my $self = shift;
    ($self->{data}) = @_;
}

sub getData {
    my ($self, $length) = @_;
    $self->byteAlign();
    $data = substr($self->{data}, $self->{byte_offset}, $length);
    $self->{byte_offset} += $length;
    return $data;
}

sub getByte {
    my $self = shift;
    $self->byteAlign();
    $c = substr($self->{data}, $self->{byte_offset}, 1);
    $self->{byte_offset} ++;
    return ord $c;
}

sub getBytesLE {
    my ($self, $size) = @_;
    $data = $self->getData($size);
    $value = 0;
    for ($i = $size - 1 ; $i >= 0 ; $i--) {
        $value *= 0x100;
        $value += unpack('C', substr($data, $i, 1));
    }
    return $value;
}

sub byteAlign {
    my $self = shift;
    while ($self->{bit_offset} > 0 ) {
        $self->{byte_offset} ++;
        $self->{bit_offset} = 0;
    }
}

sub getBit {
    my $self = shift;
    $c = substr($self->{data}, $self->{byte_offset}, 1);
    $value = ord $c;
    $b = ($value >> (7 - $self->{bit_offset})) & 1;
    $self->{bit_offset}++;
    # 繰り上げ
    while ($self->{bit_offset} >= 8) {
        $self->{byte_offset} ++;
        $self->{bit_offset} -= 8;
    }
    return $b;
}

sub getBits {
    my ($self, $size) = @_;
    my $value = 0;
    for ($i = 0 ; $i < $size ; $i++) {
        $value <<= 1;
        $value |= $self->getBit();
    }
    return $value;
}

sub getRectangle {
    my $self = shift;
    my $rect = {};
    $nbits = $self->getBits(5);
    $rect->{Xmin} = $self->getBits($nbits);
    $rect->{Xmax} = $self->getBits($nbits);
    $rect->{Ymin} = $self->getBits($nbits);
    $rect->{Ymax} = $self->getBits($nbits);
    return $rect;
}

1;
