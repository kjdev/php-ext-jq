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
  public static fromString(string $text): Jq\Executor
  public static fromFile(string $file): Jq\Executor
}
```

---

### Jq\Input::fromString

``` php
public static fromString(string $text): Jq\Executor
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
public static fromFile(string $file): Jq\Executor
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
  public filter(string $filter, int $flags): mixed
  public variable(string $name, string $value): self
  public variables(): array
}
```

---

### Jq\Executor::filter

``` php
public filter(string $filter, int $flags = 0): mixed
```

Get filtering result of the load string.

**Parameters:**

* filter

  jq filter string.

* flags

  - `Jq\RAW` is raw output
  - `Jq\SORT` is object with the keys in sorted order

**Return Values:**

Returns the result value, or FALSE on error.

### Jq\Executor::variable

``` php
public variable(string $name, string $value): self
```

Set variable value.

**Parameters:**

* name

  variable name.

* value

  variable value.

  - treat strings starting with `@` as JSON strings

**Return Values:**

Returns the self instance.

### Jq\Executor::variables

``` php
public variables(): array
```

Get variables value.

**Return Values:**

Returns the variables.

---

### Jq\Run

``` php
Jq\Run {
  public static fromString(string $text, string $filter, int $flags = 0, array $variables = []): mixed
  public static fromFile(string $file, string $filter, int $flags = 0, array $variables = []): mixed
}
```

---

### Jq\Run::fromString

``` php
public static fromString(string $text, string $filter, int $flags = 0, array $variables = []): mixed
```

Get filtering result of the JSON string.

**Parameters:**

* text

  JSON text string.

* filter

  jq filter string.

* flags

  - `Jq\RAW` is raw output
  - `Jq\SORT` is object with the keys in sorted order

* variables

  jq variables array.

  - key is variable name
  - value is string of variable value
      - treat strings starting with `@` as JSON strings

**Return Values:**

Returns the result value, or FALSE on error.

---

### Jq\Run::fromFile

``` php
public static fromFile(string $file, string $filter, int $flags = 0, array $variables = []): mixed
```

Get filtering result of the JSON file.

**Parameters:**

* file

  JSON file name.

* filter

  jq filter string.

* flags

  - `Jq\RAW` is raw output
  - `Jq\SORT` is object with the keys in sorted order

* variables

  jq variables array.

  - key is variable name
  - value is string of variable value
      - treat strings starting with `@` as JSON strings

**Return Values:**

Returns the result value, or FALSE on error.

## Examples

* Setting a `Jq\RAW`

```php
$jq = Jq\Input::fromString('{"name": "jq", "version": "0.1.0"}');
print_r($jq->filter('.', Jq\RAW));
echo PHP_EOL;
echo 'NAME: ', $jq->filter('.name', Jq\RAW), PHP_EOL;
echo 'VERSION: ', $jq->filter('.version', Jq\RAW), PHP_EOL;
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
echo 'VERSION: ', Jq\Run::fromString($text, '.version', Jq\RAW), PHP_EOL;
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

- Execute static function with a variables

```php
$text = <<<EOT
[
  {"key": "string", "var": "STRING"},
  {"key": 123, "var": "NUMBER:123"}
]
EOT;
$jq = Jq\Input::fromString($text);
print_r(
  $jq->filter('.')
  // (OR) Jq\Run::fromString($text, '.')
);
print_r(
  $jq->variable('key', 'string')->filter('.[] | select(.key == $key)')
  // (OR) Jq\Run::fromString($text, '.[] | select(.key == $key)', 0, ['key' => 'string'])
);
print_r(
  $jq->variable('key', '@123')->filter('.[] | select(.key == $key)')
  // (OR) Jq\Run::fromString($text, '.[] | select(.key == $key)', 0, ['key' => '@123'])
);
// ['key' => '123'], it is evaluated as a character string, so it does not match
```

The above example will output:

```
Array
(
    [0] => Array
        (
            [key] => string
            [var] => STRING
        )

    [1] => Array
        (
            [key] => 123
            [var] => NUMBER:123
        )

)
Array
(
    [key] => string
    [var] => STRING
)
Array
(
    [key] => 123
    [var] => NUMBER:123
)
```
