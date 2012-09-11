<?php

class Pages_Model_Phrase extends XFCP_Pages_Model_Phrase
{

	public function getPhrasesByTitle($title)
	{
		return $this->fetchAllKeyed('
			SELECT *
			FROM xf_phrase
			WHERE title = ?
		', 'language_id', $title);
	}

}