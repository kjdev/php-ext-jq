--TEST--
Jq\Run::fromString() variables argument
--SKIPIF--
--FILE--
<?php
$text = <<<EOT
[
  { "var": "int-0", "test": 0 },
  { "var": "int-1", "test": 1 },
  { "var": "int-2", "test": 2 },
  { "var": "str-test", "test": "test" },
  { "var": "str-0", "test": "0" },
  { "var": "str-1", "test": "1" },
  { "var": "str-2", "test": "2" },
  { "var": "str-true", "test": "true" },
  { "var": "str-false", "test": "false" },
  { "var": "str-@", "test": "@" },
  { "var": "true", "test": true },
  { "var": "false", "test": false },
  { "var": "null", "test": null },
  { "var": "array-1", "test": ["1"] },
  { "var": "array-2", "test": ["2"] },
  { "var": "obj-1", "test": {"k": "1"} },
  { "var": "obj-2", "test": {"k": "2"} }
]
EOT;

$variables = [
  [ 'test' => 'test' ],
  [ 'test' => '@test' ],
  [ 'test' => '@"test"' ],
  [ 'test' => '1' ],
  [ 'test' => '@1' ],
  [ 'test' => 'true' ],
  [ 'test' => '@true' ],
  [ 'test' => '["1"]' ],
  [ 'test' => '@["1"]' ],
  [ 'test' => '@{"k":"1"}' ],
  [ 'test' => '@' ],
  [ 'test' => 1 ],
];

foreach ($variables as $var) {
  try {
    echo "== test: ", ((string) $var['test']), PHP_EOL;
    var_dump(Jq\Run::fromString($text, '.[] | select(.test == $test)', Jq\NONE, $var));
  } catch (Throwable $e) {
    echo $e->getMessage(), PHP_EOL;
  }
}
?>
===Done===
--EXPECTF--
== test: test
array(2) {
  ["var"]=>
  string(8) "str-test"
  ["test"]=>
  string(4) "test"
}
== test: @test
test: invalid JSON text passed to variables.
== test: @"test"
array(2) {
  ["var"]=>
  string(8) "str-test"
  ["test"]=>
  string(4) "test"
}
== test: 1
array(2) {
  ["var"]=>
  string(5) "str-1"
  ["test"]=>
  string(1) "1"
}
== test: @1
array(2) {
  ["var"]=>
  string(5) "int-1"
  ["test"]=>
  int(1)
}
== test: true
array(2) {
  ["var"]=>
  string(8) "str-true"
  ["test"]=>
  string(4) "true"
}
== test: @true
array(2) {
  ["var"]=>
  string(4) "true"
  ["test"]=>
  bool(true)
}
== test: ["1"]
bool(false)
== test: @["1"]
array(2) {
  ["var"]=>
  string(7) "array-1"
  ["test"]=>
  array(1) {
    [0]=>
    string(1) "1"
  }
}
== test: @{"k":"1"}
array(2) {
  ["var"]=>
  string(5) "obj-1"
  ["test"]=>
  array(1) {
    ["k"]=>
    string(1) "1"
  }
}
== test: @
array(2) {
  ["var"]=>
  string(5) "str-@"
  ["test"]=>
  string(1) "@"
}
== test: 1
failed to compile filter string.
===Done===
