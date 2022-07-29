<?php
/**
* class SelectField
*
* Create a SelectField
* Crea un campo de seleccion
*
* @author Teye Heimans
* @package FormHandler
* @subpackage Fields
*/
class SelectField extends Field
{
	public $_aOptions;              // array: the options of the selectfield/ opciones para el campo de seleccion
	public $_bUseArrayKeyAsValue;   // boolean: if the keys of the array should be used as values/ si las claves de la matriz deben usarse como valores
	private $_iClass;                 // string: class associated with the field/ clase asociada al campo
	private $_bMultiple;             // boolean: can multiple items be selected or not?/ se pueden seleccionar varias opciones o no?
	private $_classOpt;
	/**
     * SelectField::SelectField()
     *
     * Public constructor: Create a selectfield object/ Crea un objeto de campo de seleccion
     *
     * @param object $oForm: The form where the field is located on/ formulario donde se encuentra localizado
     * @param string $sName: The name of the form/ nombre del formulario
     * @return SelectField
     * @access public
     * @author Teye Heimans
     */
	public function __construct( &$oForm, $sName )
	{
		// call the constructor of the Field class
		// llama al constructor de la clase campo
		parent::__construct( $oForm, $sName );

		$temp=explode('_', $sName);
		if (isset($temp[1])) {
		$this->setClass($temp[1]);	
		}
		else{
		$this->setClass('');
		}
		
		$this->useArrayKeyAsValue( FH_DEFAULT_USEARRAYKEY );
		$this->setMultiple( false );
	}

	/**
     * SelectField::getValue()
     *
     * Return the value of the field
     * Devuelve el valor del campo
     *
     * @return mixed
     * @access public
     * @author Teye Heimans     
     */
	public function getValue()
	{
		// are multiple selects possible?/ seleccion multiple es posible?
		if( $this->_bMultiple )
		{
			// is there a value ?/ hay un valor?
			if( isset( $this->_mValue ) )
			{
				if( is_string( $this->_mValue) )
				{
					return explode(',', $this->_mValue );
				}
			}
			else
			{
				return array();
			}
		}

		return parent::getValue();
	}

