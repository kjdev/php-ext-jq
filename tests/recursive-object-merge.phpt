--TEST--
recursive object merge
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('{"k": {"a": 1, "b": 2}} * .', '{"k": {"a": 0,"c": 3}}'),
    array('{"k": {"a": 1, "b": 2}, "hello": {"x": 1}} * .', '{"k": {"a": 0,"c": 3}, "hello": 1}'),
    array('{"k": {"a": 1, "b": 2}, "hello": 1} * .', '{"k": {"a": 0,"c": 3}, "hello": {"x": 1}}'),
    array('{"a": {"b": 1}, "c": {"d": 2}, "e": 5} * .', '{"a": {"b": 2}, "c": {"d": 3, "f": 9}}'),
    array('[.[]|arrays]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
    array('[.[]|objects]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
    array('[.[]|iterables]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
    array('[.[]|scalars]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
    array('[.[]|values]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
    array('[.[]|booleans]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
    array('[.[]|nulls]', '[1,2,"foo",[],[3,[]],{},true,false,null]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== {"k": {"a": 1, "b": 2}} * .
array(1) {
  ["k"]=>
  array(3) {
    ["a"]=>
    int(0)
    ["b"]=>
    int(2)
    ["c"]=>
    int(3)
  }
}
string(25) "{"k":{"a":0,"b":2,"c":3}}"
== {"k": {"a": 1, "b": 2}, "hello": {"x": 1}} * .
array(2) {
  ["k"]=>
  array(3) {
    ["a"]=>
    int(0)
    ["b"]=>
    int(2)
    ["c"]=>
    int(3)
  }
  ["hello"]=>
  int(1)
}
string(35) "{"k":{"a":0,"b":2,"c":3},"hello":1}"
== {"k": {"a": 1, "b": 2}, "hello": 1} * .
array(2) {
  ["k"]=>
  array(3) {
    ["a"]=>
    int(0)
    ["b"]=>
    int(2)
    ["c"]=>
    int(3)
  }
  ["hello"]=>
  array(1) {
    ["x"]=>
    int(1)
  }
}
string(41) "{"k":{"a":0,"b":2,"c":3},"hello":{"x":1}}"
== {"a": {"b": 1}, "c": {"d": 2}, "e": 5} * .
array(3) {
  ["a"]=>
  array(1) {
    ["b"]=>
    int(2)
  }
  ["c"]=>
  array(2) {
    ["d"]=>
    int(3)
    ["f"]=>
    int(9)
  }
  ["e"]=>
  int(5)
}
string(37) "{"a":{"b":2},"c":{"d":3,"f":9},"e":5}"
== [.[]|arrays]
array(2) {
  [0]=>
  array(0) {
  }
  [1]=>
  array(2) {
    [0]=>
    int(3)
    [1]=>
    array(0) {
    }
  }
}
string(11) "[[],[3,[]]]"
== [.[]|objects]
array(1) {
  [0]=>
  array(0) {
  }
}
string(4) "[{}]"
== [.[]|iterables]
array(3) {
  [0]=>
  array(0) {
  }
  [1]=>
  array(2) {
    [0]=>
    int(3)
    [1]=>
    array(0) {
    }
  }
  [2]=>
  array(0) {
  }
}
string(14) "[[],[3,[]],{}]"
== [.[]|scalars]
array(6) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  string(3) "foo"
  [3]=>
  bool(true)
  [4]=>
  bool(false)
  [5]=>
  NULL
}
string(27) "[1,2,"foo",true,false,null]"
== [.[]|values]
array(8) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  string(3) "foo"
  [3]=>
  array(0) {
  }
  [4]=>
  array(2) {
    [0]=>
    int(3)
    [1]=>
    array(0) {
    }
  }
  [5]=>
  array(0) {
  }
  [6]=>
  bool(true)
  [7]=>
  bool(false)
}
string(35) "[1,2,"foo",[],[3,[]],{},true,false]"
== [.[]|booleans]
array(2) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
}
string(12) "[true,false]"
== [.[]|nulls]
array(1) {
  [0]=>
  NULL
}
string(6) "[null]"
