--TEST--
jq.display_errors
--SKIPIF--
--FILE--
<?php
use Jq\Input;

echo "== default\n";
$jq = Input::fromString('{"name": "jq", "version": "0.1.0"}');
var_dump($jq->filter('.[] | .test'));

echo "== on\n";
ini_set('jq.display_errors', 'on');
var_dump($jq->filter('.[] | .test'));

echo "== off\n";
ini_set('jq.display_errors', 'off');
var_dump($jq->filter('.[] | .test'));
?>
===Done===
--EXPECTF--
== default
bool(false)
== on

Warning: Jq\Executor::filter(): filter parse error in %s on line %d
bool(false)
== off
bool(false)
===Done===
