--TEST--
Jq\Run::fromString() static method
--SKIPIF--
--FILE--
<?php
$text = <<<EOT
[
  {
    "id": 4343183,
    "name": "apache-mod-coffee",
    "full_name": "kjdev/apache-mod-coffee",
    "owner": {
      "login": "kjdev",
      "id": 465132,
      "avatar_url": "https://avatars.githubusercontent.com/u/465132?v=3",
      "gravatar_id": "",
      "url": "https://api.github.com/users/kjdev",
      "html_url": "https://github.com/kjdev",
      "followers_url": "https://api.github.com/users/kjdev/followers",
      "following_url": "https://api.github.com/users/kjdev/following{/other_user}",
      "gists_url": "https://api.github.com/users/kjdev/gists{/gist_id}",
      "starred_url": "https://api.github.com/users/kjdev/starred{/owner}{/repo}",
      "subscriptions_url": "https://api.github.com/users/kjdev/subscriptions",
      "organizations_url": "https://api.github.com/users/kjdev/orgs",
      "repos_url": "https://api.github.com/users/kjdev/repos",
      "events_url": "https://api.github.com/users/kjdev/events{/privacy}",
      "received_events_url": "https://api.github.com/users/kjdev/received_events",
      "type": "User",
      "site_admin": false
    },
    "private": false,
    "html_url": "https://github.com/kjdev/apache-mod-coffee",
    "description": "mod_coffee is CoffeeScript handler module for Apache HTTPD Server. ",
    "fork": false,
    "url": "https://api.github.com/repos/kjdev/apache-mod-coffee",
    "forks_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/forks",
    "keys_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/keys{/key_id}",
    "collaborators_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/collaborators{/collaborator}",
    "teams_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/teams",
    "hooks_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/hooks",
    "issue_events_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/issues/events{/number}",
    "events_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/events",
    "assignees_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/assignees{/user}",
    "branches_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/branches{/branch}",
    "tags_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/tags",
    "blobs_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/git/blobs{/sha}",
    "git_tags_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/git/tags{/sha}",
    "git_refs_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/git/refs{/sha}",
    "trees_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/git/trees{/sha}",
    "statuses_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/statuses/{sha}",
    "languages_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/languages",
    "stargazers_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/stargazers",
    "contributors_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/contributors",
    "subscribers_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/subscribers",
    "subscription_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/subscription",
    "commits_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/commits{/sha}",
    "git_commits_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/git/commits{/sha}",
    "comments_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/comments{/number}",
    "issue_comment_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/issues/comments/{number}",
    "contents_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/contents/{+path}",
    "compare_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/compare/{base}...{head}",
    "merges_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/merges",
    "archive_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/{archive_format}{/ref}",
    "downloads_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/downloads",
    "issues_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/issues{/number}",
    "pulls_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/pulls{/number}",
    "milestones_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/milestones{/number}",
    "notifications_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/notifications{?since,all,participating}",
    "labels_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/labels{/name}",
    "releases_url": "https://api.github.com/repos/kjdev/apache-mod-coffee/releases{/id}",
    "created_at": "2012-05-16T03:31:27Z",
    "updated_at": "2013-10-24T01:20:49Z",
    "pushed_at": "2012-05-16T03:32:01Z",
    "git_url": "git://github.com/kjdev/apache-mod-coffee.git",
    "ssh_url": "git@github.com:kjdev/apache-mod-coffee.git",
    "clone_url": "https://github.com/kjdev/apache-mod-coffee.git",
    "svn_url": "https://github.com/kjdev/apache-mod-coffee",
    "homepage": null,
    "size": 152,
    "stargazers_count": 1,
    "watchers_count": 1,
    "language": "C",
    "has_issues": true,
    "has_downloads": true,
    "has_wiki": true,
    "has_pages": false,
    "forks_count": 0,
    "mirror_url": null,
    "open_issues_count": 0,
    "forks": 0,
    "open_issues": 0,
    "watchers": 1,
    "default_branch": "master"
  }
]
EOT;

$filter = '.[0] | {name: .name, owner: .owner.login}';
echo "== ", $filter, PHP_EOL;

var_dump(Jq\Run::fromString($text, $filter));
var_dump(Jq\Run::fromString($text, $filter, Jq\RAW));
try {
  var_dump(Jq\Run::fromString('text', ''));
} catch (Throwable $e) {
  echo get_class($e), PHP_EOL;
  echo $e->getMessage(), PHP_EOL;
}
--EXPECTF--
== .[0] | {name: .name, owner: .owner.login}
array(2) {
  ["name"]=>
  string(17) "apache-mod-coffee"
  ["owner"]=>
  string(5) "kjdev"
}
string(44) "{"name":"apache-mod-coffee","owner":"kjdev"}"
Jq\Exception
failed to load json.
