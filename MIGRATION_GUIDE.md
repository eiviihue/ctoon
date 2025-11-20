# CToon - Migration from PHP/Laravel to Jakarta EE

## 📋 Migration Summary

Your CToon website has been successfully rebuilt from **PHP/Laravel** to **Jakarta EE 10 (Java)** with a modern, beautiful UI and improved authentication system.

## ✨ What's New

### 1. **Technology Stack**
- **Framework**: Jakarta EE 10 (replacing Laravel)
- **Build Tool**: Maven 3.8.6+
- **Runtime**: Tomcat 10+ or any Jakarta EE 10 compatible server
- **JPA Provider**: Hibernate 6.4
- **Database**: MySQL 8.0+
- **Security**: JWT (JSON Web Tokens) with BCrypt password hashing
- **Frontend**: Bootstrap 5 + Vanilla JavaScript

### 2. **Beautiful UI/UX**
- Modern gradient-based design with purple and blue themes
- Responsive Bootstrap 5 layout (mobile-first)
- Smooth animations and transitions
- Professional card-based layouts
- Dark mode ready (extensible)
- Improved form validation and error handling

### 3. **Authentication System**
- Secure JWT-based authentication
- BCrypt password hashing (Spring Security)
- User registration with validation
- User login with token generation
- Session management via localStorage
- Protected routes and API endpoints

### 4. **Project Structure**
```
ctoon/
├── src/
│   ├── main/
│   │   ├── java/com/ctoon/
│   │   │   ├── entities/        (JPA Entities)
│   │   │   ├── services/        (Business Logic)
│   │   │   ├── rest/            (REST Servlets)
│   │   │   ├── security/        (JWT, Auth)
│   │   │   ├── dto/             (Request/Response)
│   │   │   └── util/            (Utilities)
│   │   ├── resources/
│   │   │   └── META-INF/
│   │   │       └── persistence.xml
│   │   └── webapp/
│   │       ├── login.jsp
│   │       ├── register.jsp
│   │       ├── index.jsp
│   │       ├── css/style.css
│   │       ├── js/
│   │       └── WEB-INF/web.xml
│   └── test/
├── database/migrations/
│   └── 001_create_ctoon_schema.sql
├── pom.xml
└── .github/workflows/main_CToon.yml
```

## 🚀 Getting Started

### Prerequisites
- **Java 17+** (JDK)
- **Maven 3.8.6+**
- **MySQL 8.0+**
- **Git**

### Local Development Setup

1. **Clone and navigate to project**
   ```bash
   cd e:\ctoon
   ```

2. **Configure Database**
   - Create MySQL database: `ctoon`
   - Update `src/main/resources/META-INF/persistence.xml`:
     ```xml
     <property name="jakarta.persistence.jdbc.url" value="jdbc:mysql://localhost:3306/ctoon?useSSL=false&amp;serverTimezone=UTC"/>
     <property name="jakarta.persistence.jdbc.user" value="your_user"/>
     <property name="jakarta.persistence.jdbc.password" value="your_password"/>
     ```

3. **Run Database Migrations**
   ```bash
   mysql -u root -p ctoon < database/migrations/001_create_ctoon_schema.sql
   ```

4. **Build Project**
   ```bash
   mvn clean package
   ```

5. **Run Locally (Using Tomcat Maven Plugin)**
   ```bash
   mvn tomcat7:run
   ```
   - Access: `http://localhost:8080`

## 📦 Building for Production

### Create WAR File
```bash
mvn clean package -DskipTests
```
Output: `target/ROOT.war` (ready for deployment)

## ☁️ Azure Deployment

### Configuration

1. **GitHub Secrets** (set in your GitHub repository settings):
   ```
   AZURE_WEBAPP_PUBLISH_PROFILE    (Your publish profile XML)
   DB_HOST                          (Database host)
   DB_PORT                          (Database port)
   DB_DATABASE                      (Database name)
   DB_USERNAME                      (Database user)
   DB_PASSWORD                      (Database password)
   AZURE_STORAGE_ACCOUNT            (Storage account name)
   AZURE_STORAGE_KEY                (Storage account key)
   AZURE_STORAGE_CONTAINER          (Container name)
   ```

2. **Azure App Service Settings**
   - Runtime: Java 17
   - Web Server: Tomcat 10
   - Document Root: `/` (default for WAR)

3. **Deploy via GitHub Actions**
   - Push to `main` branch or manually trigger workflow
   - `.github/workflows/main_CToon.yml` automatically:
     - Builds project with Maven
     - Compiles to WAR file
     - Deploys to Azure App Service
     - Configures database settings

## 🔐 Authentication API

### Register User
```javascript
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "passwordConfirmation": "SecurePass123"
}

Response:
{
  "success": true,
  "message": "Registration successful",
  "token": "eyJhbGciOiJIUzUxMiJ9...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "createdAt": "2025-11-20T10:30:00"
  }
}
```

### Login User
```javascript
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePass123"
}

Response: (same as register)
```

### Using Token
```javascript
// Store token
const token = response.data.token;
localStorage.setItem('token', token);

// Use in requests
fetch('/api/comics', {
  headers: {
    'Authorization': 'Bearer ' + token
  }
});
```

## 📱 Pages & Features

