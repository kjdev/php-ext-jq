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

```php
$jq = new Jq;
$jq->load('{"name": "jq", "version": "0.1.0"}');
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

```
Jq {
  public __construct(void)
  public bool load(string $str)
  public bool loadString(string $str)
  public bool loadFile(string $filename)
  public mixed filter(string $filter, int $flags = 0)
  static public mixed parse(string $str, string $filter, int $flags = 0)
  static public mixed parseString(string $str, string $filter, int $flags = 0)
  static public mixed parseFile(string $filename, string $filter, int $flags = 0)
}
```

### Jq::\_\_construct

```php
public __construct(void)
```

Create a Jq instance.

**Return Values:**

Returns a new Jq object

---

### Jq::load

```php
public bool load(string $str)
```

Load a JSON string.

**Parameters:**

* str

  JSON text string.

**Return Values:**

Returns TRUE on success or FALSE on failure.

---

### Jq::loadString

```php
public bool load(string $str)
```

Load a JSON string.

alias: [Jq::load](#jqload)

---

### Jq::loadFile

```php
public bool loadFile(string $filename)
```

Load a JSON string from file.

**Parameters:**

* filename

  JSON text filen name.

**Return Values:**

Returns TRUE on success or FALSE on failure.

---

### Jq::filter

```php
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

### Jq::parse

```php
static public mixed parse(string $str, string $filter, int $flags = 0)
```

Get filtering result of the JSON string.

**Parameters:**

* str

  JSON text string.

* filter

  jq filter string.

* flags

  - `Jq::RAW` is raw output

**Return Values:**

Returns the result value, or FALSE on error.

---

### Jq::parseString

```php
static public mixed parseString(string $str, string $filter, int $flags = 0)
```

Get filtering result of the JSON string.

alias: [Jq::parse](#jqparse)

---

### Jq::parseFile

```php
static public mixed parseFile(string $filename, string $filter, int $flags = 0)
```

Get filtering result of the JSON string file.

**Parameters:**

* filename

  JSON text file name.

* filter

  jq filter string.

* flags

  - `Jq::RAW` is raw output

**Return Values:**

Returns the result value, or FALSE on error.

## Examples

* Setting a `Jq::RAW`

```php
$jq = new Jq;
$jq->load('{"name": "jq", "version": "0.1.0"}');
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
print_r(Jq::parse($text, '.'));
echo 'NAME: ', Jq::parse($text, '.name'), PHP_EOL;
echo 'VERSION: ', Jq::parse($text, '.version', JQ::RAW), PHP_EOL;
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
