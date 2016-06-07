<?php

/**
* @version
* @package		Joomla
* @copyright
* @license			GNU/GPL, see LICENSE.php
*
* @author			automatem
* @package		Joomla
* @subpackage	System
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgSystemLoadSymfony extends JPlugin {

    public function onAfterInitialise(){
	}

    function onAfterRoute(){
        $nonsense = '';
    }

    public function onAfterDispatch(){

        global $mainframe;
        $document	=& JFactory::getDocument();
        $doctype	= $document->getType();

        if ($mainframe->isAdmin() || $doctype == 'raw') {
			return;
		}
		$body = $document->getBuffer(null, 'component');
		if (is_array($body['component'])){
            $body = $body['component'][''];
		}else{
		    $body = '';
		}
		$mappings = $this->params->get('mappings');
		$mapArray = explode(',',$mappings);

		foreach ($mapArray as $entry){

		    list ($replace, $uri) = explode('-',$entry);
		    $replace = trim($replace);
		    $uri = trim($uri);
		    if (strstr($body,$replace)){
                require_once( JPATH_BASE.DS.'components'.DS.'com_bridge'.DS.'controller.php' );
                require_once( JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_bridge'.DS.'BridgeComponentHelper.php' );
                require_once( JPATH_BASE.DS.'components'.DS.'com_bridge'.DS.'views'.DS.'nzgbc_member'.DS.'view.html.php' );

		        BridgeComponentHelper::loadBridgeAssets();
		    }
		}
    }

    public function onAfterRender(){

    	global $mainframe;

		$document	=& JFactory::getDocument();
		$doctype	= $document->getType();

        if ($mainframe->isAdmin() || $doctype == 'raw') {
			return;
		}

		$body = JResponse::getBody();

		$mappings = $this->params->get('mappings');
		$mapArray = explode(',',$mappings);

		foreach ($mapArray as $entry){

		    list ($replace, $uri) = explode('-',$entry);

		    $replace = trim($replace);
		    $uri = trim($uri);

		    if (strstr($body,$replace)){

    		    if (strstr($uri,'{PERSON_ID}')){
    		        $db =& JFactory::getDBO();
    		        $user =& JFactory::getUser();
    		        $db->setQuery('select id from person where joomla_user_id='.$user->id);
    		        $uri = str_replace('{PERSON_ID}',intval($db->loadResult()),$uri);
    		    }


		        require_once( JPATH_BASE.DS.'components'.DS.'com_bridge'.DS.'controller.php' );
                require_once( JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_bridge'.DS.'BridgeComponentHelper.php' );
                require_once( JPATH_BASE.DS.'components'.DS.'com_bridge'.DS.'views'.DS.'nzgbc_member'.DS.'view.html.php' );

                BridgeComponentHelper::loadBridgeAssets();

                // Require specific controller if requested
                if($controller = JRequest::getWord('controller')) {
                    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
                    if (file_exists($path)) {
                        require_once $path;
                    } else {
                        $controller = '';
                    }
                }


                // Create the controller
                $classname    = 'BridgeController'.$controller;
                $controller   = new $classname(array(
                'default_task' => 'browse') );

                $oldUri = JRequest::getVar('uri');
                $view = JRequest::getCmd('view',null);
    	        $id = JRequest::getInt('id',null);
    	        $layout= JRequest::getCmd('layout',null);
                $option= JRequest::getCmd('option',null);

                JRequest::setVar('uri', $uri);
                JRequest::setVar('jUser', $user->id);
                JRequest::setVar('view', null);
                JRequest::setVar('layout', null);
                JRequest::setVar('task', null);
                JRequest::setVar('option', 'com_bridge');
                JRequest::setVar('plugin', 1);

                // Perform the Request task
                ob_start();
        		$controller->execute( 'browse' );
                $pluginHtml = ob_get_contents();
        		ob_end_clean();

                JRequest::setVar('uri', $oldUri);
                JRequest::setVar('view', $view);
                JRequest::setVar('id', $id);
                JRequest::setVar('layout', $layout);
                JRequest::setVar('plugin', 0);

                //SOlve javascript conflicts between Dependent Select and mootools

                if (strstr($uri,'organisation/newMember') || strstr($uri,'/membership')){
                    $body = str_replace('/plugins/system/mtupgrade/mootools.js','',$body);
                    $body = str_replace('/media/system/js/caption.js','',$body);
                }else{
                    $body = str_replace('/components/com_bridge/sfDependentSelectPlugin/web/js/SelectDependiente.min.js','',$body);
                }

                // in any case remove rs form css
                $body = str_replace('/components/com_rsform/assets/css/front.css','',$body);

        		$body = str_replace($replace, $pluginHtml, $body);
                JResponse::setBody($body);

    		}
		}

    }




}

