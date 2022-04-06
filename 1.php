<?php

function print_array($array) { echo '<pre>', print_r($array, 1), '</pre>'; }

$map = [
 [1,  2,  3,  4,  ],
 [5,  6,  7,  8,  ],
 [9,  10, 11, 12, ],
 [13, 14, 15, 16, ],
];

echo '<table border="2">';
foreach ($map as $i => $row) {
  echo '<tr>';
  foreach ($row as $j => $col) {
    echo "<td>{$map[$i][$j]}</td>";
  }
  echo '</tr>';
}
echo '</table>';



// , 'left', 'down'

$m = [
  ['x' => 1,  'y' => 0,   'x_n' => false, 'y_n' => 1],
];
$graph = [];
foreach (['right', 'top'] as $direction)
  $graph = scanner($direction, 1, 1, false, false, $graph);

print_array($graph);

function scanner($direction, $base_x, $base_y, $scan_x, $scan_y, $arr) {
  switch ($direction) {
    case 'right':
      $scan_x = $scan_x === false ? $base_x : $scan_x;
      $scan_y = $scan_y === false ? 0       : $scan_y;
      $node = getNode($scan_x, $scan_y);

      if ($node_next = getNode($scan_x + 1, $scan_y)) {
        $arr[$node][] = $node_next;
        $arr = scanner($direction, 1, 1, $scan_x + 1, $scan_y, $arr);
      } elseif (getNode($scan_x, $scan_y + 1)) {
        $arr = scanner($direction, 1, 1, false, $scan_y + 1, $arr);
      }

      break;

    case 'top':
      $scan_x = $scan_x === false ? 0       : $scan_x;
      $scan_y = $scan_y === false ? $base_y : $scan_y;
      $node = getNode($scan_x, $scan_y);

      if ($node_next = getNode($scan_x, $scan_y + 1)) {
        $arr[$node][] = $node_next;
        $arr = scanner($direction, 1, 1, $scan_x, $scan_y + 1, $arr);
      } elseif (getNode($scan_x + 1, $scan_y)) {
        $arr = scanner($direction, 1, 1, $scan_x + 1, false, $arr);
      }

      break;
  }

  if (!empty($node) && empty($arr[$node])) $arr[$node][] = null;

  return $arr;
}

function getNode($x, $y) {
  global $map;
  return !empty($map[$y][$x]) ? $map[$y][$x] : false;
}