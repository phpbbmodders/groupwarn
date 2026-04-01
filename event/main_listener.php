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
		$group_warn = $this->query_warn_groups();

		if (in_array($event['user_row']['user_id'], $group_warn) && $this->user->data['user_type'] != USER_FOUNDER)
		{
			trigger_error($this->language->lang('CANNOT_WARN_USER_GROUP'));
		}
	}

	/**
	 * Modify user data before displaying the profile
	 */
	public function memberlist_view_profile($event)
	{
		$group_warn = $this->query_warn_groups();

		if (in_array($event['member']['user_id'], $group_warn) && $this->user->data['user_type'] != USER_FOUNDER)
		{
			$event['warn_user_enabled'] = false;
		}
	}

	public function viewtopic_modify_post_action_conditions($event)
	{
		$group_warn = $this->query_warn_groups();

		if (in_array($event['row']['user_id'], $group_warn) && $this->user->data['user_type'] != USER_FOUNDER)
		{
			$event['warn_allowed'] = false;
		}
	}

	private function query_warn_groups()
	{
		$sql_array = [
			'SELECT'	=> 'g.group_id, g.group_warn, ug.user_id, ug.group_id',

			'FROM'		=> [
				$this->table_prefix . 'groups'	=> 'g',
			],

			'LEFT_JOIN' => [
				[
					'FROM'	=> [
						$this->table_prefix . 'user_group'	=> 'ug'
					],
					'ON'	=> 'g.group_id = ug.group_id'
				],
			],

			'WHERE'		=> 'g.group_warn = 0'
		];

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$group_warn = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$group_warn[] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		return $group_warn;
	}
}
