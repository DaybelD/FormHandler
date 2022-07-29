<?php

/**
 * class PassField
 *
 * Create a PassField
 * Crea un campo de clave
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class PassField extends TextField
{
    private $_sPre;

    /**
     * PassField::PassField()
     *
     * Constructor: Create a new passfield object/ crea un nuevo objeto de campo de clave
     *
     * @param object $oForm: The form where the field is located on/ formulario donde se encuentra el campo
     * @param string $sName: The name of the form/ nombre del formulario
     * @return Passfield
     * @author Teye Heimans
     * @access public
     */
    public function __construct(&$oForm, $sName)
    {
        // call the constructor of the Field class
        // llama al constructor de la clase Campo
        parent::__construct($oForm, $sName);

        $this->_sPre = ('');
        $this->setClass('');
    }

    /**
     * PassField::getField()
     *
     * Return the HTML of the field/ Devuelve el HTML del campo
     *
     * @return string: the html
     * @author Teye Heimans
     * @access public
     */
    public function getField()
    {
        // view mode enabled ?/ Modo vista habilitado? 
        if( $this -> getViewMode() )
        {
            // get the view value../ obtenga el valor de la vista
            return '****';
        }

        return sprintf(
          '%s<input type="password" name="%s" id="%2$s" class="%s" %s'. FH_XHTML_CLOSE .'>%s',
          $this->_sPre,
          $this->_sName,
          $this->_iClass,
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'" ' : '').
          (isset($this->_sExtra) ? $this->_sExtra.' ' :''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
        );
    }

    /**
     * PassField::setPre()
     *
     * Set the message above the passfield
     * Establezca el mensaje sobre el campo de clave
     * 
     * @param string $sMsg: the message/ el mensaje
     * @return void
     * @author Teye Heimans
     * @access public
     */
    public function setPre( $sMsg)
    {
        $this->_sPre = $sMsg;
    }

    /**
     * PassField::checkPassword()
     *
     * Check the value of this field with another passfield
     * Comprueba el valor de este campo con otro campo de clave
     *
     * @param object $oObj
     * @return boolean: true if the values are correct, false if not/ true si el valor es corecto, false si no.
     * @author Teye Heimans
     * @access public
     */
    public function checkPassword( &$oObj )
    {
        // if the fields doesn't match
        // Si los campos no concuerdan
        if($this->getValue() != $oObj->getValue())
        {
            $this->_sError = $this->_oForm->_text( 15 );
            return false;
        }
        else
        {
            // when there is no value/ cuando no hay valor
            if($this->getValue() == '')
            {
                // it's an edit form.. keep the original
                // Es una forma de edicion.. mantenga el original
                if(isset($this->_oForm->edit) && $this->_oForm->edit)
                {
                    $this->_oForm->_dontSave[] = $this->_sName;
                    $this->_oForm->_dontSave[] = $oObj->_sName;

                    // make sure that no validator is overwriting the messages...
                    // asegúrese de que ningún validador esté sobrescribiendo los mensajes...
                    $this->setValidator( null );
                    $oObj->setValidator( null );
                }
                // insert form. PassField is required! error!
                // Inserte formulario. El campo clave es requerido! error!
                else
                {
                    $this->_sError = $this->_oForm->_text( 16 );
                    return false;
                }
            }
            else
            {
                // is the password not to short ?
                // la clave no es muy corta?
                if(strLen($this->getValue()) < FH_MIN_PASSWORD_LENGTH )
                {
                    $this->_sError = sprintf( $this->_oForm->_text( 17 ), FH_MIN_PASSWORD_LENGTH );
                    return false;
                }
                // is it an valif password ?
                // es una clave valida?
                else if( ! Validator::IsPassword($this->getValue()) )
                {
                    $this->_sError = $this->_oForm->_text( 18 );
                    return false;
                }
            }
        }
        // everything is OK!
        // Todo esta bien 
        return true;
    }
}
