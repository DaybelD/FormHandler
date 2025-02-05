<?php
/**
 * class CheckBox
 *
 * Create a checkbox on the given form object/ crea una caja de seleccion
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class CheckBox extends Field
{
	private $_aOptions;              // array: contains all the options!/ contiene todas las opciones
	// $this->_mValue contains the values which are selected!
	private $_iClass;				 // checkbox class/ clase del checkbox
	private $_bUseArrayKeyAsValue;   // boolean: if the keys of the array should be used as values/ si las claves de la matriz seran usadas como valores
	private $_sMask;                 // string: what kind of "glue" should be used to merge the checkboxes/ qué tipo de "pegamento" se debe usar para fusionar los checkboxes
	private $_oLoader;               // object: The maskLoader/ cargador de mascara

	/**
     * CheckBox::CheckBox()
     *
     * Constructor: Create a new checkbox object/ crea un nuevo objeto para checkbox
     *
     * @param object $oForm: The form where this field is located on/ formulario donde este campo esta localizado
     * @param string $sName: The name of the field/ nombre del campo
     * @param mixed: array|string $aOptions - The options for the field/ opciones para el campo
     * @return CheckBox
     * @access public
     * @author Teye Heimans
     */
	public function __construct( &$oForm, $sName, $aOptions )
	{
		$this->_mValue = '';
		$sName = str_replace('[]','', $sName);

		$this->_aOptions = $aOptions;

		// call the constructor of the Field class/ llama al constructor de la clase campo
		parent::__construct( $oForm, $sName );

		$this->setClass('');
		$this->setMask 			 ( FH_DEFAULT_GLUE_MASK );
		$this->useArrayKeyAsValue( FH_DEFAULT_USEARRAYKEY );
	}

	/**
     * CheckBox::setValue()
     *
     * Set the value of the field/ establezca el valor del campo
     *
     * @param string / array $mValue: the value to set/ valor a establecer
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setValue( $aValue )
	{
		// make an array from the value/ hacer una matriz a partir del valor 
		if( !is_array($aValue) && is_array($this->_aOptions) )
		{
			$aValue = explode(',', $aValue);
			foreach($aValue as $iKey => $sValue)
			{
				$sValue = trim($sValue);

				// dont save an empty value when it does not exists in the options array!/ no guardar valores vacios cuando no existe en la matriz de opciones 
				if( !empty($sValue)  ||
				((is_array($this->_aOptions) &&
				( in_array( $sValue, $this->_aOptions ) ||
				array_key_exists( $sValue, $this->_aOptions )
				)
				) ||
				$sValue == $this->_aOptions ))
				{
					$aValue[$iKey] = $sValue;
				}
				else
				{
					unset( $aValue[$iKey] );
				}
			}
		}

		$this->_mValue = $aValue;
	}

	public function setClass( $class )
	{
		$this->_iClass = trim('form-check '. $class);
	}


	/**
     * CheckBox::useArrayKeyAsValue()
     *
     * Set if the array keys of the options has to be used as values for the field/ Establecer si las claves de matriz de las opciones deben usarse como valores para el campo
     *
     * @param boolean $bMode
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function useArrayKeyAsValue( $bMode )
	{
		$this->_bUseArrayKeyAsValue = $bMode;
	}

	/**
     * CheckBox::setMask()
     *
     * Set the glue used to glue multiple checkboxes. This can be a mask
     * where %field% is replaced with a checkbox!
     *Establezca el pegamento utilizado para pegar varios checkboxes. esto puede ser una mascara
     *¡donde %field% se reemplaza con un checkbox!
     * 
     * @param string $sMask
     * @return void
     * @author Teye Heimans
     * @access Public
     */
	public function setMask( $sMask )
	{
		// when there is no %field% used, put it in front of the mask/glue
		// cuando no se use %field%, colóquelo delante de la máscara/pegamento
		if( strpos( $sMask, '%field%' ) === false )
		{
			$sMask = '%field%' . $sMask;
		}

		$this->_sMask = $sMask;
	}

	/**
     * CheckBox::getField()
     *
     * Return the HTML of the field/ Devuelve el HTML del campo
     *
     * @return string: the html of the field
     * @access Public
     * @author Teye Heimans
     */
	public function getField()
	{
		// view mode enabled ? / el modo vista esta habilitado? 
		if( $this -> getViewMode() )
		{
			// get the view value.. obtener el valor de la vista
			return $this -> _getViewValue();
		}

		// multiple checkboxes ? multiples checkboxes
		if( is_array( $this->_aOptions ) && count( $this->_aOptions )>0 )
		{
			$sResult = '';

			// get the checkboxes/ obtenga los checkboxes
			foreach( $this->_aOptions as $iKey => $sValue )
			{
				// use the array key as value?
				if(!$this->_bUseArrayKeyAsValue)
				{
					$iKey = $sValue;
				}

				// get the checbox/ obtenga el checkbox
				$sResult .= $this->_getCheckBox( $iKey, $sValue, true );
			}

			// get a possible half filled mask/ obtener una posible máscara medio llena
			$sResult .= $this -> _oLoader -> fill();

		}
		elseif( is_array( $this->_aOptions ) && count( $this->_aOptions ) === 0 )
		{
			$sResult = '';
		}

		// just 1 checkbox.../ solo 1 checkbox
		else
		{
			$sResult = $this->_getCheckBox( $this->_aOptions, '' );
		}

		return $sResult.
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
	}

	/**
     * CheckBox::_getCheckBox()
     *
     * Return an option of the checkbox with the given value/ Devuelve una opcion del checkbox con el valor dado
     *
     * @param string $sValue: the value for the checkbox/ valor del checkbox
     * @param string $sTitle: the title for the checkbox/ titulo para el checkbox
     * @param bool $bUseMask: do we have to use the mask after the field?/ Tenemos que usar la máscara después del campo?
     * @return string: the HTML for the checkbox/ HTML para los checkboxes
     * @access private
     * @author Teye Heimans
     */
	private function _getCheckBox( $sValue, $sTitle, $bUseMask = false )
	{
		static $iCounter = 1;

		// create a MaskLoader object when it does not exists yet/ crea un objeto MaskLoader cuando aún no existe
		if( !isset( $this->_oLoader ) || is_null( $this->_oLoader ) )
		{
			$this -> _oLoader = new MaskLoader();
			$this -> _oLoader -> setMask( $this->_sMask );
			$this -> _oLoader -> setSearch( '/%field%/' );
		}

		// remove unwanted spaces/ remueve espacios no deseados
		$sValue = trim( $sValue );
		$sTitle = trim( $sTitle );

		// get the field HTML/ obtenga el campo HTML
		if( $sTitle == '' ) 
		{
			$sField = sprintf(
			'<input type="checkbox" name="%s" class="%s" id="%s_%d" value="%s" %s'. FH_XHTML_CLOSE .'>',
			$this->_sName.(is_array($this->_aOptions)?'[]':''),
			$this->_sName,
			$this->_iClass,
			$iCounter++,
			htmlspecialchars($sValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING),
			(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
			((isset($this->_mValue) && ((is_array($this->_mValue) && in_array($sValue, $this->_mValue)) || $sValue == $this->_mValue) ) ?
			'checked="checked" ':'').
			(isset($this->_sExtra) ? $this->_sExtra.' ':''),
			$sTitle
			);
		}
		else
		{
			$sField = sprintf(
			'<input type="checkbox" name="%s" id="%s_%d" class="form-check-input" value="%s" %s'. FH_XHTML_CLOSE .'><label for="%2$s_%3$d" class="form-check-label">%s</label>',
			$this->_sName.(is_array($this->_aOptions)?'[]':''),
			$this->_sName,
			$iCounter++,
			htmlspecialchars($sValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING),
			(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
			((isset($this->_mValue) && ((is_array($this->_mValue) && in_array($sValue, $this->_mValue)) || $sValue == $this->_mValue) ) ?
			'checked="checked" ':'').
			(isset($this->_sExtra) ? $this->_sExtra.' ':''),
			$sTitle
			);
		}
		// do we have to use the mask ?/ Tenemos que usar la mascara?
		if( $bUseMask )
		{
			$sField = $this -> _oLoader -> fill( $sField );
		}

		return $sField;
	}
}