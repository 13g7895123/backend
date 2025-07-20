# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a CodeIgniter 4 backend application that serves multiple domains and applications:

- **Casino**: Gaming content management, articles, game guides, user management
- **Promotion**: Promotional campaigns, user verification, LINE integration
- **Jiachu**: Food/product category and file management system
- **Rootadviser**: Email/advisory services

The application uses a modular controller structure with shared base classes and models.

## Architecture

### Controller Structure
- `BaseController`: Abstract base class for all controllers
- Domain-specific namespaces: `Casino`, `Promotion`, `Jiachu`, `Rootadviser`
- Common base controllers in each domain (e.g., `Casino\Common\CommonBaseController`)
- API endpoints primarily use POST/GET with some PUT/DELETE operations

### Model Architecture
- `M_Common`: Shared base model with database abstraction methods
- Domain-specific models extend common functionality
- Multiple database connection support via `setDatabase()` method
- Models follow naming convention: `M_ModelName` or `ModelNameModel`

### Database Configuration
- Multi-database setup with connection switching
- Primary connection: 'promotion' database
- Database migrations located in `src/app/Database/Migrations/`
- Session storage and file management tables

### Key Features
- **CORS Support**: Configured via `CorsFilter` and `Cors` config
- **File Management**: Upload/download capabilities across domains
- **LINE Integration**: OAuth callback and notification system
- **Multi-language**: English language files in `src/system/Language/en/`
- **Session Management**: File-based sessions in `writable/session/`

## Development Commands

### PHP Server
```bash
# Start development server (standard CodeIgniter)
cd src
php -S localhost:8080

# Or using CodeIgniter's built-in server
php spark serve
```

### Database Operations
```bash
# Run migrations
php spark migrate

# Create new migration
php spark make:migration MigrationName

# Seed database
php spark db:seed SeederName
```

### Code Generation
```bash
# Create controller
php spark make:controller ControllerName

# Create model
php spark make:model ModelName

# Create filter
php spark make:filter FilterName
```

### Cache Management
```bash
# Clear cache
php spark cache:clear

# Clear logs
php spark housekeeping:clearlogs
```

## Important Configuration

### Base URL Configuration
- Production URL: `https://backend.mercylife.cc/`
- Configured in `src/app/Config/App.php:19`

### Database Connections
- Primary: 'promotion' database
- Connection switching via `M_Common::setDatabase()`

### API Routes Structure
- All API routes under `/api` prefix
- Domain-specific route groups: `/api/promotion`, `/api/casino`, etc.
- OPTIONS requests handled globally for CORS

### File Upload Locations
- Images: `writable/uploads/images/`
- Casino images: `writable/uploads/images/casino/`

## Testing

The codebase includes CodeIgniter 4's testing framework. Run tests using:

```bash
php spark test
```

## Security Notes

- CORS filter implemented for cross-origin requests
- Authentication filters in place (`AuthFilter`)
- File upload security via dedicated controllers
- Session management with secure configuration