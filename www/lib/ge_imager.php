<?php
/**
 *	GydruS's Engine "GEngine" ge_Imager Module (Project)
 *	this images manager is developed for caching and accessing images flexible way
 *
 *	image "query" path example: "http://localhost/ge/ge_imager.php?pic=pictures/test.jpg&w=120&h=90&p=1"
 *
 *	"p" param means that picture will be resized for preview with saving proportions and using croping if needed
 *
 *
 *  (C) Grigori S. Mozharovski aka GydruS
 *
 *  Version 1.2b @ 2017.03.11
 *  Mailto: gydrus@mail.ru
 */

define('IMAGER_CACHE_PATH','../cache/'); // this folder must exists!!!
define('IMAGER_CACHE_TTL',86400);
//define('IMAGER_CACHE_TTL',1);

#=============================
# Initialization 
#=============================
# Functuions 
function getImageFileName($filePath)
{
	$fileName = strrchr($filePath, '/');
	if ( $fileName === false ) { return false; }
	else { return $rest = substr($fileName, 1, strlen($fileName)-1); }
}
//********************************************************************************************
function getImageFileExtension($filePath)
{
	$extenstion = strrchr($filePath, '.');
	if ( $extenstion === false ) { return false; }
	else { return $rest = substr($extenstion, 1, strlen($extenstion)-1); }
}
//********************************************************************************************
function getImageFileBaseName($filePath)
{
    // native basename() function is not works correctly with unicode names!
    $fn = getImageFileName($filePath);
	$dotPos = strpos($fn, '.');
	if ( $dotPos === false ) { return false; }
	else { return substr($fn, 0, $dotPos); }
}
//********************************************************************************************
function getImageType($fileName)
{
    return exif_imagetype($fileName);
}
//********************************************************************************************
function getImage($fn)
{
	if ( file_exists($fn) )  return file_get_contents($fn); 
	else  return NULL; 
}
//********************************************************************************************
function deleteOldCache()
{
	$dd = dir(IMAGER_CACHE_PATH);
	while ( false !== ( $entry = $dd->read() ) )
	{
		if ( $entry == '.' || $entry == '..' || substr($entry,-6) != '.cache' ) continue;
		$fileName = IMAGER_CACHE_PATH.$entry;
		if ( time() - filemtime($fileName) > IMAGER_CACHE_TTL ) unlink($fileName);
	}
}
//**************************************************************************
function imagecreatefrom($image,$type)
{
    $src = false;
	switch($type)
	{
		case IMAGETYPE_PNG:  { $src = imagecreatefrompng($image); break;}
		case IMAGETYPE_GIF:  { $src = imagecreatefromgif($image); break;}
		case IMAGETYPE_WBMP: { $src = imagecreatefromwbmp($image); break;}
		case IMAGETYPE_JPEG: { $src = imagecreatefromjpeg($image); break;}
	}
	return $src;
}
//**************************************************************************
function image($image,$dest,$type)
{
	switch($type)
	{
		case IMAGETYPE_PNG:  { imagepng($image,$dest); break;}
		case IMAGETYPE_GIF:  { imagegif($image,$dest); break;}
		case IMAGETYPE_WBMP: { imagewbmp($image,$dest); break;}
		case IMAGETYPE_JPEG: { imagejpeg($image,$dest); break;}
	}
}
//**************************************************************************
// p - means "Proportional scaling" (with cropping, if setted booth width and height and p = 1, or with scaling to fit only biggest side when p = 2)
// ne - means "do Not Enlage"
// $watermarkPosition - 0 = top left; 9 = bottom right; 5 = center and etc. in accomplishing to 123\n456\n789 square
function resizeImage($width, $height, $source, $destination, $p=0, $ne=0, $watermarkSrc='', $watermarkPosition=9, $drawWatermarkIfPicWidthGT = 320, $drawWatermarkIfPicHeightGT = 240)
{
	$type = getImageType($source);
	$src = imagecreatefrom($source,$type);
	$src_w = imagesx($src);
	$src_h = imagesy($src);
	
	if(($width!=0) && ($height!=0))
	{
		if ($ne != 0)
		{
			($src_w < $width) ? $out_w = $src_w : $out_w = $width;
			($src_h < $height) ? $out_h = $src_h : $out_h = $height;
		}
		else
		{
			$out_w = $width;
			$out_h = $height;
		}
	}
	elseif (($width!=0) && ($height==0))
	{
		if ($ne != 0)
		{
			($src_w < $width) ? $out_w = $src_w : $out_w = $width;
			$out_h = $src_h;
		}
		else
		{
			$out_w = $width;
			$out_h = $src_h * ($width/ $src_w);
		}
	}
	elseif (($width==0) && ($height!=0))
	{
		if ($ne != 0)
		{
			($src_h < $height) ? $out_h = $src_h : $out_h = $height;
			$out_w = $src_w;
		}
		else
		{
			$out_w = $src_w * ($height / $src_h);
			$out_h = $height;
		}
	}
	else
	{
		// no width and height params was passed, so no resizing needed
		showError('Invalid parameters: No width ("w") or height ("h") was provided');
		die();
	}
	
	$img = imagecreatetruecolor($out_w, $out_h);
	
	if ($p==1)
	{
		// down from here code can be optimized with simple arithmetic operations, POSSIBLY
		// 2do: try simple optimization! but current code is NOT damn!!!
		$kw = $out_w/$src_w;
		$kh = $out_h/$src_h;
		
		if ($kw >= $kh)
		{
			$reservspace = $src_h-($src_h / ($kw/$kh));
			$h_shift = round(($reservspace/100)*20);
			imagecopyresampled($img, $src, 0, 0, 0, $h_shift, $out_w, $out_h, $src_w, $out_h*(1/$kw));
		}
		else
		{
			$reservspace = $src_w-($src_w / ($kh/$kw));
			$w_shift = round(($reservspace/100)*20);
			imagecopyresampled($img, $src, 0, 0, $w_shift, 0, $out_w, $out_h, $out_w*(1/$kh), $src_h );
		}
	}
	elseif ($p==2)
	{
		$kw = $out_w/$src_w;
		$kh = $out_h/$src_h;
		$k = $kh/$kw;
		if($k<1) // высота больше ширины
		{
			//imagecopyresampled($img, $src, 0, 0, 0, 0, $out_w/$k, $out_h, $src_w, $src_h );
			$img = imagecreatetruecolor($src_w*($out_h/$src_h), $out_h);
			imagecopyresampled($img, $src, 0, 0, 0, 0, $src_w*($out_h/$src_h), $out_h, $src_w, $src_h );
		}
		elseif ($k>1)
		{
			$img = imagecreatetruecolor($out_w, $src_h*($out_w/$src_w));
			imagecopyresampled($img, $src, 0, 0, 0, 0, $out_w, $src_h*($out_w/$src_w), $src_w, $src_h );
		}
		else
		{
			imagecopyresampled($img, $src, 0, 0, 0, 0, $out_w, $out_h, $src_w, $src_h);
		}
	}
	else
	{
		imagecopyresampled($img, $src, 0, 0, 0, 0, $out_w, $out_h, $src_w, $src_h);
	}
	
	if ($watermarkSrc != '')
	{
		$wmFN = getImageFileName($watermarkSrc);
		$wmType = getImageType($wmFN);
		$wmSrc = imagecreatefrom($watermarkSrc,$wmType);
		$wmSrc_w = imagesx($wmSrc);
		$wmSrc_h = imagesy($wmSrc);
		//if ( ($wmSrc_w < ($out_w / 3)) or ($wmSrc_h < ($out_h / 3)) )
		if (($drawWatermarkIfPicWidthGT <= $out_w) and ($drawWatermarkIfPicHeightGT <= $out_h))
		{
			$img_w = imagesx($img);
			$img_h = imagesy($img);
			switch ($watermarkPosition)
			{
				case 5: # center
					$x = round(($img_w-$wmSrc_w)/2);
					$y = round(($img_h-$wmSrc_h)/2);
					imagecopyresampled($img, $wmSrc, $x, $y, 0, 0, $wmSrc_w, $wmSrc_h, $wmSrc_w, $wmSrc_h);
					break;
				default: # like 9
					imagecopyresampled($img, $wmSrc, $img_w-$wmSrc_w, $img_h-$wmSrc_h, 0, 0, $wmSrc_w, $wmSrc_h, $wmSrc_w, $wmSrc_h);
					break;
			}
			//imagecopymerge($img, $wmSrc, $img_w-$wmSrc_w, $img_h-$wmSrc_h, 0, 0, $wmSrc_w, $wmSrc_h, 50); // 50 is alpha!
		}
	}
	
	image($img,$destination,$type);
}

