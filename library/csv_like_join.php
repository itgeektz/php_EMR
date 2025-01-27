<?php
function csv_like_join($array, $quote_all = FALSE) {
	$result = '';
	$first = TRUE;

	foreach ($array as $value) {
		if ($first) {
			$first = FALSE;
		} else {
			$result .= ',';
		}
		if ($quote_all) {
			$result .= csv_quote($value);
		} else {
			$result .= maybe_csv_quote($value);
		}
	}

	return $result;
}

function csv_quote($string) {
	return '"' . str_replace($string, '"', '""') . '"';
}

function maybe_csv_quote($string) {
	if (need_csv_quote($string)) {
		return csv_quote($string);
	}

	return $string;
}

function need_csv_quote($string) {
	if (strpos($string, ',') === FALSE
	 && strpos($string, '"') === FALSE
	 && strpos($string, "\r") === FALSE
	 && strpos($string, "\n") === FALSE)
	{
		return FALSE;
	}

	return TRUE;
}

function split_csv_line($record) {
	$first = NULL;

	if (strlen($record) == 0) {
		return array('');
	}

	if ($record[0] === '"') {
		$first = '';
		$start = 1;

		while ($start < strlen($record)
		    && ($end = strpos($record, '"', $start)) !== FALSE
		    && $end < strlen($record) - 1
		    && $record[$end + 1] !== ',')
		{
			if ($record[$end + 1] !== '"') {
				die("Found characters between double-quoted field and comma.");
			}

			$first .= substr($record, $start, $end - $start - 1);
			$start = $end + 2;
		}

		if ($start < strlen($record) || $end === FALSE) {
			die("Could not find end-quote for double-quoted field");
		}

		$first .= substr($record, $start, $end - $start - 1);

		if ($end >= strlen($record) - 1) {
			return array($first);
		}

		/* Assertion: $record[$end + 1] == ',' */
		$rest = substr($record, $end + 2);
	} else {
		$end = strpos($record, ',');

		if ($end === FALSE) {
			return array($record);
		}

		/* Assertion: $end < strlen($record) */

		$first = substr($record, 0, $end);
		$rest = substr($record, $end + 1);
	}

	$fields = split_csv_line($rest);
	array_unshift($fields, $first);
	return $fields;
}
?>
