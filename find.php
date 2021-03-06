<?php

$results = '';

if ( isset($_GET['q']) ) {
	$q = strtolower(trim($_GET['q']));
	$q = str_replace(array("'", '-'), '', $q);
	$q = preg_replace('/\W+/', '_', $q);

	if ( !$q ) {
		exit('<p>Invalid q.</p>');
	}

	while ( strlen($q) >= 1 ) {
		$url = 'http://sg.media-imdb.com/suggests/' . $q[0] . '/' . urlencode($q) . '.json';
		$jsonp = @file_get_contents($url);
		if ( $jsonp ) {
			break;
		}
		else {
			$q = substr($q, 0, -1);
		}
	}

	$jsonp = trim($jsonp, ' ;');
	$json = substr($jsonp, strlen($q) + 6, -1);
	$response = json_decode($json);

	$results .= "<ul>\n";
	foreach ( $response->d as $object ) {
		$description = @$object->s ?: '';
		if ( strstr($object->id, '://') ) {
			$url = $object->id;
			$title = $object->l;
		}
		else if ( $object->id[0] == 't' ) {
			$url = 'http://www.imdb.com/title/' . $object->id . '/';
			$title = $object->l . ' (' . ( @$object->y ?: '?' ) . ')';
		}
		else {
			$url = 'http://www.imdb.com/name/' . $object->id . '/';
			$title = $object->l;
			$description = trim(substr($description, strpos($description, ',') + 1));
		}

		$results .= '<li>';
		$results .= '<a href="' . $url . '">' . $title . '</a>';
		if ( $description ) {
			$results .= '<br>' . $description;
		}
		$results .= "</li>\n";
	}
	$results .= "</ul>\n";
}

?>
<!doctype html>
<html>

<head>
	<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="theme-color" content="#333" />
	<title>IMDb quick find</title>
	<style>
	* {
		box-sizing: border-box;
	}
	body, input, button {
		font-size: 20px;
		line-height: 1.3;
	}
	li {
		margin-bottom: 5px;
	}
	input {
		padding: 10px;
	}
	button {
		padding: 10px 25px;
		font-weight: bold;
		width: 100%;
	}
	</style>
</head>

<body>
	<p><a href="intersect.php">Intersect here</a></p>

	<?= $results ?>

	<form action>
		<p>Query: <input name="q" value="<?= @$_GET['q'] ?>" autocomplete="off" /></p>
		<p><button>Search</button></p>
	</form>

</body>

</html>
