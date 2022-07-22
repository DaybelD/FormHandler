<?php
require_once(dirname(__FILE__)."/class.TextField.php");

/**
 * class ColorPicker
 *
 * Allows the user to pick a color/ permite al usuario eslegir un color
 *
 * @author Rick den Haan
 * @package FormHandler
 * @subpackage Fields
 * @since 02-07-2008
 */
class ColorPicker extends TextField
{
	public $sTitleAdd = "";

	/**
     * ColorPicker::ColorPicker()
     *
     * Constructor: Create a new ColorPicker object
     *
     * @param object &$oForm: The form where this field is located on/ formulario donde esta localizado
     * @param string $sName: The name of the field/ nombre del campo
     * @return ColorPicker
     * @access public
     * @author Rick den Haan
     */
	public function __construct( &$oForm, $sName )
	{
		parent::__construct($oForm, $sName);

		$this->setClass('');
		static $bSetJS = false;

		// needed javascript included yet ? el javascript necesario aun no esta incluido?
		if(!$bSetJS)
		{
			// include the needed javascript/ incluye el javascript necesario
			$bSetJS = true;
			$oForm->_setJS(FH_FHTML_DIR."js/jscolor/jscolor.js", true);
		}

	}
	public function setClass( $class )
	{
		$this->_iClass ='form-control form-control-color'. $class;
	}
	/**
     * ColorPicker::getField()
     *
     * Return the HTML of the field/ devuelve el HTML al campo
     *
     * @return string: the html of the field/ HTML del campo
     * @access public
     * @author Rick den Haan
     */
	public function getField()
	{
		// view mode enabled ?/ modo vista habilitado?
		if( $this -> getViewMode() )
		{
			// get the view value.. obtenga ek valor de vista
			return $this -> _getViewValue();
		}

		// check if the user set a class/ Compruebe si el usuario establece una clase
		if(isset($this->_sExtra) && preg_match("/class *= *('|\")(.*)$/i", $this->_sExtra))
		{
			// put the function into a onchange tag if set/ ponga la función en una etiqueta onchange si está configurada
			$this->_sExtra = preg_replace("/class *= *('|\")(.*)$/i", "class=\"color \\2", $this->_sExtra);
		}
		else
		{
			$this->_sExtra = "class=\"color\"".(isset($this->_sExtra) ? $this->_sExtra : '');
		}

		return sprintf(
		'<input type="color" name="%s" id="%1$s" value="%s" class="%s" %s'. FH_XHTML_CLOSE .'>%s',
		$this->_sName,
		(isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
		$this->_iClass,
		(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
		);
	}
}