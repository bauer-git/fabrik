<?php
/**
 * Add a user to a mailchimp mailing list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.mailchimp
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once 'MCAPI.class.php';

/**
 * Add a user to a mailchimp mailing list
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.mailchimp
 * @since       3.0
 */

class PlgFabrik_FormMailchimp extends PlgFabrik_Form
{
	protected $html = null;

	/**
	 * Set up the html to be injected into the bottom of the form
	 *
	 * @return  void
	 */

	public function getBottomContent()
	{
		$params = $this->getParams();

		if ($params->get('mailchimp_userconfirm', true))
		{
			$checked = $this->app->input->get('fabrik_mailchimp_signup', '') !== '' ? ' checked="checked"' : '';
			$this->html = '<label class="mailchimpsignup"><input type="checkbox" name="fabrik_mailchimp_signup" class="fabrik_mailchimp_signup" value="1" '
				. $checked . '/>' . $params->get('mailchimp_signuplabel') . '</label>';
		}
		else
		{
			$this->html = '';
		}

		// $this->getGroups($params);
	}

	/**
	 * Get Mailchimp email groups
	 *
	 * @param   JRegistry  $params  Params
	 *
	 * @throws RuntimeException
	 *
	 * @return  array groups
	 */

	protected function getGroups($params)
	{
		$listId = $params->get('mailchimp_listid');
		$apiKey = $params->get('mailchimp_apikey');

		if ($apiKey == '')
		{
			throw new RuntimeException('Mailchimp: no api key specified');
		}

		if ($listId == '')
		{
			throw new RuntimeException('Mailchimp: no list id specified');
		}

		$api = new MCAPI($params->get('mailchimp_apikey'));
		$groups = $api->listInterestGroupings($listId);

		return $groups;
	}

	/**
	 * Inject custom html into the bottom of the form
	 *
	 * @param   int  $c  Plugin counter
	 *
	 * @return  string  html
	 */

	public function getBottomContent_result($c)
	{
		return $this->html;
	}

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @return	bool
	 */

	public function onAfterProcess()
	{
		$params = $this->getParams();
		$formModel = $this->getModel();
		$emailData = $this->getProcessData();
		$filter = JFilterInput::getInstance();
		$post = $filter->clean($_POST, 'array');

		if (!array_key_exists('fabrik_mailchimp_signup', $post) && (bool) $params->get('mailchimp_userconfirm', true) === true)
		{
			return;
		}

		$listId = $params->get('mailchimp_listid');
		$apiKey = $params->get('mailchimp_apikey');

		if ($apiKey == '')
		{
			throw new RuntimeException('Mailchimp: no api key specified');
		}

		if ($listId == '')
		{
			throw new RuntimeException('Mailchimp: no list id specified');
		}

		$api = new MCAPI($params->get('mailchimp_apikey'));
		$opts = array();
		$emailKey = $formModel->getElement($params->get('mailchimp_email'), true)->getFullName();
		$firstNameKey = $formModel->getElement($params->get('mailchimp_firstname'), true)->getFullName();
		$fname = $formModel->formDataWithTableName[$firstNameKey];
		$opts['FNAME'] = $fname;
		$opts['NAME'] = $fname;

		if ($params->get('mailchimp_lastname', '') !== '')
		{
			$lastNameKey = $formModel->getElement($params->get('mailchimp_lastname'), true)->getFullName();
			$lname = $formModel->formDataWithTableName[$lastNameKey];
			$opts['LNAME'] = $lname;
			$opts['NAME'] .= ' ' . $lname;
		}

		$email = $formModel->formDataWithTableName[$emailKey];
		$w = new FabrikWorker;
		$groupOpts = json_decode($params->get('mailchimp_groupopts', "[]"));

		if (!empty($groupOpts))
		{
			foreach ($groupOpts as $groupOpt)
			{
				$groups = array();

				if (isset($groupOpt->groups))
				{
					$groupOpt->groups = $w->parseMessageForPlaceHolder($groupOpt->groups, $emailData);

					// An array of additional options: array('name'=>'Your Interests:', 'groups'=>'Bananas,Apples')
					$groups[] = JArrayHelper::fromObject($groupOpt);
				}
				else
				{
					foreach ($groupOpt as $k => $v)
					{
						// Don't use emailData as that contains html markup which is not shown in the list view
						$opts[strtoupper($k)] = $w->parseMessageForPlaceHolder($v, $formModel->formData);

						// But... labels for db joins etc. are not available in formData
						$opts[strtoupper($k)] = $w->parseMessageForPlaceHolder($v, $emailData);
					}

					$opts['GROUPINGS'] = $groups;
				}
			}

			$opts['GROUPINGS'] = $groups;
		}

		// By default this sends a confirmation email - you will not see new members until the link contained in it is clicked!
		$emailType = $params->get('mailchimp_email_type', 'html');
		$doubleOptin = (bool) $params->get('mailchimp_double_optin', true);
		$updateExisting = (bool) $params->get('mailchimp_update_existing', true);
		$retval = $api->listSubscribe($listId, $email, $opts, $emailType, $doubleOptin, $updateExisting);

		if ($api->errorCode)
		{
			$this->app->enqueueMessage($api->errorCode, 'Mailchimp: ' . $api->errorMessage, 'notice');

			if ((bool) $params->get('mailchimp_fail_on_error', true) === true)
			{
				$formModel->errors['mailchimp_error'] = true;

				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			return true;
		}
	}
}
