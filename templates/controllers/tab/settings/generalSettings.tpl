{**
 * controllers/tab/settings/generalSettings.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Publication process general settings (book file types).
 *
 *}

<h3 class="pkp_grid_title">{translate key="manager.setup.genres"}</h3>
<p class="pkp_grid_description">{translate key="manager.setup.genresDescription"}</p>

{url|assign:genresUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.genre.GenreGridHandler" op="fetchGrid"}
{load_url_in_div id="genresContainer" url=$genresUrl}