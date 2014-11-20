--TEST--
slices
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('[.[3:2], .[-5:4], .[:-2], .[-2:], .[3:3][1:], .[10:]]', '[0,1,2,3,4,5,6]'),
    array('[.[3:2], .[-5:4], .[:-2], .[-2:], .[3:3][1:], .[10:]]', '"abcdefghi"'),
    array('del(.[2:4],.[0],.[-2:])', '[0,1,2,3,4,5,6,7]'),
    array('.[2:4] = ([], ["a","b"], ["a","b","c"])', '[0,1,2,3,4,5,6,7]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== [.[3:2], .[-5:4], .[:-2], .[-2:], .[3:3][1:], .[10:]]
array(6) {
  [0]=>
  array(0) {
  }
  [1]=>
  array(2) {
    [0]=>
    int(2)
    [1]=>
    int(3)
  }
  [2]=>
  array(5) {
    [0]=>
    int(0)
    [1]=>
    int(1)
    [2]=>
    int(2)
    [3]=>
    int(3)
    [4]=>
    int(4)
  }
  [3]=>
  array(2) {
    [0]=>
    int(5)
    [1]=>
    int(6)
  }
  [4]=>
  array(0) {
  }
  [5]=>
  array(0) {
  }
}
string(34) "[[],[2,3],[0,1,2,3,4],[5,6],[],[]]"
== [.[3:2], .[-5:4], .[:-2], .[-2:], .[3:3][1:], .[10:]]
array(6) {
  [0]=>
  string(0) ""
  [1]=>
  string(0) ""
  [2]=>
  string(7) "abcdefg"
  [3]=>
  string(2) "hi"
  [4]=>
  string(0) ""
  [5]=>
  string(0) ""
}
string(28) "["","","abcdefg","hi","",""]"
== del(.[2:4],.[0],.[-2:])
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(4)
  [2]=>
  int(5)
}
string(7) "[1,4,5]"
== .[2:4] = ([], ["a","b"], ["a","b","c"])
array(3) {
  [0]=>
  array(6) {
    [0]=>
    int(0)
    [1]=>
    int(1)
    [2]=>
    int(4)
    [3]=>
    int(5)
    [4]=>
    int(6)
    [5]=>
    int(7)
  }
  [1]=>
  array(8) {
    [0]=>
    int(0)
    [1]=>
    int(1)
    [2]=>
    string(1) "a"
    [3]=>
    string(1) "b"
    [4]=>
    int(4)
    [5]=>
    int(5)
    [6]=>
    int(6)
    [7]=>
    int(7)
  }
  [2]=>
  array(9) {
    [0]=>
    int(0)
    [1]=>
    int(1)
    [2]=>
    string(1) "a"
    [3]=>
    string(1) "b"
    [4]=>
    string(1) "c"
    [5]=>
    int(4)
    [6]=>
    int(5)
    [7]=>
    int(6)
    [8]=>
    int(7)
  }
}
array(3) {
  [0]=>
  string(13) "[0,1,4,5,6,7]"
  [1]=>
  string(21) "[0,1,"a","b",4,5,6,7]"
  [2]=>
  string(25) "[0,1,"a","b","c",4,5,6,7]"
}