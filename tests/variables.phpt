--TEST--
variables
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('1 as $x | 2 as $y | [$x,$y,$x]', 'null'),
    array('[1,2,3][] as $x | [[4,5,6,7][$x]]', 'null'),
    array('42 as $x | . | . | . + 432 | $x + 1', '34324'),
    array('1 as $x | [$x,$x,$x as $x | $x]', 'null'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== 1 as $x | 2 as $y | [$x,$y,$x]
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(1)
}
string(7) "[1,2,1]"
== [1,2,3][] as $x | [[4,5,6,7][$x]]
array(3) {
  [0]=>
  array(1) {
    [0]=>
    int(5)
  }
  [1]=>
  array(1) {
    [0]=>
    int(6)
  }
  [2]=>
  array(1) {
    [0]=>
    int(7)
  }
}
array(3) {
  [0]=>
  string(3) "[5]"
  [1]=>
  string(3) "[6]"
  [2]=>
  string(3) "[7]"
}
== 42 as $x | . | . | . + 432 | $x + 1
int(43)
string(2) "43"
== 1 as $x | [$x,$x,$x as $x | $x]
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(1)
  [2]=>
  int(1)
}
string(7) "[1,1,1]"
