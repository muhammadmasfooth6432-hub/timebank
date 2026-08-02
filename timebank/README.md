# Time Bank Peer-to-Peer Exchange Platform

A community-driven cashless service exchange system where users exchange skills using category-based credits.

## Requirements

- XAMPP (Apache, MySQL, PHP 7.4+)
- Modern web browser
- PowerShell (Windows) or Terminal (Mac/Linux)

## Installation

1. Clone or extract project to `C:\xampp\htdocs\timebank`

2. Start Apache and MySQL via XAMPP Control Panel

3. Create database:
   - Open http://localhost/phpmyadmin
   - Create database: `timebank_db`
   - Import `database/timebank_db.sql`

4. Configure (optional):
   - Edit `config/config.php` for custom settings

5. Access application:
   - Open http://localhost/timebank

## Default Credentials (for testing)

No default accounts - register a new user to begin.

## Folder Structure

See project documentation for complete structure.

## Security Notes

- Change database password in production
- Set `display_errors` to 0 in production
- Enable HTTPS before deploying
- Review file permissions on uploads folder

## License

MIT License - See LICENSE file for details.