<?php

function formatFloat($value) { //http://stackoverflow.com/questions/22274437/php-format-any-float-as-a-decimal-expansion
    if ($value == 0.0)  return '0.0';
    $decimalDigits = max(13 - floor(log10(abs($value))), 0);
    $formatted = number_format($value, $decimalDigits);
    // Trim excess 0's
    $formatted = preg_replace('/(\.[0-9]+?)0*$/', '$1', $formatted);
    return $formatted;
}
