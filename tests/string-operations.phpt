--TEST--
string operations
--SKIPIF--
--FILE--
<?php
$jq = new Jq;

$data = array(
    array('[.[]|startswith("foo")]', '["fo", "foo", "barfoo", "foobar", "barfoob"]'),
    array('[.[]|endswith("foo")]', '["fo", "foo", "barfoo", "foobar", "barfoob"]'),
    array('[.[]|ltrimstr("foo")]', '["fo", "foo", "barfoo", "foobar", "afoo"]'),
    array('[.[]|rtrimstr("foo")]', '["fo", "foo", "barfoo", "foobar", "foob"]'),
    array('[(index(","), rindex(",")), indices(",")]', '"a,bc,def,ghij,klmno"'),
    array('indices(1)', '[0,1,1,2,3,4,1,5]'),
    array('indices([1,2])', '[0,1,2,3,1,4,2,5,1,2,6,7]'),
    array('indices([1,2])', '[1]'),
    array('indices(", ")', '"a,b, cd,e, fgh, ijkl"'),
    array('[.[]|split(",")]', '["a, bc, def, ghij, jklmn, a,b, c,d, e,f", "a,b,c,d, e,f,g,h"]'),
    array('[.[]|split(", ")]', '["a, bc, def, ghij, jklmn, a,b, c,d, e,f", "a,b,c,d, e,f,g,h"]'),
    array('[.[] * 3]', '["a", "ab", "abc"]'),
    array('[.[] / ","]', '["a, bc, def, ghij, jklmn, a,b, c,d, e,f", "a,b,c,d, e,f,g,h"]'),
    array('[.[] / ", "]', '["a, bc, def, ghij, jklmn, a,b, c,d, e,f", "a,b,c,d, e,f,g,h"]'),
    array('map(.[1] as $needle | .[0] | contains($needle))', '[[[],[]], [[1,2,3], [1,2]], [[1,2,3], [3,1]], [[1,2,3], [4]], [[1,2,3], [1,4]]]'),
    array('map(.[1] as $needle | .[0] | contains($needle))', '[[["foobar", "foobaz"], ["baz", "bar"]], [["foobar", "foobaz"], ["foo"]], [["foobar", "foobaz"], ["blap"]]]'),
    array('[({foo: 12, bar:13} | contains({foo: 12})), ({foo: 12} | contains({})), ({foo: 12, bar:13} | contains({baz:14}))]', '{}'),
    array('{foo: {baz: 12, blap: {bar: 13}}, bar: 14} | contains({bar: 14, foo: {blap: {}}})', '{}'),
    array('{foo: {baz: 12, blap: {bar: 13}}, bar: 14} | contains({bar: 14, foo: {blap: {bar: 14}}})', '{}'),
    array('sort', '[42,[2,5,3,11],10,{"a":42,"b":2},{"a":42},true,2,[2,6],"hello",null,[2,5,6],{"a":[],"b":1},"abc","ab",[3,10],{},false,"abcd",null]'),
    array('(sort_by(.b) | sort_by(.a)), sort_by(.a, .b), sort_by(.b, .c), group_by(.b), group_by(.a + .b - .c == 2)', '[{"a": 1, "b": 4, "c": 14}, {"a": 4, "b": 1, "c": 3}, {"a": 1, "b": 4, "c": 3}, {"a": 0, "b": 2, "c": 43}]'),
    array('unique', '[1,2,5,3,5,3,1,3]'),
    array('unique', '[]'),
    array('[min, max, min_by(.[1]), max_by(.[1]), min_by(.[2]), max_by(.[2])]', '[[4,2,"a"],[3,1,"a"],[2,4,"a"],[1,3,"a"]]'),
    array('[min,max,min_by(.),max_by(.)]', '[]'),
    array('.foo[.baz]', '{"foo":{"bar":4},"baz":"bar"}'),
    array('.[] | .error = "no, it\'s OK"', '[{"error":true}]'),
    array('[{a:1}] | .[] | .a=999', 'null'),
    array('to_entries', '{"a": 1, "b": 2}'),
    array('from_entries', '[{"key":"a", "value":1}, {"key":"b", "value":2}]'),
    array('with_entries(.key |= "KEY_" + .)', '{"a": 1, "b": 2}'),
    array('map(has("foo"))', '[{"foo": 42}, {}]'),
    array('map(has(2))', '[[0,1], ["a","b","c"]]'),
    array('keys', '[42,3,35]'),
    array('[][.]', '1000000000000000000'),
    array('map([1,2][0:.])', '[-1, 1, 2, 3, 1000000000000000000]'),
);

