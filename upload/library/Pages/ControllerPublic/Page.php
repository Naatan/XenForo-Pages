<?php

class Pages_ControllerPublic_Page extends XFCP_Pages_ControllerPublic_Page
{

	/**
	 * Gets the specified page or throws an error.
	 *
	 * @param string $nodeName
	 *
	 * @return array
	 */
	protected function _getPageOrError($nodeName)
	{
		$page = parent::_getPageOrError($nodeName);
		XenForo_Visitor::getInstance()->get('language_id');
		
		$title 			= new XenForo_Phrase('page_title_' . $page['node_id']);
		$description 	= new XenForo_Phrase('page_description_' . $page['node_id']);
		
		$page['title']			= $title->render();
		$page['description']	= $description->render();
		
		return $page;
	}

}