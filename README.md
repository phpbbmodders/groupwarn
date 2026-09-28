# Group Warn

[![Tests](https://github.com/phpbbmodders/groupwarn/actions/workflows/tests.yml/badge.svg)](https://github.com/phpbbmodders/groupwarn/actions/workflows/tests.yml) [![Lint](https://github.com/phpbbmodders/groupwarn/actions/workflows/lint.yml/badge.svg)](https://github.com/phpbbmodders/groupwarn/actions/workflows/lint.yml)

Lets administrators choose, per group, whether that group's members can be warned.

## Features

- Adds a **Group warn** setting to each group in **ACP → Users and Groups → Manage groups**.
- A member can only be warned if every group they belong to has **Group warn** ticked.
- For members who can't be warned, the warn button is hidden on their posts and profile, and warning them through the MCP is refused.
- Board founders can always warn anyone.

**Note:** no group is ticked after installation, so nobody can be warned (except by founders) until you tick the groups whose members may be warned, usually at least **Registered users**.

## Requirements

- phpBB 3.3.17 or later
- PHP 8.2 or later

## Installation

1. Copy the extension to `/ext/phpbbmodders/groupwarn`
2. In the Administration Control Panel, go to **Customise → Manage extensions**
3. Enable the **Group Warn** extension
4. Tick **Group warn** on the groups whose members may be warned

## Contributing

Contributions are welcome!

- **Bug reports**: [Open an issue](https://github.com/phpbbmodders/groupwarn/issues).
- **Everything else** (questions, feature requests, ideas, general discussion): [Use Discussions](https://github.com/orgs/phpbbmodders/discussions), or the [community forum](https://www.phpbbmodders.com/community/).
- Pull requests are welcome for bug fixes or discussed features.

## Acknowledgments

- Code review, bug fixes, and documentation assisted by [Claude](https://www.anthropic.com/claude).

## License

This extension is licensed under the **GNU General Public License v2.0**.

See [license.txt](license.txt) for more information.