foreach ($data as $value) {
    echo "== ", $value[0], PHP_EOL;
    $jq->load($value[1]);
    var_dump($jq->filter($value[0]));
    var_dump($jq->filter($value[0], Jq::RAW));
}

--EXPECTF--
== [.[]|startswith("foo")]
array(5) {
  [0]=>
  bool(false)
  [1]=>
  bool(true)
  [2]=>
  bool(false)
  [3]=>
  bool(true)
  [4]=>
  bool(false)
}
string(29) "[false,true,false,true,false]"
== [.[]|endswith("foo")]
array(5) {
  [0]=>
  bool(false)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
  [4]=>
  bool(false)
}
string(29) "[false,true,true,false,false]"
== [.[]|ltrimstr("foo")]
array(5) {
  [0]=>
  string(2) "fo"
  [1]=>
  string(0) ""
  [2]=>
  string(6) "barfoo"
  [3]=>
  string(3) "bar"
  [4]=>
  string(4) "afoo"
}
string(31) "["fo","","barfoo","bar","afoo"]"
== [.[]|rtrimstr("foo")]
array(5) {
  [0]=>
  string(2) "fo"
  [1]=>
  string(0) ""
  [2]=>
  string(3) "bar"
  [3]=>
  string(6) "foobar"
  [4]=>
  string(4) "foob"
}
string(31) "["fo","","bar","foobar","foob"]"
== [(index(","), rindex(",")), indices(",")]
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(13)
  [2]=>
  array(4) {
    [0]=>
    int(1)
    [1]=>
    int(4)
    [2]=>
    int(8)
    [3]=>
    int(13)
  }
}
string(17) "[1,13,[1,4,8,13]]"
== indices(1)
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(6)
}
string(7) "[1,2,6]"
== indices([1,2])
array(2) {
  [0]=>
  int(1)
  [1]=>
  int(8)
}
string(5) "[1,8]"
== indices([1,2])
array(0) {
}
string(2) "[]"
== indices(", ")
array(3) {
  [0]=>
  int(3)
  [1]=>
  int(9)
  [2]=>
  int(14)
}
string(8) "[3,9,14]"
== [.[]|split(",")]
array(2) {
  [0]=>
  array(11) {
    [0]=>
    string(1) "a"
    [1]=>
    string(3) " bc"
    [2]=>
    string(4) " def"
    [3]=>
    string(5) " ghij"
    [4]=>
    string(6) " jklmn"
    [5]=>
    string(2) " a"
    [6]=>
    string(1) "b"
    [7]=>
    string(2) " c"
    [8]=>
    string(1) "d"
    [9]=>
    string(2) " e"
    [10]=>
    string(1) "f"
  }
  [1]=>
  array(8) {
    [0]=>
    string(1) "a"
    [1]=>
    string(1) "b"
    [2]=>
    string(1) "c"
    [3]=>
    string(1) "d"
    [4]=>
    string(2) " e"
    [5]=>
    string(1) "f"
    [6]=>
    string(1) "g"
    [7]=>
    string(1) "h"
  }
}
string(99) "[["a"," bc"," def"," ghij"," jklmn"," a","b"," c","d"," e","f"],["a","b","c","d"," e","f","g","h"]]"
== [.[]|split(", ")]
array(2) {
  [0]=>
  array(8) {
    [0]=>
    string(1) "a"
    [1]=>
    string(2) "bc"
    [2]=>
    string(3) "def"
    [3]=>
    string(4) "ghij"
    [4]=>
    string(5) "jklmn"
    [5]=>
    string(3) "a,b"
    [6]=>
    string(3) "c,d"
    [7]=>
    string(3) "e,f"
  }
  [1]=>
  array(2) {
    [0]=>
    string(7) "a,b,c,d"
    [1]=>
    string(7) "e,f,g,h"
  }
}
string(73) "[["a","bc","def","ghij","jklmn","a,b","c,d","e,f"],["a,b,c,d","e,f,g,h"]]"
== [.[] * 3]
array(3) {
  [0]=>
  string(3) "aaa"
  [1]=>
  string(6) "ababab"
  [2]=>
  string(9) "abcabcabc"
}
string(28) "["aaa","ababab","abcabcabc"]"
== [.[] / ","]
array(2) {
  [0]=>
  array(11) {
    [0]=>
    string(1) "a"
    [1]=>
    string(3) " bc"
    [2]=>
    string(4) " def"
    [3]=>
    string(5) " ghij"
    [4]=>
    string(6) " jklmn"
    [5]=>
    string(2) " a"
    [6]=>
    string(1) "b"
    [7]=>
    string(2) " c"
    [8]=>
    string(1) "d"
    [9]=>
    string(2) " e"
    [10]=>
    string(1) "f"
  }
  [1]=>
  array(8) {
    [0]=>
    string(1) "a"
    [1]=>
    string(1) "b"
    [2]=>
    string(1) "c"
    [3]=>
    string(1) "d"
    [4]=>
    string(2) " e"
    [5]=>
    string(1) "f"
    [6]=>
    string(1) "g"
    [7]=>
    string(1) "h"
  }
}
string(99) "[["a"," bc"," def"," ghij"," jklmn"," a","b"," c","d"," e","f"],["a","b","c","d"," e","f","g","h"]]"
== [.[] / ", "]
array(2) {
  [0]=>
  array(8) {
    [0]=>
    string(1) "a"
    [1]=>
    string(2) "bc"
    [2]=>
    string(3) "def"
    [3]=>
    string(4) "ghij"
    [4]=>
    string(5) "jklmn"
    [5]=>
    string(3) "a,b"
    [6]=>
    string(3) "c,d"
    [7]=>
    string(3) "e,f"
  }
  [1]=>
  array(2) {
    [0]=>
    string(7) "a,b,c,d"
    [1]=>
    string(7) "e,f,g,h"
  }
}
string(73) "[["a","bc","def","ghij","jklmn","a,b","c,d","e,f"],["a,b,c,d","e,f,g,h"]]"
== map(.[1] as $needle | .[0] | contains($needle))
array(5) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(true)
  [3]=>
  bool(false)
  [4]=>
  bool(false)
}
string(28) "[true,true,true,false,false]"
== map(.[1] as $needle | .[0] | contains($needle))
array(3) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(false)
}
string(17) "[true,true,false]"
== [({foo: 12, bar:13} | contains({foo: 12})), ({foo: 12} | contains({})), ({foo: 12, bar:13} | contains({baz:14}))]
array(3) {
  [0]=>
  bool(true)
  [1]=>
  bool(true)
  [2]=>
  bool(false)
}
string(17) "[true,true,false]"
== {foo: {baz: 12, blap: {bar: 13}}, bar: 14} | contains({bar: 14, foo: {blap: {}}})
bool(true)
string(4) "true"
== {foo: {baz: 12, blap: {bar: 13}}, bar: 14} | contains({bar: 14, foo: {blap: {bar: 14}}})
bool(false)
string(5) "false"
== sort
array(19) {
  [0]=>
  NULL
  [1]=>
  NULL
  [2]=>
  bool(false)
  [3]=>
  bool(true)
  [4]=>
  int(2)
  [5]=>
  int(10)
  [6]=>
  int(42)
  [7]=>
  string(2) "ab"
  [8]=>
  string(3) "abc"
  [9]=>
  string(4) "abcd"
  [10]=>
  string(5) "hello"
  [11]=>
  array(4) {
    [0]=>
    int(2)
    [1]=>
    int(5)
    [2]=>
    int(3)
    [3]=>
    int(11)
  }
  [12]=>
  array(3) {
    [0]=>
    int(2)
    [1]=>
    int(5)
    [2]=>
    int(6)
  }
  [13]=>
  array(2) {
    [0]=>
    int(2)
    [1]=>
    int(6)
  }
  [14]=>
  array(2) {
    [0]=>
    int(3)
    [1]=>
    int(10)
  }
  [15]=>
  array(0) {
  }
  [16]=>
  array(1) {
    ["a"]=>
    int(42)
  }
  [17]=>
  array(2) {
    ["a"]=>
    int(42)
    ["b"]=>
    int(2)
  }
  [18]=>
  array(2) {
    ["a"]=>
    array(0) {
    }
    ["b"]=>
    int(1)
  }
}
string(130) "[null,null,false,true,2,10,42,"ab","abc","abcd","hello",[2,5,3,11],[2,5,6],[2,6],[3,10],{},{"a":42},{"a":42,"b":2},{"a":[],"b":1}]"
== (sort_by(.b) | sort_by(.a)), sort_by(.a, .b), sort_by(.b, .c), group_by(.b), group_by(.a + .b - .c == 2)
array(5) {
  [0]=>
  array(4) {
    [0]=>
    array(3) {
      ["a"]=>
      int(0)
      ["b"]=>
      int(2)
      ["c"]=>
      int(43)
    }
    [1]=>
    array(3) {
      ["a"]=>
      int(1)
      ["b"]=>
      int(4)
      ["c"]=>
      int(14)
    }
    [2]=>
    array(3) {
      ["a"]=>
      int(1)
      ["b"]=>
      int(4)
      ["c"]=>
      int(3)
    }
    [3]=>
    array(3) {
      ["a"]=>
      int(4)
      ["b"]=>
      int(1)
      ["c"]=>
      int(3)
    }
  }
  [1]=>
  array(4) {
    [0]=>
    array(3) {
      ["a"]=>
      int(0)
      ["b"]=>
      int(2)
      ["c"]=>
      int(43)
    }
    [1]=>
    array(3) {
      ["a"]=>
      int(1)
      ["b"]=>
      int(4)
      ["c"]=>
      int(14)
    }
    [2]=>
    array(3) {
      ["a"]=>
      int(1)
      ["b"]=>
      int(4)
      ["c"]=>
      int(3)
    }
    [3]=>
    array(3) {
      ["a"]=>
      int(4)
      ["b"]=>
      int(1)
      ["c"]=>
      int(3)
    }
  }
  [2]=>
  array(4) {
    [0]=>
    array(3) {
      ["a"]=>
      int(4)
      ["b"]=>
      int(1)
      ["c"]=>
      int(3)
    }
    [1]=>
    array(3) {
      ["a"]=>
      int(0)
      ["b"]=>
      int(2)
      ["c"]=>
      int(43)
    }
    [2]=>
    array(3) {
      ["a"]=>
      int(1)
      ["b"]=>
      int(4)
      ["c"]=>
      int(3)
    }
    [3]=>
    array(3) {
      ["a"]=>
      int(1)
      ["b"]=>
      int(4)
      ["c"]=>
      int(14)
    }
  }
  [3]=>
  array(3) {
    [0]=>
    array(1) {
      [0]=>
      array(3) {
        ["a"]=>
        int(4)
        ["b"]=>
        int(1)
        ["c"]=>
        int(3)
      }
    }
    [1]=>
    array(1) {
      [0]=>
      array(3) {
        ["a"]=>
        int(0)
        ["b"]=>
        int(2)
        ["c"]=>
        int(43)
      }
    }
    [2]=>
    array(2) {
      [0]=>
      array(3) {
        ["a"]=>
        int(1)
        ["b"]=>
        int(4)
        ["c"]=>
        int(14)
      }
      [1]=>
      array(3) {
        ["a"]=>
        int(1)
        ["b"]=>
        int(4)
        ["c"]=>
        int(3)
      }
    }
  }
  [4]=>
  array(2) {
    [0]=>
    array(2) {
      [0]=>
      array(3) {
        ["a"]=>
        int(1)
        ["b"]=>
        int(4)
        ["c"]=>
        int(14)
      }
      [1]=>
      array(3) {
        ["a"]=>
        int(0)
        ["b"]=>
        int(2)
        ["c"]=>
        int(43)
      }
    }
    [1]=>
    array(2) {
      [0]=>
      array(3) {
        ["a"]=>
        int(4)
        ["b"]=>
        int(1)
        ["c"]=>
        int(3)
      }
      [1]=>
      array(3) {
        ["a"]=>
        int(1)
        ["b"]=>
        int(4)
        ["c"]=>
        int(3)
      }
    }
  }
}
array(5) {
  [0]=>
  string(83) "[{"a":0,"b":2,"c":43},{"a":1,"b":4,"c":14},{"a":1,"b":4,"c":3},{"a":4,"b":1,"c":3}]"
  [1]=>
  string(83) "[{"a":0,"b":2,"c":43},{"a":1,"b":4,"c":14},{"a":1,"b":4,"c":3},{"a":4,"b":1,"c":3}]"
  [2]=>
  string(83) "[{"a":4,"b":1,"c":3},{"a":0,"b":2,"c":43},{"a":1,"b":4,"c":3},{"a":1,"b":4,"c":14}]"
  [3]=>
  string(89) "[[{"a":4,"b":1,"c":3}],[{"a":0,"b":2,"c":43}],[{"a":1,"b":4,"c":14},{"a":1,"b":4,"c":3}]]"
  [4]=>
  string(87) "[[{"a":1,"b":4,"c":14},{"a":0,"b":2,"c":43}],[{"a":4,"b":1,"c":3},{"a":1,"b":4,"c":3}]]"
}
== unique
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(5)
}
string(9) "[1,2,3,5]"
== unique
array(0) {
}
string(2) "[]"
== [min, max, min_by(.[1]), max_by(.[1]), min_by(.[2]), max_by(.[2])]
array(6) {
  [0]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(3)
    [2]=>
    string(1) "a"
  }
  [1]=>
  array(3) {
    [0]=>
    int(4)
    [1]=>
    int(2)
    [2]=>
    string(1) "a"
  }
  [2]=>
  array(3) {
    [0]=>
    int(3)
    [1]=>
    int(1)
    [2]=>
    string(1) "a"
  }
  [3]=>
  array(3) {
    [0]=>
    int(2)
    [1]=>
    int(4)
    [2]=>
    string(1) "a"
  }
  [4]=>
  array(3) {
    [0]=>
    int(4)
    [1]=>
    int(2)
    [2]=>
    string(1) "a"
  }
  [5]=>
  array(3) {
    [0]=>
    int(1)
    [1]=>
    int(3)
    [2]=>
    string(1) "a"
  }
}
string(61) "[[1,3,"a"],[4,2,"a"],[3,1,"a"],[2,4,"a"],[4,2,"a"],[1,3,"a"]]"
== [min,max,min_by(.),max_by(.)]
array(4) {
  [0]=>
  NULL
  [1]=>
  NULL
  [2]=>
  NULL
  [3]=>
  NULL
}
string(21) "[null,null,null,null]"
== .foo[.baz]
int(4)
string(1) "4"
== .[] | .error = "no, it's OK"
array(1) {
  ["error"]=>
  string(11) "no, it's OK"
}
string(23) "{"error":"no, it's OK"}"
== [{a:1}] | .[] | .a=999
array(1) {
  ["a"]=>
  int(999)
}
string(9) "{"a":999}"
== to_entries
array(2) {
  [0]=>
  array(2) {
    ["key"]=>
    string(1) "a"
    ["value"]=>
    int(1)
  }
  [1]=>
  array(2) {
    ["key"]=>
    string(1) "b"
    ["value"]=>
    int(2)
  }
}
string(45) "[{"key":"a","value":1},{"key":"b","value":2}]"
== from_entries
array(2) {
  ["a"]=>
  int(1)
  ["b"]=>
  int(2)
}
string(13) "{"a":1,"b":2}"
== with_entries(.key |= "KEY_" + .)
array(2) {
  ["KEY_a"]=>
  int(1)
  ["KEY_b"]=>
  int(2)
}
string(21) "{"KEY_a":1,"KEY_b":2}"
== map(has("foo"))
array(2) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
}
string(12) "[true,false]"
== map(has(2))
array(2) {
  [0]=>
  bool(false)
  [1]=>
  bool(true)
}
string(12) "[false,true]"
== keys
array(3) {
  [0]=>
  int(0)
  [1]=>
  int(1)
  [2]=>
  int(2)
}
string(7) "[0,1,2]"
== [][.]
NULL
string(4) "null"
== map([1,2][0:.])
array(5) {
  [0]=>
  array(1) {
    [0]=>
    int(1)
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
    int(2)
  }
  [3]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
  [4]=>
  array(2) {
    [0]=>
    int(1)
    [1]=>
    int(2)
  }
}
string(27) "[[1],[1],[1,2],[1,2],[1,2]]"
