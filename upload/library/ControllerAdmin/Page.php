<?php

class Pages_ControllerAdmin_Page extends XFCP_Pages_ControllerAdmin_Page
{

	/**
	 * Name of the DataWriter that will handle this node type
	 *
	 * @var string
	 */
	protected $_nodeDataWriterName = 'XenForo_DataWriter_Page';

	public function actionIndex()
	{
		return $this->responseReroute('XenForo_ControllerAdmin_Node', 'index');
	}

	public function actionAdd()
	{
		return $this->responseReroute('Pages_ControllerAdmin_Page', 'edit');
	}

	public function actionEdit()
	{
		if ($nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT))
		{
			
			$phraseModel	= $this->_getPhraseModel();
			$pageModel 		= $this->_getPageModel();
			
			$page 			= $pageModel->getPageById($nodeId);
			
			// if a node ID was specified, we should be editing, so make sure a page exists
			if ( ! $page)
			{
				return $this->responseError(new XenForo_Phrase('requested_page_not_found'), 404);
			}
			
			$phrases = array(
				'title'			=> $phraseModel->getPhrasesByTitle('page_title_' . $nodeId),
				'description'	=> $phraseModel->getPhrasesByTitle('page_description_' . $nodeId),
				'content'		=> $phraseModel->getPhrasesByTitle('page_content_' . $nodeId)
			);
			
			$template = $this->_getTemplateModel()->getTemplateInStyleByTitle($pageModel->getTemplateTitle($page));
			
			if ( ! $template)
			{
				$template = array('template' => '');
			}
		}
		else
		{
			// add a new page
			$page = array(
				'parent_node_id' 	=> $this->_input->filterSingle('parent_node_id', XenForo_Input::UINT),
				'display_order' 	=> 1
			);
			
			$phrases = array('title' => array(), 'description' => array(), 'content' => array());
			
			$template = array('template' => '');
		}
		
		$options 		= XenForo_Application::get('options');
		$languageId 	= $this->_input->filterSingle('language_id', XenForo_Input::UINT);
		$languageModel 	= $this->_getLanguageModel();
		$nodeModel 		= $this->_getNodeModel();
		
		if ( ! $languageId)
		{
			$languageId = $options->defaultLanguageId;
		}
		
		$viewParams = array(
			'page' 				=> $page,
			'template' 			=> $template,
			'nodeParentOptions' => $nodeModel->getNodeOptionsArray(
				$nodeModel->getPossibleParentNodes($page), $page['parent_node_id'], true
			),
			'styles' 			=> $this->_getStyleModel()->getAllStylesAsFlattenedTree(),
			
			'phrases'			=> $phrases,
			
			'languages' 		=> $languageModel->getAllLanguagesAsFlattenedTree(),
			'language' 			=> $languageModel->getLanguageById($languageId, true),
			'defaultLanguageId'	=> $options->defaultLanguageId
		);

		return $this->responseView('Pages_ViewAdmin_Page', 'pages_page_edit', $viewParams);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();
		
		if ($this->_input->filterSingle('delete', XenForo_Input::STRING))
		{
			return $this->responseReroute('XenForo_ControllerAdmin_Page', 'deleteConfirm');
		}
		
		$pageData = $this->_input->filter(array(
			'node_name' 		=> XenForo_Input::STRING,
			'node_type_id' 		=> XenForo_Input::BINARY,
			'parent_node_id' 	=> XenForo_Input::UINT,
			'display_order' 	=> XenForo_Input::UINT,
			'display_in_list' 	=> XenForo_Input::UINT,
			'style_id' 			=> XenForo_Input::UINT,
			'log_visits' 		=> XenForo_Input::UINT,
			'list_siblings' 	=> XenForo_Input::UINT,
			'list_children' 	=> XenForo_Input::UINT,
			'callback_class' 	=> XenForo_Input::STRING,
			'callback_method' 	=> XenForo_Input::STRING,
		));

