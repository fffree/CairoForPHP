<?php
//Cache image
static $img = null;
if( $img === null) {
  $path = $GLOBALS['CFP_CONF']->res_path.'/sImage.png';
  if( !file_exists($path) )
    throw new CfpIoException('Failed to load file: "'.$path.'"');
  $img = CairoImageSurface::createFromPng($path);
}

//Set parameters
$w = $img->getWidth();
$h = $img->getHeight();

$context->translate(128, 128);
$context->rotate(45*M_PI/180);
$context->scale(256/$w, 256/$h);
$context->translate(-0.5*$w, -0.5*$h);

$context->setSourceSurface($img, 0, 0);
$context->paint();
?>