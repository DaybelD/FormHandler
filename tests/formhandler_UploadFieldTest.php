<?php

declare(strict_types=1);

require_once 'helper/formhandlerTestCase.php';


final class formhandler_UploadFieldTest extends FormhandlerTestCase
{
    private ?string $_tempPath = null;
    protected function setUp(): void
    {
        parent::setUp();

        $this->_tempPath = sys_get_temp_dir() . "/uploadtest";

        if (!is_dir($this->_tempPath))
            mkdir($this->_tempPath);
    }
    public function test_new(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield");

        $this->assertEmpty($form->getValue("uploadfield"));

        $this->assertFormFlushContains($form, ['Uploadfield:<input type="file" name="uploadfield" id="uploadfield" onchange="fh_checkUpload(this, \'jpg jpeg png gif doc txt bmp tif tiff pdf\', \'Only the following extensions are allowed: %s.\')"  />error_uploadfield']);
    }

    public function test_new_config(): void
    {
        $config = array(
            "type" => "jpg jpeg jpe",
            "mime" => "image/jpeg image/jpg"
          ); 
          
          $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield", $config);

        $this->assertEmpty($form->getValue("uploadfield"));

        $this->assertFormFlushContains($form, ['Uploadfield:<input type="file" name="uploadfield" id="uploadfield" onchange="fh_checkUpload(this, \'jpg jpeg jpe\', \'Only the following extensions are allowed: %s.\')"  />error_uploadfield']);
    }

    public function test_posted(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_FILES = [
            'uploadfield' => [
                'error'    => UPLOAD_ERR_OK,
                'name'     => 'test.jpg',
                'size'     => 8009,
                'tmp_name' => dirname(__FILE__) . "/test.jpg",
                'type'     => 'image/jpeg'
            ]
        ];
        $config = array(
            "type" => "jpg jpeg jpe",
            "mime" => "image/jpeg image/jpg",
            "name" => "uploaded",
            "path" => $this->_tempPath
          ); 

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield", $config);

        $this->assertTrue($form->isUploaded("uploadfield"));
        $this->assertEquals("uploaded.jpg", $form->getValue("uploadfield"));

        $data  = $form->GetFileInfo("uploadfield");

        $this->assertEquals($_FILES['uploadfield'], $data);

        $this->setPrivateProperty($form, "_unittestmode", true);
        $this->assertFlush($form);

        $this->assertTrue(unlink("{$this->_tempPath}/uploaded.jpg"));
    }

    public function test_posted_fillvalue_byinvalid(): void
    {
        $config = array(
            "type" => "jpg jpeg jpe",
            "mime" => "image/jpeg image/jpg",
            "path" => $this->_tempPath
          ); 

        $_POST['FormHandler_submit'] = "1";
        $_FILES = [
            'uploadfield' => [
                'error'    => UPLOAD_ERR_OK,
                'name'     => 'test.jpg',
                'size'     => 8009,
                'tmp_name' => dirname(__FILE__) . "/test.jpg",
                'type'     => 'image/jpeg'
            ]
        ];

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield", $config);
        $form->textField("TextForFailure", "textforfailure");

        $this->assertTrue($form->isUploaded("uploadfield"));

        $data  = $form->GetFileInfo("uploadfield");

        $this->assertEquals($_FILES['uploadfield'], $data);

        $form->setError("textforfailure", "isfails");

        $this->assertFormFlushContains($form, ['Uploadfield:<input type="file" name="uploadfield" id="uploadfield" onchange="fh_checkUpload(this, \'jpg jpeg jpe\', \'Only the following extensions are allowed: %s.\')"  class="error" />error_uploadfield',
                                                "<span id=\"error_uploadfield\" class=\"error\">Because the form isn't valid the file <b>test.jpg</b> has to be selected again if you want to send it with this form!<br />"]);
    }

    public function test_validator(): void
    {
        $config = array(
            "type" => "jpg jpeg jpe",
            "mime" => "image/jpeg image/jpg",
            "required" => true
          ); 

        $_POST['FormHandler_submit'] = "1";
        $_FILES = [
            'uploadfield' => [
                'error'    => UPLOAD_ERR_NO_FILE,
                'name'     => '',
                'size'     => 0,
                'tmp_name' => "",
                'type'     => ""
            ]
        ];

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield", $config, FH_NOT_EMPTY);

        $this->assertEmpty($form->getValue("uploadfield"));

        $t = $form->catchErrors(false);

        $this->assertEquals('<span id="error_uploadfield" class="error">You did not enter a correct value for this field!</span>',
                                $t['uploadfield']);
    }

    public function test_new_extra(): void
    {
        $form = new FormHandler();

        $this->assertFalse($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield", array(), null, 'data-old="123"');

        $this->assertEmpty($form->getValue("uploadfield"));

        $this->assertFormFlushContains($form, ['Uploadfield:<input type="file" name="uploadfield" id="uploadfield" onchange="fh_checkUpload(this, \'jpg jpeg png gif doc txt bmp tif tiff pdf\', \'Only the following extensions are allowed: %s.\')" data-old="123" />error_uploadfield']);
    }

    public function test_resizeImage(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_FILES = [
            'uploadfield' => [
                'error'    => UPLOAD_ERR_OK,
                'name'     => 'test.jpg',
                'size'     => 8009,
                'tmp_name' => dirname(__FILE__) . "/test.jpg",
                'type'     => 'image/jpeg'
            ]
        ];
        $config = array(
            "type" => "jpg jpeg jpe",
            "mime" => "image/jpeg image/jpg",
            "name" => "uploaded",
            "path" => $this->_tempPath
          ); 

        $form = new FormHandler();

        $this->assertTrue($form->isPosted());

        $form->uploadField("Uploadfield", "uploadfield", $config);

        $this->assertTrue($form->isUploaded("uploadfield"));

        $data  = $form->GetFileInfo("uploadfield");

        $this->assertEquals($_FILES['uploadfield'], $data);

        $form->resizeImage("uploadfield", $this->_tempPath . "/resize" . $form->getValue("uploadfield"), 30, 30);

        $this->setPrivateProperty($form, "_unittestmode", true);
        $this->assertFlush($form);

        $image_info = getimagesize("{$this->_tempPath}/resizeuploaded.jpg");

        $this->assertEquals(30, $image_info[0]);

        $this->assertTrue(unlink("{$this->_tempPath}/uploaded.jpg"));
        $this->assertTrue(unlink("{$this->_tempPath}/resizeuploaded.jpg"));
    }
};
