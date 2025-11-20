# Railway Deployment Guide - CToon

This guide explains how to deploy CToon to Railway with automated CI/CD.

## Prerequisites

- Railway account (https://railway.app)
- GitHub repository linked to Railway
- MySQL database (Railway provides this)
- Java 17 and Maven 3.9.11+

## Table of Contents

1. [Build Setup](#build-setup)
2. [Railway Configuration](#railway-configuration)
3. [Deployment Steps](#deployment-steps)
4. [Environment Variables](#environment-variables)
5. [Database Setup](#database-setup)
6. [Monitoring & Troubleshooting](#monitoring--troubleshooting)

---

## Build Setup

### Maven Build

The project is configured with Maven for building and packaging:

```bash
# Clean and build
mvn clean package -DskipTests

# This generates: target/ROOT.war
```

### Dependencies Verified

✅ **Java 17** - Compiler and runtime  
✅ **Jakarta EE 10** - Web framework  
✅ **Hibernate 6.4.0** - JPA provider  
✅ **MySQL Connector/J 8.2.0** - Database driver  
✅ **JWT (JJWT 0.12.3)** - Authentication tokens  
✅ **Spring Security Crypto** - Password hashing  
✅ **Gson 2.10.1** - JSON serialization  

---

## Railway Configuration

### File: Dockerfile

Located at project root, uses multi-stage build:

```dockerfile
# Stage 1: Build with Maven
FROM maven:3.9.11-eclipse-temurin-17 AS builder
...

# Stage 2: Runtime with Tomcat
FROM eclipse-temurin:17-jre
...
EXPOSE 8080
CMD ["catalina.sh", "run"]
```

**Build process:**
1. Downloads Maven dependencies
2. Compiles Java source code
3. Packages into WAR file
4. Copies WAR to Tomcat runtime
5. Starts Tomcat server

### File: railway.toml

Configuration for Railway platform:

```toml
[build]
builder = "dockerfile"
dockerfilePath = "Dockerfile"

[deploy]
startCommand = "java -Dserver.port=${PORT} -jar target/ROOT.war"
```

### File: Procfile

Alternative process file (if not using Dockerfile):

```
web: java -Dserver.port=${PORT} -jar target/ROOT.war
```

### File: docker-compose.yml

For local testing with Docker:

```bash
docker-compose up
```

This starts:
- MySQL database on port 3306
- CToon web app on port 8080

---

## Deployment Steps

### Step 1: Connect GitHub Repository

1. Go to https://railway.app
2. Click "New Project"
3. Select "Deploy from GitHub"
4. Choose `eiviihue/ctoon` repository
5. Railway auto-detects Dockerfile

### Step 2: Configure Build Settings

Railway should automatically detect:

```
Builder: Dockerfile
Start Command: java -Dserver.port=$PORT -jar target/ROOT.war
```

If not, manually set in Railway dashboard:
- Settings → Build & Deploy
- Builder: Dockerfile
- Port: 8080

### Step 3: Add MySQL Database

1. In Railway project dashboard
2. Click "+ Create"
3. Select "MySQL"
4. Configure:
   - User: `ctoon_user`
   - Password: (auto-generated, copy to variables)
   - Database: `ctoon`

### Step 4: Set Environment Variables

In Railway project settings → Variables, add:

```
DATABASE_URL=mysql://ctoon_user:PASSWORD@HOST:PORT/ctoon
DB_HOST=mysql-container-name
DB_PORT=3306
DB_DATABASE=ctoon
DB_USERNAME=ctoon_user
DB_PASSWORD=PASSWORD
```

### Step 5: Deploy Database Schema

Two options:

**Option A: Run migration on container startup**

Add to environment:
```
INIT_SCRIPT_PATH=/app/database/migrations/001_create_ctoon_schema.sql
```

**Option B: Manual migration**

1. Get MySQL credentials from Railway dashboard
2. Connect to database:
```bash
mysql -h HOST -u ctoon_user -p ctoon
```
3. Copy contents of `database/migrations/001_create_ctoon_schema.sql`
4. Paste into MySQL client and execute

### Step 6: Deploy

1. Commit and push to GitHub:
```bash
git add .
git commit -m "Deploy to Railway"
git push origin main
```

2. Railway automatically builds and deploys
3. View logs: Railway dashboard → Deployments

---

## Environment Variables

### Database Connection

```
DB_HOST=mysql.railway.dev
DB_PORT=3306
DB_DATABASE=ctoon
DB_USERNAME=ctoon_user
DB_PASSWORD=your_secure_password
```

### Application Settings

```
PORT=8080
JAVA_OPTS=-Xms256m -Xmx512m
```

### Optional: Azure Storage (for image uploads)

```
AZURE_STORAGE_ACCOUNT=your_account
AZURE_STORAGE_KEY=your_key
AZURE_STORAGE_CONTAINER=ctoon-images
```

---

## Database Setup

### 1. Create Database

Railway MySQL automatically creates database if not exists.

### 2. Run Migrations

Execute SQL script:

```bash
mysql -h HOST -u ctoon_user -p ctoon < database/migrations/001_create_ctoon_schema.sql
```

This creates:
- users table (with email uniqueness)
- comics table (with genre relationships)
- chapters, pages tables
- comments, ratings, bookmarks tables
- covers, profiles tables
- Proper indexes and foreign keys

### 3. Verify Schema

```sql
-- Connect to ctoon database
USE ctoon;

-- Verify tables created
SHOW TABLES;

-- Verify users table structure
DESC users;

-- Verify foreign keys
SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'ctoon';
```

---

## Application Access

Once deployed, Railway provides a URL:

```
https://your-app-name.railway.app
```

### Initial Access

1. Navigate to `https://your-app-name.railway.app`
2. Click "Register" or "Sign In"
3. Create a test account
4. After login, view home page

### API Endpoints

```
POST   /api/auth/register  - Create new account
POST   /api/auth/login     - Sign in with JWT

Future endpoints (to be implemented):
GET    /api/comics         - List all comics
GET    /api/comics/{id}    - Comic details
POST   /api/bookmarks      - Save comic
And more...
```

---

## Monitoring & Troubleshooting

### View Logs

```bash
# In Railway dashboard:
# Project → Deployments → Click deployment → View logs

# Common logs:
[Tomcat] INFO: Server startup...
[Hibernate] Connecting to mysql://...
[AuthService] Registration successful
```

### Common Issues

#### 1. Build Fails: "mvn command not found"

**Solution:** Dockerfile automatically downloads Maven 3.9.11

#### 2. Database Connection Failed

Check environment variables:
```bash
# Railway dashboard → Variables

DB_HOST=correct-host
DB_PORT=3306
DB_USERNAME=ctoon_user
DB_PASSWORD=correct-password
```

#### 3. WAR File Not Found

Verify build output:
```bash
# Should contain:
# Building war: target/ROOT.war
# BUILD SUCCESS
```

#### 4. Port Already in Use

Railway automatically assigns PORT variable:
```bash
java -Dserver.port=${PORT} ...
```

#### 5. Schema Not Created

Manually run migration:
1. Connect to Railway MySQL
2. Run: `cat database/migrations/001_create_ctoon_schema.sql | mysql -h HOST -u USER -p DB`

### Health Check Endpoint

Currently, add to AuthServlet (recommended for Phase 2):

```java
@WebServlet("/health")
public class HealthServlet extends HttpServlet {
    protected void doGet(HttpServletRequest req, HttpServletResponse res) throws IOException {
        res.getWriter().write("{\"status\":\"UP\"}");
    }
}
```

---

## Performance Tips

### 1. Connection Pooling

The `persistence.xml` already configures HikariCP:
- Min connections: 5
- Max connections: 20
- Auto-adjustment based on load

### 2. Caching (Future)

Add Redis to Railway:
```
REDIS_URL=redis://...
```

### 3. Database Indexes

Current schema has indexes on:
- `users.email` (unique)
- `comics.title` (for search)
- Foreign keys (for joins)

### 4. Static Asset Caching

Configure in JSP headers:
```jsp
<meta http-equiv="Cache-Control" content="max-age=31536000">
```

---

## Local Testing Before Deploy

### Using Docker Compose

```bash
# Start containers
docker-compose up

# Access at http://localhost:8080
# MySQL at localhost:3306

# Logs
docker-compose logs -f

# Stop
docker-compose down
```

### Using Maven Directly

```bash
# Build WAR
mvn clean package -DskipTests

# Run with Tomcat plugin
mvn tomcat7:run

# Access at http://localhost:8080
```

---

## Next Steps

After successful deployment:

1. **Test the application**
   - Register a user
   - Login with JWT token
   - Verify database connectivity

2. **Configure domain** (optional)
   - Railway dashboard → Domains
   - Add custom domain (e.g., ctoon.example.com)

3. **Enable HTTPS**
   - Railway auto-enables for railway.app domain
   - Custom domain requires SSL certificate

4. **Setup monitoring**
   - Railway Alerts
   - Application Insights (if using Azure storage)
   - Log aggregation

5. **Phase 2 Development**
   - Create REST API endpoints for comics
   - Build additional JSP pages
   - Implement search and filtering

---

## Deployment Checklist

- [ ] Git repository pushed to GitHub
- [ ] `Dockerfile` present and correct
- [ ] `railway.toml` or `Procfile` configured
- [ ] `pom.xml` dependencies updated
- [ ] MySQL database created
- [ ] Environment variables set
- [ ] Database migrations run
- [ ] Local testing passed with docker-compose
- [ ] Build succeeds: `mvn clean package -DskipTests`
- [ ] WAR file generated: `target/ROOT.war`
- [ ] GitHub repo connected to Railway
- [ ] Deployment triggered and succeeded
- [ ] Application accessible via Railway URL

---

## Support

For issues or questions:

- Railway Docs: https://docs.railway.app
- CToon Migration Guide: See `MIGRATION_GUIDE.md`
- Quick Start: See `QUICKSTART.md`

---

**Last Updated:** 2025-11-20  
**Status:** Ready for Railway Deployment
