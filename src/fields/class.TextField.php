<?php

/**
 * class TextField
 *
 * Create a textfield
 * Crea un campo de texto
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class TextField extends Field
{
	protected $_iClass;      // string: class associated with the field/ clase asociada al campo
	protected $_iMaxlength;  // int: the maxlength of the field/ longitud maxima del campo

	/**
     * TextField::TextField()
     *
     * Constructor: Create a new textfield object/ crea un nuevo objeto de campo de texto
     *
     * @param object &$oForm: The form where this field is located on/ formulario donde se encuentra localizado
     * @param string $sName: The name of the field/ nombre del campo
     * @return TextField
     * @author Teye Heimans
     * @access public
     */
	public function __construct( &$oForm, $sName )
	{
		// call the constructor of the Field class
		parent::__construct($oForm, $sName);

		$this->setClass( '' );
	}

	/**
     * TextField::setClass()
     *
     * Set the new class of the field
     * Establezca la nueva clase del campo
     * 
     * @param integer $iClass: the new class
     * @return void
     * @author Teye Heimans
     * @access public
     */
	public function setClass( $class )
	{
		$this->_iClass = trim('form-control '. $class);
	}

	/**
	 * TextField::checkMaxLength()
	 *
	 * Check the maxlength of the field
	 * Comprueba la maxima longitud del campo
	 *
	 * @param integer $iLength: the maxlength
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 * @since 17-04-2009
	 */

	public function checkMaxLength( $iLength )
	{
		if( strlen( $this->getValue() ) > $iLength )
		{
			$this->_sError = $this->_oForm->_text( 14 );
			return false;
		}
	}

	/**
	 * TextField::checkMinLength()
	 *
	 * Check the minlength of the field
	 * Comprueba la minima longitud del campo
	 *
	 * @param integer $iLength: the maxlength
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 * @since 17-04-2009
	 */

	public function checkMinLength( $iLength )
	{
		if( strlen( $this->getValue() ) < $iLength )
		{
			$this->_sError = $this->_oForm->_text( 14 );
			return false;
		}
	}

	/**
     * TextField::setMaxlength()
     *
     * Set the new maxlength of the field
     * Establezca la nueva longitud maxima del campo
     *
     * @param integer $iMaxlength: the new maxlength
     * @return void
     * @access public
     * @author Teye Heimans
	public function setMaxlength( $iMaxlength )
	{
		$this->_iMaxlength = $iMaxlength;
	}
    */

	/**
     * TextField::getField()
     *
     * Return the HTML of the field/ Devuelve el HTML del campo
     *
     * @return string: the html
     * @access public
     * @author Teye Heimans
     */
	public function getField()
	{
		// view mode enabled ?/ modo vista habilitado?
		if( $this -> getViewMode() )
		{
			// get the view value../ obtenga el valor de la vista
			return $this -> _getViewValue();
		}

		return sprintf(
		'<input type="text" name="%s" id="%1$s" value="%s" class="%s" %s'. FH_XHTML_CLOSE .'>%s',

		$this->_sName,
		(isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
		$this->_iClass,
		(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
		);
	}
}