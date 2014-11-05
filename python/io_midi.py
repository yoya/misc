#   http:#www.omnibase.net/smf/
#   http:#www.sonicspot.com/guide/midifiles.html

from __future__ import print_function
import sys
import math
from pprint import pprint
from io_bit import IO_Bit

class IO_MIDI :
    def __init__(self):
        self.tracks = []
        self.xfkaraoke = None
    def parse(self, mididata):
        self._mididata = mididata
        reader = IO_Bit()
        reader.input(mididata)
        while reader.hasNextData(4):
            chunk = self._parseChunk(reader)
            if chunk['type'] == 'MThd':
                self.header = chunk
            elif chunk['type'] == 'MTrk':
                self.tracks.append(chunk)
            elif chunk['type'] == 'XFIH':
                self.xfinfo = chunk
            elif chunk['type'] == 'XFKM':
                self.xfkaraoke = chunk
            else:
                sys.stderr.write("Can't parse chunk.")
                break

    def _parseChunk(self, reader):
        offset, dummy = reader.getOffset()
        type = reader.getData(4)
        length = reader.getUI32BE()
        nextOffset = offset + 8 + length
        chunk = {'type':type, 'length':length, '_offset':offset}

        if type == 'MThd':
            chunk['header'] = self._parseChunkHeader(reader)
        elif type == 'MTrk':
            chunk['track'] = self._parseChunkTrack(reader, nextOffset)
        elif type == 'XFIH':
            chunk['xfinfo'] = self._parseChunkXFInfo(reader, nextOffset)
        elif type == 'XFKM':
            chunk['xfkaraoke'] = self._parseChunkXFKaraoke(reader, nextOffset)
        else:
            sys.stderr.write("warning: Unknown chunk (type=type)\n")
            return {}
        doneOffset, dummy = reader.getOffset()
        if doneOffset != nextOffset: 
            print("done:{0} next:{1}".format(doneOffset, nextOffset))
        reader.setOffset(nextOffset, 0)

        return chunk
    

    def _parseChunkHeader(self, reader):
        header = {}
        header['Format'] = reader.getUI16BE()
        header['NumberOfTracks'] = reader.getUI16BE()
        division = reader.getUI16BE()
        header['DivisionFlag'] = division >> 15
        header['Division'] = division & 0x7fff
        return header
    

    def _parseChunkTrack(self, reader, nextOffset):
        track = []
        prev_status = None
	time = 0
        while True:
            offset, dummy = reader.getOffset()
            if offset >= nextOffset: 
                break # done
            
            chunk = {'_offset': offset}
            # delta time
	    deltaTime = self.getVaribleLengthValue(reader)
            chunk['DeltaTime'] = deltaTime

	    time += deltaTime
            # event
            status = reader.getUI8() # status byte
            while status < 0x80:  # running status
                status = prev_status
                reader.incrementOffset(-1, 0) # 1 byte back
            
            eventType = status >> 4
            midiChannel = status & 0x0f
            chunk['EventType'] = eventType
            chunk['MIDIChannel'] = midiChannel
            if eventType == 0x8 or eventType == 0x9: # Note Off or Note On
                chunk['NoteNumber'] = reader.getUI8()
                chunk['Velocity'] = reader.getUI8()
            elif eventType == 0xA: # Note Aftertouch Event
                chunk['NoteNumber'] = reader.getUI8()
                chunk['Amount'] = reader.getUI8()
            elif eventType == 0xB: # Controller
                controllerType = reader.getUI8()
                chunk['ControllerType'] = controllerType
                if controllerType == 0 or controllerType == 1 or controllerType == 98 or controllerType == 100:
                    # Bank Select #32 more commonly used
                    # Modulation Wheel
                    # NRPN LSB(Fine)
                    # RPN LSB(Fine)
                    chunk['LSB'] = reader.getUI8()
                elif controllerType == 99 or controllerType == 101:
                    # NRPN MSB(Coarse)
                    # RPN MSB(Coarse)
                    chunk['MSB'] = reader.getUI8()
                else:
                    chunk['Value'] = reader.getUI8()
                    
            elif eventType == 0xC: # Program Change
                chunk['ProgramNumber'] =  reader.getUI8()
            elif eventType == 0xD: # Note Aftertouch Event
                chunk['Amount'] = reader.getUI8()
            elif eventType == 0xE: # Pitch Bend Event
                value =  reader.getUI8()
                value = ((reader.getUI8() & 0x7f) << 7) + (value & 0x7f)
                chunk['Value'] = value - 0x2000
            elif eventType == 0xF: # Meta Event of System Ex
                del chunk['MIDIChannel']
                if midiChannel == 0xF:  # not midiChannel
                    metaEventType = reader.getUI8()
                    chunk['MetaEventType'] = metaEventType
                    length = self.getVaribleLengthValue(reader)
                    chunk['MetaEventData'] = reader.getData(length)
                elif midiChannel == 0x0: # System Ex
                    length = self.getVaribleLengthValue(reader)
                    chunk['SystemEx'] = reader.getData(length)
                elif midiChannel == 0x7: # System Ex continue
                    length = self.getVaribleLengthValue(reader)
                    chunk['SystemExCont'] = reader.getData(length)
                else:
                    print("unknown status=0x%02X" % status)
            else:
                printf("unknown EventType=0x%02X" % eventType)
                var_dump(chunks)
                exit (0)
            offset2, dummy = reader.getOffset()
            chunk['_length'] = offset2 - offset
	    chunk['_time'] = time
            track.append(chunk)
            prev_status = status
        return track
    

    def _parseChunkXFInfo(self, reader, nextOffset):
        xfinfo = []
        while True:
            offset, dummy = reader.getOffset()
            if offset >= nextOffset: 
                break # done
            
            chunk = {'_offset':offset}
            # delta time
            chunk['DeltaTime'] = self.getVaribleLengthValue(reader)
            status = reader.getUI8() # status byte
            if status != 0xFF: 
                o, dummy = reader.getOffset()
                sys.stderr.write("Unknown format(0x{:02X}) offset(0x{:x}) in XFInfoHeader".format(status, o - 1))
                break # failed
            
            chunk['MetaEventType'] = reader.getUI8()
            length = self.getVaribleLengthValue(reader)
            chunk['MetaEventData'] = reader.getData(length)
            offset2, dummy = reader.getOffset()
            chunk['_length'] = offset2 - offset
            xfinfo.append(chunk)
        return xfinfo
    

    def _parseChunkXFKaraoke(self, reader, nextOffset):
        xfkaraoke = []
	time = 0
        while True:
            offset, dummy = reader.getOffset()
            if offset >= nextOffset: 
                break # done
            chunk = {'_offset':offset}
            # delta time
	    deltaTime = self.getVaribleLengthValue(reader)
            chunk['DeltaTime'] = deltaTime
	    time += deltaTime
	    # event
            status = reader.getUI8() # status byte
            if status != 0xFF: 
                o, dummy = reader.getOffset()
                sys.stderr.write("Unknown status(0x{:02X}) offset(0x{:x}) in xfkaraokeHeader\n".format(status, o - 1))
                break # failed
            
            type = reader.getUI8()
            chunk['MetaEventType'] = type
            if type == 0x05:    #karaoke
                length = self.getVaribleLengthValue(reader)
                chunk['MetaEventData'] = reader.getData(length)
            elif type == 0x07: # ????
                length = self.getVaribleLengthValue(reader)
                chunk['MetaEventData'] = reader.getData(length)
            elif type == 0x2F: # End of Track
                length = self.getVaribleLengthValue(reader)
            else:
                o, dummy = reader.getOffset()
                sys.stderr.write("Unknown type(0x{:02X}) offset(0x{:x}) in xfkaraokeHeader\n".format(type, o - 1))
            
            offset2, dummy = reader.getOffset()
            chunk['_length'] = offset2 - offset
	    chunk['_time'] = time
            xfkaraoke.append(chunk)
        return xfkaraoke
    
    def getVaribleLengthValue(self, reader):
        ret_value = 0
        while True:
            value = reader.getUI8()
            if value & 0x80: 
                ret_value = (ret_value << 7) + (value & 0x7f)
            else:
                ret_value = (ret_value << 7) + value
                break;
        return ret_value
    

    event_name = {
        0x8:'Note Off',
        0x9:'Note On',
        0xA:'Note Aftertouch Eventn',
        0xB:'Controller',
        0xC:'Program Change',
        0xD:'Note Aftertouch Event',
        0xE:'Pitch Bend Event',
        0xF:'System Exclusive',
        }
    meta_event_name = {
        0x00:'Sequence Number',
        0x01:'Text',
        0x02:'Copyright Notice',
        0x03:'Sequence/Track Name',
        0x04:'Instrument Name',
        0x05:'Lylic',
        0x06:'Marker',
        0x07:'Cue Point',
        0x20:'MIDI Channel Prefix',
        0x2F:'End of Track',
        0x51:'Set Tempo',
        0x54:'SMPTE Offset',
        0x58:'Time Signature',
        0x59:'Key Signature',
        0x7F:'Sequencer Specific',
        }
    # http:#www.bass.radio42.com/help/html/f7f8b18f-a4a4-91bf-83c1-651b8dfc8f96.htm
    controller_type_name = {
        0:'BankSelect',
        1:'Modulation',
        2:'BreathControl',
        3:'User3',
        4:'FootControl',
        5:'PortamentoTime',
        6:'DataEntry',
        7:'MainVolume',
        8:'Balance',
        9:'User9',
        10:'Panorama',
        11:'Expression',
        12:'EffectControl1', 13:'EffectControl2',
        14:'User14', 15:'User15',
        16:'GeneralPurpose1', 17:'GeneralPurpose2',
        18:'GeneralPurpose3', 19:'GeneralPurpose4',
        20:'User20', 21:'User21', 22:'User22', 23:'User23',
        24:'User24', 25:'User25', 26:'User26', 27:'User27',
        28:'User28', 29:'User29', 30:'User30', 31:'User31',
        32:'BankSelectFine',
        33:'ModulationFine',
        34:'BreathControlFine',
        35:'User3Fine',
        36:'FootControlFine',
        37:'PortamentTimeFine',
        38:'DataEntryFine',
        39:'MainVolumeFine',
        40:'BalanceFine',
        41:'User9Fine',
        42:'PanoramaFine',
        43:'ExpressionFine',
        44:'EffectControl1Fine', 45:'EffectControl2Fine',
        46:'User14Fine', 47:'User15Fine',
        48:'GeneralPurpose1Fine', 49:'GeneralPurpose2Fine',
        50:'GeneralPurpose3Fine', 51:'GeneralPurpose4Fine',
        52:'User20Fine', 53:'User21Fine', 54:'User22Fine',
        55:'User23Fine', 56:'User24Fine', 57:'User25Fine',
        58:'User26Fine', 59:'User27Fine', 60:'User28Fine',
        61:'User29Fine', 62:'User30Fine', 63:'User31Fine',
        64:'HoldPedal1',
        65:'Portamento',
        66:'SutenutoPedal',
        67:'SoftPedal',
        68:'LegatoPedal',
        69:'HoldPedal2',
        70:'SoundVariation',
        71:'SoundTimbre',
        72:'SoundReleaseTime',
        73:'SoundAttackTime',
        74:'SoundBrightness',
        75:'SoundControl6', 76:'SoundControl7', 77:'SoundControl8',
        78:'SoundControl9',79:'SoundControl10',
        80:'GeneralPurposeButton1', 81:'GeneralPurposeButton2',
        82:'GeneralPurposeButton3', 83:'GeneralPurposeButton4',
        84:'GeneralPurposeButton5', 85:'GeneralPurposeButton6',
        86:'GeneralPurposeButton7', 87:'GeneralPurposeButton8',
        88:'GeneralPurposeButton9', 89:'GeneralPurposeButton10',
        90:'GeneralPurposeButton11',
        91:'EffectsLevel',
        92:'TremeloLevel',
        93:'ChrusLevel',
        94:'CelesteLevel',
        95:'PhaserLevel',
        96:'DataButtonIncrement', 97:'DataButtonDecrement',
        98:'NRPN LSB(Fine]',
        99:'NRPN MSB(Coarse)',
        100:'RPN LSB(Fine)',
        101:'RPN MSB(Coarse)',
        102:'User102', 103:'User103', 104:'User104',
        105:'User105', 106:'User106', 107:'User107',
        108:'User108', 109:'User109', 110:'User110',
        111:'User111', 112:'User112', 113:'User113',
        114:'User114', 115:'User115', 116:'User116',
        117:'User117', 118:'User118', 119:'User119',
        120:'AllSoundOff',
        121:'AllControllerReset',
        122:'LocalKeyboard',
        123:'AllNotesOff',
        124:'OmniModeOff', 125:'OmniModeOn',
        126:'MonoOperation', 127:'PolyOperation',
    }

    def dump(self, fp = sys.stdout, opts = {}):
        if opts.has_key('hexdump') and opts['hexdump']:
            bitio = IO_Bit()
            bitio.input(self._mididata)
        fp.write("HEADER:\n")
        for key, value in self.header['header'].items(): 
            fp.write("  {0}: {1}\n".format(key, value))
        
        if opts.has_key('hexdump') and opts['hexdump']:
            bitio.hexdump(0, self.header['length'] + 8)
        xfkaraoke_with_track = {}
        for idx, value in enumerate(self.tracks):
            pprint(value)
            xfkaraoke_with_track["%s" % idx] = value;
	if self.xfkaraoke != None:
	    xfkaraoke_with_track["karaoke"] = self.xfkaraoke
            xfkaraoke_with_track["karaoke"]["track"] = self.xfkaraoke["xfkaraoke"]
        for idx, track in enumerate(xfkaraoke_with_track):
            scaleCharactors = ['C','C#','D','D#','E','F','F#','G','G#','A','A#','B']
            fp.write("TRACK[%d]:\n" % idx)
            if opts.has_key('hexdump') and opts['hexdump']:
                bitio.hexdump(track['_offset'], 8)
            for idx2, chunk in enumerate(track['track']):
                fp.write("  [%d]:" % idx2)
                meta_event_type = -1
                for key, value in chunk.items():
                    if key == 'EventType':
                        if value < 0xFF: 
                            eventname = self.event_name[value]
                        else:
                            eventname = "Meta Event"
                        fp.write(" {0}:{1}({2}),".format(key, value, eventname))
                    elif key == 'MetaEventType':
                        if self.meta_event_name.has_key(value):
                            meta_event_type = value
                            eventname = self.meta_event_name[value]
                            fp.write(" {0}:{1}({2}),".format(key, value, eventname))
                        else:
                            fp.write(" {0}:{1},".format(key, value))
                        
                    elif key == 'ControllerType':
                        typename = self.controller_type_name[value]
                        fp.write(" {0}:{1}({2}),".format(key, value, typename))
                    elif key ==  'SystemEx' or key ==  'SystemExCont' or key ==  'MetaEventData':
                        fp.write(" %s:" % key)
                        dataLen = len(value)
                        if (key == 'MetaEventData') and (meta_event_type == 0x05):
                            fp.write(value.decude('sjis'))
                        fp.write("(")
                        i = 0
                        while i < dataLen:
                           fp.write("%02x" % ord(value[0:1]))
                           i += 1
                        fp.write(")")
                    elif key ==  'NoteNumber':
		            noteoct = math.floor(value / 12)
		            notekey = scaleCharactors[value % 12]
                            fp.write(" {0}:{1}({2}{3}),".format(key, value, notekey, noteoct))
                    else:
		        if (key[0:1] != '_') or (opts.has_key('verbose') and opts['verbose']): 
                            fp.write(" {0}:{1},".format(key, value))
                
                fp.write("\n")
                if opts.has_key('hexdump') and opts['hexdump']:
                    bitio.hexdump(chunk['_offset'], chunk['_length'])

    def build(self, opts = {}):
        writer = IO_Bit()
        self._buildChunk(writer, self.header, opts)
        for track in self.tracks: 
            self._buildChunk(writer, track, opts)
        
	if self.xfinfo: 
            self._buildChunk(writer, self.xfinfo, opts)
        
	if self.xfkaraoke: 
            self._buildChunk(writer, self.xfkaraoke, opts)
        
        return writer.output()
    

    def _buildChunk(self, writer, chunk, opts):
        type = chunk['type']
        writerChunk = IO_Bit()
        if type == 'MThd':
              self._buildChunkHeader(writerChunk, chunk['header'], opts)
        elif type == 'MTrk':
              self._buildChunkTrack(writerChunk, chunk['track'], opts)
        elif type ==  'XFIH':
              self._buildChunkXFInfo(writerChunk, chunk['xfinfo'], opts)
        elif type == 'XFKM':
              self._buildChunkXFKaraoke(writerChunk, chunk['xfkaraoke'], opts)
        else:
              raise Exception("Unknown chunk (type=type)\n")
        
        chunkData = writerChunk.output()
        length = len(chunkData)
        writer.putData(type , 4)
        writer.putUI32BE(length)
        writer.putData(chunkData, length)
    

    def _buildChunkHeader(self, writer, header, opts):
        writer.putUI16BE(header['Format'])
        writer.putUI16BE(header['NumberOfTracks'])
        division = (header['DivisionFlag'] << 15) | header['Division']
        writer.putUI16BE(division)
    

    def _buildChunkTrack(self, writer, track, opts):
        prev_status = None
        for chunk in track: 
           self.putVaribleLengthValue(writer, chunk['DeltaTime'])
           eventType = chunk['EventType']
           if chunk.has_key('MIDIChannel'):
               midiChannel = chunk['MIDIChannel']
           else:
               if chunk.has_key('MetaEventType'):
                   midiChannel = 0xF
               elif chunk.has_key('SystemEx'):
                   midiChannel = 0
               elif chunk.has_key('SystemExCont'):
                   midiChannel = 0x7
               else:
                   raise Exception("unknown MetaEventType")
               
           
           status = eventType << 4 | midiChannel
           if empty(opts['runningstatus'] == True):
               writer.putUI8(status)
           else:
               if prev_status != status: 
                   writer.putUI8(status)
                   prev_status = status

           if eventType == 0x8 or eventType == 0x9:
               # Note Off # Note On
               writer.putUI8(chunk['NoteNumber'])
               writer.putUI8(chunk['Velocity'])
               break
           elif eventType == 0xA: # Note Aftertouch Event
                writer.putUI8(chunk['NoteNumber'])
                writer.putUI8(chunk['Amount'])
                break
           elif eventType == 0xB: # Controller
                controllerType = chunk['ControllerType']
                writer.putUI8(controllerType)
                if controllerType == 0 or controllerType == 1 or controllerType == 98 or controllerType == 100:
                    # Bank Select #32 more commonly used
                    # Modulation Wheel
                    # NRPN LSB(Fine)
                    # RPN LSB(Fine)
                    writer.putUI8(chunk['LSB'])
                elif controllerType == 99 or controllerType == 101:
                    # NRPN MSB(Coarse)
                    # RPN MSB(Coarse)
                    writer.putUI8(chunk['MSB'])
                else:
                    writer.putUI8(chunk['Value'])
           elif eventType == 0xC: # Program Change
                writer.putUI8(chunk['ProgramNumber'])
           elif eventType == 0xD: # Note Aftertouch Event
                writer.putUI8(chunk['Amount'])
           elif eventType == 0xE: # Pitch Bend Event
                value = chunk['Value'] + 0x2000
                writer.putUI8(value & 0x7f)
                writer.putUI8(value >> 7)
           elif eventType == 0xF: # Meta Event of System Ex
               if midiChannel == 0xF:  # not midiChannel
                   writer.putUI8(chunk['MetaEventType'])
                   length = len(chunk['MetaEventData'])
                   self.putVaribleLengthValue(writer, length)
                   writer.putData(chunk['MetaEventData'], length)
               elif midiChannel == 0x0: # System Ex
                   length = len(chunk['SystemEx'])
                   self.putVaribleLengthValue(writer, length)
                   writer.putData(chunk['SystemEx'], length)
               elif midiChannel == 0x7: # System Ex Cont
                   length = len(chunk['SystemExCont'])
                   self.putVaribleLengthValue(writer, length)
                   writer.putData(chunk['SystemExCont'], length)
               else:
                   print("unknown status=0x%02X\n" % status, end="")
                
           else:
               print("unknown EventType=0x%02X\n" % eventType, end="")
               exit (0)
           
        
    

    def _buildChunkXFInfo(self, writer, xfinfo, opts):
        prev_status = None
        for chunk in xfinfo: 
            self.putVaribleLengthValue(writer, chunk['DeltaTime'])
     	    status = 0xFF # MetaEvent
            if empty(opts['runningstatus'] == True):
               writer.putUI8(status)
            else:
               if prev_status != status: 
                   writer.putUI8(status)
                   prev_status = status
               
            
            writer.putUI8(chunk['MetaEventType'])
	    length = len(chunk['MetaEventData'])
            self.putVaribleLengthValue(writer, length)
            writer.putData(chunk['MetaEventData'], length)

    def _buildChunkXFKaraoke(self, writer, xfkaraoke, opts):
        prev_status = None
	for chunk in xfkaraoke: 
	    self.putVaribleLengthValue(writer, chunk['DeltaTime'])
     	    status = 0xFF # MetaEvent
            if empty(opts['runningstatus'] == True):
               writer.putUI8(status)
            else:
               if prev_status != status: 
                   writer.putUI8(status)
                   prev_status = status
               
            
	    type = chunk['MetaEventType']
            writer.putUI8(type)
	    if type == 0x2F:  # End of Track
	        self.putVaribleLengthValue(writer, 0)
            else:
                length = len(chunk['MetaEventData'])
                self.putVaribleLengthValue(writer, length)
                writer.putData(chunk['MetaEventData'], length)
            
        
    

    def putVaribleLengthValue(self, writer, value):
        binList = []
        if value == 0: 
                binList.append(0)
        else:
            while value > 0:
                binList.append(value & 0x7F)
                value >>= 7
        
        while len(binList) > 1:
            bin = array_pop(binList)
            writer.putUI8(0x80 | bin)
        
        writer.putUI8(binList[0])
        return True
