<?php

class Pages_ViewPublic_Page_View extends XFCP_Pages_ViewPublic_Page_View
{
	
	protected static $_templateFallbacks = array();
	
	public function renderHtml()
	{
		$options 	= XenForo_Application::get('options');
		$languageId = XenForo_Visitor::getInstance()->get('language_id');
		
		if ($languageId == 0)
		{
			$languageId = $options->defaultLanguageId;
		}
		
		$templateName = '_page_node.' . $this->_params['page']['node_id'] . '.' . $languageId;
		$this->_params['templateHtml'] = $this->createTemplateObject(
			$templateName,
			$this->_params
		);
		
		if ($languageId != $options->defaultLanguageId)
		{
			$templateFallbackName = '_page_node.' . $this->_params['page']['node_id'] . '.' . $options->defaultLanguageId;
			$this->_params['templateHtmlFallback'] = $this->createTemplateObject(
				$templateFallbackName,
				$this->_params
			);
			
			self::$_templateFallbacks[$templateName] = $templateFallbackName;
			
			XenForo_CodeEvent::addListener('template_post_render', array($this, 'postRenderTemplate'));
		}
	}
	
	public function postRenderTemplate($templateName, &$content, array &$containerData, XenForo_Template_Abstract $template)
	{
		if (empty($content) AND isset(self::$_templateFallbacks[$templateName]))
		{
			$fallback = new XenForo_Template_Public(self::$_templateFallbacks[$templateName], $template->getParams());
			$content = $fallback->render();
		}
	}

}