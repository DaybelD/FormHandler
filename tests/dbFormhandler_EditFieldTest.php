<?php

declare(strict_types=1);

require_once 'helper/dbFormhandlerTestCase.php';


final class dbFormhandler_EditFieldTest extends dbFormhandlerTestCase
{
    public function test_edit_noDataset(): void
    {
        $this->createMocksForTable();

        $_GET['id'] = "123";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->getDatabaseMock()
                ->expects($this->exactly(1))
                ->query($this->matches("SELECT * FROM test WHERE id = '123'"))
                ->willReturnResultSet([
                ]);

        $this->expectError();
        $this->expectErrorMessage("Try to edit a none existing record!");

        $this->setConnectedTable($form, "test");
    }

    public function test_edit(): void
    {
        $this->createMocksForTable();

        $_GET['id'] = "100";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->getDatabaseMock()
                ->expects($this->exactly(1))
                ->query($this->matches("SELECT * FROM test WHERE id = '100'"))
                ->willReturnResultSet([
                    ['id' => '100', 'textNullable' => 'text1', 'textNotNullable' => 'text2'],
                ]);

        $this->setConnectedTable($form, "test");

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->assertEquals("text1", $form->getValue("textNullable"));
        $this->assertEquals("text2", $form->getValue("textNotNullable"));

        $this->assertFormFlushContains($form, ['id="textNullable" value="text1"', 'id="textNotNullable" value="text2"']);
    }

    public function test_insert_noValues(): void
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
                ->query("INSERT INTO test (textNullable, textNotNullable) VALUES (NULL, '');")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4712);

        $form->onSaved(array($this, "callback_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4712, $this->_expectedResult['id']);
        $this->assertEmpty($this->_expectedResult['values']['textNullable']);
        $this->assertEmpty($this->_expectedResult['values']['textNotNullable']);
    }
 
    public function test_insert(): void
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
                ->query("INSERT INTO test (textNullable, textNotNullable) VALUES ('thetext', 'anothertext');")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4713);

        $form->onSaved(array($this, "callback_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4713, $this->_expectedResult['id']);
        $this->assertEquals('thetext', $this->_expectedResult['values']['textNullable']);
        $this->assertEquals('anothertext', $this->_expectedResult['values']['textNotNullable']);
    }
    
    public function test_update_noValues(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_GET['id'] = "4714";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->createMocksForTable();
        $this->getDatabaseMock()
                ->expects($this->exactly(1))
                ->query($this->matches("SELECT * FROM test WHERE id = '4714'"))
                ->willReturnResultSet([
                    ['id' => '4714', 'textNullable' => 'text1', 'textNotNullable' => 'text2'],
                ]);

        $this->setConnectedTable($form, "test");

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("UPDATE test SET textNullable = NULL, textNotNullable = '' WHERE id = '4714'");

        $form->onSaved(array($this, "callback_onSaved"));
        
        $r = $form->flush(true);

        $this->assertEmpty($r);
        $this->assertEquals(4714, $this->_expectedResult['id']);
        $this->assertEmpty($this->_expectedResult['values']['textNullable']);
        $this->assertEmpty($this->_expectedResult['values']['textNotNullable']);
    }
 
    public function test_update(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_GET['id'] = "4715";
        $_POST['textNullable'] = "thetext";
        $_POST['textNotNullable'] = "anothertext";

        $form = new dbFormHandler();

        $this->assertFalse($form->insert);
        $this->assertTrue($form->edit);

        $this->createMocksForTable();
        $this->getDatabaseMock()
                ->expects($this->exactly(1))
                ->query($this->matches("SELECT * FROM test WHERE id = '4715'"))
                ->willReturnResultSet([
                    ['id' => '4715', 'textNullable' => 'text1', 'textNotNullable' => 'text2'],
                ]);
        $this->setConnectedTable($form, "test");

        $form->textField("TextNullable", "textNullable");
        $form->textField("TextNotNullable", "textNotNullable");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("UPDATE test SET textNullable = 'thetext', textNotNullable = 'anothertext' WHERE id = '4715'");

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
