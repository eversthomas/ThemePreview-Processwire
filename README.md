# Theme Preview Module for ProcessWire

A ProcessWire module that enables safe theme development and testing by allowing you to preview a new theme while keeping your production theme live.

## Features

- 🎨 **Easy Theme Switching** - Toggle between production and preview themes with one click
- 👀 **Private Preview** - Only logged-in users with permissions see the preview theme
- 📊 **Template Comparison** - Visual overview showing which template files exist in each theme
- 🔒 **Safe Development** - Develop your new theme without affecting live visitors
- 🍪 **Cookie-Based** - Preview state persists across sessions
- ⚡ **No Page Reload** - Toggle preview mode without refreshing the backend

## Installation

1. Download or clone this repository into `/site/modules/ThemePreview/`
2. In ProcessWire admin, go to **Modules → Refresh**
3. Click **Install** next to "Theme Preview"
4. Copy your current theme folder:
   ```
   /site/templates/ → /site/templates-new/
   ```
5. Configure the module (Modules → Configure → Theme Preview)

## Usage

### Basic Setup

1. **Configure Theme Paths**
   - Go to **Modules → Configure → Theme Preview**
   - Set your production theme path (default: `templates`)
   - Set your preview theme path (default: `templates-new`)

2. **Activate Preview Mode**
   - Click the "Preview aktivieren" button in the module configuration
   - Your frontend will open in a new tab showing the preview theme
   - Only you (with the cookie set) will see the preview theme

3. **Toggle Between Themes**
   - Use the toggle button to switch between production and preview
   - The button state updates instantly without page reload
   - The status indicator shows which theme is currently active

4. **Template Comparison**
   - View the template overview in the module configuration
   - See which templates exist in both themes
   - Identify missing or new template files

### Access Control

**User Permissions:**
- Restrict preview access to specific users in the module configuration
- If no users are selected, all logged-in users can use preview mode
- Non-logged-in visitors always see the production theme

**Cookie Duration:**
- Configure how long the preview state persists (default: 30 days)
- After expiration, preview mode must be reactivated

### Going Live

When your new theme is ready:

1. Deactivate preview mode
2. Backup your production theme (via FTP/SSH):
   ```
   /site/templates/ → /site/templates-backup/
   ```
3. Replace production with preview:
   ```
   /site/templates-new/ → /site/templates/
   ```
4. Done! 🎉

## Configuration Options

| Setting | Description | Default |
|---------|-------------|---------|
| Production Theme Path | Relative path from `/site/` | `templates` |
| Preview Theme Path | Relative path from `/site/` | `templates-new` |
| Enabled Users | Users allowed to use preview mode | All logged-in users |
| Cookie Duration | How long preview stays active (days) | 30 |

## Commented Features

The module includes commented-out code for potential future features:

- **IP Whitelist** - Restrict preview access by IP address
- **A/B Testing** - Show preview theme to random visitors for testing

These can be uncommented and customized if needed.

## Requirements

- ProcessWire 3.0 or newer
- PHP 7.4 or newer

## Disclaimer

**This module was developed with AI assistance (Claude by Anthropic).**

**NO WARRANTY:** This software is provided "as is" without warranty of any kind, express or implied. Use at your own risk. Always backup your site before testing new themes or modules.

## License

MIT License

Copyright (c) 2025

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

## Contributing

Contributions, issues, and feature requests are welcome!

## Support

For questions or issues, please open an issue on GitHub.

## Changelog

### Version 1.0.1
- Initial release
- Theme switching functionality
- Template comparison view
- User access control
- Cookie-based preview state
- JavaScript-based instant toggle

---

**Happy theme developing! 🎨**
