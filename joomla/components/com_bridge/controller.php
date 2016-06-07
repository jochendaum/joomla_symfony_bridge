<?php
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

class BridgeController extends JController
{
    /**
     * Method to display the view
     *
     * @access    public
     */


	function display(){
        parent::display();
    }

    function browse() {

        $document =& JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );

		$view = & $this->getView( $viewName, $viewType, '',

		    array( 'template_path'=> JPATH_BASE.DS.'components'.DS.'com_bridge'.DS.'views'.DS.'bridge'.DS.'tmpl')
		);

		/*// Get/Create the model
		if ($model = & $this->getModel($viewName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}*/

		// Set the layout
		$view->setLayout('default');

		$view->display();

    }




}
?>
