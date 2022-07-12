<?php
include "FH3/src/class.FormHandler.php";
fh_conf('FH_FHTML_DIR', 'FH3/src/FHTML/');

//crea un nuevo objeto FormHandler
$form = new FormHandler('prueba', '', 'needs-validation');

//CONTINUA SIN FUNCIONAR, NO APARECE NADA AL CARGAR
//first page... 
$form -> textField("Question 1", "q1", FH_STRING); 
$form -> submitButton("Next page"); 

// second page 
$form -> newPage(); 
$form -> textArea("Question 2", "q2", FH_TEXT); 
$form -> submitButton("Next Page"); 

// third and last page 
$form -> newPage(); 
$form -> textField("Question 3", "q3", FH_STRING); 
$form -> submitButton("Submit");


// set the handler function 
$form -> onCorrect("doRun"); 

// flush the form.. 
$form -> flush(); 

// data handler 
function doRun( $data ) 
{ 
    echo "Data submitted:"; 
    echo "<pre>\n"; 
    print_r( $data ); 
    echo "</pre>\n";     
} 