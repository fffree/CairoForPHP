<?php
//Draw some corners..
$context->setLineWidth(40.96);

$context->moveTo(76.8, 84.48);
$context->relLineTo(51.2, -51.2);
$context->relLineTo(51.2, 51.2);
$context->setLineJoin(CairoLineJoin::MITER);
$context->stroke();

$context->moveTo(76.8, 161.28);
$context->relLineTo(51.2, -51.2);
$context->relLineTo(51.2, 51.2);
$context->setLineJoin(CairoLineJoin::BEVEL);
$context->stroke();

$context->moveTo(76.8, 238.08);
$context->relLineTo(51.2, -51.2);
$context->relLineTo(51.2, 51.2);
$context->setLineJoin(CairoLineJoin::ROUND);
$context->stroke();
?>