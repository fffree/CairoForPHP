<?php
//Set parameters
$x  =  25.6;   $y  = 128.0;
$x1 = 102.4;   $y1 = 230.4;
$x2 = 153.6;   $y2 =  25.6;
$x3 = 230.4;   $y3 = 128.0;

//Draw curve
$context->moveTo($x, $y);
$context->curveTo($x1, $y1, $x2, $y2, $x3, $y3);
$context->setLineWidth(10);
$context->stroke();

//Draw support lines
$context->setSourceRGBA(1, 0.2, 0.2, 0.6);
$context->setLineWidth(6.0);
$context->moveTo($x, $y);
$context->lineTo($x1, $y1);
$context->moveTo($x2, $y2);
$context->lineTo($x3, $y3);
$context->stroke();
?>