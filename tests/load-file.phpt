--TEST--
load File
--SKIPIF--
--FILE--
<?php
use Jq\Input;

$filename = __DIR__ . '/test.json';
$filter = '.[] | .name';

$jq = Input::fromFile($filename);

echo "== ", $filter, PHP_EOL;
var_dump($jq->filter($filter));
var_dump($jq->filter($filter, Jq\RAW));

--EXPECTF--
== .[] | .name
array(30) {
  [0]=>
  string(17) "apache-mod-coffee"
  [1]=>
  string(18) "apache-mod-hoedown"
  [2]=>
  string(20) "apache-mod-identicon"
  [3]=>
  string(16) "apache-mod-mongo"
  [4]=>
  string(15) "apache-mod-sass"
  [5]=>
  string(19) "apache-mod-shorturl"
  [6]=>
  string(18) "apache-mod-sundown"
  [7]=>
  string(13) "apache-mod-v8"
  [8]=>
  string(8) "cphalcon"
  [9]=>
  string(7) "fswatch"
  [10]=>
  string(9) "hoextdown"
  [11]=>
  string(12) "livereload-c"
  [12]=>
  string(2) "lq"
  [13]=>
  string(32) "mariadb-udf-php-password-hashing"
  [14]=>
  string(4) "mmhd"
  [15]=>
  string(5) "pdefs"
  [16]=>
  string(15) "php-ext-callmap"
  [17]=>
  string(12) "php-ext-elog"
  [18]=>
  string(12) "php-ext-enum"
  [19]=>
  string(22) "php-ext-extension_load"
  [20]=>
  string(17) "php-ext-extmethod"
  [21]=>
  string(22) "php-ext-handlersocketi"
  [22]=>
  string(14) "php-ext-hidefl"
  [23]=>
  string(15) "php-ext-hoedown"
  [24]=>
  string(11) "php-ext-lz4"
  [25]=>
  string(16) "php-ext-msgpacki"
  [26]=>
  string(16) "php-ext-override"
  [27]=>
  string(19) "php-ext-shellinford"
  [28]=>
  string(14) "php-ext-snappy"
  [29]=>
  string(17) "php-ext-transactd"
}
array(30) {
  [0]=>
  string(17) "apache-mod-coffee"
  [1]=>
  string(18) "apache-mod-hoedown"
  [2]=>
  string(20) "apache-mod-identicon"
  [3]=>
  string(16) "apache-mod-mongo"
  [4]=>
  string(15) "apache-mod-sass"
  [5]=>
  string(19) "apache-mod-shorturl"
  [6]=>
  string(18) "apache-mod-sundown"
  [7]=>
  string(13) "apache-mod-v8"
  [8]=>
  string(8) "cphalcon"
  [9]=>
  string(7) "fswatch"
  [10]=>
  string(9) "hoextdown"
  [11]=>
  string(12) "livereload-c"
  [12]=>
  string(2) "lq"
  [13]=>
  string(32) "mariadb-udf-php-password-hashing"
  [14]=>
  string(4) "mmhd"
  [15]=>
  string(5) "pdefs"
  [16]=>
  string(15) "php-ext-callmap"
  [17]=>
  string(12) "php-ext-elog"
  [18]=>
  string(12) "php-ext-enum"
  [19]=>
  string(22) "php-ext-extension_load"
  [20]=>
  string(17) "php-ext-extmethod"
  [21]=>
  string(22) "php-ext-handlersocketi"
  [22]=>
  string(14) "php-ext-hidefl"
  [23]=>
  string(15) "php-ext-hoedown"
  [24]=>
  string(11) "php-ext-lz4"
  [25]=>
  string(16) "php-ext-msgpacki"
  [26]=>
  string(16) "php-ext-override"
  [27]=>
  string(19) "php-ext-shellinford"
  [28]=>
  string(14) "php-ext-snappy"
  [29]=>
  string(17) "php-ext-transactd"
}
