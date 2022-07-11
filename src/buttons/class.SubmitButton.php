<?php

/**
 * class SubmitButton
 *
 * Create a submitbutton on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Buttons
 */
class SubmitButton extends Button
{
    private $_bDisableOnSubmit;

    /**
     * SubmitButton::SubmitButton()
     *
     * Constructor: The constructor to create a new Submitbutton object.
     *
     * @param object $oForm: the form where this field is located on
     * @param string $sName: the name of the button
     * @return SubmitButton
     * @access public
     * @author Teye Heimans
     */
    public function __construct(&$oForm, $sName)
    {
        parent::__construct( $oForm, $sName );

        $this->disableOnSubmit( FH_DEFAULT_DISABLE_SUBMIT_BTN );

        // set the default submit caption
        $this->setCaption( $oForm->_text( 26 ) );

        $this->setClass('');
    }

    /**
     * SubmitButton::disableOnSubmit()
     *
     * Set if the submitbutton has to be disabled after pressing it
     * (avoid dubble post!)
     *
     * @param boolean status
     * @return void
     * @access public
     * @author Teye Heimans
     */

    public function setClass( $class )
    {
        $this->_iClass ='btn btn-warning '. $class;
    }

    public function disableOnSubmit( $bStatus )
    {
        $this->_bDisableOnSubmit = (bool) $bStatus;
    }

    /**
     * SubmitButton::getButton()
     *
     * Returns the button
     *
     * @return string: the HTML of the button
     * @access public
     * @author Teye Heimans
     */
    public function getButton()
    {
        // set the javascript disable dubble submit option if wanted
        if( $this->_bDisableOnSubmit )
        {
            // check if the user set a onclick event
            if(isset($this->_sExtra) && preg_match("/onclick *= *('|\")(.*)$/i", $this->_sExtra))
            {
                // put the function into a onchange tag if set
                $this->_sExtra = preg_replace("/onclick *= *('|\")(.*)$/i", "onclick=\\1this.form.submit();this.disabled=true;\\2", $this->_sExtra);
            }
            // no onclick event defined.. just add the js code
            else
            {
           		$this->_sExtra = "onclick=\" if (this.form.querySelector(':invalid') == null) { this.form.submit();this.disabled=true;}\" ".(isset($this->_sExtra) ? $this->_sExtra : '');
            }
        }

        // return the button
        return sprintf(
          '<input type="submit" value="%s" name="%s" id="%2$s" class="%s" %s '. FH_XHTML_CLOSE .'>',
          $this->_sCaption,
          $this->_sName,
          $this->_iClass,
          (isset($this->_sExtra) ? ' '.$this->_sExtra:'').
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'"' : '')
        );
    }
}

?>