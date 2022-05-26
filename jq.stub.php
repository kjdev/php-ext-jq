<?php

namespace Jq;

class Executor {
    /**
     * @return mixed
     */
    public function filter(string $filter, int $flags = 0) {}
    public function variable(string $name, string $value): self {}
    public function variables(): array {}
}

class Input {
    public static function fromString(string $text): Executor {}
    public static function fromFile(string $file): Executor {}
}

class Run {
    /**
     * @return mixed
     */
    public static function fromString(string $text, string $filter, int $flags = 0, array $variables = []) {}
    /**
     * @return mixed
     */
    public static function fromFile(string $file, string $filter, int $flags = 0, array $variables = []) {}
}

