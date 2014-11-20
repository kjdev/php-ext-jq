--TEST--
paths
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('path(.foo[0,1])', 'null'),
    array('path(.[] | select(.>3))', '[1,5,3]'),
    array('path(.)', '42'),
    array('[paths]', '[1,[[],{"a":2}]]'),
    array('[leaf_paths]', '[1,[[],{"a":2}]]'),
    array('["foo",1] as $p | getpath($p), setpath($p; 20), delpaths([$p])', '{"bar": 42, "foo": ["a", "b", "c", "d"]}'),
    array('map(getpath([2])), map(setpath([2]; 42)), map(delpaths([[2]]))', '[[0], [0,1], [0,1,2]]'),
    array('map(delpaths([[0,"foo"]]))', '[[{"foo":2, "x":1}], [{"bar":2}]]'),
    array('["foo",1] as $p | getpath($p), setpath($p; 20), delpaths([$p])', '{"bar":false}'),
    array('delpaths([[-200]])', '[1,2,3]'),
    array('del(.), del(empty), del((.foo,.bar,.baz) | .[2,3,0]), del(.foo[0], .bar[0], .foo, .baz.bar[0].x)', '{"foo": [0,1,2,3,4], "bar": [0,1]}'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== path(.foo[0,1])
array(2) {
  [0]=>
  array(2) {
    [0]=>
    string(3) "foo"
    [1]=>
    int(0)
  }
  [1]=>
  array(2) {
    [0]=>
    string(3) "foo"
    [1]=>
    int(1)
  }
}
array(2) {
  [0]=>
  string(9) "["foo",0]"
  [1]=>
  string(9) "["foo",1]"
}
== path(.[] | select(.>3))
array(1) {
  [0]=>
  int(1)
}
string(3) "[1]"
== path(.)
array(0) {
}
string(2) "[]"
== [paths]
array(5) {
  [0]=>
  array(1) {
    [0]=>
    int(0)
  }
  [1]=>
  array(1) {
    [0]=>
    int(1)
  }
  [2]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(0)
  }
  [3]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(1)
  }
  [4]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(1)
    [2]=>
    string(1) "a"
  }
}
string(31) "[[0],[1],[1,0],[1,1],[1,1,"a"]]"
== [leaf_paths]
array(2) {
  [0]=>
  array(1) {
    [0]=>
    int(0)
  }
  [1]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(1)
    [2]=>
    string(1) "a"
  }
}
string(15) "[[0],[1,1,"a"]]"
== ["foo",1] as $p | getpath($p), setpath($p; 20), delpaths([$p])
array(3) {
  [0]=>
  string(1) "b"
  [1]=>
  array(2) {
    ["bar"]=>
    int(42)
    ["foo"]=>
    array(4) {
      [0]=>
      string(1) "a"
      [1]=>
      int(20)
      [2]=>
      string(1) "c"
      [3]=>
      string(1) "d"
    }
  }
  [2]=>
  array(2) {
    ["bar"]=>
    int(42)
    ["foo"]=>
    array(3) {
      [0]=>
      string(1) "a"
      [1]=>
      string(1) "c"
      [2]=>
      string(1) "d"
    }
  }
}
array(3) {
  [0]=>
  string(1) "b"
  [1]=>
  string(33) "{"bar":42,"foo":["a",20,"c","d"]}"
  [2]=>
  string(30) "{"bar":42,"foo":["a","c","d"]}"
}
== map(getpath([2])), map(setpath([2]; 42)), map(delpaths([[2]]))
array(3) {
  [0]=>
  array(3) {
    [0]=>
    NULL
    [1]=>
    NULL
    [2]=>
    int(2)
  }
  [1]=>
  array(3) {
    [0]=>
    array(3) {
      [0]=>
      int(0)
      [1]=>
      NULL
      [2]=>
      int(42)
    }
    [1]=>
    array(3) {
      [0]=>
      int(0)
      [1]=>
      int(1)
      [2]=>
      int(42)
    }
    [2]=>
    array(3) {
      [0]=>
      int(0)
      [1]=>
      int(1)
      [2]=>
      int(42)
    }
  }
  [2]=>
  array(3) {
    [0]=>
    array(1) {
      [0]=>
      int(0)
    }
    [1]=>
    array(2) {
      [0]=>
      int(0)
      [1]=>
      int(1)
    }
    [2]=>
    array(2) {
      [0]=>
      int(0)
      [1]=>
      int(1)
    }
  }
}
array(3) {
  [0]=>
  string(13) "[null,null,2]"
  [1]=>
  string(31) "[[0,null,42],[0,1,42],[0,1,42]]"
  [2]=>
  string(17) "[[0],[0,1],[0,1]]"
}
== map(delpaths([[0,"foo"]]))
array(2) {
  [0]=>
  array(1) {
    [0]=>
    array(1) {
      ["x"]=>
      int(1)
    }
  }
  [1]=>
  array(1) {
    [0]=>
    array(1) {
      ["bar"]=>
      int(2)
    }
  }
}
string(23) "[[{"x":1}],[{"bar":2}]]"
== ["foo",1] as $p | getpath($p), setpath($p; 20), delpaths([$p])
array(3) {
  [0]=>
  NULL
  [1]=>
  array(2) {
    ["bar"]=>
    bool(false)
    ["foo"]=>
    array(2) {
      [0]=>
      NULL
      [1]=>
      int(20)
    }
  }
  [2]=>
  array(1) {
    ["bar"]=>
    bool(false)
  }
}
array(3) {
  [0]=>
  string(4) "null"
  [1]=>
  string(29) "{"bar":false,"foo":[null,20]}"
  [2]=>
  string(13) "{"bar":false}"
}
== delpaths([[-200]])
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
}
string(7) "[1,2,3]"
== del(.), del(empty), del((.foo,.bar,.baz) | .[2,3,0]), del(.foo[0], .bar[0], .foo, .baz.bar[0].x)
array(4) {
  [0]=>
  NULL
  [1]=>
  array(2) {
    ["foo"]=>
    array(5) {
      [0]=>
      int(0)
      [1]=>
      int(1)
      [2]=>
      int(2)
      [3]=>
      int(3)
      [4]=>
      int(4)
    }
    ["bar"]=>
    array(2) {
      [0]=>
      int(0)
      [1]=>
      int(1)
    }
  }
  [2]=>
  array(2) {
    ["foo"]=>
    array(2) {
      [0]=>
      int(1)
      [1]=>
      int(4)
    }
    ["bar"]=>
    array(1) {
      [0]=>
      int(1)
    }
  }
  [3]=>
  array(1) {
    ["bar"]=>
    array(1) {
      [0]=>
      int(1)
    }
  }
}
array(4) {
  [0]=>
  string(4) "null"
  [1]=>
  string(31) "{"foo":[0,1,2,3,4],"bar":[0,1]}"
  [2]=>
  string(23) "{"foo":[1,4],"bar":[1]}"
  [3]=>
  string(11) "{"bar":[1]}"
}
