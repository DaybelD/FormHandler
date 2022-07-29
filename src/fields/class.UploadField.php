<?php

/**
 * class UploadField
 *
 * File uploads handler class
 * Clase de controlador de carga de archivos
 * 
 * @author Teye Heimans
 * @package FormHandler
 * @subpackage Fields
 * @since 19-03-2008 J.Wiegel Fixed CHMOD bug
 */
class UploadField extends Field
{
	private $_aConfig;           // array: which contains the default upload config/ que contiene la configuracion de carga predeterminada
	private $_bAlertOverwrite;   // boolean: display a message when the file already exists/ muestra un mensaje cuando el archivo ya existe
	private $_sFilename;         // string: filename of the file/ nombre del archivo

	/**
     * UploadField::UploadField()
     *
     * Constructor: Create a new UploadField object/ crea un nuevo objeto de campo de carga
     *
     * @param object &$oForm: The form where this field is located on/ formulario donde se encuentra el campo
     * @param string $sName: The name of the field/ nombre del campo
     * @param array $aConfig: The config array/ La matriz de configuracion
     * @return UploadField
     * @access public
     * @author Teye Heimans
     */
	public function __construct( &$oForm, $sName, $aConfig )
	{
		require_once(FH_INCLUDE_DIR.'includes/mimeTypes.php');
		static $bSetJS = false;

		// needed javascript included yet ?/ el javascript necesario ya esta incluido?
		if(!$bSetJS)
		{
			// include the needed javascript/ incluye el javascript necesario
			$bSetJS = true;
			$oForm->_setJS(FH_FHTML_DIR."js/extension_check.js", true);
		}
		
		$this->setClass('');
		$this->_bAlertOverwrite = true;

		// check if there are spaces in the fieldname
		// comprueba si hay espacios en el nombre del archivo
		if(strpos($sName,' ') !== false)
		{
			trigger_error('Warning: There are spaces in the field name "'.$sName.'"!', E_USER_WARNING );
		}

		// set the config file/ establece el archivo de configuracion
		$aDefault = unserialize( FH_DEFAULT_UPLOAD_CONFIG );
		$this->_aConfig = array_merge( $aDefault, $aConfig );

		// add slash to the end of the pathname if it does not have already
		// agregue una barra al final del nombre de la ruta si aún no lo ha hecho
		$l = substr($this->_aConfig['path'], -1);
		if($l != '\\' && $l != '/')
		{
			$this->_aConfig['path'] .= '/';
		}

		// if no size is given, get the max uploadsize
		// si no se proporciona el tamaño, obtenga el tamaño máximo de carga
		if( empty($this->_aConfig['size']) )
		{
			$this->_aConfig['size'] = $this->_getMaxUploadSize();
		}

		// make the mime types given by the user useable
		// hacer utilizables los tipos mime proporcionados por el usuario
		if( is_string( $this->_aConfig['mime'] ) )
		{
			$mime = explode(' ', $this->_aConfig['mime'] );
			$this->_aConfig['mime'] = array(
			'*' => $mime
			);
			unset( $mime );
		}
		// mime types are given as an array
		// los tipo mime se dan como una matriz
		else
		{
			foreach ( $this->_aConfig['mime'] as $key => $value )
			{
				if( is_numeric( $key ) )
				{
					$this->_aConfig['mime']['*'][] = $value;
					unset( $this->_aConfig['mime'][$key] );
				}
				else
				{
					if( is_string( $value ) )
					{
						$this->_aConfig['mime'][$key] = explode(' ', $value);
					}
				}
			}
		}

		$this->_oForm = $oForm;
		$this->_sName = $sName;

		// get the value of the field/ obtenga el valor del campo
		if( $oForm->isPosted() )
		{
			// make sure that the $_FILES and $_POST array are global
			// asegurese de que la matriz $_FILES y $_POST sean globales
			if(!_global) global $_FILES, $_POST;

			// get the value if it exists in the $_FILES array
			// obtenga el valor si este existe en la matriz $_FILES
			if( isset( $_FILES[$sName] ) )
			{
				// detect error type if php version is older then 4.2.0 (make the errors the same)
				// detecta el tipo de error si la versión de php es anterior a la 4.2.0 (haz que los errores sean iguales)
				if($_FILES[$sName]['tmp_name'] == 'none' && empty($_FILES[$sName]['name']))
				{
					// nothing uploaded/ nada ha sido cargado
					$_FILES[$sName]['error'] = 4;
				}
				elseif($_FILES[$sName]['tmp_name'] == 'none' && !empty($_FILES[$sName]['name']))
				{
					// file is bigger then given in MAX_FILE_SIZE.
					// el archivo es mas grande que el tamaño dado en MAX_FILE_SIZE.
					$_FILES[$sName]['error'] = 2;
				}
				elseif(!isset($_FILES[$sName]['error']))
				{
					// no error occured/ no ocurrio ningun error
					$_FILES[$sName]['error'] = 0;
				}

				// save the uploaded file data
				// guarda los datos del archivo cargado
				$this->_mValue = $_FILES[$sName];

				// check if there is a file uploaded...
				// comprueba si hay un archivo cargado..
				if( $this->isUploaded() )
				{
					$this->setValue( $this->_getFilename( ) );
				}
				else
				{
					// if this is an edit form and no value is given to the field, keep the existing value thats in the database
					// si este es un formulario de edicion y no se le da un valor al campo, mantenga el valor existente en la base de datos
					if( isset( $oForm->edit) && $oForm->edit && isset( $oForm->_dbData[$sName] ) )
					{
						$this->setValue( $oForm->_dbData[$sName] );
					}
				}
			}
			// posted value known? (when using multiple pages)
			// valor enviado conocido? (cuando se usan multiples paginas)
			elseif ( isset( $_POST[$sName] ) )
			{
				$this->setValue( $_POST[$sName] );
			}
			// edit form?/ formulario de edicion?
			elseif( (isset($oForm->edit) && $oForm->edit) )
			{
				// value known from the database?
				// valor conocido de la base de datos?
				if( isset( $oForm->_dbData[$sName] ) )
				{
					$this->setValue( $oForm->_dbData[$sName] );
				}
			}
		}
		// load database value if exists
		// cargue el valor de la base de datos si existe
		elseif( (isset($oForm->edit) && $oForm->edit) )
		{
			if( isset( $oForm->_dbData[$sName] ) )
			{
				$this->setValue( $oForm->_dbData[$sName] );
			}
		}

		// if no file is uploaded and it is a edit form, dont overwrite the current value
		// si no se carga ningún archivo y es un formulario de edición, no sobrescriba el valor actual
		if(!$this->isUploaded() && (isset($oForm->edit) && $oForm->edit) && $oForm->isPosted() )
		{
			$this->_oForm->_dontSave[] = $sName;
		}

		// check if the user got another value for this field.
		// comprueba si el usuario tiene otro valor para este campo.
		if( isset($oForm ->_buffer[ $sName ] ) )
		{
			list( $bOverwrite, $sValue ) = $oForm->_buffer[ $sName ];

			// if the field does not exists in the database
			// si el campo no existe en la base de datos
			if($bOverwrite || !isset($oForm->_dbData[$sName]) )
			{
				$this->setValue( $sValue );
			}

			// remove the value from the buffer..
			// elimine el valor del buffer.. 
			unset( $oForm->_buffer[ $sName ] );
		}
	}

