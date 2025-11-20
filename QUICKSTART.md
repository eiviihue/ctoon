# CToon - Quick Start Guide

## 🎯 Project Overview

CToon has been completely rebuilt from PHP/Laravel to **Jakarta EE 10** with a modern, beautiful UI and secure JWT-based authentication.

## ⚡ Quick Setup (5 minutes)

### 1. Prerequisites Check
```bash
java -version      # Should be 17+
mvn -v             # Should be 3.8.6+
mysql -V           # Should be 8.0+
```

### 2. Database Setup
```bash
# Create database
mysql -u root -p
> CREATE DATABASE ctoon;
> EXIT;

# Run migrations
mysql -u root -p ctoon < database/migrations/001_create_ctoon_schema.sql
```

### 3. Configure Connection
Edit `src/main/resources/META-INF/persistence.xml`:
```xml
<property name="jakarta.persistence.jdbc.url" value="jdbc:mysql://localhost:3306/ctoon"/>
<property name="jakarta.persistence.jdbc.user" value="root"/>
<property name="jakarta.persistence.jdbc.password" value="your_password"/>
```

### 4. Build & Run
```bash
mvn clean package
mvn tomcat7:run
```

### 5. Access Application
- **URL**: http://localhost:8080
- **Register**: Create new account
- **Login**: Sign in to dashboard

## 📁 Project Structure

```
src/
├── main/java/com/ctoon/
│   ├── entities/           JPA entity classes
│   ├── services/           Business logic (AuthService, etc.)
│   ├── rest/               REST API servlets
│   ├── security/           JWT token provider
│   ├── dto/                Request/Response DTOs
│   └── util/               Utilities (PasswordUtil, etc.)
└── main/webapp/
    ├── login.jsp           Login page
    ├── register.jsp        Registration page
    ├── index.jsp           Home page
    ├── css/style.css       Global styles
    └── WEB-INF/web.xml     Deployment descriptor
```

## 🔑 Key Features Implemented

### ✅ Completed
- [x] Maven project setup with Jakarta EE 10
- [x] JPA entities for all models
- [x] Secure JWT authentication
- [x] Beautiful responsive UI (Bootstrap 5)
- [x] User registration & login pages
- [x] Home page with featured comics
- [x] Database schema with migrations
- [x] Azure deployment workflow

### ⏳ To Do
- [ ] Comics listing & filtering
- [ ] Comic reader with pagination
- [ ] Comment system
- [ ] Rating system
- [ ] Bookmarks management
- [ ] User profile page
- [ ] Admin dashboard

## 🔐 Authentication Flow

### Register
```
1. Fill form (name, email, password)
2. Submit to /api/auth/register
3. Get JWT token + user data
4. Store in localStorage
5. Redirect to home
```

### Login
```
1. Enter credentials
2. Submit to /api/auth/login
3. Receive JWT token
4. Store token locally
5. Use token for authenticated requests
```

## 🛠️ Common Commands

```bash
# Build project
mvn clean package

# Run locally
mvn tomcat7:run

# Run on different port
mvn tomcat7:run -Dtomcat.port=9090

# Skip tests during build
mvn clean package -DskipTests

# Run only tests
mvn test

# Check for updates
mvn versions:display-dependency-updates
```

## 🌐 API Endpoints

### Authentication
```
POST /api/auth/register      Register new user
POST /api/auth/login         Login user
```

### Response Format
```json
{
  "success": true,
  "message": "Success message",
  "token": "eyJhbGciOiJIUzUxMiJ9...",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "createdAt": "2025-11-20T10:30:00"
  }
}
```

## 💾 Database Tables

All tables automatically created from SQL migration:
- `users` - User accounts
- `profiles` - User profiles
- `comics` - Comic series
- `chapters` - Story chapters
- `pages` - Comic pages/images
- `genres` - Categories
- `comments` - User comments
- `ratings` - Ratings
- `bookmarks` - Saved comics

## 🎨 Styling

### Global CSS
- File: `src/main/webapp/css/style.css`
- Colors: Purple, Blue, Red gradient theme
- Framework: Bootstrap 5
- Features: Responsive, animations, dark-mode ready

### Key Classes
- `.auth-container` - Login/Register layout
- `.card` - Content cards
- `.btn-primary` - Primary buttons
- `.hero` - Hero sections
- `.navbar` - Navigation bar

## 🐛 Debugging

### Check logs
```bash
# Tomcat logs during development
mvn tomcat7:run

# Hibernate SQL logging
# Edit persistence.xml:
<property name="hibernate.show_sql" value="true"/>
```

### Database verification
```bash
mysql -u root -p ctoon
> SHOW TABLES;
> DESC users;
> SELECT * FROM users;
```

### Browser console
```javascript
// Check stored token
console.log(localStorage.getItem('token'));

// Check user info
console.log(JSON.parse(localStorage.getItem('user')));
```

## ☁️ Azure Deployment

### Prerequisites
1. Azure subscription
2. Azure App Service (Java 17 + Tomcat 10)
3. GitHub repository with secrets configured

### Deploy
```bash
# Just push to main branch
git add .
git commit -m "Deploy to Azure"
git push origin main

# Workflow automatically:
# 1. Builds project
# 2. Creates WAR file
# 3. Deploys to Azure
# 4. Configures settings
```

## 📦 Dependencies

Main libraries:
- **Jakarta EE 10** - Web framework
- **Hibernate 6.4** - JPA provider
- **JJWT 0.12.3** - JWT handling
- **Spring Security** - Password hashing
- **Gson** - JSON processing
- **SLF4J + Logback** - Logging

## 🚨 Common Issues

### Maven build fails
```bash
# Clear cache and retry
mvn clean
mvn package
```

### Database connection error
- Check MySQL is running: `net start MySQL80` (Windows)
- Verify credentials in persistence.xml
- Check database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Port 8080 already in use
```bash
# Use different port
mvn tomcat7:run -Dtomcat.port=9090
```

### JSP pages not rendering
- Check `web.xml` is in `WEB-INF/`
- Verify `.jsp` files are in `src/main/webapp/`
- Clear browser cache

## 📚 Next Steps

1. **Run the app locally** and test registration/login
2. **Review the code** in `src/main/java/`
3. **Add new API endpoints** for comics, chapters, etc.
4. **Create additional JSP pages** for features
5. **Deploy to Azure** when ready
6. **Monitor** in Azure portal

## 📞 Need Help?

Check these files:
- `MIGRATION_GUIDE.md` - Detailed migration info
- `pom.xml` - Dependency definitions
- `persistence.xml` - Database configuration
- `.github/workflows/main_CToon.yml` - Deployment workflow

---

**You're all set! Happy coding! 🚀**
