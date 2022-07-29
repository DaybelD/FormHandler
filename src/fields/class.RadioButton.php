<?php

/**
 * class RadioButton
 *
 * Create a RadioButton
 * Crea un radiobutton
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class RadioButton extends Field
{
    public $_aOptions;              // string: the value with is selected/ valor que se selecciona
    private $_iClass;                // clase check (radio) comprueba la clase (radio)
    private $_bUseArrayKeyAsValue;   // boolean: if the keys of the array should be used as values/ si las claves de la matriz deben usarse como valores
    private $_sMask;                 // string: what kind of "glue" should be used to merge the fields/ que tipo de "glue" debe usarse oara fusionar los campos
    private $_oLoader;               // object: a maskloader object/ cargador de mascaras

    /**
     * RadioButton::RadioButton()
     *
     * Constructor: Create a new radiobutton object/ crea un nuevo objeto radiobutton
     *
     * @param object $oForm: The form where this field is located on/ formulario donde se encuentra el campo
     * @param string $sName: The name of the field/ nombre del campo
     * @param array|string $aOptions: The options for the field/ opciones para el campo
     * @return RadioButton
     * @author Teye Heimans
     */
    public function __construct( &$oForm, $sName, $aOptions )
    {
        // call the constructor of the Field class
        // llama al constructor de la clase campo
        parent::__construct( $oForm, $sName );

        $this->_aOptions = $aOptions;

        $this->setMask           ( FH_DEFAULT_GLUE_MASK );
        $this->useArrayKeyAsValue( FH_DEFAULT_USEARRAYKEY );
    }

    //public function setClass( $class )
    //{
    //    $this->_iClass = 'form-check-label '. $class;
    //}
    //Nota: al colocar la funcion no muestra los nombres de las opciones
    //No hace falta la funcion de class para el radiobutton 


    /**
     * RadioButton::useArrayKeyAsValue()
     *
     * Set if the array keys of the options has to be used as values for the field
     * Establece si las claves de matriz de las opciones deben usarse como valores para el campo
     *
     * @param boolean $bMode:  The mode/ modo
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function useArrayKeyAsValue( $bMode )
    {
        $this->_bUseArrayKeyAsValue = $bMode;
    }

    /**
     * RadioButton::setMask()
     *
     * Set the "glue" used to glue multiple radiobuttons
     * establezca la "glue" para pegar varios radiobuttons
     *
     * @param string $sMask
     * @return void
     * @author Teye Heimans
     * @access public
     */
    public function setMask( $sMask )
    {
        // when there is no %field% used, put it in front of the mask/glue
        // cuando no se use %field%, colóquelo delante de la máscara/glue
        if( strpos( $sMask, '%field%' ) === false )
        {
            $sMask = '%field%' . $sMask;
        }

        $this->_sMask = $sMask;
    }

    /**
     * RadioButton::getField()
     *
     * Return the HTML of the field/ devuelve el HTML del campo
     *
     * @return string: the html of the field
     * @access Public
     * @author Teye Heimans
     */
    public function getField()
    {
        // view mode enabled ?/ modo vista habilitado?
        if( $this -> getViewMode() )
        {
            // get the view value../ obtenga el valor de la vista..
            return $this -> _getViewValue();
        }

		if( is_array( $this->_aOptions ) && count( $this->_aOptions )>0 )
        {
            $sResult = '';
            foreach( $this->_aOptions as $iKey => $sValue )
            {            
                if(!$this->_bUseArrayKeyAsValue)
                {
                    $iKey = $sValue;
                }
				
                $sResult .= $this->_getRadioButton( $iKey, $sValue, true );
            }
        }
        
        elseif( $this->_aOptions == '' || count( $this->_aOptions )===0 )
        {
        	$sResult = ' '; 
        }
        
        else
        {
            $sResult = $this->_getRadioButton( $this->_aOptions, '' );
        }

        // when we still got nothing, the mask is not filled yet.
        // cuando todavía no tenemos nada, la mascara aún no está llena.
        // get the mask anyway/ obtenga la mascara de todas formas
        if( empty( $sResult ) )
        {
            $sResult = $this -> _oLoader -> fill();
        }

        return $sResult . (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
    }

    /**
     * RadioButton::_getRadioButton()
     *
     * Return the radiobutton with the given title and value
     * Devuelve el radiobutton con el titulo y valor dados
     *
     * @param string $sValue: the value for the checkbox/ valor del checkbox
     * @param string $sTitle: the title for the checkbox/ titulo del checkbox
     * @param bool $bUseMask: Do we need to use the mask ?/ necesitamos usar la mascara?
     * @return string: the HTML for the checkbox/ HTML para el checkbox
     * @access Private
     * @author Teye Heimans
     */
    private function _getRadioButton( $sValue, $sTitle, $bUseMask = false )
    {
        
        static $counter = 1;

        $sValue = trim( $sValue );
        $sTitle = trim( $sTitle );

        if( !isset( $this -> _oLoader ) ||is_null( $this -> _oLoader ) )
        {        
            $this -> _oLoader = new MaskLoader();
            $this -> _oLoader -> setMask( $this->_sMask );
            $this -> _oLoader -> setSearch( '/%field%/' );
        }

        $sField = sprintf(
          '<input type="radio" name="%s" id="%1$s_%d" class="form-check-input" value="%s" %s'. FH_XHTML_CLOSE .'><label for="%1$s_%2$d" class="form-check-label">%s</label>',
          $this->_sName,
          $counter++,
          htmlspecialchars($sValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING),
          (isset($this->_mValue) && $sValue == $this->_mValue ? 'checked="checked" ':'').
          (isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
          (!empty($this->_sExtra) ? $this->_sExtra.' ':''),
          $sTitle
        );

        // do we have to use the mask ?
        // tenemos que usar la mascara?
        if( $bUseMask )
        {
            $sField = $this -> _oLoader -> fill( $sField );
        }

        return $sField;
    }
}
