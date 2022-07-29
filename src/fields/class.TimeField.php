<?php

/**
 * class TimeField
 *
 * Create a new TimeField class
 * Crea una nueva clase de campo de tiempo
 *
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 */
class TimeField extends Field
{
    private $_iFormat;   // integer: hour format: {12, 24}/ formato de hora
    public $_oHour;     // SelectField: object of the hour selectfield/ objeto del campo de seleccion de hora
    public $_oMinute;   // SelectField: object of the minute selectfield/ objeto del campo de seleccion de minuto
    public $_bRequired; // boolean: if the field is required or if we have to give the option to leave this field empty/ si el campo es requerido o si tenemos que darle la opcion de dejar el campo vacio

    /**
     * TimeField::TimeField()
     *
     * Constructor: create a new TimeField on the given form/ crea un nuevo campo de hora
     *
     * @param object $oForm: The form where the field is located on/ formulario donde se encuentra el campo
     * @param string $sName: The name of the field/ nombre del campo
     * @return TimeField
     * @author Teye Heimans
     */
    public function __construct( &$oForm, $sName )
    {
        // set the default hour format/ establece el formato de hora por defecto
        $this->setHourFormat( FH_TIMEFIELD_DEFAULT_HOUR_FORMAT );

        // set if the field is required/ establece si el campo es requerido
        $this->setRequired( FH_TIMEFIELD_DEFAULT_REQUIRED );

        // make the hour and minute fields/ hacer los campos para hora y minuto
        $this->_oHour   = new SelectField($oForm, $sName.'_hour');
        $this->_oMinute = new SelectField($oForm, $sName.'_minute');

        parent::__construct( $oForm, $sName );

        // posted or edit form? Then load the value of the time
        // fomulario de envio o edicion? Entonces cargue el valor del tiempo
        if( $oForm->isPosted() || (isset($oForm->edit) && $oForm->edit) )
        {
            $this->_mValue = $this->_oHour->getValue().':'.$this->_oMinute->getValue();
        }
    }

    /**
     * TimeField::setExtra()
     *
     * Set some extra tag information of the fields 
     * Establece alguna etiqueta adiconal de informacion de los campos
     *
     * @param string $sExtra: The extra information to include with the html tag/ la informacion adicional para incluir con la etiqueta html
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setExtra( $sExtra )
    {
    	$this->_oHour->setExtra  ( $sExtra );
    	$this->_oMinute->setExtra( $sExtra );
    }

    /**
     * TimeField::setHourFormat()
     *
     * Set the hour format (eg. 12 or 24) 
     * Establece el formato de hora
     *
     * @param integer $iFormat: The hour format/ formato de hora
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setHourFormat( $iFormat )
    {
        if($iFormat == 12 || $iFormat == 24)
        {
            $this->_iFormat = $iFormat;
        }
        else
        {
        	trigger_error(
        	  'Invalid value as hour format! Only 12 or 24 are allowed!',
        	  E_USER_WARNING
        	);
        }
    }

    /**
     * TimeField::setRequired()
     *
     * Set if the timefield is required or if we have to give the user
     * the option to select an empty value 
     * Establece si el campo de hora es requerido o si tenemos que darle al usuario 
     * la opcion de seleccionar un valor vacio
     *
     * @param boolean $bStatus: The status
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setRequired( $bStatus )
    {
        $this->_bRequired = $bStatus;
    }


    /**
     * TimeField::setValue()
     *
     * Set the value of the field
     * Establece el valor del campo
     *
     * @param string $sValue: The new value of the field/ el nuevo valor del campo
     * @return void
     * @access Public
     * @author Teye Heimans
     */
    public function setValue( $sValue )
    {
    	if( strpos($sValue,':') !== false)
    	{
            list($sHour, $sMinute) = explode(':', $sValue);

            $this->_oHour->setValue   ( $sHour );
            $this->_oMinute->setValue ( $sMinute );
            $this->_mValue = $sValue;
        }
        // possibility to set "no" value when the field is not required
        // posibilidad de establecer el valor "no" cuando el campo no es requerido
        elseif( (strtolower($sValue )== "null" || empty( $sValue ) ) && !$this->_bRequired )
        {
            $this->_mValue = "";
        }
    }

