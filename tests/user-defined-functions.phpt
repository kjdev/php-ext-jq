--TEST--
user-defined functions
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('def f: . + 1; def g: def g: . + 100; f | g | f; (f | g), g', '3.0'),
    array('def f: (1000,2000); f', '123412345'),
    array('def f(a;b;c;d;e;f): [a+1,b,c,d,e,f]; f(.[0];.[1];.[0];.[0];.[0];.[0])', '[1,2]'),
    array('([1,2] + [4,5])', '[1,2,3]'),
    array('true', '[1]'),
    array('null,1,null', '"hello"'),
    array('[1,2,3]', '[5,6]'),
    array('[.[]|floor]', '[-1.1,1.1,1.9]'),
    array('[.[]|sqrt]', '[4,9]'),
    array('(add / length) as $m | map((. - $m) as $d | $d * $d) | add / length | sqrt', '[2,4,4,4,5,5,7,9]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== def f: . + 1; def g: def g: . + 100; f | g | f; (f | g), g
array(2) {
  [0]=>
  int(106)
  [1]=>
  int(105)
}
array(2) {
  [0]=>
  string(3) "106"
  [1]=>
  string(3) "105"
}
== def f: (1000,2000); f
array(2) {
  [0]=>
  int(1000)
  [1]=>
  int(2000)
}
array(2) {
  [0]=>
  string(4) "1000"
  [1]=>
  string(4) "2000"
}
== def f(a;b;c;d;e;f): [a+1,b,c,d,e,f]; f(.[0];.[1];.[0];.[0];.[0];.[0])
array(6) {
  [0]=>
  int(2)
  [1]=>
  int(2)
  [2]=>
  int(1)
  [3]=>
  int(1)
  [4]=>
  int(1)
  [5]=>
  int(1)
}
string(13) "[2,2,1,1,1,1]"
== ([1,2] + [4,5])
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(4)
  [3]=>
  int(5)
}
string(9) "[1,2,4,5]"
== true
bool(true)
string(4) "true"
== null,1,null
array(3) {
  [0]=>
  NULL
  [1]=>
  int(1)
  [2]=>
  NULL
}
array(3) {
  [0]=>
  string(4) "null"
  [1]=>
  string(1) "1"
  [2]=>
  string(4) "null"
}
== [1,2,3]
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
}
string(7) "[1,2,3]"
== [.[]|floor]
array(3) {
  [0]=>
  int(-2)
  [1]=>
  int(1)
  [2]=>
  int(1)
}
string(8) "[-2,1,1]"
== [.[]|sqrt]
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(3)
}
string(5) "[2,3]"
== (add / length) as $m | map((. - $m) as $d | $d * $d) | add / length | sqrt
int(2)
string(1) "2"
