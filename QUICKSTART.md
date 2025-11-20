# CToon - Quick Start Guide

## 🎯 Project Overview

CToon has been completely rebuilt from PHP/Laravel to **Jakarta EE 10** with a modern, beautiful UI and secure JWT-based authentication. **Deployed automatically to Railway via GitHub Actions CI/CD—no local setup required!**

## 🚀 Deployment via GitHub Actions & Railway

This project is configured for **automatic deployment to Railway**. No local Maven build or Docker commands needed—everything happens in CI!

### 1. Initial Setup (One-time)

#### Create Railway Project
1. Go to [Railway.app](https://railway.app/)
2. Create a new project
3. Add MySQL plugin (for database)
4. Copy your **Project ID** from project settings

#### Configure GitHub Secrets
Add these secrets to your GitHub repository (Settings → Secrets and variables → Actions):

| Secret Name | Value |
|-------------|-------|
| `RAILWAY_TOKEN` | Your Railway API token (from Railway account settings) |
| `RAILWAY_PROJECT_ID` | Your Railway project ID |

### 2. Deploy Application

Just **push to the `main` branch** and GitHub Actions handles everything:

```bash
git add .
git commit -m "Your changes here"
git push origin main
```

That's it! The workflow will automatically:
1. ✅ Build Java project with Maven
2. ✅ Create Docker image
3. ✅ Deploy to Railway
4. ✅ Apply database migrations

**Check deployment status**: View GitHub Actions tab or Railway dashboard

### 3. Access Your Application

Once deployed:
- **URL**: `https://<your-railway-domain>.up.railway.app`
- **Register**: Create new account
- **Login**: Sign in to dashboard

## 📁 Project Structure

```
src/main/
├── java/com/ctoon/
│   ├── entities/           JPA entity classes (User, Comic, Chapter, etc.)
│   ├── services/           Business logic (AuthService, etc.)
│   ├── rest/               REST API servlets (/api/auth/*)
│   ├── security/           JWT token provider & password utilities
│   ├── dto/                Request/Response DTOs
│   └── util/               Utility classes
└── webapp/
    ├── login.jsp           Login page
    ├── register.jsp        Registration page
    ├── index.jsp           Home page
    ├── css/style.css       Global Bootstrap 5 styles
    └── WEB-INF/web.xml     Deployment descriptor

database/
└── migrations/
    └── 001_create_ctoon_schema.sql    Database schema (auto-applied by Railway)

docker/
├── Dockerfile              Multi-stage build (Maven + Tomcat)
└── docker-compose.yml      Local testing with MySQL + Tomcat

.github/workflows/
└── ci-deploy-railway.yml   GitHub Actions → Railway deployment
```

## 🔑 Key Features

### ✅ Completed
- [x] Maven project setup with Jakarta EE 10
- [x] JPA entities for all models
- [x] Secure JWT authentication
- [x] Beautiful responsive UI (Bootstrap 5)
- [x] User registration & login pages
- [x] Home page with featured comics
- [x] Database schema with migrations
- [x] GitHub Actions CI/CD to Railway
- [x] Automatic Docker build & deployment

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
1. User fills form (name, email, password)
2. Submits to POST /api/auth/register
3. Receives JWT token + user data
4. Token stored in localStorage
5. Redirected to dashboard
```

### Login
```
1. User enters credentials
2. Submits to POST /api/auth/login
3. Receives JWT token
4. Token stored in localStorage
5. Used for authenticated requests via Authorization header
```

## 🛠️ Local Development (Optional)

For **local testing only** (not required for Railway deployment):

### Prerequisites
```bash
java -version      # Should be 17+
mvn -v             # Should be 3.9.11 (wrapper auto-downloads)
mysql -V           # Should be 8.0+
```

### Build Locally
```bash
# Clean build (skips tests for speed)
mvn clean package -DskipTests

# Run locally with Tomcat
mvn tomcat7:run

# Run on different port
mvn tomcat7:run -Dtomcat.port=9090
```

### Database Setup (Local)
```bash
# Create database
mysql -u root -p
> CREATE DATABASE ctoon;
> EXIT;

# Run migrations
mysql -u root -p ctoon < database/migrations/001_create_ctoon_schema.sql
```

### Docker Compose (Local Testing)
```bash
# Start MySQL + Tomcat locally
docker-compose up -d

# Access application
# URL: http://localhost:8080
# Database: localhost:3306

# Stop services
docker-compose down
```

### Access Locally
- **URL**: http://localhost:8080
- **Register**: Create new account
- **Login**: Sign in to dashboard


## 🌐 API Endpoints

### Authentication
```
POST /api/auth/register      Register new user
POST /api/auth/login         Login user
```

### Request Format
```json
{
  "name": "John Doe",          // for register only
  "email": "user@example.com",
  "password": "securepass123"
}
```

### Response Format
```json
{
  "success": true,
  "message": "Registration successful",
  "token": "eyJhbGciOiJIUzUxMiJ9...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "createdAt": "2025-11-20T10:30:00"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "User already exists"
}
```

## 💾 Database Tables

All tables automatically created from SQL migration in Railway:
- `users` - User accounts with JWT support
- `profiles` - User profile information
- `comics` - Comic series metadata
- `chapters` - Story chapters
- `pages` - Comic pages/images
- `genres` - Comic categories
- `comments` - User comments on comics
- `ratings` - User ratings and reviews
- `bookmarks` - User saved comics

## 🎨 Styling

### Frontend Framework
- **CSS**: Bootstrap 5 with custom theme
- **File**: `src/main/webapp/css/style.css`
- **Theme**: Purple, Blue, Red gradient
- **Features**: Responsive design, animations, mobile-first

### Key CSS Classes
- `.auth-container` - Login/Register form layout
- `.card` - Content cards with shadow
- `.btn-primary` - Primary CTA buttons
- `.hero` - Hero banner sections
- `.navbar` - Top navigation bar
- `.responsive-grid` - Auto-layout grid system

## 🐛 Local Debugging (Optional)

### View Application Logs
```bash
# If running locally with Tomcat
mvn tomcat7:run

# Logs appear in terminal; watch for errors
```

### Enable Hibernate SQL Logging
Edit `src/main/resources/META-INF/persistence.xml`:
```xml
<property name="hibernate.show_sql" value="true"/>
<property name="hibernate.format_sql" value="true"/>
```

### Database Verification
```bash
mysql -u root -p ctoon
> SHOW TABLES;
> DESC users;
> SELECT COUNT(*) FROM users;
```

### Browser Developer Tools
```javascript
// Check JWT token in localStorage
console.log(localStorage.getItem('token'));

// Check user data
console.log(JSON.parse(localStorage.getItem('user')));

// Test API call
fetch('https://your-railway-url/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'test@example.com',
    password: 'testpass'
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

## ☁️ Railway Deployment Details

### How It Works
1. **Push to GitHub**: `git push origin main`
2. **GitHub Actions triggers**: `.github/workflows/ci-deploy-railway.yml`
3. **Maven builds WAR**: Java project compiled to `target/ROOT.war`
4. **Docker builds image**: Multi-stage build (Maven builder → Tomcat runtime)
5. **Railway deploys**: Image pushed and running on Railway
6. **Migrations applied**: SQL schema applied to Railway MySQL

### CI/CD Workflow File
```yaml
# .github/workflows/ci-deploy-railway.yml
- Builds Java project with Maven
- Creates Docker image
- Deploys to Railway using Railway CLI
- Uses RAILWAY_TOKEN + RAILWAY_PROJECT_ID secrets
```

### Monitor Deployment
- **GitHub**: Check Actions tab for workflow status
- **Railway**: View deployment logs in Railway dashboard
- **Application**: Visit your Railway domain URL

### Rollback
```bash
# If deployment fails, just fix the code and push again
# Railway automatically re-deploys on next push
git add .
git commit -m "Fix issue"
git push origin main
```

## 📦 Dependencies

### Build & Runtime
- **Java Runtime**: OpenJDK 17+
- **Web Server**: Tomcat 10.0
- **Build Tool**: Maven 3.9.11 (wrapper included)

### Java Frameworks & Libraries
- **Jakarta EE 10** - Enterprise Java framework
- **Hibernate 6.4** - JPA Object-Relational Mapping
- **JJWT 0.12.3** - JWT token creation & validation
- **Spring Security Crypto** - Password hashing (BCrypt)
- **Gson** - JSON serialization/deserialization
- **SLF4J + Logback** - Structured logging
- **MySQL Connector** - MySQL JDBC driver

### Frontend
- **Bootstrap 5** - CSS framework
- **JavaScript (ES6)** - Client-side logic
- **JSP 3.0** - Server-side templating

## 🚨 Common Issues

### GitHub Actions Build Fails
**Error**: `[ERROR] COMPILATION ERROR`
```bash
# Solution: Check Java version in pom.xml
# Should be: <source>17</source> <target>17</target>
# And verify Maven settings
```

### Railway Deployment Not Triggering
**Check**: 
1. Verify secrets exist: `RAILWAY_TOKEN`, `RAILWAY_PROJECT_ID`
2. Check GitHub Actions workflow file: `.github/workflows/ci-deploy-railway.yml`
3. View workflow logs in GitHub Actions tab

**Solution**: 
```bash
git add .
git commit --amend --no-edit
git push origin main -f
```

### Application Won't Start on Railway
**Error**: Connection to MySQL fails
```
# Possible causes:
1. Database not created in Railway MySQL
2. Schema not migrated
3. Connection credentials mismatch

# Solution: Check Railway logs and verify MySQL service is running
```

### Port Already in Use (Local Testing)
```bash
# Use different port
mvn tomcat7:run -Dtomcat.port=9090
```

### JSP Pages Not Found
- Verify `.jsp` files exist in `src/main/webapp/`
- Check `web.xml` deployment descriptor
- Ensure WAR file includes JSPs

## 📊 Project Statistics

- **Languages**: Java 17, SQL, HTML5, CSS3, JavaScript ES6
- **Core Classes**: 10 JPA entities, 3 REST servlets, 1 security provider
- **Database Tables**: 9 (users, profiles, comics, chapters, pages, genres, comments, ratings, bookmarks)
- **API Endpoints**: 2 (register, login)
- **UI Pages**: 3 JSP templates (login, register, home)
- **Configuration Files**: pom.xml, persistence.xml, web.xml, docker-compose.yml, railway configs

## 📚 Next Steps

1. **Get Railway secrets configured** (RAILWAY_TOKEN, RAILWAY_PROJECT_ID)
2. **Push to main branch** to trigger first automated deployment
3. **Verify Railway deployment** in Railway dashboard
4. **Test registration & login** on Railway URL
5. **Build out comic features** (listing, reader, comments, etc.)
6. **Add more JSP pages** for new features
7. **Monitor performance** in Railway dashboard

## 📞 Need Help?

### Documentation Files
- `MIGRATION_GUIDE.md` - Details on PHP → Java migration
- `PROGRESS.md` - Current project status
- `RAILWAY_DEPLOYMENT.md` - Railway setup details
- `pom.xml` - Maven build configuration
- `.github/workflows/ci-deploy-railway.yml` - CI/CD pipeline

### View Deployment History
```bash
# GitHub Actions
# Go to: GitHub → Actions tab → ci-deploy-railway workflow

# Railway Dashboard
# Go to: Railway.app → Project → Deployments tab
```

---

**✨ Fully Automated Railway Deployment Ready! Just push to main. 🚀**
