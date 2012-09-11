<?php

class Pages_ViewPublic_Page_View extends XFCP_Pages_ViewPublic_Page_View
{
	
	public function renderHtml()
	{
		$this->_params['templateHtml'] = $this->createTemplateObject(
			'_page_node.' . $this->_params['page']['node_id'] . '.' . XenForo_Visitor::getInstance()->get('language_id'),
			$this->_params
		);
	}

}