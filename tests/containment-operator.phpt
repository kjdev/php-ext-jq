--TEST--
containment operator
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('[("foo" | contains("foo")), ("foobar" | contains("foo")), ("foo" | contains("foobar"))]', '{}'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== [("foo" | contains("foo")), ("foobar" | contains("foo")), ("foo" | contains("foobar"))]
array(3) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(false)
}
string(17) "[true,true,false]"
