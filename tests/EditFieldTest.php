<?php declare(strict_types=1);

require_once 'helper/FormhandlerTestCase.php';

final class EditFieldTest extends FormhandlerTestCase
{
    final protected function getFormhandlerType() : string
    {
        return "Formhandler";
    } 

    public function testEditField() : void
    {
        $form = new FormHandler();

        $form->textField("Text1", "text1");

        $expected = "  <tr>\n".
        "    <td valign='top' align='right'>Text1</td>\n".
        "    <td valign='top'>:</td>\n".
        "    <td valign='top'><input type=\"text\" name=\"text1\" id=\"text1\" value=\"\" size=\"20\" />  <span id='error_text1' class='error'></span></td>\n".
        "  </tr>\n";

        $this->assertStringContainsString($expected, $form->flush(true));
    }

    public function testEditField_Size40() : void
    {
        $form = new FormHandler();

        $form->textField("Text1", "text1", null, 40);

        $expected = "  <tr>\n".
        "    <td valign='top' align='right'>Text1</td>\n".
        "    <td valign='top'>:</td>\n".
        "    <td valign='top'><input type=\"text\" name=\"text1\" id=\"text1\" value=\"\" size=\"40\" />  <span id='error_text1' class='error'></span></td>\n".
        "  </tr>\n";

        $this->assertStringContainsString($expected, $form->flush(true));
    }

    public function testEditField_MaxLength40() : void
    {
        $form = new FormHandler();

        $form->textField("Text1", "text1", null, null, 40);

        $expected = "  <tr>\n".
        "    <td valign='top' align='right'>Text1</td>\n".
        "    <td valign='top'>:</td>\n".
        "    <td valign='top'><input type=\"text\" name=\"text1\" id=\"text1\" value=\"\" size=\"20\" maxlength=\"40\" />  <span id='error_text1' class='error'></span></td>\n".
        "  </tr>\n";

        $this->assertStringContainsString($expected, $form->flush(true));
    }
    
    public function testEditField_Extra() : void
    {
        $form = new FormHandler();

        $form->textField("Text1", "text1", null, null, null, "abc='123'");

        $expected = "  <tr>\n".
        "    <td valign='top' align='right'>Text1</td>\n".
        "    <td valign='top'>:</td>\n".
        "    <td valign='top'><input type=\"text\" name=\"text1\" id=\"text1\" value=\"\" size=\"20\"  abc='123' />  <span id='error_text1' class='error'></span></td>\n".
        "  </tr>\n";

        $this->assertStringContainsString($expected, $form->flush(true));
    }

    public function testEditField_FHDIGIT_correct() : void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['digit1'] = '1';

        $form = new FormHandler();

        $form->textField("Digit1", "digit1", FH_DIGIT);
        $form->textField("Digit2", "digit2", _FH_DIGIT);

        $this->assertEmpty($form->flush(true));
        $this->assertTrue($form->isCorrect());
    }

    public function testEditField_FHDIGIT_incorrect() : void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['digit2'] = 'a';

        $form = new FormHandler();

        $form->textField("Digit1", "digit1", FH_DIGIT);
        $form->textField("Digit2", "digit2", FH_DIGIT);

        $output = $form->flush(true);
        $this->assertStringContainsString('<span id="error_digit1" class="error">You did not enter a correct value for this field!</span>', $output);
        $this->assertStringContainsString('<span id="error_digit2" class="error">You did not enter a correct value for this field!</span>', $output);

        $expectedDigit2 = "  <tr>\n".
        "    <td valign='top' align='right'>Digit2</td>\n".
        "    <td valign='top'>:</td>\n".
        "    <td valign='top'><input type=\"text\" name=\"digit2\" id=\"digit2\" value=\"a\" size=\"20\" class=\"error\" />  <span id='error_digit2' class='error'><span id=\"error_digit2\" class=\"error\">You did not enter a correct value for this field!</span></span></td>\n".
        "  </tr>\n";

        $this->assertStringContainsString($expectedDigit2, $output);

        $this->assertFalse($form->isCorrect());
    }
}