<?php
include "FH3/src/class.FormHandler.php";
fh_conf('FH_FHTML_DIR', 'FH3/src/FHTML/');

//crea un nuevo objeto FormHandler
$form = new FormHandler();

//some fields.. (see manual for examples)
$form -> addLine("Campo de texto: ");
$form->textField("Nombre", "name", FH_STRING, 20, 40);

// set a hidden field
$form -> hiddenField("language", "nl");

// a textfield
$form -> addLine("Campo de clave: ");
$form -> passField("Your password", "pass", FH_PASSWORD);
// set the help message for the field 
$form -> setHelpText('pass', 'Ingrese la clave');
// addHTML! 
$form -> addHTML( 
  "<hr size='1' />" 
);

// textarea
$form -> addLine("Area de texto: ");
$form -> textArea("Descripcion", "message", FH_TEXT);
$form -> setMaxLength("message", 30);

// Opciones de navegadores
$browsers = array(
    ""             => "-- Select --",
    "__LABEL(IE)__" => "Microsoft Internet Explorer",
    "msie3"         => "Microsoft Internet Explorer 3",
    "msie4"         => "Microsoft Internet Explorer 4",
    "msie5"         => "Microsoft Internet Explorer 5",
    "msie55"        => "Microsoft Internet Explorer 5.5",
    "msie6"         => "Microsoft Internet Explorer 6",
    "__LABEL(MO)__" => "Mozilla",
    "moz1"          => "Mozilla 1",
    "__LABEL(NN)__" => "Netscape Navigator",
    "nn3"           => "Netscape Navigator 3",
    "nn4"           => "Netscape Navigator 4",
    "nn6"           => "Netscape Navigator 5",
    "nn6"           => "Netscape Navigator 6",
    "nn7"           => "Netscape Navigator 7",
    "__LABEL(OP)__" => "Opera",
    "op3"           => "Opera 3",
    "op35"          => "Opera 3.5",
    "op4"           => "Opera 4",
    "op5"           => "Opera 5",
    "op6"           => "Opera 6",
    "op7"           => "Opera 7"
);

// Campo de seleccion
$form -> addLine("Campo de seleccion: ");
$form -> selectField("Navegadores", "browser1", $browsers, FH_NOT_EMPTY, true);

//Checkbox variable
// Opciones for the checkbox
$animals = array(
  "Dog",
  "Cat",
  "Cow"
); 

// Checkbox
$form -> addLine("Seleccione: ");
$form -> checkBox("Animal Favorito", "animal", $animals, null, false); 

// opcione radiobutton
$gender = array(
  "m" => "Male",
  "f" => "Female"
); 


//Genero
$form -> addLine("Genero: ");
// make the radiobutton
$form -> radioButton("Gender", "gender", $gender);

//PRUEBA DE CARGA DE IMAGENES
// The upload configuration
// NOTE: You dont have to set every value!
// Like below, we have not set the "size", so the default configuration
// value is used (max size which is possible).
$cfg = array(
  "path"       => $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']).'/uploads/images',  
  "type"       => "jpg jpeg",
  "name"       => "", // <-- keep the original name
  "required"   => true,
  "exists"     => "rename"
);

// upload field
$form -> addLine("Cargue su imagen: ");
$form -> uploadField("Image", "image", $cfg);

// the values for the listfield
$values = array(
  1 => "PHP",
  2 => "MySQL database",
  3 => "Frontpage extensions",
  4 => "ASP",
  5 => "10 MB extra webspace",
  6 => "Webmail",
  7 => "Cronjobs"
); 

// the listfield
$form -> addLine("Listas: ");
$form->ListField("Products", "products", $values);


// make the editor  
 $config = array(
  "contentsCss" => "cms.css"
);
 
$form -> addLine("Editor de texto: ");
$form -> editor("Message", "message2", null, "images/uploads/");

// make the datefield 
$form -> addLine("Fecha de nacimiento: ");
$form -> dateField("Birthdate", "birthdate1");

