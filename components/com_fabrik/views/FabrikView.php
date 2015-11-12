<?php
/**
 * Base Fabrik view class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * Class FabrikView
 */
class FabrikView extends JViewLegacy
{
	/**
	 * @var JApplicationCMS
	 */
	protected $app;

	/**
	 * @var JUser
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $package;

	/**
	 * @var JSession
	 */
	protected $session;

	/**
	 * @var JDocument
	 */
	protected $doc;

	/**
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * @var Registry
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param   array $config A named configuration array for object construction.
	 *
	 */
	public function __construct($config = array())
	{
		$this->app     = JArrayHelper::getValue($config, 'app', JFactory::getApplication());
		$this->user    = JArrayHelper::getValue($config, 'user', JFactory::getUser());
		$this->package = $this->app->getUserState('com_fabrik.package', 'fabrik');
		$this->session = JArrayHelper::getValue($config, 'session', JFactory::getSession());
		$this->doc     = JArrayHelper::getValue($config, 'doc', JFactory::getDocument());
		$this->db      = JArrayHelper::getValue($config, 'db', JFactory::getDbo());
		$this->config  = JArrayHelper::getValue($config, 'config', JFactory::getConfig());
		parent::__construct($config);
	}
}