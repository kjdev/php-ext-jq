--TEST--
conditionals
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('[.[] | if .foo then "yep" else "nope" end]', '[{"foo":0},{"foo":1},{"foo":[]},{"foo":true},{"foo":false},{"foo":null},{"foo":"foo"},{}]'),
    array('[.[] | if .baz then "strange" elif .foo then "yep" else "nope" end]', '[{"foo":0},{"foo":1},{"foo":[]},{"foo":true},{"foo":false},{"foo":null},{"foo":"foo"},{}]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== [.[] | if .foo then "yep" else "nope" end]
array(8) {
  [0]=>
  string(3) "yep"
  [1]=>
  string(3) "yep"
  [2]=>
  string(3) "yep"
  [3]=>
  string(3) "yep"
  [4]=>
  string(4) "nope"
  [5]=>
  string(4) "nope"
  [6]=>
  string(3) "yep"
  [7]=>
  string(4) "nope"
}
string(52) "["yep","yep","yep","yep","nope","nope","yep","nope"]"
== [.[] | if .baz then "strange" elif .foo then "yep" else "nope" end]
array(8) {
  [0]=>
  string(3) "yep"
  [1]=>
  string(3) "yep"
  [2]=>
  string(3) "yep"
  [3]=>
  string(3) "yep"
  [4]=>
  string(4) "nope"
  [5]=>
  string(4) "nope"
  [6]=>
  string(3) "yep"
  [7]=>
  string(4) "nope"
}
string(52) "["yep","yep","yep","yep","nope","nope","yep","nope"]"
