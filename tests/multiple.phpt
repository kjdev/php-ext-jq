--TEST--
multiple outputs, iteration
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('.[]', '[1,2,3]'),
    array('1,1', '[]'),
    array('1,.', '[]'),
    array('[.]', '[2]'),
    array('[[2]]', '[3]'),
    array('[{}]', '[2]'),
    array('[.[]]', '["a"]'),
    array('[(.,1),((.,.[]),(2,3))]', '["a","b"]'),
    array('[([5,5][]),.,.[]]', '[1,2,3]'),
    array('{x: (1,2)},{x:3} | .x', 'null'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== .[]
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
}
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
== 1,1
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(1)
}
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "1"
}
== 1,.
array(2) {
  [0]=>
  int(1)
  [1]=>
  array(0) {
  }
}
array(2) {
  [0]=>
  string(1) "1"
  [1]=>
  string(2) "[]"
}
== [.]
array(1) {
  [0]=>
  array(1) {
    [0]=>
    int(2)
  }
}
string(5) "[[2]]"
== [[2]]
array(1) {
  [0]=>
  array(1) {
    [0]=>
    int(2)
  }
}
string(5) "[[2]]"
== [{}]
array(1) {
  [0]=>
  array(0) {
  }
}
string(4) "[{}]"
== [.[]]
array(1) {
  [0]=>
  string(1) "a"
}
string(5) "["a"]"
== [(.,1),((.,.[]),(2,3))]
array(7) {
  [0]=>
  array(2) {
    [0]=>
    string(1) "a"
    [1]=>
    string(1) "b"
  }
  [1]=>
  int(1)
  [2]=>
  array(2) {
    [0]=>
    string(1) "a"
    [1]=>
    string(1) "b"
  }
  [3]=>
  string(1) "a"
  [4]=>
  string(1) "b"
  [5]=>
  int(2)
  [6]=>
  int(3)
}
string(35) "[["a","b"],1,["a","b"],"a","b",2,3]"
== [([5,5][]),.,.[]]
array(6) {
  [0]=>
  int(5)
  [1]=>
  int(5)
  [2]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(2)
    [2]=>
    int(3)
  }
  [3]=>
  int(1)
  [4]=>
  int(2)
  [5]=>
  int(3)
}
string(19) "[5,5,[1,2,3],1,2,3]"
== {x: (1,2)},{x:3} | .x
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
}
array(3) {
  [0]=>
  string(1) "1"
  [1]=>
  string(1) "2"
  [2]=>
  string(1) "3"
}
