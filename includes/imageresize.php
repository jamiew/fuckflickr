<?php
##############################################################
##    ___              __     ___ __ __        __           ##
##  .'  _|.--.--.----.|  |--.'  _|  |__|.----.|  |--.----.  ##
##  |   _||  |  |  __||    <|   _|  |  ||  __||    <|   _|  ##
##  |__|  |_____|____||__|__|__| |__|__||____||__|__|__|    ##
##############################################################


/*
* image resizing object, parent to our flickr fucker
*/
class imageResize {
	function resizeImage($file, $dest, $newWidth, $newHeight, $quality, $bResizeOnWidth){
		if (!file_exists($file)) {
			if ($this->debug) echo 'source does not exist!'. FF_BR;
			return false;
		}

		$w = 0;
		$h = 0;
		$src = false;

		// Create an Image from it so we can do the resize
		$info = getimagesize($file);
    $width = $info[0]; $height = $info[1]; $type = $info[2]; $bits = $info['bits'];

    // Do not resample up the image size
		if ($width <= $newWidth && $height <= $newHeight) {
		  copy($file, $dest);
		  return (is_file($dest));
		}

    if ($type == '1' && $dest != $file) { // if dest != file, then must be orig gif file, not the temp file to be resized
      $fp = fread (fopen($file, "rb"), filesize($file));
      if ($fp) {
        $gifb = new GIFDecoder($fp);
      } elseif ($this->debug) {
        echo 'could not read animated file'. FF_BR;
      }
    }

    // Must eval is GIF has more than one frame. Cannot determine by bit length, as some are 6, some are 8, and some are crazy motherfuckers.
    if ($type == '1' && is_object($gifb) && sizeof($gifb->GIFGetFrames()) > 1) {
      $arr = $gifb->GIFGetFrames();
      $dly = $gifb->GIFGetDelays();
      for ($i=0; $i<count($arr); $i++) {
        $name = dirname($dest) .'/temp_'. (($i<10) ? '00' : (($i<100) ? '0' : '')) . $i .'.gif';
        fwrite(fopen($name, 'wb'), $arr[$i]);
        if ($this->resizeImage($name, $name, $newWidth, $newHeight, $quality, $bResizeOnWidth)) {
          list($w, $h) = getimagesize($name);
          $giff[] = $name;
          $gifd[] = $dly[$i];
        }
      }

      $gife = new GIFEncoder($giff, $gifd, 0, 2, 0, 0, 0, 'url');
      fwrite(fopen($dest, 'wb'), $gife->GetAnimation());
      //for ($i=0; $i<sizeof($giff); $i++) unlink($giff[$i]);
      $this->resize_count += (sizeof($arr)-1);
      return (is_file($dest));
    } else { // Do normal resize, whatever that is...
  		switch ($type) {
  			case '2': $src = @imagecreatefromjpeg($file); break;
  			case '3': $src = @imagecreatefrompng($file); break;
  			case '1': $src = @imagecreatefromgif($file); break;
  		}

  		if (!$src) {
        if ($this->debug) echo 'problem opening '. $file .' - check it is complete'. FF_BR;
  			return false;
  		}

  		if ($bResizeOnWidth == 1 || ($width > $height)){
  			$w = $newWidth;
  			$ratio = $height/$width;
  			$h = round($ratio * $w);
  		} else {
  			$h = $newHeight;
  			$ratio = $width/$height;
  			$w = round($ratio * $h);
  		}

  		if ($tmp = imagecreatetruecolor($w,$h)) {
  			// FROM Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable
  			if (FF_IMG_QUALITY < 5 && (($w * FF_IMG_QUALITY) < $width || ($h * FF_IMG_QUALITY) < $height)) {
  			  if ($tmp2 = imagecreatetruecolor (($w * FF_IMG_QUALITY + 1), ($h * FF_IMG_QUALITY + 1))) {
  					imagecopyresized ($tmp2, $src, 0, 0, 0, 0, ($w * FF_IMG_QUALITY + 1), ($h * FF_IMG_QUALITY + 1), $width, $height);
  					imagecopyresampled ($tmp, $tmp2, 0, 0, 0, 0, $w, $h, ($w * FF_IMG_QUALITY), ($h * FF_IMG_QUALITY));
  					imagedestroy ($tmp2);
  				} elseif ($this->debug) {
  					echo 'could not create temp image for faster resize'. FF_BR;
  					return false;
  				}
  			} else {
  			  imagecopyresampled ($tmp, $src, 0, 0, 0, 0, $w, $h, $width, $height);
  			}

  			switch ($type) {
  				case '2': @imagejpeg($tmp, $dest, $quality); break;
  				case '3': @imagepng($tmp, $dest, (9-round($quality/9))); break; // quality is 0-9, with 0 being the best. convert from jpeg 100 scale.
  				case '1': @imagegif($tmp, $dest); break; // gif has no quality
  			}
  			imagedestroy($src);
  			imagedestroy($tmp);

  			return (is_file($dest)); // return true if file was created successfully
  		} elseif ($this->debug) {
  			echo 'could not create temp image for resize'. FF_BR;
  		}

  		return false;
  	}
	}
}

