--TEST--
dictionary construction syntax
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('{a: 1}', 'null'),
    array('{a,b,(.d):.a,e:.b}', '{"a":1, "b":2, "c":3, "d":"c"}'),
    array('{"a",b,"a$\(1+1)"}', '{"a":1, "b":2, "c":3, "a$2":4}'),
);
foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== {a: 1}
array(1) {
  ["a"]=>
  int(1)
}
string(7) "{"a":1}"
== {a,b,(.d):.a,e:.b}
array(4) {
  ["a"]=>
  int(1)
  ["b"]=>
  int(2)
  ["c"]=>
  int(1)
  ["e"]=>
  int(2)
}
string(25) "{"a":1,"b":2,"c":1,"e":2}"
== {"a",b,"a$\(1+1)"}
array(3) {
  ["a"]=>
  int(1)
  ["b"]=>
  int(2)
  ["a$2"]=>
  int(4)
}
string(21) "{"a":1,"b":2,"a$2":4}"
