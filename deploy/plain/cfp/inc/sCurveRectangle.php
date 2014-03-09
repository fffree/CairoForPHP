<?php
//Set parameters
$x0 = 25.6;
$y0 = 25.6;
$rect_width = 204.8;
$rect_height = 204.8;
$radius = 102.4;
$x1 = $x0 + $rect_width;
$y1 = $y0 + $rect_height;

//Validate parameters
if( !$rect_height || !$rect_width )
  return;

//Map shape
if( $rect_width/2 < $radius ) {
  if($rect_height/2 < $radius ) {
    $context->moveTo($x0, ($y0+$y1)/2);
    $context->curveTo($x0, $y0, $x0, $y0, ($x0+$x1)/2, $y0);
    $context->curveTo($x1, $y0, $x1, $y0, $x1, ($y0+$y1)/2);
    $context->curveTo($x1, $y1, $x1, $y1, ($x1+$x0)/2, $y1);
    $context->curveTo($x0, $y1, $x0, $y1, $x0, ($y0+$y1)/2);
  } else {
    $context->moveTo($x0, $y0+$radius);
    $context->curveTo($x0, $y0, $x0, $y0, ($x0+$x1)/2, $y0);
    $context->curveTo($x1, $y0, $x1, $y0, $x1, $y0 + $radius);
    $context->lineTo($x1, $y1-$radius);
    $context->curveTo($x1, $y1, $x1, $y1, ($x1+$x0)/2, $y1);
    $context->curveTo($x0, $y1, $x0, $y1, $x0, $y1-$radius);
  }
} else {
  if($rect_height/2 < $radius) {
    $context->moveTo($x0, ($y0+$y1)/2);
    $context->curveTo($x0, $y0, $x0, $y0, $x0+$radius, $y0);
    $context->lineTo($x1-$radius, $y0);
    $context->curveTo($x1, $y0, $x1, $y0, $x1, ($y0+$y1)/2);
    $context->curveTo($x1, $y1, $x1, $y1, $x1-$radius, $y1);
    $context->lineTo($x0+$radius, $y1);
    $contect->curveTo($x0, $y1, $x0, $y1, $x0, ($y0+$y1)/2);
  } else {
    $context->moveTo($x0, $y0+$radius);
    $context->curveTo($x0, $y0, $x0, $y0, $x0+$radius, $y0);
    $context->lineTo($x1-$radius, $y0);
    $context->curveTo($x1, $y0, $x1, $y0, $x1, $y0+$radius);
    $context->lineTo($x1, $y1-$radius);
    $context->curveTo($x1, $y1, $x1, $y1, $x1-$radius, $y1);
    $context->lineTo($x0+$radius, $y1);
    $context->curveTo($x0, $x1, $x0, $y1, $x0, $y1-$radius);
  }
}
$context->closePath();

//Fill and encircle shape
$context->setSourceRGB(0.5, 0.5, 1);
$context->fillPreserve();
$context->setSourceRGBA(0.5, 0, 0, 0.5);
$context->setLineWidth(10);
$context->stroke();
?>