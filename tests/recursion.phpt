--TEST--
recursion
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('def fac: if . == 1 then 1 else . * (. - 1 | fac) end; [.[] | fac]', '[1,2,3,4]'),
    array('reduce .[] as $x (0; . + $x)', '[1,2,4]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== def fac: if . == 1 then 1 else . * (. - 1 | fac) end; [.[] | fac]
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(6)
  [3]=>
  int(24)
}
string(10) "[1,2,6,24]"
== reduce .[] as $x (0; . + $x)
int(7)
string(1) "7"
