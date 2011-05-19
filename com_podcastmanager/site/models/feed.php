<?php 
/**
* Podcast Manager for Joomla!
*
* @copyright	Copyright (C) 2011 Michael Babker. All rights reserved.
* @license		GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* 
* Podcast Manager is based upon the ideas found in Podcast Suite created by Joe LeBlanc
* Original copyright (c) 2005 - 2008 Joseph L. LeBlanc and released under the GPLv2 license
*/

// Restricted access
defined('_JEXEC') or die();

jimport('joomla.application.component.modellist');

class PodcastManagerModelFeed extends JModelList
{
	/**
	 * Method to get a feed's parameters.
	 *
	 * @return	object
	 */
	public function getFeed()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__podcastmanager_feeds` AS a');

		$feedId = $this->getState('feed.id');
		$query->where('a.id = '.(int) $feedId);

		$db->setQuery($query);
		$feed = $db->loadObject();
		return $feed;
	}

	/**
	 * Method to get a list of items.
	 *
	 * @return	mixed	An array of objects on success, false on failure.
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		return $items;
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields
		$query->select($this->getState('list.select', 'a.*'));
		$query->from('`#__podcastmanager` AS a');

		// Join over the users for the modified_by name.
		$query->join('LEFT', '#__users AS uam ON uam.id = a.modified_by');

		// Filter by feed
		$feed = $this->getState('feed.id');
		if (is_numeric($feed)) {
			$query->where('a.feedname = '.(int) $feed);
		}

		// Filter by state
		$state = $this->getState('filter.published');
		if (is_numeric($state)) {
			$query->where('a.published = '.(int) $state);
		}

		// Filter by start date.
		$nullDate = $db->Quote($db->getNullDate());
		$nowDate = $db->Quote(JFactory::getDate()->toMySQL());

		if ($this->getState('filter.publish_date')){
			$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		}

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('a.language in ('.$db->Quote(JFactory::getLanguage()->getTag()).','.$db->Quote('*').')');
		}

		// Add the list ordering clause.
		$query->order($this->getState('list.ordering', 'a.publish_up').' '.$this->getState('list.direction', 'DESC'));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		$params	= JComponentHelper::getParams('com_podcastmanager');

		// List state information
		$feed = JRequest::getInt('feedname');
		$this->setState('feed.id', $feed);

		$limit = JRequest::getInt('limit');
		$this->setState('list.limit', $limit);

		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$this->setState('list.start', $limitstart);

		$orderCol	= JRequest::getCmd('filter_order', 'a.publish_up');
		if (!in_array($orderCol, $this->filter_fields)) {
			$orderCol = 'a.publish_up';
		}
		$this->setState('list.ordering', $orderCol);

		$listOrder	=  JRequest::getCmd('filter_order_Dir', 'DESC');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
			$listOrder = 'DESC';
		}
		$this->setState('list.direction', $listOrder);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_podcastmanager')) &&  (!$user->authorise('core.edit', 'com_podcastmanager'))){
			// Limit to published for people who can't edit or edit.state.
			$this->setState('filter.published',	1);

			// Filter by published dates.
			$this->setState('filter.publish_date', true);
		}

		$this->setState('filter.language', $app->getLanguageFilter());

		// Load the parameters.
		$this->setState('params', $params);
	}
}
