<?php

function trace($message = '', $skipCalls = 1, $format = 'html') {
    switch ($format) {
        case 'txt': echo getTraceTxt($message, $skipCalls); break;
        default: echo getTraceHtml($message, $skipCalls); break;
    }
}

function debugCallToStr($call) {
    $callsCopy = Array();
    foreach($call['args'] as $key => $value) {
        if (is_array($value)) $callsCopy[$key] = 'Array('.json_encode($value).')';
        elseif (is_object($value)) $callsCopy[$key] = 'Object('.json_encode($value).')';
        elseif (is_bool($value)) $callsCopy[$key] = $value ? 'true' : 'false';
        elseif (is_null($value)) $callsCopy[$key] = 'null';
        elseif (is_string($value)) $callsCopy[$key] = '\''.$value.'\'';
        else $callsCopy[$key] = (string)$value;
    }
    return $call['function'].'('.implode(', ', $callsCopy).');';
}

function getTraceHtml($message = '', $skipCalls = 1) {
    $result = '';
    if (!empty($message)) $result .= $message.'<br/>';
    $calls = debug_backtrace();
    $l = count($calls);
    $result .= '<table border="0">';
    
    for ($i = $l-1; $i > $skipCalls-1; $i--) { // not >= 0, cause $calls[0] is this function (trace())! we don't need to output this!
        $result .= '<tr>';
        $call = $calls[$i];
        $callNumber = $l-$i;
        if ($callNumber < 10) $callNumber = '0'.$callNumber;
        $s = '';
        $result .= '<td style="color:grey;">'.$callNumber.'&nbsp;</td>';
        if (!empty($call['class'])) $s .= $call['class'];
        if (!empty($call['type'])) $s .= $call['type'];
        $s .= debugCallToStr($call);
        $file = !empty($call['file']) ? $call['file'] : '?';
        $line = !empty($call['line']) ? $call['line'] : '?';
        $result .= '<td>'.$s.'</td><td style="color:grey;">&nbsp;'.$file.':'.$line.'</td>';
        $result .= '</tr>';
    }
    $result .= '</table>';
    
    return $result;
}

function getTraceTxt($message = '', $skipCalls = 1, $traceLineBreak = PHP_EOL, $sourceInfoXPosition = 60) {
    $result = '';
    if (!empty($message)) $result .= $message.$traceLineBreak;
    $calls = debug_backtrace();
    $l = count($calls);
    
    for ($i = $l-1; $i > $skipCalls-1; $i--) { // not >= 0, cause $calls[0] is this function (trace())! we don't need to output this!
        $call = $calls[$i];
        $callNumber = $l-$i;
        if ($callNumber < 10) $callNumber = '0'.$callNumber;
        $s = $callNumber.":\t";
        if (!empty($call['class'])) $s .= $call['class'];
        if (!empty($call['type'])) $s .= $call['type'];
        $s .= debugCallToStr($call);
        $file = !empty($call['file']) ? $call['file'] : '?';
        $line = !empty($call['line']) ? $call['line'] : '?';
        $slen = strlen($s);
        while ($slen < $sourceInfoXPosition) {
            $s .= ' ';
            $slen++;
        }
        $s = $s.$file.':'.$line;
        $result .= $s.$traceLineBreak;
    }
    
    return $result;
}

function dump($expression) {
    var_dump($expression);
}