// Datefield con calendario js
$form -> addLine("Fecha con JS: ");
$form -> jsdateField("Birthdate", "birthdate2");

// make the datefield
$form -> addLine("Fecha en texto: ");  
$form -> dateTextField("Birthdate", "birthdate3");

// make the datefield con js
$form -> addLine("Fecha en texto con JS: "); 
$form -> jsDateTextField("Birthdate", "birthdate4"); 

// a timefield 
$form -> timeField("Time", "time"); 


// make the browser field
$form->BrowserField('Image','image1', "/uploads/Image");

// a ColorPicker 
$form->colorpicker("Color", "colorselect"); 

// set the options array
$aOptions = array( 'Red', 'Green' );

// new TextSelect field
$form->TextSelectField( 'Color', 'color', $aOptions );


// a textfield
$form -> addLine("No muestra la imagen error");
$form->CaptchaField("Verify the code", "code");


//BOTONES

// the button 
$form -> button("Test", "btnTest", "onclick='alert(this.name)'"); 

// image button! 
$form -> imageButton("images/boton.png");

// the reset button 
$form -> resetButton(); 
// go to ../index.php when the button is pressed 
$form -> cancelButton("Cancel", "../index.php");

//back button
$form -> backButton("Atras", "Regresar");

// star for required fields 
$star = ' <font color="red">*</font>'; 

// some fields 
$form -> textField("Name".$star, "name1", FH_STRING, 20, 50); 
$form -> textField("Age".$star, "age", FH_INTEGER, 3, 2); 

// add a line that every field with a red * is required 
$form -> addLine($star); 


// some options used in the form 
$brow = array( 
  "IE"  => "Microsoft Internet Explorer", 
  "NN"  => "Netscape Navigator", 
  "MOZ" => "Mozilla", 
  "FF"  => "Firefox", 
  "OP"  => "Opera", 
  "-1"  => "Other..." 
);
// start a fieldset! 
$form -> borderStart("Browser"); 

// set a mask for the upcoming field 
$form -> setMask( 
  "  <tr><td>%title%:</td></tr>\n". 
  "  <tr><td>%field% %error%</td></tr>\n", 
  1 # repeat it once (so for the upcoming 2 fields!!) 
); 

// browsers to select from 
$form -> radioButton("Select the browser you use", "browswer", $brow); 
// which version of the browser?  
$form -> textField("Version", "version", FH_FLOAT, 5, 5); 

// stop the border 
$form -> borderStop();

// set another type of mask 
$form -> setMask( 
  "  <tr><td>%title% %seperator%</td></tr>\n". 
  "  <tr><td>%field% %error%</td></tr>\n", 
  true  # repeat this mask! 
); 

// some fields 
$form -> textField("Name", "name3", FH_STRING); 
$form -> textField("Age", "age3", FH_INTEGER, 3, 2); 
$form -> selectField("Gender", "gender3", array('M', 'F'), null, false);

// a textfield + custom error message!!! 
$form -> textField("First Name", "fname", FH_STRING); 
$form -> setErrorMessage( "fname", "You have to enter a first name!");

// the auto complete items 
$colors = array ( "red", "orange", "yellow", "green", "blue", "indigo", "violet", "brown", "rood" ); 
// the textfield used for auto completion 
$form -> textField("Type a color", "color2", FH_STRING); 

// set the auto completion for the field Color 
$form -> setAutoComplete("color2", $colors); 

// the auto complete items 
$providers = array ( "hotmail.com", "live.com", "php-globe.nl", "freeler.nl" ); 
// the textfield used for auto completion after
$form -> textField("Type your email", "email", FH_STRING); 

// set the auto completion for the field Color 
$form -> setAutoCompleteAfter("email", "@", $providers); 








//button for submitting
$form->submitButton();

//set the 'commit-after-form' function
$form->onCorrect('doRun');

//the 'commit-after-form' function
function doRun($data) {
	echo "Hello " . $data['name'] . ", you are " . $data['age'] . " years old!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Prueba Formularios</title>
</head>
<body>
<?php
//display the form
$form->flush();

?>

</body>
</html>