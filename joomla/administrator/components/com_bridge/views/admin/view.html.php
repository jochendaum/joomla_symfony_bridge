<?php
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class adminViewadmin extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		JToolBarHelper::title( JText::_( 'NZGBC Membership/ Individual system' ), 'generic.png' );
		JToolBarHelper::preferences('com_bridge', 500);

		$return = BridgeComponentHelper::requestAndFollow('administrator/');

		$this->assign('symfony', $return);
		parent::display($tpl);
	}

	function readHeader($ch, $header){
		if($pos = strpos($header, ':')){
			$this->headers[substr($header, 0, $pos)] = substr(strstr($header, ':'), 1);
      	}

      	return strlen($header);
    }
}
