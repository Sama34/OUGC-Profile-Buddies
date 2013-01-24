<?php

/***************************************************************************
 *
 *   OUGC Profile Buddies plugin ()
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Shows a formatted list of users buddies in their profiles.
 *
 ***************************************************************************
 
****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// Run the hooks.
if(!defined('IN_ADMINCP') && defined('THIS_SCRIPT') && THIS_SCRIPT == 'member.php')
{
	global $mybb;

	if($mybb->input['action'] == 'profile')
	{
		global $templatelist;

		$plugins->add_hook('member_profile_end', 'ougc_profilebuddies_run');

		if(isset($templatelist))
		{
			$templatelist .= ',';
		}

		$templatelist .= ', ougc_profilebuddies, ougc_profilebuddies_empty, ougc_profilebuddies_user, multipage_page_current, multipage_page, multipage_nextpage, multipage';

		define('OUGC_PROFILEBUDDIES_USEMYBBURLS', 1);
	}
}

// Necessary plugin information for the ACP plugin manager.
function ougc_profilebuddies_info()
{
	global $lang;
	$lang->load('ougc_profilebuddies');

	return array(
		'name'			=> 'OUGC Profile Buddies',
		'description'	=> $lang->ougc_profilebuddies_plugin_d,
		'website'		=> 'http://udezain.com.ar/',
		'author'		=> 'Omar Gonzalez',
		'authorsite'	=> 'http://udezain.com.ar/',
		'version'		=> '1.0',
		'compatibility'	=> '16*'
	);
}

// Activate the plugin.
function ougc_profilebuddies_activate()
{
	global $lang, $db;
	$lang->load('ougc_profilebuddies');
	ougc_profilebuddies_deactivate();

	// Add our settings group.
	$gid = $db->insert_query('settinggroups', 
		array(
			'name'			=> 'ougc_profilebuddies',
			'title'			=> $db->escape_string($lang->ougc_profilebuddies_settings),
			'description'	=> $db->escape_string($lang->ougc_profilebuddies_plugin_d),
			'disporder'		=> 15,
			'isdefault'		=> 'no'
		)
	);
	$gid = intval($gid);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_multipage',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_multipage),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_multipage_d),
			'optionscode'	=>	'onoff',
			'value'			=>	1,
			'disporder'		=>	1,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_limit',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_limit),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_limit_d),
			'optionscode'	=>	'text',
			'value'			=>	10,
			'disporder'		=>	2,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_orderby',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_orderby),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_orderby_d),
			'optionscode'	=>	"select
username={$db->escape_string($lang->ougc_profilebuddies_orderby_username)}
regdate={$db->escape_string($lang->ougc_profilebuddies_orderby_regdate)}
RAND()={$db->escape_string($lang->ougc_profilebuddies_orderby_random)}
			",
			'value'			=>	'username',
			'disporder'		=>	3,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_orderdir',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_orderdir),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_orderdir_d),
			'optionscode'	=>	"select
asc={$db->escape_string($lang->ougc_profilebuddies_orderdir_asc)}
desc={$db->escape_string($lang->ougc_profilebuddies_orderdir_desc)}
			",
			'value'			=>	'asc',
			'disporder'		=>	4,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_davatar',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_davatar),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_davatar_d),
			'optionscode'	=>	'text',
			'value'			=>	$GLOBALS['settings']['bburl'].'/images/avatars/athlon.gif',
			'disporder'		=>	5,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_ddim',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_ddim),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_ddim_d),
			'optionscode'	=>	'text',
			'value'			=>	'73|73',
			'disporder'		=>	6,
			'gid'			=>	$gid
		)
	);
	$db->insert_query('settings',
		array(
			'name'			=>	'ougc_profilebuddies_maxdim',
			'title'			=>	$db->escape_string($lang->ougc_profilebuddies_maxdim),
			'description'	=>	$db->escape_string($lang->ougc_profilebuddies_maxdim_d),
			'optionscode'	=>	'text',
			'value'			=>	'35x35',
			'disporder'		=>	7,
			'gid'			=>	$gid
		)
	);
	rebuild_settings();

	// Add our templates.
	$db->insert_query('templates', 
		array(
			'title'		=>	'ougc_profilebuddies',
			'template'	=>	$db->escape_string('<br />
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead" colspan="2"><strong>{$lang->ougc_profilebuddies}</strong></td>
</tr>
{$ougc_profilebuddies_list}
</table>
{$multipage}'),
			'sid'		=>	-1
		)
	);
	$db->insert_query('templates', 
		array(
			'title'		=>	'ougc_profilebuddies_empty',
			'template'	=>	$db->escape_string('<tr>
<td class="trow1" colspan="2">{$lang->ougc_profilebuddies_empty}</td>
</tr>'),
			'sid'		=>	-1
		)
	);
	$db->insert_query('templates', 
		array(
			'title'		=>	'ougc_profilebuddies_user',
			'template'	=>	$db->escape_string('<tr valign="top">
	<td class="{$bg_color}" width="1">
		<img src="{$user[\'avatar\']}" alt="" width="{$scaled_dimensions[\'width\']}" height="{$scaled_dimensions[\'height\']}" />
	</td>
	<td class="{$bg_color}">
		<img src="{$mybb->settings[\'bburl\']}/images/buddy_{$onlinestatus}.gif" alt="" title="{$lang_val}" style="vertical-align: top;" />{$user[\'profilelink\']}<br/>{$groupimage}
	</td>
</tr>'),
			'sid'		=>	-1
		)
	);

	// Remove added variables.
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile', '#'.preg_quote('{$signature}').'#', '{$signature}{$ougc_profilebuddies}');
}

// Deactivate the plugin.
function ougc_profilebuddies_deactivate()
{
	global $db;

	// Delete setting group.
	$query = $db->simple_select('settinggroups', 'gid', 'name="ougc_profilebuddies"');
	$gid = $db->fetch_field($query, 'gid');
	if($gid)
	{
		$db->delete_query('settings', "gid='{$gid}'");
		$db->delete_query('settinggroups', "gid='{$gid}'");
		rebuild_settings();
	}
	$db->free_result($query);

	// Delete any old templates.
	$db->delete_query('templates', "title IN('ougc_profilebuddies', 'ougc_profilebuddies_empty', 'ougc_profilebuddies_user') AND sid='-1'");

	// Remove added variables.
	require_once MYBB_ROOT.'/inc/adminfunctions_templates.php';
	find_replace_templatesets('member_profile', '#'.preg_quote('{$ougc_profilebuddies}').'#', '', 0);
}

// Run our function to show the affiliates box
function ougc_profilebuddies_run()
{
	global $mybb;

	$limit = intval($mybb->settings['ougc_profilebuddies_limit']);
	if($limit < 1)
	{
		return;
	}

	global $theme, $lang, $memprofile, $templates, $db, $ougc_profilebuddies, $cache;
	$lang->load('ougc_profilebuddies');

	$usertitles = $cache->read('usertitles');
	$memprofile['username'] = htmlspecialchars_uni($memprofile['username']);
	$lang->ougc_profilebuddies = $lang->sprintf($lang->ougc_profilebuddies, $memprofile['username']);
	if(empty($memprofile['buddylist']))
	{
		eval("\$ougc_profilebuddies_list = \"".$templates->get("ougc_profilebuddies_empty")."\";");
	}
	else
	{
		$options = array(
			'order_by'	=>	$db->escape_string($mybb->settings['ougc_profilebuddies_orderby']),
			'order_dir'	=>	($mybb->settings['ougc_profilebuddies_orderdir'] == 'asc' ? 'asc' : 'desc'),
			'limit'	=>	$limit,
		);

		// Add pagination
		$count = count(explode(',', $memprofile['buddylist']));
		if($mybb->settings['ougc_profilebuddies_multipage'] == 1)
		{
			$page = intval($mybb->input['page']);
			if($page > 0 && $mybb->input['view'] == 'buddies')
			{
				$options['limit_start'] = ($page-1) * $limit;
				$pages = ceil($count / $limit);
				if($page > $pages)
				{
					$options['limit_start'] = 0;
					$page = 1;
				}
			}
			else
			{
				$options['limit_start'] = 0;
				$page = 1;
			}

			$url = get_profile_link($memprofile['uid']);
			$multipage = multipage($count, $limit, $page, $url.(strpos($url, '?') === false ? '?' : '&amp;').'view=buddies');
		}

		// Now query users.
		$count = my_number_format($count);
		$comma = '';
		$query = $db->simple_select('users', 'uid, postnum, username, avatar, avatardimensions, usergroup, displaygroup, usertitle, lastactive, invisible, lastvisit', "uid IN ({$memprofile['buddylist']})", $options);
		while($user = $db->fetch_array($query))
		{
			$bg_color = alt_trow();
			$usergroup = ($GLOBALS['groupscache'][$user['displaygroup']] ? $GLOBALS['groupscache'][$user['displaygroup']] : $GLOBALS['groupscache'][$user['usergroup']]);
			$user['username'] = htmlspecialchars_uni($user['username']);
			$user['username_formatted'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
			if(defined('OUGC_PROFILEBUDDIES_USEMYBBURLS') && OUGC_PROFILEBUDDIES_USEMYBBURLS)
			{
				$profilelink = get_profile_link($user['uid']);
				$user['profilelink'] = build_profile_link($user['username_formatted'], $user['uid']);
			}
			else
			{
				if(!$user['username'] && !$user['uid'])
				{
					$user['profilelink'] = $lang->guest;
				}
				elseif(!$user['uid'])
				{
					$user['profilelink'] = $user['username_formatted'];
				}
				else
				{
					$profilelink = htmlspecialchars_uni(str_replace('{uid}', $user['uid'], PROFILE_URL));
					$user['profilelink'] = "<a href=\"{$mybb->settings['bburl']}/{$profilelink}\">{$user['username_formatted']}</a>";
				}
			}

			// Figure this user online status.
			if($user['lastactive'] > (TIME_NOW-$mybb->settings['wolcutoff']) && ($user['invisible'] != 1 || $mybb->usergroup['canviewwolinvis'] == 1) && $user['lastvisit'] != $user['lastactive'])
			{
				$onlinestatus = 'online';
			}
			elseif($user['away'] == 1 && $mybb->settings['allowaway'] != 0)
			{
				$onlinestatus = 'away';
			}
			else
			{
				$onlinestatus = 'offline';
			}
			$lang_val = 'postbit_status_'.$onlinestatus;
			$lang_val = $lang->$lang_val;

			// Avatar stuff...
			if(empty($user['avatar']))
			{
				$user['avatar'] = $mybb->settings['ougc_profilebuddies_davatar'];
				$user['avatardimensions'] = $mybb->settings['ougc_profilebuddies_ddim'];
			}
			$user['avatar'] = htmlspecialchars_uni($user['avatar']);
			$avatar_dimensions = explode('|', $user['avatardimensions']);
			if($avatar_dimensions[0] && $avatar_dimensions[1])
			{
				list($max_width, $max_height) = explode('x', $mybb->settings['ougc_profilebuddies_maxdim']);
				if($avatar_dimensions[0] > $max_width || $avatar_dimensions[1] > $max_height)
				{
					require_once MYBB_ROOT.'inc/functions_image.php';
					$scaled_dimensions = scale_image($avatar_dimensions[0], $avatar_dimensions[1], $max_width, $max_height);
				}
				else
				{
					$scaled_dimensions['width'] = $avatar_dimensions[0];
					$scaled_dimensions['width'] = $avatar_dimensions[1];
				}
			}

			// User title
			if(trim($user['usertitle']) != '')
			{
				$user['usertitle'] = $user['usertitle'];
			}
			elseif(trim($usergroup['usertitle']) != '')
			{
				$user['usertitle'] = $usergroup['usertitle'];
			}
			elseif(is_array($usertitles))
			{
				foreach($usertitles as $title)
				{
					if($user['postnum'] >= $title['posts'])
					{
						$user['usertitle'] = $title['title'];
						break;
					}
				}
			}
			$user['usertitle'] = htmlspecialchars_uni($user['usertitle']);

			// Too empty, lets get the group image too.
			$groupimage = '';
			if(!empty($usergroup['image']))
			{
				$displaygroup['image'] = htmlspecialchars_uni($usergroup['image']);
				eval("\$groupimage = \"".$templates->get("member_profile_groupimage")."\";");
			}

			// Format the row..
			eval("\$ougc_profilebuddies_list .= \"".$templates->get("ougc_profilebuddies_user")."\";");
			$comma = ', ';
		}
		$db->free_result($query);
	}
	$count = ($count > 0 ? $count : 0);
	eval("\$ougc_profilebuddies = \"".$templates->get("ougc_profilebuddies")."\";");
}