--TEST--
closures and lexical scoping
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = array(
    array('def id(x):x; 2000 as $x | def f(x):1 as $x | id([$x, x, x]); def g(x): 100 as $x | f($x,$x+x); g($x)', '"more testing"')
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq = Input::fromString($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== def id(x):x; 2000 as $x | def f(x):1 as $x | id([$x, x, x]); def g(x): 100 as $x | f($x,$x+x); g($x)
array(5) {
  [0]=>
  int(1)
  [1]=>
  int(100)
  [2]=>
  int(2100)
  [3]=>
  int(100)
  [4]=>
  int(2100)
}
string(21) "[1,100,2100,100,2100]"
