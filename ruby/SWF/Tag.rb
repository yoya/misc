class SWF_Tag
  def setMetaInfo(type, length, typeName)
    @Type = type
    @Length = length
    @TypeName = typeName
  end
  def getType
    return @Type
  end
  def getTypeName
    return @TypeName
  end
  def getLength
    return @Length
  end
  def dump()
    printf("Type=%d(%s) Length=%d\n", @Type, @TypeName, @Length)
  end
end
