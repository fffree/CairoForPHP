<?php
//Draw some lines
$context->moveTo(50, 75);
$context->lineTo(200, 75);

$context->moveTo(50, 125);
$context->lineTo(200, 125);

$context->moveTo(50, 175);
$context->lineTo(200, 175);

$context->setLineWidth(30);
$context->setLineCap(CairoLineCap::ROUND);
$context->stroke();
?>