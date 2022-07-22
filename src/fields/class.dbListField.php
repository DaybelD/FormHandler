<?php
/**
 * class dbListField
 *
 * Create a listfield from records retrieved from the db
 * Crea un campo de lista a partir de registros recuperados de la base de datos
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class dbListField extends ListField
{

    /**
     * dbListField::dbListField()
     *
     * Create a new dbListField object/ crear un nuevo objeto dbListField
     *
     * @param object &$oForm: the form where the datefield is located on/ formulario donde esta localizado el campo de fecha
     * @param string $sName: the name of the datefield/ nombre del campo de fecha
     * @param object $oDb: object of the database handler/ objeto del manejador de base de datos
     * @param string $sTable: the table to get the fields from/ tabla para obtener los campos de
     * @param mixed $mFields: array of string with the names of the fields which data we should get/ matriz de cadenas con los nombres de los campos cuyos datos debemos obtener
     * @param string $sExtraSQL: extra SQL statements/ declaraciones SQL adicionales
     * @return dbListField
     * @access public
     * @author Teye Heimans
     */
    public function __construct( &$oForm, $sName, &$oDb, $sTable, $mFields, $sExtraSQL = null )
    {
		// make sure that the fields are set in an array
		// asegurese que los campos estan establecidos en una matriz
		$aFields = !is_array($mFields) ? array( $mFields ) : $mFields;

		// generate the query to retrieve the records
		// generar la consulta para recuperar los registros
		$sQuery =
		  'SELECT '. implode(', ', $aFields).
		  ' FROM '. $oDb->quote( $sTable).' '.$sExtraSQL;

		// get the records and load the options/ Obtener los registros y cargar las opciones
		$aOptions = array();

		// execute the query/ ejecutar la consulta
		$sql = $oDb->query( $sQuery );

		// query succeeded?/ consulta exitosa?
		if( $sql )
		{
		    // fetch the results/ obtener los resultados
    		while( $row = $oDb->getRecord( $sql ) )
    		{

    			if( sizeof( $row ) == 1 )
    			{
    				$aOptions[] = array_shift( $row );
    			}
    			else
    			{
    		        $aOptions[array_shift( $row )] = array_shift( $row );
    		    }
    		}
		}
		// query failed/ consulta fallida
		else
		{
		    trigger_error(
		      "Error, could not retrieve records.<br '. FH_XHTML_CLOSE .'>\n".
		      "Error message: ". $oDb->getError()."<br '. FH_XHTML_CLOSE .'>\n".
		      "Query: ". $sQuery,
		      E_USER_WARNING
		    );
		}

		// call the constructor of the listfield with the new options
		// llama al constructor del campo de lista con las nuevas opciones
		parent::__construct( $oForm, $sName, $aOptions );

		// if two fields are given, use the first field as value
		// si se dan dos campos, use el primer campo como valor
		$this->useArrayKeyAsValue( sizeof( $aFields) == 2 );
    }
}