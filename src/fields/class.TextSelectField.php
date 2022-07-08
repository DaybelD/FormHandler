<?php
/**
 * class TextSelectField
 *
 * Create a textselectfield
 *
 * @author Johan Wiegel
 * @since 22-10-2008
 * @package FormHandler
 * @subpackage Fields
 */
class TextSelectField extends TextField
{
	protected $_iClass;         // string: clases
	private $_sOptions;
	
	/**
     * TextSelectField::TextSelectField()
     *
     * Constructor: Create a new textfield object
     *
     * @param object &$oForm: The form where this field is located on
     * @param string $sName: The name of the field
     * @return TextField
     * @author Teye Heimans
     * @access public
     */

	public function __construct( &$oForm, $sName, $aOptions )
	{
		parent::__construct($oForm, $sName);
		
		$this->setClass('');
		static $bSetJS = false;

    	// needed javascript included yet ?
        if(!$bSetJS)
        {
            $bSetJS = true;

            // add the needed javascript
            $oForm->_setJS(
             "function FH_CLOSE_TEXTSELECT( id )"."\n".
             "{"."\n".
             "  setTimeout( 'document.getElementById(\"'+id+'\").style.display=\"none\"', 110 );"."\n".
             "}"."\n\n".
             "function FH_SET_TEXTSELECT( id, waarde )"."\n".
             "{"."\n".
             "  document.getElementById(id).value=waarde;"."\n".
             "  FH_CLOSE_TEXTSELECT( 'FHSpan_'+id );return false;"."\n".
             "}"."\n\n"             
            );
        }
   
		foreach( $aOptions as $key => $value )
		{	
			$this->_sOptions .= sprintf( FH_TEXTSELECT_OPTION_MASK, $sName, $value );
		}
		
		$this->setClass( '' );
		
	}

	//No muestra todas las opciones que se agregan en controller.php
	public function setClass( $class )
	{
		$this->_iClass ='form-control form-select '. $class;
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
		$this->_iClass,
		(isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
		(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :''),
		$this->_sOptions
		);


	}
}

?>