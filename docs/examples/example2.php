<?php

require_once '../../FOAF/Parser.php';

$parser = new XML_FOAF_Parser;

$foafs = glob('../../tests/foafs/*.foaf');

//var_dump($foafs);

$foaf_rand = rand(0,sizeof($foafs)-1);

$foaf = isset($_GET['foaf']) ? file_get_contents(urldecode($_GET['foaf'])) : file_get_contents($foafs[$foaf_rand]);

echo '<h1>' .$foaf_uri = isset($_GET['foaf']) ? urldecode($_GET['foaf']) : $foafs[$foaf_rand];
echo '</h1>';
require_once 'Benchmark/Timer.php';
$timer = new Benchmark_Timer();
$timer->start();
$parser->parseFromMem($foaf);
$timer->setMarker('Time Taken to Parse the FOAF');
$timer->stop();
$timer->display();

echo "<h2>FOAF as Array</h2>";
var_dump($parser->toArray());
?>