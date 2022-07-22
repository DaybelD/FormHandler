<?php
/**
 * class DateField
 *
 * Create a datefield/ crea un campo de fecha
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */

class DateField extends Field
{
    private $_sMask;     // string: how to display the fields (d-m-y) or other/ cómo mostrar los campos (d-m-y) u otros
    private $_oDay;      // SelectField or TextField: object of the day selectfield/ objeto del dia en el campo de seleccion 
    private $_oMonth;    // SelectField or TextField: object of the month selectfield/ objeto del mes en el campo de seleccion
    private $_oYear;     // SelectField or TextField: object of the year selectfield/ objeto del año en el campo de seleccion
    private $_sInterval; // string: interval of the year/ intervalo del año 
    private $_bRequired; // boolean: if the field is required or if we have to give the option to leave this field empty/ si el campo es requerido o si tenemos que dar la opción de dejar este campo vacío

    /**
     * DateField::DateField()
     *
     * Constructor: create a new datefield object/ crea un nuevo objeto para el campo de fecha
     *
     * @param object &$oForm: the form where the datefield is located on/ formulario donde esta localizado el campo de fecha
     * @param string $sName: the name of the datefield/ nombre del campo
     * @param string $sMask: the mask which is used to display the fields/ mascara que se utiliza para mostrar los campos 
     * @return DateField
     * @access public
     * @author Teye Heimans
     */
    public function __construct( &$oForm, $sName, $sMask = null, $bRequired = null, $sInterval = null )
    {
        // set the default date display/ establezca la fecha a mostrar por defecto
       $this -> setMask( !is_null( $sMask ) ? $sMask : FH_DATEFIELD_DEFAULT_DISPLAY );

        // set the default interval/ establezca el intervalo por defecto
        $this -> setInterval( !is_null( $sInterval ) ? $sInterval : FH_DATEFIELD_DEFAULT_DATE_INTERVAL);

        // set if the field is required/ establezca si el campo es requerido
        $this->setRequired( !is_null( $bRequired ) ? $bRequired : FH_DATEFIELD_DEFAULT_REQUIRED );

        // d = selectfield day
        // m = selectfield month
        // y = selectfield year
        // D = textfield day
        // M = textfield month
        // Y = textfield year

        // generate the objects for the fields/ genera los objetos para los campos
        $fields = $this -> _getFieldsFromMask();
        $len = strlen( $fields );

        for( $x = 0; $x < $len; $x++ )
        {
            $c = $fields[$x];

            switch ( $c ) {
                // year selectfield/ seleccion de año
            	case 'y':
            	    $this -> _oYear = new SelectField( $oForm, $sName.'_year');

                    // get the year interval/ obtenga el intervalo del año
                	list( $iStart, $iEnd ) = $this->_getYearInterval();
                	$iEnd   = intval($iEnd);
                    $iStart = intval( $iStart );
                    $iYear = date('Y');

                    // set the years/ establezca los años
                    $aYears = array();
                    if(!$bRequired) $aYears[''] = ''; // was 0000

                    // calculate the difference between the years/ calcular la diferencia entre años
                    $iDiff = ($iYear + $iEnd) - ($iYear - $iStart);

                    $iCounter = 0;
                    while( $iDiff != $iCounter )
                    {
                        $i = ($iYear + $iEnd) - $iCounter;

                        $aYears[$i] = $i;

                        $iCounter += $iCounter < $iDiff ? 1 : -1;
                    }

                    // set the options/ establezca las opciones
                    $this -> _oYear -> setOptions( $aYears );

            		break;

                // year textfield/ campo de texto para año
            	case 'Y':
            	    $this -> _oYear = new TextField ( $oForm, $sName.'_year');
                    $this -> _oYear -> setSize( 4 );
                    $this -> _oYear -> setMaxlength( 4 );
                    $this -> _oYear -> setValidator( _FH_DIGIT );
                    break;

                // month selectfield/ seleccion de mes
                case 'm':
                    $this -> _oMonth = new SelectField( $oForm, $sName.'_month');
                    // set the months in the field/ establezca los meses en el campo
                    $aMonths = array(
                      '01' => $oForm->_text( 1 ),
                      '02' => $oForm->_text( 2 ),
                      '03' => $oForm->_text( 3 ),
                      '04' => $oForm->_text( 4 ),
                      '05' => $oForm->_text( 5 ),
                      '06' => $oForm->_text( 6 ),
                      '07' => $oForm->_text( 7 ),
                      '08' => $oForm->_text( 8 ),
                      '09' => $oForm->_text( 9 ),
                      '10' => $oForm->_text( 10 ),
                      '11' => $oForm->_text( 11 ),
                      '12' => $oForm->_text( 12 )
                    );
                    if(!$bRequired )
                    {
                        $aMonths[''] = ''; // was 00
                        ksort($aMonths);
                    }

                    // set the options/ establezca las opciones
                    $this -> _oMonth -> setOptions( $aMonths );
                    break;

                // month textfield/ campo de texto para mes
                case 'M':
                    $this -> _oMonth = new TextField ( $oForm, $sName.'_month' );
                    $this -> _oMonth -> setSize( 2 );
                    $this -> _oMonth -> setMaxlength( 2 );
                    $this -> _oMonth -> setValidator( _FH_DIGIT );
                    break;

                // day selectfield/ seleccion de dia 
                case 'd':
                    $this -> _oDay = new SelectField( $oForm, $sName.'_day');

                    // get the days/ obtenga los dias 
                    $aDays = array();
                    if(!$bRequired) $aDays[''] = ''; // was 00

                    for($i = 1; $i <= 31; $i++)
                    {
                        $aDays[sprintf('%02d', $i)] = sprintf('%02d', $i);
                    }
                    $this -> _oDay -> setOptions( $aDays );
                    break;

                // day textfield/ campo de texto para dia
                case 'D':
                    $this -> _oDay = new TextField( $oForm, $sName.'_day' );
                    $this -> _oDay -> setSize( 2 );
                    $this -> _oDay -> setMaxlength( 2 );
                    $this -> _oDay -> setValidator( _FH_DIGIT );
                    break;

            }
        }

        // call the Field constructor/ llama al contructor del campo
        parent::__construct( $oForm, $sName);
    }

