<?php
/**
 * Installs comments into modules
 *
 * @package comments
 * @version 0.7.0
 * @author Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2011
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

require cot_incfile('comments', 'plug', 'enablement');

// Add options into module configs
foreach ($com_modules_list as $mod_name)
{
	if (cot_module_installed($mod_name))
	{
		cot_config_implant($mod_name, $com_options);
	}
}

// Add options into module structure configs
foreach ($com_modules_struct_list as $mod_name)
{
	if (cot_module_installed($mod_name))
	{
		cot_config_implant($mod_name, $com_options, true);
	}
}
?>
