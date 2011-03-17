<?php
$name = 'pasty';
$url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . rtrim($_SERVER['REQUEST_URI'], '/');
$shell = strpos($_SERVER['HTTP_USER_AGENT'], 'curl') === 0;
$id = substr(base_convert(bin2hex(openssl_random_pseudo_bytes(20)), 16, 36), 0, 10);
if ($_FILES['f']) {
	move_uploaded_file($_FILES['f']['tmp_name'], ".$name/$id");
	echo "$url/$id\n";
} elseif (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '/') {
	list ($id, $lang) = split('/', substr($_SERVER['PATH_INFO'], 1));
	$file = ".$name/$id";
	if (!preg_match('/^\w{10}$/', $id) || !file_exists($file)) {
		echo "404\n";
	} elseif ($lang && preg_match('/^\w{2,10}$/', $lang)) {
		$format = $shell ? '-f terminal' : '-f html -O full,linenos=1';
		passthru("pygmentize -l $lang $format $file");
	} else {
		$content = file_get_contents($file);
		echo($shell ? "$content\n" : '<pre>' . htmlentities($content) . '</pre>');
	}
} else {
	$name = basename($_SERVER['SCRIPT_NAME'], '.php');
	$uname = strtoupper($name);
	$link = $shell ? '/language' : '<a href="http://pygments.org/docs/lexers/">/language</a>';
$help = <<<HELP
$name(1)                          $uname                          $name(1)

NAME
    $name: command line pastebin

SYNOPSIS
    command | curl -Ff=@- $url

DESCRIPTION
    add $link to resulting url for line numbers and syntax highlighting

EXAMPLES
    $ cat script.sh | curl -Ff=@- $url
    $url/$id
    $ firefox $url/$id/sh

HELP;
	echo($shell ? $help : "<html><head><style>a{ text-decoration: none; }</style></head><body><pre>\n$help\n</pre></body>");
}
