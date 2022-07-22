<?php

/**
 * class ResetButton
 *
 * Create a resetbutton on the given form/ Crea un boton de reset
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Buttons
 */
class ResetButton extends Button
{
    /**
     * ResetButton::ResetButton()
     *
     * constructor: Create a new reset button object/ crea un nuevo objeto de botón de reinicio
     *
     * @param object $form: the form where the button is located on/ formulario donde esta el boton localizado
     * @param string $name: The name of the button/ nombre del boton
     * @return ResetButton
     * @access public
     * @author Teye Heimans
     */
    public function __construct(&$oForm, $sName)
    {
        parent::__construct($oForm, $sName);

        $this->setCaption( $oForm->_text( 27 ) );
        $this->setClass('');
    }

    /**
     * ResetButton::getButton()
     *
     * Return the HTMl of the button/ Devuelve el HTML del boton
     *
     * @return string: the html of the button/ html del boton
     * @access public
     * @author Teye Heimans
     */

    public function getButton()
    {
        return sprintf(
          '<input type="reset" value="%s" name="%s" id="%2$s" class="%s" %s '. FH_XHTML_CLOSE .'>',
          $this->_sCaption,
          $this->_sName,
          $this->_iClass,
          (isset($this->_sExtra) ? ' '.$this->_sExtra:'').
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'"' : '')
        );
    }
    public function setClass( $class )
    {
        $this->_iClass ='btn '.$class;
    }
}

?>