<?php
include "FH3/src/class.FormHandler.php";
fh_conf('FH_FHTML_DIR', 'FH3/src/FHTML/');

//crea un nuevo objeto FormHandler
$form = new FormHandler('prueba1', '', 'class="was-validated"');

// make the datefield 
$form -> dateField("Datefield", "birthdate1", null, null, null, "display:flex");


// make the datefield
$form -> dateTextField("datetextfield", "birthdate3");

$form->submitButton();

// set the handler function 
$form -> onCorrect("doRun"); 

// data handler 
function doRun( $data ) 
{ 
 
   echo "Your birthday is ". $aData["birthdate"];  
}   
