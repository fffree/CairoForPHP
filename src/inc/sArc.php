<?php
//Set parameters
$xc = 128;
$yc = 128;
$radius = 100;
$angle1 = 45 * (M_PI/180);
$angle2 = 180 * (M_PI/180);

//Draw arc
$context->setLineWidth(10);
$context->arc($xc, $yc, $radius, $angle1, $angle2);
$context->stroke();

//Draw helping lines
$context->setSourceRGBA(1, 0.2, 0.2, 0.6);
$context->setLineWidth(6);
$context->arc($xc, $yc, 10, 0, 2*M_PI);
$context->fill();
$context->arc($xc, $yc, $radius, $angle1, $angle1);
$context->lineTo($xc, $yc);
$context->arc($xc, $yc, $radius, $angle2, $angle2);
$context->lineTo($xc, $yc);
$context->stroke();
?>