# PHP Extension for jq

[![Build Status](https://secure.travis-ci.org/kjdev/php-ext-jq.png?branch=master)](http://travis-ci.org/kjdev/php-ext-jq)

This extension allows [jq](http://stedolan.github.io/jq).

## Build

```
% git clone --depth=1 https://github.com/kjdev/php-ext-jq.git
% cd php-ext-jq
% phpize
% ./configure
% make
% make test
% make install
```

## Configuration

jq.ini:

```
extension=jq.so
; jq.display_errors=Off
```

## Usage

``` php
$jq = Jq\Input::fromString('{"name": "jq", "version": "0.1.0"}');
print_r($jq->filter('.'));
echo 'NAME: ', $jq->filter('.name'), PHP_EOL;
echo 'VERSION: ', $jq->filter('.version'), PHP_EOL;
```

output:

```
Array
(
    [name] => jq
    [version] => 0.1.0
)
NAME: jq
VERSION: 0.1.0
```

## Class synopsis

### Jq\Input

``` php
Jq\Input {
  public static Jq\Executor fromString(string $text)
  public static Jq\Executor fromFile(string $file)
}
```

---

### Jq\Input::fromString

``` php
public static Jq\Executor fromString(string $text)
```

Load a JSON string.

**Parameters:**

* text

  JSON text string.

**Return Values:**

Returns Jq\Executor instance.

---

### Jq\Input::fromFile

``` php
public static Jq\Executor fromFile(string $file)
```

Load a JSON file.

**Parameters:**

* file

  JSON file name.

**Return Values:**

Returns Jq\Executor instance.

---

### Jq\Executor

``` php
Jq\Executor {
  public mixed filter(string $filter, int $flags)
}
```

---

### Jq\Executor::filter

``` php
public mixed filter(string $filter, int $flags = 0)
```

Get filtering result of the load string.

**Parameters:**

* filter

  jq filter string.

* flags

  - `Jq::RAW` is raw output

**Return Values:**

Returns the result value, or FALSE on error.

---

### Jq\Run

``` php
Jq\Run {
  public static mixed fromString(string $text, string $filter, int $flags = 0)
  public static mixed fromFile(string $file, string $filter, int $flags = 0)
}
```

---

### Jq\Run::fromString

``` php
public static mixed fromString(string $text, string $filter, int $flags = 0)
```

Get filtering result of the JSON string.

**Parameters:**

* text

  JSON text string.

* filter

  jq filter string.

* flags

  - `Jq::RAW` is raw output

**Return Values:**

Returns the result value, or FALSE on error.

---

### Jq\Run::fromFile

``` php
public static mixed fromFile(string $file, string $filter, int $flags = 0)
```

Get filtering result of the JSON file.

**Parameters:**

* file

  JSON file name.

* filter

  jq filter string.

* flags

  - `Jq::RAW` is raw output

**Return Values:**

Returns the result value, or FALSE on error.

## Examples

* Setting a `Jq::RAW`

```php
$jq = Jq\Input::fromString('{"name": "jq", "version": "0.1.0"}');
print_r($jq->filter('.', JQ::RAW));
echo PHP_EOL;
echo 'NAME: ', $jq->filter('.name', JQ::RAW), PHP_EOL;
echo 'VERSION: ', $jq->filter('.version', JQ::RAW), PHP_EOL;
```

The above example will output:

```
{"name":"jq","version":"0.1.0"}
NAME: jq
VERSION: 0.1.0
```

* Execute static function

```php
$text = '{"name": "jq", "version": "0.1.0"}';
print_r(Jq\Run::fromString($text, '.'));
echo 'NAME: ', Jq\Run::fromString($text, '.name'), PHP_EOL;
echo 'VERSION: ', Jq\Run::fromString($text, '.version', JQ::RAW), PHP_EOL;
```

The above example will output:

```
Array
(
    [name] => jq
    [version] => 0.1.0
)
NAME: jq
VERSION: 0.1.0
```
