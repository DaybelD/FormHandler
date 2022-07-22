<?php
/**
 * class dbRadioButton
 *
 * Create a Radio button field from records retrieved from the db
 * Crea un campo de RadioButton a partir de registros recuperados de la base de datos
 *
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 * @since 10-04-2008
 */

class dbRadioButton extends RadioButton
{
     /**
     * dbCheckBox::dbCheckBox()
     *
     * Public constructor: create a new db RadioButton object/ crear un nuevo objeto db RadioButton
     *
     * @param object &$oForm: the form where the datefield is located on/  formulario donde esta localizado el campo de fecha
     * @param string $sName: the name of the datefield/ nombre del campo de fecha 
     * @param object $oDb: object of the database handler/ objeto del manejador de base de datos
     * @param string $sTable: the table to get the fields from/ tabla para obtener los campos de
     * @param mixed $mFields: array of string with the names of the fields which data we should get/ matriz de cadenas con los nombres de los campos cuyos datos debemos obtener
     * @param string $sExtraSQL: extra SQL statements/ declaraciones SQL adicionales
     * @return dbRadioButton
     * @access public
     * @author Johan Wiegel
     */
	public function __construct( &$oForm, $sName, &$oDb, $sTable, $mFields, $sExtraSQL = null, $sMask = null )
	{
	    // call the constructor of the selectfield/ llama al constructor del campo de seleccion
		parent::__construct( $oForm, $sName, '' );

		// convert _aOptions to array as we cannot add elements to a string
		//convertir _aOptions a matriz ya que no podemos agregar elementos a una cadena  
		if (is_string($this->_aOptions))
        	{
            		$this->_aOptions = array();			
        	}		
		
		// make sure that the fields are set in an array
		// asegurese que los campos estan establecidos en una matriz
		$aFields = !is_array($mFields) ? array( $mFields ) : $mFields;
		$this -> useArrayKeyAsValue( sizeof( $aFields) == 2 );

		// generate the query to retrieve the records
		// genera la consulta para recuperar los resgistros
		$sQuery =
		  'SELECT '. implode(', ', $aFields).
		  ' FROM '. $oDb->quote( $sTable).' '.$sExtraSQL;

		// get the records and load the options/ obtener los registros y cargar las opciones
		//$this->_aOptions = is_array($aMergeArray) ? $aMergeArray : array();

		
		// execute the query/ ejecuta la consulta
		$sql = $oDb->query( $sQuery );

		// query succeeded/ consulta exitosa
		if( $sql )
		{
    		while( $row = $oDb->getRecord( $sql ) )
    		{
    			if( sizeof( $row ) == 1 )
    			{
    				$this->_aOptions[] = array_shift( $row );
    			}
    			else
    			{
    		       	$this->_aOptions[array_shift( $row )] = array_shift( $row );
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
 	}
}