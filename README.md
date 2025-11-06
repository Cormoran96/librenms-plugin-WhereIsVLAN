# WhereIsVLAN - LibreNMS Plugin

A modern LibreNMS plugin to search and display switch ports that have specific VLANs configured.

## Features

- 🔍 **Flexible VLAN Search**: Search for single VLANs, multiple VLANs (comma-separated), or VLAN ranges
- 🎯 **Filter Options**: Exclude access ports (untagged) or trunk ports (tagged) from results
- 📊 **Detailed Information**: View interface names, descriptions, speeds, duplex, and operational status
- 🔗 **Quick Navigation**: Direct links to device and port pages
- 🎨 **Modern UI**: Clean, responsive interface using Bootstrap

## Requirements

- LibreNMS (modern version with plugin system v2 support)
- PHP 7.4 or higher
- LibreNMS with local plugin support enabled

## Installation

### Method 1: Manual Installation (Recommended)

1. Navigate to your LibreNMS installation directory:
   ```bash
   cd /opt/librenms
   ```

2. Create the plugin directory structure:
   ```bash
   mkdir -p app/Plugins/WhereIsVLAN
   mkdir -p resources/views/plugins/whereisvlan
   ```

3. Copy the plugin files:
   ```bash
   # Copy PHP classes
   cp /path/to/plugin/app/Plugins/WhereIsVLAN/*.php app/Plugins/WhereIsVLAN/

   # Copy Blade views
   cp /path/to/plugin/resources/views/plugins/whereisvlan/*.blade.php resources/views/plugins/whereisvlan/
   ```

4. Set proper permissions:
   ```bash
   chown -R librenms:librenms app/Plugins/WhereIsVLAN
   chown -R librenms:librenms resources/views/plugins/whereisvlan
   ```

5. Enable the plugin in LibreNMS:
   - Navigate to: **Overview → Plugins → Plugin Admin**
   - Find "WhereIsVLAN" in the list
   - Click **Enable**

### Method 2: Git Clone (Development)

1. Clone this repository into a temporary directory:
   ```bash
   git clone https://github.com/Cormoran96/librenms-plugin-WhereIsVLAN.git /tmp/whereisvlan
   ```

2. Copy to LibreNMS installation:
   ```bash
   cd /opt/librenms
   mkdir -p app/Plugins/WhereIsVLAN resources/views/plugins/whereisvlan

   cp -r /tmp/whereisvlan/app/Plugins/WhereIsVLAN/* app/Plugins/WhereIsVLAN/
   cp -r /tmp/whereisvlan/resources/views/plugins/whereisvlan/* resources/views/plugins/whereisvlan/

   chown -R librenms:librenms app/Plugins/WhereIsVLAN
   chown -R librenms:librenms resources/views/plugins/whereisvlan
   ```

3. Enable the plugin via the web interface

## Usage

1. Navigate to: **Overview → Plugins → WhereIsVLAN**

2. Enter VLAN ID(s) to search for:
   - **Single VLAN**: `100`
   - **Multiple VLANs**: `100,200,300`
   - **VLAN Range**: `100-110`
   - **Mixed**: `100,200,250-255`

3. Optional filters:
   - ☑️ **Exclude access ports**: Hide untagged (access) ports from results
   - ☑️ **Exclude trunk ports**: Hide tagged (trunk) ports from results

4. Click **Search** to see results organized by:
   - Device (with link to device page)
   - VLAN (with VLAN name)
   - Ports (with links to port pages)

## Features Details

### Search Capabilities

The plugin supports flexible VLAN searching:
- ✅ Single VLAN: `242`
- ✅ Multiple VLANs: `100,200,300`
- ✅ Ascending range: `1984-1999`
- ✅ Descending range: `1999-1984`
- ✅ Mixed: `100,200,242,1984-1999`

### Display Information

For each matching port, the plugin displays:
- Interface name (clickable link to port page)
- Interface description (ifAlias)
- Port type (Access/Trunk with color coding)
- Operational status (up/down with color coding)
- Administrative status (up/down with color coding)
- Interface speed (in Mbps)
- Duplex mode

### Modern Implementation

This is a **modern rewrite** of the original plugin with the following improvements:

- 🏗️ **Modern Architecture**: Uses LibreNMS Plugin System v2 with proper hooks
- 🔒 **Security**: Protected against SQL injection using Laravel Query Builder
- 🎨 **Clean Code**: PSR-12 compliant with proper namespacing
- 📱 **Responsive Design**: Modern Bootstrap-based UI
- ⚡ **Performance**: Optimized database queries with proper indexing
- 🧪 **Validation**: Proper input validation and error handling
- 🔗 **Integration**: Deep links to devices and ports

## Directory Structure

```
WhereIsVLAN/
├── app/
│   └── Plugins/
│       └── WhereIsVLAN/
│           ├── Menu.php           # Menu hook implementation
│           └── Page.php           # Main page logic and queries
├── resources/
│   └── views/
│       └── plugins/
│           └── whereisvlan/
│               ├── menu.blade.php # Menu template
│               └── page.blade.php # Main page template
├── LICENSE
└── README.md
```

## Troubleshooting

### Plugin doesn't appear in menu

1. Check that the plugin is enabled in Plugin Admin
2. Verify file permissions:
   ```bash
   ls -la /opt/librenms/app/Plugins/WhereIsVLAN
   ls -la /opt/librenms/resources/views/plugins/whereisvlan
   ```
3. Clear Laravel cache:
   ```bash
   cd /opt/librenms
   php artisan view:clear
   php artisan cache:clear
   ```

### "No results found" when VLANs exist

1. Verify the VLAN exists in the `vlans` table
2. Check that ports are associated with the VLAN in `ports_vlans` table
3. Ensure devices are actively monitored

### Error messages

- **"VLAN ID must be between 1 and 4094"**: Enter valid VLAN IDs only
- **"Invalid VLAN format"**: Use only numbers, commas, and hyphens (e.g., `100,200-210`)

## Migration from Old Version

If you're upgrading from the old plugin version (`WhereIsVLAN.php` and `WhereIsVLAN.inc.php`):

1. **Backup your old plugin** (optional):
   ```bash
   cd /opt/librenms/html/plugins
   mv WhereIsVLAN WhereIsVLAN.old
   ```

2. **Follow the installation instructions above** for the new version

3. **The old plugin directory** (`html/plugins/WhereIsVLAN/`) is no longer needed and can be removed

4. **Database schema**: No changes needed - the plugin uses the same database tables

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.

## Credits

- Original plugin concept and implementation
- Modernized by the LibreNMS community
- Maintained by [Cormoran96](https://github.com/Cormoran96)

## Support

- **Issues**: [GitHub Issues](https://github.com/Cormoran96/librenms-plugin-WhereIsVLAN/issues)
- **Discussions**: [LibreNMS Community](https://community.librenms.org/)
- **Documentation**: [LibreNMS Plugin System](https://docs.librenms.org/Extensions/Plugin-System/)
