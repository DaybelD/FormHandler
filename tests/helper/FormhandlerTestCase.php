<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

$_SERVER['REQUEST_METHOD'] = 'POST';

// for testen trigger_error
define('FH_DISPLAY_ERRORS', false);

abstract class FormhandlerTestCase extends TestCase
{
    private ?ReflectionClass $_Reflector = null;

    abstract protected function getFormhandlerType() : string;

    private function getReflector() : ReflectionClass
    {
        if ($this->_Reflector === null)
            $this->_Reflector = new ReflectionClass($this->getFormhandlerType());
        return $this->_Reflector;
    }

    /**
 	 * getPrivateProperty
 	 *
 	 * @param FormHandler $form
 	 * @param string $propertyName
 	 * @return mixed
 	 */
      public function getPrivateProperty(FormHandler $form, string $propertyName)
      {
		$property = $this->getReflector()->getProperty($propertyName);
		$property->setAccessible(true);

		return $property->getValue($form);
    }
    
    /**
 	 * setPrivateProperty
 	 *
 	 * @param FormHandler $form
 	 * @param string $propertyName
 	 * @return mixed
 	 */
      public function setPrivateProperty(FormHandler $form, string $propertyName, $value) : void
      {
		$property = $this->getReflector()->getProperty($propertyName);
		$property->setAccessible(true);

		$property->setValue($form, $value);
    }
    
	/**
 	 * executePrivateMethod
 	 *
 	 * @param FormHandler $form
     * @param string $propertyName
     * @param array $params
 	 * @return mixed
 	 */
      public function executePrivateMethod(FormHandler $form, string $methodName, array $params)
      {
		$method = $this->getReflector()->getMethod($methodName);
		$method->setAccessible(true);

		return $method->invokeArgs($form, $params);
    }
};
