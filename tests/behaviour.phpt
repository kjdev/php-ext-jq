--TEST--
behavior
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('[.[] | [.foo[] // .bar]]', '[{"foo":[1,2], "bar": 42}, {"foo":[1], "bar": null}, {"foo":[null,false,3], "bar": 18}, {"foo":[], "bar":42}, {"foo": [null,false,null], "bar": 41}]'),
    array('.[] //= .[0]', '["hello",true,false,[false],null]'),
    array('.[] | [.[0] and .[1], .[0] or .[1]]', '[[true,[]], [false,1], [42,null], [null,false]]'),
    array('[.[] | not]', '[1,0,false,null,true,"hello"]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== [.[] | [.foo[] // .bar]]
array(5) {
  [0]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [1]=>
  array(1) {
    [0]=>
    int(1)
  }
  [2]=>
  array(1) {
    [0]=>
    int(3)
  }
  [3]=>
  array(1) {
    [0]=>
    int(42)
  }
  [4]=>
  array(1) {
    [0]=>
    int(41)
  }
}
string(25) "[[1,2],[1],[3],[42],[41]]"
== .[] //= .[0]
array(5) {
  [0]=>
  string(5) "hello"
  [1]=>
  bool(true)
  [2]=>
  string(5) "hello"
  [3]=>
  array(1) {
    [0]=>
    bool(false)
  }
  [4]=>
  string(5) "hello"
}
string(38) "["hello",true,"hello",[false],"hello"]"
== .[] | [.[0] and .[1], .[0] or .[1]]
array(4) {
  [0]=>
  array(2) {
    [0]=>
    bool(true)
    [1]=>
    bool(true)
  }
  [1]=>
  array(2) {
    [0]=>
    bool(false)
    [1]=>
    bool(true)
  }
  [2]=>
  array(2) {
    [0]=>
    bool(false)
    [1]=>
    bool(true)
  }
  [3]=>
  array(2) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
  }
}
array(4) {
  [0]=>
  string(11) "[true,true]"
  [1]=>
  string(12) "[false,true]"
  [2]=>
  string(12) "[false,true]"
  [3]=>
  string(13) "[false,false]"
}
== [.[] | not]
array(6) {
  [0]=>
  bool(false)
  [1]=>
  bool(false)
  [2]=>
  bool(true)
  [3]=>
  bool(true)
  [4]=>
  bool(false)
  [5]=>
  bool(false)
}
string(35) "[false,false,true,true,false,false]"
