<?php
//Set parameters
$dashes = array(50, //ink
                10, //skip
                10, //ink
                10  //skip
               );
$offset = -50;

//Adjust dashed line
$context->setDash($dashes, $offset);
$context->setLineWidth(10);

//Draw dashed line
$context->moveTo(128, 25.6);
$context->lineTo(230.4, 230.4);
$context->relLineTo(-102.4, 0);
$context->curveTo(51.2, 230.4, 51.2, 128, 128, 128);
$context->stroke();
?>