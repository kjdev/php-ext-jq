--TEST--
in/equality
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('[ 10 == 10, 10 != 10, 10 != 11, 10 == 11]', '{}'),
    array('["hello" == "hello", "hello" != "hello", "hello" == "world", "hello" != "world" ]', '{}'),
    array('[[1,2,3] == [1,2,3], [1,2,3] != [1,2,3], [1,2,3] == [4,5,6], [1,2,3] != [4,5,6]]', '{}'),
    array('[{"foo":42} == {"foo":42},{"foo":42} != {"foo":42}, {"foo":42} != {"bar":42}, {"foo":42} == {"bar":42}]', '{}'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== [ 10 == 10, 10 != 10, 10 != 11, 10 == 11]
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
}
string(23) "[true,false,true,false]"
== ["hello" == "hello", "hello" != "hello", "hello" == "world", "hello" != "world" ]
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
  [2]=>
  bool(false)
  [3]=>
  bool(true)
}
string(23) "[true,false,false,true]"
== [[1,2,3] == [1,2,3], [1,2,3] != [1,2,3], [1,2,3] == [4,5,6], [1,2,3] != [4,5,6]]
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
  [2]=>
  bool(false)
  [3]=>
  bool(true)
}
string(23) "[true,false,false,true]"
== [{"foo":42} == {"foo":42},{"foo":42} != {"foo":42}, {"foo":42} != {"bar":42}, {"foo":42} == {"bar":42}]
array(4) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
}
string(23) "[true,false,true,false]"
