<?php
defined('_JEXEC') or die;

// control center 
// ToDo:: rename to rsg_control and use as default ...

global $Rsg2DebugActive;

$Rsg2DebugActive = false; // ToDo: $rsgConfig->get('debug');
if ($Rsg2DebugActive)
{
	// Include the JLog class.
	jimport('joomla.log.log');
	
	// identify active file
	JLog::add('==> ctrl.ctrl.rsgallery2.php ');
}

jimport('joomla.application.component.controllerform');

// ToDo: Check which functions are used

class Rsgallery2ControllerRsgallery2 extends JControllerForm
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	}



}

