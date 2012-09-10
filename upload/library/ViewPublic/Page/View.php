<?php

class Pages_ViewPublic_Page_View extends XFCP_Pages_ViewPublic_Page_View
{
	
	public function renderHtml()
	{
		$this->_params['templateHtml'] = $this->_params['page']['content'];
	}

}