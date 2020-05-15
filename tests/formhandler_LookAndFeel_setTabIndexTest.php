<?php

declare(strict_types=1);

require_once 'helper/formhandlerTestCase.php';


final class formhandler_LookAndFeel_setTabIndexTest extends FormhandlerTestCase
{
    public function test_byString(): void
    {
        $form = new FormHandler();

        $form->textField("Field 1", "fld1");
        $form->textField("Field 2", "fld2");
        $form->textField("Field 3", "fld3"); 

        $form->setTabIndex("fld2, fld3, fld1"); 

        $this->assertFormFlushContains($form, ['Field 1:<input type="text" name="fld1" id="fld1" value="" size="20" tabindex="3" />error_fld1',
                                                'Field 2:<input type="text" name="fld2" id="fld2" value="" size="20" tabindex="1" />error_fld2',
                                                'Field 3:<input type="text" name="fld3" id="fld3" value="" size="20" tabindex="2" />error_fld3'
                                                ]);
    }

    public function test_byArray(): void
    {
        $form = new FormHandler();

        $form->textField("Field 1", "fld1");
        $form->textField("Field 2", "fld2");
        $form->textField("Field 3", "fld3"); 

        $tabs = array(
            3 => "fld1",
            1 => "fld2 ",
            2 => "fld3",
        );
  
        $form->setTabIndex($tabs); 

        $this->assertFormFlushContains($form, ['Field 1:<input type="text" name="fld1" id="fld1" value="" size="20" tabindex="3" />error_fld1',
                                                'Field 2:<input type="text" name="fld2" id="fld2" value="" size="20" tabindex="1" />error_fld2',
                                                'Field 3:<input type="text" name="fld3" id="fld3" value="" size="20" tabindex="2" />error_fld3'
                                                ]);
    }

    public function test_byArray2(): void
    {
        $form = new FormHandler();

        $form->textField("Field 1", "fld1");
        $form->textField("Field 2", "fld2");
        $form->textField("Field 3", "fld3"); 

        $tabs = array(
            "fld2",
            "fld3",
            "fld1",
        );
  
        $form->setTabIndex($tabs); 

        $this->assertFormFlushContains($form, ['Field 1:<input type="text" name="fld1" id="fld1" value="" size="20" tabindex="3" />error_fld1',
                                                'Field 2:<input type="text" name="fld2" id="fld2" value="" size="20" tabindex="1" />error_fld2',
                                                'Field 3:<input type="text" name="fld3" id="fld3" value="" size="20" tabindex="2" />error_fld3'
                                                ]);
    }

    public function test_error(): void
    {
        $form = new FormHandler();

        $form->textField("Field 1", "fld1");

        $tabs = array(
            1 => "fld4",
        );
  
        $form->setTabIndex($tabs); 

        $this->expectError();
        $this->expectErrorMessage('Error, try to set the tabindex of an unknown field "fld4"!');

        $form->flush();
    }

    public function test_error2(): void
    {
        $form = new FormHandler();

        $form->textField("Field 1", "fld1");
        $form->textField("Field 2", "fld2");
        $form->textField("Field 3", "fld3"); 

        $tabs = array(
            3 => "fld1",
            1 => "fld2",
            2 => "fld3",
            4 => ""
        );
  
        $this->expectError();
        $this->expectExceptionMessage("Undefined variable: tabs");
        $form->setTabIndex($tabs);
    }
};
