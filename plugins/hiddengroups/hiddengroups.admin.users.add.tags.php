<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin.users.add.tags
[END_COT_EXT]
==================== */

/**
 * Hidden groups
 *
 * @package hiddengroups
 * @version 1.0
 * @author Koradhil, Cotonti Team
 * @copyright Copyright (c) Cotonti Team 2008-2011
 * @license BSD
 */

(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

$t->assign('ADMIN_USERS_NGRP_HIDDEN', cot_radiobox(0, 'rhidden', array(1, 0), array($L['Yes'], $L['No'])));

?>