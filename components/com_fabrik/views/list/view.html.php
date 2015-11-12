<?php
/**
 * HTML Fabrik List view class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_fabrik/views/list/view.base.php';

/**
 * HTML Fabrik List view class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */
class FabrikViewList extends FabrikViewListBase
{
	/**
	 * Tabbed content
	 *
	 * @var array
	 */
	public $tabs = array();

	/**
	 * Display the template
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		if (parent::display($tpl) !== false)
		{
			/** @var FabrikFEModelList $model */
			$model = $this->getModel();
			$this->tabs = $model->loadTabs();

			if (!$this->app->isAdmin() && isset($this->params))
			{
				/** @var JObject $state */
				$state = $model->getState();
				$stateParams = $state->get('params');

				if ($stateParams->get('menu-meta_description'))
				{
					$this->doc->setDescription($stateParams->get('menu-meta_description'));
				}

				if ($stateParams->get('menu-meta_keywords'))
				{
					$this->doc->setMetadata('keywords', $stateParams->get('menu-meta_keywords'));
				}

				if ($stateParams->get('robots'))
				{
					$this->doc->setMetadata('robots', $stateParams->get('robots'));
				}
			}

			$this->output();
		}
	}

	/**
	 * Render the group by heading as a JLayout list.fabrik-group-by-heading
	 *
	 * @param   string  $groupedBy  Group by key for $this->grouptemplates
	 * @param   array   $group      Group data
	 *
	 * @return string
	 */
	public function layoutGroupHeading($groupedBy, $group)
	{
		$displayData = new stdClass;
		$displayData->emptyDataMessage = $this->emptyDataMessage;
		$displayData->tmpl = $this->tmpl;
		$displayData->title = $this->grouptemplates[$groupedBy];
		$displayData->count = count($group);
		$layout = FabrikHelperHTML::getLayout('list.fabrik-group-by-heading');

		return $layout->render($displayData);
	}
}
