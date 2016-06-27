<?php
class App_Base_Common {
/***** LICENSE GOES HERE *****/
/**
 * Fills in missing options; useful for when loading JSON from somewhere and you want to make
 * sure that any missing options are set to their defaults.
 *
 * This will be applied recursively.
 *
 * For example, if you do:
 *   $object_options = (object) array('foo' => 1234, 'bar' => 6789);
 *   $default_options = (object) array('foo' => 1000, 'other' => 2000);
 *
 * and then call fillDefaultOptions($object_options, $default_options);
 *
 * then $object_options will now equal (object) array('foo' => 1234, 'bar' => 6789, 'other' => 2000).
 *
 * @param object $object_options   The object where missing keys should be filled in.
 * @param object $default_options  The object storing defaults.
 */
static public function fillDefaultOptions(&$object_options, $default_options) {
	if (!(is_object($object_options) && is_object($default_options)))
		throw new Exception('fillDefaultOptions: both parameters must be objects');

	foreach ($default_options as $key => $val) {
		if (!isset($object_options->{$key})) {
			$object_options->{$key} = $val;
		} elseif (is_object($object_options->{$key})) {
			static::fillDefaultOptions($object_options->{$key}, $default_options->{$key});
		}
	}
}

/**
 * Does the same as fillDefaultOptions, but allows:
 *
 * 1) any falsy value to be specified instead of `(object) array()` for either parameter
 * 2) an array to be specified instead of an object for either parameter
 *
 * When this function returns $object_options will contain an object.
 *
 * Useful for handling options parameters.
 */
static public function fillDefaultOptionsLax(&$object_options, $default_options) {
	// If both parameters are empty give an empty object.
	if (empty($object_options) && empty($default_options)) {
		$object_options = (object) array();
		return;
	}

	// Convert arrays to objects
	if (is_array($object_options))
		$object_options = (object) $object_options;
	if (is_array($default_options))
		$default_options = (object) $default_options;

	// If object_options is empty and default_options was valid, give default_options.
	if (is_object($default_options) && empty($object_options)) {
		$object_options = $default_options;
		return;
	}

	// If default_options is empty and object_options was valid, give object_options.
	if (is_object($object_options) && empty($default_options))
		return;

	self::fillDefaultOptions($object_options, $default_options);
}
}