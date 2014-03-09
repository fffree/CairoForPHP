<?php
//Set parameters
$string = "cairo";
$x = 25;
$y = 150;

//Set font properties
$context->selectFontFace(
  "Sans",
  CairoFontSlant::NORMAL,
  CairoFontWeight::NORMAL
);
$context->setFontSize(100);

//Get extents
$extents = $context->textExtents($string);

//Write text
$context->moveTo($x, $y);
$context->showText($string);

//Draw helping lines
$context->setSourceRgba(1, 0.2, 0.2, 0.6);
$context->setLineWidth(6);
$context->arc($x, $y, 10, 0, 2*M_PI);
$context->fill();
$context->moveTo($x, $y);
$context->relLineTo(0, -$extents['height']);
$context->relLineTo($extents['width'], 0);
$context->relLineTo(
  $extents['x_bearing'],
  -$extents['y_bearing']
);
$context->stroke();
?>