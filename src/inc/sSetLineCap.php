<?php
//Draw some lines
$context->setLineWidth(30);

$context->setLineCap(CairoLineCap::BUTT);
$context->moveTo(64, 50);
$context->lineTo(64, 200);
$context->stroke();

$context->setLineCap(CairoLineCap::ROUND);
$context->moveTo(128, 50);
$context->lineTo(128, 200);
$context->stroke();

$context->setLineCap(CairoLineCap::SQUARE);
$context->moveTo(192, 50);
$context->lineTo(192, 200);
$context->stroke();

//Draw helping lines
$context->setSourceRGB(1, 0.2, 0.2);
$context->setLineWidth(2.56);
$context->moveTo(64, 50);
$context->lineTo(64, 200);
$context->moveTo(128, 50);
$context->lineTo(128, 200);
$context->moveTo(192, 50);
$context->lineTo(192, 200);
$context->stroke();
?>