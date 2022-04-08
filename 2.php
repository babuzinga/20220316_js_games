<?php
const SEP = '_';
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

/*$map = [
  [1,1,1,1,1,1,1],
  [1,1,0,0,1,1,1],
  [1,1,1,0,1,1,1],
  [1,1,1,1,1,1,1],
  [1,1,1,1,1,1,1],
];
$start    = [1,2];
$end      = [5,2];*/


list($result, $openList, $routeList) = searchWay($map, $start[0], $start[1], $end[0], $end[1]);
printMap($map, $openList, $routeList, $start, $end);

/**
 * @param $map
 * @param $x_current        - координаты точки относительно которой ищется маршрут
 * @param $y_current
 * @param $x_end            - конечная точка маршрута
 * @param $y_end
 * @param array $openList   - список весов координат
 * @param array $routeList  - маршрутный лист
 * @param array $backList   - список точек отката
 * @param int $step
 * @return array
 */
function searchWay($map, $x_current, $y_current, $x_end, $y_end, $openList = [], $routeList = [], $backList = [], $step = 1) {
  if ($x_current == $x_end && $y_current == $y_end) return [true, $openList, $routeList];

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

  $openListCurrent = [];
  $current_alias = ca($x_current, $y_current);
  // Расчет весов соседних клеток по $routes направлениям
  foreach ($routes as $route) {
    $x_d = $x_current + $route[0];
    $y_d = $y_current + $route[1];
    $alias = ca($x_d, $y_d);

    if (inRouteList($alias, $routeList)) continue;

    // Если по вычеслинным координатам существует клетка и она доступна
    // будет расчитан её вес и все значения поместятся в "открытый" лист
    if ( isset($map[$y_d][$x_d]) && $map[$y_d][$x_d] == 1 ) {
      // вес1 - длинна пути родмителя (если такой имеется)
      $heft1_parent = !empty($openList[$current_alias]) ? $openList[$current_alias]['heft_1'] : 0;
      // вес1 - длинна пути (прописаны заранее)
      $heft1 = $route[2] + $heft1_parent;
      // вес2 - ивристическое приближение - количество клеток до цели (расчитывается)
      $heft2 = (abs($x_end - $x_d) + abs($y_end - $y_d)) * 10;

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
        ( empty($openList[$alias]) || $openList[$alias]['heft_1'] >= $heft1 )
          ? $data
          : $openList[$alias]
      ;
    }
  }

  // Из получившегося набора, вибирается клетка с минимальным весом
  $items_min_heft_alias = getMinHeft($openListCurrent);
  // Если количество блоков с минимальным весом несколько - извлекаем один,
  // а остальные помещаем в лист возврата (используется при откате назад, если путь зайдет в тупик)
  if (!empty($items_min_heft_alias) && count($items_min_heft_alias) > 1) {
    $item_min_heft_alias = array_pop($items_min_heft_alias);
    $backList = array_merge($backList, $items_min_heft_alias);
  } elseif (!empty($items_min_heft_alias)) {
    $item_min_heft_alias = $items_min_heft_alias[0];
  } else {
    $item_min_heft_alias = false;
  }

  // Удаление элемента из листа возврата
  $backList = removeInBackList($item_min_heft_alias, $backList);

  // Если не удалось установить элемент с минимальным весом - значит путь
  // вошел в тупик, необходимо откатится к стартовой точке
  if (empty($item_min_heft_alias)) {
    //$back_to = !empty($backList) ? array_pop($backList) : $routeList[1]['from'];
    $back_to = $routeList[1]['from'];
    $c = explode(SEP, $back_to);
    $item = [
      'x' => $c[0],
      'y' => $c[1],
    ];
  } else {
    $item = $openListCurrent[$item_min_heft_alias];
  }

  // В СтопЛист заносится информация о переходах
  $routeList[$step] = [
    'from'  => ca($x_current, $y_current),
    'to'    => ca($item['x'], $item['y']),
  ];

  $openList = mergeOpenList($openList, $openListCurrent);

  if (!empty($_GET['s']) && $step == (int)$_GET['s']) { echo $current_alias; return [false, $openList, $routeList]; };

  list($result, $openList, $routeList) = searchWay($map, $item['x'], $item['y'], $x_end, $y_end, $openList, $routeList, $backList, $step + 1);

  return [$result, $openList, $routeList];
}

function getMinHeft($items) {
  if (empty($items) || !is_array($items)) return;
  $min_items = [];
  $first_item = current($items);
  $min_heft = $first_item['heft_t'];
  foreach ($items as $key => $item) {
    if ($item['heft_t'] < $min_heft) {
      $min_heft = $item['heft_t'];
      $min_items = [$key];
    } elseif ($item['heft_t'] == $min_heft) {
      $min_items[] = $key;
    }
  }

  return $min_items;
}

function inRouteList($alias, $routeList) {
  if (empty($routeList) || !is_array($routeList)) return false;
  foreach ($routeList as $step => $item)
    if ($item['from'] == $alias) return $step;

  return false;
}

function removeInBackList($value, $backList) {
  $backList_new = [];
  foreach ($backList as $item)
    if ($item != $value)
      $backList_new[] = $item;

  return $backList_new;
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

function printMap($map, $openList, $routeList, $start, $end) {
  echo '<style>
    h2 {margin:0}
    td {text-align:center;font-size:12px} 
    td.red {color:red;font-weight:bold}
    td.green {color:green}
    td.blue {color:blue}
    td.step {background:#d0f1d0}
    td > table td {width:22px; height:22px}
    .d'.ca($start[0], $start[1]). ' {background:#7de1f3 !important;} 
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
      $cent     = !empty($openList[$alias]) ? '['.$openList[$alias]['cent'].']' : '';
      $d_step   = inRouteList($alias, $routeList) ? 'step' : '';

      echo "
<td class='d{$alias} v{$value} {$d_step}'>
<table>
<tr><td class='red'>{$d_heft_t}</td><td>{$cent}</td><td>{$alias}</td></tr>
<tr><td></td><td><h2>{$value}</h2></td><td></td></tr>
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
  <button type="submit" name="s" value="1">RESET</button>
  <button type="submit" name="s" value="' . ( !empty($_GET['s']) ? $_GET['s'] + 1 : 1 ) . '"> >>> </button>
</form>
  ';
}