	/**
     * UploadField::getFileInfo()
     *
     * Return the data of an uploaded file or an empty array when no file is uploaded
     * Devuelve los datos de un archivo cargado o una matriz vacía cuando no se carga ningún archivo
     *
     * @return array
     * @access public
     * @author Teye Heimans
     */
	public function getFileInfo()
	{
		// file uploaded! return the data!
		// archivo cargado! devuelve los datos!
		if( $this -> isUploaded() )
		{
			return $this -> _mValue;
		}

		// no file uploaded, return empty array
		// no se ha cargado algun archivo/ devuelve la matriz vacia
		return array();
	}

	/**
      * UploadField::setValue()
      *
      * Set the value of the field (the filename of the uploaded file) 
      * Establece el valor del campo (el nombre del archivo cargado)
      *
      * @param string $sFilename: The filename of the value/ nombre de archivo del valor
      * @return void
      * @access public
      * @author Teye Heimans
      */
	public function setValue( $sFilename )
	{
		$this->_sFilename = $sFilename;
	}

	/**
     * UploadField::getSavePath()
     *
     * Return the path were we are going to save the file  
     * Devuelve la ruta donde vamos a guardar el archivo.
     *
     * @return string: the path where the file is saved/ la ruta donde se guarda el archivo
     * @access public
     * @author Teye Heimans
     */
	public function getSavePath()
	{
		return $this->_aConfig['path'];
	}

