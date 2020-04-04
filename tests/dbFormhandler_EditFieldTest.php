<?php

declare(strict_types=1);

require_once 'helper/dbFormhandlerTestCase.php';


final class dbFormhandler_EditFieldTest extends dbFormhandlerTestCase
{
    public function test_EditField_insert_noValues(): void
    {
        $_POST['FormHandler_submit'] = "1";

        $form = new dbFormHandler();

        $this->assertTrue($form->insert);
        $this->assertFalse($form->edit);

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("INSERT INTO `test` (
                    `textNullable`, 
                    `textNotNullable`) VALUES (
                    NULL,
                    ''
                  );")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4712);

        $form->onSaved(array($this, "callback_EditField_insert_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4712, $this->_expectedResult['id']);
        $this->assertEmpty($this->_expectedResult['values']['textNullable']);
        $this->assertEmpty($this->_expectedResult['values']['textNotNullable']);
    }
 
    public function test_EditField_insert(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['textNullable'] = "thetext";
        $_POST['textNotNullable'] = "anothertext";

        $form = new dbFormHandler();

        $this->assertTrue($form->insert);
        $this->assertFalse($form->edit);

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("INSERT INTO `test` (
                    `textNullable`, 
                    `textNotNullable`) VALUES (
                    'thetext',
                    'anothertext'
                  );")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4713);

        $form->onSaved(array($this, "callback_EditField_insert_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4713, $this->_expectedResult['id']);
        $this->assertEquals('thetext', $this->_expectedResult['values']['textNullable']);
        $this->assertEquals('anothertext', $this->_expectedResult['values']['textNotNullable']);
    }
    
    public function test_EditField_update_noValues(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_GET['id'] = "4714";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("UPDATE `test` SET 
                `textNullable` = NULL, 
                `textNotNullable` = '' WHERE 
                 `id` = '4714'");

        $form->onSaved(array($this, "callback_EditField_insert_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4714, $this->_expectedResult['id']);
        $this->assertEmpty($this->_expectedResult['values']['textNullable']);
        $this->assertEmpty($this->_expectedResult['values']['textNotNullable']);
    }
 
    public function test_EditField_update(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_GET['id'] = "4715";
        $_POST['textNullable'] = "thetext";
        $_POST['textNotNullable'] = "anothertext";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("UPDATE `test` SET 
                `textNullable` = 'thetext', 
                `textNotNullable` = 'anothertext' WHERE 
                 `id` = '4715'");

        $form->onSaved(array($this, "callback_EditField_insert_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4715, $this->_expectedResult['id']);
        $this->assertEquals('thetext', $this->_expectedResult['values']['textNullable']);
        $this->assertEquals('anothertext', $this->_expectedResult['values']['textNotNullable']);
    }

    function callback_EditField_insert_onSaved(int $id, array $values, dbFormHandler $form)
    {
        $this->_expectedResult['id'] = $id;
        $this->_expectedResult['values'] = $values;
    }
};
