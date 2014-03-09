<?php
//Clip area
$context->arc(128, 128, 76.8, 0, 2*M_PI);
$context->clip();
$context->newPath();

//Draw cross
$context->rectangle(0, 0, 256, 256);
$context->fill();
$context->setSourceRgb(0, 1, 0);
$context->moveTo(0, 0);
$context->lineTo(256, 256);
$context->moveTo(256, 0);
$context->lineTo(0, 256);
$context->setLineWidth(10);
$context->stroke();
?>