	/**
     * UploadField::getValue()
     *
     * Return the current value  
     * Devuelve el valor actual
     *
     * @return string: the current file/ archivo actual
     * @access public
     * @author Teye Heimans
     */
	public function getValue()
	{
		return isset($this->_sFilename) ? $this->_sFilename : '';
	}

	/**
     * UploadField::_getViewValue()
     *
     * Return the value as link to the file  
     * Devuelve es valor como un enlace al archivo
     * 
     * @return string
     * @access public
     * @author Remco van Arkelen
     * @since 19-11-2008
     */
	public function _getViewValue( )
	{
		return '<a href="'. $this->getSavePath(). $this->getValue() .'" target="_blank">'. $this->getValue() .'</a>';
	}

	/**
     * UploadField::isUploaded()
     *
     * Check if there is a file uploaded or not
     * Compruebe si hay un archivo cargado o no
     *
     * @return boolean
     * @access public
     * @author Teye Heimans
     */
	public function isUploaded()
	{
		if(!_global) global $_FILES;

		return (
		$this->_oForm->isPosted() && # form is posted
		isset( $_FILES[$this->_sName] ) && # file known in the $_FILES array
		$this->_mValue['error'] != 4 # is there a file uploaded ?
		);
	}

	/**
     * UploadField::getField()
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
			// get the view value../ obtenga el modo vista
			return $this -> _getViewValue();
		}

		// alert the user if they have to upload the file again or when they are going to overwrite a file
		if($this->_bAlertOverwrite && !empty($this->_sFilename) && empty($this->_sError))
		{
			// edit form and the field got a value ?
			if( (isset($this->_oForm->edit) && $this->_oForm->edit) && !empty($this->_sFilename) )
			//isset( $this->_oForm->_dbData[$this->_sName] ) )
			{
				// display "overwrite" message
				$sMsg = str_replace(
				array('%path%', '%filename%'),
				array(
				str_replace((isset($_SERVER['DOCUMENT_ROOT'])?$_SERVER['DOCUMENT_ROOT']:''), '', $this->_aConfig['path']),
				(!empty($this->_mValue['name']) ? $this->_mValue['name'] :$this->_sFilename)
				),
				$this->_oForm->_text( 19 )
				);
			}
			// Correct ?
			else if( $this->_oForm->isPosted() && !$this->_oForm->isCorrect() )
			{
				$sMsg = str_replace('%filename%', (!empty($this->_mValue['name']) ? $this->_mValue['name'] :$this->_sFilename), $this->_oForm->_text( 33 ));
			}

			// set the massage as it is an error message
			if( !empty($sMsg) )
			{
				$this->_sError = $sMsg;
			}
		}

		// set the javascript upload checker if wanted
		if(FH_UPLOAD_JS_CHECK && $this->_aConfig['type'] != '*')
		{
			// check if the user has also set an onchange event
			$aMatch = array();
			if(isset($this->_sExtra) && preg_match("/onchange *= *('|\")(.*)$/i", $this->_sExtra, $aMatch))
			{
				// put the function into a onchange tag if set
				$sStr = str_replace($aMatch[1], ( ($aMatch[1]=="'") ? '"' : "'" ), "fh_checkUpload(this, '".$this->_aConfig['type']."', '".$this->_oForm->_text( 20 )."');");
				$this->_sExtra = preg_replace("/onchange *= *('|\")(.*)$/i", "onchange=\\1$sStr\\2", $this->_sExtra);
			}
			// the user did not define an onchange event, just add it to the extra argument
			else
			{
				$this->_sExtra = "onchange=\"fh_checkUpload(this, '".$this->_aConfig['type']."', '".$this->_oForm->_text( 20 )."')\" " . ( !empty($this->_sExtra) ? $this->_sExtra : "");
			}
		}

		// return the field
		return sprintf(
		'<input type="file" name="%s" id="%1$s" class="%s" %s'. FH_XHTML_CLOSE .'>%s',
		$this->_sName,
		$this->_iClass,
		(isset($this->_iTabIndex) ? 'tabindex="'.$this->_iTabIndex.'" ' : '').
		(isset($this->_sExtra) ? $this->_sExtra.' ':''),
		(isset($this->_sExtraAfter) ? $this->_sExtraAfter :'')
		);
	}

	/**
     * UploadField::setAlertOverwrite()
     *
     * Set if we have to alert when the file already exists
     *
     * @param boolean $bStatus: The status to set
     * @return void
     * @access public
     * @author Teye Heimans
     */
	public function setAlertOverwrite( $bStatus )
	{
		$this->_bAlertOverwrite = $bStatus;
	}