	/**
     * SelectField::getField()
     *
     * Public: return the HTML of the field/ Devuelve el HTML del campo
     *
     * @return string: the html
     * @access public
     * @author Teye Heimans
	 * @since 12-08-2008 Altered by Johan Wiegel, repaired valid html </optgroup> thanks to Roland van Wanrooy
     */
	public function getField()
	{
		// view mode enabled ?/ modo vista habilitado?
		if( $this -> getViewMode() )
		{
			// get the view value../ obtenga el valor de la vista
			return $this -> _getViewValue();
		}

		// multiple selected items possible?
		// seleccionar varios elementos es posible?
		$aSelected = array();
		if($this->_bMultiple)
		{
			if( isset( $this->_mValue ) )
			{			
				// when there is a value../ cuando hay un valor..
				if( !is_array( $this->_mValue ) )
				{
					// split a string like 1, 4, 6 into an array
					// dividir una cadena como 1, 4, 6 en una matriz
					$aItems = explode(',', $this->_mValue );
					foreach( $aItems as $mItem )
					{
						$aSelected[] = trim( $mItem );
					}
				}
				// the value is an array/ el valor es una matriz
				else
				{
					$aSelected[] = $this->_mValue;
				}
			}
		}
		else if( isset($this->_mValue ) )
		{
			$aSelected[] = $this->_mValue;
		}

		// create the options list/ crea una lista de opciones
		$sOptions = '';

		// added by Roland van Wanrooy: flag to indicate an optgroup, in order to close it properly
		// agregado por Roland van Wanrooy: marca para indicar un optgroup, con el fin de cerrarlo correctamente
		$bOptgroup = false;
		// added by Roland van Wanrooy: string with the close tag
		// agregado por Roland van Wanrooy: cadena con etiqueta cerrada
		$sOGclose = "\t</optgroup>\n";

		foreach ($this->_aOptions as $iKey => $sValue )
		{
			// use the array value as field value if wanted
			// use los valores de matriz como valor del campo si se quiere
			if(!$this->_bUseArrayKeyAsValue) $iKey = $sValue;


			if( strpos($iKey, 'LABEL') )
			{
				// added by Roland van Wanrooy: close the optgroup if there is one
				// agregado por Roland van Wanrooy: cierre el optgroup si hay uno
				$sOptions .= ($bOptgroup ? $sOGclose : '');

				$sOptions .= "\t<optgroup label=\"". $sValue."\">\n";

				// added by Roland van Wanrooy: flag opgroup as true
				// agregado por Roland van Wanrooy: marcar opgroup true
				$bOptgroup = true;
			}
			else
			{
				if( isset( $aSelected[0] ) AND is_array( $aSelected[0] ) ){ $aSelected = $aSelected[0]; }
				$sOptions .= sprintf(
				"\t<option %s value=\"%s\" %s>%s</option>\n",
				isset( $this->_classOpt[$iKey] ) ? $this->_classOpt[$iKey] : '', 
				// added by sid benachenhou for handling styles
				// agregado por sid benachenhou para manejar estilos
				$iKey,
				( in_array( $iKey, $aSelected ) ?' selected="selected"':'' ),
				$sValue
				);
			}
		}

		// when no options are set, set an empty options for XHML compatibility
		// cuando las opciones no estan establecidas, establece opciones vacias para la compatibilidad con XHML
		if( empty($sOptions) )
		{
			$sOptions = "\t<option>&nbsp;</option>\n\t";
		}
		// added by Roland van Wanrooy: Agregado por Roland van Wanrooy
		// $sOptions is not empty, so if there was an <opgroup> then close is properly
		// $sOptions no esta vacio, por lo que si hubo un <opgroup> el cierre es correcto
		else {
			$sOptions .= ($bOptgroup ? $sOGclose : '');
		}

		// return the field/ devuelve el campo
		return sprintf(
		'<select name="%s" id="%s" class="%s"%s>%s</select>%s',
		$this->_sName. ( $this->_bMultiple ? '[]':''),
		$this->_sName,
		$this->_iClass,
		($this->_bMultiple ? ' multiple="multiple"' : '' ).
		(isset($this->_iTabIndex) ? ' tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? ' '.$this->_sExtra :'' ),
		$sOptions,
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
		);
	}

	/**
     * SelectField::setOptions()
     *
     * Set the options of the field
     * Establece las opciones del campo
     *
     * @param array $aOptions: the options for the field/ opciones del campo
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setOptions( $aOptions )
	{
		$this->_aOptions = $aOptions;
	}
	
	// added by sid benachenhou for handling styles
	// agregado por sid benachenhou para manejar estilos
	public function setCOptions( $_classOpt )
	{
		$this->_classOpt = $_classOpt;
	}
	/**
     * SelectField::setMultiple()
     *
     * Set if multiple items can be selected or not
     * Establece si multiples elementos pueden ser seleccionados o no 
     *
     * @param boolean $bMultiple
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setMultiple( $bMultiple )
	{
		$this->_bMultiple = $bMultiple;
	}

	/**
     * SelectField::setClass()
     *
     * Set the class of the field/ Establece la clase del campo
     *
     * @param integer $iClass: the new class/ la nueva clase
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setClass( $class )
	{
		$this->_iClass = trim('form-select '. $class);
	}

	/**
     * SelectField::useArrayKeyAsValue()
     *
     * Set if the array keys of the options has to be used as values for the field
     * Establezca si las claves de matriz de las opciones deben usarse como valores para el campo
     *
     * @param boolean $bMode: The mode/ modo
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function useArrayKeyAsValue( $bMode )
	{
		$this->_bUseArrayKeyAsValue = $bMode;
	}
}
