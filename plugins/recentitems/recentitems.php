<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=standalone
[END_COT_EXT]
==================== */

/**
 * Recent pages, topics in forums, users, comments
 *
 * @package recentitems
 * @version 0.7.0
 * @author esclkm, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2010
 * @license BSD
 */

defined('SED_CODE') or die("Wrong URL.");

$days = sed_import('days', 'G', 'INT');
$d = sed_import('d', 'G', 'INT');
if (empty($d)) $d = '0';
$mode = sed_import('mode', 'G', 'TXT');

if ($days == 0)
{
	if ($usr['id'] > 0)
	{
		$timeback = $usr['lastvisit'];
	}
	else
	{
		$days = 1;
	}
}
if ($days > 0)
{
	$timeminus = $days * 86400;
	$timeback = $sys['now_offset'] - $timeminus;
}

sed_require('users');
sed_require('recentitems', true);
$totalrecent[] = 0;
if ($cfg['plugin']['recentitems']['newpages'] && $cfg['module']['page'] && !$cfg['disable_page'] && (empty($mode) || $mode == 'pages'))
{
	sed_require('page');
	$res = sed_build_recentpages('recentitems.pages', $timeback, $cfg['plugin']['recentitems']['itemsperpage'], $d, $pagetitlelimit, $cfg['plugin']['recentitems']['newpagestext'], $cfg['plugin']['recentitems']['rightscan']);
	$t->assign("RECENT_PAGES", $res);
}

if ($cfg['plugin']['recentitems']['newforums'] && $cfg['module']['forums'] && !$cfg['disable_forums'] && (empty($mode) || $mode == 'forums'))
{
	sed_require('forums');
	$res = sed_build_recentforums('recentitems.forums', $timeback, $cfg['plugin']['recentitems']['itemsperpage'], $d, $forumtitlelimit, $cfg['plugin']['recentitems']['rightscan']);
	$t->assign("RECENT_FORUMS", $res);
}

if ($cfg['plugin']['recentitems']['newadditional'] && ($mode  != 'pages' || $mode != 'forums'))
{
	/* === Hook === */
	$extp = sed_getextplugins('recentitems.tags');
	foreach ($extp as $pl)
	{
		include $pl;
	}
	/* ===== */
}

$totalpages = max($totalrecent);
$days =($days > 0) ? "&amp;days=".$days : "";
$mode=(!empty($mode)) ? "&amp;mode=".$mode : "";
$pagenav = sed_pagenav('plug', 'e=recentitems'.$days.$mode, $d, $totalpages, $cfg['plugin']['recentitems']['itemsperpage']);

$t->assign(array(
	"PAGE_PAGENAV" => $pagenav['main'],
	"PAGE_PAGEPREV" => $pagenav['prev'],
	"PAGE_PAGENEXT" => $pagenav['next']
));

?>