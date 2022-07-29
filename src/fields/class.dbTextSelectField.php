<?php
/**
 * class dbTextSelectField
 *
 * Create a TextSelect field from records retrieved from the db
 * Crea un campo de seleccion de texto a partir de registros recuperados de la base de datos
 * 
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 * @since 22-10-2008
 */

class dbTextSelectField extends TextSelectField
{
	private $_aOptions;		

    /**
     * dbTextSelectField::dbTextSelectField()
     *
     * Public constructor: create a new dbTextSelectField object/ crea un nuevo objeto dbTextSelectField
     *
     * @param object &$oForm: the form where the TextSelectfield is located on/formulario donde se encuentra el campo
     * @param string $sName: the name of the datefield/ nombre del campo de fecha
     * @param object $oDb: object of the database handler/ objeto del manejador de base de datos
     * @param string $sTable: the table to get the fields from/ tabla para obtener los campos de
     * @param mixed $sField: array of string with the name of the field which data we should get/ matriz de cadenas con los nombres de los campos cuyos datos debemos obtener
     * @param string $sExtraSQL: extra SQL statements/ declaraciones SQL adicionales
     * @return dbTextSelectField
     * @access public
     * @since 22-10-2008
     * @author Johan Wiegel
     */
	public function __construct( &$oForm, $sName, &$oDb, $sTable, $sField, $sExtraSQL = null, $sMask = null )
	{
		// generate the query to retrieve the records
		// genera la consulta para recuperar los registros
		$sQuery =
		  'SELECT '.$sField.
		  ' FROM '. $oDb->quote( $sTable).' '.$sExtraSQL;

		$this->_aOptions = array();

		
		// execute the query/ ejecuta la consulta
		$sql = $oDb->query( $sQuery );

		// query succeeded/ consulta exitosa
		if( $sql )
		{
    		while( $row = $oDb->getRecord( $sql ) )
    		{
    			$this->_aOptions[] = $row[$sField];
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
			    // call the constructor of the selectfield/ llama al constructor del campo de seleccion
		parent::__construct( $oForm, $sName, $this->_aOptions );

 	}
}