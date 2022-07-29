<?php

/**
 * class TextArea
 *
 * Create a textarea
 * Crea un area de texto
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class TextArea extends Field {

    private $_iRows;        // int: number of rows which the textarea should get/ numero de filas que debe tener el area de texto
    private $_iClass;   // string: class associated with the field/ clase asociada al campo
    private $_bShowMessage; // boolean: should we display the limit message/ debemos mostrar el mensaje limtado

    /**
     * TextArea::TextArea()
     *
     * Constructor: create a new textarea/ crea una nueva area de texto
     *
     * @param object &$oForm: The form where this field is located on/ formulario donde se encuentra localizado
     * @param string $sName: The name of the field/ nombre del campo
     * @return TextArea
     * @author Teye Heimans
     * @access public
     */
    public function __construct( &$oform, $sName )
    {
        // call the constructor of the Field class
        // llama al constructor de la clase campo
        parent::__construct( $oform, $sName );

        $this->setClass('');
        $this->setRows( 5 );
    }

    /**
     * TextArea::setClass()
     *
     * Set the class of the field/ establezca la clase del campo
     *
     * @param integer $iClass: class of the field
     * @return void
     * @author Teye Heimans
     * @access public
     */
    public function setClass( $class )
    {
        $this->_iClass = trim('form-control '. $class);
    }

    /**
     * TextArea::setMaxLength()
     *
     * Set the max length of the input. Use false or 0 to disable the limit
     * Establezca la longitud máxima de la entrada. Use falso o 0 para deshabilitar el límite
     * @param int $iMaxLength
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setMaxLength( $iMaxLength, $bDisplay )
    {
        $this -> _iMaxLength   = $iMaxLength;
        $this -> _bShowMessage = $bDisplay;
    }

    /**
     * TextArea::isValid()
     *
     * Check if the field's value is valid
     * Comprueba si el valor del campo es valido
     * 
     * @return boolean
     * @access public
     * @author Teye Heimans
     */
    public function isValid()
    {
        // is a max length set ?
        // esta la longitud maxima establecida?
        if( isset( $this -> _iMaxLength ) && $this -> _iMaxLength > 0 )
        {
            // is there to many data submitted ?
            // hay muchos datos enviados?
            $iLen = strlen( $this -> _mValue );
            if( $iLen > $this -> _iMaxLength )
            {
                // set the error message
                // establezca el mensaje de error
                $this -> _sError = sprintf(
                  $this -> _oForm -> _text( 40 ),
                  $this -> _iMaxLength,
                  $iLen,
                  abs($iLen - $this->_iMaxLength)
                );

                // return false because the value is not valid
                // devuelve false porque el valor no es valido
                return false;
            }
        }

        // everything ok untill here, use the default validator
        // todo bien hasta aqui, use el validador por defecto
        return parent::isValid();
    }

    /**
     * TextArea::setRows()
     *
     * Set the number of rows of the textarea
     * Establezca el numero de filas del area de texto
     *
     * @param integer $iRows: the number of rows/ numero de filas
     * @return void
     * @author Teye Heimans
     * @access public
     */
    public function setRows( $iRows )
    {
        $this->_iRows = $iRows;
    }

    /**
     * TextArea::getField()
     *
     * Return the HTML of the field/ Devuelve el HTML del campo
     *
     * @return string: the html of the field
     * @author Teye Heimans
     * @access public
     */
    public function getField()
    {
        // view mode enabled ?/ modo vista habilitado?
        if( $this -> getViewMode() )
        {
            // get the view value../ obtenga el valor de la vista
            return $this -> _getViewValue();
        }

        // is a limit set ?/ hay limite establecido?
        if( isset( $this -> _iMaxLength ) && $this -> _iMaxLength > 0  )
        {
            // the message/ mensaje
            $sMessage = $this-> _oForm -> _text( 36 );

            // set the event/ establezca el evento
            $this -> _sExtra .=
              sprintf(
                " onkeyup=\"displayLimit('%s', '%s', %d, %s, '%s');\"",
                $this -> _oForm -> getFormName(),
                $this -> _sName,
                $this -> _iMaxLength,
                ( $this -> _bShowMessage ? 'true' : 'false'),
                htmlspecialchars( $sMessage, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING )
              )
            ;

            // should the message be displayed ?/ debe mostrarse el mensaje?
            if( $this -> _bShowMessage )
            {
                // add the javascript to the fields "extra" argument
                // agregue el javascript a los campos "extra" argumento
                $this -> setExtraAfter(
                  "<br ". FH_XHTML_CLOSE ."><div id='". $this -> _sName."_limit'></div>\n"
                );
            }

            // make sure that when the page is loaded, the message is displayed
            // asegurese que cuando la pargina este cargada, el mensaje se muestre
            $this -> _oForm -> _setJS(
              sprintf(
                "displayLimit('%s', '%s', %d, %s, '%s');\n",
                $this -> _oForm -> getFormName(),
                $this -> _sName,
                $this -> _iMaxLength,
                ( $this -> _bShowMessage ? 'true' : 'false'),
                $sMessage
              ),
              false,
              false
            );
        }

        // return the field/ devuelve el campo
        return sprintf(
          '<textarea name="%s" id="%1$s" class="%s" rows="%d"%s>%s</textarea>%s',
          $this->_sName,
          $this->_iClass,
          $this->_iRows,
          (isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'" ' : '').
          (isset($this->_sExtra) ? ' '.$this->_sExtra :''),
          (isset($this->_mValue) ? htmlspecialchars($this->_mValue, ENT_COMPAT | ENT_HTML401, FH_HTML_ENCODING) : ''),
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
        );
    }
}
