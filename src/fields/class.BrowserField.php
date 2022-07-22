<?php
/**
 * class BrowserField
 *
 * Create a browserfield/ Crea un campo de navegacion
 *
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 */
class BrowserField extends Field
{
    private $_form;    		 // object: form

    /**
     * TextField::BrowserField()
     *
     * Constructor: Create a new textfield object/ crea un nuevo objeto para el campo de texto
     *
     * @param object &$oForm: The form where this field is located on/ formulario donde este campo esta localizado
     * @param string $sName: The name of the field/ nombre del campo
     * @param string $sPath: The path to browse/ ruta de navegacion
     * @return BrowserField
     * @author Johan Wiegel
     * @access public
     */
    public function __construct( &$oForm, $sName, $sPath )
    {
        // call the constructor of the Field class/ llama al constructuor del la clase campo
        parent::__construct($oForm, $sName);
        $this->_path = $sPath;
		$this->_form = $oForm;
        $this->setClass( '' );
		$bSetJS = true;
        $oForm->_setJS( 'function SetUrl( sUrl, sName ){document.getElementById( sName ).value=sUrl}', $isFile = false, $before = true);
    }

    /**
     * TextField::setClass()
     *
     * Set the class of the field/ establezca la clase para el campo
     *
     * @param integer $iClass: the new class/ nueva clase
     * @return void
     * @author Teye Heimans
     * @access public
     */
    public function setClass( $class )
    {
        $this->_iClass ='form-control '. $class;
    }
  
    /**
     * TextField::getField()
     *
     * Return the HTML of the field/ devuelve el HTML del campo
     *
     * @return string: the html
     * @access public
     * @author Johan Wiegel
     */
    public function getField()
    {
        // view mode enabled ?/ Modo vista habilitado ? 
        if( $this -> getViewMode() )
        {
            // get the view value../ obtenga el valor de vista 
            return $this -> _getViewValue();
        }
		
        //$this->_form->_setJS( '<script>function SetUrl( sUrl ){document.getElementById(\'bestand\').value=sUrl}</script>', $isFile = false, $before = true);
        
        $oButton = new Button( $this->_form, 'Seleccione' );
        $oButton->setCaption( 'Seleccione' );
        $oButton->setExtra( "onclick=\"window.open( '".FH_FHTML_DIR."filemanager/browser/default/browser.html?Type=File&naam=".$this->_sName."&Connector=../../connectors/php/connector.php?ServerPath=".$this->_path."','','modal=yes,width=650,height=400');\"" );
		$sButton = $oButton->getButton();        
        
        return sprintf(
          '<input type="text" name="%s" id="%1$s" value="%s" class="%s" %s'. FH_XHTML_CLOSE .'>%s %s ',
          $this->_sName,
          (isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING):''),
          $this->_iClass,
          (isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
          (isset($this->_sExtra) ? ' '.$this->_sExtra.' ' :''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :''),
          $sButton
        );
    }
}