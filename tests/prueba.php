<?php
include "FH3/src/class.FormHandler.php";
fh_conf('FH_FHTML_DIR', 'FH3/FHTML/');

//create a new FormHandler object
$form = new FormHandler();

//some fields.. (see manual for examples)
$form->textField("Name", "name", FH_STRING, 20, 40);
$form->textField("Age", "age", FH_INTEGER, 4, 2);

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