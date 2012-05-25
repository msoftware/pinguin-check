<?php

require_once ('config.php');
require_once ('logging.php');
require_once ('cache.php');
require_once ('google.php');

error_reporting (E_ERROR);

if (isset ($_GET['key']))
{
	$key = $_GET['key'];
} else {
	$key = "";
}


if (strlen ($key) > 0)
{
	$cache = new Cache();
	$pinguin = $cache->loadFromCache ($key);
	if ($pinguin === false)
	{
		$pinguin['after']  = getGoogleData ($key);
		$pinguin['before'] = getGoogleData ($key . " -amazon.de");
		$cache->saveInCache ($key, $pinguin);
	}

	$max = max (count ($pinguin['after']), count ($pinguin['before']));

	$results = "<table cellspacing=1 cellpadding=4 border=0>";
	$results .= "<tr><td></td><td><b>Vor Pinguin</b></td><td><b>Nach Pinguin</b></td></tr>";
	for ($i = 0; $i < $max; $i++)
	{
		if (isset ($pinguin['before'][$i]))
		{
			$before = trim ($pinguin['before'][$i]['url']);
		} else {
			$before = "-";
		}
		if (isset ($pinguin['after'][$i]))
		{
			$after = trim ($pinguin['after'][$i]['url']);
		} else {
			$after = "-";
		}
		if ($before == $after)
		{
			$bg = "#AAFFAA";
		} else {
			$bg = "#FFAAAA";
		}
		$pos = $i + 1;
		$results .= "<tr bgcolor='$bg'><td class='pinguin'>$pos:</td>" .
			    "<td class='pinguin'>$before</td>" .
			    "<td class='pinguin'>$after</td></tr>";
	}
	$results .= "</table>";
	$title = "für den Suchbegriff '<b>$key</b>'";
} else {
	$title = "";
	$results = <<<EOF
Hinweis: Dieser Vorgang kann mehrere Minuten dauern. <hr>
Dieser Test basiert auf der Theorie von 
<a href='http://www.seroundtable.com/google-penguin-check-15165.html'>
http://www.seroundtable.com/</a>. <br>Infos in deutscher Sprache kann man unter 
<a href='http://www.seokratie.de/wurdet-ihr-vom-pinguin-abgestraft-der-ultimative-test/'>
http://www.seokratie.de/</a> finden.
<hr>
EOF;
}

?>
<html>
<head>
<title>Google Pinguin Update Check</title>
<style>
* {
	font-family:arial;
}
#content {
	text-align:center;
}
td.pinguin {
	vertical-align: top;
	font-size:0.9em;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<div id=content>
<h1>Google Pinguin Update Check <?php echo $title ?></h1>
<form method='GET'>
Suchbegriff: <input type='text' name='key' value='<?php echo $key;?>'>
<input type='submit' value='Pinguin Update Check durchführen'>
</form>
<?php echo $results;?>
<br><br>
<small>Google Pinguin Update Check - Copyright by 
<a href='http://m-software.de/'>Michael Jentsch</a></smll>
</div>
</body>
</html>
