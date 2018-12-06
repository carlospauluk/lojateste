<?php

namespace Journal3\Options;

use Journal3\Utils\Arr;
use Journal3\Utils\Str;

class Color extends Option {

	protected static function parseValue($value, $data = null) {
		if (!$value) {
			return null;
		}

		if (!is_scalar($value)) {
			trigger_error(sprintf("%s (%s) is invalid!", Arr::get($data, 'name'), Arr::get($data, 'selector')));

			return null;
		}

		if (Str::startsWith($value, '__VAR__')) {
			$value = Arr::get(static::$variables, 'color.' . $value);
		}

		list($r, $g, $b, $a) = sscanf($value, "rgba(%d, %d, %d, %f)");

		if (is_numeric($r) && is_numeric($g) && is_numeric($b) && is_numeric($a)) {
			return "rgba($r, $g, $b, $a)";
		}

		return null;
	}
}
