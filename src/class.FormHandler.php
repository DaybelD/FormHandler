<?php
/**
 * FormHandler v3.9
 *
 * @package FormHandler
 */

/******* INTERNAL VALIDATION FUNCTIONS / FUNCIONES VALIDADORAS INTERNAS *******/
define('FH_STRING', 'IsString'); // Any string that has no control data (ASCII 0 - 31) but spaces are allowed/ Cualquier cadena que no tenga datos de control (ASCII 0 - 31) pero los espacios estan permitidos
define('FH_ALPHA', 'IsAlpha'); // Only letters a-z and A-Z/ Solo letras a-z y A-Z
define('FH_DIGIT', 'IsDigit'); // Only numbers 0-9/ solo numeros 0-9/ 
define('FH_ALPHA_NUM', 'IsAlphaNum'); // Letters and numbers/ Letras y numeros 
define('FH_INTEGER', 'IsInteger'); // Only numbers 0-9 and as optional - (minus) sign (only at the beginning)/ solo numeros 0-9 y como opcional - (menos) signo (solo al comienzo) 
define('FH_FLOAT', 'IsFloat'); // FH_INTEGER, just with , (comma)/ FH_INTEGER, solo con , (coma)
define('FH_FILENAME', 'IsFilename'); // Valid filename (including periods but not slashes or other not allowed characters)/ Nombre de archivo válido (que incluya puntos pero no barras ni otros caracteres no permitidos) 
define('FH_BOOL', 'IsBool'); // a boolean (TRUE "true" or "1" is not case sensitive. Everything else is FALSE)/ a boolean (TRUE "true" or "1" No distingue entre minusculas y mayuculas. Todo lo demas es FALSE)
define('FH_VARIABLE', 'IsVariabele'); // Valid variable name (letters, digits, underscore)/ Nombre valido de variable (letras, digitos, guion bajo) 
define('FH_PASSWORD', 'IsPassword'); // Valid password (alphanumeric + some other character but without space. Only allowed ASCII 33 - 126)/ Clave valida (alfanumerica + algun otro caracter pero sin espacio. Solo permitido ASCII 33 - 126)
define('FH_URL', 'IsURL'); // a valid URL/ un URL valido 
define('FH_URL_HOST', 'IsURLHost'); //a valid URL (http connection is used to check if a URL exists!)/ una URL válida (¡la conexión http se usa para verificar si existe una URL!)
define('FH_EMAIL', 'IsEmail'); // Valid email address (Just check the valid format: xxx@xxx.xxx)/ Direccion de correo electronico valida (Solo comprueba el formato valido: xxx@xxx.xxx)
define('FH_EMAIL_HOST', 'IsEmailHost'); // FH_EMAIL only with host verification/ FH_EMAIL solo con verificacion de host
define('FH_TEXT', 'IsText'); // FH_STRING, but newline characters are allowed/ FH_STRING, pero caracteres de nuevas lineas estan permitidos
define('FH_NOT_EMPTY', 'notEmpty'); // Check that the value is not empty/ Comprueba que el valor no este vacio
define('FH_NO_HTML', 'NoHTML'); // check if the value does not contain html/ comprueba si el valor no contiene html
define('FH_IP', 'IsIp'); // check if the value is a valid IP address (xxx.xxx.xxx.xxx:xxxx)/ comprueba si el valor es una direccion IP valida (xxx.xxx.xxx.xxx:xxxx)

// Same as parent, but value is not required/ Igual al superior, pero el valor no es requerido
define('_FH_STRING', '_IsString');
define('_FH_ALPHA', '_IsAlpha');
define('_FH_DIGIT', '_IsDigit');
define('_FH_ALPHA_NUM', '_IsAlphaNum');
define('_FH_INTEGER', '_IsInteger');
define('_FH_FLOAT', '_IsFloat');
define('_FH_FILENAME', '_IsFilename');
define('_FH_BOOL', '_IsBool');
define('_FH_VARIABLE', '_IsVariabele');
define('_FH_PASSWORD', '_IsPassword');
define('_FH_URL', '_IsURL');
define('_FH_URL_HOST', '_IsURLHost');
define('_FH_EMAIL', '_IsEmail');
define('_FH_EMAIL_HOST', '_IsEmailHost');
define('_FH_TEXT', '_IsText');
define('_FH_POSTCODE', '_IsPostcode');
define('_FH_PHONE', '_IsPhone');
define('_FH_NO_HTML', '_NoHTML');
define('_FH_IP', '_IsIp');

// Mask for titles on the fields/ Mascara para títulos sobre los campos
// It is not used by default, but it can be useful for users/ No es usada por defecto, pero puede ser util para los usuarios
define('FH_TITLE_ABOVE_FIELD_MASK',

	"  %title% \n" .
	"  %seperator% \n" .
	"  %field%\n" .
	"  %error%\n" .
	"  %help%\n"
);

//Make some global variables when version < 4.1.0/ Realiza algunas variables globales cuando la version  < 4.1.0
if (intval(str_replace('.', '', phpversion())) < 410) {
	define('_global', false);
	$_GET = $HTTP_GET_VARS;
	$_POST = $HTTP_POST_VARS;
	$_FILES = $HTTP_POST_FILES;
	$_SERVER = $HTTP_SERVER_VARS;
}else {
// Set var if we don't have to make $_GET arrays global/ Configurar var si no tendremos que hacer que las matrices $_GET sean globales
	define('_global', true);
}

// Includes necessary files/ Incluye archivos necesarios
define('FH_INCLUDE_DIR', str_replace('\\', '/', dirname(__FILE__)) . '/');
require_once FH_INCLUDE_DIR . 'fields/class.Field.php';
require_once FH_INCLUDE_DIR . 'buttons/class.Button.php';
require_once FH_INCLUDE_DIR . 'includes/config.inc.php';
require_once FH_INCLUDE_DIR . 'includes/error.inc.php';
require_once FH_INCLUDE_DIR . 'includes/class.Validator.php';
require_once FH_INCLUDE_DIR . 'includes/class.MaskLoader.php';

/**
 * class FormHandler
 *
 * FormHandler sin opciones de DB
 * FormHandler without DB options
 *
 * @author Teye Heimans
 * @link https://www.fh3.nl/news.html
 */
class FormHandler {
	// protected !!
	protected $_fields; // array: Contains all fields/ Contiene todos los campos
	protected $_posted; // boolean: Whether the form is post or not/ Si el formulario es enviado o no
	protected $_name; // string: Form name/ Nombre del formulario
	protected $_action; // string: Form action/ La accion del formulario
	protected $_displayErrors; // boolean: if we have to show the errors in the form/ si tenemos que mostrar los errores en el formulario
	protected $_mask; // string: The mask to use/ La mascara que se debe usar
	protected $_upload; // array: Contains the names of the load fields/ Contiene los nombres de los campos de carga
	protected $_date; // array: Contains date field names/ Contiene los nombres de los campos de fecha
	protected $_onCorrect; // string: the callback function when the form is correct/ la función de devolución de llamada cuando el formulario es correcto
	protected $_add; // array: Contains data that were added by the user/ Contiene los datos que fueron agregados por el usuario
	protected $_focus; // string: The field that the focus must have/ El campo que debe tener el foco
	protected $_convert; // array: Fields to be converted (eg. resize or merge image)/ Campos que se deben convertir (eg. Redimensionar o fusionar imagen)
	protected $_buffer; // array: set values (used when the field does not yet exist)/ buffer valores establecidos (usados cuando el campo aun no existe)
	protected $_text; // array: Matrix language to be used to display messages, etc./ Lenguaje de matriz que usaremos para mostrar mensajes, etc.
	protected $_lang; // string: Language used/ Lenguaje usado
	protected $_setTable; // boolean: set an html table around the fields or did the user do it in the mask?/ establece una tabla html alrededor de los campos o lo hizo el usuario en la mascara?
	protected $_extra; // string: Additional tags for the <form> tag (such as CSS or javascript)/ Etiquetas adicionales para la etiqueta <form> (como CSS or javascript)
	protected $_pageCounter; // int: How many pages are in the form/ Cuantas paginas contiene el formulario
	protected $_curPage; // int: current page/ pagina actual
	protected $_mail; // array: contains mailing data/ contiene los datos de envio
	protected $_tabindexes; // array: Index of field tabs/ Indice de pestañas de los campos
	protected $_js; // array: contains all the javascript needed for the form/ contiene todo el javascript necesario para el formulario
	protected $_help; // array: contains help text for the fields/ contiene texto de ayuda para los campos
	protected $_helpIcon; // string: Route to help image/ Ruta a la imagen de ayuda
	protected $_cache; // array: Save the values of this array after calling the flush (then the objects are deleted!)/ Guarde los valores de este array despues de llamar al flush (entonces los objetos son borrados!)
	protected $_viewMode; // boolean: view mode is enabled or not/el modo vista esta habilitado o no
	protected $_tableSettings; // array: Matrix with all table configurations/ Matriz con toda la configuracion de las tablas
	protected $_ajaxValidator; // boolean: if Ajax validation should be used or not/ si la validacion de Ajax debe ser usada o no
	protected $_ajaxValidatorScript; // boolean: if the Ajax validation should include the library or not./ si la validacion de Ajax debe incluir la libreria o no.

	/**
	 * FormHandler::FormHandler()
	 *
	 * constructor: initialization of some vars/ inicializacion de algunas vars
	 *
	 * @param string $name: Form name (used in the <form> tag)/ Nombre del fomulario (usado en la etiqueta <form>)
	 * @param string $action: the action for the form (used in <form action="xxx">)/ la acción para el formulario (utilizada en <form action="xxx">)
	 * @param string $extra: extra css or js which is included in the <form> tag/ css o js extra que se incluye en la etiqueta <form>.
	 * @author Teye Heimans
	 * @return FormHandler
	 */
	public function __construct($name = null, $action = null, $extra = null) {
		// initialisation
		$this->_viewMode = false;
		$this->_ajaxValidator = false;
		$this->_ajaxValidatorScript = true;
		$this->_fields = array();
		$this->_date = array();
		$this->_upload = array();
		$this->_add = array();
		$this->_js = array();
		$this->_buffer = array();
		$this->_convert = array();
		$this->_mail = array();
		$this->_tabindexes = array();
		$this->_customMsg = array();
		$this->_help = array();
		$this->_cache = array();
		$this->_tableSettings = array();
		$this->_displayErrors = true;
		$this->_setTable = true;
		$this->_focus = null;
		$this->_pageCounter = 1;

		// make vars global if needed/ hacer vars globales si es necesario
		if (!_global) {
			global $_SERVER, $_POST, $_GET;
		}

		// try to disable caching from the browser if possible/ intente desactivar la caché del navegador si es posible
		if (!headers_sent()) {
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
			header("Cache-control: private");
		}

		// set all config values/ establecer todos los valores de configuración
		fh_conf();

		// get config setting for _setTable, since 08-10-2009 JW
		// obtener la configuracion asignada para _setTable
		$this->_setTable = FH_USE_TABLE;

		// get config setting for _focus, since 14-01-2010 JW
		// obtener la configuracion asignada para _focus
		$this->_focus = FH_SET_FOCUS;

		// set the name of the form (the user has submitted one)
		// establezca el nombre del formulario (el usuario ha enviado uno)
		if (!empty($name)) {
			$this->_name = $name;
		}
		// get a unique form name because the user did not give one
		// obtener un nombre de formulario unico porque el usuario no dio uno
		else {
			// get a unique form name!/ obtenga un nombre de formulario unico
			$i = null;
			while (defined('FH_' . FH_DEFAULT_FORM_NAME . $i)) {
				$i = is_null($i) ? 1 : ($i + 1);
			}

			define('FH_' . FH_DEFAULT_FORM_NAME . $i, 1);
			$this->_name = FH_DEFAULT_FORM_NAME . $i;
			$i = null;
		}

		// set the action of the form if none is given
		// establece la action del formulario si no se ha dado alguna
		if (!empty($action)) {
			$this->_action = $action;
		} else {
			$this->_action = $_SERVER['PHP_SELF'];
			if (!empty($_SERVER['QUERY_STRING'])) {
				$this->_action .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		// get the $extra (JS, css, etc..) to put into the <form> tag
		// obtengas el $extra (JS, css, etc..) para poner dentro de la etiqueta <form>
		if (!empty($extra)) {
			$this->_extra = $extra;
		}

		// set the default mask/ establece la mascara por defecto
		$this->setMask(FH_DEFAULT_ROW_MASK);

		// set the default help icon/ establece el icono de ayuda por defecto
		$this->setHelpIcon(FH_FHTML_DIR . 'images/helpicon.gif');

		// check if the form is posted/ comprueba si el formulario es enviado
		$this->_posted = ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST[$this->_name . '_submit']));

		// make a hidden field so we can identify the form
		// hacer un campo oculto para que podamos identificar el formulario
		$this->hiddenField($this->_name . '_submit', '1');

		// get the current page/ obtenga la pagina actual
		$this->_curPage = isset($_POST[$this->_name . '_page']) ? $_POST[$this->_name . '_page'] : 1;

		// set our own error handler/ establecer nuestro propio manejador de errores
		if (FH_DISPLAY_ERRORS) {
			error_reporting(E_ALL);
			set_error_handler('catchErrors');
		}

		// set the language.../ establece el idioma
		$this->setLanguage();

		// set the default table settings/ establece la configuracion por defecto de la tabla
		$this->setTableSettings();
	}

