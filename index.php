<?php

define('PASTES_FILE',     '.pastes');
define('PASTE_ID',                0);
define('PASTE_TIMESTAMP',         1);
define('PASTE_SUBJECT',           2);
define('PASTE_LANGUAGE',          3);
define('PASTE_TEXT',              4);

if (get_magic_quotes_gpc()) {
	foreach ($_GET as $key => $val) { $_GET[$key] = stripslashes($val); }
	foreach ($_POST as $key => $val) { $_POST[$key] = stripslashes($val); }
}
if (get_magic_quotes_runtime()) { set_magic_quotes_runtime(0); }

require 'flatfile.php';
$db = new Flatfile();
$db->datadir = './';

$languages = array('Plain Text' => 'plain',
				   'C++' => 'cpp',
				   'C#' => 'csharp',
				   'CSS' => 'css',
				   'Delphi' => 'delphi',
				   'Java' => 'java',
				   'JavaScript' => 'js',
				   'PHP' => 'php',
				   'Python' => 'python',
				   'Ruby' => 'ruby',
				   'SQL' => 'sql',
				   'VB' => 'vb',
				   'XML/HTML' => 'xml');
				   
function escapeFlatFile($text) {
	return htmlentities(str_replace("\t", chr(26), str_replace("\n", chr(27), $text)));
}

function unescapeFlatFile($text) {
	return str_replace(chr(26), "\t", str_replace(chr(27), "\n", $text));
}

if (isset($_POST['text'])) {
	$newpaste = array();
	$newpaste[PASTE_ID] = 0;
	$newpaste[PASTE_TIMESTAMP] = time();
	$newpaste[PASTE_SUBJECT] = $_POST['subject'];
	$newpaste[PASTE_LANGUAGE] = "plain";
	if (in_array($_POST['language'], $languages)) {
		$newpaste[PASTE_LANGUAGE] = $_POST['language'];
	}
	$newpaste[PASTE_TEXT] = escapeFlatFile($_POST['text']);

	$id = $db->insertWithAutoId(PASTES_FILE, PASTE_ID, $newpaste);
	die('<meta http-equiv="refresh" content="0;url=?viewpaste=' . $id . '">');
}

echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"> 
<html> 
	<head> 
		<title> 
			Flat Paste
		</title> 
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8"> 
		<script type="text/javascript" src="js/shCore.js"></script> 
		<script type="text/javascript" src="js/shBrushBash.js"></script> 
		<script type="text/javascript" src="js/shBrushCpp.js"></script> 
		<script type="text/javascript" src="js/shBrushCSharp.js"></script> 
		<script type="text/javascript" src="js/shBrushCss.js"></script> 
		<script type="text/javascript" src="js/shBrushDelphi.js"></script> 
		<script type="text/javascript" src="js/shBrushDiff.js"></script> 
		<script type="text/javascript" src="js/shBrushGroovy.js"></script> 
		<script type="text/javascript" src="js/shBrushJava.js"></script> 
		<script type="text/javascript" src="js/shBrushJScript.js"></script> 
		<script type="text/javascript" src="js/shBrushPhp.js"></script> 
		<script type="text/javascript" src="js/shBrushPlain.js"></script> 
		<script type="text/javascript" src="js/shBrushPython.js"></script> 
		<script type="text/javascript" src="js/shBrushRuby.js"></script> 
		<script type="text/javascript" src="js/shBrushScala.js"></script> 
		<script type="text/javascript" src="js/shBrushSql.js"></script> 
		<script type="text/javascript" src="js/shBrushVb.js"></script> 
		<script type="text/javascript" src="js/shBrushXml.js"></script> 
		<link type="text/css" rel="stylesheet" href="css/shCore.css"/> 
		<link type="text/css" rel="stylesheet" href="css/shThemeDefault.css"/> 
		<script type="text/javascript"> 
			SyntaxHighlighter.config.clipboardSwf = 'js/clipboard.swf';
			SyntaxHighlighter.all();
		</script>
	</head>
	<body onload="document.pasteform.text.focus();">
EOF;

if (isset($_GET['viewpaste'])) {
	$paste = $db->selectUnique(PASTES_FILE, PASTE_ID, $_GET['viewpaste']);
	if ($paste) {
		$paste[PASTE_TEXT] = unescapeFlatFile($paste[PASTE_TEXT]);
		if ($paste[PASTE_SUBJECT] != '') {
			echo <<<EOF
		<h1 style="text-align: center;">
			${paste[PASTE_SUBJECT]}
		</h1>
EOF;
		}

		echo <<<EOF
		<pre class="brush: ${paste[PASTE_LANGUAGE]};">${paste[PASTE_TEXT]}</pre>
EOF;
	} else {
		echo '<h2 style="color: red;">Invalid Paste ID</h2>';
	}
	echo '<br><small><a href="?">go back</a></small>';
} else {
	echo <<<EOF
		<form action="?" method="post" name="pasteform" id="pasteform"> 
		<table border="0">
			<tr>
				<td>
					<label for="subject">
						Subject:
					</label>
				</td>
				<td>
					<input type="text" name="subject" accesskey="s">
				</td>
			</tr>
			<tr>
				<td>
					<label for="language">
						Paste As:
					</label>
				</td>
				<td>
					<select name="language" accesskey="l">
EOF;
	foreach ($languages as $language_full => $language_abbr) {
		if ($language_abbr == 'plain') {
			echo <<<EOF
						<optgroup label="Plain">
							<option value="$language_abbr">$language_full</option>
						</optgroup>
						<optgroup label="Highlighted">
EOF;
		} else {
			echo <<<EOF
							<option value="$language_abbr">$language_full</option>
EOF;
		}
	}
	echo <<<EOF
						</optgroup>
					</select>
				</td>
			</tr>
		</table>
		<textarea name="text" id="text" rows="20" cols="88" accesskey="t"></textarea><br>
		<input type="submit" value="Paste" accesskey="p" style="font-size: 15px;height: 28px;margin: 0.2em;">
		</form>
EOF;
}

echo <<<EOF
	</body>
</html>
EOF;
?>