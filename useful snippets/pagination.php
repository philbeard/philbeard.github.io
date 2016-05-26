<?php
class App_Tool_Report {
	/**
	 * array of report row objects
	 * set by a service class, read by the view
	 * only contains data for rows actually being displayed
	 */
	public $resultData = null;
	/**
	 * a stdclass of search options set by controller and read by service class.
	 * fields within object will vary by report.
	 */
	public $searchOptions = null;

	private $_displayLimit = 100;
	private $_numResults = 0;
	private $_offset = 0;
	private $_sortcol = "";
	private $_reverse = false;
	private $_params = null;

	public function __construct() {
		$this->searchOptions = (object) array();
		$this->_params = $_GET;
		if (!isset($this->_params['o']))
			$this->_params['o'] = '0';
		if (!isset($this->_params['s']))
			$this->_params['s'] = '';
		if (!isset($this->_params['r']))
			$this->_params['r'] = '0';
		$this->_offset = intval($this->_params['o']);
		$this->_sortcol = $this->_params['s'];
		$this->_reverse = intval($this->_params['r']) ? true : false;
	}

	public function getDisplayLimit() {
		return $this->_displayLimit;
	}
	public function setDisplayLimit($limit) {
		$this->_displayLimit = $limit;
		return $this;
	}

	public function getNumResults() {
		return $this->_numResults;
	}
	public function setNumResults($num) {
		$this->_numResults = $num;
		return $this;
	}

	public function getOffset() {
		return $this->_offset;
	}

	public function getSortcol() {
		return $this->_sortcol;
	}

	public function getReverse() {
		return $this->_reverse;
	}

	private function getLinkHtml($params, $text) {
		App_Base_Common::fillDefaultOptionsLax($params, $this->_params);
		$params = (array) $params;

		$url = '';
		$notFirst = false;
		foreach ($params as $key => $val) {
			$url .= ($notFirst ? "&" : "?") . $key . "=" . $val;
			$notFirst = true;
		}
		return "<a href='$url'>$text</a>";
	}

	public function getColumnHeaderHtml($sortcol, $text) {
		$reverse = ($sortcol == $this->_sortcol && $this->_reverse == false);
		$params = array();
		$params['o'] = '0';
		$params['s'] = $sortcol;
		$params['r'] = $reverse ? '1' : '0';

		return $this->getLinkHtml($params, $text);
	}

	public function getPageNumbersHtml() {
		$print = array();
		$max = $this->_displayLimit;
		$l = 1;
		$current_page_index = 0;
		for ($i = 0; $i < $this->_numResults; $i += $max) {
			if ($i == $this->_offset) {
				$prev = $i - $max;
				$next = $i + $max;
				$current_page_index = $l - 1;
				$print[] = "<span style='font-weight:bold; color:red;'>$l</span>"; /// Current page is not displayed as link and given font color red
			} else {
				$print[] = $this->getLinkHtml(array('o' => $i), $l);
			}
			$l++;
		}
		if ($current_page_index > 0) {
			array_unshift($print, $this->getLinkHtml(array('o' => $prev), 'Prev'));
		} else {
			array_unshift($print, 'Prev');
		}
		if ($current_page_index < $l - 2) {
			$print[] = $this->getLinkHtml(array('o' => $next), 'Next');
		} else {
			$print[] = 'Next';
		}
		return implode(' ', $print);
	}
}
