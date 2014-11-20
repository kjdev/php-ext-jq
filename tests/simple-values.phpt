--TEST--
simple value
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('true', 'null'),
    array('false', 'null'),
    array('null', '42'),
    array('1', 'null'),
    array('-1', 'null'),
    array('{}', 'null'),
    array('[]', 'null'),
    array('{x: -1}', 'null'),
    array('[.[]|tojson|fromjson]', '["foo", 1, ["a", 1, "b", 2, {"foo":"bar"}]]')
);
foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== true
bool(true)
string(4) "true"
== false
bool(false)
string(5) "false"
== null
NULL
string(4) "null"
== 1
int(1)
string(1) "1"
== -1
int(-1)
string(2) "-1"
== {}
array(0) {
}
string(2) "{}"
== []
array(0) {
}
string(2) "[]"
== {x: -1}
array(1) {
  ["x"]=>
  int(-1)
}
string(8) "{"x":-1}"
== [.[]|tojson|fromjson]
array(3) {
  [0]=>
  string(3) "foo"
  [1]=>
  int(1)
  [2]=>
  array(5) {
    [0]=>
    string(1) "a"
    [1]=>
    int(1)
    [2]=>
    string(1) "b"
    [3]=>
    int(2)
    [4]=>
    array(1) {
      ["foo"]=>
      string(3) "bar"
    }
  }
}
string(37) "["foo",1,["a",1,"b",2,{"foo":"bar"}]]"
