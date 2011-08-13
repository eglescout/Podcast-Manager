<?php
/**
* Podcast Manager for Joomla!
*
* @package     PodcastManager
* @subpackage  com_podcastmanager
*
* @copyright   Copyright (C) 2011 Michael Babker. All rights reserved.
* @license     GNU/GPL - http://www.gnu.org/copyleft/gpl.html
*
* Podcast Manager is based upon the ideas found in Podcast Suite created by Joe LeBlanc
* Original copyright (c) 2005 - 2008 Joseph L. LeBlanc and released under the GPLv2 license
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_podcastmanager'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Check if Live Update is being accessed and bypass all other component data
require_once JPATH_COMPONENT_ADMINISTRATOR.'/liveupdate/liveupdate.php';
if (JRequest::getCmd('view') == 'liveupdate')
{
	LiveUpdate::handleRequest();
	return;
}

$controller = JController::getInstance('PodcastManager');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
