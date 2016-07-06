<?php
/***** LICENSE GOES HERE *****/
$callback = function ($a, $b) use ($orderBy) {
	$orderBy = 'get'. $orderBy;
	if($a->{$orderBy}() == $b->{$orderBy}()){ return 0 ; }
	return ($a->{$orderBy}() < $b->{$orderBy}()) ? -1 : 1;
};
usort($agentPerformances, $callback);