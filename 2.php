<?php

$map = [
  [1,1,1,1,1,1,1,1,1,1],
  [1,1,1,0,1,1,1,1,1,1],
  [1,1,1,0,0,0,0,0,1,1],
  [1,1,1,0,1,0,1,1,1,1],
  [1,1,1,1,1,0,1,0,0,0],
  [1,1,1,0,1,0,1,1,1,1],
  [1,1,1,0,1,0,1,1,1,1],
];
$start    = [1,4];
$end      = [9,6];
list($result, $openList, $stopList) = searchWay(
  $map,
  $start[0],
  $start[1],
  $start[0],
  $start[1],
  $end[0],
  $end[1],
  [],
  [ca($start[0], $start[1])],
  0
);

printMap($map, $openList, $stopList, $start, $end);

function searchWay($map, $x_start, $y_start, $x_center, $y_center, $x_end, $y_end, $openList, $stopList, $step) {
  if ($x_center == $x_end && $y_center == $y_end) return [true, $openList, $stopList];

  $routes = [
    [-1,  1, 14], // ЮЗ
    [-1,  0, 10], // З
    [-1, -1, 14], // СЗ
    [ 0, -1, 10], // С
    [ 1, -1, 14], // СВ
    [ 1,  0, 10], // В
    [ 1,  1, 14], // ЮВ
    [ 0,  1, 10], // Ю
  ];

  $openList = [];
  // Расчет весов соседних клеток по $routes направлениям
  foreach ($routes as $route) {
    $x_d = $x_center + $route[0];
    $y_d = $y_center + $route[1];
    $alias = ca($x_d, $y_d);

    if (in_array($alias, $stopList)) continue;

    // Если по вычеслинным координатам существует клетка и она не является стратовой
    // будет расчитан её вес и все значения поместятся в "открытый" лист
    if ( isset($map[$y_d][$x_d]) && $map[$y_d][$x_d] == 1 ) {
      // вес1 - длинна пути (прописаны заранее)
      $heft1 = $route[2];
      // вес2 - ивристическое приближение - количество клеток до цели (расчитывается)
      $heft2 = (abs($x_end - $x_d) + abs($y_end - $y_d)) * 10;

      $data = [
        'x' => $x_d,
        'y' => $y_d,
        'heft_1' => $heft1,
        'heft_2' => $heft2,
        'heft_t' => $heft1 + $heft2,
      ];
      $openList[$alias] = $data;
    }
  }

  // Из получившегося набора, вибирается клетка с минимальным весом
  $item_min_heft_alias = getMinHeft($openList);
  $openList[$item_min_heft_alias]['step'] = 1;

  $item = $openList[$item_min_heft_alias];
  $stopList[] = ca($item['x'], $item['y']);

  //if ($step == 1) return [false, $openList, $stopList];

  list($result, $openList, $stopList) = searchWay($map, $x_start, $y_start, $item['x'], $item['y'], $x_end, $y_end, $openList, $stopList, $step + 1);

  return [$result, $openList, $stopList];
}

function getMinHeft($items) {
  if (empty($items) || !is_array($items)) return;
  $min_item = current($items);
  $min_heft = $min_item['heft_t'];
  foreach ($items as $key => $item) {
    if ($item['heft_t'] <= $min_heft) {
      $min_heft = $item['heft_t'];
      $min_item = $key;
    }
  }

  return $min_item;
}

function ca($x, $y) {
  return "{$x}_{$y}";
}









function printArray($array) { echo '<pre>', print_r($array, 1), '</pre>'; }

function printMap($map, $data, $stopList, $start, $end) {
  echo '<style>
    h2 {margin:0}
    td {text-align:center;} 
    td.red {color:red;font-weight:bold}
    td.green {color:green}
    td.blue {color:blue}
    td.step {background:#d0f1d0}
    td > table td {width:20px; height:20px}
    .d'.ca($start[0], $start[1]).' {background:#7de1f3 !important;} 
    .v0 {background:#b1b1b1} 
    .d'.ca($end[0], $end[1]).' {background:#e8d6a7 !important;}
    
    </style>
  
  <table border="1">';
  foreach ($map as $i => $row) {
    echo '<tr>';
    foreach ($row as $j => $col) {
      $alias    = ca($j, $i);
      $value    = $map[$i][$j];
      $d_heft_t = !empty($data[$alias]) ? $data[$alias]['heft_t'] : '';
      $d_heft_1 = !empty($data[$alias]) ? $data[$alias]['heft_1'] : '';
      $d_heft_2 = !empty($data[$alias]) ? $data[$alias]['heft_2'] : '';
      $d_step   = is_array($stopList) && in_array($alias, $stopList) ? 'step' : '';

      echo "
<td class='d{$alias} v{$value} {$d_step}'>
<table>
<tr><td class='red'>{$d_heft_t}</td><td></td><td>{$alias}</td></tr>
<tr><td></td><td><h2>{$value}</h2></td><td></td></tr>
<tr><td class='green'>{$d_heft_1}</td><td></td><td class='blue'>{$d_heft_2}</td></tr>
</table>
</td>
";
    }

    echo '</tr>';
  }
  echo '</table><br>';
}
