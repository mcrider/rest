<?php

/**
 * @file plugins/gateway/rest/RestPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RestPlugin
 * @ingroup plugins_gateways_rest
 *
 * @brief OJS REST gateway plugin.  Retrieve JSON-formatted information from an OJS installation via HTTP calls .
 */


import('classes.plugins.GatewayPlugin');
import('classes.file.PublicFileManager');

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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetch($args, &$request) {
		if (!$this->getEnabled()) {
			return false;
		}

		$journal =& $request->getJournal();
		if (!isset($journal)) $this->showError();

		$issueDao =& DAORegistry::getDAO('IssueDAO');
		
		$journalId = $journal->getId();

		$operator = array_shift($args);
		switch ($operator) {
			case 'journalInfo': // Basic journal metadata
				$response = array(
								'id' => $journalId,
								'title' => $journal->getLocalizedTitle(),
								'url' => $journal->getUrl(),
								'initials' => $journal->getLocalizedInitials(),
								'description' => $journal->getLocalizedDescription(),
							);

				echo json_encode($response);
				break;
			case 'articleInfo': // Article metadata
				// Takes article ID as input
				$articleId = (int) array_shift($args);
				$response = $this->_getArticleInfo($request, $articleId);
				echo json_encode($response);
				break;
			case 'issueData': // Issue metadata
				//Takes article ID as input
				$issueID = (int) array_shift($args);
				$issue =& $issueDao->getIssueById($issueID, $journalId);

				$response = $this->_getIssueInfo($request, $journalId, $issue);
				echo json_encode($response);
				break;								
			case 'issueDataWithArticles': //Issue metadata along with all included article metadata
				// Takes issue ID as input
				$issueID = (int) array_shift($args);
				
				$issue =& $issueDao->getIssueById($issueID, $journalId);
				
				$response = $this->_getIssueInfo($request, $journalId, $issue, true);
				echo json_encode($response);
				break;
			case 'currentIssueData': // Current issue metadata
				$issue =& $issueDao->getCurrentIssue($journalId, true);
				
				$response = $this->_getIssueInfo($request, $journalId, $issue);
				echo json_encode($response);
				break;
			case 'currentIssueDataWithArticles': // Current issue metadata along with all included article metadata
				$issue =& $issueDao->getCurrentIssue($journalId, true);
				
				$response = $this->_getIssueInfo($request, $journalId, $issue, true);
				echo json_encode($response);
				break;				
			case 'allIssueData': // Metadata for all published issues
				$issues =& $issueDao->getPublishedIssues($journalId);

				$response = array();
				while ($issue =& $issues->next()) {
					$response[] = $this->_getIssueInfo($request, $journalId, $issue);
					unset($issue);
				}
				echo json_encode($response);
				break;
			case 'allIssueDataWithArticles': // Metadata for all published issues and all their articles (can be big!)
				$issues =& $issueDao->getPublishedIssues($journalId);

				$response = array();
				while ($issue =& $issues->next()) {
					$response[] = $this->_getIssueInfo($request, $journalId, $issue, true);
					unset($issue);
				}
				echo json_encode($response);
				break;
			case 'announcements': // Announcements
				$announcementDao =& DAORegistry::getDAO('AnnouncementDAO');
				$announcements =& $announcementDao->getAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

				$response = array();
				while($announcement =& $announcements->next()) {
					$response[] = array(
						'url' => $request->url(null, 'announcement', 'view', array($announcement->getId())),
						'id' => $announcement->getId(),
						'title' => $announcement->getLocalizedTitleFull(),
						'description' => $announcement->getLocalizedDescription(),
						'datePosted' => $announcement->getDatetimePosted()
					);
					unset($announcement);
				}

				echo json_encode($response);
				break;
			default:
				// Not a valid request
				$this->showError();
		}
		return true;
	}

	/**
	 * Display an error message and exit
	 */
	function showError() {
		header("HTTP/1.0 500 Internal Server Error");
		echo Locale::translate('plugins.gateways.rest.errors.errorMessage');
		exit;
	}

	/**
	 * Get data for an issue
	 * @param $request PKPRequest
	 * @param $journalId The journal ID
	 * @param $issueId Object
	 * @param $withArticles boolean Whether to include the issue's articles in the response
	 */
	function _getIssueInfo(&$request, $journalId, $issue, $withArticles = false) {
		if(!isset($issue)) $this->showError();

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON));
		
		//Handle getting the image URL
		$publicFileManager = new PublicFileManager();
		$coverPagePath = $request->getBaseUrl() . '/';
		$coverPagePath .= $publicFileManager->getJournalFilesPath($journalId) . '/';
		$imageFileName = $issue->getIssueFileName();
		$imageUrl = $coverPagePath . $imageFileName;
		
		$response = array(
			'url' => $request->url(null, 'issue', 'view', $issue->getId()),
			'issueId' => $issue->getId(),
			'title' => $issue->getLocalizedTitle(),
			'description' => $issue->getLocalizedDescription(),
			'identification' => $issue->getIssueIdentification(),
			'volume' => $issue->getVolume(),
			'number' => $issue->getNumber(),
			'year' => $issue->getYear(),
			'datePublished' => $issue->getDatePublished(),
			'imageUrl' => $imageUrl,
			'imageDescription' => $issue->getIssueCoverPageDescription()
		);

		if ($withArticles) {
			$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
			$publishedArticles =& $publishedArticleDao->getPublishedArticles($issue->getId());

			$articles = array();
			foreach($publishedArticles as $publishedArticle) {
				$articles[] = $this->_getArticleInfo($request, $publishedArticle->getId());
			}
			$response['articles'] = $articles;
		}
		
		return $response;
	}

	/**
	 * Get data for an article
	 * @param $request PKPRequest
	 * @param $articleId int
	 * @return array
	 */
	 function _getArticleInfo(&$request, $articleId) {
		$articleDao =& DAORegistry::getDAO('ArticleDAO');
		$article =& $articleDao->getArticle($articleId);

		if(!isset($article)) $this->showError();

		// Create an array of author information
		$authors =& $article->getAuthors();
		$authorInfo = array();
		for ($i=0, $count=count($authors); $i < $count; $i++) {
			$currentAuthor = array(
				'firstName' => $authors[$i]->getFirstName(),
				'middleName' => $authors[$i]->getMiddleName(),
				'lastName' => $authors[$i]->getLastName(),
				'affiliation' => $authors[$i]->getAffiliation(null), // Localized
				'country' => $authors[$i]->getCountry(),
				'countryLocalized' => $authors[$i]->getCountryLocalized(),
				'email' => $authors[$i]->getEmail(),
				'url' => $authors[$i]->getUrl(),
				'competingInterests' => $authors[$i]->getCompetingInterests(null), // Localized
				'biography' => $authors[$i]->getBiography(null) // Localized
			);
			if ($authors[$i]->getPrimaryContact()) {
				$currentAuthor['primaryContact'] = true;
			}

			array_push($authorInfo, $currentAuthor);
		}

		// Construct the article response data to be encoded
		$response = array(
			'url' => $request->url(null, 'article', 'view', $articleId),
			'id' => $articleId,
			'title' => $article->getLocalizedTitle(),
			'abstract' => $article->getLocalizedAbstract(),
			'authorString' => $article->getAuthorString(),
			'authors' => $authorInfo,
			'sectionId' => $article->getSectionId(),
			'sectionTitle' => $article->getSectionTitle()
		);
		
		$articleGalleyDAO =& DAORegistry::getDAO('ArticleGalleyDAO');
		$articleGalleys =& $articleGalleyDAO->getGalleysByArticle($articleId);
		$galleysResponse = array();
		foreach ($articleGalleys as $articleGalley){
    			$galleyId = $articleGalley->getId();
    			$galleyInfo = array(
    				'id' => $galleyId,
    				'label' => $articleGalley->getLabel(),
    				'url' => Request::url(null, 'article', 'viewFile', array($articleId, $galleyId))
    			);
    			$galleysResponse[] = $galleyInfo;
		}
		$response['galleys'] = $galleysResponse;
		
		// Add some optional metadata.  There may be other items the could be included here.
		if($article->getLocalizedDiscipline()) $response['discipline'] = $article->getLocalizedDiscipline();
		if($article->getLocalizedSubjectClass()) $response['subjectClass'] = $article->getLocalizedSubjectClass();
		if($article->getLocalizedCoverageGeo()) $response['coverageGeo'] = $article->getLocalizedCoverageGeo();
		if($article->getLocalizedCoverageChron()) $response['coverageChron'] = $article->getLocalizedCoverageChron();
		if($article->getLocalizedType()) $response['type'] = $article->getLocalizedType();
		if($article->getLocalizedSponsor()) $response['sponsor'] = $article->getLocalizedSponsor();
		if($article->getCitations()) $response['citations'] = $article->getCitations();

		return $response;
	 }
}

?>