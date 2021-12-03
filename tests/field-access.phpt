--TEST--
field access, piping
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('.foo', '{"foo": 42, "bar": 43}'),
    array('.foo | .bar', '{"foo": {"bar": 42}, "bar": "badvalue"}'),
    array('.foo.bar', '{"foo": {"bar": 42}, "bar": "badvalue"}'),
    array('.foo_bar', '{"foo_bar": 2}'),
    array('.["foo"].bar', '{"foo": {"bar": 42}, "bar": "badvalue"}'),
    array('."foo"."bar"', '{"foo": {"bar": 20}}'),
    array('[.[]|.foo?]', '[1,[2],{"foo":3,"bar":4},{},{"foo":5}]'),
    array('[.[]|.foo?.bar?]', '[1,[2],[],{"foo":3},{"foo":{"bar":4}},{}]'),
    array('[..]', '[1,[[2]],{ "a":[1]}]'),
    array('[.[]|.[]?]', '[1,null,[],[1,[2,[[3]]]],[{}],[{"a":[1,[2]]}]]'),
    array('[.[]|.[1:3]?]', '[1,null,true,false,"abcdef",{},{"a":1,"b":2},[],[1,2,3,4,5],[1,2]]'),
);
foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== .foo
int(42)
string(2) "42"
== .foo | .bar
int(42)
string(2) "42"
== .foo.bar
int(42)
string(2) "42"
== .foo_bar
int(2)
string(1) "2"
== .["foo"].bar
int(42)
string(2) "42"
== ."foo"."bar"
int(20)
string(2) "20"
== [.[]|.foo?]
array(3) {
  [0]=>
  int(3)
  [1]=>
  NULL
  [2]=>
  int(5)
}
string(10) "[3,null,5]"
== [.[]|.foo?.bar?]
array(2) {
  [0]=>
  int(4)
  [1]=>
  NULL
}
string(8) "[4,null]"
== [..]
array(8) {
  [0]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    array(1) {
      [0]=>
      array(1) {
        [0]=>
        int(2)
      }
    }
    [2]=>
    array(1) {
      ["a"]=>
      array(1) {
        [0]=>
        int(1)
      }
    }
  }
  [1]=>
  int(1)
  [2]=>
  array(1) {
    [0]=>
    array(1) {
      [0]=>
      int(2)
    }
  }
  [3]=>
  array(1) {
    [0]=>
    int(2)
  }
  [4]=>
  int(2)
  [5]=>
  array(1) {
    ["a"]=>
    array(1) {
      [0]=>
      int(1)
    }
  }
  [6]=>
  array(1) {
    [0]=>
    int(1)
  }
  [7]=>
  int(1)
}
string(51) "[[1,[[2]],{"a":[1]}],1,[[2]],[2],2,{"a":[1]},[1],1]"
== [.[]|.[]?]
array(4) {
  [0]=>
  int(1)
  [1]=>
  array(2) {
    [0]=>
    int(2)
    [1]=>
    array(1) {
      [0]=>
      array(1) {
        [0]=>
        int(3)
      }
    }
  }
  [2]=>
  array(0) {
  }
  [3]=>
  array(1) {
    ["a"]=>
    array(2) {
      [0]=>
      int(1)
      [1]=>
      array(1) {
        [0]=>
        int(2)
      }
    }
  }
}
string(30) "[1,[2,[[3]]],{},{"a":[1,[2]]}]"
== [.[]|.[1:3]?]
array(5) {
  [0]=>
  NULL
  [1]=>
  string(2) "bc"
  [2]=>
  array(0) {
  }
  [3]=>
  array(2) {
    [0]=>
    int(2)
    [1]=>
    int(3)
  }
  [4]=>
  array(1) {
    [0]=>
    int(2)
  }
}
string(24) "[null,"bc",[],[2,3],[2]]"
