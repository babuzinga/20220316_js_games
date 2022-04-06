<?php

function print_array($array) { echo '<pre>', print_r($array, 1), '</pre>'; }

$map = [
 [1,1,0,1,1,1],
 [1,1,1,1,1,1],
 [1,1,1,1,1,1],
 [1,1,1,1,1,1],
];

echo '<table border="1">';
foreach ($map as $i => $row) {
  echo '<tr>';
  foreach ($row as $j => $col) {
    echo "<td style='text-align: center;padding: 2px 20px'>{$j}_{$i}<h2>{$map[$i][$j]}</h2></td>";
  }
  echo '</tr>';
}
echo '</table><br>';














/*
 * 1 - параметр - перебор по оси X
 * 2 - параметр - перебор по оси Y
 * 3 - параметр - смещение по оси X
 * 4 - параметр - смещение по оси Y
 */
$methods = [
  ['x' =>  1, 'y' =>  0, 'x_n' => 0, 'y_n' => 1,],  // RIGHT
  ['x' =>  0, 'y' =>  1, 'x_n' => 1, 'y_n' => 0,],  // DOWN
  ['x' => -1, 'y' =>  0, 'x_n' => 0, 'y_n' => 1,],  // LEFT
  ['x' =>  0, 'y' => -1, 'x_n' => 1, 'y_n' => 0,],  // UP;
];
$graph = [];
foreach ($methods as $method)
  $graph = scanner($method, 1, 1, false, false, $graph);

printGraph($graph);


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
  // Координаты узла A, по которому будет происходить проверка его "соседей"
  $scan_x = $scan_x === false ? ($method['x_n'] === 0 ? $base_x : 0) : $scan_x;
  $scan_y = $scan_y === false ? ($method['y_n'] === 0 ? $base_y : 0) : $scan_y;
  // По сформированным координатам, извлекается значение узла (A)
  $alias_node = getNode($scan_x, $scan_y);
  // Если у проверяемого узла, по выбранному направлению ($method) есть "сосед" (B) ...
  if ($node_next = getNode($scan_x + $method['x'], $scan_y + $method['y'])) {
    // ... то "сосед" помечается в качестве узла к которому можно перейти (A -> B)
    $arr[$alias_node][] = $node_next;
    $arr = scanner($method, 1, 1, $scan_x + $method['x'], $scan_y + $method['y'], $arr);
    // Если по направлениею закончились узлы, но в соседнем ряду по направлению они есть ...
  } elseif (getNode($scan_x + $method['x_n'], $scan_y + $method['y_n'])) {
    // ... осуществляет переход к следующему ряду (строка или столбец - в зависимости от направления)
    $scan_x_next = $method['x_n'] === 0 ? false : $scan_x + $method['x_n'];
    $scan_y_next = $method['y_n'] === 0 ? false : $scan_y + $method['y_n'];
    $arr = scanner($method, 1, 1, $scan_x_next, $scan_y_next, $arr);
  }

  if (!empty($alias_node) && empty($arr[$alias_node])) $arr[$alias_node][] = 'NULL';

  return $arr;
}

/**
 * @param $x
 * @param $y
 * @param bool $return_value
 * @return bool|string
 */
function getNode($x, $y, $return_value = false) {
  global $map;
  return isset($map[$y][$x]) ? ($return_value === false ? $x.'_'.$y : $map[$y][$x]) : false;
}

function printGraph($array) {
  if (empty($array)) return;

  foreach ($array as $k => $row) {
    echo "$k => ";
    foreach ($row as $item) echo "$item, ";
    echo "<br>";
  }

}