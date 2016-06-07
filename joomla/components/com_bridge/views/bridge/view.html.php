<?php
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

class nzgbc_memberViewnzgbc_member extends JView{

	function display($tpl = null){

		$return = BridgeComponentHelper::requestAndFollow();

		$this->assign('symfony', $return);
		parent::display($tpl);
	}




}
?>
