<?php

class Pages_Listen
{

	public static function load_class_controller($class, array &$extend)
	{
		/* Extend XenForo_ControllerAdmin_Page */
		if ($class == 'XenForo_ControllerAdmin_Page' AND ! in_array('Pages_ControllerAdmin_Page', $extend))
		{
			$extend[] = 'Pages_ControllerAdmin_Page';
		}
		/* Extend End */
		
		/* Extend XenForo_ControllerPublic_Page */
		if ($class == 'XenForo_ControllerPublic_Page' AND ! in_array('Pages_ControllerPublic_Page', $extend))
		{
			$extend[] = 'Pages_ControllerPublic_Page';
		}
		/* Extend End */
	}

	public static function load_class_model($class, array &$extend)
	{
		/* Extend XenForo_Model_Phrase */
		if ($class == 'XenForo_Model_Phrase' AND ! in_array('Pages_Model_Phrase', $extend))
		{
			$extend[] = 'Pages_Model_Phrase';
		}
		/* Extend End */
		
		/* Extend XenForo_Model_Page */
		if ($class == 'XenForo_Model_Page' AND ! in_array('Pages_Model_Page', $extend))
		{
			$extend[] = 'Pages_Model_Page';
		}
		/* Extend End */
	}

	public static function load_class_view($class, array &$extend)
	{
		/* Extend XenForo_ViewPublic_Page_View */
		if ($class == 'XenForo_ViewPublic_Page_View' AND ! in_array('Pages_ViewPublic_Page_View', $extend))
		{
			$extend[] = 'Pages_ViewPublic_Page_View';
		}
		/* Extend End */
	}

	public static function navigation_tabs(array &$extraTabs, $selectedTabId)
	{
	}


}