// GIFDecoder Version 2.0 by László Zsidi, http://gifs.hu 
Class GIFDecoder {
  var $GIF_buffer = Array(); var $GIF_arrays = Array(); var $GIF_delays = Array(); var $GIF_stream = ''; var $GIF_string = ''; var $GIF_bfseek =  0; var $GIF_screen = Array(); var $GIF_global = Array(); var $GIF_sorted; var $GIF_colorS; var $GIF_colorC; var $GIF_colorF;

  function GIFDecoder($GIF_pointer) {
    $this->GIF_stream = $GIF_pointer;

    GIFDecoder::GIFGetByte(6);    // GIF89a
    GIFDecoder::GIFGetByte(7);    // Logical Screen Descriptor

    $this->GIF_screen = $this->GIF_buffer;

    $this->GIF_colorF = $this->GIF_buffer[ 4 ] & 0x80 ? 1 : 0;
    $this->GIF_sorted = $this->GIF_buffer[ 4 ] & 0x08 ? 1 : 0;
    $this->GIF_colorC = $this->GIF_buffer[ 4 ] & 0x07;
    $this->GIF_colorS = 2 << $this->GIF_colorC;

    if ($this->GIF_colorF == 1) {
      GIFDecoder::GIFGetByte(3 * $this->GIF_colorS);
      $this->GIF_global = $this->GIF_buffer;
    }

    for ($cycle = 1; $cycle; ) {
      if ( GIFDecoder::GIFGetByte(1)) {
        switch ( $this->GIF_buffer[0]) {
          case  0x21:
            GIFDecoder::GIFReadExtensions();
            break;
          case 0x2C:
            GIFDecoder::GIFReadDescriptor();
            break;
          case 0x3B:
            $cycle = 0;
            break;
        }
      } else {
        $cycle = 0;
      }
    }
  }

  function GIFReadExtensions ( ) {
    GIFDecoder::GIFGetByte ( 1 );
    for ( ; ; ) {
      GIFDecoder::GIFGetByte ( 1 );
      if ( ( $u = $this->GIF_buffer [ 0 ] ) == 0x00 ) break;
      GIFDecoder::GIFGetByte ( $u );
      if ( $u == 4 ) $this->GIF_delays [ ] = ( $this->GIF_buffer [ 1 ] | $this->GIF_buffer [ 2 ] << 8 );
    }
  }

  function GIFReadDescriptor ( ) {
    $GIF_screen    = Array ( );

    GIFDecoder::GIFGetByte ( 9 );
    $GIF_screen = $this->GIF_buffer;
    $GIF_colorF = $this->GIF_buffer [ 8 ] & 0x80 ? 1 : 0;
    if ( $GIF_colorF ) {
      $GIF_code = $this->GIF_buffer [ 8 ] & 0x07;
      $GIF_sort = $this->GIF_buffer [ 8 ] & 0x20 ? 1 : 0;
    } else {
      $GIF_code = $this->GIF_colorC;
      $GIF_sort = $this->GIF_sorted;
    }
    $this->GIF_screen [ 4 ] &= 0x70;
    $this->GIF_screen [ 4 ] |= 0x80;
    $this->GIF_screen [ 4 ] |= $GIF_code;
    if ( $GIF_sort ) $this->GIF_screen [ 4 ] |= 0x08;
    $this->GIF_string = "GIF87a";
    GIFDecoder::GIFPutByte ( $this->GIF_screen );
    if ( $GIF_colorF == 1 ) {
      GIFDecoder::GIFGetByte ( 3 * $GIF_size );
      GIFDecoder::GIFPutByte ( $this->GIF_buffer );
    } else {
      GIFDecoder::GIFPutByte ( $this->GIF_global );
    }
    $this->GIF_string .= chr ( 0x2C );
    $GIF_screen [ 8 ] &= 0x40;
    GIFDecoder::GIFPutByte ( $GIF_screen );
    GIFDecoder::GIFGetByte ( 1 );
    GIFDecoder::GIFPutByte ( $this->GIF_buffer );
    for ( ; ; ) {
      GIFDecoder::GIFGetByte ( 1 );
      GIFDecoder::GIFPutByte ( $this->GIF_buffer );
      if ( ( $u = $this->GIF_buffer [ 0 ] ) == 0x00 ) break;
      GIFDecoder::GIFGetByte ( $u );
      GIFDecoder::GIFPutByte ( $this->GIF_buffer );
    }
    $this->GIF_string .= chr ( 0x3B );
    $this->GIF_arrays [ ] = $this->GIF_string;
  }

  function GIFGetByte ( $len ) {
    $this->GIF_buffer = Array ( );
    for ( $i = 0; $i < $len; $i++ ) {
      if ( $this->GIF_bfseek > strlen ( $this->GIF_stream ) ) return 0;
      $this->GIF_buffer [ ] = ord ( $this->GIF_stream { $this->GIF_bfseek++ } );
    }
    return 1;
  }
  function GIFPutByte ( $bytes ) {for ( $i = 0; $i < count ( $bytes ); $i++ ) $this->GIF_string .= chr ( $bytes [ $i ] );}
  function GIFGetFrames ( ) {return ( $this->GIF_arrays );}
  function GIFGetDelays ( ) {return ( $this->GIF_delays );}
}