	public function setClass( $class )
	{
		$this->_iClass ='form-control '. $class;
	}

	/**
     * UploadField::isValid()
     *
     * Check if the value is valid
     *
     * @return bool: check if the value is valid
     * @access public
     * @author Teye Heimans
     */
	public function isValid()
	{
		// make the files array global if they are not
		if(!_global) global $_FILES;

		/**
         * Removed this part in order to get the required parameter working. 
         * @since 02-04-2008
         * @author Johan Wiegel
         * 
         * reactivated this part seems to work why was this removed?? 29-04-2009 JW
         */
		// when no uploadfield was submitted (on multi-paged forms)
		if( !isset( $_FILES[$this->_sName] ) )
		{
			return true;
		}

		// check if we have validated this field before
		if( isset( $this->_isValid ) )
		{
			return $this->_isValid;
		}

		// is a own error handler used?
		if(isset($this->_sValidator) && !empty($this->_sValidator) )
		{
			// check the field with the users validator
			$this->_isValid = Field::isValid();
			return $this->_isValid;
		}

		// easy name to work with (this is the $_FILES['xxx'] array )
		$aFile = $this->_mValue;

		// alert when file exists ? (and is a file uploaded ?)
		if( strtolower( $this->_aConfig['exists'] ) == 'alert' &&  # do we have to alert the user ?
		$aFile['error'] == 0 && # check if the file is succesfully uploaded
		file_exists( $this->_aConfig['path'] . $this->_getFilename(true) ) ) # check if the file exists
		{
			$this->_sError = $this->_oForm->_text( 21 );
		}

		// check if the field is required and if it is uploaded.
		if(( ( is_bool( $this->_aConfig['required'] ) && $this->_aConfig['required'] ) ||
		strtolower(trim($this->_aConfig['required'])) == 'true'
		) &&
		$aFile['error'] == 4 &&
		empty($this->_sFilename) )
		{
			// no file uploaded
			$this->_sError = $this->_oForm->_text( 22 );
		}

		// when a file is uploaded...
		elseif($aFile['error'] != 4)
		{
			// file size to big?
			if( isset($aFile['error']) &&
			($aFile['error'] == 1 ||
			$aFile['error'] == 2 ||
			$aFile['size'] > $this->_aConfig['size']))
			{
				$this->_sError = sprintf( $this->_oForm->_text( 23 ), round($this->_aConfig['size'] / 1024, 2) );
				$this->_isValid = false;
				return false;
			}

			// is the extension correct ?
			$sExt = $this->_getExtension( $this->_mValue['name'] );
			if( !$sExt )
			{
				// no extension found!
				$this->_sError = $this->_oForm->_text( 37 );
			}
			// extension is known..
			else
			{
				// check if the extension is allowed
				if($this->_aConfig['type'] != '*' && !in_array( $sExt , explode(' ', strtolower($this->_aConfig['type']))))
				{
					$this->_sError = sprintf( $this->_oForm->_text( 20 ), $this->_aConfig['type'] );
				}

				// does build in function exists for retrieving the mime type of the file?
				// if so, get the type of the mime from that function (more secure)
				// otherwise, use the one from the browser
				// as of php 5.3 mime_content_type is deprecated, use finfo_open instead
				if( function_exists( 'finfo_open' ) )
				{
					$finfo = finfo_open( FILEINFO_MIME );
					$sTypeRaw = finfo_file( $finfo,$aFile['tmp_name'] );
					finfo_close( $finfo );
					list( $sType ) = preg_split( '/;/', $sTypeRaw );
				}
				else
				{
					$sType = function_exists('mime_content_type') ? mime_content_type( $aFile['tmp_name'] ) : $aFile['type'];
				}


				// get the mime data
				$aMimeData = unserialize( FH_MIME_DATA );

				// the allowed mime types given by the user
				$aUsrMimeData = $this->_aConfig['mime'];


				# Debug message
				/*
				echo
				"<fieldset><legend><b>Debug Mime data</b></legend>\n".
				"Extension of the uploaded file: ".$sExt ."<br '. FH_XHTML_CLOSE .'>\n".
				"Mime type of uploaded file: ". $sType ."<br '. FH_XHTML_CLOSE .'>\n".
				"Extension known: ".(isset( $aMimeData[$sExt] ) ? "true":"false")."<br '. FH_XHTML_CLOSE .'>\n".
				(isset( $aMimeData[$sExt] ) ? "Mime type(s) expected: " . implode(", ", $aMimeData[$sExt]) : '')."<br '. FH_XHTML_CLOSE .'>\n".
				"User allowed mime types:<br'. FH_XHTML_CLOSE .'>\n<pre>";
				print_r( $aUsrMimeData );
				echo
				"</pre>\n".
				"</fieldset>\n";
				*/

				// check if the mime type is allowed
				// wo 8 feb 2006: added "!isset( $aUsrMimeData['*'] ) || "
				if( ( !isset( $aUsrMimeData[$sExt] ) || !in_array( $sType, $aUsrMimeData[$sExt] ) ) &&
				( !isset( $aUsrMimeData['*'] ) || !in_array( $sType, $aUsrMimeData['*'] ) ) &&
				( !isset( $aMimeData[$sExt] ) || !in_array($sType, $aMimeData[$sExt] ) ) )
				{
					// mime type is not allowed!
					$this->_sError = $this->_oForm->_text( 31 );
				}
				else
				{
					// is it an image and is a max width/height given? Get the proportions
					if(preg_match('/^image\//', $sType) &&
					!empty($this->_aConfig['height']) ||
					!empty($this->_aConfig['width']))
					{
						// size is incorrect.. give a size error message
						list($iWidth, $iHeight) = getimagesize( $aFile['tmp_name'] );
						if(( (int) $this->_aConfig['height'] > 0 && $iHeight > (int) $this->_aConfig['height'] ) ||
						( (int) $this->_aConfig['width']  > 0 && $iWidth  > (int) $this->_aConfig['width']))
						{
							$this->_sError = sprintf(
							$this->_oForm->_text( 32 ),
							(int) $this->_aConfig['width'],
							(int) $this->_aConfig['height'],
							$iWidth,
							$iHeight
							);
						}
					}
				}

				// if an error occured..
				if($aFile['error'] == 3)
				{
					$this->_sError = $this->_oForm->_text( 24 );
				}
			}
		}

		// when no error ocoured, the file is valid
		$this->_isValid = empty($this->_sError);
		return $this->_isValid;
	}

