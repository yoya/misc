<code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php<br /><br /></span><span style="color: #FF8000">//&nbsp;original:&nbsp;http://php-archive.net/php/hsv-similar-images/<br />//&nbsp;modified&nbsp;by&nbsp;yoya&nbsp;at&nbsp;2013/09/17<br /><br /></span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$argc&nbsp;</span><span style="color: #007700">!==&nbsp;</span><span style="color: #0000BB">2</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"Usage:&nbsp;php&nbsp;imagehuesort.php&nbsp;&lt;dir&gt;\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"ex)&nbsp;php&nbsp;imagehuesort.php&nbsp;img/\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;exit(</span><span style="color: #0000BB">1</span><span style="color: #007700">);<br />}<br /><br /></span><span style="color: #FF8000">//&nbsp;対象画像ディレクトリ<br /></span><span style="color: #0000BB">$dir&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$argv</span><span style="color: #007700">[</span><span style="color: #0000BB">1</span><span style="color: #007700">];<br /><br /></span><span style="color: #0000BB">$list&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">scandir</span><span style="color: #007700">(</span><span style="color: #0000BB">$dir</span><span style="color: #007700">);<br /></span><span style="color: #0000BB">$files&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />foreach&nbsp;(</span><span style="color: #0000BB">$list&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$path&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$dir&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">$value</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">is_file</span><span style="color: #007700">(</span><span style="color: #0000BB">$path</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$files</span><span style="color: #007700">[]&nbsp;=&nbsp;</span><span style="color: #0000BB">$path</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />}<br />&nbsp;<br /></span><span style="color: #0000BB">$hueTable&nbsp;</span><span style="color: #007700">=&nbsp;array();<br />foreach&nbsp;(</span><span style="color: #0000BB">$files&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$file</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$image&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">loadImage</span><span style="color: #007700">(</span><span style="color: #0000BB">$file</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">$image&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;continue;&nbsp;</span><span style="color: #FF8000">//&nbsp;skip<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">}<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hsv&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">imageHsv</span><span style="color: #007700">(</span><span style="color: #0000BB">$image</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">ImageDestroy</span><span style="color: #007700">(</span><span style="color: #0000BB">$image</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;明示的に後始末しないとメモリリークする<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hueTable</span><span style="color: #007700">[</span><span style="color: #0000BB">$file</span><span style="color: #007700">]&nbsp;=&nbsp;</span><span style="color: #0000BB">$hsv</span><span style="color: #007700">[</span><span style="color: #DD0000">'h'</span><span style="color: #007700">];<br />}<br /><br /></span><span style="color: #0000BB">asort</span><span style="color: #007700">(</span><span style="color: #0000BB">$hueTable</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;色相値の小さい順にソート<br /></span><span style="color: #0000BB">$result&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">array_keys</span><span style="color: #007700">(</span><span style="color: #0000BB">$hueTable</span><span style="color: #007700">);<br /><br /></span><span style="color: #0000BB">header</span><span style="color: #007700">(</span><span style="color: #DD0000">"Content-type:&nbsp;text/html;charset=utf-8"</span><span style="color: #007700">);<br />foreach&nbsp;(</span><span style="color: #0000BB">$result&nbsp;</span><span style="color: #007700">as&nbsp;</span><span style="color: #0000BB">$file</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #DD0000">"&lt;a&nbsp;href=\"</span><span style="color: #0000BB">$file</span><span style="color: #DD0000">\"&nbsp;target=\"_blank\"&gt;&nbsp;&lt;img&nbsp;src='</span><span style="color: #0000BB">$file</span><span style="color: #DD0000">'&nbsp;width='64'&nbsp;height='64'&nbsp;alt='</span><span style="color: #0000BB">$file</span><span style="color: #DD0000">'&nbsp;/&gt;&nbsp;&lt;/a&gt;"</span><span style="color: #007700">.</span><span style="color: #0000BB">PHP_EOL</span><span style="color: #007700">;<br />}<br /><br />exit&nbsp;(</span><span style="color: #0000BB">0</span><span style="color: #007700">);<br />&nbsp;<br /></span><span style="color: #FF8000">//&nbsp;画像を読み込む<br /></span><span style="color: #007700">function&nbsp;</span><span style="color: #0000BB">loadImage</span><span style="color: #007700">(</span><span style="color: #0000BB">$filepath</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$data&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">file_get_contents</span><span style="color: #007700">(</span><span style="color: #0000BB">$filepath</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$image&nbsp;</span><span style="color: #007700">=&nbsp;@</span><span style="color: #0000BB">ImageCreateFromString</span><span style="color: #007700">(</span><span style="color: #0000BB">$data</span><span style="color: #007700">);&nbsp;</span><span style="color: #FF8000">//&nbsp;XXX<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">if&nbsp;(</span><span style="color: #0000BB">$image&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">false</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;if&nbsp;(</span><span style="color: #0000BB">is_null</span><span style="color: #007700">(</span><span style="color: #0000BB">$image</span><span style="color: #007700">))&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;echo&nbsp;</span><span style="color: #0000BB">$filepath</span><span style="color: #007700">.</span><span style="color: #DD0000">"\n"</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$image</span><span style="color: #007700">;<br />}<br /><br />function&nbsp;</span><span style="color: #0000BB">imageHsv</span><span style="color: #007700">(</span><span style="color: #0000BB">$image</span><span style="color: #007700">)&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$width&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">imagesx</span><span style="color: #007700">(</span><span style="color: #0000BB">$image</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$height&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">imagesy</span><span style="color: #007700">(</span><span style="color: #0000BB">$image</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$thumb_width&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$thumb_height&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">1</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$thumb&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">imagecreatetruecolor</span><span style="color: #007700">(</span><span style="color: #0000BB">$thumb_width</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$thumb_height</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">imagecopyresampled</span><span style="color: #007700">(</span><span style="color: #0000BB">$thumb</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$image</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$thumb_width</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$thumb_height</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$width</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$height</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$index&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">imagecolorat</span><span style="color: #007700">(</span><span style="color: #0000BB">$thumb</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$rgb&nbsp;&nbsp;&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">imagecolorsforindex</span><span style="color: #007700">(</span><span style="color: #0000BB">$thumb</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$index</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">rgb2hsv</span><span style="color: #007700">(</span><span style="color: #0000BB">$rgb</span><span style="color: #007700">);<br />}<br />&nbsp;<br />function&nbsp;</span><span style="color: #0000BB">rgb2hsv</span><span style="color: #007700">(</span><span style="color: #0000BB">$rgb</span><span style="color: #007700">){<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$r&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$rgb</span><span style="color: #007700">[</span><span style="color: #DD0000">'red'</span><span style="color: #007700">]&nbsp;&nbsp;&nbsp;/&nbsp;</span><span style="color: #0000BB">255</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$g&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$rgb</span><span style="color: #007700">[</span><span style="color: #DD0000">'green'</span><span style="color: #007700">]&nbsp;/&nbsp;</span><span style="color: #0000BB">255</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$rgb</span><span style="color: #007700">[</span><span style="color: #DD0000">'blue'</span><span style="color: #007700">]&nbsp;&nbsp;/&nbsp;</span><span style="color: #0000BB">255</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">max</span><span style="color: #007700">(</span><span style="color: #0000BB">$r</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$g</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$min&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">min</span><span style="color: #007700">(</span><span style="color: #0000BB">$r</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$g</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$v&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$max</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;if(</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">$min</span><span style="color: #007700">){<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;else&nbsp;if(</span><span style="color: #0000BB">$r&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">$max</span><span style="color: #007700">){<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">60&nbsp;</span><span style="color: #007700">*&nbsp;(&nbsp;(</span><span style="color: #0000BB">$g&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$b</span><span style="color: #007700">)&nbsp;/&nbsp;(</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$min</span><span style="color: #007700">)&nbsp;)&nbsp;+&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;else&nbsp;if(</span><span style="color: #0000BB">$g&nbsp;</span><span style="color: #007700">===&nbsp;</span><span style="color: #0000BB">$max</span><span style="color: #007700">){<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">60&nbsp;</span><span style="color: #007700">*&nbsp;(&nbsp;(</span><span style="color: #0000BB">$b&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$r</span><span style="color: #007700">)&nbsp;/&nbsp;(</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$min</span><span style="color: #007700">)&nbsp;)&nbsp;+&nbsp;</span><span style="color: #0000BB">120</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}&nbsp;else&nbsp;{<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">60&nbsp;</span><span style="color: #007700">*&nbsp;(&nbsp;(</span><span style="color: #0000BB">$r&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$g</span><span style="color: #007700">)&nbsp;/&nbsp;(</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$min</span><span style="color: #007700">)&nbsp;)&nbsp;+&nbsp;</span><span style="color: #0000BB">240</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;&nbsp;}<br />&nbsp;&nbsp;&nbsp;&nbsp;if(</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">&lt;&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">)&nbsp;</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #0000BB">$h&nbsp;</span><span style="color: #007700">+&nbsp;</span><span style="color: #0000BB">360</span><span style="color: #007700">;<br />&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$s&nbsp;</span><span style="color: #007700">=&nbsp;(</span><span style="color: #0000BB">$v&nbsp;</span><span style="color: #007700">!=&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">)&nbsp;?&nbsp;(</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">-&nbsp;</span><span style="color: #0000BB">$min</span><span style="color: #007700">)&nbsp;/&nbsp;</span><span style="color: #0000BB">$max&nbsp;</span><span style="color: #007700">:&nbsp;</span><span style="color: #0000BB">0</span><span style="color: #007700">;<br />&nbsp;&nbsp;&nbsp;<br />&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="color: #0000BB">$hsv&nbsp;</span><span style="color: #007700">=&nbsp;array(</span><span style="color: #DD0000">'h'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$h</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'s'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$s</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">'v'&nbsp;</span><span style="color: #007700">=&gt;&nbsp;</span><span style="color: #0000BB">$v</span><span style="color: #007700">);<br />&nbsp;&nbsp;&nbsp;&nbsp;return&nbsp;</span><span style="color: #0000BB">$hsv</span><span style="color: #007700">;<br />}<br /></span>
</span>
</code>