--TEST--
Jq\Run::fromFile() variables argument
--SKIPIF--
--FILE--
<?php
$filename = __DIR__ . '/test.json';

echo '== id: @5202266', PHP_EOL;
var_dump(
  Jq\Run::fromFile(
    $filename,
    '.[] | select(.id == $id) | .id, .name',
    Jq\NONE,
    [ 'id' => '@5202266' ]
  )
);

echo '== id: 5202266', PHP_EOL;
var_dump(
  Jq\Run::fromFile(
    $filename,
    '.[] | select(.id == $id) | .id, .name',
    Jq\NONE,
    [ 'id' => '5202266' ]
  )
);

echo '== name: php-ext-lz4', PHP_EOL;
var_dump(
  Jq\Run::fromFile(
    $filename,
    '.[] | select(.name == $name) | .id, .name',
    Jq\NONE,
    [ 'name' => 'php-ext-lz4' ]
  )
);

?>
===Done===
--EXPECTF--
== id: @5202266
array(2) {
  [0]=>
  int(5202266)
  [1]=>
  string(14) "php-ext-snappy"
}
== id: 5202266
bool(false)
== name: php-ext-lz4
array(2) {
  [0]=>
  int(5566449)
  [1]=>
  string(11) "php-ext-lz4"
}
===Done===
