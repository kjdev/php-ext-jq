--TEST--
Jq\Executor variable(s) argument
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$data = <<<EOT
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
    $jq = Input::fromString($data);
    $jq->variable('test', $var['test']);
    var_dump($jq->variables());
    var_dump($jq->filter('.[] | select(.test == $test)'));
  } catch (Throwable $e) {
    echo $e->getMessage(), PHP_EOL;
  }
}

echo "== multiple variable", PHP_EOL;
$jq = Input::fromString($data);
$jq->variable('var1', '1');
$jq->variable('var2', '@1');
var_dump($jq->variables());
var_dump($jq->filter('.[] | select(.test == $var1)'));
var_dump($jq->filter('.[] | select(.test == $var2)'));
?>
===Done===
--EXPECTF--
== test: test
array(1) {
  ["test"]=>
  string(4) "test"
}
array(2) {
  ["var"]=>
  string(8) "str-test"
  ["test"]=>
  string(4) "test"
}
== test: @test
array(1) {
  ["test"]=>
  string(5) "@test"
}
test: invalid JSON text passed to variables.
== test: @"test"
array(1) {
  ["test"]=>
  string(7) "@"test""
}
array(2) {
  ["var"]=>
  string(8) "str-test"
  ["test"]=>
  string(4) "test"
}
== test: 1
array(1) {
  ["test"]=>
  string(1) "1"
}
array(2) {
  ["var"]=>
  string(5) "str-1"
  ["test"]=>
  string(1) "1"
}
== test: @1
array(1) {
  ["test"]=>
  string(2) "@1"
}
array(2) {
  ["var"]=>
  string(5) "int-1"
  ["test"]=>
  int(1)
}
== test: true
array(1) {
  ["test"]=>
  string(4) "true"
}
array(2) {
  ["var"]=>
  string(8) "str-true"
  ["test"]=>
  string(4) "true"
}
== test: @true
array(1) {
  ["test"]=>
  string(5) "@true"
}
array(2) {
  ["var"]=>
  string(4) "true"
  ["test"]=>
  bool(true)
}
== test: ["1"]
array(1) {
  ["test"]=>
  string(5) "["1"]"
}
bool(false)
== test: @["1"]
array(1) {
  ["test"]=>
  string(6) "@["1"]"
}
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
array(1) {
  ["test"]=>
  string(10) "@{"k":"1"}"
}
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
array(1) {
  ["test"]=>
  string(1) "@"
}
array(2) {
  ["var"]=>
  string(5) "str-@"
  ["test"]=>
  string(1) "@"
}
== test: 1

Warning: Jq\Executor::variable(): parameter 'value' must be an string in %s on line %d
array(0) {
}
failed to compile filter string.
== multiple variable
array(2) {
  ["var1"]=>
  string(1) "1"
  ["var2"]=>
  string(2) "@1"
}
array(2) {
  ["var"]=>
  string(5) "str-1"
  ["test"]=>
  string(1) "1"
}
array(2) {
  ["var"]=>
  string(5) "int-1"
  ["test"]=>
  int(1)
}
===Done===
