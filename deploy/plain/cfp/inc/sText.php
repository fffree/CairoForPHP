<?php
//Set font properties
$context->selectFontFace(
  "Sans",
  CairoFontSlant::NORMAL,
  CairoFontWeight::BOLD
);
$context->setFontSize(90);

//Write text
$context->moveTo(10, 135);
$context->showText("Hello");

//Use text as path
$context->moveTo(70, 165);
$context->textPath("void");
$context->setSourceRgb(0.5, 0.5, 1);
$context->fillPreserve();
$context->setSourceRgb(0, 0, 0);
$context->setLineWidth(2.56);
$context->stroke();

//Draw helping lines
$context->setSourceRgba(1, 0.2, 0.2, 0.6);
$context->arc(10, 135, 5.12, 0, 2*M_PI);
$context->closePath();
$context->arc(70, 165, 5.12, 0, 2*M_PI);
$context->fill();
?>