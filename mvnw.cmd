@REM ----------------------------------------------------------------------------
@REM Licensed to the Apache Software Foundation (ASF) under one
@REM or more contributor license agreements.  See the NOTICE file
@REM distributed with this work for additional information
@REM regarding copyright ownership.  The ASF licenses this file
@REM to you under the Apache License, Version 2.0 (the
@REM "License"); you may not use this file except in compliance
@REM with the License.  You may obtain a copy of the License at
@REM
@REM    http://www.apache.org/licenses/LICENSE-2.0
@REM
@REM Unless required by applicable law or agreed to in writing,
@REM software distributed under the License is distributed on an
@REM "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
@REM KIND, either express or implied.  See the License for the
@REM specific language governing permissions and limitations
@REM under the License.
@REM ----------------------------------------------------------------------------

@REM ----------------------------------------------------------------------------
@REM Maven Start Up Batch script for Windows
@REM
@REM Required ENV vars:
@REM JAVA_HOME - location of a JDK home dir
@REM
@REM Optional ENV vars
@REM M2_HOME - location of maven's installed home (default is %MAVEN_HOME%)
@REM MAVEN_BATCH_ECHO - set to 'on' to enable the echoing of the batch commands
@REM MAVEN_BATCH_PAUSE - set to 'on' to wait for a keystroke before ending
@REM MAVEN_OPTS - parameters passed to the Java VM when running Maven
@REM MAVEN_SKIP_RC - flag to disable loading of mavenrc files
@REM ----------------------------------------------------------------------------

if "%MAVEN_SKIP_RC%"=="" goto skipRcPre

@setlocal

for /f "usebackq delims="  %%A in ("%MAVEN_BATCH_ECHO%") do (
set "MAVEN_BATCH_ECHO=%%~A"
)

:skipRcPre

@setlocal enabledelayedexpansion

set DIRNAME=%~dp0
if "%DIRNAME%"=="" set DIRNAME=.
set APP_BASE_NAME=%~n0
set APP_HOME=%DIRNAME%

@rem Resolve any "." and ".." in APP_HOME to make it shorter.
for %%i in ("%APP_HOME%") do set APP_HOME=%%~fi

@rem Add default JVM options here. You can also use MAVEN_OPTS to pass JVM options to this script.
set DEFAULT_JVM_OPTS="-Xmx1024m" "-Xms1024m"

@rem Find java.exe
if defined JAVA_HOME goto findJavaFromJavaHome

set JAVA_EXE=java.exe
%JAVA_EXE% -version >nul 2>&1
if %ERRORLEVEL% equ 0 goto execute

echo.
echo ERROR: JAVA_HOME is not set and no 'java' command could be found in your PATH.
echo.
echo Please set the JAVA_HOME variable in your environment to match the
echo location of your Java installation.

goto fail

:findJavaFromJavaHome
set JAVA_HOME=%JAVA_HOME:"=%
set JAVA_EXE=%JAVA_HOME%\bin\java.exe

if exist "%JAVA_EXE%" goto execute

echo.
echo ERROR: JAVA_HOME is set to an invalid directory: %JAVA_HOME%
echo.
echo Please set the JAVA_HOME variable in your environment to match the
echo location of your Java installation.

goto fail

:execute
@rem Setup the command line

set CLASSPATH=%APP_HOME%\.mvn\wrapper\maven-wrapper.jar

@rem Execute Maven
"%JAVA_EXE%" %DEFAULT_JVM_OPTS% ^
  -classpath !CLASSPATH! ^
  "-Dmaven.home=%APP_HOME%\.mvn" ^
  "-Dmaven.multiModuleProjectDirectory=%APP_HOME%" ^
  org.apache.maven.wrapper.MavenWrapperMain %*

:end
@endlocal & set ERROR_CODE=%ERRORLEVEL%

if not "%MAVEN_BATCH_PAUSE%"=="" pause

if %ERROR_CODE% equ 0 goto mainEnd

:fail
rem Set variable MAVEN_BATCH_ECHO to 'on' to enable the echoing of the batch commands
if "%MAVEN_BATCH_ECHO%"=="on"  @echo on

echo An error occurred while running maven %MAVEN_BATCH_ECHO%

exit /b %ERROR_CODE%

:mainEnd
if "%OS%"=="Windows_NT" endlocal

exit /b %ERROR_CODE%
