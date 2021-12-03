--TEST--
builtin functions
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('1+1', 'null'),
    array('2-1', 'null'),
    array('2-(-1)', 'null'),
    array('.+4', '15'),
    array('.+null', '{"a":42}'),
    array('null+.', 'null'),
    array('.a+.b', '{"a":42}'),
    array('[1,2,3] + [.]', 'null'),
    array('{"a":1} + {"b":2} + {"c":3}', '"asdfasdf"'),
    array('"asdf" + "jkl;" + . + . + .', '"some string"'),
    array('42 - .', '11'),
    array('[1,2,3,4,1] - [.,3]', '1'),
    array('[10 * 20, 20 / .]', '4'),
    array('1 + 2 * 2 + 10 / 2', 'null'),
    array('[16 / 4 / 2, 16 / 4 * 2, 16 - 4 - 2, 16 - 4 + 2]', 'null'),
    array('25 % 7', 'null'),
    array('49732 % 472', 'null'),
    array('1 + tonumber + ("10" | tonumber)', '4'),
    array('[{"a":42},.object,10,.num,false,true,null,"b",[1,4]] | .[] as $x | [$x == .[]]', '{"object": {"a":42}, "num":10.0}'),
    array('[.[] | length]', '[[], {}, [1,2], {"a":42}, "asdf", "\u03bc"]'),
    array('map(keys)', '[{}, {"abcd":1,"abc":2,"abcde":3}, {"x":1, "z": 3, "y":2}]'),
    array('[1,2,empty,3,empty,4]', 'null'),
    array('map(add)', '[[], [1,2,3], ["a","b","c"], [[3],[4,5],[6]], [{"a":1}, {"b":2}, {"a":3}]]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq\RAW));
}

--EXPECTF--
== 1+1
int(2)
string(1) "2"
== 2-1
int(1)
string(1) "1"
== 2-(-1)
int(3)
string(1) "3"
== .+4
int(19)
string(2) "19"
== .+null
array(1) {
  ["a"]=>
  int(42)
}
string(8) "{"a":42}"
== null+.
NULL
string(4) "null"
== .a+.b
int(42)
string(2) "42"
== [1,2,3] + [.]
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  NULL
}
string(12) "[1,2,3,null]"
== {"a":1} + {"b":2} + {"c":3}
array(3) {
  ["a"]=>
  int(1)
  ["b"]=>
  int(2)
  ["c"]=>
  int(3)
}
string(19) "{"a":1,"b":2,"c":3}"
== "asdf" + "jkl;" + . + . + .
string(41) "asdfjkl;some stringsome stringsome string"
string(41) "asdfjkl;some stringsome stringsome string"
== 42 - .
int(31)
string(2) "31"
== [1,2,3,4,1] - [.,3]
array(2) {
  [0]=>
  int(2)
  [1]=>
  int(4)
}
string(5) "[2,4]"
== [10 * 20, 20 / .]
array(2) {
  [0]=>
  int(200)
  [1]=>
  int(5)
}
string(7) "[200,5]"
== 1 + 2 * 2 + 10 / 2
int(10)
string(2) "10"
== [16 / 4 / 2, 16 / 4 * 2, 16 - 4 - 2, 16 - 4 + 2]
array(4) {
  [0]=>
  int(2)
  [1]=>
  int(8)
  [2]=>
  int(10)
  [3]=>
  int(14)
}
string(11) "[2,8,10,14]"
== 25 % 7
int(4)
string(1) "4"
== 49732 % 472
int(172)
string(3) "172"
== 1 + tonumber + ("10" | tonumber)
int(15)
string(2) "15"
== [{"a":42},.object,10,.num,false,true,null,"b",[1,4]] | .[] as $x | [$x == .[]]
array(9) {
  [0]=>
  array(9) {
    [0]=>
    bool(true)
    [1]=>
    bool(true)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(false)
    [5]=>
    bool(false)
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [1]=>
  array(9) {
    [0]=>
    bool(true)
    [1]=>
    bool(true)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(false)
    [5]=>
    bool(false)
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [2]=>
  array(9) {
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
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [3]=>
  array(9) {
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
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [4]=>
  array(9) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(true)
    [5]=>
    bool(false)
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [5]=>
  array(9) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(false)
    [5]=>
    bool(true)
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [6]=>
  array(9) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(false)
    [5]=>
    bool(false)
    [6]=>
    bool(true)
    [7]=>
    bool(false)
    [8]=>
    bool(false)
  }
  [7]=>
  array(9) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(false)
    [5]=>
    bool(false)
    [6]=>
    bool(false)
    [7]=>
    bool(true)
    [8]=>
    bool(false)
  }
  [8]=>
  array(9) {
    [0]=>
    bool(false)
    [1]=>
    bool(false)
    [2]=>
    bool(false)
    [3]=>
    bool(false)
    [4]=>
    bool(false)
    [5]=>
    bool(false)
    [6]=>
    bool(false)
    [7]=>
    bool(false)
    [8]=>
    bool(true)
  }
}
array(9) {
  [0]=>
  string(53) "[true,true,false,false,false,false,false,false,false]"
  [1]=>
  string(53) "[true,true,false,false,false,false,false,false,false]"
  [2]=>
  string(53) "[false,false,true,true,false,false,false,false,false]"
  [3]=>
  string(53) "[false,false,true,true,false,false,false,false,false]"
  [4]=>
  string(54) "[false,false,false,false,true,false,false,false,false]"
  [5]=>
  string(54) "[false,false,false,false,false,true,false,false,false]"
  [6]=>
  string(54) "[false,false,false,false,false,false,true,false,false]"
  [7]=>
  string(54) "[false,false,false,false,false,false,false,true,false]"
  [8]=>
  string(54) "[false,false,false,false,false,false,false,false,true]"
}
== [.[] | length]
array(6) {
  [0]=>
  int(0)
  [1]=>
  int(0)
  [2]=>
  int(2)
  [3]=>
  int(1)
  [4]=>
  int(4)
  [5]=>
  int(1)
}
string(13) "[0,0,2,1,4,1]"
== map(keys)
array(3) {
  [0]=>
  array(0) {
  }
  [1]=>
  array(3) {
    [0]=>
    string(3) "abc"
    [1]=>
    string(4) "abcd"
    [2]=>
    string(5) "abcde"
  }
  [2]=>
  array(3) {
    [0]=>
    string(1) "x"
    [1]=>
    string(1) "y"
    [2]=>
    string(1) "z"
  }
}
string(41) "[[],["abc","abcd","abcde"],["x","y","z"]]"
== [1,2,empty,3,empty,4]
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(4)
}
string(9) "[1,2,3,4]"
== map(add)
array(5) {
  [0]=>
  NULL
  [1]=>
  int(6)
  [2]=>
  string(3) "abc"
  [3]=>
  array(4) {
    [0]=>
    int(3)
    [1]=>
    int(4)
    [2]=>
    int(5)
    [3]=>
    int(6)
  }
  [4]=>
  array(2) {
    ["a"]=>
    int(3)
    ["b"]=>
    int(2)
  }
}
string(38) "[null,6,"abc",[3,4,5,6],{"a":3,"b":2}]"
