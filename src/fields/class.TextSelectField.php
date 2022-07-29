<?php
/**
 * class TextSelectField
 *
 * Create a textselectfield
 * Crea un campo de texto de seleccion
 *
 * @author Johan Wiegel
 * @since 22-10-2008
 * @package FormHandler
 * @subpackage Fields
 */
class TextSelectField extends TextField
{
	protected $_iClass;         // string: class/ clase 
	private $_sOptions;
	
	/**
     * TextSelectField::TextSelectField()
     *
     * Constructor: Create a new textfield object
     * Constructor: Crea un nuevo objeto de campo de texto de seleccion 
     *
     * @param object &$oForm: The form where this field is located on/ formulario donde se encuentra el campo
     * @param string $sName: The name of the field/ nombre del campo
     * @return TextField
     * @author Teye Heimans
     * @access public
     */

	public function __construct( &$oForm, $sName, $aOptions )
	{
		parent::__construct($oForm, $sName);
		
		parent::setClass('');
		static $bSetJS = false;

   
		foreach( $aOptions as $key => $value )
		{	
			$this->_sOptions .= sprintf( FH_TEXTSELECT_OPTION_MASK, $value );
		}
		
		$this->setClass( '' );
		
	}


	public function getField()
	{
		// view mode enabled ?
		if( $this -> getViewMode() )
		{
			// get the view value..
			return $this -> _getViewValue();
		}
		
		return sprintf(
		FH_TEXTSELECT_MASK,
		$this->_sName,
		(isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
		(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :''),
		$this->_sOptions
		);


	}
}