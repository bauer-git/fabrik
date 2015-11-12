<?php
/**
 * Fabrik Admin QuickIcon
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_fabrik_quickicon
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__) . '/helper.php';

$buttons = modFabrik_QuickIconHelper::getButtons($params);

require JModuleHelper::getLayoutPath('mod_fabrik_quickicon', $params->get('layout', 'default'));
