<?php

include '../arrayquery.php';

header('Content-Type: text/plain');

echo 'all:' . PHP_EOL;
echo json_encode(array_query([false, true, false])->all()) . PHP_EOL;
echo json_encode(array_query([true, true])->all()) . PHP_EOL;
echo json_encode(array_query([1, 2, 3])->all('$i => ($i % 2) === 1')) . PHP_EOL;
echo json_encode(array_query([1, 2, 3])->all('$i => $i > 0')) . PHP_EOL;
echo PHP_EOL;

echo 'any:' . PHP_EOL;
echo json_encode(array_query([false, true, false])->any()) . PHP_EOL;
echo json_encode(array_query([false, false])->any()) . PHP_EOL;
echo json_encode(array_query([1, 2, 3])->any('$i => ($i % 2) === 1')) . PHP_EOL;
echo json_encode(array_query([1, 2, 3])->any('$i => $i > 4')) . PHP_EOL;
echo PHP_EOL;

echo 'average:' . PHP_EOL;
echo array_query([1, 2, 3])->average() . PHP_EOL;
echo array_query([-1, 2, 3])->average() . PHP_EOL;
echo array_query([['index' => -1], ['index' => 3], ['index' => 4]])->average('$i => $i[\'index\']') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->average() . PHP_EOL;
echo PHP_EOL;

echo 'contains:' . PHP_EOL;
echo json_encode(array_query([1, 2])->contains(2)) . PHP_EOL;
echo PHP_EOL;

echo 'concat:' . PHP_EOL;
echo array_query([1, 2])->concat(array_query([3, 4])) . PHP_EOL;
echo PHP_EOL;

echo 'count:' . PHP_EOL;
echo array_query([1, 2, 3])->count() . PHP_EOL;
echo array_query([1, 2, 3])->count('$i => $i >= 2') . PHP_EOL;
echo PHP_EOL;

echo 'except:' . PHP_EOL;
echo array_query([1, 2])->except(array_query([2, 3])) . PHP_EOL;
echo PHP_EOL;

echo 'first:' . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->first() . PHP_EOL;
echo PHP_EOL;

echo 'groupby:' . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->groupby('$i => $i') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->groupby('$str => $str[1]') . PHP_EOL;
echo PHP_EOL;

echo 'intersect:' . PHP_EOL;
echo array_query([1, 2])->intersect(array_query([2, 3])) . PHP_EOL;
echo PHP_EOL;

echo 'join:' . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->join(
    array_query([['name' => 'house', 'pet' => 'cat'], ['name' => 'shed', 'pet' => 'rabbit'], ['name' => 'yard', 'pet' => 'dog']]),
    '$str => $str',
    '$h => $h[\'pet\']',
    function ($str, $h) { return ['animal' => $str, 'area' => $h['name']]; }) . PHP_EOL;
echo array_query([['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Eric'], ['id' => 3, 'name' => 'Matt']])->join(
    array_query([['id' => 1, 'type' => 'dog'], ['id' => 1, 'type' => 'fish'], ['id' => 3, 'type' => 'cat']]),
    '$person => $person["id"]',
    '$pet => $pet["id"]',
    '($person, $pet) => ["name" => $person["name"], "pet" => $pet["type"]]'
    ) . PHP_EOL;
echo PHP_EOL;

echo 'last:' . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->last() . PHP_EOL;
echo PHP_EOL;

echo 'max:' . PHP_EOL;
echo array_query([1, 2, 1, 3, 4, 3])->max() . PHP_EOL;
echo array_query([['index' => 1], ['index' => 2], ['index' => 4]])->max('$i => $i[\'index\']') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->max() . PHP_EOL;
echo array_query([0])->max() . PHP_EOL;
echo PHP_EOL;

echo 'min:' . PHP_EOL;
echo array_query([1, 2, 1, 3, 4, 3])->min() . PHP_EOL;
echo array_query([['index' => 1], ['index' => 2], ['index' => 4]])->min('$i => $i[\'index\']') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->min() . PHP_EOL;
echo array_query([0])->min() . PHP_EOL;
echo PHP_EOL;

echo 'orderby:' . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->orderby('$i => $i') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->orderby('$str => $str[strlen($str) - 1]') . PHP_EOL;
echo PHP_EOL;

echo 'orderbydesc:' . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->orderbydesc('$i => $i') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->orderbydesc('$str => $str[strlen($str) - 1]') . PHP_EOL;
echo PHP_EOL;

echo 'reverse:' . PHP_EOL;
echo array_query([1, 2, 3])->reverse() . PHP_EOL;
echo PHP_EOL;

echo 'select:' . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->select('$str => strtoupper($str)[0]') . PHP_EOL;
echo PHP_EOL;

echo 'single:' . PHP_EOL;
echo array_query([1])->single() . PHP_EOL;
echo array_query([1, 2, 3])->single('$i => $i === 2') . PHP_EOL;
echo json_encode(array_query([1, 2, 3])->single('$i => $i === \'cat\'')) . PHP_EOL;
echo PHP_EOL;

echo 'skip:' . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->skip(3) . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->skip(3, '$i => $i > 1') . PHP_EOL;
echo PHP_EOL;

echo 'sum:' . PHP_EOL;
echo array_query([1, 2, 1, 3, 4, 3])->sum() . PHP_EOL;
echo array_query([['index' => 1], ['index' => 2], ['index' => 4]])->sum('$i => $i[\'index\']') . PHP_EOL;
echo array_query(['dog', 'rabbit', 'cat', 'fish'])->sum() . PHP_EOL;
echo PHP_EOL;

echo 'take:' . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->take(3) . PHP_EOL;
echo array_query([1, 2, 1, 1, 3, 4, 3])->take(3, '$i => $i > 1') . PHP_EOL;
echo PHP_EOL;

echo 'where:' . PHP_EOL;
echo array_query([1, 2, 3, 4])->where(function ($i) { return ($i % 2) === 1; }) . PHP_EOL;
echo array_query([1, 2, 3, 4])->where('$i => ($i % 2) === 1') . PHP_EOL;
echo PHP_EOL;

?>