### Implemented Pages
- ✅ **Login Page** (`/login.jsp`) - Modern auth form
- ✅ **Register Page** (`/register.jsp`) - User registration
- ✅ **Home Page** (`/index.jsp`) - Featured comics, categories
- ⏳ **Comics Listing** (`/comics.jsp`) - Browse comics by genre
- ⏳ **Comic Reader** - Chapter viewer with pages
- ⏳ **Bookmarks** (`/bookmarks.jsp`) - Saved comics
- ⏳ **User Profile** (`/profile.jsp`) - Profile management

## 🗄️ Database Schema

### Tables
1. **users** - User accounts
2. **profiles** - User profiles (bio, avatar, etc.)
3. **comics** - Comic series
4. **chapters** - Chapters within comics
5. **pages** - Images/pages in chapters
6. **genres** - Comic categories
7. **comic_genre** - Many-to-many relationship
8. **comments** - User comments on comics
9. **ratings** - User ratings
10. **bookmarks** - User bookmarks

All tables use:
- UTF8MB4 encoding
- Proper foreign keys with CASCADE delete
- Timestamps (created_at, updated_at)
- Indexes on frequently queried columns

## 🔄 Migration from PHP Data

If you have existing data in the old Laravel database, run this to migrate:

```bash
# Export from Laravel database
mysqldump -u root -p laravel_db > old_data.sql

# Import schema to new database
mysql -u root -p ctoon < database/migrations/001_create_ctoon_schema.sql

# Import data (ensure structure matches)
mysql -u root -p ctoon < old_data.sql
```

## 🛠️ Development Commands

```bash
# Clean and build
mvn clean package

# Build without tests
mvn clean package -DskipTests

# Run tests
mvn test

# Run locally
mvn tomcat7:run

# Check for dependencies updates
mvn versions:display-dependency-updates

# Generate Maven site (documentation)
mvn site
```

## 📝 Key Files Changed/Created

### Deleted
- `app/` (PHP controllers)
- `routes/` (Laravel routes)
- `resources/views/` (Blade templates)
- `config/` (Laravel config)
- `composer.json` (PHP dependencies)
- `.github/workflows/main_CToon.yml` (old PHP workflow)

### Created
- `src/main/java/` (Java source code)
- `src/main/webapp/` (JSP views + assets)
- `pom.xml` (Maven config)
- `persistence.xml` (JPA config)
- `.github/workflows/main_CToon.yml` (new Java workflow)
- `database/migrations/001_create_ctoon_schema.sql`

### Updated
- `azure.settings.json` - Java 17 runtime
- `.gitignore` - Maven, Java build files

## 🎨 Design System

### Color Palette
- Primary: `#6c5ce7` (Purple)
- Secondary: `#0984e3` (Blue)
- Accent: `#ff7675` (Red)
- Background: `#f5f6fa` (Light)
- Dark: `#1e1e2e` (Night)

### Components
- Bootstrap 5 Grid
- Custom cards with hover effects
- Gradient buttons
- Form validation
- Alert modals
- Spinner animations

## 🔒 Security Features

1. **Password Security**
   - BCrypt hashing with 12 rounds
   - Never store plain text passwords
   - Constant-time comparison

2. **JWT Tokens**
   - HMAC-SHA512 signing
   - 24-hour expiration
   - User ID and email claims

3. **Session Management**
   - HttpOnly cookies
   - Secure flag for HTTPS
   - CSRF protection via web.xml

4. **Database**
   - Parameterized queries (JPA prevents SQL injection)
   - Foreign key constraints
   - Unique email indexes

## 🐛 Troubleshooting

### Build Fails
```bash
# Clear Maven cache
mvn clean

# Update dependencies
mvn dependency:resolve
```

### Database Connection Issues
- Check `persistence.xml` credentials
- Verify MySQL is running
- Test connection: `mysql -u root -p -h localhost ctoon`

### JAR/WAR Issues
```bash
# Check what's in WAR
jar tf target/ROOT.war

# Extract WAR
jar xf target/ROOT.war
```

### Port Already in Use (8080)
```bash
mvn tomcat7:run -Dtomcat.port=9090
# Access at http://localhost:9090
```

## 📚 Additional Resources

- [Jakarta EE Documentation](https://jakarta.ee/)
- [Hibernate ORM Guide](https://hibernate.org/orm/)
- [Maven Official Site](https://maven.apache.org/)
- [Bootstrap 5 Docs](https://getbootstrap.com/)
- [JWT Introduction](https://jwt.io/)

## 🤝 Next Steps

1. **Complete REST API Endpoints** - Implement remaining endpoints for:
   - Comics listing/detail
   - Chapters and pages
   - Comments and ratings
   - Bookmarks management

2. **Add More Pages** - Build JSP pages for:
   - Comics browsing
   - Reader interface
   - User profile editing

3. **Frontend Enhancement** - Add:
   - Dark mode toggle
   - Search functionality
   - Pagination
   - Filtering by genre

4. **Testing** - Set up:
   - Unit tests (JUnit 5)
   - Integration tests
   - End-to-end tests

5. **Monitoring** - Implement:
   - Logging with SLF4J
   - Application Insights for Azure
   - Performance monitoring

## 📞 Support

For issues or questions, check:
- `DEPLOYMENT.md` - Deployment specifics
- Java stack traces in logs
- Azure portal diagnostics

---

**Happy coding! Your CToon platform is now ready to serve beautiful comics with modern technology. 🎉**
