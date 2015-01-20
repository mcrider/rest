<?php

/**
 * @file plugins/gateway/rest/SettingsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RestPlugin
 * @ingroup plugins_gateways_rest
 *
 * @brief OJS REST gateway plugin settings.
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

	/** @var $journalId int */
	var $journalId;

	/** @var $plugin object */
	var $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 * @param $journalId int
	 */
	function SettingsForm(&$plugin, $journalId) {
		$this->journalId = $journalId;
		$this->plugin =& $plugin;

		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$journalId = $this->journalId;
		$plugin =& $this->plugin;

		if ($plugin->getSetting($journalId, 'apiKey') == '') {
			$this->setData('apiKey', Validation::generatePassword(16));
			$plugin->updateSetting($journalId, 'apiKey', $this->getData('apiKey'));
		} else {
			$this->setData('apiKey', $plugin->getSetting($journalId, 'apiKey'));
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('apiKey'));
	}

	/**
	 * Save settings.
	 */
	function execute() {
		$plugin =& $this->plugin;
		$journalId = $this->journalId;

		$plugin->updateSetting($journalId, 'apiKey', $this->getData('apiKey'));
	}

}

?>
