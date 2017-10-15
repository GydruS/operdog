<?php
#################################
#  GydruS's PHP Funcs Library   #
#            "ge_csv"           #
#             v. 1.0            #
#           2009 08 24          #
#################################

#################################
# Initialization


#################################
# Functions

function geCSV_GetFirstVal($line, $c=';')
{
	$cl = strlen($c);
	$cPos = 0;
	$next = true;
	do
	{
		$cPos = strpos($line, $c, $cPos);
		if ($cPos === false) $next = false;
		if (substr($line, $cPos+$cl, $cl)!=$c) $next = false;
		else $cPos += $cl*2;
	}
	while ($next);
	if ($cPos !== false) return substr($line, 0, $cPos);
	else return $line;
}

function geCSV_CutFirstVal(&$line, $c=';', $allowCommaEscaping=true)
{
	$cl = strlen($c);
	$cPos = 0;
	do
	{
		$next = false;
		$cPos = strpos($line, $c, $cPos);
		if ($cPos !== false)
		{
			if ($allowCommaEscaping)
			{
				if (substr($line, $cPos+$cl, $cl)==$c)
				{
					$cPos += $cl*2;
					$next = true;
				}
			}
		}
	}
	while ($next);
	if ($cPos !== false)
	{
		$value = substr($line, 0, $cPos);
		$line = substr($line, $cPos+$cl, strlen($line)-$cPos-$cl);
	}
	else
	{
		$value = $line;
		$line = '';
	}
	return $value;
}

function geCSV_GetLastVal($line, $c=';', $allowCommaEscaping=true)
{
	$cl = strlen($c);
	$ll = strlen($line);
	$cPos = 0;
	$offset = 0;
	do
	{
		$next = false;
		$cPos = strrpos($line, $c, -$offset);
		if ($cPos !== false)
		{
			if ($allowCommaEscaping)
			{
				if (substr($line, $cPos-$cl, $cl)==$c)
				{
					$offset = $ll - $cPos + $cl*2;
					$next = true;
				}
			}
		}
	}
	while ($next);

	if ($cPos !== false)
	{
		return substr($line, $cPos+$cl);
	}
	else 
	{
		if($line == $c) return '';
		else return $line;
	}
}

function geCSV_CutLastVal(&$line, $c=';', $allowCommaEscaping=true)
{
	$cl = strlen($c);
	$ll = strlen($line);
	$lastVal = geCSV_GetLastVal($line, $c, $allowCommaEscaping);
	$lvl = strlen($lastVal);
	if ($lvl < $ll) $line = substr($line, 0, $ll-$lvl-$cl);
	else $line = '';
//	echo '$lastVal = '.$lastVal.'<br />';
	return $lastVal;
}

# Usage:
#	geCSV_ParseLine('val1;val2;val3','filed1;;field3',';');
#	will return Array ( [filed1] => val1 [field3] => val3 ) 
function geCSV_ParseLine($line, $fieldNames, $c=';', $Trim=true)
{
	//$line=iconv("windows-1251","UTF-8",$line);
	$res = array();
	do
	{
		$value = geCSV_CutFirstVal($line, $c);
		$field = geCSV_CutFirstVal($fieldNames, $c);
		if ($Trim) $value = trim($value);
		if ($field != '') $res[$field] = $value;
	}
	while ($line != '');
	return $res;
	//$cl = strlen($c);
	/*	
	do
	{
		$cPos = strpos($line, $c);
		if ($cPos === false) $cPos = strlen($line);
		$value = substr($line, 0, $cPos);
		$line = substr($line, $cPos+$cl, strlen($line)-$cPos-$cl);
		$cFNPos = strpos($fieldNames, $c);
		$field = substr($fieldNames, 0, $cFNPos);
		$fieldNames = substr($fieldNames, $cFNPos+$cl, strlen($fieldNames)-$cFNPos-$cl);
		if ($field != '') $res[$field] = $value;
		if(strpos($fieldNames, $c) === false) break;
	}
	while (strpos($line, $c) !== false);
	$value = geCSV_GetFirstVal($line, $c);
	$field = geCSV_GetFirstVal($fieldNames, $c);
	if ($field != '') $res[$field] = $value;
	return $res;*/
}

