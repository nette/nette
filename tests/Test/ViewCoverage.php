<?php

/**
 * coverage.dat HTML viewer.
 *
 * This file is part of the Nette Framework.
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 * Copyright (c) 2004 Jakub Vrana (http://php.vrana.cz)
 *
 * @package    Nette\Test
 */



// load coverage.dat
$file = __DIR__ . '/coverage.dat';
if (!is_file($file)) {
	die("File '$file' is missing.");
}

$coverageInfo = @unserialize(file_get_contents($file));
if (!$coverageInfo) {
	die("Content of file '$file' is invalid.");
}


// search files
$root = realpath(__DIR__ . '/../../Nette') . DIRECTORY_SEPARATOR;
$files = array();
$totalSum = $coveredSum = 0;
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $entry) {
	if (substr($entry->getBasename(), 0, 1) === '.') { // . or .. or .gitignore
		continue;
	}
	$entry = (string) $entry;

	$coverage = $covered = $total = 0;
	$lines = array();
	if (isset($coverageInfo[$entry])) {
		$lines = $coverageInfo[$entry];
		foreach ($lines as $flag) {
			if ($flag >= -1) {
				$total++;
			}
			if ($flag >= 1) {
				$covered++;
			}
		}
		$coverage = round($covered * 100 / $total);
		$totalSum += $total;
		$coveredSum += $covered;
	}

	$files[] = (object) array(
		'name' => str_replace($root, '', $entry),
		'file' => $entry,
		'lines' => $lines,
		'coverage' => $coverage,
		'total' => $total,
		'light' => $total ? $total < 5 : count(file($entry)) < 50,
	);
}



$classes = array(
	1 => 't', // tested
	-1 => 'u', // untested
	-2 => 'dead', // dead code
);

ini_set('highlight.comment', '#999; font-style: italic');
ini_set('highlight.default', '#000');
ini_set('highlight.html', '#06B');
ini_set('highlight.keyword', '#D24; font-weight: bold');
ini_set('highlight.string', '#080');

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex,noarchive">
	<meta name="generator" content="Nette Test Framework">

	<title>Nette Framework code coverage</title>

	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>

	<style type="text/css">
	html {
		font: 16px/1.5 sans-serif;
		border-top: 4.7em solid #F4EBDB;
	}

	body {
		max-width: 990px;
		margin: -4.7em auto 0;
		background: white;
		color: #333;
	}

	h1 {
		font-size: 1.9em;
		margin: .5em 0 1.5em;
		background: url(http://files.nette.org/icons/logo-e1.png) right center no-repeat;
		color: #7A7772;
		text-shadow: 1px 1px 0 white;
	}

	div.code {
		font: 14px/1.2 Consolas, monospace;
		background: #FDF5CE;
		padding: .4em .7em;
		border: 1px dotted silver;
		display: none;
	}

	span.line {
		color: #9F9C7F;
		font-weight: normal;
		font-style: normal;
	}

	.t {
		background: #99f999;
	}

	.u {
		background: #f9ac9e;
	}

	td {
		vertical-align: middle;
	}

	small {
		color: gray;
	}

	.number {
		text-align: right;
		width: 50px;
	}

	.bar {
		border: 1px solid #ACACAC;
		background: #e50400;
		width: 35px;
		height: 1em;
	}

	.bar div {
		background: #1A7E1E;
		height: 1em;
	}

	.light td {
		opacity: .5;
	}

	.light td * {
		color: gray;
	}
	</style>
</head>

<body>
	<h1>Nette Framework Code coverage <?php echo round($coveredSum * 100 / $totalSum) ?>&nbsp;%</h1>

	<?php foreach ($files as $id => $info): ?>
	<div>
		<table>
		<tr <?php echo $info->light ? 'class="light"' : '' ?>>
			<td class="number"><small><?php echo $info->coverage ?> %</small></td>
			<td><div class="bar"><div style="width: <?php echo $info->coverage ?>%"></div></div></td>
			<td><a href="#fragment<?php echo $id ?>"><span><?php echo $info->name ?></span></a></td>
		</tr>
		</table>

		<div class="code" id="fragment<?php echo $id ?>">
		<?php
			$source = explode('<br />', highlight_file($info->file, TRUE));

			end($source);
			$numWidth = strlen((string) key($source));

			unset($prevColor);
			$tags = '';
			foreach ($source as $n => $line) {
				if (isset($info->lines[$n + 1]) && isset($classes[$info->lines[$n + 1]])) {
					$color = $classes[$info->lines[$n + 1]];
				} else {
					$color = '';  // not executable
				}
				if (!isset($prevColor)) {
					$prevColor = $color;
				}
				$line = sprintf("<span class='line'>%{$numWidth}s:    </span>", $n + 1) . $line;
				if ($prevColor !== $color || $n === count($source) - 1) {
					echo '<div' . ($prevColor ? " class='$prevColor'" : '') . '>', str_replace(' />', '>', $tags);
					$openTags = array();
					preg_match_all('#<([^>]+)#', $tags, $matches);
					foreach ($matches[1] as $m) {
						if ($m[0] === '/') {
							array_pop($openTags);
						} elseif (substr($m, -1) !== '/') {
							$openTags[] = $m;
						}
					}
					foreach (array_reverse($openTags) as $tag) {
						echo '</' . preg_replace('# .*#', '', $tag) . '>';
					}
					echo "</div>\n";
					$tags = ($openTags ? '<' . implode('><', $openTags) . '>' : '');
					$prevColor = $color;
				}
				$tags .= "$line<br />\n";
			}
		?></div>
	</div>
	<?php endforeach ?>

	<script type="text/javascript">
	$(function(){
		$("a").click(function(event){
			$($(this).attr('href')).toggle();
			event.preventDefault();
		});

		$("div.code").click(function(){
			$(this).toggle();
		});
	});
	</script>
</body>
</html>