	/**
     * UploadField::doUpload()
     *
     * Upload the file
     *
     * @return string | bool: the filename of the uploaded file, or false on an error
     * @access public
     * @author Teye Heimans
     */
	public function doUpload()
	{
		// alias for the file data
		$aFile = $this->_mValue;

		// is a file uploaded ?
		if( !is_null($aFile['error']) && $aFile['error'] != 4)
		{
			if( is_uploaded_file($aFile['tmp_name']) )
			{
				// make the dir if not exists
				if(!is_dir($this->_aConfig['path']))
				{
					if(!$this->_forceDir($this->_aConfig['path'], FH_DEFAULT_CHMOD))
					{
						trigger_error(
						"Could not upload file to dir: ".$this->_aConfig['path'].". ".
						"The dir does not exists and trying to make the dir failed...",
						E_USER_WARNING
						);
						return false;
					}
				}

				// the file like where going to upload it
				$sFilename = $this->_getFilename(false);
				$sUpload   = $this->_aConfig['path'].$sFilename;

				// uploading
				if(!move_uploaded_file($aFile['tmp_name'], $sUpload))
				{
					trigger_error(
					sprintf(
					'Unable to move uploaded file %s to location %s',
					$aFile['tmp_name'],
					$sUpload
					),
					E_USER_ERROR
					);
					return false;
				}
				@chmod( $sUpload, FH_DEFAULT_CHMOD );

				$this->_sFilename = $sFilename;

				return $sUpload;
			}
			else
			{
				trigger_error(
				sprintf(
				'Possible file upload attack: filename %s',
				$aFile['name']
				),
				E_USER_WARNING
				);
				return false;
			}
		}
		return false;
	}

