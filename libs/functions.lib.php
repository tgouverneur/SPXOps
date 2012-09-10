<?php

function parseFrequency($f) {
  if ($f >= 2678400) {
    $months = round($f/2678400);
    return $months.'m';
  }
  if ($f >= 604800) {
    $week = round($f/604800);
    return $week.'w';
  }
  if ($f >= 86400) {
    $day = round($f/86400);
    return $day.'d';
  }
  if ($f >= 3600) {
    $hour = round($f/3600);
    return $hour.'h';
  }
   if ($f >= 60) {
    $min = round($f/60);
    return $min.'m';
  }
  return $f.'s';
}

function parseVars($c) {
  $lines = explode(PHP_EOL, $c);
  $rc = array();
  foreach($lines as $line) {
    $line = trim($line);
    if (empty($line))
      continue;

    $v = explode('=', $line, 2);
    if (count($v) == 2) {
      $v[1] = trim(preg_replace('/(^"|"$)/', '', $v[1]));
      $v[0] = trim($v[0]);
      $rc[$v[0]] = $v[1];
    }
  }
  return $rc;
}

function formatBytes($k) {
  $k /= 1024;
  if ($k < 1024) { return round($k, 2)." KB"; }
  $k = $k / 1024;
  if ($k < 1024) { return round($k)." MB"; }
  $k = $k / 1024;
  if ($k < 1024) { return round($k)." GB"; }
  $k = $k / 1024;
  return round($k)." TB";
}


function formatBlocks($k) {
  if ($k < 1024) { return round($k)." KB"; }
  $k = $k / 1024;
  if ($k < 1024) { return round($k, 2)." MB"; }
  $k = $k / 1024;
  if ($k < 1024) { return round($k, 2)." GB"; }
  $k = $k / 1024;
  return round($k)." TB";
}


?>