//**************************************************************************
# Run
if(isset($_GET['w'])==true){$width=(int)$_GET['w'];} else $width=0;
if(isset($_GET['h'])==true){$height=(int)$_GET['h'];} else $height=0;
if(isset($_GET['p'])){$p=(int)$_GET['p'];} else $p=0;
if(isset($_GET['ne'])){$ne=(int)$_GET['ne'];} else $ne=0;
if(isset($_GET['wm'])){$wm=(string)$_GET['wm'];} else $wm='';		// watermark
if(isset($_GET['wmp'])){$wmp=(string)$_GET['wmp'];} else $wmp=9;	// watermark position
if(isset($_GET['wmifwgt'])){$wmifwgt=(string)$_GET['wmifwgt'];} else $wmifwgt=320;	// watermark drawWatermarkIfPicWidthGreatThen
if(isset($_GET['wmifhgt'])){$wmifwgt=(string)$_GET['wmifhgt'];} else $wmifhgt=240;	// watermark drawWatermarkIfPicHeightGreatThen
//if(isset($_GET['wma'])){$wma=(int)$_GET['wma'];} else $wma=50;	// watermark alpha value

if (isset($_GET['pic'])==true) {
	$path="../".(string)$_GET['pic'];
	$fn=getImageFileBaseName($path);
	$type=getImageType($path);
	if($type == false) { echo 'Access denied: requested file is not an image!'; die(0); }
	else {
        $sw='';
        $sh='';
        $extension=getImageFileExtension($path);
		if($width>0 || $height>0) {
			if ($width>0) $sw='-w'.$width;
			if ($height>0) $sh='-h'.$height;
			//$cacheFileName=md5($path.$width.$height.(string)$p).'.cache'; // .img
            $hashPart = substr(md5($path.$width.$height.(string)$p), 0, 8);
			$cacheFileName=$fn.'.'.$hashPart.'.'.$extension;
			$cachedImagePath=IMAGER_CACHE_PATH.$cacheFileName;
			if (!file_exists($cachedImagePath)) resizeImage($width,$height,$path,$cachedImagePath,$p, $ne, $wm, $wmp, $wmifwgt, $wmifhgt);
			$path=$cachedImagePath;
		}
		
		# отдаем картинку
        //$fn=basename($fn).$sw.$sh.'.'.$extension; // native basename() function is not works correctly with unicode names!
        $fn=$fn.$sw.$sh.'.'.$extension;
		header('Content-Type: '.image_type_to_mime_type($type));
        header('Content-Length: '.filesize($path));
		header('Content-Disposition: filename="'.$fn.'"');
		//header('Content-Disposition: attachment; filename="'.$fn.'"');
		echo getImage($path);
		
		# подчищаем кеш
		deleteOldCache();
	}
}
?>