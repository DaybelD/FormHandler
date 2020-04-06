<?php

declare(strict_types=1);

require_once 'helper/dbFormhandlerTestCase.php';


final class dbFormhandler_PassFieldTest extends dbFormhandlerTestCase
{
    public function test_insert_noValues(): void
    {
        $_POST['FormHandler_submit'] = "1";

        $form = new dbFormHandler();

        $this->assertTrue($form->insert);
        $this->assertFalse($form->edit);

        $this->setConnectedTable($form, "test");

        $form->passField("Your password", "pass", FH_PASSWORD);

        $form->onSaved(array($this, "callback_EditField_insert_onSaved"));
        
         $e = $form->catchErrors();

         $expected  = "You did not enter a correct value for this field!";
         $this->assertStringContainsString($expected, $e['pass']);
    }
 
    public function test_insert(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['pass'] = "secret";
        $_POST['textNullable'] = "thetext";

        $form = new dbFormHandler();

        $this->assertTrue($form->insert);
        $this->assertFalse($form->edit);

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $form->passField("Your password", "pass", FH_PASSWORD);
        $form->textField("TextNullable", "textNullable");

        // only textfield, not passfield
        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("INSERT INTO test (textNullable) VALUES ('thetext');")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4711);

        $form->onSaved(array($this, "callback_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4711, $this->_expectedResult['id']);
        $this->assertEquals('secret', $this->_expectedResult['values']['pass']);
        $this->assertEquals('thetext', $this->_expectedResult['values']['textNullable']);
    }
    
    public function test_update_noValues(): void
    {
        $this->createMocksForTable();

        $_POST['FormHandler_submit'] = "1";
        $_GET['id'] = "4714";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->getDatabaseMock()
                ->expects($this->exactly(1))
                ->query($this->matches("SELECT * FROM test WHERE id = '4714'"))
                ->willReturnResultSet([
                    ['id' => '4714', 'textNullable' => 'text'],
                ]);

        $this->setConnectedTable($form, "test");

        $form->passField("Your password", "pass", FH_PASSWORD);
        $form->textField("TextNullable", "textNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("UPDATE test SET textNullable = NULL WHERE id = '4714'");

        $form->onSaved(array($this, "callback_onSaved"));
        
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

        $form->onSaved(array($this, "callback_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4715, $this->_expectedResult['id']);
        $this->assertEquals('thetext', $this->_expectedResult['values']['textNullable']);
        $this->assertEquals('anothertext', $this->_expectedResult['values']['textNotNullable']);
    }

    public function callback_onSaved(int $id, array $values, dbFormHandler $form) : void
    {
        $this->_expectedResult['id'] = $id;
        $this->_expectedResult['values'] = $values;
    }
};
