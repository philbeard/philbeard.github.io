<?php
$sortOrder = $reportTool->getReverse() ? 'DESC' : 'ASC';
$orderBy = $reportTool->getSortcol() ? $reportTool->getSortcol() : 'Agent';
// object sorting callback function
$callback = function ($a, $b) use ($orderBy , $sortOrder) {
	$orderBy = 'get'. $orderBy;
	if ($sortOrder == 'DESC') {
		if($a->{$orderBy}() == $b->{$orderBy}()){ return 0 ; }
		return ($a->{$orderBy}() < $b->{$orderBy}()) ? -1 : 1;
	} else {
		if($b->{$orderBy}() == $a->{$orderBy}()){ return 0 ; }
		return ($b->{$orderBy}() < $a->{$orderBy}()) ? -1 : 1;
	}
};
usort($agentPerformances, $callback);