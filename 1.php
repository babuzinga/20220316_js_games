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



/*
 * 1 - параметр - перебор по оси X
 * 2 - параметр - перебор по оси Y
 * 3 - параметр - смещение по оси X
 * 4 - параметр - смещение по оси Y
 */
$methods = [
  // RIGHT
  [
    'x'   => 1,
    'y'   => 0,
    'x_n' => 0,
    'y_n' => 1,
  ],
  // DOWN
  [
    'x'   => 0,
    'y'   => 1,
    'x_n' => 1,
    'y_n' => 0,
  ],
  // LEFT
  [
    'x'   => -1,
    'y'   => 0,
    'x_n' => 0,
    'y_n' => 1,
  ],
  // UP
  [
    'x'   => 0,
    'y'   => -1,
    'x_n' => 1,
    'y_n' => 0,
  ],
];
$graph = [];
foreach ($methods as $method)
  $graph = scanner($method, 1, 1, false, false, $graph);

print_array($graph);


/**
 * @param $method
 * @param $base_x
 * @param $base_y
 * @param $scan_x
 * @param $scan_y
 * @param $arr
 * @return mixed
 */
function scanner($method, $base_x, $base_y, $scan_x, $scan_y, $arr) {
  $scan_x = $scan_x === false ? ($method['x_n'] === 0 ? $base_x : 0) : $scan_x;
  $scan_y = $scan_y === false ? ($method['y_n'] === 0 ? $base_y : 0) : $scan_y;

  $node = getNode($scan_x, $scan_y);

  if ($node_next = getNode($scan_x + $method['x'], $scan_y + $method['y'])) {
    $arr[$node][] = $node_next;
    $arr = scanner($method, 1, 1, $scan_x + $method['x'], $scan_y + $method['y'], $arr);
  } elseif (getNode($scan_x + $method['x_n'], $scan_y + $method['y_n'])) {
    $scan_x_go = $method['x_n'] === 0 ? false : $scan_x + $method['x_n'];
    $scan_y_go = $method['y_n'] === 0 ? false : $scan_y + $method['y_n'];
    $arr = scanner($method, 1, 1, $scan_x_go, $scan_y_go, $arr);
  }

  if (!empty($node) && empty($arr[$node])) $arr[$node][] = null;

  return $arr;
}

/**
 * @param $x
 * @param $y
 * @return bool|int|mixed
 */
function getNode($x, $y) {
  global $map;
  return !empty($map[$y][$x]) ? $map[$y][$x] : false;
}