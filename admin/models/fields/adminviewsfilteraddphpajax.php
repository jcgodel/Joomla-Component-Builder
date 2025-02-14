<?php
/**
 * @package    Joomla.Component.Builder
 *
 * @created    30th April, 2015
 * @author     Llewellyn van der Merwe <http://www.joomlacomponentbuilder.com>
 * @gitea      Joomla Component Builder <https://git.vdm.dev/joomla/Component-Builder>
 * @github     Joomla Component Builder <https://github.com/vdm-io/Joomla-Component-Builder>
 * @copyright  Copyright (C) 2015 Vast Development Method. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the list field type
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Adminviewsfilteraddphpajax Form Field class for the Componentbuilder component
 */
class JFormFieldAdminviewsfilteraddphpajax extends JFormFieldList
{
	/**
	 * The adminviewsfilteraddphpajax field type.
	 *
	 * @var		string
	 */
	public $type = 'adminviewsfilteraddphpajax';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array    An array of JHtml options.
	 */
	protected function getOptions()
	{
		// Get a db connection.
		$db = JFactory::getDbo();

		// Create a new query object.
		$query = $db->getQuery(true);

		// Select the text.
		$query->select($db->quoteName('add_php_ajax'));
		$query->from($db->quoteName('#__componentbuilder_admin_view'));
		$query->order($db->quoteName('add_php_ajax') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		$results = $db->loadColumn();
		$_filter = array();
		$_filter[] = JHtml::_('select.option', '', '- ' . JText::_('COM_COMPONENTBUILDER_FILTER_SELECT_ADD_PHP_AJAX') . ' -');

		if ($results)
		{
			// get admin_viewsmodel
			$model = ComponentbuilderHelper::getModel('admin_views');
			$results = array_unique($results);
			foreach ($results as $add_php_ajax)
			{
				// Translate the add_php_ajax selection
				$text = $model->selectionTranslation($add_php_ajax,'add_php_ajax');
				// Now add the add_php_ajax and its text to the options array
				$_filter[] = JHtml::_('select.option', $add_php_ajax, JText::_($text));
			}
		}
		return $_filter;
	}
}
