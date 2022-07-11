<?php
include "FH3/src/class.FormHandler.php";
fh_conf('FH_FHTML_DIR', 'FH3/src/FHTML/');

//crea un nuevo objeto FormHandler
$form = new FormHandler();

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

/*
//NEW PAGE

//TABINDEX
//NOTA: Campo para navegar en diversas paginas del mismo
// set the tabs
$form -> setTabIndex($tabs); 
// some fields + button
$form -> textField("Field 1", "fld1");
$form -> textField("Field 2", "fld2");
$form -> textField("Field 3", "fld3");
$form -> submitButton("Submit", "submitBtn");

// the tabs!
$tabs = array(
  3 => "fld1",
  1 => "fld2",
  2 => "fld3",
  4 => "submitBtn"
);

//No hay prueba debido a que debe crearse un archivo aparte con lenguaje y demas contenido
// set the language to dutch
$form -> setLanguage( 'nl' );

// get the errors of invalid fields
$errors = $form->catchErrors();

// any errors?
if( sizeof($errors) > 0 ) 
{
    // create a JS message
    $msg = "Some fields are incorrect!\\n";

    foreach($errors as $field => $error) 
    {
        $msg .= "- ". $form -> getTitle( $field )."\\n";
    }
    echo
    "<script language='javascript'>\n".
    "alert('".$msg."');\n".
    "</script>\n";
}

// FOCUS
$form -> textField("Username", "username", FH_STRING);
$form -> passField("Password", "password", FH_PASSWORD);

// set the focus to the password
$form -> setFocus("password"); 

// set another type of mask 
//$form -> setMask( 
  //"  <tr><td>%title% %seperator%</td></tr>\n". 
  //"  <tr><td>%field% %error%</td></tr>\n", 
  //true  # repeat this mask! 
//); 
// set a mask for the upcoming field 
//$form -> setMask( 
  //"  <tr><td>%title%:</td></tr>\n". 
  //"  <tr><td>%field% %error%</td></tr>\n", 
  //1 # repeat it once (so for the upcoming 2 fields!!) 
//); 

// addHTML! 
$form -> addHTML( 
  "<hr size='1' />" 
);

$form -> setMaxLength("message", 30);
*/

//button for submitting
$form->submitButton();

// data handler 
function doRun( $data ) 
{ 
    echo "Data submitted:"; 
    echo "<pre>\n"; 
    print_r( $data ); 
    echo "</pre>\n";     
} 