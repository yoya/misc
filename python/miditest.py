import sys
from io_midi import IO_MIDI

midi = IO_MIDI()
midi.parse(open(sys.argv[1]).read())
midi.dump()