// GIFEncoder Version 2.0 by László Zsidi, http://gifs.hu
Class GIFEncoder {
  var $GIF = "GIF89a"; var $VER = "GIFEncoder V2.05";
  var $BUF = Array (); var $LOP =  0; var $DIS =  2; var $COL = -1; var $IMG = -1;

  var $ERR = Array (
    'ERR00'=>'Requires more than one image.',
    'ERR01'=>'Source is not a GIF image.',
    'ERR02'=>'Unintelligible flag.',
    'ERR03'=>'Cannot make animated GIF from animated GIF (duh!).',
  );

  function GIFEncoder($GIF_src, $GIF_dly, $GIF_lop, $GIF_dis, $GIF_red, $GIF_grn, $GIF_blu, $GIF_mod) {
    if ( ! is_array ( $GIF_src ) && ! is_array ( $GIF_dly ) ) {
      printf    ( "%s: %s", $this->VER, $this->ERR [ 'ERR00' ] );
      exit(0);
    }
    $this->LOP = ( $GIF_lop > -1 ) ? $GIF_lop : 0;
    $this->DIS = ( $GIF_dis > -1 ) ? ( ( $GIF_dis < 3 ) ? $GIF_dis : 3 ) : 2;
    $this->COL = ( $GIF_red > -1 && $GIF_grn > -1 && $GIF_blu > -1 ) ? ( $GIF_red | ( $GIF_grn << 8 ) | ( $GIF_blu << 16 ) ) : -1;

    for ( $i = 0; $i < count ( $GIF_src ); $i++ ) {
      if ( strToLower ( $GIF_mod ) == "url" ) {
      $this->BUF [ ] = fread ( fopen ( $GIF_src [ $i ], "rb" ), filesize ( $GIF_src [ $i ] ) );
      } else if ( strToLower ( $GIF_mod ) == "bin" ) {
      $this->BUF [ ] = $GIF_src [ $i ];
      } else {
        printf    ( "%s: %s ( %s )!", $this->VER, $this->ERR [ 'ERR02' ], $GIF_mod );
        exit(0);
      }
      if ( substr ( $this->BUF [ $i ], 0, 6 ) != "GIF87a" && substr ( $this->BUF [ $i ], 0, 6 ) != "GIF89a" ) {
        printf    ( "%s: %d %s", $this->VER, $i, $this->ERR [ 'ERR01' ] );
        exit(0);
      }
      for ( $j = ( 13 + 3 * ( 2 << ( ord ( $this->BUF [ $i ] { 10 } ) & 0x07 ) ) ), $k = TRUE; $k; $j++ ) {
      switch ( $this->BUF [ $i ] { $j } ) {
        case "!":
          if ( ( substr ( $this->BUF [ $i ], ( $j + 3 ), 8 ) ) == "NETSCAPE" ) {
            printf ( "%s: %s ( %s source )!", $this->VER, $this->ERR [ 'ERR03' ], ( $i + 1 ));
            exit (0);
          }
          break;
        case ";":
          $k = FALSE;
          break;
        }
      }
    }
    GIFEncoder::GIFAddHeader ( );
    for ( $i = 0; $i < count ( $this->BUF ); $i++ ) GIFEncoder::GIFAddFrames ( $i, $GIF_dly [ $i ] );
    GIFEncoder::GIFAddFooter ( );
  }

  function GIFAddHeader ( ) {
    $cmap = 0;

    if ( ord ( $this->BUF [ 0 ] { 10 } ) & 0x80 ) {
      $cmap = 3 * ( 2 << ( ord ( $this->BUF [ 0 ] { 10 } ) & 0x07 ) );
      $this->GIF .= substr ( $this->BUF [ 0 ], 6, 7);
      $this->GIF .= substr ( $this->BUF [ 0 ], 13, $cmap);
      $this->GIF .= "!\377\13NETSCAPE2.0\3\1" . GIFEncoder::GIFWord ( $this->LOP ) . "\0";
    }
  }

  function GIFAddFrames ( $i, $d ) {
    $Locals_str = 13 + 3 * ( 2 << ( ord ( $this->BUF [ $i ] { 10 } ) & 0x07 ) );

    $Locals_end = strlen ( $this->BUF [ $i ] ) - $Locals_str - 1;
    $Locals_tmp = substr ( $this->BUF [ $i ], $Locals_str, $Locals_end );

    $Global_len = 2 << ( ord ( $this->BUF [ 0  ] { 10 } ) & 0x07 );
    $Locals_len = 2 << ( ord ( $this->BUF [ $i ] { 10 } ) & 0x07 );

    $Global_rgb = substr ( $this->BUF [ 0  ], 13, 3 * ( 2 << ( ord ( $this->BUF [ 0  ] { 10 } ) & 0x07 ) ) );
    $Locals_rgb = substr ( $this->BUF [ $i ], 13, 3 * ( 2 << ( ord ( $this->BUF [ $i ] { 10 } ) & 0x07 ) ) );

    $Locals_ext = "!\xF9\x04" . chr ( ( $this->DIS << 2 ) + 0 ) .
    chr ( ( $d >> 0 ) & 0xFF ) . chr ( ( $d >> 8 ) & 0xFF ) . "\x0\x0";

    if ( $this->COL > -1 && ord ( $this->BUF [ $i ] { 10 } ) & 0x80 ) {
      for ( $j = 0; $j < ( 2 << ( ord ( $this->BUF [ $i ] { 10 } ) & 0x07 ) ); $j++ ) {
        if (ord ( $Locals_rgb { 3 * $j + 0 } ) == ( ( $this->COL >> 16 ) & 0xFF ) && ord ( $Locals_rgb { 3 * $j + 1 } ) == ( ( $this->COL >>  8 ) & 0xFF ) && ord ( $Locals_rgb { 3 * $j + 2 } ) == ( ( $this->COL >>  0 ) & 0xFF )) {
          $Locals_ext = "!\xF9\x04" . chr ( ( $this->DIS << 2 ) + 1 ) .
          chr ( ( $d >> 0 ) & 0xFF ) . chr ( ( $d >> 8 ) & 0xFF ) . chr ( $j ) . "\x0";
          break;
        }
      }
    }
    switch ( $Locals_tmp { 0 } ) {
      case "!":
        $Locals_img = substr ( $Locals_tmp, 8, 10 );
        $Locals_tmp = substr ( $Locals_tmp, 18, strlen ( $Locals_tmp ) - 18 );
        break;
      case ",":
        $Locals_img = substr ( $Locals_tmp, 0, 10 );
        $Locals_tmp = substr ( $Locals_tmp, 10, strlen ( $Locals_tmp ) - 10 );
        break;
    }

    if (ord($this->BUF [ $i ] { 10 } ) & 0x80 && $this->IMG > -1 ) {
      if ( $Global_len == $Locals_len ) {
        if ( GIFEncoder::GIFBlockCompare ( $Global_rgb, $Locals_rgb, $Global_len ) ) {
          $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_tmp );
        } else {
          $byte  = ord ( $Locals_img { 9 } );
          $byte |= 0x80;
          $byte &= 0xF8;
          $byte |= ( ord ( $this->BUF [ 0 ] { 10 } ) & 0x07 );
          $Locals_img { 9 } = chr ( $byte );
          $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp );
        }
      } else {
        $byte  = ord ( $Locals_img { 9 } );
        $byte |= 0x80;
        $byte &= 0xF8;
        $byte |= ( ord ( $this->BUF [ $i ] { 10 } ) & 0x07 );
        $Locals_img { 9 } = chr ( $byte );
        $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_rgb . $Locals_tmp );
      }
    } else {
      $this->GIF .= ( $Locals_ext . $Locals_img . $Locals_tmp );
    }

    $this->IMG  = 1;
  }

  function GIFAddFooter() {$this->GIF .= ";";}
  function GIFBlockCompare($GlobalBlock, $LocalBlock, $Len) {
    for ($i = 0; $i < $Len; $i++) if ($GlobalBlock { 3 * $i + 0 } != $LocalBlock { 3 * $i + 0 } || $GlobalBlock { 3 * $i + 1 } != $LocalBlock { 3 * $i + 1 } || $GlobalBlock { 3 * $i + 2 } != $LocalBlock { 3 * $i + 2 }) return (0);
    return (1);
  }
  function GIFWord ($int) {return (chr($int & 0xFF) . chr(($int >> 8) & 0xFF));}
  function GetAnimation() {return ($this->GIF);}
}


?>