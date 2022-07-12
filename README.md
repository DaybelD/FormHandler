# FormHandler

[![GitHub license](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://raw.githubusercontent.com/FormHandler/FormHandler/master/LICENSE)
[![Build Status](https://travis-ci.org/FormHandler/FormHandler.svg?branch=master)](https://travis-ci.org/FormHandler/FormHandler)

FormHandler is a PHP written "module" which allows you to create dynamic forms in an easy way.
So easy that you can build a fully working form, including field validations, within 10 lines!

## Installation

Copy the package to some site on your project.

When the vendor directory is outside the root directory of your web application use a symlink on the FHTML directory to make it publicly available.






For example: map vendor/formhandler/formhandler/src/FHTML to /FHTML/ of your website root. Then add to your application


## Basic Usage

```php
<?php

//include the class (only needed when not using Composer)
include "FH3/class.FormHandler.php";
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

//display the form
$form->flush();

//the 'commit-after-form' function
function doRun($data)
{
    echo "Hello ". $data['name'].", you are ".$data['age'] ." years old!";
}
```

### Documentation

For an extended version of the documentation please check our manual located at
http://www.fh3.nl/manual/manual.html

### License

FormHandler is licensed under the GNU Lesser General Public License Version 2.1 - see the `LICENSE` file for details

## Change log realizadas por 3Valtech

*Eliminado el campo ListField
*Eliminado el campo jsDateField
*Eliminado el campo jsDateTextField
*Eliminadas las referencias en config.inc
*Eliminado jscalendar (innecesario)