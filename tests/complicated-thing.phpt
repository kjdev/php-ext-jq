--TEST--
complicated thing
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('[{"foo":[1,2,{"bar":18},"world"]} == {"foo":[1,2,{"bar":18},"world"]},{"foo":[1,2,{"bar":18},"world"]} == {"foo":[1,2,{"bar":19},"world"]}]', '{}'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== [{"foo":[1,2,{"bar":18},"world"]} == {"foo":[1,2,{"bar":18},"world"]},{"foo":[1,2,{"bar":18},"world"]} == {"foo":[1,2,{"bar":19},"world"]}]
array(2) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
}
string(12) "[true,false]"
