--TEST--
numeric comparison binops
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('[10 > 0, 10 > 10, 10 > 20, 10 < 0, 10 < 10, 10 < 20]', '{}'),
    array('[10 >= 0, 10 >= 10, 10 >= 20, 10 <= 0, 10 <= 10, 10 <= 20]', '{}'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== [10 > 0, 10 > 10, 10 > 20, 10 < 0, 10 < 10, 10 < 20]
array(6) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
  [2]=>
  bool(false)
  [3]=>
  bool(false)
  [4]=>
  bool(false)
  [5]=>
  bool(true)
}
string(35) "[true,false,false,false,false,true]"
== [10 >= 0, 10 >= 10, 10 >= 20, 10 <= 0, 10 <= 10, 10 <= 20]
array(6) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(false)
  [3]=>
  bool(false)
  [4]=>
  bool(true)
  [5]=>
  bool(true)
}
string(33) "[true,true,false,false,true,true]"
