<?php
function getFilePermissions($file) {
    $perms = fileperms($file);

    if (($perms & 0xC000) == 0xC000) {
        // Socket
        $info = 's';
    } elseif (($perms & 0xA000) == 0xA000) {
        // Symbolic Link
        $info = 'l';
    } elseif (($perms & 0x8000) == 0x8000) {
        // Regular
        $info = '-';
    } elseif (($perms & 0x6000) == 0x6000) {
        // Block special
        $info = 'b';
    } elseif (($perms & 0x4000) == 0x4000) {
        // Directory
        $info = 'd';
    } elseif (($perms & 0x2000) == 0x2000) {
        // Character special
        $info = 'c';
    } elseif (($perms & 0x1000) == 0x1000) {
        // FIFO pipe
        $info = 'p';
    } else {
        // Unknown
        $info = 'u';
    }

    // Owner
    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ?
                (($perms & 0x0800) ? 's' : 'x' ) :
                (($perms & 0x0800) ? 'S' : '-'));

    // Group
    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ?
                (($perms & 0x0400) ? 's' : 'x' ) :
                (($perms & 0x0400) ? 'S' : '-'));

    // World
    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ?
                (($perms & 0x0200) ? 't' : 'x' ) :
                (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}

function getFilesList($path, $returnFullPath = true, $includeDirs = false, $addedPath = '') {
    $files = array();
    if ($path[strlen($path)-1] != '/') $path .= '/'; //добавляем слеш в конец если его нет
    if (!empty($addedPath) && ($addedPath[strlen($addedPath)-1] != '/')) $addedPath .= '/'; //добавляем слеш в конец если его нет
    $dirHandle = opendir($path.$addedPath);
    while (false !== ($file = readdir($dirHandle))) {
        if (($file != ".") && ($file != ".."))
        {
            $resFile = $path.$addedPath.$file;
            
            if (!is_dir($resFile) || $includeDirs) //если это не директория или надо включать директории в результирующий список
                $files[] = ($returnFullPath) ? $path.$addedPath.$file : $addedPath.$file;
            
            if (is_dir($resFile)) //если это директория, идем вглубь
                $files = array_merge($files, getFilesList($path, $returnFullPath, $includeDirs, $addedPath.$file));
        }
    }
    closedir($dirHandle);
    return $files;
}

function geCorrectFileName($fileName)
{
	//$FileName = preg_replace("/[\\'&\s#абвгдеёжзийклмнопрстуфхцчшщьыъэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯ]+/","_", $FileName);
    //include_once("libs/a.charset.php");
    //$FileName = charset_x_win($FileName);
	$fileName = preg_replace("/[\\'&\s@\":=#]+/","_", $fileName);
	return $fileName;
}

function geGetNewFilenameIfFileExists($fileName) {
    $c = 0;
    $currentFileName = $fileName;
    while (file_exists($currentFileName))
    {
        $cPos = strrpos($fileName, '.');
        $c++;
        $currentFileName = substr($fileName, 0, $cPos).$c.'.'.substr($fileName, $cPos+1);
    }
    return $currentFileName;
}

function geDeleteDir($directory) {
    $dir = opendir($directory); 
    while($file = readdir($dir)) {
        if (is_file($directory."/".$file)) { 
            unlink($directory."/".$file); 
        } elseif (is_dir($directory."/".$file) && $file !== "." && $file !=="..") { 
            geDeleteDir($directory."/".$file); 
        }
    }
    closedir($dir);
    rmdir($directory);
}
