<?php

declare(strict_types=1);

require_once 'helper/formhandlerTestCase.php';


final class formhandler_CaptchaFieldTest extends FormhandlerTestCase
{
    public function test_new(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->CaptchaField("Captchafield", "captchafield");

        $this->assertEmpty($form->getValue("captchafield"));

        $this->assertFormFlushContains($form, ['<input type="image" src="',
                                                'FHTML/securimage/securimage_show.php?sid=',
                                                'name="button1" id="button1" onclick="return false;" style="cursor:default;" />error_button1Captchafield:<input type="text" name="captchafield" id="captchafield" value="" size="20" />error_captchafield']);
    }

    public function test_posted(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['captchafield'] = "textvalue";

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->CaptchaField("Captchafield", "captchafield");

        $this->assertFalse($form->isCorrect());

        $t = $form->catchErrors(false);

        $this->assertEquals('<span id="error_captchafield" class="error">You did not enter a correct value for this field!</span>',
                                $t['captchafield']);
    }
};
