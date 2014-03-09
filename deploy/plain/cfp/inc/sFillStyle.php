<?php
$context->setLineWidth(6);

$context->rectangle(12, 12, 232, 70);
$context->newSubPath();
$context->arc(64, 64, 40, 0, 2*M_PI);
$context->newSubPath();
$context->arcNegative(192, 64, 40, 0, -2*M_PI);

$context->setFillRule(CairoFillRule::EVEN_ODD);
$context->setSourceRGB(0, 0.7, 0);
$context->fillPreserve();
$context->setSourceRGB(0, 0, 0);
$context->Stroke();

$context->translate(0, 128);
$context->rectangle(12, 12, 232, 70);
$context->newSubPath();
$context->arc(64, 64, 40, 0, 2*M_PI);
$context->newSubPath();
$context->arcNegative(192, 64, 40, 0, -2*M_PI);

$context->setFillRule(CairoFillRule::WINDING);
$context->setSourceRGB(0, 0, 0.9);
$context->fillPreserve();
$context->setSourceRGB(0, 0, 0);
$context->stroke();
?>