    /**
     * TimeField::getValue()
     *
     * Return the current value of the field 
     * Devuelve el valor actual del campo
     *
     * @return string: the value of the field/ valor del campo
     * @access public
     * @author Teye Heimans
     */
    public function getValue()
    {
        if($this->_oHour->getValue() == '' && $this->_oMinute->getValue() == '')
        {
            return '';
        }
        else
        {
        	$this->_mValue = $this->_oHour->getValue().':'.$this->_oMinute->getValue();
            return $this->_mValue;
        }
    }


    /**
     * TimeField::getField()
     *
     * Return the HTML of the field 
     * Devuelve el HTML del campo
     *
     * @return string: the html of the field/ html del campo
     * @access public
     * @author Teye Heimans
     */
    public function getField()
    {
        // view mode enabled ?/ modo vista habilitado?
        if( $this -> getViewMode() )
        {
            // get the view value../ obtenga el valor de la vista
            return $this -> _getViewValue();
        }


    	// set the currect time if wanted/ si se quiere establecer la hora actual 
        if( !$this->_oForm->isPosted() &&
            (!isset($this->_oForm->edit) || !$this->_oForm->edit) &&
            $this->_bRequired &&
            $this->getValue() == '' &&
            FH_TIMEFIELD_SET_CUR_TIME)
        {
        	$this->setValue( date('H').':'.date('i') );
        }

        // generate the hour options/ generar las opciones de las horas 
        $aHours = array();
        if(!$this->_bRequired)
        {
            $aHours[''] = '';
        }
        for($i = 0; $i <= ($this->_iFormat-1); $i++ )
        {
            $aHours[sprintf('%02d', $i)] = sprintf('%02d', $i);
        }

        // generate the minutes options/ generar las opciones de los minutos
        $aMinutes = array();
        if(!$this->_bRequired)
        {
            $aMinutes[''] = '';
        }
        $i = 0;
        while($i <= 59)
        {
            $aMinutes[sprintf("%02d", $i)] = sprintf("%02d", $i);
            $i += FH_TIMEFIELD_MINUTE_STEPS;
        }

        // set the options/ establece las opciones
        $this->_oHour->setOptions  ( $aHours );
        $this->_oMinute->setOptions( $aMinutes );

        // make sure that the minutes option can be displayed
        // asegurese que la opcion de minutos pueda ser mostrada
        if( $this -> _bRequired ||  $this -> getValue() != "" )
        {
            $this->_oHour->_mValue += $this->_getNearestMinute( $this->_oMinute->_mValue );
            if($this->_oHour->_mValue == 24) $this->_oHour->_mValue = 0;
        }

        //debug
        //print_Var( $this -> _mValue, $this->_oHour->_mValue, $this->_oMinute->_mValue );

        // return the fields/ devuelve los campos
        return
          $this->_oHour->getField() . " : " .
          $this->_oMinute->getField().
          (isset($this->_sExtraAfter) ? $this->_sExtraAfter :'');
    }

    /**
     * TimeField::_getNearestMinute()
     *
     * Get the nearest minute in the minutes list
     * Obtener el minuto más cercano en la lista de minutos
     * 
     * @param int $minute
     * @return int: 1 or 0 if the hour should be increased/ 1 o 0 si se debe aumentar la hora
     * @access private
     * @author Teye Heimans
     */
    private function _getNearestMinute( &$minute )
    {
        // get the nearest value at the minutes...
        // obtenga el valor más cercano en los minutos...
    	for($i = 0; $i < $minute; $i += FH_TIMEFIELD_MINUTE_STEPS);

    	$i = abs( $minute - $i ) < abs( $minute - ($i - FH_TIMEFIELD_MINUTE_STEPS)) ?
    	$i : ($i - FH_TIMEFIELD_MINUTE_STEPS);

    	$minute = $i;

    	if($minute == 60)
    	{
    	    $minute = 0;
    	    return 1;
    	}
    	else
    	{
    	    return 0;
    	}
    }
}