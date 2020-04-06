<?php declare(strict_types=1);

require_once 'helper/dbFormhandlerTestCase.php';


final class dbFormhandler_dbSelectFieldTest extends dbFormhandlerTestCase
{
    private array $_expectedResult;

    public function test_show(): void
    {
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->matches('SELECT keyField, valueField FROM loadFromTable ORDER BY valueField'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY valueField',
            FH_NOT_EMPTY
        );

        $aExpected = [
            "<td valign='top' align='right'>Options from a table</td>",
            '<select name="saveInField" id="saveInField" size="1">',
            '<option  value="1" >foo</option>',
            '<option  value="2" >bar</option>'
        ];

        $this->assertFormFlushContains($form, $aExpected);
    }

    public function test_edit(): void
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
                    ['id' => '123', 'saveInField' => '2'],
                ]);

        $this->setConnectedTable($form, "test");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->matches('SELECT keyField, valueField FROM loadFromTable ORDER BY valueField'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY valueField',
            FH_NOT_EMPTY
        );

        $this->assertEquals("2", $form->getValue("saveInField"));

        $aExpected = [
            "<td valign='top' align='right'>Options from a table</td>",
            '<select name="saveInField" id="saveInField" size="1">',
            '<option  value="1" >foo</option>',
            '<option  value="2"  selected="selected">bar</option>'
        ];

        $a = $this->assertFormFlushContains($form, $aExpected);
    }

    public function test_insert(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['saveInField'] = "2";
        
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->stringStartsWith('SELECT keyField, valueField FROM loadFromTable'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY valueField',
            FH_NOT_EMPTY
        );

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("INSERT INTO test (saveInField) VALUES ('2');")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4711);


        $form->onSaved(array($this, "callback_onSaved"));

        $r = $form->flush(true);

        $this->assertEquals("", $r);
        $this->assertEquals(4711, $this->_expectedResult['id']);
        $this->assertEquals('2', $this->_expectedResult['values']['saveInField']);
    }

    public function test_insert_wrongValue(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['saveInField'] = "3";
        
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->stringStartsWith('SELECT keyField, valueField FROM loadFromTable'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY `valueField`',
            FH_NOT_EMPTY
        );

        $e = $form->catchErrors();

        // $this->getDatabaseMock()
        //         ->expects($this->once())
        //         ->query("INSERT INTO `test` (
        //             `saveInField`) VALUES (
        //             '2'
        //           );")
        //         ->willSetAffectedRows(1)
        //         ->willSetLastInsertId(4711);


        // $form->onSaved(array($this, "callback_onSaved"));

        $r = $form->flush(true);

        // $this->assertEquals("", $r);
        // $this->assertEquals(4711, $this->_expectedResult['id']);
        // $this->assertEquals('2', $this->_expectedResult['values']['saveInField']);
    }

    public function test_multiple_show(): void
    {
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->matches('SELECT keyField, valueField FROM loadFromTable ORDER BY valueField'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY valueField',
            FH_NOT_EMPTY
            ,true
        );

        $aExpected = [
            "<td valign='top' align='right'>Options from a table</td>",
            '<select name="saveInField[]" id="saveInField" size="4" multiple="multiple">',
            '<option  value="1" >foo</option>',
            '<option  value="2" >bar</option>'
        ];

        $this->assertFormFlushContains($form, $aExpected);
    }

    public function test_multiple_edit(): void
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
                    ['id' => '123', 'saveInField' => '2'],
                ]);

        $this->setConnectedTable($form, "test");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->matches('SELECT keyField, valueField FROM loadFromTable ORDER BY valueField'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY valueField',
            FH_NOT_EMPTY
        );

        $this->assertEquals("2", $form->getValue("saveInField"));

        $aExpected = [
            "<td valign='top' align='right'>Options from a table</td>",
            '<select name="saveInField" id="saveInField" size="1">',
            '<option  value="1" >foo</option>',
            '<option  value="2"  selected="selected">bar</option>'
        ];

        $a = $this->assertFormFlushContains($form, $aExpected);
    }

    public function test_multiple_insert(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['saveInField'] = ['0' => "2", '1' => "1"];
        
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->stringStartsWith('SELECT keyField, valueField FROM loadFromTable'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            'ORDER BY valueField',
            FH_NOT_EMPTY
        );

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("INSERT INTO test (saveInField) VALUES ( '2, 1' );")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4711);


        $form->onSaved(array($this, "callback_onSaved"));

        $r = $form->flush(true);

        $this->assertEquals("", $r);
        $this->assertEquals(4711, $this->_expectedResult['id']);
        $this->assertEquals('2, 1', $this->_expectedResult['values']['saveInField']);
    }

    public function callback_onSaved(int $id, array $values, dbFormHandler $form) : void
    {
        $this->_expectedResult['id'] = $id;
        $this->_expectedResult['values'] = $values;
    }
};
