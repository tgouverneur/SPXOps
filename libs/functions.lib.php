<?php

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
