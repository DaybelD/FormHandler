<?php

/**
 * class Button
 *
 * Create a button on the given form
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Buttons
 */
class Button
{
    private $_oForm;
    protected $_sName;
  //  protected $_iClass;
    protected $_sExtra;
    protected $_sCaption;
    protected $_iTabIndex;

    /**
     * Button::Button()
     *
     * Constructor: create a new Button object
     *
     * @param object $form: the form where the button is located on
     * @param string $name: the name of the button
     * @return Button
     * @access public
     * @author Teye Heimans
     */
    public function __construct(&$oForm, $sName)
    {
        // set the button name and caption
        $this->_oForm    = $oForm;
        $this->_sName    = $sName;
        //$this->setClass(''); 
    }

    /**
     * Field::setTabIndex()
     *
     * set the tabindex of the field
     *
     * @param int $iIndex
     * @return void
     * @access public
     * @author Teye Heimans
     */

    public function setTabIndex( $iIndex )
    {
        $this->_iTabIndex = $iIndex;
    }

   // public function setClass( $class )
   // {
   //     $this->_iClass ='btn '.$class;
   // }


    /**
     * Button::setCaption()
     *
     * Set the caption of the button
     *
     * @param string $caption: The caption of the button
     * @return void
     * @access public
     * @author Teye Heimans
     */

    public function setCaption($sCaption)
    {
        $this->_sCaption = $sCaption;
    }

    /**
     * Button::getButton()
     *
     * Return the HTML of the button
     *
     * @return string: the button
     * @access public
     * @author Teye Heimans
     */
    public function getButton()
    {
        return sprintf(
          '<input type="button" id="%1$s" value="%s" name="%s" %s '. FH_XHTML_CLOSE .'>',
          $this->_sName,
          $this->_sCaption,
          //$this->_iClass,
          (isset($this->_sExtra) ? ' '.$this->_sExtra:'').
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'"' : '')
        );
    }

    /**
     * Button::setExtra()
     *
     * Set extra tag information, like CSS or Javascript
     *
     * @param string $extra: the CSS, JS or other extra tag info
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setExtra($sExtra)
    {
        $this->_sExtra = $sExtra;
    }
}