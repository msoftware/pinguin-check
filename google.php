<?
function getGoogleData($term)
{
	$entries = array ();
	logInfo ("Get Google Results for " . $term);
	$googledata = fetchAllGoogleResults ($term, 0);
	foreach ($googledata as $googlekey => $googleentry)
	{
		$entry['url'] = $googleentry['unescapedUrl'];
		$entry['title'] = $googleentry['titleNoFormatting'];
		$entry['snippet'] = strip_tags($googleentry['content']);
		$entries[] = $entry;
	}
	return $entries;
}

function fetchAllGoogleResults ($term)
{
	global $googledelay, $maxresults;
	$lastpageindex = -1;
	/**There is a maximum of 64 results returned for the Search APIs and there is no way to get more.**/
	$result = array();
	for ($pos = 0; $pos < $maxresults; $pos+=8)
	{
		$tmp = fetchGoogleResults ($term, $pos);

		// Quota Probleme
		if (!is_array ($tmp["responseData"]))
		{
			logInfo( "Google Quota exceeded " . date ("H:i:s") . " wait $googledelay seconds");
			sleep ($googledelay); 
			$tmp = fetchGoogleResults ($term, $pos);
		}
		$pageindex = 0;
		if (isset ($tmp['responseData']['cursor']["currentPageIndex"]))
		{
			$pageindex = $tmp['responseData']['cursor']["currentPageIndex"];
		}
		logInfo ("PageIndex: " + $pageindex);

		if ($lastpageindex < $pageindex)
		{
			foreach ($tmp['responseData']['results'] as $entry)
			{
				$result[] = $entry;
			}
		} else {
			logInfo ("fetchAllGoogleResults stopped at page " . $pageindex);
			break;
		}
		$lastpageindex = $pageindex;
	}
	// var_dump ($result);
	return $result;
}

function fetchGoogleResults ($term, $pos)
{
	// $url = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=" .  urlencode  ($term)  . "&gl=de&start=" . $pos . "&rsz=large";
	$url = "http://www.google.com/uds/GwebSearch?lstkp=0&rsz=large&hl=de&gl=de&q=" . urlencode  ($term) . "&v=1.0&start=" . $pos;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_REFERER, "http://www.m-software.de/");
	$body = curl_exec($ch);
	curl_close($ch);
	$json = json_decode($body, true);
	if (!is_array ($json["responseData"]))
	{
		// TODO entfernen
		var_dump ($body);
	}
	return $json;
}

?>
