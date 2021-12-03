--TEST--
sort
--SKIPIF--
--FILE--
<?php
use Jq\Input;
use Jq\Run;

$text = '{"c":"3", "a":"1", "b":{"2":{"z":"12", "y":"11", "x":"10"}}}';

$jq = Jq\Input::fromString($text);

echo "== default\n";
print_r($jq->filter('.'));
echo "== sort\n";
print_r($jq->filter('.', Jq\SORT));

echo "== raw\n";
echo $jq->filter('.', Jq\RAW), PHP_EOL;
echo "== raw|sort\n";
echo $jq->filter('.', Jq\RAW|Jq\SORT), PHP_EOL;

echo "== run\n";
print_r(Run::fromString($text, '.'));
echo "== run sort\n";
print_r(Run::fromString($text, '.', Jq\SORT));

echo "== run raw\n";
echo Run::fromString($text, '.', Jq\RAW), PHP_EOL;
echo "== run raw|sort\n";
echo Run::fromString($text, '.', Jq\RAW|Jq\SORT), PHP_EOL;

--EXPECTF--
== default
Array
(
    [c] => 3
    [a] => 1
    [b] => Array
        (
            [2] => Array
                (
                    [z] => 12
                    [y] => 11
                    [x] => 10
                )

        )

)
== sort
Array
(
    [a] => 1
    [b] => Array
        (
            [2] => Array
                (
                    [x] => 10
                    [y] => 11
                    [z] => 12
                )

        )

    [c] => 3
)
== raw
{"c":"3","a":"1","b":{"2":{"z":"12","y":"11","x":"10"}}}
== raw|sort
{"a":"1","b":{"2":{"x":"10","y":"11","z":"12"}},"c":"3"}
== run
Array
(
    [c] => 3
    [a] => 1
    [b] => Array
        (
            [2] => Array
                (
                    [z] => 12
                    [y] => 11
                    [x] => 10
                )

        )

)
== run sort
Array
(
    [a] => 1
    [b] => Array
        (
            [2] => Array
                (
                    [x] => 10
                    [y] => 11
                    [z] => 12
                )

        )

    [c] => 3
)
== run raw
{"c":"3","a":"1","b":{"2":{"z":"12","y":"11","x":"10"}}}
== run raw|sort
{"a":"1","b":{"2":{"x":"10","y":"11","z":"12"}},"c":"3"}
