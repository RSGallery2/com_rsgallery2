<?php
defined('_JEXEC') or die;

/*
global $Rsg2DebugActive;

if ($Rsg2DebugActive)
{
	// Include the JLog class.
	jimport('joomla.log.log');

	// identify active file
	JLog::add('==> ctrl.galleries.php ');
}
/**/

jimport('joomla.application.component.controlleradmin');

class Rsgallery2ControllerGalleries extends JControllerAdmin
{

	public function getModel($name = 'Gallery', 
 							 $prefix = 'Rsgallery2Model',
  							 $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}


	public function saveOrdering ()
	{
		//JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		$msg = "saveOrder: ";
		$msgType = 'notice';

		try{


			$input = JFactory::getApplication()->input;
			$orders = $input->post->get( 'order', array(), 'ARRAY');
			$ids = $input->post->get( 'ids', array(), 'ARRAY');

			$OutTxt = '';

			$msg .= "<br>" . "isset(orders): " . isset($orders);
			$msg .= "<br>" . "is_array(orders): " . is_array($orders);

			$msg .= "<br>" . "isset(ids): " . isset($ids);
			$msg .= "<br>" . "is_array(ids): " . is_array($ids);


			$CountOrders = count($ids);
			$msg .= "<br>" . "$CountOrders: " . $CountOrders;
			$CountIds = count($ids);
			$msg .= "<br>" . "$CountIds: " . $CountIds;


			$OutTxt = '';
			for ($idx = 0; $idx < $CountIds; $idx++) {
				$msg .= '<br>' . 'ID: ' . $ids[$idx] . ' ' . 'Order: ' . $orders[$idx];
			}
			$msg .= "<br>";




		}
		catch (RuntimeException $e)
		{
			$OutTxt = '';
			$OutTxt .= 'Error executing saveOrdering: "' . '<br>';
			$OutTxt .= 'Error: "' . $e->getMessage() . '"' . '<br>';

			$app = JFactory::getApplication();
			$app->enqueueMessage($OutTxt, 'error');
		}

		$msg .= '!!! Not implemented yet !!!';

		$this->setRedirect('index.php?option=com_rsgallery2&view=galleries', $msg, $msgType);
	}


	function saveOrder( &$cid ) {
		$mainframe =& JFactory::getApplication();
		$database = JFactory::getDBO();

		$total		= count( $cid );
		// $order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		$input = JFactory::getApplication()->input;
		$order = $input->post->get( 'order', array(), 'ARRAY');
//  JArrayHelper::toInteger($order, array(0));
		ArrayHelper::toInteger($order, array(0));
		$row 		= new rsgGalleriesItem( $database );

		$conditions = array();

		// update ordering values
		for ( $i=0; $i < $total; $i++ ) {
			$row->load( (int) $cid[$i] );
			$groupings[] = $row->parent;
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError(500, $mainframe->getErrorMsg());
				} // if
			} // if
		} // for

		// reorder each group
		$groupings = array_unique( $groupings );
		foreach ( $groupings as $group ) {
			$row->reorder('parent = '.$database->Quote($group));
		} // foreach

		// clean any existing cache files
		$cache =& JFactory::getCache('com_rsgallery2');
		$cache->clean( 'com_rsgallery2' );

		$msg 	= JText::_( 'COM_RSGALLERY2_NEW_ORDERING_SAVED' );
		$mainframe->enqueueMessage( $msg );
		$mainframe->redirect( 'index.php?option=com_rsgallery2&rsgOption=galleries');
	} // saveOrder

}

