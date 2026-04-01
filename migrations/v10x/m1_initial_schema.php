<?php
/**
 *
 * Group Warn extension for the phpBB Forum Software package
 *
 * @copyright (c) 2026, phpBB Modders, https://www.phpbbmodders.com/
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbmodders\groupwarn\migrations\v10x;

class m1_initial_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->db_tools->sql_column_exists($this->table_prefix . 'groups', 'group_warn');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v33x\v3316'];
	}

	/**
	 * Update database schema
	 */
	public function update_schema()
	{
		return [
			'add_columns'	=> [
				$this->table_prefix . 'groups'			=> [
					'group_warn'				=> ['UINT', 0],
				],
			],
		];
	}

	/**
	 * Revert database schema
	 */
	public function revert_schema()
	{
		return [
			'drop_columns'	=> [
				$this->table_prefix . 'groups'			=> [
					'group_warn',
				],
			],
		];
	}
}
