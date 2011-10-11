<?php

/**
 * @file plugins/gateway/rest/RestPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RestPlugin
 * @ingroup plugins_gateways_resolver
 *
 * @brief Simple resolver gateway plugin
 */


import('classes.plugins.GatewayPlugin');

class RestPlugin extends GatewayPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of the settings file to be installed on new journal
	 * creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'RestPlugin';
	}

	function getDisplayName() {
		return Locale::translate('plugins.gateways.rest.displayName');
	}

	function getDescription() {
		return Locale::translate('plugins.gateways.rest.description');
	}

	/**
	 * Handle fetch requests for this plugin.
	 */
	function fetch($args, $request) {
		if (!$this->getEnabled()) {
			return false;
		}

		$operator = array_shift($args);
		switch ($operator) {
			case 'journalInfo':
				$journal =& $request->getJournal();
				$response = array(
								'id' => $journal->getId(),
								'title' => $journal->getLocalizedTitle(),
								'url' => $journal->getUrl(),
								'initials' => $journal->getInitials(),
								'description' => $journal->getLocalizedDescription(),
							);

				echo json_encode($response);
				break;
			case 'articleInfo': // Takes article ID as input
				break;
			case 'currentIssueData':
				break;
			case 'allIssueData':
				break;
			case 'editorialTeam':
				break;
			case 'announcements':
				break;
			default: 
				// Failure.
				header("HTTP/1.0 500 Internal Server Error");
				echo Locale::translate('plugins.gateways.rest.errors.errorMessage');
				exit;
		}

		
	}
}

?>
