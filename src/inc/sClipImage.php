<?php
//Cache image
static $img = null;
if( $img === null) {
  $path = $GLOBALS['CFP_CONF']->res_path.'/sClipImage.png';
  if( !file_exists($path) )
    throw new CfpIoException('Failed to load file: "'.$path.'"');
  $img = CairoImageSurface::createFromPng($path);
}

//Set parameters
$w = $img->getWidth();
$h = $img->getHeight();

//Clip area
$context->arc(128, 128, 76.8, 0, 2*M_PI);
$context->clip();
$context->newPath();

//Show image
$context->scale(256/$w, 256/$h);
$context->setSourceSurface($img, 0, 0);
$context->paint();
?>