    /**
     * DateField::setRequired()
     *
     * Set if the datefield is required or if we have to give the user 
     * the option to select empty value
     * Establecer si el campo de fecha es obligatorio o si tenemos que darle al usuario
     * la opcion de seleccionar un campo vacio
     *
     * @param boolean $bStatus: the status
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setRequired( $bStatus )
    {
        $this->_bRequired = $bStatus;

        if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) )
          $this -> _oYear -> setValidator( $bStatus ? FH_DIGIT : _FH_DIGIT );

        if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) )
          $this -> _oMonth -> setValidator( $bStatus ? FH_DIGIT : _FH_DIGIT );

        if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) )
          $this -> _oDay -> setValidator( $bStatus ? FH_DIGIT : _FH_DIGIT );
    }

    /**
     * DateField::setDisplay()
     *
     * Set the display of the fields
     * (use d,m,y and t for positioning, like "d-m-y", "t, d d" or "y/m/d")
     * Configurar la visualización de los campos
     * (use d, m, y y t para posicionamiento, como "d-m-y", "t, d d" o "y/m/d")
     * 
     * @param string $sMast: how we have to display the datefield (day-month-year combination)
     * cómo tenemos que mostrar el campo de fecha (combinación de día-mes-año) 
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setMask( $sMask )
    {
        $this->_sMask = $sMask ;
    }

    /**
     * DateField::setInterval()
     *
     * Set the year range of the years/ Establecer el rango de año de los años
     * The interval between the current year and the years to start/stop.
     * El intervalo entre el año actual y los años para empezar/finalizar.
     * Default the years are beginning at 90 years from the current. It is also possible to have years in the future.
     * Por defecto, los años comienzan a los 90 años a partir del actual. También es posible tener años en el futuro.
     * This is done like this: "90:10" (10 years in the future).Esto seria asi: "90:10" (10 años en el futuro)
     *
     * @param string/int $sInterval: the interval we should use/ el intervalo que debemos usar
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setInterval( $sInterval )
    {
        $this->_sInterval = $sInterval;
    }

    /**
     * DateField::setExtra()
     *
     * Set some extra tag information of the fields/ Establezca alguna informacion extra en la etiqueta de los campos
     *
     * @param string $sExtra: The extra information to include with the html tag/ La información adicional para incluir con la etiqueta html
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setExtra( $sExtra )
    {
    	if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) )
    	  $this -> _oYear -> setExtra ( $sExtra );

    	if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) )
    	  $this -> _oMonth -> setExtra ( $sExtra );

    	if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) )
    	  $this -> _oDay -> setExtra ( $sExtra );
    }

    /**
     * DateField::getValue()
     *
     * return the value of the field (in d-m-Y format!) or when a field is given, the value of that field
     * Devuelve el valor del campo (en d-m-y formato!) o cuando se da un campo, el valor de ese campo
     * @param string $fld: the field where you want to value of/ el campo en el que desea el valor de
     * @return string: the current value of the field/ el valor actual del campo
     * @access public
     * @author Teye Heimans
     */
    public function getValue( $fld = null)
    {
        // when no specific field is requested../ cuando no se solicita un campo específico.
        if( $fld == null )
        {
            // get the values of all fields/ obtenga el valor de todos los campos
            $d = $this -> getValue('d');
            $m = $this -> getValue('m');
            $y = $this -> getValue('y');

            // return the value of the datefield/ devuelve el valor del campo de fecha
            if( $d == '' && $m == '' && $y == '')
            {
                return '';
            }
            else
            {
                return $this->_fillMask( $d, $m, $y );
            }
        }
        // a specific field is requested/ un campo especifico es requerido
        else
        {
            // which field is requested ?/ cual campo es requerido?
            switch ( strtolower( $fld ) )
            {
                case 'y':
                    if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) )
    	            return $this -> _oYear -> getValue();
    	            break;

    	        case 'm':
    	            if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) )
    	            return $this -> _oMonth -> getValue();
    	            break;

    	        case 'd':
    	            if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) )
    	            return $this -> _oDay -> getValue();
    	            break;
            }

        // no field matched. Return an empty value/ ningún campo coincide. Devolver un valor vacío
            return '';
        }
    }

    /**
     * DateField::getAsArray()
     *
     * Get the date value as an array: array(y,m,d)
     * Obtenga el valor de la fecha como una matriz: matriz (y, m, d)
     *
     * @return array
     * @access public
     * @author Teye Heimans
     * @since 25/11/2005
     */
    public function getAsArray()
    {
        $d = $this -> getValue('d');
        $m = $this -> getValue('m');
        $y = $this -> getValue('y');

        return array( $y, $m, $d );
    }

    /**
     * DateField::isValid()
     *
     * Check if the date is valid (eg not 31-02-2003)
     * Comprobar si la fecha es válida (por ejemplo, no 31-02-2003)
     * 
     * @return boolean: true if the field is correct, false if not
     * @access public
     * @author Teye Heimans
     */
    public function isValid()
    {
    	// the result has been requested before../ el resultado ha sido solicitado antes..
    	if( isset($this->_isValid))
    	{
    		return $this->_isValid;
    	}

    	// check if the year field is valid/ compruebe si el campo año es valido
        if( isset( $this -> _oYear ) && is_object( $this->_oYear) )
        {
            if( ! $this -> _oYear -> isValid() )
            {
                // get the error
                $this -> _sError = $this -> _oYear -> getError();
                return false;
            }
        }

        // check if the month field is valid/ compruebe si el campo mes es valido
        if( isset( $this -> _oMonth ) && is_object( $this->_oMonth) )
        {
            if( ! $this -> _oMonth -> isValid() )
            {
                // get the error
                $this -> _sError = $this -> _oMonth -> getError();
                return false;
            }
        }

        // check if the day field is valid/ compruebe si el campo dia es valido
        if( isset( $this -> _oDay ) && is_object( $this->_oDay) )
        {
            if( ! $this -> _oDay -> isValid() )
            {
                // get the error
                $this -> _sError = $this -> _oDay -> getError();
                return false;
            }
        }

        $d = $this -> getValue('d');
        $m = $this -> getValue('m');
        $y = $this -> getValue('y');
        $mask = strtolower( $this->_sMask );

        if( $y != '' && strlen( $y ) != 4 )
        {
            $this->_sError = $this->_oForm->_text( 13 );
            return false;
        }

    	// first of al check if the date is right when a valid date is submitted
    	// (but only when all fields are displayed (d m and y or t in the display string!)
        // En primer lugar, compruebe si la fecha es correcta cuando se envía una fecha válida
        // (pero solo cuando se muestran todos los campos (¡d m y y o t en la cadena de visualización!)
    	if( strpos( $mask, 'd') !== false &&
    	    strpos( $mask, 'm') !== false &&
    	    strpos( $mask, 'y') !== false &&
    	    ($d != '00' && $d != '') &&
    	    ($m != '00' && $m != '') &&
    	    ($y != '0000' && $y != '') &&
            !checkdate( $m, $d, $y ))
        {
        	$this->_sError = $this->_oForm->_text( 13 );
            $this->_isValid = false;
            return $this->_isValid;
        }

        // if validator given, check the value with the validator
        // si se proporciona un validador, verifique el valor con el validador
    	if(isset($this->_sValidator) && !empty($this->_sValidator))
    	{
    		$this->_isValid = parent::isValid();
    	}
    	// no validator is given.. value is always valid
        // no se da validador... el valor siempre es válido
    	else
    	{
    		$this->_isValid = true;
    	}

    	return $this->_isValid;
    }

    /**
     * DateField::getField()
     *
     * return the field/ devuelve el campo
     *
     * @return string: the field/ campo
     * @access public
     * @author Teye Heimans
     */
    public function getField()
    {
        // set the date when: / establezca la fecha cuando:
        // - the field is empty/ el campo este vacio
    	// - its not an edit form/ no es un formulario de edicion  
    	// - the form is not posted/ el formulario no esta publicado
    	// - the field is required/ el campo es requerido
    	// - there is no value set.../ no hay un valor establecido
    	if( !$this->_oForm->isPosted() && # not posted
    	    (!isset($this->_oForm->edit) || !$this->_oForm->edit) &&       # no edit form
    	    ($this->getValue() == $this->_fillMask() || # empty values
    	     $this->getValue() == '') &&  # there is no value
    	     $this->_bRequired )          # field is required
    	{
    		// set the current date if wanted/ establece la fecha actual si se quiere
    		if( FH_DATEFIELD_SET_CUR_DATE )
    		{
    			$this->setValue( date('d-m-Y') );
    		}
    	}

    	// view mode enabled ?/ modo vista esta habilitado?
        if( $this -> getViewMode() )
        {
            // get the view value.. / obtenga el valor de la vista
            return $this -> _getViewValue();
        }

    	$year = isset( $this -> _oYear ) && is_object( $this -> _oYear ) ?
    	  $this -> _oYear -> getField() : '';

        $month = isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) ?
          $this -> _oMonth -> getField() : '';

        $day = isset( $this -> _oDay ) && is_object( $this -> _oDay ) ?
    	  $this -> _oDay -> getField() : '';

        // replace the values by the fields../ reemplace los valores por los campos
        return $this->_fillMask(
          ' '.$day.' ', #day
          ' '.$month.' ', #month
          ' '.$year.' ' #year
        ) .
        (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
    }

    /**
     * DateField::setValue()
     *
     * Set the value of the field. The value can be 4 things: 
     * Establece el valor del campo. El valor pueden ser 4 cosas:
     * - "d-m-Y" like 02-04-2004/ - "d-m-Y" como 02-04-2004
     * - "Y-m-d" like 2003-12-24/ - "Y-m-d" como 2003-12-24
     * - Unix timestamp like 1104421612/ Marca de tiempo de Unix como 1104421612
     * - Mask style. If you gave a mask like d/m/y, this is valid: 02/12/2005/ -Estilo de máscara. Si diste una máscara como d/m/y, esto es válido: 12/02/2005
     *
     * @param string $sValue: the time to set the current value/ El tiempo para establecer el valor actual
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setValue( $sValue )
    {
        // remove the time part if the date is coming from a datetime field
        // elimine la parte de la hora si la fecha proviene de un campo de fecha y hora
    	$aMatch = array();
    	if( preg_match('/^([0-9]{4}-[0-9]{2}-[0-9]{2}) [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $sValue, $aMatch) )
    	{
    		$sValue = $aMatch[1];
    	}

    	// replace the d, m and y values/ reemplace los valores de d, m, y.
    	$regex = $this->_fillMask( '%%2%%', '%%2%%', '%%4%%' );

       	// next, escape dangerous characters for the regex
        //a continuación, escape de caracteres peligrosos para la expresión regular
    	$metachar = array( '\\',   '/',  '^',  '$',  '.',  '[',  ']',  '|',  '(',  ')',  '?',  '*',  '+',  '{',  '}' );
    	$escape   = array( '\\\\', '\/', '\^', '\$', '\.', '\[', '\]', '\|', '\(', '\)', '\?', '\*', '\+', '\{', '\}' );
    	$regex    = str_replace( $metachar, $escape, $regex );

    	// now add the (\d+) for matching the day, month and year values
        // ahora agregue el (\d+) para hacer coincidir los valores de día, mes y año
    	$regex = str_replace('%%2%%', '(\d+){1,2}', $regex );
    	$regex = str_replace('%%4%%', '(\d{4})', $regex );
    	$regex = '/'.$regex.'/';

    	// now find the results/ ahora encuentra los resultados
    	$match = array();
    	if( preg_match($regex, $sValue, $match ) )
    	{
    	    // get the fields from the mask/ obtenga los campos desde la mascara
    	    $str = $this->_getFieldsFromMask();

    	    // get the length of the buffer (containing the dmy order)
            // obtener la longitud del buffer (que contiene la orden dmy)
    	    $len = strlen( $str );

    	    // save the results in the vars $d, $m and $y
            //guarda los resultados en las vars $d, $m, $y
    	    for( $i = 0; $i < $len; $i++ )
    	    {
    	        $c  = $str[$i];
    	        $$c = $match[$i+1];
    	    }
    	}
    	// the given value does not match the mask... is it dd-mm-yyyy style ?
        // el valor dado no coincide con la máscara... es estilo dd-mm-yyyy?
    	elseif( preg_match( '/^(\d{2})-(\d{2})-(\d{4})$/', $sValue, $match ) )
    	{
    	    $d = $match[1];
    	    $m = $match[2];
    	    $y = $match[3];
    	}
    	// is the given value in yyyy-mm-dd style ? / El valor dado está en estilo aaaa-mm-dd?
    	elseif( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $sValue, $match ) )
    	{
    	    $d = $match[3];
    	    $m = $match[2];
    	    $y = $match[1];
    	}
    	// is the given value a timestamp ?/ El valor dado es una marca de tiempo?
    	elseif( strlen( $sValue ) >= 8 && Validator::IsDigit($sValue) )
    	{
    	    $d = date('d', $sValue );
    	    $m = date('m', $sValue );
    	    $y = date('Y', $sValue );
    	}
    	if( !empty( $t ) ) $y = $t;

    	// save the dates for the fields/ guarda los datos del campo
    	if( isset( $this -> _oYear ) && is_object( $this -> _oYear ) && isset( $y ) )
    	  $this -> _oYear -> setValue( $y );

	    if( isset( $this -> _oMonth ) && is_object( $this -> _oMonth ) && isset( $m ) )
	      $this -> _oMonth -> setValue( $m );

        if( isset( $this -> _oDay ) && is_object( $this -> _oDay ) && isset( $d ))
          $this -> _oDay -> setValue( $d );
    }


    /**
     * DateField::_getFieldsFromMask()
     *
     * Get the fields from the mask. Obtenga los campos desde la mascara
     * For example: "select the \da\y: d" will result in "d".
     * "y/m/d" will result in "ymd"
     * Por ejemplo: "seleccione el \da\y: d" dará como resultado "d".
     * "y/m/d" resultará en "ymd"
     *
     * @param string $mask: The mask where we should get the fields from/ La máscara de donde debemos obtener los campos
     * @return string
     * @access protected
     * @author Teye Heimans
     */
    protected function _getFieldsFromMask( $mask = null)
    {
        // when no mask is given, use the default mask
        // cuando no se da la mascara, use la mascara por defecto
        if( is_null( $mask ) )
        {
            $mask = $this->_sMask;
        }

        // buffer
	    $str = '';
	    $len = strlen( $mask );
	    $placeholders = array( 'd', 'D', 'm', 'M', 'y', 'Y' );

	    // walk each character in the mask/ camina cada caracter en la máscara
	    for( $i = 0; $i < $len; $i++ )
	    {
	        // get the character/ obtenga el caracter
	        $c = $mask[ $i ];

	        // day, month or year ?/ dia, mes o año?
    	    if( in_array( $c, $placeholders ) )
    	    {
	           // not the first char ? / no es el caracter?
	           if( $i != 0 )
	           {
	               // was the char not escaped?/ no se escapo el caracter? 
	               if( $mask[ $i - 1 ] != '\\' )
	               {
	                   $str .= $c;
	               }
	           }
	           // the first char/ el primer caracter
	           else
	           {
	               // just add it to the buffer
	               $str .= $c;
	           }
    	    }
	    }

	    return $str;
    }

    /**
     * DateField::_fillMask()
     *
     * Return the mask filled with the given values
     * Delvuelve la mascara llena con los valores dados
     *
     * @param string $d: The replacement for the "d"/ el reemplazo de "d"
     * @param string $m: The replacement for the "m"/ el reemplazo de "m"
     * @param string $y: The replacement for the "y"/ el reemplazo de "y"
     * @return string
     * @access protected
     * @author Teye Heimans
     */
    protected function _fillMask( $d = '', $m = '', $y = '', $mask = null )
    {
        // when no mask is given, use the default mask
        // cuando no se da la mascara, use la mascara por defecto
        if( is_null( $mask ) )
        {
            $mask = $this->_sMask;
        }

        $placeholders = array( 'd', 'D', 'm', 'M', 'y', 'Y' );

        // make sure that the fields are not replacing other fields characters
        // and that escaped chars are possible, like "the \da\y is: d"
        // asegúrese de que los campos no reemplacen otros caracteres de campos
        // y que los caracteres escapados son posibles, como "el \da\y es: d"
        $len = strlen( $mask );
        $str = '';
        for( $i = 0; $i < $len; $i++ )
        {
            $c = $mask[$i];

            // field char ?/ caracter de campo?
            if( in_array( $c, $placeholders))
            {
                // first char ?/ primer caracter?
                if( $i == 0 )
                {
                    $str .= '%__'.strtolower($c).'__%';
                }
                else
                {
                    // check if the char is escaped./ compruebe si el caracter se escapa
                    if( $mask[$i - 1] == '\\' )
                    {
                        // the char is escaped, display the char without slash
                        // el carácter se escapa, muestra el carácter sin slash
                        $str = substr($str, 0, -1).$c;
                    }
                    // the char is not escaped/ el caracter no se escapa
                    else
                    {
                        $str .= '%__'.strtolower($c).'__%';
                    }
                }
            }
            else
            {
                $str .= $c;
            }
        }

        // replace the values by the new values/ reemplace los valores por los nuevos valores
        return str_replace(
          array('%__d__%', '%__m__%', '%__y__%' ),
          array( $d, $m, $y ),
          $str
        );
    }

    /**
     * DateField::_getYearInterval()
     *
     * Get the year interval/ Obtenga el intervalo del año
     *
     * @return array
     * @access protected
     * @author Teye Heimans
     */
    protected function _getYearInterval ()
    {
    	$sInterval = $this->_sInterval;

        // get the year interval for the dates in the field
        // obtenga el intervalo del año por las fechas en el campo
        if( strpos($sInterval, ':') )
        {
             list( $iStart, $iEnd ) = explode( ':', $sInterval, 2 );
        }
        // no splitter found, just change the start interval
        // no se encontró divisor, solo cambie el intervalo de inicio
        elseif( is_string($sInterval) || is_integer($sInterval) && !empty($sInterval) )
        {
            $iStart = $sInterval;
            $iEnd = 0;
        }
        // no interval given.. use the default/ no se da intervalo, use el intervalo por defecto
        else
        {
            $iStart = 90;
            $iEnd = 0;
        }

        return array( $iStart, $iEnd );
    }
}