	/******* PRIVATE! *************/

	/**
     * UploadField::_forceDir()
     *
     * Create the given dir
     *
     * @param string $sPath: the path to create
     * @param int $mode: the chmode which should be used to create the dir
     * @return boolean
     * @access private
     * @author Teye Heimans
     */
	private function _forceDir( $sPath, $iMode)
	{
		if ( strlen( $sPath) == 0)
		{
			return 0;
		}
		if ( strlen( $sPath) < 3)
		{
			return 1; // avoid 'xyz:\' problem.
		}
		elseif ( is_dir( $sPath ))
		{
			return 1; // avoid 'xyz:\' problem.
		}
		elseif   ( dirname( $sPath) == $sPath )
		{
			return 1; // avoid 'xyz:\' problem.
		}

		return ( $this->_forceDir( dirname($sPath), $sPath) and mkdir( $sPath, $iMode));
	}

	/**
     * UploadField::_getFilename()
     *
     * Get the filename like we are going to save it  
     * Obtener el nombre de archivo como lo vamos a guardar
     *
     * @param boolean $bIgnoreRename: Ignore the rename option?/ ignorar la opcion de renombrar?
     * @return string: the filename/ nombre de archivo
     * @access private
     * @author Teye Heimans
     */
	private function _getFilename( $bIgnoreRename = false )
	{
		// easy name to work with/ un nombre facil que funcione con
		$sFile = $this->_mValue['name'];

		// get the extension of the uploaded file/ obtenga la extension del archivo cargado
		$sExt = $this->_getExtension( $sFile );
		if( !$sExt )
		{
			return null;
		}

		// use the given filename if wanted/ use el nombre de archivo dado si se quiere
		if(!empty($this->_aConfig['name']))
		{
			$sFile = $this->_aConfig['name'].'.'.$sExt;
		}

		// replace not wanted caracters/ reemplace los caracteres no deseados
		$sFile = preg_replace('/\.'.$sExt.'$/i', '', $sFile);  // remove extension
		$sFile = preg_replace("{^[\\/\*\?\:\,]+$}", '', $sFile ); // remove dangerous characters

		// rename when wanted/ renombrar cuando se quiera
		if(strtolower($this->_aConfig['exists']) == 'rename' && !$bIgnoreRename)
		{
			$sPath = $this->_aConfig['path'];

			$sCopy = '';
			$i = 1;
			while (
			// file exists or .../ el archivo existe o no
			file_exists($sPath.$sFile.$sCopy.'.'.$sExt) ||
			// other uploadfield has registered this filename ...
			// otro campo de carga ha registrado este nombre de archivo...
			!$this->_oForm->_registerFileName( $sPath.$sFile.$sCopy.'.'.$sExt, $this->_sName))
			{
				// then get a new filename/ entonces obtenga un nuevo nombre de archivo
				$sCopy = '('.$i++ .')';
			}
			return $sFile.$sCopy.'.'.$sExt;
		}
		// no renaming wanted../ no se quiere renombrar..
		else
		{
			return $sFile.'.'.$sExt;
		}
	}

