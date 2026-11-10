<?php
/**
 *
 * Group Warn extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\groupwarn\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Group Warn event listener
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string */
	protected $table_prefix;

	/** @var bool[] Whether each user already looked up may not be warned, keyed by user ID */
	protected $cannot_warn = [];

	/**
	 * Constructor
	 *
	 * @param \phpbb\db\driver\driver_interface  $db
	 * @param \phpbb\language\language           $language
	 * @param \phpbb\request\request             $request
	 * @param \phpbb\template\template           $template
	 * @param \phpbb\user                        $user
	 * @param string                             $table_prefix
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $table_prefix)
	{
		$this->db = $db;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->table_prefix = $table_prefix;
	}

	/**
	 * Map phpBB core events to the listener methods that should handle those events
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'	=> 'user_setup',

			// ACP
			'core.acp_manage_group_request_data'	=> 'acp_manage_group_request_data',
			'core.acp_manage_group_initialise_data'	=> 'acp_manage_group_initialise_data',
			'core.acp_manage_group_display_form'	=> 'acp_manage_group_display_form',

			// MCP
			'core.mcp_warn_post_before'	=> 'mcp_warn_before',
			'core.mcp_warn_user_before'	=> 'mcp_warn_before',

			// Memberlist
			'core.memberlist_view_profile'	=> 'memberlist_view_profile',

			// Viewtopic
			'core.viewtopic_modify_post_action_conditions'	=> 'viewtopic_modify_post_action_conditions',
		];
	}

	/**
	 * Load common language files
	 */
	public function user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'phpbbmodders/groupwarn',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Request group data and operate on it
	 */
	public function acp_manage_group_request_data($event)
	{
		$event->update_subarray('submit_ary', 'warn', $this->request->variable('group_warn', 0));
	}

	/**
	 * Initialise data before displaying the add/edit form
	 */
	public function acp_manage_group_initialise_data($event)
	{
		$event->update_subarray('test_variables', 'warn', 'int');
	}

	/**
	 * Modify group template data before displaying the form
	 */
	public function acp_manage_group_display_form($event)
	{
		$this->template->assign_vars([
			'GROUP_WARN'	=> (!empty($event['group_row']['group_warn'])) ? ' checked="checked"' : '',
		]);
	}

	/**
	 * Event for before warning a user
	 */
	public function mcp_warn_before($event)
	{
		if ($this->user_cannot_be_warned((int) $event['user_row']['user_id']))
		{
			trigger_error($this->language->lang('CANNOT_WARN_USER_GROUP'));
		}
	}

	/**
	 * Modify user data before displaying the profile
	 */
	public function memberlist_view_profile($event)
	{
		if ($this->user_cannot_be_warned((int) $event['member']['user_id']))
		{
			$event['warn_user_enabled'] = false;
		}
	}

	/**
	 * Hide the warn button on posts by users who may not be warned
	 */
	public function viewtopic_modify_post_action_conditions($event)
	{
		if ($this->user_cannot_be_warned((int) $event['row']['user_id']))
		{
			$event['warn_allowed'] = false;
		}
	}

	/**
	 * Whether the current user may not warn a user because of the user's groups
	 *
	 * A user can be warned only when every group they are a full member of
	 * (pending memberships do not count) has Group warn ticked. Founders can
	 * always warn. Each user is looked up once per request, because the
	 * viewtopic check runs for every post on the page.
	 *
	 * @param int $user_id The user who would be warned
	 * @return bool True if the warning must be refused
	 */
	protected function user_cannot_be_warned(int $user_id): bool
	{
		if ((int) $this->user->data['user_type'] === USER_FOUNDER)
		{
			return false;
		}

		if (!isset($this->cannot_warn[$user_id]))
		{
			$sql = 'SELECT ug.group_id
				FROM ' . $this->table_prefix . 'user_group ug, ' . $this->table_prefix . 'groups g
				WHERE ug.user_id = ' . $user_id . '
					AND ug.user_pending = 0
					AND g.group_id = ug.group_id
					AND g.group_warn = 0';
			$result = $this->db->sql_query_limit($sql, 1);
			$this->cannot_warn[$user_id] = (bool) $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);
		}

		return $this->cannot_warn[$user_id];
	}
}
