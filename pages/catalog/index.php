<?php

/**
 * @defgroup pages_catalog
 */

/**
 * @file pages/catalog/index.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_catalog
 * @brief Handle requests for the public catalog view.
 *
 */

switch ($op) {
	case 'index':
	case 'category':
	case 'newReleases':
	case 'series':
	case 'results':
		define('HANDLER_CLASS', 'CatalogHandler');
		import('pages.catalog.CatalogHandler');
		break;
	case 'book':
	case 'download':
		define('HANDLER_CLASS', 'CatalogBookHandler');
		import('pages.catalog.CatalogBookHandler');
		break;
}

?>
