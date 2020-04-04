<?php declare(strict_types=1);

require_once 'helper/dbFormhandlerTestCase.php';


final class dbFormhandler_dbSelectFieldTest extends dbFormhandlerTestCase
{
    private array $_expectedResult;

    public function test_dbSelectField(): void
    {
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->stringStartsWith('SELECT keyField, valueField FROM `loadFromTable`'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            ' ORDER BY `valueField`',
            FH_NOT_EMPTY
        );

        $r = (string)$form->flush(true);
 
        $expected = "<td valign='top' align='right'>Options from a table</td>";
        $this->assertStringContainsString($expected, $r);
 
        $expected = '<select name="saveInField" id="saveInField" size="1">';
        $this->assertStringContainsString($expected, $r);
 
        $expected = '<option  value="1" >foo</option>';
        $this->assertStringContainsString($expected, $r);
 
        $expected = '<option  value="2" >bar</option>';
        $this->assertStringContainsString($expected, $r);
    }

    public function test_dbSelectField_insert(): void
    {
        $_POST['FormHandler_submit'] = "1";
        $_POST['saveInField'] = "2";
        
        $form = new dbFormHandler();

        $this->setConnectedTable($form, "test");
        $this->createMocksForTable();

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query($this->stringStartsWith('SELECT keyField, valueField FROM `loadFromTable`'))
                ->willReturnResultSet([
                    ['keyField' => 1, 'valueField' => 'foo'],
                    ['keyField' => 2, 'valueField' => 'bar'],
                ]);
    
        $form->dbSelectField(
            'Options from a table',
            'saveInField',
            'loadFromTable',
            array('keyField', 'valueField'),
            ' ORDER BY `valueField`',
            FH_NOT_EMPTY
        );

        $this->getDatabaseMock()
                ->expects($this->once())
                ->query("INSERT INTO `test` (
                    `saveInField`) VALUES (
                    '2'
                  );")
                ->willSetAffectedRows(1)
                ->willSetLastInsertId(4711);


        $form->onSaved(array($this, "callback_dbSelectField_insert_onSaved"));

        $r = $form->flush(true);

        $this->assertEquals("", $r);
        $this->assertEquals(4711, $this->_expectedResult['id']);
        $this->assertEquals('2', $this->_expectedResult['values']['saveInField']);
    }
    function callback_dbSelectField_insert_onSaved(int $id, array $values, dbFormHandler $form)
    {
        $this->_expectedResult['id'] = $id;
        $this->_expectedResult['values'] = $values;
    }
};
