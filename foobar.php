<?php

/**
 * output number from 1 to 100
 * Where the number is divisible by three (3) output the word “foo”
 * Where the number is divisible by five (5) output the word “bar”
 * Where the number is divisible by three (3) and (5) output the word “foobar”
 */
$output = [];
for ($i = 0; $i < 100; $i++) {
    $num = $i + 1;
    if ($num % 3) {
        $output[$i] = $num;
        if ($num % 5 == 0) {
            $output[$i] = "bar";
        }
    } else {
        $output[$i] = "foo";
        if ($num % 5 == 0) {
            $output[$i] .= "bar";
        }
    }
}
echo join(",", $output);
