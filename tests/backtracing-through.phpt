--TEST--
backtracking through function
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('[[20,10][1,0] as $x | def f: (100,200) as $y | def g: [$x + $y, .]; . + $x | g; f[0] | [f][0][1] | f]', '999999999'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== [[20,10][1,0] as $x | def f: (100,200) as $y | def g: [$x + $y, .]; . + $x | g; f[0] | [f][0][1] | f]
array(8) {
  [0]=>
  array(2) {
    [0]=>
    int(110)
    [1]=>
    int(130)
  }
  [1]=>
  array(2) {
    [0]=>
    int(210)
    [1]=>
    int(130)
  }
  [2]=>
  array(2) {
    [0]=>
    int(110)
    [1]=>
    int(230)
  }
  [3]=>
  array(2) {
    [0]=>
    int(210)
    [1]=>
    int(230)
  }
  [4]=>
  array(2) {
    [0]=>
    int(120)
    [1]=>
    int(160)
  }
  [5]=>
  array(2) {
    [0]=>
    int(220)
    [1]=>
    int(160)
  }
  [6]=>
  array(2) {
    [0]=>
    int(120)
    [1]=>
    int(260)
  }
  [7]=>
  array(2) {
    [0]=>
    int(220)
    [1]=>
    int(260)
  }
}
string(81) "[[110,130],[210,130],[110,230],[210,230],[120,160],[220,160],[120,260],[220,260]]"
