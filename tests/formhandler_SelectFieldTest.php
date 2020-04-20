<?php

declare(strict_types=1);

require_once 'helper/formhandlerTestCase.php';


final class formhandler_SelectFieldTest extends FormhandlerTestCase
{
    private $aOptions = [
        "o1" => "Option1",
        "o2" => "Option2",
        "o3" => "Option3"
    ];
    
    public function test_new(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions, null, true);

        $this->assertEmpty($form->getValue("selectfield"));

        $this->assertFormFlushContains($form, ['Selectfield:<select name="selectfield" id="selectfield" size="1">',
                                                '<option  value="o1" >Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3" >Option3</option>',
                                                '</select>error_selectfield']);
    }

    public function test_new_multiple(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions, null, true, true);

        $this->assertEmpty($form->getValue("selectfield"));

        $this->assertFormFlushContains($form, ['Selectfield:<select name="selectfield[]" id="selectfield" size="4" multiple="multiple">',
                                                '<option  value="o1" >Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3" >Option3</option>',
                                                '</select>error_selectfield']);
    }

    public function test_new_label(): void
    {
        $aOptions = array_merge(['__LABEL(A)__' => 'Label1'], $this->aOptions);
        $aOptions['__LABEL(B)__'] = 'Label2';
        $aOptions['o4'] = 'Option4';
        $aOptions['o5'] = 'Option5';
        $aOptions['o6'] = 'Option6';
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $aOptions, null, true);

        $this->assertEmpty($form->getValue("selectfield"));

        $this->assertFormFlushContains($form, ['Selectfield:<select name="selectfield" id="selectfield" size="1">',
                                                '<optgroup label="Label1">',
                                                '<option  value="o1" >Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3" >Option3</option>',
                                                "</optgroup>\n\t<optgroup label=\"Label2\">",
                                                '<option  value="o4" >Option4</option>',
                                                '<option  value="o5" >Option5</option>',
                                                "<option  value=\"o6\" >Option6</option>\n\t</optgroup>",
                                                '</select>error_selectfield']);
    }

    public function test_new_ArrayKeyAsValue_false(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions, null, false);

        $this->assertEmpty($form->getValue("selectfield"));

        $this->assertFormFlushContains($form, ['Selectfield:<select name="selectfield" id="selectfield" size="1">',
                                                '<option  value="Option1" >Option1</option>',
                                                '<option  value="Option2" >Option2</option>',
                                                '<option  value="Option3" >Option3</option>',
                                                '</select>error_selectfield']);
    }

    public function test_posted(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['selectfield'] = "o1";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions);

        $this->assertEquals("o1", $form->getValue("selectfield"));

        $this->assertTrue($form->isCorrect());
    }

    public function test_posted_fillvalue_byinvalid(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['selectfield'] = "o1";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions);

        $this->assertEquals("o1", $form->getValue("selectfield"));

        $form->setError("selectfield", "forcedError");

        $this->assertFormFlushContains($form, ['Selectfield:<select class="error" name="selectfield" id="selectfield" size="1">',
                                                '<option  value="o1"  selected="selected">Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3" >Option3</option>',
                                                '</select>error_selectfield<span id="error_selectfield" class="error">forcedError</span>']);
    }

    public function test_posted_multiple_fillvalue_byinvalid(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['selectfield'] = ["o1", "o3"];

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions, null, null, true);

        $this->assertEquals(["o1", "o3"], $form->getValue("selectfield"));

        $form->setError("selectfield", "forcedError");

        $this->assertFormFlushContains($form, ['Selectfield:<select class="error" name="selectfield[]" id="selectfield" size="4" multiple="multiple">',
                                                '<option  value="o1"  selected="selected">Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3"  selected="selected">Option3</option>',
                                                '</select>error_selectfield<span id="error_selectfield" class="error">forcedError</span>']);
    }

    public function test_new_size(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions, null, true, null, 2);

        $this->assertEmpty($form->getValue("selectfield"));

        $this->assertFormFlushContains($form, ['Selectfield:<select name="selectfield" id="selectfield" size="2">',
                                                '<option  value="o1" >Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3" >Option3</option>',
                                                '</select>error_selectfield']);
    }
    public function test_new_extra(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->selectField("Selectfield", "selectfield", $this->aOptions, null, true, null, null, 'data-old="123"');

        $this->assertEmpty($form->getValue("selectfield"));

        $this->assertFormFlushContains($form, ['Selectfield:<select name="selectfield" id="selectfield" size="1" data-old="123">',
                                                '<option  value="o1" >Option1</option>',
                                                '<option  value="o2" >Option2</option>',
                                                '<option  value="o3" >Option3</option>',
                                                '</select>error_selectfield']);
    }

};
