<?php

$data = [1, 3, 4, 6, 9 ,2, 3, 4, 5, 5, 7, 8, 9, 10, 1, 4, 5, 34, 23, 1, 4, 6, 77, 3, 9];

function average($data) {
    return array_sum($data) / count($data);
}

$more = [];
$less = [];

$average = average($data);

foreach ($data as $element) {
    $element < $average ? $less[] = $element : $more[] = $element;
}

echo average($more) . " " . average($less);