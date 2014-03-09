<?php
//Set parameters
$string = "cairo";

//Set font properties
$context->selectFontFace(
  "Sans",
  CairoFontSlant::NORMAL,
  CairoFontWeight::NORMAL
);
$context->setFontSize(52);

//Calculate centerpoint
$extents = $context->textExtents($string);
$x = 128-($extents['width']/2 + $extents['x_bearing']);
$y = 128-($extents['height']/2 + $extents['y_bearing']);

//Write text
$context->moveTo($x, $y);
$context->showText($string);

//Draw helping lines
$context->setSourceRgba(1, 0.2, 0.2, 0.6);
$context->setLineWidth(6);
$context->arc($x, $y, 10, 0, 2*M_PI);
$context->fill();
$context->moveTo(128, 0);
$context->relLineTo(0, 256);
$context->moveTo(0, 128);
$context->relLineTo(256, 0);
$context->stroke();
?>