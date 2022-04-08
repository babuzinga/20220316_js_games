<?php
const SEP = '_';
$map = [
  [1,1,1,1,1,1,1,1,1,1,1,1],
  [1,1,1,0,1,1,1,1,1,1,1,1],
  [1,1,1,0,0,0,0,0,1,1,1,1],
  [1,1,1,0,1,0,1,1,1,1,1,1],
  [1,1,1,1,1,0,1,0,0,0,1,1],
  [1,1,1,0,1,0,1,1,1,1,1,1],
  [1,1,1,0,1,0,1,1,1,1,1,1],
  [1,1,1,0,1,0,1,1,1,1,1,1],
  [1,1,1,0,1,0,1,1,1,1,1,1],
  [1,1,1,1,1,1,1,1,1,1,1,1],
];
$start    = [2,9];
$end      = [9,6];



$openList = searchWay($map, $start, $end);
$routes = [];

if (!empty($openList['status'])) {
  $start2 = ca($start[0], $start[1]);
  $end2   = ca($end[0], $end[1]);
  $step   = 0;
  while ($end2 != $start2) {
    $routes[] = $end2;
    $end2 = $openList[$end2]['cent'];
  }
}

printMap($map, $openList, $routes, $start, $end);
//printArray($routes);
//printArray($openList);

/**
 * Алгоритм поиска по первому наилучшему совпадению на графе,
 * который находит маршрут с наименьшей стоимостью от одной
 * вершины (начальной) к другой (целевой, конечной).
 *
 * https://ru.wikipedia.org/wiki/A*
 *
 * @param $map
 * @param $start
 * @param $end
 * @param array $openList
 * @param int $step
 * @return array
 */
function searchWay($map, $start, $end, $openList = [], $step = 1) {
  if ($start == $end) { $openList['status'] = true; return $openList; }



  $current_alias = ca($start[0], $start[1]);
  $item_min = $min_heft = false;
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



  // Расчет весов соседних клеток по $routes направлениям
  $openListCurrent = [];
  foreach ($routes as $route) {
    $x_d = $start[0] + $route[0];
    $y_d = $start[1] + $route[1];
    $alias = ca($x_d, $y_d);

    if (inOpenList($alias, $openList)) continue;

    // Если по вычеслинным координатам существует клетка и она доступна
    // будет расчитан её вес и все значения поместятся в "открытый" лист
    if ( isset($map[$y_d][$x_d]) && $map[$y_d][$x_d] == 1 ) {
      // вес1.1 - длинна пути родмителя (если такой имеется)
      $heft1_parent = !empty($openList[$current_alias]) ? $openList[$current_alias]['heft_1'] : 0;
      // вес1.2 - длинна пути (прописаны заранее)
      $heft1 = $route[2] + $heft1_parent;
      // вес2 - ивристическое приближение - количество клеток до цели (расчитывается)
      $heft2 = (abs($end[0] - $x_d) + abs($end[1] - $y_d)) * 10;
      // Формирование основного набора данных
      $data = [
        'x' => $x_d,
        'y' => $y_d,
        'heft_1' => $heft1,
        'heft_2' => $heft2,
        'heft_t' => $heft1 + $heft2,
        'cent'   => $current_alias,
      ];



      // если ранее значение высчитывалось и оно меньше, оставляем его без изменения
      $openListCurrent[$alias] =
        ( empty($openList[$alias]) || $openList[$alias]['heft_1'] > $heft1 )
          ? $data
          : $openList[$alias]
      ;

      // Определение ячейки с минимальным весом
      if (empty($min_heft) || $openListCurrent[$alias]['heft_t'] <= $min_heft) {
        $min_heft = $openListCurrent[$alias]['heft_t'];
        $item_min = $alias;
      }
    }
  }



  // Если не удалось установить элемент с минимальным весом - значит путь
  // вошел в тупик, необходимо откатится к стартовой точке
  if (empty($item_min)) {
    global $start;
    $item_next = $start;
  } else {
    $openListCurrent[$item_min]['step'] = $step;
    $openListCurrent[$item_min]['from'] = $current_alias;
    $item_next = [$openListCurrent[$item_min]['x'], $openListCurrent[$item_min]['y']];
  }



  // Слияние глобального и текущего "открытого" листа
  $openList = mergeOpenList($openList, $openListCurrent);

  if (!empty($_GET['s']) && $step == (int)$_GET['s']) { return $openList; };

  $openList = searchWay($map, $item_next, $end, $openList, $step + 1);

  return $openList;
}

function inOpenList($alias, $openList) {
  if (empty($openList) || !is_array($openList)) return false;
  foreach ($openList as $item)
    if ($item['from'] == $alias) return $item['step'];

  return false;
}

function mergeOpenList($openList, $openListCurrent) {
  if (empty($openListCurrent) || !is_array($openListCurrent)) return $openList;
  foreach ($openListCurrent as $key => $item) {
    $openList[$key] = $item;
  }

  return $openList;
}

function ca($x, $y) {
  return $x.SEP.$y;
}
















function printArray($array) { echo '<pre>', print_r($array, 1), '</pre>'; }

function printMap($map, $openList, $routes, $start, $end) {
  echo '<style>
    h2 {margin:0}
    td {text-align:center;font-size:12px} 
    td.red {color:red;font-weight:bold}
    td.green {color:green}
    td.blue {color:blue}
    td.step {background:#d0f1d0}
    td.step1 {background:#97e097}
    td > table td {width:22px; height:22px}
    .d' .ca($start[0], $start[1]). ' {background:#7de1f3 !important;} 
    .v0 {background:#585858} 
    .d' .ca($end[0], $end[1]).' {background:#e8d6a7 !important;}
    
    </style>
  
  <table border="1">';
  foreach ($map as $i => $row) {
    echo '<tr>';
    foreach ($row as $j => $col) {
      $alias    = ca($j, $i);
      $value    = $map[$i][$j];
      $d_heft_t = !empty($openList[$alias]) ? $openList[$alias]['heft_t'] : '';
      $d_heft_1 = !empty($openList[$alias]) ? $openList[$alias]['heft_1'] : '';
      $d_heft_2 = !empty($openList[$alias]) ? $openList[$alias]['heft_2'] : '';
      $cent     = !empty($openList[$alias]) ? $openList[$alias]['cent'] : '';
      $d_step   = !empty($openList[$alias]['from']) ? 'step' : '';
      $d_step1  = in_array($alias, $routes) ? 'step1' : '';

      echo "
<td class='d{$alias} v{$value} {$d_step} {$d_step1}'>
<table>
<tr><td class='red'>{$d_heft_t}</td><td></td><td>{$alias}</td></tr>
<tr><td colspan='3'><h2>{$cent} [{$value}]</h2></td></tr>
<tr><td class='green'>{$d_heft_1}</td><td></td><td class='blue'>{$d_heft_2}</td></tr>
</table>
</td>
";
    }

    echo '</tr>';
  }
  echo '</table><br>';

  echo '
<form action="/2.php" method="get">
  <button type="submit" name="s" value="' . ( !empty($_GET['s']) ? $_GET['s'] - 1 : 0 ) . '"> <<< </button>
  <button type="submit">RESET</button>
  <button type="submit" name="s" value="' . ( !empty($_GET['s']) ? $_GET['s'] + 1 : 1 ) . '"> >>> </button>
</form>
  ';
}
