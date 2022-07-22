<?php
/**
 * class Editor
 *
 * Create a Editor on the given form with CKEDitor 4.3.1
 * Crea un editor en el formulario dado con CKEDitor 4.3.1
 *
 * @author Johan Wiegel
 * @package FormHandler
 * @subpackage Fields
 * @since 2013-12-17
 */

class Editor extends TextArea
{
	public function __construct( $oForm, $sName )
	{
		parent::__construct( $oForm, $sName );	
		
		static $bSetJS = false;

		// needed javascript included yet ?/ javascript necesarios ya estan incluidos?
		if(!$bSetJS)
		{

			$bSetJS = true;
				$oForm->_setJS(
					FH_FHTML_DIR."ckeditor/ckeditor.js", true
				);
		}

		$this->_oEditor = new stdClass( $sName );
		$this->_oEditor->basePath = FH_FHTML_DIR . 'ckeditor/';
        $this->_oEditor->Value = isset( $this->_mValue ) ? $this->_mValue : '';

        $this->setToolbar( 'Default' ); // Default or Basic
        $this->setServerPath( '' );
		
        // set the language/ establezca el lenguaje
        $this->_oEditor->config['language']  = str_replace('-utf8', '', $oForm->GetLang());        

		// default height & width/ alto y ancho por defecto
        $this->setWidth ( 720 );
        $this->setHeight( 400 );

        // moono
        $this->setSkin( 'moono' );

		
	}
	
    /**
     * Editor::setHeight()
     *
     * Set the height of the editor (in pixels!)
     * Establezca la altura del editor (en pixeles!)
     *
     * @param integer $iHeight: the height / la altura
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setHeight( $iHeight )
    {
        $this->_oEditor->config['height'] = $iHeight;
    }	
	
    /**
     * Editor::setValue()
     *
     * Set the value of the field
     * Establezca el valor del campo
     *
     * @param string $sValue: The html to set into the field/ Html para establecer en el campo
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setValue( $sValue )
    {
    	$this->_mValue = $sValue;
    }


    /**
     * Editor::setWidth()
     *
     * Set the width of the editor  (in pixels!)
     * Establezca el ancho del editor (en pixeles!)
     *
     * @param integer $iWidth: the width / el ancho
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setWidth( $iWidth)
    {
        $this->_oEditor->config['width'] = $iWidth;
    }

    /**
     * Editor::setToolbar()
     *
     * Set the toolbar we should use for the editor
     * Establezca la barra de herramientas que debemos usar para el editor
     * 
     * @param string $sToolbar: The toolbar we should use
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setToolbar( $sToolbar )
    {
        $this->_oEditor->config['toolbar'] = $sToolbar;
    }


    /**
     * Editor::setConfig()
     *
     * Set extra config options for the editor
     * Establezca opciones de configuracion extras para el editor
     * @param array $config: The config array with extra config options to set for the fckeditor/ La matriz de configuración con opciones de configuración extras para establecer para el fckeditor
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setConfig( $config )
    {
        $this->_oEditor->config = array_merge( $this->_oEditor->config, $config );
    }

    /**
     * Editor::setServerPath()
     *
     * Set the server path used for browsing and uploading images
     * Establezca la ruta del servidor utilizada para navegar y cargar imágenes
     * 
     * @param string $sPath: The path/ la ruta
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setServerPath( $sPath )
    {
        if( $sPath === false )
        {
            $this->_oEditor->Config['filebrowserBrowseUrl']  = false;
            $this->_oEditor->Config['filebrowserUploadUrl'] = false;
            return;
        }

    	// get the dir where the script is located in
        // obtener el directorio donde se encuentra el script
    	$sSelfPath = $_SERVER['PHP_SELF'] ;
	    $sSelfPath = substr( $sSelfPath, 0, strrpos( $sSelfPath, '/' ) ) ;

	    // get the dir where the user want's to upload the dir in
        // obtener el directorio donde el usuario quiere cargar el directorio en
	    $sPath = $this->_getServerPath( $sPath, $sSelfPath );

        // path (URL) to the FCKeditor...
        // ruta (URL) al FCKeditor...
        $char = substr(FH_FHTML_DIR, 0, 1);
        $pre  = ($char != '/' && $char != '\\' && strtolower(substr(FH_FHTML_DIR, 0, 4)) != 'http') ? str_replace('//', '/', dirname( $_SERVER['PHP_SELF'] ).'/') : '';

        $sURL =
          $pre . FH_FHTML_DIR .
          'filemanager/browser/default/browser.html?'.
          'Type=%s&Connector=../../connectors/php/connector.php?ServerPath='.$sPath
        ;

        $this->_oEditor->config['filebrowserBrowseUrl']  = ( sprintf( $sURL, 'File', $sPath ) );
        $this->_oEditor->config['filebrowserUploadUrl']  = ( sprintf( $sURL, 'File', $sPath ) );
    }


    /**
     * Editor::setSkin()
     *
     * Set the skin used for the FCKeditor
     * Establezca la mascara utilizada para el FCKeditor
     *
     * @param string $skin
     * @return void
     * @access public
     * @author Teye Heimans
     */
    public function setSkin( $sSkin )
    {
    	$this->_oEditor->config['skin'] = $sSkin;
    }        
	