	/**
	 * Get Name Form/ Obtenga el nombre del formulario
	 *
	 * @return string
	 */
	public function getName(): string {
		return $this->_name;
	}

	/********************************************************/
	/************* FIELDS/CAMPOS ***********************************/
	/********************************************************/

	/**
	 * FormHandler::browserField()
	 *
	 * Creates a browserfield on the form
	 *
	 * @param string $title: The title of the field/ Titulo del campo
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param string $path: The path to browse/ Ruta de navegacion
	 * @param string $validator: The validator which should be used to validate the value of the field
	 * @param int $size: The size of the field
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 */
	// Carga imagenes de un archivo de FCKeditor
	public function browserField(
		$title,
		$name,
		$path,
		$validator = null,
		$size = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.BrowserField.php';
		require_once FH_INCLUDE_DIR . 'buttons/class.Button.php';

		// create the field/ crea el campo
		$fld = new BrowserField($this, $name, $path);
		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($size)) {
			$fld->setSize($size);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::textField()
	 *
	 * Create a textfield/ Crea campo de texto
	 *
	 * El campo de texto trae por defecto la clase de boostrap asignada (form-control)
	 * 
	 * @param string $title: The title of the field/ Titulo del campo
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param string $validator: Validacion del campo (o null)
	 * @param string $class: Las clases asociadas al campo (o null)
	 * @param string $extra: CSS, Javascript o cualquier otra cosa dentro de la etiqueta HTML
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 * @author Modificado por Daybel Diaz (06/07/2022)
	 */
	public function textField(
		$title,
		$name,
		$validator = null,
		$class = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.TextField.php';

		// create the field
		$fld = new TextField($this, $name);
		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($class)) {
			$fld->setClass($class);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title, $class);
	}

	/**
	 * FormHandler::captchaField()
	 *
	 * Creates a captchafield on the form using Securimage - A PHP class for creating and managing form CAPTCHA images
	 *
	 * @param string $title: The title of the field
	 * @param string $name: The name of the field
	 * @param int $size: The size of the field
	 * @param int $maxlength: The allowed max input of the field
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 * @since 27-11-2007
	 */
	public function CaptchaField(
		$title,
		$name,
		$size = null,
		$maxlength = null,
		$extra = null) {
		static $bCaptcha = true;
		if ($bCaptcha) {
			$bCaptcha = false;
			require_once FH_INCLUDE_DIR . 'fields/class.TextField.php';

			// create the field
			$fld = new TextField($this, $name);
			if ($this->isPosted()) {
				$fld->setValidator('FH_CAPTCHA');
			}
			if (!empty($size)) {
				$fld->setSize($size);
			}

			if (!empty($maxlength)) {
				$fld->setMaxlength($maxlength);
			}

			if (!empty($extra)) {
				$fld->setExtra($extra);
			}

			$this->ImageButton(FH_FHTML_DIR . 'securimage/securimage_show.php?sid=' . md5(uniqid(time())), null, 'onclick="return false;" style="cursor:default;"');

			// register the field
			$this->_registerField($name, $fld, $title);

			// empty the field if the value was not correct.

			if ($this->isPosted() && !$this->isCorrect()) {
				$this->setValue($name, "", true);
			}
		} else {
			trigger_error("Only one captchafield in a form", E_USER_WARNING);
		}
	}

	/**
	 * FormHandler::textSelectField()
	 *
	 * Creates a textSelectfield on the form
	 * Crea un campo de texto de selección
	 *
	 * @param string $title: The title of the field/ Titulo del campo
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param array $aOptions : the options for the select part/ Opciones para la parte de seleccion
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que se usará para validar el valor del campo
	 * @param int $size: The size of the field/ Tamaño del campo
	 * @param int $maxlength: The allowed max input of the field/ El maximo de entradas permitidas del campo
	 * @param string $extra: CSS, JS or other which are inserted into the HTML tag/ CSS, JS, u otro, el cual será insertado dentro de la etiqueta HTML
	 * @return void
	 * @access public
	 * @author Johan wiegel
	 * @since 22-10-2008
	 */
	public function textSelectField(
		$title,
		$name,
		$aOptions,
		$validator = null,
		$size = null,
		$maxlength = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.TextField.php';
		require_once FH_INCLUDE_DIR . 'fields/class.TextSelectField.php';

		// create the field
		$fld = new TextSelectField($this, $name, $aOptions);
		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($size)) {
			$fld->setSize($size);
		}

