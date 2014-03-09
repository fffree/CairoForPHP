<?php
//Define first shape
$context->moveTo(128, 25.6);
$context->lineTo(230.4, 230.4);
$context->relLineTo(-102.4, 0);
$context->curveTo(51.2, 230.4, 51.2, 128, 128, 128);
$context->closePath();

//Define second shape
$context->moveTo(65, 25.6);
$context->relLineTo(51.2, 51.2);
$context->relLineTo(-51.2, 51.2);
$context->relLineTo(-51.2, -51.2);
$context->closePath();

//Fill and encircle
$context->setLineWidth(10);
$context->setSourceRGB(0, 0, 1);
$context->fillPreserve();
$context->setSourceRGB(0, 0, 0);
$context->stroke();
?>