    /**
     * Editor::_getServerPath()
     *
     * Get the dir which should be used for browsing...
     * Obtenga el directorio que debe usarse para navegar...
     * 
     * @param string $sDir: The dir given by the user/ direccion dada por el usuario
     * @param string $sServerPath: The dir where the script is located on the server/ direccion donde se encuentra el script en el servidor
     * @return void
     * @access private
     * @author Teye Heimans
     */
    private function _getServerPath( $sDir, $sServerPath )
    {
    	// remove ending slash at the server path/ eliminar el slash final en la ruta del servidor
    	if( substr($sServerPath, -1) == '/' )
    	{
			$sServerPath = substr( $sServerPath, 0, -1);
		}
		// when no dir is given, just return the path where the script is located
        // cuando no se proporciona ningún directorio, simplemente devuelva la ruta donde se encuentra el script
		if( $sDir == '' )
		{
			return $sServerPath;
		}

		// dir starting with a /? Then start at the root...
        // el directorio que comienza con /? Entonces empieza por la raíz...
		if( $sDir[0] == '/' )
		{
			return $sDir;
		}
		// dir starting with ./? Then relative from the dir where the script is located
        // dir comenzando con ./? Entonces es relativo al directorio donde se encuentra el script
		else if( substr( $sDir, 0, 2) == './' )
		{
			return $sServerPath.'/'.substr($sDir, 2);
		}
		// if we are at the root of the server, return the dir..
        // si estamos en la raíz del servidor, devuelve el directorio..
		else if( $sServerPath == '/' || $sServerPath == '')
		{
			if( $sDir[0] != '.' && $sDir[0] != '/' )
			{
				 $sDir = '/'.$sDir;
			}
			return $sDir;
		}
		// go a dir lower.../ir un directorio más abajo...
		else if( substr($sDir, 0, 3) == '../' )
		{
    		$sServerPath = substr($sServerPath, 0, -strlen( strrchr($sServerPath, "/") ));

    		return $this->_getServerPath( substr($sDir, 3), $sServerPath);
    	}
    	// none of the above, then return the dir!
        //ninguna de las anteriores, entonces devuelve el directorio!
    	else
    	{
    		if( $sDir[0] == '/' )
    		{
    			$sDir = substr( $sDir, 1);
    		}
    		return $sServerPath.'/'.$sDir;
    	}
    }    
    
	/**
     * Editor::getField()
     *
     * return the field/ devuelve el campo
     *
     * @return string: the field
     * @author Teye Heimans
     * @access public
     */
	public function getField()
	{
		// view mode enabled ?/ modo vista esta habilitado?
		if( $this -> getViewMode() )
		{
			// get the view value.. obtener el valor de la vista
			return $this -> _getViewValue();
		}

		$html = parent::getField();
	
		// add the javascript needed for the js calendar field
        // agregue el javascript necesario para el campo de calendario js
		$this -> _oForm -> _setJS( 
		"
			CKEDITOR.replace( '".$this->_sName."', ".json_encode( $this->_oEditor->config )." );		
		", 0, 0 );
				
		return $html;
	}	
}