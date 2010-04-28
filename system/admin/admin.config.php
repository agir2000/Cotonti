<?php
/**
 * Administration panel - Configuration
 *
 * @package Cotonti
 * @version 0.7.0
 * @author Neocrome, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

(defined('SED_CODE') && defined('SED_ADMIN')) or die('Wrong URL.');

list($usr['auth_read'], $usr['auth_write'], $usr['isadmin']) = sed_auth('admin', 'a');
sed_block($usr['isadmin']);

$t = new XTemplate(sed_skinfile('admin.config'));

require_once sed_incfile('forms');

$adminpath[] = array(sed_url('admin', 'm=config'), $L['Configuration']);

$sed_select_charset = sed_loadcharsets();
$sed_select_doctypeid = sed_loaddoctypes();
$sed_select_rss_charset = sed_loadcharsets();

/* === Hook === */
$extp = sed_getextplugins('admin.config.first');
foreach ($extp as $pl)
{
	include $pl;
}
/* ===== */

require_once sed_incfile('forms');

switch($n)
{
	case 'edit':
		$o = sed_import('o', 'G', 'ALP');
		$p = sed_import('p', 'G', 'ALP');
		$v = sed_import('v', 'G', 'ALP');
		$o = empty($o) ? 'core' : $o;
		$p = empty($p) ? 'global' : $p;
		
		if ($a == 'update')
		{
			
			$sql = sed_sql_query("SELECT config_name FROM $db_config
				WHERE config_owner='$o' AND config_cat='$p'");
			while ($row = sed_sql_fetcharray($sql))
			{
				$cfg_value = trim(sed_import($row['config_name'], 'P', 'NOC'));
				if ($o == 'core' && $p == 'users'
					&& ($cfg_name == 'av_maxsize' || $cfg_name == 'sig_maxsize' || $cfg_name == 'ph_maxsize'))
				{
					$cfg_value = min($cfg_value, sed_get_uploadmax() * 1024);
				}
				sed_sql_query("UPDATE $db_config SET config_value='" . sed_sql_prep($cfg_value) . "'
					WHERE config_name='" . $row['config_name'] . "' AND config_owner='$o' AND config_cat='$p'");
			}
			
			$cot_cache && $cot_cache->db->remove('cot_cfg', 'system');
			$adminwarnings = $L['Updated'];
		}
		elseif ($a == 'reset' && !empty($v))
		{
			sed_sql_query("UPDATE $db_config SET config_value=config_default WHERE config_name='$v' AND config_owner='$o'");
			$cot_cache && $cot_cache->db->remove('cot_cfg', 'system');
		}
		
		$sql = sed_sql_query("SELECT * FROM $db_config WHERE config_owner='$o' AND config_cat='$p' ORDER BY config_cat ASC, config_order ASC, config_name ASC");
		sed_die(sed_sql_numrows($sql) == 0);
		
		if ($o == 'core')
		{
			$adminpath[] = array(sed_url('admin', 'm=config&n=edit&o='.$o.'&p='.$p), $L["core_".$p]);
		}
		else
		{
			$adminpath[] = array(sed_url('admin', 'm=plug&a=details&pl='.$p), $L['Plugin'].' ('.$o.':'.$p.')');
			$adminpath[] = array(sed_url('admin', 'm=config&n=edit&o='.$o.'&p='.$p), $L['Edit']);
		}
		
		if ($o == 'plug')
		{
			$path_lang_def = $cfg['plugins_dir']."/$p/lang/$p.en.lang.php";
			$path_lang_alt = $cfg['plugins_dir']."/$p/lang/$p.$lang.lang.php";
			if (file_exists($path_lang_def))
			{
				require_once($path_lang_def);
			}
			if (file_exists($path_lang_alt) && $lang !='en')
			{
				require_once($path_lang_alt);
			}
		}
		
		/* === Hook - Part1 : Set === */
		$extp = sed_getextplugins('admin.config.edit.loop');
		/* ===== */
		while ($row = sed_sql_fetcharray($sql))
		{
			$config_owner = $row['config_owner'];
			$config_cat = $row['config_cat'];
			$config_name = $row['config_name'];
			$config_value = $row['config_value'];
			$config_default = $row['config_default'];
			$config_type = $row['config_type'];
			$config_title = $L['cfg_'.$config_name][0];
			$config_text = htmlspecialchars($row['config_text']);
			$config_more = $L['cfg_'.$config_name][1];
			$if_config_more = (!empty($config_more)) ? true : false;
					
			if ($config_type == 1)
			{
				$config_input = sed_inputbox('text', $config_name, $config_value);
			}
			elseif ($config_type == 2)
			{
				if (!empty($row['config_variants']))
				{
					$cfg_params = explode(',', $row['config_variants']);
					$cfg_params_titles = (isset($L['cfg_'.$config_name.'_params']) && is_array($L['cfg_'.$config_name.'_params'])) ? $L['cfg_'.$config_name.'_params'] : $cfg_params;
				}
				$config_input = (is_array($cfg_params)) ? sed_selectbox($config_value, $config_name, $cfg_params, $cfg_params_titles, false) : sed_inputbox('text', $config_name, $config_value);
			}
			elseif ($config_type == 3)
			{
				$config_input = sed_radiobox($config_value, $config_name, array('1', '0'), array($L['Yes'], $L['No']), '', ' ');
			}
			elseif ($config_type == 4)
			{
				$varname = "sed_select_".$config_name;
				reset($$varname);
				$vararray = array();
				foreach ($$varname as $key => $value)
				{
					$vararray[$value[1]] = $value[0];
				}
				$config_input = sed_selectbox($config_value, $config_name, array_keys($vararray), array_values($vararray), false);
			}
			else
			{
				$config_input = sed_textarea($config_name, $config_value, 8, 56);
			}
			
			$t->assign(array(
				'ADMIN_CONFIG_ROW_CONFIG' => $config_input,
				'ADMIN_CONFIG_ROW_CONFIG_TITLE' => (empty($L['cfg_'.$row['config_name']][0]) && !empty($config_text)) ? $config_text : $config_title,
				'ADMIN_CONFIG_ROW_CONFIG_MORE_URL' => sed_url('admin', "m=config&n=edit&o=".$o."&p=".$p."&a=reset&v=".$config_name),
				'ADMIN_CONFIG_ROW_CONFIG_MORE' => $config_more
			));
			/* === Hook - Part2 : Include === */
			foreach ($extp as $pl)
			{
				include $pl;
			}
			/* ===== */
			$t->parse('MAIN.EDIT.ADMIN_CONFIG_ROW');
		}
		
		$t->assign(array(
			'ADMIN_CONFIG_FORM_URL' => sed_url('admin', "m=config&n=edit&o=".$o."&p=".$p."&a=update")
		));
		/* === Hook  === */
		$extp = sed_getextplugins('admin.config.edit.tags');
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */
		$t->parse('MAIN.EDIT');
		break;
	
	default:
		$sql = sed_sql_query("SELECT DISTINCT(config_cat) FROM $db_config WHERE config_owner='core' ORDER BY config_cat ASC");
		while ($row = sed_sql_fetcharray($sql))
		{
			if($L["core_".$row['config_cat']])
			{
				$t->assign(array(
					'ADMIN_CONFIG_ROW_CORE_URL' => sed_url('admin', "m=config&n=edit&o=core&p=".$row['config_cat']),
					'ADMIN_CONFIG_ROW_CORE_NAME' => $L["core_".$row['config_cat']]
				));
				$t->parse('MAIN.DEFAULT.ADMIN_CONFIG_ROW_CORE');
			}
		}
		$sql = sed_sql_query("SELECT DISTINCT(config_cat) FROM $db_config WHERE config_owner='plug' ORDER BY config_cat ASC");
		while ($row = sed_sql_fetcharray($sql))
		{
			$t->assign(array(
				'ADMIN_CONFIG_ROW_PLUG_URL' => sed_url('admin', "m=config&n=edit&o=plug&p=".$row['config_cat']),
				'ADMIN_CONFIG_ROW_PLUG_NAME' => $row['config_cat']
			));
			$t->parse('MAIN.DEFAULT.ADMIN_CONFIG_ROW_PLUG');
		}
		/* === Hook  === */
		$extp = sed_getextplugins('admin.config.default.tags');
		foreach ($extp as $pl)
		{
			include $pl;
		}
		/* ===== */
		$t->parse('MAIN.DEFAULT');
		break;
}

$is_adminwarnings = isset($adminwarnings);

$t->assign(array(
	'ADMIN_CONFIG_ADMINWARNINGS' => $adminwarnings
));

/* === Hook  === */
$extp = sed_getextplugins('admin.config.tags');
foreach ($extp as $pl)
{
	include $pl;
}
/* ===== */

$t->parse('MAIN');
if (SED_AJAX)
{
	$t->out('MAIN');
}
else
{
	$adminmain = $t->text('MAIN');
}
?>