# Usage:
#	geCSV_ParsePairedLine('val1=1;val2;val3=qwa');
#	will return Array ( [val1] => 1 [val2] => '' [val3] => qwa )
#	if $recursive == true, 
function geCSV_ParsePairedLine($line, $recursive = false, $c=';', $e='=')
{
	$res = array();
	while (strlen($line)>0)
	{
		$pair = geCSV_CutFirstVal($line,$c);
		//echo 'pair = '.$pair.'       line = '.$line.'<br />';
		$varName = geCSV_CutFirstVal($pair,$e);
		//echo 'varName = '.$varName.'       pair = '.$pair.'<br />';
		$varValue = $pair;//$varValue = geCSV_GetLastVal($pair,$e);
		$varValue = str_replace($c.$c, $c, $varValue);
		$varValue = str_replace($e.$e, $e, $varValue);
		if ($recursive)
		{
			if (strpos($varValue, $c)!==false) $varValue = geCSV_ParsePairedLine($varValue, $recursive, $c, $e);
		}
		//echo 'varValue = '.$varValue.'       pair = '.$pair.'<br />';
		$res[$varName] = $varValue;
	}
	return $res;
}

function geCSV_PairedLineFromArray($arr, $c=';', $e='=', $skipEmptyValues=true)
{
	$res = '';
	foreach ($arr as $k => $v)
	{
		//echo $v.'<br />';
		//if (is_array($v)) $v = geCSV_PairedLineFromArray($arr, $c, $e, $skipEmptyValues);
		if (is_array($v)) $v = geCSV_PairedLineFromArray($v, $c, $e, $skipEmptyValues);
		//else
		//{
			if ($skipEmptyValues) { if ($v =='') continue; }
			$v = str_replace($e, $e.$e, $v);
			$v = str_replace($c, $c.$c, $v);
			$res .= $k.$e.$v.$c;
		//}
	}
	//echo $res.'<br />';
	return $res;
}

function geCSV_ProcessFile($fn, $fieldNames, $c=';')
{
	$res = array();
	$i = 0;
	$lines = file($fn);
	foreach ($lines as $line_num => $line) 
	{
		$res[$i] = geCSVParseLine($line,$fieldNames,$c);
		$i++;
	}
	return $res;
}

function geCSV_StringToArray($line, $c=';', $skipEmptyVals = false)
{
	$res = array();
	do
	{
		$value = geCSV_CutFirstVal($line, $c);
		if($skipEmptyVals == false)	$res[] = $value;
		else if ($value != '') $res[] = $value;
	}
	while ($line != '');	
	/*$cl = strlen($c);
	do
	{
		$cPos = strpos($line, $c);
		if ($cPos === false) $cPos = strlen($line);
		$value = substr($line, 0, $cPos);
		$line = substr($line, $cPos+$cl, strlen($line)-$cPos-$cl);
		if($skipEmptyVals == false)	$res[] = $value;
		else if ($value != '') $res[] = $value;
	}
	while (strpos($line, $c) !== false);
	$value = geCSV_GetFirstVal($line, $c);
	if($skipEmptyVals == false)	$res[] = $value;
	else if ($value != '') $res[] = $value;*/
	return $res;
}

function geCSV_ArrayToCSV($Arr, $c=';', $OutputTemplate=0, $InsertSpaces=true)
{
	$res = '';
	if (count($Arr))
	{
		# выводим список полей первой строкой
		foreach($Arr[0] as $k => $v) $res .= $k.$c;
		$res .= "\n";
		
		# выводим значения записей построчно
		foreach ($Arr as $k => $v)
		{
			foreach($v as $vk => $vv)
			{
				$res .= str_replace($c, $c.$c, $vv).$c;
				if ($InsertSpaces) $res .= " ";
			}
			$res .= "\n";
		}
	}
	return $res;
}

function geCSV_CSVToArray($CSV, $c=';', $OutputTemplate = 0)
{
	$res = array();
	$CSV = explode("\n", $CSV);
	if (count($CSV))
	{
		$fieldNames = $CSV[0];
		//echo $fieldNames.'<br />';
		foreach($CSV as $line_num => $line)
		{
			if ($line_num == 0) continue;
			else $res[] = geCSV_ParseLine($line,$fieldNames,$c);
		}
	}
	return $res;
}
