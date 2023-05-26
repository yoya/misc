export PKG_CONFIG_PATH="/usr/local/opt/libxml2/lib/pkgconfig"
#export CFLAGS="-I /opt/local/include"
#export LIBS="-L/opt/local/lib"
export CFLAGS="-I /usr/local/include"
export LIBS="-L/usr/local/lib"
#zlibver=1.2.4.5
zlibver=1.2.5.3
#libpngver=1.2.59
#libpngver=1.4.22
#libpngver=1.5.30
libpngver=1.6.29
#openjpegver=1.5.2
openjpegver=2.0.1
#export CFLAGS="-I $HOME/Zlib/$zlibver/include -I $HOME/libpng/$libpngver/include -I /opt/local/include -DFARDATA=\"\" "
#export LIBS="-L$HOME/Zlib/$zlibver/lib -L$HOME/libpng/$libpngver/lib -L/opt/local/lib"
#export CFLAGS="-I $HOME/Zlib/$zlibver/include -I $HOME/libpng/$libpngver/include -I $HOME/openjpeg/$openjpegver/include -DFARDATA=\"\" "
#export LIBS="-L$HOME/Zlib/$zlibver/lib -L$HOME/libpng/$libpngver/lib -L$HOME/openjpeg/$openjpegver/lib "

#files=`ls -r ImageMagick-7.0.11-12.tar.gz`
#files=`ls ImageMagick-6.*.tar.gz`
files=`ls -r ImageMagick-*.tar.gz`

confopts="--without-perl --without-magick-plus-plus  --without-x --with-wmf=no"
# confopts="--without-perl --without-magick-plus-plus  --without-x -with-wmf=no --with-zlib=$HOME/Zlib/$zlibver --with-png=$HOME/libpng/$libpngver -with-openjp2=$HOME/openjpeg/$openjpegver "
# confopts="--without-perl --without-magick-plus-plus  --without-x --with-webp=yes --with-heic=yes --with-wmf=no --with-zlib=$HOME/Zlib/$zlibver --with-png=$HOME/libpng/$libpngver -with-openjp2=$HOME/openjpeg/$openjpegver "

for file in $files ; do
  version=`echo $file | sed 's/ImageMagick-\(.*\).tar\(.*\)/\1/'`
  version2=`echo $file | sed 's/ImageMagick-\(.*\)-[0-9]\+.tar\(.*\)/\1/'`
  echo $version $version2
  prefix="$HOME/ImageMagick/$version"
  if [ -f "$prefix/bin/convert" ] ; then
      echo "found: $prefix/bin/convert"
      continue
  fi
  if [ "$pre_version" != "$version" ] ; then
    echo === $file ===
    tar xf $file
    dir="ImageMagick-$version"
    if ! [ -d $dir ] ; then
        dir="ImageMagick-$version2";
    fi
    if [ -d $dir ] ; then
      (cd $dir ; ./configure --prefix=$prefix $confopts ; make install)
      # # # # # rm -rf $dir
    else
      echo "Not found dir: $dir";
    fi
    pre_version=$version
  fi
done
