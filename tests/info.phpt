--TEST--
phpinfo() displays jq info
--SKIPIF--
--FILE--
<?php
phpinfo();
?>
--EXPECTF--
%a
jq

jq support => enabled
Extension Version => %d.%d.%d
%a
