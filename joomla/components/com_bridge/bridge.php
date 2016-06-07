<?php
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the base controller

require_once( JPATH_COMPONENT.DS.'controller.php' );
//load shared functionality from admin backend
require_once( JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_bridge'.DS.'BridgeComponentHelper.php' );


JFactory::getDocument()->addScript('/components/com_bridge/js/jquery-1.5.1.js');
// Require specific controller if requested
if($controller = JRequest::getWord('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        require_once $path;
    } else {
        $controller = '';
    }
}
//make sure that we don't return template stuff when json is returned
BridgeComponentHelper::switchRawMode();
//load js and css
    BridgeComponentHelper::loadBridgeAssets();
// Create the controller
$classname    = 'BridgeController'.$controller;
$controller   = new $classname(array(
'default_task' => 'browse') );


// Perform the Request task
$controller->execute( JRequest::getVar( 'task') );

// Redirect if set by the controller
$controller->redirect();

?>