		if ( ! $this->_input->filterSingle('style_override', XenForo_Input::UINT))
		{
			$pageData['style_id'] = 0;
		}

		$nodeId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);

		$pageData['modified_date'] = XenForo_Application::$time;
		
		$phraseDatas 	= array();
		$options 		= XenForo_Application::get('options');
		
		$languageModel 	= $this->_getLanguageModel();
		$languages 		= $languageModel->getAllLanguages();
		
		$phraseData = $this->_input->filter(array(
			'title' 		=> XenForo_Input::ARRAY_SIMPLE,
			'description' 	=> XenForo_Input::ARRAY_SIMPLE,
			'content' 		=> XenForo_Input::ARRAY_SIMPLE
		));
		
		foreach ($languages AS $language)
		{
			$languageId = $language['language_id'];
			
			foreach ($phraseData AS $key => $values)
			{
				$phraseDatas[$languageId.$key] = array(
					'language_id' 	=> $languageId,
					'title'			=> 'page_' . $key . '_',
					'phrase_text'	=> $values[$languageId]
				);
			}
			
			if ($languageId == $options->defaultLanguageId)
			{
				$phraseDatas[0]				= $phraseDatas[$languageId.$key];
				
				$pageData['title'] 			= $phraseData['title'][$languageId];
				$pageData['description'] 	= $phraseData['description'][$languageId];
				
				$nodeId = $this->_getPageModel()->savePage(
					$pageData,
					$this->_input->filterSingle($phraseData['content'][$languageId], XenForo_Input::STRING),
					$nodeId,
					$this->_input->filterSingle('template_id', XenForo_Input::UINT)
				);
			}
		}
		
		$phraseModel = $this->_getPhraseModel();
		
		foreach ($phraseDatas AS $phraseData)
		{
			$writer = $this->_getPhraseDataWriter();
			
			$phraseData['title'] = $phraseData['title'] . $nodeId;
			
			$phrase = $phraseModel->getPhraseInLanguageByTitle($phraseData['title'], $phraseData['language_id']);
			if ($phrase)
			{
				$writer->setExistingData($phrase);
			}
			
			$writer->bulkSet($phraseData);
			
			$writer->save();
		}

		return $this->responseRedirect(
			XenForo_ControllerResponse_Redirect::SUCCESS,
			XenForo_Link::buildAdminLink('nodes') . $this->getLastHash($nodeId)
		);
	}

	/**
	 * Validates a field against a DataWriter.
	 * Expects 'name' and 'value' keys to be present in the request.
	 *
	 * @param string $dataWriterName Name of DataWriter against which this field will be validated
	 * @param array $data Array containing name, value or existingDataKey, which will override those fetched from _getFieldValidationInputParams
	 * @param array $options Key-value pairs of options to set
	 * @param array $extraData Key-value pairs of extra data to set
	 *
	 * @return XenForo_ControllerResponse_Redirect|XenForo_ControllerResponse_Error
	 */
	protected function _validateField($dataWriterName, array $data = array(), array $options = array(), array $extraData = array())
	{
		if (preg_match('/^([a-z0-9-_]*?)\[[a-z0-9_-]\]$/i', $_REQUEST['name'], $match))
		{
			$data['name'] = $match[1];
		}
		
		return parent::_validateField($dataWriterName, $data, $options, $extraData);
	}

	/**
	 * @return XenForo_Model_Phrase
	 */
	protected function _getPhraseModel()
	{
		return $this->getModelFromCache('XenForo_Model_Phrase');
	}
	
	/**
	 * @return XenForo_DataWriter_Phrase
	 */
	protected function _getPhraseDataWriter()
	{
		return XenForo_DataWriter::create('XenForo_DataWriter_Phrase');
	}
	
	/**
	 * Lazy load the language model object.
	 *
	 * @return  XenForo_Model_Language
	 */
	protected function _getLanguageModel()
	{
		return $this->getModelFromCache('XenForo_Model_Language');
	}

}