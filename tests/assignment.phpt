--TEST--
assignment
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('.message = "goodbye"', '{"message": "hello"}'),
    array('.foo = .bar', '{"bar":42}'),
    array('.foo |= .+1', '{"foo": 42}'),
    array('.[] += 2, .[] *= 2, .[] -= 2, .[] /= 2, .[] %=2', '[1,3,5]'),
    array('[.[] % 7]', '[-7,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,7]'),
    array('.foo += .foo', '{"foo":2}'),
    array('.[0].a |= {"old":., "new":(.+1)}', '[{"a":1,"b":2}]'),
    array('def inc(x): x |= .+1; inc(.[].a)', '[{"a":1,"b":2},{"a":2,"b":4},{"a":7,"b":8}]'),
    array('.[2][3] = 1', '[4]'),
    array('.foo[2].bar = 1', '{"foo":[11], "bar":42}'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== .message = "goodbye"
array(1) {
  ["message"]=>
  string(7) "goodbye"
}
string(21) "{"message":"goodbye"}"
== .foo = .bar
array(2) {
  ["bar"]=>
  int(42)
  ["foo"]=>
  int(42)
}
string(19) "{"bar":42,"foo":42}"
== .foo |= .+1
array(1) {
  ["foo"]=>
  int(43)
}
string(10) "{"foo":43}"
== .[] += 2, .[] *= 2, .[] -= 2, .[] /= 2, .[] %=2
array(5) {
  [0]=>
  array(3) {
    [0]=>
    int(3)
    [1]=>
    int(5)
    [2]=>
    int(7)
  }
  [1]=>
  array(3) {
    [0]=>
    int(2)
    [1]=>
    int(6)
    [2]=>
    int(10)
  }
  [2]=>
  array(3) {
    [0]=>
    int(-1)
    [1]=>
    int(1)
    [2]=>
    int(3)
  }
  [3]=>
  array(3) {
    [0]=>
    float(0.5)
    [1]=>
    float(1.5)
    [2]=>
    float(2.5)
  }
  [4]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(1)
    [2]=>
    int(1)
  }
}
array(5) {
  [0]=>
  string(7) "[3,5,7]"
  [1]=>
  string(8) "[2,6,10]"
  [2]=>
  string(8) "[-1,1,3]"
  [3]=>
  string(13) "[0.5,1.5,2.5]"
  [4]=>
  string(7) "[1,1,1]"
}
== [.[] % 7]
array(15) {
  [0]=>
  int(0)
  [1]=>
  int(-6)
  [2]=>
  int(-5)
  [3]=>
  int(-4)
  [4]=>
  int(-3)
  [5]=>
  int(-2)
  [6]=>
  int(-1)
  [7]=>
  int(0)
  [8]=>
  int(1)
  [9]=>
  int(2)
  [10]=>
  int(3)
  [11]=>
  int(4)
  [12]=>
  int(5)
  [13]=>
  int(6)
  [14]=>
  int(0)
}
string(37) "[0,-6,-5,-4,-3,-2,-1,0,1,2,3,4,5,6,0]"
== .foo += .foo
array(1) {
  ["foo"]=>
  int(4)
}
string(9) "{"foo":4}"
== .[0].a |= {"old":., "new":(.+1)}
array(1) {
  [0]=>
  array(2) {
    ["a"]=>
    array(2) {
      ["old"]=>
      int(1)
      ["new"]=>
      int(2)
    }
    ["b"]=>
    int(2)
  }
}
string(31) "[{"a":{"old":1,"new":2},"b":2}]"
== def inc(x): x |= .+1; inc(.[].a)
array(3) {
  [0]=>
  array(2) {
    ["a"]=>
    int(2)
    ["b"]=>
    int(2)
  }
  [1]=>
  array(2) {
    ["a"]=>
    int(3)
    ["b"]=>
    int(4)
  }
  [2]=>
  array(2) {
    ["a"]=>
    int(8)
    ["b"]=>
    int(8)
  }
}
string(43) "[{"a":2,"b":2},{"a":3,"b":4},{"a":8,"b":8}]"
== .[2][3] = 1
array(3) {
  [0]=>
  int(4)
  [1]=>
  NULL
  [2]=>
  array(4) {
    [0]=>
    NULL
    [1]=>
    NULL
    [2]=>
    NULL
    [3]=>
    int(1)
  }
}
string(27) "[4,null,[null,null,null,1]]"
== .foo[2].bar = 1
array(2) {
  ["foo"]=>
  array(3) {
    [0]=>
    int(11)
    [1]=>
    NULL
    [2]=>
    array(1) {
      ["bar"]=>
      int(1)
    }
  }
  ["bar"]=>
  int(42)
}
string(36) "{"foo":[11,null,{"bar":1}],"bar":42}"
