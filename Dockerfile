# Stage 1: Build with Maven
FROM maven:3.9.11-eclipse-temurin-17 AS builder

WORKDIR /app

# Copy pom.xml first
COPY pom.xml .

# Download dependencies
RUN mvn dependency:go-offline -B

# Copy entire source code
COPY . .

# Build the application with verbose output
RUN mvn clean package -DskipTests -B && \
    ls -lh target/ROOT.war && \
    echo "Build completed successfully"

# Stage 2: Runtime
FROM eclipse-temurin:17-jre

WORKDIR /app

# Install Tomcat 10.1.28
ENV TOMCAT_VERSION=10.1.28
RUN apt-get update && apt-get install -y wget && \
    wget -q https://archive.apache.org/dist/tomcat/tomcat-10/v${TOMCAT_VERSION}/bin/apache-tomcat-${TOMCAT_VERSION}.tar.gz && \
    tar -xzf apache-tomcat-${TOMCAT_VERSION}.tar.gz && \
    rm apache-tomcat-${TOMCAT_VERSION}.tar.gz && \
    mv apache-tomcat-${TOMCAT_VERSION} tomcat && \
    rm -rf tomcat/webapps/ROOT && \
    apt-get remove -y wget && apt-get clean

# Copy the WAR file from builder stage
COPY --from=builder /app/target/ROOT.war /app/tomcat/webapps/ROOT.war

# Verify WAR file exists
RUN ls -lh /app/tomcat/webapps/ROOT.war || (echo "ERROR: WAR file not found!" && exit 1)

# Expose port 8080
EXPOSE 8080

# Set environment variables
ENV CATALINA_HOME=/app/tomcat
ENV PATH=$CATALINA_HOME/bin:$PATH
ENV JAVA_OPTS="-Xmx512m -Xms256m"

# Start Tomcat
CMD ["catalina.sh", "run"]
