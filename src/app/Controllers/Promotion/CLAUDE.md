# CLAUDE.md - Promotion Module

This file provides guidance for working with the Promotion module in this CodeIgniter 4 application.

## Module Overview

The Promotion module is a comprehensive system for managing promotional campaigns across multiple game servers. It handles user registration, campaign management, file uploads, reward distribution, and notifications through email and LINE messenger.

## Database Architecture

### Core Tables

#### **promotions** (5,018 rows) - Main Entity
```sql
id (PRIMARY KEY), user_id, server, status (standby/success/failed), 
created_at, updated_at
```
- Central table tracking all promotional campaigns
- Links to users and servers via foreign keys
- Status-based workflow management

#### **promotion_items** (27,508 rows) - Content Management
```sql
id (PRIMARY KEY), promotion_id, type (image/text), content, 
status (standby/success/failed), created_at, updated_at
```
- Stores promotional content (images and text/URLs)
- Linked to promotions via promotion_id
- Supports multiple content items per promotion

#### **users** (10 rows) - System Users
```sql
id (PRIMARY KEY), account, password, type (user/admin/jarvis), 
switch, created_at, updated_at
```
- System administrators and regular users
- Role-based access control (admin/user/jarvis)
- Controls access to different servers

#### **player** (358 rows) - Game Players
```sql
id (PRIMARY KEY), username, server, character_name, email, line_id,
notify_email, notify_line, created_at, updated_at
```
- Game players who participate in promotions
- Multi-server support via server field
- Notification preferences for email and LINE

#### **server** (8 rows) - Game Server Configuration
```sql
id (PRIMARY KEY), code, name, require_character, cycle, limit_number,
notify_email, notify_line, created_at, updated_at
```
- Configuration for different game servers
- Controls promotion cycles and limits
- Server-specific notification settings

### Supporting Tables

#### **files** (15,405 rows) - File Management
```sql
id (PRIMARY KEY), name, path, type, size, uploaded_at
```
- Central file storage for uploaded content
- Referenced by promotion_items for image content

#### **reward** (2,373 rows) - Reward Distribution
```sql
id (PRIMARY KEY), promotion_id, player_id, server_code, reward, 
insert_id, created_at
```
- Tracks rewards distributed to players
- Links promotions to specific players and servers

#### **line** (31 rows) - LINE Integration
```sql
id (PRIMARY KEY), player_id, uid, name, image_url, email, created_at
```
- LINE messenger user data
- OAuth integration for notifications

#### **token** (4,061 rows) - Access Control
```sql
id (PRIMARY KEY), token, server, user_id, page, is_used, created_at
```
- Token-based authentication for frontend access
- Server-specific token validation

### Permission & Configuration Tables

#### **user_server_permissions** (18 rows)
```sql
id (PRIMARY KEY), user_id, server_code
```
- Maps users to servers they can manage

#### **customized_db** (7 rows)
```sql
id (PRIMARY KEY), server_code, name, host, port, account, password, 
account_field, character_field
```
- Database connection configurations for different game servers

#### **server_image** (86 rows)
```sql
id (PRIMARY KEY), server_code, type (icon/background), file_id, is_selected
```
- Server-specific images (icons and backgrounds)

## Controller Architecture

### **Promotion Controller** (`src/app/Controllers/Promotion/Promotion.php`)
**Primary Functions:**
- `index()` - List promotions with filtering (all/finished)
- `create()` - Create new promotion campaigns
- `delete()` - Remove promotion data
- `batchAudit()` - Batch approve/reject promotions

**Key Features:**
- Role-based filtering (admin vs user permissions)
- Server permission validation
- Promotion detail aggregation with images and links
- Status workflow management

### **Player Controller** (`src/app/Controllers/Promotion/Player.php`)
**Primary Functions:**
- `index()` - List players with server filtering
- `delete()` - Remove player data
- `submit()` - Player registration and validation
- `getPlayerInfo()` - Retrieve player details
- `callback()` - LINE OAuth callback handling

**Key Features:**
- Multi-server player management
- LINE integration for notifications
- Player validation and creation
- Promotion frequency tracking

### **User Controller** (`src/app/Controllers/Promotion/User.php`)
**Primary Functions:**
- `index()` - List system users (excluding admins)
- `create()` - Create new users
- `update()` - Update user information
- `condition()` - Query users by conditions
- `login()` - User authentication

**Key Features:**
- User role management (admin/user/jarvis)
- Server permission assignment
- Email notification settings
- Password management

### **Server Controller** (`src/app/Controllers/Promotion/Server.php`)
**Primary Functions:**
- `getServer()` - Retrieve server configurations
- `create()` - Add new game servers
- `update()` - Modify server settings
- `delete()` - Remove servers (cascading delete)
- `getDatabase()` - Manage database connections