	/**
     * UploadField::_getExtension()
     *
     * Retrieve the extension of the given filename
     * Recuperar la extensión del nombre de archivo dado 
     * 
     * @param string $sFilename: The filename where we have to retrieve the extension from/ El nombre del archivo del que tenemos que recuperar la extensión.
     * @return string: the extension
     * @access private
     * @author Teye Heimans
     */
	private function _getExtension( $sFilename )
	{
		$sExt = substr(strrchr( $sFilename, '.'), 1);

		return ($sExt !== false) ? strtolower($sExt) : false;
	}

	/**
     * UploadField::_getMaxUploadSize()
     *
     * Get the max uploadsize
     * Obtenga el tamaño de carga maximo 
     * 
     * @return integer: the max upload size/ tamaño de carga maximo
     * @access private
     * @author Teye Heimans
     */
	private function _getMaxUploadSize()
	{
		static $iIniSize = false;

		if(!$iIniSize)
		{
			$iPost = intval($this->_iniSizeToBytes(ini_get('post_max_size')));
			$iUpl  = intval($this->_iniSizeToBytes(ini_get('upload_max_filesize')));
			$iIniSize = floor(($iPost < $iUpl) ? $iPost : $iUpl);
		}
		return $iIniSize;
	}

	/**
     * UploadField::_iniSizeToBytes()
     *
     * Get the given size in bytes  
     * Obtenga el tamaño dado en bytes
     *
     * @param string $sIniSize: The size we have to make to bytes/ El tamaño que tenemos que pasar a bytes.
     * @return integer: the size in bytes
     * @access private
     * @author Teye Heimans
     */
	private function _iniSizeToBytes( $sIniSize )
	{
		$aIniParts = array();
		if (!is_string($sIniSize))
		{
			trigger_error('Argument A is not a string! dump: '.$sIniSize, E_USER_NOTICE);
			return false;
		}
		if (!preg_match ('/^(\d+)([bkm]*)$/i', $sIniSize, $aIniParts))
		{
			trigger_error('Argument A is not a valid php.ini size! dump: '.$sIniSize, E_USER_NOTICE);
			return false;
		}

		$iSize = $aIniParts[1];
		$sUnit = strtolower($aIniParts[2]);

		switch($sUnit)
		{
			case 'm':
				return (int)($iSize * 1048576);
			case 'k':
				return (int)($iSize * 1024);
			case 'b':
			default:
				return (int)$iSize;
		}
	}
}