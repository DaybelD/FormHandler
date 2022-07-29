<?php
/**
 * class Field
 *
 * Class to create a field./ Clase para crear un campo
 * This class contains code which is used by all the other fields
 * Esta clase contiene el codigo que sera usado por todos los demas campos
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class Field
{
	protected $_oForm;         // object: the form where the field is located in/formulario donde se encuentra el campo
	protected $_sName;         // string: name of the field/ nombre del campo
	protected $_sValidator;    // string: callback function to validate the value of the field/ función de devolución de llamada para validar el valor del campo
	protected $_mValue;        // mixed: the value of the field/ valor del campo 
	public $_sError;        // string: if the field is not valid, this var contains the error message/ si el campo no es valido, esta var contiene el mensaje de error
	protected $_sExtra;        // string: extra data which should be added into the HTML tag (like CSS or JS)/ Datos extras que deben agregarse a la etiqueta HTML (como CSS o JS)
	protected $_iTabIndex;     // int: tabindex or null when no tabindex is set/ enfoque o nulo cuando el enfoque no se establece
	protected $_sExtraAfter;   // string: extra data which should be added AFTER the HTML tag/ datos adicionales que deben agregarse DESPUÉS de la etiqueta HTML
	public $_viewMode;      // boolean: should we only display the value instead of the field?/ Debemos mostrar solo el valor en lugar del campo?
	protected $_isValid;      // boolean: field is valid/ campo valido


	/**
     * Field::Field()
     *
     * Public abstract constructor: Create a new field/ crea un nuevo campo
     *
     * @param object $oForm: The form where the field is located on/ Formulario donde se encuentra el campo
     * @param string $sName: The name of the field/ Nombre del campo
     * @return Field
     * @access public
     * @author Teye Heimans
     */
	public function __construct( &$oForm, $sName )
	{
		// save the form and name of the field/ guarda el formulario y el nombre del campo
		$this->_oForm = &$oForm;
		$this->_sName = $sName;
		$this->_isValid = null;

		// check if there are spaces in the fieldname
		// comprueba si hay espacios en el nombre del campo
		if(strpos($sName,' ') !== false)
		{
			trigger_error('Warning: There are spaces in the field name "'.$sName.'"!', E_USER_WARNING );
		}

		// get the value of the field/ obtener el valor del campo
		if( $oForm->isPosted() )
		{
			// make sure that the $_POST array is global
			// asegurese que la matriz $_POST es global
			if(!_global) global $_POST;

			// get the value if it exists in the $_POST array
			// obtenga el valor si este existe en la matriz $_POST
			if( isset( $_POST[$sName] ) )
			{
				// is the posted value a string/ el valor enviado es una cadena
				if( is_string( $_POST[$sName] ) )
				{
					// save the value.../ guarda el valor...
					$this->setValue(
						$_POST[$sName]
					);
				}
				// the posted value is an array/ el valor enviado esta es una matriz
				else if( is_array( $_POST[$sName] ) )
				{
					// escape the incoming data if needed and pass it to the field
					// escapar de los datos entrantes si es necesario y pasarlos al campo
					$item = array();
					foreach ( $_POST[$sName] as $key => $value )
					{
						$item[$key] = $value;
					}
					$this->setValue($item);
				}
			}

			/*
			* When the form is posted but this field is not found in the $_POST array,
			* keep the data from the db
			* Cuando el formulario se envía pero este campo no se encuentra en la matriz $_POST, 
			* guarda los datos de la db
			* (This happens when the DISABLED attribute in the field's tag is used)
			* (Esto ocurre cuando se utiliza el atributo DISABLED en la etiqueta del campo)
			* Problem is that datefield's are never in the post array (because
			* they have 3 fields: {name}_day, etc.). Because of this, the old value always
			* will be kept...
			* El problema es que los campos de fecha nunca están en el array de correos
			* (porque tienen 3 campos: {nombre}_día, etc.). 
			* Debido a esto, el valor antiguo siempre se mantendrá...
			* see (dutch topics!):
			* ver (¡tema holandés!):
			* http://www.formhandler.net/FH3/index.php?pg=12&id=1333#1333
			* http://www.formhandler.net/FH3/index.php?pg=12&id=1296#1296
			*
			* TODO!!
			*/
			/*
			elseif ( $oForm->edit )
			{
			if( isset( $oForm->_dbData[$sName] ) )
			{
			$this->setValue( $oForm->_dbData[$sName] );
			}
			}*/

		}
		// The form is not posted, load database value if exists
		// El formulario no esta enviado, cargar el valor de la base de datos si existe
		else if( isset( $oForm->edit) && $oForm -> edit )
		{
			// does a db value exists for this field ?
			// existe un valor en la base de datos para este campo?
			if( isset( $oForm->_dbData[$sName] ) )
			{
				// load the value into the field
				// carga el valor dentro del campo
				$this->setValue( $oForm->_dbData[$sName] );
			}
		}

		// check if the user got another value for this field.
		// comprueba si el usuario tiene otro valor para este campo.
		if( isset($oForm ->_buffer[ $sName ] ) )
		{
			list( $bOverwrite, $sValue ) = $oForm->_buffer[ $sName ];

			// if the field does not exists in the database
			// si el campo no existe en la base de datos
			if($bOverwrite || (!isset($oForm->_dbData[$sName]) && !$oForm->isPosted() ))
			{
				$this->setValue( $sValue );
			}

			// remove the value from the buffer..
			// elimina el valor del buffer..
			unset( $oForm->_buffer[ $sName ] );
		}
	}

	/**
     * Field::isValid()
     *
     * Check if the value of the field is valid. If not,
     * set the error message and return false
     * Comprueba si el valor del campo es valido. Si no, 
     * establezca el mensaje de error y devuelva false
     *
     * @return boolean: If the value of the field is valid/ Si el valor del campo es valido
     * @author Teye Heimans
     * @access public
     * @since 11-04-2008 ADDED POSSIBILITY TO USE MULTIPLE VALIDATORS 
     * @author Remco van Arkelen & Johan Wiegel
     */
	public function isValid()
	{
		// done this function before... return the prefious value
		// ya se ha hecho esta función... devuelve el valor prefijado
		if( isset( $this->_isValid ) )
		{
			return $this->_isValid;
		}

		// field in view mode?
		// el campo en modo vista?
		if( $this -> getViewMode() )
		{
			$this->_isValid = true;
			return $this->_isValid;
		}

		// is a validator set?
		// es un conjunto de validadores?
		if(isset($this->_sValidator) && $this->_sValidator != null)
		{
			// if it's an array, it's a method/ si esta en una matriz, es un método
			if (!is_array($this->_sValidator))
			{
				// Is there an | , there are more validators
				// Hay un |, hay mas validadores
				if( strpos( $this->_sValidator, '|' ) > 0 )
				{
					$aValidator = explode( '|', $this->_sValidator );
					foreach( $aValidator AS $val )
					{
						// is the validator a user-specified function?
						// el validador es una funcion especificada por el usuario?
						if( function_exists($this->_sValidator) )
						{
							$value = $this->getValue();
							$v = is_string($value) ? trim( $value) : $value;
							$error = call_user_func( $this->_sValidator, $v, $this->_oForm );
						}
						else
						{
							$v = new Validator();
							// is this a defined function? translate it to the correct function
							// esta es una funcion definida? Traducir a la funcion correcta
							if( defined( $val ) )
							{
								$aVal = get_defined_constants();
								$val = $aVal[ $val ];
							}

							if( is_object( $v ) && method_exists($v, $val ) )
							{
								// call the build in validator function
								// llamar a la función del validador integrado
								$value = $this->getValue();
								if( is_string( $value) )
								$value = trim( $value );
								$error = $v->{$val}( $value );
							}
							else
							{
								trigger_error('Unknown validator: "'.$val.'" used in field "'.$this->_sName.'"');
								$error = false;
							}
							unset( $v );
						}
						// Stop processing validators if 1 fails.
						// Deja de procesar los validadores si 1 falla.
						if( true !== $error )
						{
							break;
						}
					}
				}
				else
				{
					// is the validator a user-spicified function?
					// el validador es una funcion especificada por el usuario?
					if( function_exists($this->_sValidator) )
					{
							$value = $this->getValue();
							$v = is_string($value) ? trim( $value) : $value;
							$error = call_user_func( $this->_sValidator, $v, $this->_oForm );
					}
					else
					{
						$v = new Validator();
						if( is_object( $v ) && method_exists($v, $this->_sValidator) )
						{
							// call the build in  validator function
							// llamar a la función del validador integrad
							$value = $this->getValue();
							if( is_string( $value) )
							$value = trim( $value );
							$error = $v->{$this->_sValidator}( $value );
						}
						else
						{
							trigger_error('Unknown validator: "'.$this->_sValidator.'" used in field "'.$this->_sName.'"');
							$error = false;
						}
						unset( $v );
					}
				}
			}
			// method given/ metodo dado
			else
			{
				if( method_exists( $this->_sValidator[0], $this->_sValidator[1] ) )
				{
					$value = $this->getValue();
					$value = (is_array ($value)) ? $value : trim ($value);
					$error = call_user_func(array(&$this->_sValidator[0], $this->_sValidator[1]), $value );
				}
				else
				{
					trigger_error(
					"Error, the validator method '".$this->_sValidator[1]."' does not exists ".
					"in object '".get_class($this->_sValidator[0])."'!",
					E_USER_ERROR
					);
					$error = false;
				}
			}

			// set the error message
			// establezca el mensaje de error
			$this->_sError =
			is_string($error) ? $error :
			(!$error ? $this->_oForm->_text( 14 ) :
			(isset($this->_sError) ? $this->_sError : ''));
		}

		$this->_isValid = empty( $this->_sError );
		return $this->_isValid;
	}
	/**
	 * Field::getValidator()
	 * 
	 * Returns the validator from this field
	 * Devuelve el validador de este campo
	 * Added in order to use ajax validation
	 * Agregado para usar la validación ajax
	 * 
	 * @return string
	 * @access public
	 * @author Johan Wiegel
	 * @since 04-12-2008
	 */
	public function getValidator( )
	{
		return $this->_sValidator;
	}

	/**
     * Field::setValidator()
     *
     * Set the validator which is used to validate the value of the field
     * This can also be an array.
     * Establece el validador que se utiliza para validar el valor del campo
     * Esto también puede ser una matriz.
     * If you want to use a method to validate the value use it like this:
     * array(&$obj, 'NameOfTheMethod')
     * Si quieres usar un método para validar el valor úsalo así
     * array(&$obj, 'NombreDelMétodo')
     *
     * @param string $sValidator: the name of the validator/ nombre del validador
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setValidator( $sValidator )
	{
		$this->_sValidator = $sValidator;

		/*
		if( $this->_oForm->_ajaxValidator === true )
		{
		echo 'JAJA';
		require_once( FH_INCLUDE_DIR . 'includes/class.AjaxValidator.php' );
		$oAjaxValidator = new AjaxValidator( $this );
		$oAjaxValidator->AjaxValidator( $this );
		}
		*/

	}

	/**
     * Field::setTabIndex()
     *
     * Set the tabindex of the field
     * establezca el indice de tabulacion del campo
     *
     * @param int $iIndex
     * @return void
     * @author Teye Heimans
     * @access public
     */
	public function setTabIndex( $iIndex )
	{
		$this->_iTabIndex = $iIndex;
	}

	/**
     * Field::setExtraAfter()
     *
     * Set some extra HTML, JS or something like that (to use after the html tag)
     * Establezca algunos HTML, JS, o algun otro adicional (usado luego de la etiqueta html)
     *
     * @param string $sExtra: the extra html to insert into the tag
     * @return void
     * @author Teye Heimans
     * @access public
     */
	public function setExtraAfter( $sExtraAfter )
	{
		$this->_sExtraAfter = $sExtraAfter;
	}

	/**
     * Field::setError()
     *
     * Set a custom error
     * establezca un error personalizado
     *
     * @param string $sError: the error to set into the tag/ el error establecido en la etiqueta
     * @return void
     * @access public
     * @author Filippo Toso - filippotoso@libero.it
     */
	public function setError( $sError )
	{
		$this->_sError = $sError;
	}

	/**
     * Field::getValue()
     *
     * Return the value of the field
     * Devuelve el alor del campo
     *
     * @return mixed: the value of the field/ valor del campo
     * @access public
     * @author Teye Heimans
     */
	public function getValue()
	{
		return isset( $this->_mValue ) ? $this->_mValue : '';
	}

	/**
     * Field::getError()
     *
     * Return the error of the field (if the field-value is not valid)
     * Devuelve el error del campo (si el valor del campo no es valido)
     *
     * @return string: the error message/ mensaje de error
     * @access public
     * @author Teye Heimans
     */
	public function getError()
	{
		return isset( $this->_sError ) && strlen($this->_sError) > 0 ? sprintf( FH_ERROR_MASK, $this->_sName ,$this->_sError): '';
	}

	/**
     * Field::setValue()
     *
     * Set the value of the field/ establezca el valor del campo
     *
     * @param mixed $mValue: The new value for the field/ nuevo valor del campo
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setValue( $mValue )
	{
		$this->_mValue = $mValue;
	}

	/**
	 * Field::getExtra()
	 * 
	 * Get extra of the Field/ Obtenga el adicional del campo
	 *
	 * @return string|null
	 */
	public function getExtra() : ?string
	{
		return $this->_sExtra;
	}

	/**
     * Field::setExtra()
     *
     * Set some extra CSS, JS or something like that (to use in the html tag)
     * Establezca algunos CCS, JS o algo similar adicionales (oara usar en la etiqueta html)
     *
     * @param string $sExtra: the extra html to insert into the tag/ el html extra para insertar en la etiqueta
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setExtra( $sExtra )
	{
		$this->_sExtra = $sExtra;
	}

	/**
     * Field::getField()
     *
     * Return the HTML of the field./ Devuelve el HTML del campo
     * This function HAS TO BE OVERWRITTEN by the child class!
     * Esta funcion DEBE SER SOBREESCRITA por la clase secuendaria
     *
     * @return string: the html of the field/ el html del campo
     * @access public
     * @author Teye Heimans
     */
	public function getField()
	{
		trigger_error('Error, getField has not been overwritten!', E_USER_WARNING);
		return '';
	}

	/**
     * Field::getViewMode()
     *
     * Return if this field is set to view mode
     * Devuelve si este campo establece el modo vista
     *
     * @return bool
     * @access public
     * @author Teye Heimans
     */
	public function getViewMode()
	{
		return (isset( $this -> _viewMode) && $this -> _viewMode) ||
		$this -> _oForm -> isViewMode();
	}

	/**
     * Field::setViewMode()
     *
     * Enable or disable viewMode for this field
     * Habilitado o deshabilitado el modo vista para este campo
     *
     * @param boolean $mode
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setViewMode( $mode = true )
	{
		$this -> _viewMode = (bool) $mode;
	}

	/**
     * Field::setInvalid()
     *
     * Invalids this field/ el campo no es valido
     *
     * @return void
     * @access public
     */
	public function setInvalid()
	{
		$this ->_isValid = false;
	}

	/**
	 * Field::_getViewValue()
	 *
	 * Return the value of the field/ devuelve el valor del campo
	 *
	 * @return mixed: the value of the field/ valor del campo
	 * @access protected
	 * @author Teye Heimans
	 */
	protected function _getViewValue()
	{
		// edit form and posted ? then first get the database value!
		// editar formulario y publicarlo? primero obtenga el valor de la base de datos!
		if( isset( $this -> _oForm -> edit ) && $this -> _oForm -> edit && $this -> _oForm -> isPosted() )
		{
			$this -> setValue( $this -> _oForm -> _dbData[ $this -> _sName ] );
		}

		// get the value for the field/ obtenga el valor del campo
		$val = $this->getValue();

		// implode arrays/ matrices de implosion
		$save = is_array( $val) ? implode( ',', $val) : $val;

		// are there mulitple options?/ hay multiples opciones?
		if( isset( $this->_aOptions ) )
		{
			// is the key returned while we should show the "label" to the user ?
			// La clave es devuelta mientras debemos mostrar la "etiqueta" al usuario?
			if( isset($this->_bUseArrayKeyAsValue) && $this->_bUseArrayKeyAsValue )
			{
				// is the value an array?/ el valor es una matriz?
				if( is_array( $val) )
				{
					// save the labels instead of the index keys as view value
					// guarde las etiquetas en lugar de las claves de indice como valor de vista
					foreach ( $val as $key => $value )
					{
						$val[$key] = $this->_aOptions[$value];
					}
				}
				// is there a "label" for this value? 
				// Hay una "etiqueta" para este valor?
				else if( array_key_exists( $val, $this->_aOptions ) )
				{
					// get the "label" instead of the index
					// obtener la "etiqueta" en lugar del indice
					$val = $this->_aOptions[$val];
				}
			}
		}

		// when the value is an array/ cuando el valor esta en una matriz
		if( is_array($val) )
		{
			// is there only one item?/ solo hay un elemento?
			if( sizeof($val) == 1 )
			{
				$result = $val[0];
			}
			else
			{
				// make a list of the selected items/ hacer una lista de los elementos seleccionados
				$result = "\t<ul>\n";
				foreach($val as $item )
				{
					$result .= "\t  <li>".$item."</li>\n";
				}
				$result .= "\t</ul>\n";
			}
		}
		else
		{
			$result = $val;
		}

		// return the value/ devuelve el valor
		return $result;
	}
}