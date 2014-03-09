<?php
//Cache image
static $img = null;
if( $img === null) {
  $path = $GLOBALS['CFP_CONF']->res_path.'/sImagePattern.png';
  if( !file_exists($path) )
    throw new CfpIoException('Failed to load file: "'.$path.'"');
  $img = CairoImageSurface::createFromPng($path);
}

//Set parameters
$w = $img->getWidth();
$h = $img->getHeight();

$pattern = new CairoSurfacePattern($img);
$pattern->setExtend(CairoExtend::REPEAT);

$context->translate(128, 128);
$context->rotate(M_PI/4);
$context->scale(1/sqrt(2), 1/sqrt(2));
$context->translate(-128, -128);

$matrix = CairoMatrix::initScale($w/256*5, $h/256*5);
$pattern->setMatrix($matrix);

$context->setSource($pattern);

$context->rectangle(0, 0, 256, 256);
$context->fill();
?>