<?php
//Draw background gradient
$pattern = new CairoLinearGradient(0, 0, 0, 256);
$pattern->addColorStopRGBA(1, 0, 0, 0, 1);
$pattern->addColorStopRGBA(0, 1, 1, 1, 1);
$context->rectangle(0, 0, 256, 256);
$context->setSource($pattern);
$context->fill();

//Draw foreground round gradient
$pattern = new CairoRadialGradient(
             115.2, 102.4,
              25.6, 102.4,
             102.4, 128
           );
$pattern->addColorStopRGBA(0, 1, 1, 1, 1);
$pattern->addColorStopRGBA(1, 0, 0, 0, 1);
$context->setSource($pattern);
$context->arc(128, 128, 76.8, 0, 2*M_PI);
$context->fill();
?>