<?php

/**
 * @defgroup plugins_gateways_rest
 */
 
/**
 * @file plugins/gateways/rest/index.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_gateways_rest
 * @brief Wrapper for REST gateway plugin.
 *
 *
 */

require_once('RestPlugin.inc.php');

return new RestPlugin();

?>
