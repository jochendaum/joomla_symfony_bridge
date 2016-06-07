<?php
/**
* @version		2.5
* @package		Ignite Gallery
* @copyright	Copyright (C) 2010 Matthew Thomson. All rights reserved.
* @license		GNU/GPLv2
*/
defined('_JEXEC') or die('Restricted access');

/*Version check, no more PHP4! */
$phpVersion = phpversion();
$firstDigit = intval( substr($phpVersion, 0, 1) );
if($firstDigit < 5)
{
    JError::raise(2, 500, 'PHP Version: '.$phpVersion.', PHP 5 Required, Please See Common Support Questions' );
    return;
}
else
{
    require_once(JPATH_COMPONENT.DS.'controller.php');
    require_once(JPATH_COMPONENT.DS.'BridgeComponentHelper.php' );
    
    //make sure that we don't return template stuff when json is returned
	BridgeComponentHelper::switchRawMode();
    //load js and css
    BridgeComponentHelper::loadBridgeAssets();
	
    $controller = new adminController();
    $controller->execute('default');
    $controller->redirect(); 
}
?>