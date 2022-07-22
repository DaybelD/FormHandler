<?php

/**
 * class ImageButton
 *
 * Create a image button on the given form/ Crea una imagen como boton en el formulario
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Buttons
 */
class ImageButton extends Button
{
	private $_sImage;

    /**
     * ImageButton::ImageButton()
     *
     * Constructor: Create a new ImageButton object
     *
     * @param object $form: the form where the image button is located on/ formulario donde esta localizado el boton
     * @param string $name: the name of the button/ nombre del boton
     * @param string $image: the image we have to use as button/ imagen que usaremos como boton
     * @return ImageButton
     * @access public
     * @author Teye Heimans
     */
    public function __construct( &$oForm, $sName, $sImage)
    {
        parent::__construct($oForm, $sName);

        // set the image we use/ establezca la imagen que usaremos
        $this->_sImage = $sImage;
    }

    /**
     * ImageButton::getButton()
     *
     * Return the HTML of the button/ Devuelve el HTMl del boton 
     *
     * @return string: the HTML of the button/  HTML del boton
     * @access public
     * @author Teye Heimans
     */
    public function getButton()
    {
        // return the button/ devuelve el boton 
        return sprintf(
          '<input type="image" src="%s" name="%s" id="%2$s"%s '. FH_XHTML_CLOSE .'>',
          $this->_sImage,
          $this->_sName,
          (isset($this->_sExtra) ? ' '.$this->_sExtra:'').
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'"' : '')
        );
    }
}

?>