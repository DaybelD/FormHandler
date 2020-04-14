<?php  declare(strict_types=1); ?>
<!DOCTYPE html>
<head>
	<meta charset="utf-8">
</head>
<body>
<?php

define('FH_FHTML_DIR', 'http://formhandler.test/src/FHTML/' );
ini_set("display_errors", "1");
@error_reporting(E_USER_WARNING | E_ALL);

require_once '../../vendor/autoload.php';

// make a new formhandler object
$form = new FormHandler();
// passfield
$form -> passField("Password", "password", FH_STRING);

// get the value of the field
$value = $form->value("password");

// save the password MD5 encrypted
// so OVERWRITE the current value!!
$form->addValue(
  "password", 
  "MD5('". ctype_lower( $value ) ."')", 
);

// submitbutton
$form -> submitButton("Save");

// set the onCorrect function
$form -> onCorrect("doRun");

// flush
$form -> flush();

// commit after form function
function doRun($data) 
{
    echo "MD5 encrypted password: ".$data['password'];
} 