**Key Features:**
- Multi-server configuration management
- Database connection handling
- Server image management
- Promotion cycle and limit configuration

### **File Controller** (`src/app/Controllers/Promotion/File.php`)
**Primary Functions:**
- `upload()` - Handle file uploads
- `show()` - Serve uploaded files

**Key Features:**
- Secure file upload handling
- File type validation
- URL generation for file access

## Model Architecture

### **M_Promotion** (`src/app/Models/Promotion/M_Promotion.php`)
**Key Methods:**
- `create($data)` - Create promotion with duplicate prevention
- `updateData($promotionId, $data)` - Update promotion status
- `deleteData($promotionId)` - Cascade delete promotion
- `getPromotionByFrequency($playerId, $frequency)` - Track promotion limits

**Features:**
- Automatic duplicate prevention (one per day per user/server)
- Status workflow management
- Frequency-based limit enforcement

### **M_Player** (`src/app/Models/Promotion/M_Player.php`)
**Key Methods:**
- `create($data)` - Player registration with validation
- `getPlayerInfo($userId)` - Retrieve player details
- `deleteData($id)` - Remove player data

**Features:**
- Input validation and sanitization
- Username uniqueness enforcement
- Multi-server player support

### **M_Server** (`src/app/Models/Promotion/M_Server.php`)
**Key Methods:**
- `getServer($where, $field, $queryMultiple)` - Flexible server queries
- `deleteData($id)` - Cascade delete server and related data

**Features:**
- Cascading deletes (players, promotions, images)
- Multi-condition querying

## API Endpoints Structure

### Frontend API Routes (`/api/promotion/`)
```
POST /login                    - User authentication
POST /server                   - Get server data
POST /player/submit            - Player registration
POST /player/info              - Get player information
POST /main                     - Get promotions (filtered)
POST /main/delete              - Delete promotions
POST /main/batchAudit          - Batch audit promotions
GET  /detail/{id}              - Get promotion details
PUT  /detail/update/{id}       - Update promotion
POST /detail/url/check         - Validate URLs
POST /file                     - Upload files
GET  /file/show/{id}           - Display files
POST /                         - Create promotion
DELETE /{id}                   - Delete promotion
POST /items                    - Create promotion items
```

### Backend Management Routes (`/api/promotion/`)
```
GET/POST /user                 - User management
POST /user/create              - Create users
POST /user/update              - Update users
POST /user/condition           - Query users
GET  /manager                  - Get managers
POST /player                   - Player management
POST /player/delete            - Delete players
POST /server/*                 - Server management endpoints
```

## Key Features & Workflows

### 1. **Promotion Workflow**
1. Player submits promotional content (images/URLs)
2. Content stored in `promotion_items` with "standby" status
3. Admin reviews and approves/rejects via `batchAudit()`
4. Approved promotions trigger reward distribution
5. Notifications sent via email/LINE

### 2. **Multi-Server Architecture**
- Each server has independent database connections
- User permissions control server access
- Server-specific configuration (cycles, limits, images)
- Isolated player data per server

### 3. **Permission System**
- Role-based access: admin, user, jarvis
- Server-specific permissions via `user_server_permissions`
- Admins have global access, users are server-restricted

### 4. **Notification System**
- Email notifications via CodeIgniter Email service
- LINE messenger integration with OAuth
- Player preference settings for notification types
- Server-level notification configuration

### 5. **File Management**
- Centralized file storage in `files` table
- Type and size validation
- URL-based file serving
- Integration with promotion content

## Development Guidelines

### Database Operations
- Always use the promotion database connection: `\Config\Database::connect('promotion')`
- Implement proper transaction handling for multi-table operations
- Use the `M_Model_Common` class for standardized database operations

### Security Considerations
- Validate all user inputs, especially file uploads
- Implement proper authentication via token system
- Sanitize database queries to prevent SQL injection
- Check user permissions before server-specific operations

### Error Handling
- Use try-catch blocks for database operations
- Implement proper rollback mechanisms for failed transactions
- Provide meaningful error messages for API responses
- Log important operations and errors

### Testing Database Connection
```php
// Test promotion database connectivity
$db = \Config\Database::connect('promotion');
$query = $db->query("SELECT COUNT(*) as count FROM promotions");
$result = $query->getResult();
```

## Common Development Tasks

### Adding New Server
1. Insert into `server` table with unique code
2. Add database configuration to `customized_db`
3. Set up user permissions in `user_server_permissions`
4. Configure server images in `server_image`

### Creating New Promotion
1. Validate user has permission for target server
2. Check daily promotion limits
3. Create promotion record with "standby" status
4. Add content items to `promotion_items`
5. Generate tokens for frontend access

### Implementing New Notification Channel
1. Add configuration to `server` table
2. Extend notification models
3. Update player preferences schema
4. Implement delivery method in controllers