<?php

declare(strict_types=1);

function currentBaseUrl(): string {
	$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ) ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
	$dir = rtrim(dirname((string)($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
	return $scheme . '://' . $host . $dir;
}

function buildFeedUrl( string $username, string $baseUrl, string $format = 'Mrss'): string {
	$username = ltrim(trim($username), '@');
	$query = http_build_query([
		'action' => 'display',
		'bridge' => 'Instagram',
		'context' => 'Username',
		'u' => $username,
		'format' => $format,
	]);

	return $baseUrl . '/?' . $query;
}

function isValidUsername(string $username) : bool {
	return (bool) preg_match('/^[A-Za-z0-9_.]{1,30}$/', $username);
}

$rawInput = trim((string)($_GET['u'] ?? $_POST['u'] ?? ''));
$username = ltrim($rawInput, '@');
$feedUrl = null;
$error = null;

if( $rawInput !== '' ) {
	if( !isValidUsername($username) ) {
		$error = 'Not a valid username';
	} else {
		$feedUrl = buildFeedUrl($username, currentBaseUrl());
	}
}

if (($_GET['format'] ?? '') === 'text') {
	header('Content-Type: text/plain; charset=utf-8');
    if ($feedUrl) {
        echo $feedUrl . "\n";
    } else {
        http_response_code(400);
        echo ($error ?? 'Missing ?u=<username>') . "\n";
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Instagram to RSS URL Generator</title>
		<style>
			  body { font-family: system-ui, -apple-system, sans-serif; max-width: 640px; margin: 3rem auto; padding: 0 1rem; color: #1a1a1a; }
			  h1 { font-size: 1.4rem; }
			  form { display: flex; gap: 0.5rem; margin: 1.5rem 0; }
			  input[type=text] { flex: 1; padding: 0.5rem 0.75rem; font-size: 1rem; border: 1px solid #ccc; border-radius: 6px; }
			  button { padding: 0.5rem 1.25rem; font-size: 1rem; border: none; border-radius: 6px; background: #1a1a1a; color: white; cursor: pointer; }
			  .result { margin-top: 1rem; padding: 1rem; background: #f5f5f5; border-radius: 8px; word-break: break-all; }
			  .result a { color: #0645ad; }
			  .error { color: #b00020; margin-top: 1rem; }
			  .hint { color: #666; font-size: 0.9rem; }
		</style>
	</head>
	<body>
	  <h1>Instagram to RSS Feed URL Generator</h1>
	  <p class="hint">Enter any Instagram username to get its RSS-Bridge feed URL</p>
	 
	  <form method="get">
		<input type="text" name="u" placeholder="Instagram username" value="<?= htmlspecialchars($rawInput, ENT_QUOTES) ?>" autofocus>
	    <button type="submit">Generate</button>
	  </form>
	 
	  <?php if ($error): ?>
	    <p class="error"><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
	  <?php elseif ($feedUrl): ?>
	    <div class="result">
	      <strong>Feed URL:</strong><br>
	      <a href="<?= htmlspecialchars($feedUrl, ENT_QUOTES) ?>"><?= htmlspecialchars($feedUrl, ENT_QUOTES) ?></a>
	    </div>
	  <?php endif; ?>
	</body>
</html>