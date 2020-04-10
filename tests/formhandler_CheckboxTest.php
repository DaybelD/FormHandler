<?php

declare(strict_types=1);

require_once 'helper/formhandlerTestCase.php';

final class formhandler_CheckboxTest extends FormhandlerTestCase
{
    public function test_new_single(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->checkBox("Checkbox", "checkbox");

        $this->assertEmpty($form->getValue("checkbox"));

        $this->assertFormFlushContains($form, ['Checkbox:<input type="checkbox" name="checkbox" id="checkbox_1" value="on" />error_checkbox']);
    }

    public function test_new_array(): void
    {
        $aChecks = array (
            "Check1",
            "Check2",
            "Check3"
        );
        
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->checkBox("Checkbox", "checkbox", $aChecks);

        $this->assertEmpty($form->getValue("checkbox"));

        $this->assertFormFlushContains($form, ['Checkbox:<input type="checkbox" name="checkbox[]" id="checkbox_1" value="0" /><label for="checkbox_1" class="noStyle">Check1</label><br />',
                                                '<input type="checkbox" name="checkbox[]" id="checkbox_2" value="1" /><label for="checkbox_2" class="noStyle">Check2</label><br />',
                                                '<input type="checkbox" name="checkbox[]" id="checkbox_3" value="2" /><label for="checkbox_3" class="noStyle">Check3</label><br />',
                                                'error_checkbox']);
    }

    public function test_posted_single(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['checkbox'] = "on";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->checkBox("Checkbox", "checkbox");

        $this->assertEquals("on", $form->getValue("checkbox"));
    }

    public function test_posted_array(): void
    {
        $aChecks = array (
            "Check1",
            "Check2",
            "Check3"
        );

        $_POST['FormHandler_submit'] = "1";
        $_POST['checkbox'] = ["Check1", "Check2"];

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->checkBox("Checkbox", "checkbox", $aChecks);

        $this->assertEquals(["Check1", "Check2"], $form->getValue("checkbox"));
    }

    public function test_posted_single_fillvalue_byinvalid(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['checkbox'] = "ona";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->checkBox("Checkbox", "checkbox");

        $this->assertEquals("ona", $form->getValue("checkbox"));

        $form->setError("checkbox", "forcedError");
        $this->assertFormFlushContains($form, ['Checkbox:<input type="checkbox" name="checkbox" id="checkbox_1" value="on" checked="checked" class="error" />error_checkbox',
                                                '<span id="error_checkbox" class="error">forcedError</span>']);
    }

    public function test_posted_array_fillvalue_byinvalid(): void
    {
        $aChecks = array (
            "Check1",
            "Check2",
            "Check3"
        );

        $_POST['FormHandler_submit'] = "1";
        $_POST['checkbox'] = ["Check1", "Check2"];

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->checkBox("Checkbox", "checkbox", $aChecks);

        $this->assertEquals(["Check1", "Check2"], $form->getValue("checkbox"));
    }

    public function test_posted_fillvalue_byinvalid(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['textfield'] = "textvalue";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->textField("Textfield", "textfield");

        $this->assertEquals("textvalue", $form->getValue("textfield"));

        $form->setError("textfield", "forcedError");

        $this->assertFormFlushContains($form, ['Textfield:<input type="text" name="textfield" id="textfield" value="textvalue" size="20" class="error" />error_textfield',
                                                '<span id="error_textfield" class="error">forcedError</span>']);

    }

    public function test_validator(): void
    {
        $_POST['FormHandler_submit'] = "1";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->textField("Textfield", "textfield", FH_NOT_EMPTY);

        $this->assertEmpty($form->getValue("textfield"));

        $t = $form->catchErrors(false);

        $this->assertEquals('<span id="error_textfield" class="error">You did not enter a correct value for this field!</span>',
                                $t['textfield']);
    }

    public function test_new_size(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->textField("Textfield", "textfield", null, 123);

        $this->assertEmpty($form->getValue("textfield"));

        $this->assertFormFlushContains($form, ['Textfield:<input type="text" name="textfield" id="textfield" value="" size="123" />error_textfield']);
    }

    public function test_new_maxlength(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->textField("Textfield", "textfield", null, null, 123);

        $this->assertEmpty($form->getValue("textfield"));

        $this->assertFormFlushContains($form, ['Textfield:<input type="text" name="textfield" id="textfield" value="" size="20" maxlength="123" />error_textfield']);
    }

    public function test_new_extra(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->textField("Textfield", "textfield", null, null, null, 'data-old="123"');

        $this->assertEmpty($form->getValue("textfield"));

        $this->assertFormFlushContains($form, ['Textfield:<input type="text" name="textfield" id="textfield" value="" size="20"  data-old="123"']);
    }

};
