# WebEngine CMS + OpenMU Integration Setup Guide

## 🚀 Quick Start with XAMPP

### Prerequisites
- XAMPP installed (PHP 8.1+)
- PostgreSQL running with OpenMU database
- WebEngine CMS files in your project directory

### Step 1: Setup XAMPP Environment
1. Run `setup_xampp.bat` to configure your XAMPP environment
2. This will check PHP version and PostgreSQL extensions

### Step 2: Configure WebEngine CMS
1. Open `setup_config.php` in your browser
2. This will copy the OpenMU configuration to `webengine.json`
3. Verify the configuration is correct

### Step 3: Install WebEngine Tables
1. Run `install_webengine_tables.php` in your browser
2. This will create all necessary WebEngine tables in your OpenMU database
3. Verify all tables were created successfully

### Step 4: Test Integration
1. Run `test_openmu_integration.php` in your browser
2. This will test all components of the integration
3. Fix any issues that are reported

### Step 5: Start WebEngine CMS
1. Run `start_webengine.bat` to start the PHP development server
2. Open http://localhost:8000 in your browser
3. WebEngine CMS should now be running with OpenMU integration

## 🔧 Manual Setup (Alternative)

If you prefer to use XAMPP's Apache instead of PHP's built-in server:

### Using XAMPP Apache
1. Copy the WebEngine files to `C:\xampp\htdocs\webengine\`
2. Start Apache in XAMPP Control Panel
3. Open http://localhost/webengine/ in your browser

### Configuration Files
- `includes/config/webengine.json` - Main configuration
- `includes/config/openmu.tables.php` - OpenMU table mappings
- `includes/functions/openmu.php` - OpenMU helper functions

## 🧪 Testing

### Test Files
- `test_openmu_integration.php` - Complete integration test
- `setup_config.php` - Configuration setup
- `install_webengine_tables.php` - Database table installation

### What to Test
1. **Database Connection** - Verify connection to OpenMU database
2. **Authentication** - Test login with OpenMU accounts
3. **Rankings** - Check if rankings display correctly
4. **Character Profiles** - Verify character data is shown
5. **Guild System** - Test guild rankings and profiles
6. **Admin Panel** - Access admin functionality

## 🐛 Troubleshooting

### Common Issues

#### PHP Extensions Missing
```
Error: PostgreSQL extension not loaded
```
**Solution**: Enable `php_pgsql` and `php_pdo_pgsql` in `php.ini`

#### Database Connection Failed
```
Error: Could not connect to database
```
**Solution**: 
- Check PostgreSQL is running
- Verify database credentials
- Ensure database 'openmu' exists

#### Tables Not Found
```
Error: Table 'data.Account' not found
```
**Solution**: 
- Verify OpenMU database is properly set up
- Check schema names (data, guild, friend, config)
- Run the table installation script

#### Configuration Issues
```
Error: Could not load WebEngine CMS
```
**Solution**:
- Check `webengine.json` exists and is valid
- Verify `server_files` is set to "openmu"
- Ensure all required files are present

## 📁 File Structure

```
WebEngine/
├── includes/
│   ├── config/
│   │   ├── webengine.json (main config)
│   │   ├── webengine.openmu.json (OpenMU template)
│   │   └── openmu.tables.php (table mappings)
│   ├── classes/
│   │   ├── class.database.php (updated for PostgreSQL)
│   │   ├── class.login.openmu.php (OpenMU auth)
│   │   └── class.rankings.openmu.php (OpenMU rankings)
│   └── functions/
│       └── openmu.php (helper functions)
├── install/
│   └── sql/
│       └── openmu/
│           └── webengine_tables.sql (installation script)
├── setup_xampp.bat (XAMPP setup)
├── setup_config.php (config setup)
├── install_webengine_tables.php (table installer)
├── test_openmu_integration.php (integration test)
└── start_webengine.bat (start server)
```

## 🎯 Next Steps

After successful setup:
1. **Customize Configuration** - Adjust server settings in `webengine.json`
2. **Add Content** - Create news, downloads, vote sites
3. **Configure Admin** - Set up admin accounts and permissions
4. **Test Features** - Verify all WebEngine features work with OpenMU
5. **Deploy** - Move to production server when ready

## 📞 Support

If you encounter issues:
1. Check the test results from `test_openmu_integration.php`
2. Verify all prerequisites are met
3. Check the troubleshooting section above
4. Review the WebEngine CMS documentation




