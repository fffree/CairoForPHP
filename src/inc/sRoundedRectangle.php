<?php
//Set parameters
$x = 25.6;
$y = 25.6;
$width = 204.8;
$height = 204.8;
$aspect = 1;                    // Aspect ratio and
$corner_radius = $height / 10;  // corner curvature radius

$radius = $corner_radius / $aspect;
$degrees = M_PI / 180;

//Map shape
$context->newSubPath();
$context->arc($x + $width - $radius, $y + $radius, $radius, -90 * $degrees, 0 * $degrees);
$context->arc($x + $width - $radius, $y + $height - $radius, $radius, 0 * $degrees, 90 * $degrees);
$context->arc($x + $radius, $y + $height - $radius, $radius, 90 * $degrees, 180 * $degrees);
$context->arc($x + $radius, $y + $radius, $radius, 180 * $degrees, 270 * $degrees);
$context->closePath();

//Fill and encircle shape
$context->setSourceRGB(0.5, 0.5, 1);
$context->fillPreserve();
$context->setSourceRGBA(0.5, 0, 0, 0.5);
$context->setLineWidth(10);
$context->stroke();
?>