		if (!empty($maxlength)) {
			$fld->setMaxlength($maxlength);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::passField()
	 *
	 * Create a passField on the form/ Crea campo de clave
	 *
	 * @param string $title: The tittle of the field/ Titulo del campo 
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $class: Classes associated with the field/ Clases asociadas al campo
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag/ CSS, Javascript o cualquier cosa dentro de la etiqueta HTML
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 * @author Modificado por Daybel Diaz (06/07/2022)
	 */
	public function passField(
		$title,
		$name,
		$validator = null,
		$class = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.TextField.php';
		require_once FH_INCLUDE_DIR . 'fields/class.PassField.php';

		// create the field/ crea el campo
		$fld = new PassField($this, $name);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($class)) {
			$fld->setClass($class);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::hiddenField()
	 *
	 * Create a hidden field/ Crea un campo oculto
	 *
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param string $value: The value of the field/ Valor del campo
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function hiddenField(
		$name,
		$value = null,
		$validator = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.HiddenField.php';

		// create new hidden field/ crea un nuevo campo oculto
		$fld = new HiddenField($this, $name);

		// only set the hidden field value if there is not a value in the $_POST array
		if (!is_null($value) && !$this->isPosted()) {
			$fld->setValue($value);
		}

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, '__HIDDEN__');
	}

	/**
	 * FormHandler::textArea()
	 *
	 * Create a new textArea/ Crea un campo de area de texto 
	 *
	 * @param string $title: The title of the field/ Titulo del campo
	 * @param string $name: Name of the field/ Nombre del campo
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $class: clases asociadas al campo
	 * @param int $rows: Altura del campo
	 * @param string $extra: CSS, Javascript o cualquier cosa dentro de la etiqueta HTML
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 * @author Modificado por Daybel Diaz 06/07/2022
	 */
	public function textArea(
		$title,
		$name,
		$validator = null,
		$class= null,
		$rows = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.TextArea.php';

		// create new textarea
		$fld = new TextArea($this, $name);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($rows)) {
			$fld->setRows($rows);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::selectField()
	 *
	 * Crea un campo de seleccion
	 *
	 * @param string $title: Titulo del campo
	 * @param string $name: Nombre del campo
	 * @param array $options: Opciones usadas por el campo
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param boolean $useArrayKeyAsValue: Valores para las opciones
	 * @param boolean $multiple: Sera posible la seleccion multiple ? (Default: false)
	 * @param int $size: Tamaño del campo (Cuantas opciones se muestran)
	 * @param string $extra: CSS, Javascript o cualquier cosa dentro de la etiqueta HTML
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 * @author Modificado por Daybel Diaz 06/07/2022
	 */
	public function selectField(
		$title,
		$name,
		$options,
		$validator = null,
		$class = null,
		$useArrayKeyAsValue = null,
		$multiple = null,
		$size= null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.SelectField.php';

		// options has to be an array
		if (!is_array($options)) {
			trigger_error(
				"You have to give an array as value with the selectfield '$name'",
				E_USER_WARNING
			);
			return;
		}

		// create new selectfield/ crea un nuevo campo de seleccion
		$fld = new SelectField($this, $name);
		$fld->setOptions($options);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($class)) {
			$fld->setClass($class);

		}

		if (!is_null($useArrayKeyAsValue)) {
			$fld->useArrayKeyAsValue($useArrayKeyAsValue);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		if ($multiple) {
			$fld->setMultiple($multiple);
		}


		// if no size is set and multiple is enabled, set the size default to 4
		// 
		else if ($multiple) {
			$fld->setSize(4);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::checkBox()
	 *
	 * Crea un Checkbox
	 *
	 * @param string $title: The Title of the field/ Titulo del campo
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param array|string $value: Opciones a utilizar
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $class: clase general para el campo
	 * @param boolean $useArrayKeyAsValue: Valores de las opciones
	 * @param string $extra: CSS, Javascript o cualquier cosa dentro de la etiqueta HTML
	 * @param string $mask: Si hay mas de una opcion, pegue el campo a la mascara
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 * @author Modificado por Daybel Diaz 07/07/2022
	 */
	public function checkBox(
		$title,
		$name,
		$value = 'on',
		$validator = null,
		$class= null,
		$useArrayKeyAsValue = null,
		$extra = null,
		$mask = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.CheckBox.php';

		// create a new checkbox/ crea un nuevo checkbox
		$fld = new CheckBox($this, $name, $value);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($class)) {
			$fld->setClass($class);
		}

		if (!is_null($useArrayKeyAsValue)) {
			$fld->useArrayKeyAsValue($useArrayKeyAsValue);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		if (!empty($mask)) {
			$fld->setMask($mask);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::radioButton()
	 *
	 * Create a radioButton on the form
	 *
	 * @param string $title: The title of the field
	 * @param string $name: The name of the field
	 * @param array $options: The options used for the field
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param boolean $useArrayKeyAsValue: If the array key's are the values for the options in the field
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @param string $mask: if more the 1 options are given, glue the fields together with this mask
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function radioButton(
		$title,
		$name,
		$options,
		$validator = null,
		$useArrayKeyAsValue = null,
		$extra = null,
		$mask = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.RadioButton.php';

		// value has to be an array
		if (!is_array($options)) {
			trigger_error(
				"You have to give an array as value with the radiobutton '$name'",
				E_USER_WARNING
			);
			return;
		}

		// create a new checkbox
		$fld = new RadioButton($this, $name, $options);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!is_null($useArrayKeyAsValue)) {
			$fld->useArrayKeyAsValue($useArrayKeyAsValue);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		if (!empty($mask)) {
			$fld->setMask($mask);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::uploadField()
	 *
	 * Crea un campo de carga de imagenes
	 *
	 * @param string $title: The title of the filed/ Titulo del campo
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param array $config: Configuracion usada en el campo
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $extra: CSS, Javascript o cualquier cosa agregada a la etiqueta HTML
	 * @param string $alertOverwrite: Alerta de sobreescritura
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function uploadField(
		$title,
		$name,
		$config = array(),
		$validator = null,
		$class = null,
		$extra = null,
		$alertOverwrite = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.UploadField.php';

		// create a new uploadfield
		$fld = new UploadField($this, $name, $config);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($class)) {
			$fld->setClass($class);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		if (!is_null($alertOverwrite)) {
			$fld->setAlertOverwrite($alertOverwrite);
		}

		// register the field
		$this->_registerField($name, $fld, $title);

		// set that this form is using uploadfields
		$this->_upload[] = $name;
	}


	/**
	 * FormHandler::editor()
	 *
	 * Create a editor on the form
	 *
	 * @param string $title: The title of the field/ Titulo del campo
	 * @param string $name: The name of the field/ Nombre del campo
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $path: Path on the server where we have to upload the files
	 * @param string $toolbar: The toolbar we have to use
	 * @param string $skin: The skin to use
	 * @param int $width: The width of the field
	 * @param int $height: The height of the field
	 * @param boolean $useArrayKeyAsValue: If the array key's are the values for the options in the field
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function editor(
		$title,
		$name,
		$validator = null,
		$path = null,
		$toolbar = null,
		$skin = null,
		$width = null,
		$height = null,
		$config = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.TextArea.php';
		require_once FH_INCLUDE_DIR . 'fields/class.Editor.php';

		// create a new editor
		$fld = new Editor($this, $name);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!is_null($path)) {
			$fld->setServerPath($path);
		}

		if (!empty($toolbar)) {
			$fld->setToolbar($toolbar);
		}

		if (!empty($skin)) {
			$fld->setSkin($skin);
		}

		if (!empty($width)) {
			$fld->setWidth($width);
		}

		if (!empty($height)) {
			$fld->setHeight($height);
		}

		if (is_array($config)) {
			$fld->setConfig($config);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::dateField()
	 *
	 * Create a dateField on the form
	 *
	 * @param string $title: The title of the field
	 * @param string $name: The name of the field
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param boolean $required: If the field is required to fill in or can the user leave it blank
	 * @param string $mask: How do we have to display the fields? These can be used: d, m and y.
	 * @param string $interval: The interval between the current year and the years to start/stop.Default the years are beginning at 90 yeas from the current. It is also possible to have years in the future. This is done like this: "90:10" (10 years in the future).
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function dateField(
		$title,
		$name,
		$validator = null,
		$required = null,
		$mask = null,
		$interval = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.SelectField.php';
		require_once FH_INCLUDE_DIR . 'fields/class.TextField.php';
		require_once FH_INCLUDE_DIR . 'fields/class.DateField.php';

		// create a new datefield
		$fld = new DateField(
			$this,
			$name,
			!empty($mask) ? $mask : null,
			$required,
			$interval
		);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		/// register the field
		$this->_registerField($name, $fld, $title);

		// save the field in the datefields array (special treatment! :)
		$this->_date[] = $name;
	}

	/**
	 * FormHandler::timeField()
	 *
	 * Create a timeField on the form
	 *
	 * @param string $title: The title of the field
	 * @param string $name: The name of the field
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param int $format: 12 or 24. Which should we use?
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function timeField(
		$title,
		$name,
		$validator = null,
		$required = null,
		$format = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.SelectField.php';
		require_once FH_INCLUDE_DIR . 'fields/class.TimeField.php';

		// create a new timefield
		$fld = new TimeField($this, $name);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!is_null($required)) {
			$fld->setRequired($required);
		}

		if (!empty($format)) {
			$fld->setHourFormat($format);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title);
	}

	/**
	 * FormHandler::colorPicker()
	 *
	 * Creates a colorpicker on the form
	 *
	 * @param string $title: The title of the field
	 * @param string $name: The name of the field
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param int $size: The size of the field
	 * @param int $maxlength: The allowed max input of the field
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Johan Wiegel
	 * @since 23-10-2008
	 */
	public function colorPicker(
		$title,
		$name,
		$validator = null,
		$class = null,
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'fields/class.ColorPicker.php';

		// create the field
		$fld = new ColorPicker($this, $name);
		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($class)) {
			$fld->setclass($class);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		// register the field
		$this->_registerField($name, $fld, $title . $fld->sTitleAdd);
	}

	/**
	 * FormHandler::dateTextField()
	 *
	 * Create a dateTextField on the form
	 * Validator added by Johan Wiegel
	 *
	 * @param string $title: The title of the field
	 * @param string $name: The name of the field
	 * @param string $validator: The validator which should be used to validate the value of the field/ Validador que debe usarse para validar el valor del campo
	 * @param string $mask: How do we have to display the fields? These can be used: d, m and y. (Only for DB-Field with Type 'Date')
	 * @param bool $bParseOtherPresentations: try to parse other presentations of dateformat
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Thomas Branius
	 * @since 16-03-2010
	 */

	public function dateTextField(
		$title,
		$name,
		$validator = null,
		$mask = null,
		$bParseOtherPresentations = false,
		$extra = null
	) {
		require_once FH_INCLUDE_DIR . 'fields/class.TextField.php';
		require_once FH_INCLUDE_DIR . 'fields/class.DateTextField.php';

		// create a new datetextfield
		$fld = new DateTextField(
			$this,
			$name,
			!empty($mask) ? $mask : null,
			$bParseOtherPresentations
		);

		if (!empty($validator)) {
			$fld->setValidator($validator);
		}

		if (!empty($extra)) {
			$fld->setExtra($extra);
		}

		/// register the field
		$this->_registerField($name, $fld, $title);

		// save the field in the datefields array (special treatment! :)
		$this->_date[] = $name;
	}

	
	/*****************/
	/**** BUTTONS ****/
	/*****************/

	/**
	 * FormHandler::button()
	 *
	 * Create a button on the form
	 *
	 * @param string $caption: The caption of the button
	 * @param string $name: The name of the button
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function button(
		$caption,
		$name = null,
		$class = null,
		$extra = null
		) {

		require_once FH_INCLUDE_DIR . 'buttons/class.Button.php';


		// create new submitbutton
		$btn = new Button($this, $name);
		$btn->setCaption($caption);


		// get new button name if none is given
		if (empty($name)) {
			$name = $this->_getNewButtonName();
		}

		if (!empty($class)) {
			$btn->setClass($class);
		}

		if (!empty($extra)) {
			$btn->setExtra($extra);
		}


		// register the button
		$this->_registerField($name, $btn);
	}

	/**
	 * FormHandler::submitButton()
	 *
	 * Create a submitButton on the form
	 *
	 * @param string $caption: The caption of the button
	 * @param string $name: The name of the button
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @param boolean $disableOnSubmit: Disable the button when it is pressed
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function submitButton(
		$caption = null, 
		$name = null,
		$class = null, 
		$extra = null, 
		$disableOnSubmit = null) {
		require_once FH_INCLUDE_DIR . 'buttons/class.SubmitButton.php';
		// get new button name if none is given
		if (empty($name)) {
			$name = $this->_getNewButtonName();
		}

		// create new submitbutton
		$btn = new SubmitButton($this, $name);

		if (!empty($caption)) {
			$btn->setCaption($caption);
		}

		if (!empty($class)) {
			$btn->setClass($class);
		}

		if (!empty($extra)) {
			$btn->setExtra($extra);
		}

		if (!is_null($disableOnSubmit)) {
			$btn->disableOnSubmit($disableOnSubmit);
		}

		// register the button
		$this->_registerField($name, $btn);
	}

	/**
	 * FormHandler::imageButton()
	 *
	 * Create a imageButton on the form
	 *
	 * @param string $image: The image URL which should be a button
	 * @param string $name: The name of the button
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @param boolean $disableOnSubmit: Disable the button when it is pressed
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function imageButton($image, $name = null, $extra = null) {
		require_once FH_INCLUDE_DIR . 'buttons/class.ImageButton.php';

		// get new button name if none is given
		if (empty($name)) {
			$name = $this->_getNewButtonName();
		}

		// create the image button
		$btn = new ImageButton($this, $name, $image);

		if (!empty($extra)) {
			$btn->setExtra($extra);
		}

		// register the button
		$this->_registerField($name, $btn);
	}

	/**
	 * FormHandler::resetButton()
	 *
	 * Create a resetButton on the form
	 *
	 * @param string $caption: The caption of the button
	 * @param string $name: The name of the button
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function resetButton(
		$caption = null, 
		$name = null,
		$class = null, 
		$extra = null) {
		require_once FH_INCLUDE_DIR . 'buttons/class.ResetButton.php';

		// get new button name if none given
		if (empty($name)) {
			$name = $this->_getNewButtonName();
		}

		// create new resetbutton
		$btn = new ResetButton($this, $name);
		if (!empty($caption)) {
			$btn->setCaption($caption);
		}

		if (!empty($class)) {
			$btn->setClass($class);
		}

		if (!empty($extra)) {
			$btn->setExtra($extra);
		}

		// register the button
		$this->_registerField($name, $btn);
	}

	/**
	 * FormHandler::cancelButton()
	 *
	 * Create a cancelButton on the form
	 *
	 * @param string $caption: The caption of the button
	 * @param string $url: The URL to go to when the button is clicked
	 * @param string $name: The name of the button
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function cancelButton(
		$caption = null, 
		$url = null, 
		$name = null,
		$class = null, 
		$extra = null) {
		// get new button name if none given
		if (empty($name)) {
			$name = $this->_getNewButtonName();
		}

		if (!$url) {
			$url = 'history.back(-1)';
		}

		// where to go when the button is clicked...
		$extra .= preg_match('/history/', $url) ? ' onclick="' . $url . '"' : ' onclick="document.location.href=\'' . $url . '\'"';

		// if no caption is given, get our own caption
		if (is_null($caption)) {
			$caption = $this->_text(28);
		}

		// create new button
		$btn = new Button($this, $name);
		$btn->setCaption($caption);

		if (!empty($class)) {
			$btn->setClass($class);
		}

		if (!empty($extra)) {
			$btn->setExtra($extra);
		}

		// register the button
		$this->_registerField($name, $btn);
	}

	/**
	 * FormHandler::backButton()
	 *
	 * Generate a back button to go one page back in a multi-paged form
	 *
	 * @param string $caption: The caption of the button
	 * @param string $name: The name of the button
	 * @param string $extra: CSS, Javascript or other which are inserted into the HTML tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function backButton(
		$caption = null, 
		$name = null, 
		$class = null,
		$extra = null) {
		static $setJS = false;

		// include the needed javascript file
		if (!$setJS) {
			$this->_setJS(FH_FHTML_DIR . 'js/page_back.js', true);
			$setJS = true;
		}

		// get new button name if none given
		if (empty($name)) {
			$name = $this->_getNewButtonName();
		}

		$extra .= ' onclick="pageBack(document.forms[\'' . $this->_name . '\']);"';

		// if no caption is given, get our own caption
		if (is_null($caption)) {
			$caption = $this->_text(38);
		}

		// create new button
		$btn = new Button($this, $name);
		$btn->setCaption($caption);

		if (!empty($class)) {
			$btn->setClass($class);
		}

		if (!empty($extra)) {
			$btn->setExtra($extra);
		}

		// register the button
		$this->_registerField($name, $btn);
	}

	/********************************************************/
	/************* LOOK & FEEL ******************************/
	/********************************************************/

	/**
	 * FormHandler::setMaxLength()
	 *
	 * Set the maximum length of a TextArea
	 *
	 * @param string $field: The field for which the maximum length will be set
	 * @param int $maxlength: The allowed max input length of the field
	 * @param boolean $displaymessage: determines if a message is displayed with characters left
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setMaxLength($field, $maxlength, $displaymessage = true) {
		static $setJSmaxlength = false;

		// check if the field exists and is a textarea
		if (!$this->fieldExists($field) || strtolower(get_class($this->_fields[$field][1])) != 'textarea') {
			trigger_error(
				'You have to declare the textarea first! ' .
				'The field "' . $field . '" does not exists in the form!',
				E_USER_WARNING
			);
			return;
		}

		// check if the maxlength is numeric
		if (!is_numeric($maxlength)) {
			trigger_error('You have to give an numeric maxlength!', E_USER_WARNING);
			return;
		}

		// add the javascript file if not done yet
		if (!$setJSmaxlength) {
			$setJSmaxlength = true;
			$this->_setJS(FH_FHTML_DIR . 'js/maxlength.js', true);
		}

		// set the max length PHP check
		$this->_fields[$field][1]->setMaxLength($maxlength, $displaymessage);
	}

	/**
	 * FormHandler::parse_error_style()
	 *
	 * Set the style class on a by %error_style% specified element
	 *
	 * @param string $html: html for the field
	 * @return string
	 * @access public
	 * @author Ronald Hulshof
	 * @since 07-01-2009
	 */

	public function parse_error_style($mask) {
		// Get element containing %error_style%
		$pattern = '/<[^<>]*%error_style%[^<>]*>/';

		if (preg_match($pattern, $mask, $result)) {
			$element = $result[0];

			// Check if class-attribute already exists in element
			if (preg_match('/class=\"[^"]*"/', $element)) {
				// Class-attribute exists; add style
				$pattern = array('/class="/', '/\s*%error_style%\s*/');
				$replace = array('class="error ', '');
				$new_elem = preg_replace($pattern, $replace, $element);
				$mask = str_replace($element, $new_elem, $mask);
			} else {
				// Class-attribute does not exist; create it
				$new_elem = preg_replace('/%error_style%/', 'class="error"', $element);
				$mask = str_replace($element, $new_elem, $mask);
			}
		}
		return $mask;
	}

	/**
	 * Formhandler::parse_error_Fieldstyle
	 *
	 * Set the error class to the field itself
	 *
	 * @param string $field
	 * @return string
	 * @access public
	 * @author Johan Wiegel
	 * @since 25-08-2009
	 */
	public function parse_error_Fieldstyle($field) {
		// Check if class-attribute already exists in element
		if (preg_match('/class=\"[^"]*"/', $field) OR preg_match('/class=\'[^"]*\'/', $field)) {
			// Class-attribute exists; add style
			$pattern = array('/class="/', '/class=\'/');
			$replace = array('class="error ', 'class=\'error ');
			$field = preg_replace($pattern, $replace, $field);
		} elseif (preg_match('/class=[^"]*/', $field)) {
			// Class-attribute exists; add style
			$pattern = array('/class=/');
			$replace = array('class=error ');
			$field = preg_replace($pattern, $replace, $field);
		} else {
			// Class-attribute does not exist; create it
			if (FH_XHTML_CLOSE != '' AND !preg_match('/\<select /', $field) AND !preg_match('/\<textarea name/', $field)) {
				$field = preg_replace('/\/>/', 'class="error" />', $field);
			} else {
				if (preg_match('/\<textarea name/', $field)) {
					$field = preg_replace('/<textarea /', '<textarea class="error" ', $field);
				} elseif (preg_match('/\<select name/', $field)) {
					$field = preg_replace('/<select /', '<select class="error" ', $field);
				} else {
					$field = preg_replace('/>/', 'class="error">', $field);
				}
			}
		}
		return $field;
	}

	/**
	 * FormHandler::setHelpText()
	 *
	 * Set the help text for a specific field
	 *
	 * @param string $field: The name of the field to set the help text for
	 * @param string $helpText: The help text for the field
	 * @param string $helpTitle: The help title
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setHelpText($field, $helpText) {
		static $setJS = false;

		// escape the values from dangerous characters
		$helpText = preg_replace("/\r?\n/", "\\n", addslashes($helpText));

		// set the help text
		$this->_help[$field] = array(
			htmlentities($helpText, null, FH_HTML_ENCODING),
		);
	}

	/**
	 * FormHandler::setHelpIcon()
	 *
	 * Set the help icon used for help messages
	 *
	 * @param string $helpIcon: The path to the help icon
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setHelpIcon($helpIcon) {
		$this->_helpIcon = $helpIcon;
	}

	/**
	 * FormHandler::addHTML()
	 *
	 * Add some HTML to the form
	 *
	 * @param string $html: The HTML we have to add to the form
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function addHTML($html) {
		$this->_fields[] = array('__HTML__', $html);
	}

	/**
	 * FormHandler::addLine()
	 *
	 * Add a new row to the form.
	 *
	 * @param string $data: Possible data to set into the row (line)
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function addLine($text = null) {
		$this->_fields[] = array('__LINE__', sprintf(FH_LINE_MASK, $text));
	}

	/**
	 * FormHandler::borderStart()
	 *
	 * Begin a new fieldset
	 *
	 * @param string $caption: The caption of the fieldset
	 * @param string $name: The name of the fieldset
	 * @param string $extra: Extra css or javascript which should be placed in the fieldset tag
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function borderStart($caption = null, $name = null, $extra = '') {
		static $i = 1;

		if (empty($name)) {
			$name = 'fieldset' . $i++;
		}

		$this->_fields[] = array(
			'__FIELDSET__',
			array($name, $caption, $extra),
		);
	}

	/**
	 * FormHandler::borderStop()
	 *
	 * Stops a fieldset
	 *
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function borderStop() {
		$this->_fields[] = array('__FIELDSET-END__', true);
	}

	/**
	 * FormHandler::useTable()
	 *
	 * Do we have to set the <table> tag arround the fields ?
	 *
	 * @param bool $setTable
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function useTable($setTable) {
		$this->_setTable = (bool) $setTable;

	}

	/**
	 * FormHandler::setMask()
	 *
	 * Sets a mask for the new row of fields
	 *
	 * @param string $mask: The mask we have to use
	 * @param int|bool $repeat: If we have to repeat the mask. When a integer is given, it will be countdown
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 * @since 14-02-2008 Changed in order to also parse php as a template by Johan Wiegel
	 */
	public function setMask($mask = null, $repeat = true) {
		// when no mask is given, set the default mask
		if (is_null($mask)) {
			$mask = FH_DEFAULT_ROW_MASK;
		}
		// a mask is given.. is it a file ?
		else if (file_exists($mask) && is_file($mask)) // double check of PHP bug in file_exists
		{
			// is the file readable ?
			if (is_readable($mask)) {
				// get the contents of the file and parse php code in it
				$mask = $this->get_include_contents($mask);
			}
			// the file is not readable!
			else {
				trigger_error('Could not read template ' . $mask, E_USER_WARNING);
			}
		}

		// is there a third arument (the old way for disabling the table tag)
		if (func_num_args() == 3) {
			// display deprectated message
			trigger_error(
				'This way of disabling the table tag is deprecated! ' .
				'Use the method "useTable" instead!',
				E_USER_NOTICE
			);

			// save the var
			$this->_setTable = func_get_arg(2);
		}

		// save the mask
		$this->_fields[] = array('__MASK__', array($mask, $repeat));
	}

	/**
	 * Get the file contents by including it, to enable parsing of php files
	 *
	 * @param string $sFilename : the file to get/parse
	 * @return void
	 * @access public
	 * @author sid benachenhou
	 * @since 14-02-2008 added by Johan Wiegel
	 */
	public function get_include_contents($sFilename) {
		if (is_file($sFilename)) {
			ob_start();
			include $sFilename;
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		return false;
	}

	/**
	 * FormHandler::setErrorMessage()
	 *
	 * Set a spicified error message to a field
	 *
	 * @param string $field: The field to set the message for
	 * @param string $message: The message to use when the fields value is invalid
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setErrorMessage($field, $message, $useStyle = true) {
		$this->_customMsg[$field] = array($message, $useStyle);
	}

	/**
	 * FormHandler::setAutoComplete()
	 *
	 * Set a list of items for auto complete
	 *
	 * @param string $field: The field which should be auto complete
	 * @param array $options: The list of options for the uto complete
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setAutoComplete($field, $options) {
		static $setJS = false;

		// check if the field exists and is a textfield
		if (!$this->fieldExists($field) || strtolower(get_class($this->_fields[$field][1])) != 'textfield') {
			trigger_error(
				'You have to declare the textfield first! ' .
				'The field "' . $field . '" does not exists in the form!',
				E_USER_WARNING
			);

			return;
		}

		// check if the options are correct
		if (!is_array($options)) {
			trigger_error('You have to give an array as options!', E_USER_WARNING);
			return;
		}

		// add the javascript file if not done yet
		if (!$setJS) {
			$setJS = true;
			$this->_setJS(FH_FHTML_DIR . 'js/autocomplete.js', true);
		}

		// create the javascript array
		$js = $field . '_values = [';
		foreach ($options as $option) {
			$js .= '"' . htmlentities($option, null, FH_HTML_ENCODING) . '", ';
		}
		$this->_setJS(substr($js, 0, -2) . "];\n");

		// add the javascript to the fields "extra" argument
		$tmpExtra = $this->_fields[$field][1]->getExtra();
		$tmpExtra .= " onkeypress='return FH_autocomplete(this, event, " . $field . "_values);' ";
		$this->_fields[$field][1]->setExtra($tmpExtra);
	}

	/**
	 * FormHandler::setAutoComplete()
	 *
	 * Set a list of items for auto complete after specified character
	 *
	 * @param string $field: The field which should be auto complete
	 * @param string $after: The character after wicht auto completion will start
	 * @param array $options: The list of options for the uto complete
	 * @return void
	 * @access public
	 * @author Rob Geerts
	 * @since 12-02-2008 ADDED BY Johan Wiegel
	 */
	public function setAutoCompleteAfter($field, $after, $options) {
		static $setJS = false;

		// check if the field exists and is a textfield
		if (!$this->fieldExists($field) || strtolower(get_class($this->_fields[$field][1])) != 'textfield') {
			trigger_error(
				'You have to declare the textfield first! ' .
				'The field "' . $field . '" does not exists in the form!',
				E_USER_WARNING
			);

			return;
		}

		// check if the options are correct
		if (!is_array($options)) {
			trigger_error('You have to give an array as options!', E_USER_WARNING);
			return;
		}

		// add the javascript file if not done yet
		if (!$setJS) {
			$setJS = true;
			$this->_setJS(FH_FHTML_DIR . 'js/autocomplete.js', true);
		}

		// create the javascript array
		$js = $field . '_values = [';
		foreach ($options as $option) {
			$js .= '"' . htmlentities($option, null, FH_HTML_ENCODING) . '", ';
		}
		$this->_setJS(substr($js, 0, -2) . "];\n");

		// add the javascript to the fields "extra" argument
		$tmpExtra = $this->_fields[$field][1]->getExtra();
		$tmpExtra .= " onkeypress='return autocompleteafter(this, event,\"" . $after . "\", " . $field . "_values);' ";
		$this->_fields[$field][1]->setExtra($tmpExtra);
	}
	/***/

	/**
	 * FormHandler::newPage()
	 *
	 * Put the following fields on a new page
	 *
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function newPage() {
		$this->_fields[] = array('__PAGE__', $this->_pageCounter++);
	}

	/**
	 * FormHandler::setTabIndex()
	 *
	 * Set the tab index for the fields
	 *
	 * @param mixed $mTabs: array or comma seperated string with the field names.
	 * When an array is given the array index will set as tabindex
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setTabIndex($tabs) {
		// is the given value a string?
		if (is_string($tabs)) {
			// split the commas
			$tabs = explode(',', $tabs);

			// add an empty value so that the index 0 isnt used
			array_unshift($tabs, '');
		}
		// is the given value an array
		else if (is_array($tabs)) {
			// is set element 0, then move all elements
			// (0 is not a valid tabindex, it starts with 1)
			if (isset($tabs[0])) {
				ksort($tabs);
				$new = array();

				foreach ($tabs as $key => $value) {
					while (array_key_exists($key, $new) || $key <= 0) {
						$key++;
					}

					$new[$key] = $value;
				}
				$tabs = $new;
			}
			// the tabs array is good.. just use it
		}

		// array with tabs set ?
		if (isset($tabs)) {
			// walk each tabindex
			foreach ($tabs as $key => $value) {
				// if there is a field..
				if (!empty($value)) {
					$tabs[$key] = trim($value);
				}
				// no field is given, remove it's index
				else {
					unset($tabs);
				}
			}

			// save the tab indexes
			$this->_tabindexes = $this->_tabindexes + $tabs;
		}
	}

	/**
	 * FormHandler::setLanguage()
	 *
	 * Set the language we should use for error messages etc.
	 * If no language is given, try to get the language defined by the visitors browser.
	 *
	 * @param string $language: The language we should use
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setLanguage($sLanguage = null) {
		if (!_global) {
			global $_SERVER;
		}

		// if nog language is given, try to get it from the visitors browser if wanted
		if (is_null($sLanguage)) {
			// auto detect language ?
			$bSet = false;
			if (FH_AUTO_DETECT_LANGUAGE) {
				// get all accepted languages by the browser
				$aLang = array();
				if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
					foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $sValue) {
						if (strpos($sValue, ';') !== false) {
							list($sValue) = explode(';', $sValue);
						}
						if (strpos($sValue, '-') !== false) {
							list($sValue) = explode('-', $sValue);
						}
						$aLang[] = $sValue;
					}
				}

				// set the language which formhandler supports
				foreach ($aLang as $l) {
					$lFilename = null;
					// check if the language file exists in UTF-8
					if (file_exists(FH_INCLUDE_DIR . 'language/' . strtolower($l) . '-utf8.php')) {
						$lFilename = $l . '-utf8';
					}
					// check if the language file exists
					elseif (file_exists(FH_INCLUDE_DIR . 'language/' . strtolower($l) . '.php')) {
						$lFilename = $l;
					}
					if (!is_null($lFilename)) {
						// set the language
						$this->setLanguage($lFilename);
						$bSet = true;
						break;
					}
				}
			}

			// no language is set yet.. set the default language
			if (!$bSet) {
				$this->setLanguage(FH_DEFAULT_LANGUAGE);
			}
		}
		// when a language is given
		else {
			// check if the language does not contain any slashes or dots
			if (preg_match('/\.|\/|\\\/', $sLanguage)) {
				return;
			}

			// make sure that the language is set in lower case
			$sLanguage = strtolower($sLanguage);

			// check if the file exists
			if (file_exists(FH_INCLUDE_DIR . 'language/' . $sLanguage . '.php')) {
				// include the language file
				include FH_INCLUDE_DIR . 'language/' . $sLanguage . '.php';

				// load the array from the text file
				$this->_text = $fh_lang;

				// save the language
				$this->_lang = $sLanguage;
			}
			// language file does not exists
			else {
				trigger_error(
					'Unknown language: ' . $sLanguage . '. Could not find ' .
					'file ' . FH_INCLUDE_DIR . 'language/' . $sLanguage . '.php!',
					E_USER_ERROR
				);
			}
		}
	}

	/**
	 * FormHandler::catchErrors()
	 *
	 * Get the errors occoured in the form
	 *
	 * @param boolean $display: If we still have to display the errors in the form (default this is disabled)
	 * @return array of errors or an empty array if none occoured
	 * @access public
	 * @author Teye Heimans
	 */
	public function catchErrors($display = false) {
		// only return the errors when the form is posted
		// and the form is not correct
		if ($this->isPosted() && !$this->isCorrect()) {
			$this->_displayErrors = $display;

			// walk each field and get the error of the field
			$errors = array();
			foreach ($this->_fields as $field => $obj) {
				// check if it's a field (where we can get an error message from )
				$obj = $this->_fields[$field];

				if (is_object($obj[1]) && method_exists($obj[1], 'getError') && method_exists($obj[1], 'isValid')) {
					// check if it's valid
					if (!$obj[1]->isValid()) {
						// save the error message if there is one
						$err = $obj[1]->getError();
						if (strlen($err) > 0) {
							// if there is an error, check if we should use a custom error message
							if (array_key_exists($field, $this->_customMsg)) {
								// use the default error mask ?
								if ($this->_customMsg[$field][1]) {
									$err = sprintf(FH_ERROR_MASK, $this->_customMsg[$field][1], $this->_customMsg[$field][0]);
								}
								// dont use the default error mask...
								else {
									$err = $this->_customMsg[$field][0];
								}
							}
							$errors[$field] = $err;
						}
					}
				}
			}

			return $errors;
		}

		return array();
	}

	/**
	 * FormHandler::setFocus()
	 *
	 * Set the focus to a sepcific field
	 *
	 * @param string $field: The field which should get the focus
	 * @return boolean: true if the focus could be set, false if not
	 * @access public
	 * @author Teye Heimans
	 */
	public function setFocus($field) {
		// if the field is false, no focus has to be set...
		if ($field === false) {
			$this->_focus = false;
			return true;
		}

		// check if the field exists
		if (!$this->fieldExists($field)) {
			trigger_error(
				'Could net set focus to unknown field "' . $field . '"',
				E_USER_NOTICE
			);

			return;
		}

		// some fields have other names... change it.
		switch (strtolower(get_class($this->_fields[$field][1]))) {
		case 'jsdatefield':
		case 'datefield':
			$field = $field . '_day';
			break;

		case 'listfield':
			$field = $field . '_ListOn';
			break;

		case 'timefield':
			$field = $field . '_hour';
			break;

		// these fields cant have the focus
		case 'editor':
		case 'radiobutton':
		case 'checkbox':
		case 'hiddenfield':
		// buttons cant have the focus
		case 'submitbutton':
		case 'resetbutton':
		case 'imagebutton':
		case 'button':
			$field = null;
			break;
		}

		$this->_focus = $field;

		return !is_null($field);
	}

	/**
	 * FormHandler::enableAjaxValidator
	 *
	 * @param boolean $mode: The new state of the AjaxValidator
	 * @param boolean $bScript: Should the library (jQuery) be included by FH
	 * @return void
	 *
	 * @since 03-12-2008
	 * @author Johan Wiegel
	 */

	public function enableAjaxValidator($mode = true, $bScript = true) {
		$this->_ajaxValidator = (bool) $mode;
		$this->_ajaxValidatorScript = (bool) $bScript;
	}

	/**
	 * FormHandler::enableViewMode()
	 *
	 * Set all fields in view mode
	 *
	 * @param boolean $mode: The new state of the Forms View Mode
	 * @return void
	 */
	public function enableViewMode($mode = true) {
		$this->_viewMode = (bool) $mode;
	}

	/**
	 * FormHandler::isViewMode()
	 *
	 * Gets the ViewMode state
	 *
	 * @return boolean
	 * @access public
	 * @author Teye Heimans
	 */
	public function isViewMode() {
		return $this->_viewMode;
	}

	/**
	 * FormHandler::setFieldViewMode()
	 *
	 * Sets and indiviual f ields display mode
	 *
	 * @param string $field: The name of the field to set the display mode for
	 * @param boolean $mode: True = field is view only
	 * @return void
	 * @access public
	 */
	public function setFieldViewMode($field, $mode = true) {
		// does the field exists?
		if ($this->fieldExists($field)) {
			// set the new modes
			$this->_fields[$field][1]->setViewMode($mode);
		}
		// the field does not exists! error!
		else {
			trigger_error(
				'Error, could not find field "' . $field . '"! Please define the field first!',
				E_USER_NOTICE
			);
		}
	}

	/**
	 * FormHandler::isFieldViewMode()
	 *
	 * Check if the field should be displayed as view only
	 *
	 * @param string $field: The field to check
	 * @return boolean
	 * @access public
	 */
	public function isFieldViewMode($field) {
		// does the field exists?
		if ($this->fieldExists($field) && is_object($this->_fields[$field][1]) && method_exists($this->_fields[$field][1], 'getViewMode')) {
			// get the mode
			return $this->_fields[$field][1]->getViewMode();
		}
		// the field does not exists! error!
		else {
			trigger_error(
				'Error, could not find field "' . $field . '"! Please define the field first!',
				E_USER_NOTICE
			);
		}
	}

	/**
	 * FormHandler::setTableSettings()
	 *
	 * @param int width
	 * @return void
	 * @author Teye Heimans
	 */
	public function setTableSettings(
		$width = null,
		$cellspacing = null,
		$cellpadding = null,
		$border = null,
		$extra = '') {
		// set the default
		if (is_null($width)) {
			$width = FH_DEFAULT_TABLE_WIDTH;
		}

		if (!is_numeric($cellspacing)) {
			$cellspacing = FH_DEFAULT_TABLE_CELLSPACING;
		}

		if (!is_numeric($cellpadding)) {
			$cellpadding = FH_DEFAULT_TABLE_CELLPADDING;
		}

		if (!is_numeric($border)) {
			$border = FH_DEFAULT_TABLE_BORDER;
		}

		// save the table settings
		$this->_tableSettings = array(
			'width' => $width,
			'cellspacing' => $cellspacing,
			'cellpadding' => $cellpadding,
			'border' => $border,
			'extra' => $extra,
		);
	}

	/********************************************************/
	/************* DATA HANDLING ****************************/
	/********************************************************/

	/**
	 * FormHandler::getValue()
	 *
	 * Alias for the function value
	 *
	 * @param string $field: The field which value we have to return
	 * @return string
	 * @access public
	 * @author Teye Heimans
	 */
	public function getValue($field) {
		return $this->value($field);
	}

	/**
	 * FormHandler::getAsArray()
	 *
	 * Return the value of a datefield as an array: array(y,m,d)
	 *
	 * @param string $datefield: return the value of the datefield as an array
	 * @return array
	 * @access public
	 * @author Teye Heimans
	 */
	public function getAsArray($datefield) {
		// check if the datefield exists
		if (in_array($datefield, $this->_date)) {
			return $this->_fields[$datefield][1]->getAsArray();
		}
		// the datefield does not exists
		else {
			trigger_error(
				'The datefield "' . $datefield . '" does not exists!',
				E_USER_NOTICE
			);

			return false;
		}
	}

	/**
	 * FormHandler::value()
	 *
	 * Get the value of the requested field
	 *
	 * @param string $field: The field which value we have to return
	 * @return string
	 * @access public
	 * @author Teye Heimans
	 */
	public function value($field) {
		if (!_global) {
			global $_POST;
		}

		// is it a field?
		if (isset($this->_fields[$field]) && is_object($this->_fields[$field][1]) && method_exists($this->_fields[$field][1], 'getvalue')) {
			return $this->_fields[$field][1]->getValue();
		}
		// is it an user added value ?
		else if (isset($this->_add[$field])) {
			return $this->_add[$field];
		}
		// _chache contains the values of the fields after flush() is called
		// (because then all objects are removed from the memory)
		else if (isset($this->_cache[$field])) {
			return $this->_cache[$field];
		}
		// is it a set value of a field which does not exists yet ?
		else if (isset($this->_buffer[$field])) {
			return $this->_buffer[$field][1];
		}
		// is it a value from the $_POST array ?
		else if (isset($_POST[$field])) {
			// give a notice
			//trigger_error(
			//  'Notice: the value retrieved from the field "'.$field.'" could '.
			//  'only be fetched from the $_POST array. The field is not found in the form...',
			//  E_USER_NOTICE
			//);

			return $_POST[$field];
		}

		trigger_error(
			'Try to get the value of an unknown field "' . $field . '"!',
			E_USER_WARNING
		);

		return null;
	}

	/**
	 * FormHandler::setValue()
	 *
	 * Set the value of the spicified field
	 *
	 * @param string $field: The field which value we have to set
	 * @param string $value: The value we have to set
	 * @param boolean $overwriteCurrentValue: Do we have to overwrite the current value of the field (posted value)
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function setValue($sField, $sValue, $bOverwriteCurrentValue = false) {
		// check if the field exists
		if ($this->fieldExists($sField)) {
			// if the field does not exists in the database
			if ($bOverwriteCurrentValue || !$this->isPosted() ||
				// only set the value if the page is not 'done' yet, otherwise
				// we will overwrite it
				$this->_curPage < $this->_pageCounter) {
				$this->_fields[$sField][1]->setValue($sValue);
			}
		}
		// the field does not exists. Save the value in the buffer.
		// the field will check this buffer and use it value when it's created
		else {
			// save the data untill the field exists
			$this->_buffer[$sField] = array($bOverwriteCurrentValue, $sValue);
		}
	}

	/**
	 * FormHandler::addValue()
	 *
	 * Add a value to the data array which is going
	 * to be saved/used in the oncorrect & onsaved functions
	 *
	 * @param string $field: The field which value we have to set
	 * @param string $value: The value we have to set
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function addValue($field, $value) {
		// save the added value
		$this->_add[$field] = $value;
	}

	/**
	 * FormHandler::onCorrect()
	 *
	 * Set the function which has to be called when the form is correct
	 *
	 * @param string $callback: The name of the function
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function onCorrect($callback) {
		// is the given value a string ?
		if (!is_array($callback)) {
			// does the function exists ?
			if (function_exists($callback)) {
				$this->_onCorrect = $callback;
			}
			// the given callback function does not exists
			else {
				trigger_error(
					'Error, the onCorrect function "' . $callback . '" does not exists!',
					E_USER_ERROR
				);
			}
		}
		// we have to call a method
		else {
			// check if the method exists in the given object
			if (method_exists($callback[0], $callback[1])) {
				$this->_onCorrect = $callback;
			}
			// the method does not exists
			else {
				trigger_error(
					'Error, the onCorrect method "' . $callback[1] . '" does not exists in the given object' .
					(is_object($callback[0]) ? ' "' . get_class($callback[0]) . '"!' : '!'),
					E_USER_ERROR
				);
			}
		}
	}

	/**
	 * FormHandler::setError()
	 *
	 * Set a specified error to a field
	 *
	 * @param string $field: The field to set the error for
	 * @param string $error: The error message to use
	 * @return boolean: Returns the success of the operation
	 * @access public
	 * @author Filippo Toso - filippotoso@libero.it
	 */

	public function setError($field, $error) {
		if (isset($this->_fields[$field][1])) {
			$this->_fields[$field][1]->setError($error);
			$this->_fields[$field][1]->setInvalid();
			return true;
		}

		return false;
	}

	/********************************************************/
	/************* GENERAL **********************************/
	/********************************************************/

	/**
	 * FormHandler::getFileInfo()
	 *
	 * Get the file info af an uploaded file
	 *
	 * @param string $uploadfield: the name of the uploadfield
	 * @return array file info
	 * @access public
	 * @author Teye Heimans
	 */
	public function getFileInfo($uploadfield) {
		// does the field exists ?
		if ($this->fieldExists($uploadfield)) {
			// is it an uploadfield ?
			$obj = &$this->_fields[$uploadfield][1];
			if (strtolower(get_class($obj)) == 'uploadfield') {
				// check if there is an file uploaded
				if ($obj->isUploaded()) {
					// return the file info
					return $obj->getFileInfo();
				}
			}
			// the field is not an uploadfield
			else {
				trigger_error(
					'Error, the field "' . $uploadfield . '" is not an uploadfield!',
					E_USER_NOTICE
				);
			}
		}
		// the field does not exists
		else {
			trigger_error(
				'Error, the uploadfield "' . $uploadfield . '" does not exists!',
				E_USER_NOTICE
			);
		}

		// if we come here, something went wrong. Return empty array
		return array();
	}

	/**
	 * FormHandler::isUploaded()
	 *
	 * Check if the given uploadfield has a file which is uploaded
	 *
	 * @param string $uploadfield: the name of the uploadfield
	 * @return boolean
	 * @access public
	 * @author Teye Heimans
	 */
	public function isUploaded($uploadfield) {
		// does the field exists ?
		if ($this->fieldExists($uploadfield)) {
			// is it an uploadfield ?
			$obj = &$this->_fields[$uploadfield][1];
			if (strtolower(get_class($obj)) == 'uploadfield') {
				// check if there is an file uploaded
				return $obj->isUploaded();
			}
			// the field is not an uploadfield
			else {
				trigger_error(
					'Error, the field "' . $uploadfield . '" is not an uploadfield!',
					E_USER_NOTICE
				);
			}
		}
		// the field does not exists
		else {
			trigger_error(
				'Error, the uploadfield "' . $uploadfield . '" does not exists!',
				E_USER_NOTICE
			);
		}

		// if we come here, something went wrong. Return false
		return false;
	}

	/**
	 * FormHandler::getLastSubmittedPage()
	 *
	 * Returns the page number of the last submitted page of the form
	 *
	 * @return int
	 * @access public
	 * @author Remco van Arkelen & Johan Wiegel
	 * @since 21-08-2009
	 */
	public function getLastSubmittedPage() {
		return $this->getPage();
	}

	/**
	 * FormHandler::getPage()
	 *
	 * Returns the page number of the last submitted page the form (when getPage is called)
	 *
	 * @return int
	 * @access public
	 * @author Teye Heimans
	 */
	public function getPage() {
		return $this->_pageCounter;
	}

	/**
	 * FormHandler::getCurrentPage()
	 *
	 * Returns the current page number of the current form (used when newPage is used!)
	 *
	 * @return int
	 * @access public
	 * @author Teye Heimans
	 */
	public function getCurrentPage() {
		return $this->_curPage;
	}

	/**
	 * Liefert die eingestelle Sprache
	 * @return string
	 */
	public function getLang() {
		return $this->_lang;
	}

	/**
	 * FormHandler::linkSelectFields()
	 *
	 * Link de given selectfields (load the values dynamicly)
	 *
	 * @param string $filename: the name of the file which will load the new values for the select field
	 * @param string $fields: the name of the first dynamic select field.
	 * @param ...: More fields which are linked to eachother
	 * @return null
	 * @access public
	 * @author Teye Heimans
	 */
	public function linkSelectFields($filename, $fields) {
		static $setJS = false;

		$page = $this->isPosted() && !$this->isCorrect() ? $this->_curPage - 1 : $this->_curPage;

		// when we are not at the correct page, do nothing
		// added && $this - > isCorrect() in order to keep validation 07-10-2009 JW
		if ($page != ($this->_pageCounter - 1 < 1 ? 1 : $this->_pageCounter - 1) && $this->isCorrect()) {
			return;
		}

		// add the javascript file if not done yet
		if (!$setJS) {
			$setJS = true;
			$this->_setJS(FH_FHTML_DIR . 'js/linked_select.js', true);
		}

		// walk all arguments
		$js = '';
		$values = array();
		for ($i = 1; $i < (func_num_args() - 1); $i++) {
			// get the "parent" and "child" field
			$fld1 = func_get_arg($i);
			$fld2 = func_get_arg($i + 1);
			$extra = '';

			// extra arguments given ?
			if (is_array($fld1)) {
				$arr = $fld1;
				$fld1 = array_shift($arr);

				// walk all "extra" arguments
				while ($item = array_shift($arr)) {
					// is this argument a field? Then load the "db" value
					if ($this->fieldExists($item)) {
						$extra .= '&' . $item . '="+document.forms["' . $this->getFormName() . '"].elements["' . $item . '"].value+"';
					}
					// just load the extra argument, it's a js string
					else {
						$extra .= $item;
					}
				}
			}
			if (is_array($fld2)) {
				list($fld2) = $fld2;
			}

			// make sure that the fields exists
			if (!$this->fieldExists($fld1)) {
				trigger_error(
					'Error, the field "' . $fld1 . '" could not be found in the form!',
					E_USER_NOTICE
				);
				return false;
			}
			// make sure that the fields exists
			if (!$this->fieldExists($fld2)) {
				trigger_error(
					'Error, the field "' . $fld2 . '" could not be found in the form!',
					E_USER_NOTICE
				);
				return false;
			}

			// values opslaan
			$values[] = $this->getValue($fld2);

			// change the name of a listfield to {$fieldname}_ListOff[]
			if (strtolower(get_class($this->_fields[$fld1][1])) == 'listfield') {
				$fld1 .= '_ListOff';
			}
			if (strtolower(get_class($this->_fields[$fld2][1])) == 'listfield') {
				$fld2 .= '_ListOff';
			}

			// if this is the first field
			if ($i == 1) {
				$jsAfter =
					"// load the first item of the dynamic select fields\n" .
					"attach" . $fld1 . "(";
			}

			// create the javascript for dynamic loading..
			$func = $i < (func_num_args() - 2) ? 'attach' . $fld2 : 'null';
			$param = isset($this->edit) && $this->edit ? '&value=' . $values[count($values) - 1] : '';
			$param .= $extra;

			$js .=
			'function attach' . $fld1 . '( aArgs, sValue ) {' . "\n" .
			'    attachelement("' . $fld1 . '", "change", load' . $fld2 . ');' . "\n" .
			'    load' . $fld2 . '( aArgs, sValue );' . "\n" .
			'}' . "\n\n" .
			'function load' . $fld2 . '( aArgs, sValue ) {' . "\n" .
			'    value = GetElement("' . $fld1 . '").value;' . "\n" .
			//'    value = document.forms["'.$this->getFormName().'"].elements["'.$fld1.'"].value;'."\n".
			//'    //GetElement("'.$fld2.'").innerHTML = "loading";'."\n".
			'    loadexternal(' . "\n" .
			'      "' . $filename . '",' . "\n" .
			'      "linkselect=true&filter="+value+"&field=' . $fld2 . $param . '",' . "\n" .
			'      "' . $fld2 . '",' . "\n" . //display
			'      ' . $func . ",\n" .
				'      aArgs,' . "\n" .
				'      sValue' . "\n" .
				'    )' . "\n" .
				'}' . "\n";
		}

		// add the value of the last field to the values array
		//$values[] = $this->getValue( $fld2 );

		// finalize the js to load the values
		if (!empty($jsAfter)) {
			$jsAfter .= " new Array(  ";
			foreach ($values as $value) {
				if (is_array($value)) {
					$jsAfter .= " new Array(  ";
					foreach ($value as $item) {
						$jsAfter .= "'" . addslashes($item) . "', ";
					}
					$jsAfter = substr($jsAfter, 0, -2) . "), ";
				} else {
					$jsAfter .= "'" . addslashes($value) . "', ";
				}
			}

			$jsAfter = substr($jsAfter, 0, -2) . "));\n\n";

			$this->_setJS($jsAfter, 0, 0);
		}

		// set the js..
		$this->_setJS($js, false);
	}

	/**
	 * FormHandler::setDynamicOptions()
	 *
	 * Static: Make a javascript array of the given php array. This is
	 * used for dynamic select fields
	 *
	 * @param array $options: the new options for the select field
	 * @return null
	 * @access public
	 * @author Teye Heimans
	 */
	public function setDynamicOptions($options, $useArrayKeyAsValue = true) {

		$output = 'var options = Array(' . " \n";

		// generate a javascript array from the given array
		foreach ($options as $key => $value) {
			$key = $useArrayKeyAsValue ? $key : $value;
			$output .= '  Array("' . addslashes($key) . '", "' . addslashes($value) . '"),' . "\n";
		}

		$output = substr($output, 0, -2);
		$output .= "\n);\n";

		echo $output;
	}

	/**
	 * FormHandler::getTitle()
	 *
	 * Return the title of the given field name
	 *
	 * @param string $sField: The fieldname where to retrieve the title from
	 * @return string
	 * @access public
	 * @author Teye Heimans
	 */
	public function getTitle($sField) {
		// check if the field exists
		if (isset($this->_fields[$sField]) && is_object($this->_fields[$sField][1])) {
			// check if the field is a child of the "field" class
			if (is_subclass_of($this->_fields[$sField][1], 'field')) {
				// return the title
				return $this->_fields[$sField][0];
			} else {
				// print an error message
				$sClass = strtolower(get_class($this->_fields[$sField][1]));
				trigger_error(
					'Error, cannot retrieve title of this kind of field: ' . $sClass,
					E_USER_WARNING
				);
			}
		}
		// the given field does not exists!
		else {
			trigger_error(
				'Could not find field "' . $sField . '"',
				E_USER_WARNING
			);
		}

		return null;
	}

	/**
	 * FormHandler::getLanguage()
	 *
	 * Return the language used for the form
	 *
	 * @return string: the language
	 * @access public
	 * @author Teye Heimans
	 */
	public function getLanguage() {
		return $this->_lang;
	}

	/**
	 * FormHandler::fieldExists()
	 *
	 * Check if the field exists in the form
	 *
	 * @param string $sField: The field to check if it exists in the form or not
	 * @return boolean
	 * @access public
	 * @author Teye Heimans
	 */
	public function fieldExists($sField) {
		return array_key_exists($sField, $this->_fields);
	}

	/**
	 * FormHandler::getFormName()
	 *
	 * Return the name of the form
	 *
	 * @return string: the name of the form
	 * @access public
	 * @author Teye Heimans
	 */
	public function getFormName() {
		return $this->_name;
	}

	/**
	 * FormHandler::getJavascriptCode()
	 *
	 * Return the needed javascript code for this form
	 *
	 * @param bool $header: returns the javascript code for in the header (otherwise the javascript code which hase to be beneath the form will be returned)
	 * @return string: the needed javascript code for this form
	 * @access public
	 * @author Teye Heimans
	 *
	 * @since 17-08-2009 removed static before $return in order to handle multiple forms on a page. JW
	 */
	public function getJavascriptCode($header = true) {
		$return = array(0 => false, 1 => false);

		$s = $header ? 0 : 1;

		// if the javascript is not retrieved yet..
		if (!$return[$s]) {
			// generate the js "files" script
			$result = '';
			if (isset($this->_js[$s]['file']) && is_array($this->_js[$s]['file'])) {
				foreach ($this->_js[$s]['file'] as $line) {
					$result .= '<script type="text/javascript" src="' . $line . '"></script>' . "\n";
				}
			}
			// generate the other js script
			if (isset($this->_js[$s]['code']) && is_array($this->_js[$s]['code'])) {
				$result .= '<script type="text/javascript">' . "\n";
				foreach ($this->_js[$s]['code'] as $code) {
					$result .= $code;
				}
				$result .= "</script>\n";
			}

			$return[$s] = true;
			return $result;
		}

		return '';
	}

	/**
	 * FormHandler::getAsMailBody()
	 *
	 * Returns the values of the form as mail body
	 *
	 * @param string $mask: The mask which should be used for creating the mail body
	 * @return string
	 * @access public
	 * @author Teye Heimans
	 * @since 25/11/2005
	 */
	public function getAsMailBody($mask = null) {
		// TODO
		// replacement of %field% and of %{fieldname}%
		$loader = new MaskLoader();
		$loader->setMask($mask);

		// create the search and replace strings
		$search = array();
		$replace = array();

		// walk all elements in this form
		$mail = '';
		foreach ($this->_fields as $name => $fld) {
			// only use it in the mail if it has a view value (the fields)
			if (is_object($fld[1]) && method_exists($fld[1], 'getViewValue') && $name != $this->_name . '_submit') {
				// search and replace the %field% %value% items
				$loader->setSearch(array('/%field%/', '/%value%/'));

				$mail .= $loader->fill(
					array(
						$name,
						$fld[1]->getViewValue(),
					)
				);

				// add the %{fieldname}% seach item to the search string for later...
				$search[] = '/%' . $name . '%/';
				$replace[] = $fld[1]->getViewValue();
			}
		}

		// add the user added values to the search and replace arrays
		foreach ($this->_add as $name => $value) {
			$search[] = '/%' . $name . '%/';
			$replace[] = $value;
		}

		$loader->setSearch($search);

		// check if there is still something to fill
		if (!$loader->isFull()) {
			$mail .= $loader->fill($replace, -1);
		}

		// get possible half filled mask
		$mail .= $loader->fill();

		return $mail;
	}

	/**
	 * FormHandler::resizeImage()
	 *
	 * Resize the image uploaded in the given field
	 *
	 * @param string $field: The field where the image is uploaded
	 * @param string $saveAs: How the image has to be saved (if not given, the original wil be overwritten)
	 * @param int $maxWidth: The maximum width of the resized image
	 * @param int $maxHeight: the maximum height of the resized image
	 * @param int $quality: the quality of the resized image
	 * @param bool $constrainProportions: Keep the proportions when the image is resized?
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function resizeImage($field, $saveAs = null, $maxWidth = null, $maxHeight = null, $quality = null, $constrainProportions = true) {
		require_once FH_INCLUDE_DIR . 'includes/class.ImageConverter.php';

		// is gd enabled ?
		if (!ImageConverter::GDVersion()) {
			trigger_error(
				'Error! To use the function resizeImage you have to enable GD Libary!',
				E_USER_WARNING
			);
			return;
		}

		// set some default vars if none given
		if (is_null($maxWidth)) {
			$maxWidth = FH_DEFAULT_RESIZE_WIDTH;
		}

		if (is_null($maxHeight)) {
			$maxHeight = $maxWidth;
		}

		// save the settings
		$this->_convert[$field]['resize'][] = array($saveAs, $maxWidth, $maxHeight, $quality, $constrainProportions);
	}

	/**
	 * FormHandler::mergeImage()
	 *
	 * Merge a image uploaded in the given field with another image
	 *
	 * @param string $field: The field where the image is uploaded
	 * @param string $merge: The image which we should merge
	 * @param int $align: The align of the merge image (eg: left, center, right)
	 * @param int $valign: The vertical align of the merge image( eg: top, middle, bottom)
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function mergeImage($field, $merge, $align = 'center', $valign = 'bottom', $transparantColor = null) {
		require_once FH_INCLUDE_DIR . 'includes/class.ImageConverter.php';

		// is gd enabled ?
		if (!ImageConverter::GDVersion()) {
			trigger_error(
				'Error! To use the function mergeImage you have to enable GD Libary!',
				E_USER_WARNING
			);
			return;
		}

		// save the settings
		$this->_convert[$field]['merge'][] = array($merge, $align, $valign, $transparantColor);
	}

	/**
	 * FormHandler::checkPassword()
	 *
	 * Preform a password check on 2 password fields:
	 * - both values are the same
	 * - the values are longer then a minimum length (configured in the config file)
	 * - on an add-form, the fields are required
	 * - on an edit-form, the fields can be left empty, and the old password will stay (no changes will take place)
	 *
	 * @param string $field1: The first password field we should check
	 * @param string $field2: The second password field we should check
	 * @param boolean $setEditMsg: Should a message beeing displayed in an edit form that when leaving the fields blank the current passwords will be kept?
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	public function checkPassword($field1, $field2, $setEditMsg = true) {
		// check if the fields exists and that they are both passfields
		if (!$this->fieldExists($field1) || !$this->fieldExists($field2) ||
			strtolower(get_class($this->_fields[$field1][1])) != 'passfield' ||
			strtolower(get_class($this->_fields[$field2][1])) != 'passfield') {
			trigger_error('Error: unknown field used in checkPassword!');
			return;
		}

		// add some text to notify the user that he only has to enter his
		// password when he wants to change it
		if (isset($this->edit) && $this->edit && $setEditMsg) {
			$this->_fields[$field1][1]->setPre($this->_text(25));
		}

		// is the form posted and this page is posted in case of mulitple page form.
		if ($this->isPosted() && ($this->getPage() == $this->getCurrentPage())) {
			// let passfield 1 check if it matches passfield 2
			$this->_fields[$field1][1]->checkPassword($this->_fields[$field2][1]);
		}
	}

	/**
	 * FormHandler::isPosted()
	 *
	 * If the form is posted
	 *
	 * @return boolean: if the form is posted or not
	 * @access public
	 * @author Teye Heimans
	 */
	public function isPosted() {
		return $this->_posted;
	}

	/**
	 * FormHandler::isCorrect()
	 *
	 * Return if the form is filled correctly (for the fields which are set!)
	 *
	 * @return boolean: the form values valid or not
	 * @access public
	 * @author Teye Heimans
	 */
	public function isCorrect() {
		if (!_global) {
			global $_POST;
		}

		$result = true;

		foreach ($this->_fields as $id => $data) {
			// check if the fields are valid
			if (is_object($this->_fields[$id][1]) && method_exists($this->_fields[$id][1], 'isvalid') && $this->_fields[$id][1]->isValid() !== true) {
				// the field is not valid. If the focus is not set yet, set the focus to this field
				if (is_null($this->_focus)) {
					$this->setFocus($id);
				}
				$result = false;

			}
			// if multiple pages are used, only make sure that
			// all pages untill the current page are correct
			else if ($data[0] == '__PAGE__' && $this->_curPage == $data[1]) {
				break;
			}
		}

		return $result;
	}

	/**
	 * FormHandler::flush()
	 *
	 * Prints or returns the form
	 *
	 * @return string: the form or null when the form should be printed
	 * @access public
	 * @author Teye Heimans
	 */
	public function flush($return = false) {
		if ($this->_ajaxValidator === true) {
			require_once FH_INCLUDE_DIR . 'includes/class.AjaxValidator.php';
			$oAjaxValidator = new AjaxValidator($this->_ajaxValidatorScript);
			$oAjaxValidator->CreateObservers($this);
		}

		// when the form is not posted or the form is not valid
		if (!$this->isPosted() || !$this->isCorrect()) {
			// check if a value is set of an unknown field
			if (sizeof($this->_buffer) > 0) {
				// error messages for the values for unknown fields
				foreach ($this->_buffer as $sField => $a) {
					trigger_error('Value set of unknown field "' . $sField . '"', E_USER_WARNING);
				}
			}

			// get the form
			$form = $this->_getForm();
		}
		// when the form is not totaly completed yet (multiple pages)
		else if ($this->_curPage < $this->_pageCounter) {
			// upload and convert uploads
			$this->_handleUploads();

			// get the next form
			$form = $this->_getForm($this->_curPage + 1);
		}
		// form in view mode
		elseif ($this->isViewMode() == true) {
			$form = $this->_getForm();
		}
		// when the form is valid
		else {
			// upload and convert uploads
			$this->_handleUploads();

			// generate the data array
			$data = array();
			foreach ($this->_fields as $name => $fld) {
				if (is_object($fld[1]) && method_exists($fld[1], 'getValue') && $name != $this->_name . '_submit') {
					$data[$name] = $fld[1]->getValue();
				}
			}

			// add the user added data to the array
			$data = array_merge($data, $this->_add);

			// call the users oncorrect function
			if (!empty($this->_onCorrect)) {
				if (is_array($this->_onCorrect)) {
					$hideForm = call_user_func_array(array(&$this->_onCorrect[0], $this->_onCorrect[1]), array($data, &$this));
				} else {
					$hideForm = call_user_func_array($this->_onCorrect, array($data, &$this));
				}
			}

			// add the user added data again to the array (could have been changed!)
			$data = array_merge($data, $this->_add);

			// display the form again if wanted..
			if (isset($hideForm) && $hideForm === false) {
				$form = $this->_getForm();
			}
			// the user want's to display something else..
			else if (isset($hideForm) && is_string($hideForm)) {
				$form = $hideForm;
			}
			// dont display the form..
			else {
				$form = '';
			}
		}

		// cache all the fields values for the function value()
		foreach ($this->_fields as $fld => $value) {
			// check if it's a field
			if (is_object($this->_fields[$fld][1]) && method_exists($this->_fields[$fld][1], "getvalue")) {
				$this->_cache[$fld] = $this->_fields[$fld][1]->getValue();
			}
		}

		/*
			// remove all vars to free memory
			$vars = get_object_vars($this);
			foreach( $vars as $name => $value )
			{
			// remove all vars except these..
			if( !in_array($name, array( '_cache', 'edit', 'insert', '_posted', '_name' ) ) )
			{
			unset( $this->{$name} );
			}
			}
		*/

		// disable our error handler!
		if (FH_DISPLAY_ERRORS) {
			restore_error_handler();
		}

		// return or print the form
		if ($return) {
			return $form;
		} else {
			echo $form;
			return null;
		}
	}

	/********************************************************/
	/************* BELOW IS ALL PRIVATE!! *******************/
	/********************************************************/

	/**
	 * FormHandler::_getNewButtonName()
	 *
	 * when no button name is given, get a unique button name
	 *
	 * @access private
	 * @return string: the new unique button name
	 * @author Teye Heimans
	 */
	private function _getNewButtonName() {
		static $counter = 1;

		return 'button' . $counter++;
	}

	/**
	 * FormHandler::_setJS()
	 *
	 * Set the javascript needed for the fields
	 *
	 * @param string $js: The javascript to set
	 * @param boolean $isFile: Is the setted javascript a file?
	 * @param boolean $before: should the javascript be placed before or after the form?
	 * @return void
	 * @access private
	 * @author Teye Heimans
	 */
	public function _setJS($js, $isFile = false, $before = true) {
		$this->_js[$before ? 0 : 1][$isFile ? 'file' : 'code'][] = $js;
	}

	/**
	 * FormHandler::_text()
	 *
	 * Return the given text in the correct language
	 *
	 * @param int $index: the index of the text in the textfile
	 * @return string: the text in the correct language
	 * @access private
	 * @author Teye Heimans
	 */
	public function _text($iIndex) {

		// is a language set?
		if (!isset($this->_text) || !is_array($this->_text)) {
			trigger_error('No language file set!', E_USER_ERROR);
			return false;
		}

		// does the index exists in the language file ?
		if (!array_key_exists($iIndex, $this->_text)) {
			trigger_error('Unknown index ' . $iIndex . ' to get language string!', E_USER_NOTICE);
			return '';
		}

		// return the language string
		return $this->_text[$iIndex];
	}

	/**
	 * FormHandler::_registerField()
	 *
	 * Register a field or button at FormHandler
	 *
	 * @param string $name: The name of the field (or button)
	 * @param object $field: The object of the field or button
	 * @return string $title: The titlt of the field. Leave blank for a button
	 * @access private
	 * @author Teye Heimans
	 */
	protected function _registerField($name, &$field, $title = null) {
		// if no title is known then its a button..
		if ($title === null) {
			$title = '__BUTTON__';
		}

		$this->_fields[$name] = array($title, &$field);
		return $field;
	}

	/**
	 * FormHandler::_registerFileName()
	 *
	 * Register the filenames which upload fields are using for there
	 * uploaded file so that other upload fields cannot use these.
	 * This is to prevent double filenames assumed by the upload fields
	 *
	 * @param string $sFilename: the filename to register
	 * @param string $sField: the field who is registering the file
	 * @return bool: false if the filename is already registered, true otherwise
	 * @access private
	 * @author Teye Heimans
	 */
	public function _registerFileName($sFilename, $sField) {
		static $aFilenames = array();

		// is the filename already registerd ?
		if (isset($aFilenames[$sFilename]) && $aFilenames[$sFilename] != $sField) {
			return false;
		}

		// file name is still free.. register it and return true
		$aFilenames[$sFilename] = $sField;
		return true;
	}

	/**
	 * FormHandler::_handleUploads()
	 *
	 * Private: method to handle the uploads and image convertions
	 *
	 * @return void
	 * @access public
	 * @author Teye Heimans
	 */
	protected function _handleUploads() {
		// upload the uploaded files
		foreach ($this->_upload as $name) {
			$this->_fields[$name][1]->doUpload();
		}

		// walk all convert actions for the upload fields
		foreach ($this->_convert as $field => $convertions) {
			// walk all convertions for this field
			foreach ($convertions as $action => $data) {
				// check if the field is an upload field and that there is a file uploaded
				if (in_array($field, $this->_upload)) {
					// is the file uploaded ?
					if ($this->_fields[$field][1]->isUploaded()) {
						// get the file which is uploaded
						$file =
						$this->_fields[$field][1]->getSavePath() .
						$this->_fields[$field][1]->getValue();

						// check if the file exitst
						if (!file_exists($file)) {
							trigger_error("Error! Could not find uploaded file $file!", E_USER_WARNING);
							unset($file);
							continue;
						}
					}
					// the uploadfield is not uploaded yet... do nothing.
					else {
						// go to the next field
						continue 2;
					}
				}
				// it's not a uploadfield... is it an image ?
				else if (file_exists($field)) {
					$file = $field;
					// unknown field or file!
				} else {
					trigger_error('Could not find field or file to convert: ' . $field, E_USER_WARNING);
					continue;
				}

				// do the convert actions with the image (when the uploaded file is a jpg or png!)
				if (isset($file) && in_array(strtolower(substr($file, -4)), array('.jpg', '.png', 'jpeg', '.gif'))) {
					// create a new image converter
					$img = new ImageConverter($file);

					// stop when a error occoured
					if ($img->getError()) {
						trigger_error($img->getError(), E_USER_WARNING);
						unset($img);
						continue;
					}

					// walk each data (there can be more of the save convertions on the save file!)
					foreach ($data as $info) {
						// check if an error occured
						if ($img->getError()) {
							// stop converting and notice the user
							trigger_error($img->getError(), E_USER_WARNING);
							break;
						}

						switch ($action) {
						// merge the uploaded file with a merge image
						case 'merge':
							list($stamp, $align, $valign, $transparant) = $info;
							$img->doMerge($stamp, $align, $valign, $transparant);
							break;

						// resize the uploaded file
						case 'resize':
							list($destination, $maxX, $maxY, $quality, $proportions) = $info;
							if (empty($destination)) {
								$destination = $file;
							}

							$img->setQuality($quality);
							$img->setConstrainProportions($proportions);
							$img->doResize($destination, $maxX, $maxY);
							break;
						}
					}

					unset($img);
				}

				unset($file);
			}
		}
	}

	/**
	 * FormHandler::_getForm()
	 *
	 * Private: get the form
	 *
	 * @return string: the generated form
	 * @access public
	 * @author Teye Heimans
	 */
	protected function _getForm($iDisplayPage = null) {
		// is no specific page requested, then get the "current" page
		if (is_null($iDisplayPage)) {
			$iDisplayPage = $this->_curPage;
		}

		// make sure that the requested page cannot be negative
		if ($iDisplayPage <= 0) {
			$iDisplayPage = 1;
		}

		// set the tab indexes for the fields...
		ksort($this->_tabindexes);
		foreach ($this->_tabindexes as $index => $field) {
			// check if the field exists in the form ?
			if ($this->fieldExists($field)) {
				// set the tab index
				$this->_fields[$field][1]->setTabIndex($index);
			}
			// tab index set for unknown field... trigger_error
			else {
				trigger_error(
					'Error, try to set the tabindex of an unknown field "' . $field . '"!',
					E_USER_NOTICE
				);
			}
		}

		// set the focus to the first (tab index) field if no focus is set yet
		if ($this->_focus === true) {
			// are there tab indexes set ?
			if (sizeof($this->_tabindexes) > 0) {
				// set the focus to the element with the lowest positive tab index
				foreach ($this->_tabindexes as $key => $field) {
					if ($key >= 0 && $this->setFocus($field)) {
						break;
					}
				}

			}

			// no focus set yet. Set the focus to the first field
			if ($this->_focus === true) {
				// is it a object (only fields + buttons are objects)
				foreach ($this->_fields as $name => $data) {
					if (is_object($this->_fields[$name][1]) && $this->setFocus($name)) {
						break;
					}
				}

			}
		}

		// initialize the used vars
		$hidden = '';
		$form = '';
		$buffer = array();
		$repeat = true;
		$page = 1;

		// start a new mask loader
		$mask = new MaskLoader();

		// set the seach values
		$mask->setSearch(
			array(
				'/%field%/',
				'/%error%/',
				'/%title%/',
				'/%seperator%/',
				'/%name%/',
				'/%error_id%/',
				'/%value%/',
				'/%help%/',
			)
		);

		// walk trought the fields array
		foreach ($this->_fields as $id => $field) {
			switch ($field[0]) {
			// multiple pages in this form
			case '__PAGE__':
				# why did we stop at the current page ?
				//if( $field[1] == $iDisplayPage)
				//{
				//    break;
				//}
				$page++;
				break;

			// hidden field
			case '__HIDDEN__':
				$hidden .= $field[1]->getField() . "\n";
				$hidden .= $field[1]->getError() . "\n";
				break;

			// new mask to set
			case '__MASK__':
				if (!isset($this->_mask) || is_null($this->_mask) || $page == $iDisplayPage) {
					list($this->_mask, $repeat) = $field[1];
				}
				break;

			// insert html or a line
			case '__HTML__':
			case '__LINE__':
				// but only if the html or line is on this page!
				if ($page == $iDisplayPage) {
					$form .= $field[1];
				}
				break;

			// begin new fieldset
			case '__FIELDSET__':
				if ($page == $iDisplayPage) {
					array_unshift($field[1], $form);
					array_push($buffer, $field[1]);
					$form = '';
				}
				break;

			// end new fieldset
			case '__FIELDSET-END__':
				if ($page == $iDisplayPage) {
					if (sizeof($buffer) > 0) {
						$d = array_pop($buffer);
						$form = $d[0] .
						str_replace(
							array('%name%', '%caption%', '%content%', '%extra%'),
							array($d[1], $d[2], $form, $d[3]),
							FH_FIELDSET_MASK
						);
					} else {
						trigger_error('Fieldset is closed while there is not an open fieldset!');
					}
				}
				break;

			// default action: field or button
			default:
				// the fields are not displayed in this page..
				// set them as hidden fields in the form
				if ($page != $iDisplayPage) {
					// put the data of the field in a hidden field
					// buttons are just ignored
					if ($field[0] != '__BUTTON__') {
						// create a new hidden field to set the field's value in
						$h = new HiddenField($this, $id);
						$value = $field[1]->getValue();
						$h->setValue(is_array($value) ? implode(', ', $value) : $value);
						$hidden .= $h->getField() . "\n";
						unset($h);
					}
				}
				// the field is on the current page of the form
				else {
					// set the mask which should be filled
					$mask->setMask($this->_mask);

					// easy names for the data
					$title = $field[0];
					$obj = &$field[1];
					$name = $id;

					// buttons don't have a title :-)
					if ($title == '__BUTTON__') {
						$title = '';
					}

					/**
					 * From this point, we are collecting the data
					 * to fill the mask.
					 */

					// Get the field or button value
					// can we get a field ?
					if (is_object($obj) && method_exists($obj, 'getField')) {
						$fld = $obj->getField();
					}
					// can we get a button ?
					else if (is_object($obj) && method_exists($obj, 'getButton')) {
						$fld = $obj->getButton();
					}
					// ai, not a field and not a button..
					else {
						// trigger error ?? (TODO)
						$fld = '';
					}

					// escape dangerous characters
					$fld = str_replace('%', '____FH-percent____', $fld);

					/**
					 * Get the error message for this field
					 */

					// get possible error message
					$error = '';
					if ($this->_displayErrors && is_object($obj) && method_exists($obj, 'getError')) {
						// custom error message set and we got an error?
						if (array_key_exists($name, $this->_customMsg) && $obj->getError() != '') {
							// use the default error mask ?
							if ($this->_customMsg[$name][1]) {
								$error = sprintf(FH_ERROR_MASK, $name, $this->_customMsg[$name][0]);
							}
							// dont use the default error mask...
							else {
								$error = $this->_customMsg[$name][0];
							}
						}
						// dont use a custom error message.. just get the FH error message
						else {
							$error = $obj->getError();
						}
					}

					// save the error messages
					// (when the user wants to use his own error displayer)
					$this->errors[$name] = $error;

					/**
					 * Get the value for of the field
					 */
					$value = '';
					if (is_object($obj) && method_exists($obj, 'getValue')) {
						if (is_array($obj->getValue())) {
							$value = implode(', ', $obj->getValue());
						} else {
							$value = $obj->getValue();
						}
					}

					/**
					 * Get the help string
					 */
					$help = '';
					if (array_key_exists($name, $this->_help) && !$this->isViewMode() && !$this->isFieldViewMode($name)) {
						if (strpos(FH_HELP_MASK, '%s')) {
							$help = sprintf(
								FH_HELP_MASK,
								$this->_help[$name][0]
								);

						} else {
							$help = str_replace( '%helptext%',	$this->_help[$name][0] , FH_HELP_MASK);
						}
					}

					// give the field a class error added 25-08-2009 in order to give the field the error mask
					if ($this->isPosted() == true AND $error != '') {
						$fld = $this->parse_error_Fieldstyle($fld);
					}

					// now, put all the replace values into an array
					$replace = array(
						/* %field%     */$fld,
						/* %error%     */$error,
						/* %title%     */!empty($title) ? $title : "",
						/* %seperator% */(!strlen($title) ? '' : ':'),
						/* %name%      */(!empty($name) ? $name : ''),
						/* %error_id%  */(!empty($name) ? 'error_' . $name : ''),
						/* %value%     */$value,
						/* %help%      */$help,
					);

					// fill the mask
					$html = $mask->fill($replace);

					// added 07-01-2009 in order to specify which element should get the error class
					if ($this->isPosted() == true AND $error != '') {
						$html = $this->parse_error_style($html);
					} else {
						$html = str_replace('%error_style%', '', $html);
					}

					// is the mask filled ?
					if ($html) {
						// add it the the form HTML
						$form .= str_replace('____FH-percent____', '%', $html);

						// if we don't have to repeat the current mask, use the original
						if (!$repeat) {
							$this->_mask = FH_DEFAULT_ROW_MASK;
						}
						// if we have to repeat the mask, repeat it and countdown
						else if (is_numeric($repeat)) {
							$repeat--;
						}
					}
				}
				break;
			}
		}

		// add the page number to the forms HTML
		if ($this->_pageCounter > 1) {
			$h = new HiddenField($this, $this->_name . '_page');
			$h->setValue($iDisplayPage);
			$hidden .= $h->getField() . "\n";
			unset($h);
		}

		// get a possible half filled mask and add it to the html
		$form .= str_replace('____FH-percent____', '%', $mask->fill(null));

		// delete the mask loader
		unset($mask);

		// get occured PHP errors
		$errors = catchErrors();
		$errmsg = '';

		// walk all error messages
		foreach ($errors as $error) {
			switch ($error['no']) {
			case E_USER_WARNING:$type = 'Warning';
				break;
			case E_USER_NOTICE:$type = 'Notice';
				break;
			case E_USER_ERROR:$type = 'Error';
				break;
			default:$type = 'Warning (' . $error['no'] . ')';
				break;
			}
			$errmsg .= "<b>" . $type . ":</b> " . basename($error['file']) . " at " . $error['line'] . " " . $error['text'] . "<br />\n";
		}

		// set the javascript needed for setting the focus
		if ($this->_focus) {
			$this->_setJS(
				"// set the focus on a specific field \n" .
				"var elem = document.getElementById ? document.getElementById('" . $this->_focus . "'): document.all? document.all['" . $this->_focus . "']: false; \n" .
				"if( (elem) && (elem.type != 'hidden')) {\n" .
				"    try {\n" .
				"      elem.focus();\n" .
				"    } catch(e) {}\n" .
				"}\n", 0, 0
			);
		}

		// NOTE!!
		// DO NOT REMOVE THIS!
		// You can remove the line "This form is generated by FormHandler" in the config file!!
		// DONT REMOVE THE HTML CODE BELOW! Just set FH_EXPOSE to FALSE!
		$sHeader =
		$errmsg .
		"<!--\n" .
		"  This form is automaticly being generated by FormHandler v3.\n" .
		"  See for more info: http://www.formhandler.net\n" .
		"  This credit MUST stay intact for use\n" .
		"-->\n" .
		$this->getJavascriptCode(true) .
		'<form id="' . $this->_name . '" method="post" action="' . htmlentities($this->_action, null, FH_HTML_ENCODING) . '"' .
			(sizeof($this->_upload) > 0 ? ' enctype="multipart/form-data"' : '') .
			(!empty($this->_extra) ? " " . $this->_extra : "") . ">\n" .
			'<ins>' . $hidden . '</ins>' ;
		$sFooter = (FH_EXPOSE ?
			"<p class='atribucion'><span>" .
			'This form is generated by </span><a href="https://www.fh3.nl/news.html" >' .
			'<span><strong>FormHandler</strong></span></a></p>' . "\n" : ''
		) .
		"</form>\n" .
		"<!--\n" .
		"  This form is automaticly being generated by FormHandler v3.\n" .
		"  See for more info: https://www.fh3.nl/news.html\n" .
		"-->" . $this->getJavascriptCode(false);

		$search = array('%header%', '%footer%');
		$replace = array($sHeader, $sFooter);

		$new_form = str_replace($search, $replace, $form, $num_replaced);

		if ($num_replaced === 2) {
			return $new_form;
		} else {
			return $sHeader . $form . $sFooter;
		}
	}
}