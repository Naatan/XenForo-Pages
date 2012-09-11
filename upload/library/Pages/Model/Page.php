<?php

class Pages_Model_Page extends XFCP_Pages_Model_Page
{
	
	public function saveMultiLingualPage($pageData, $langData, $nodeId, $templateId)
	{
		$db = $this->_getDb();
		XenForo_Db::beginTransaction($db);
		
		$options 	= XenForo_Application::get('options');
		$nodeId 	= $this->saveRegularPage($pageData, $langData[$options->defaultLanguageId], $nodeId, $templateId);
		
		foreach ($langData AS $languageId => $data)
		{
			$this->saveLanguagePage($data, $languageId, $nodeId);
		}
		
		XenForo_Db::commit($db);
		
		return $nodeId;
	}
	
	public function saveRegularPage($pageData, $inputData, $nodeId, $templateId)
	{
		$pageData['modified_date'] 	= XenForo_Application::$time;
		$pageData['title'] 			= $inputData['title'];
		$pageData['description'] 	= $inputData['description'];
		
		// save page
		$pageWriter = XenForo_DataWriter::create('XenForo_DataWriter_Page');
		
		if ($nodeId)
		{
			$pageWriter->setExistingData($nodeId);
		}
		
		$pageWriter->bulkSet($pageData);
		$pageWriter->save();
		
		$page = $pageWriter->getMergedData();
		
		$this->_savePageTemplate($this->getTemplateTitle($page), $inputData['template'], $templateId);
		
		return $pageWriter->get('node_id');
	}
	
	public function saveLanguagePage($inputData, $languageId, $nodeId)
	{
		$this->_savePagePhrase('title_' . $nodeId, $inputData['title'], $languageId, $inputData['id']['title']);
		$this->_savePagePhrase('description_' . $nodeId, $inputData['description'], $languageId, $inputData['id']['description']);
		$this->_savePageTemplate('_page_node.' . $nodeId . '.' . $languageId, $inputData['template'], $inputData['id']['template']);
	}
	
	protected function _savePageTemplate($title, $template, $templateId)
	{
		$templateWriter = XenForo_DataWriter::create('XenForo_DataWriter_Template');
		
		if ($templateId)
		{
			$templateWriter->setExistingData($templateId);
		}
		
		$templateWriter->set('title', $title);
		$templateWriter->set('template', $template);
		$templateWriter->set('style_id', 0);
		$templateWriter->set('addon_id', $templateId);
		
		$templateWriter->preSave();
		
		if ($templateWriter->getErrors())
		{
			$a = func_get_args();
			$e = $templateWriter->getErrors();
			var_dump($a);exit;
		}
		
		$templateWriter->save();
	}
	
	protected function _savePagePhrase($key, $value, $languageId, $phraseId = null)
	{
		$options = XenForo_Application::get('options');
		
		if ($languageId == $options->defaultLanguageId)
		{
			$this->_savePagePhrase($key, $value, 0, true);
		}
		
		$writer = XenForo_DataWriter::create('XenForo_DataWriter_Phrase');
		
		if ($phraseId === true)
		{
			$phraseModel 	= XenForo_Model::create('XenForo_Model_Phrase');
			$phrase 		= $phraseModel->getPhraseInLanguageByTitle('page_' . $key, $languageId);
			
			if ($phrase)
			{
				$writer->setExistingData($phrase);
			}
		}
		else if ($phraseId)
		{
			$writer->setExistingData($phraseId);
		}
		
		if (empty($value) AND $languageId != 0)
		{
			if ($writer->isUpdate())
			{
				$writer->delete();
			}
		}
		else
		{
			$writer->bulkSet(array(
				'language_id'	=> $languageId,
				'title'			=> 'page_' . $key,
				'phrase_text'	=> $value
			));
			$writer->save();
		}
	}

}