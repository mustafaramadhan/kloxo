<?php 
function get_plural($word)
{
	if ($word[strlen($word) - 1] === 's') {
		$ret = "{$word}es";
	} else if ($word[strlen($word) - 1] === 'y') {
		if ($word[strlen($word) - 2] === 'e') {
			$ret = "{$word}s";
		} else {
			$ret = substr($word, 0, strlen($word) - 1) . "ies";
		}
	} else if ($word[strlen($word) - 1] === 'x') {
		$ret = substr($word, 0, strlen($word) - 1) . "xes";
	} else {
		$ret = "{$word}s";
	}
	return ucfirst($ret);
}

// This is an alternate get_plural, which has the all the plurals are defined in a file.
function get_plural_alternate($word)
{
	include_once "lang/en/lang_plural.inc";

	if (isset($__plural_desc[$word])) {
		return $__plural_desc[$word];
	}

	return "{$word}s";
}


