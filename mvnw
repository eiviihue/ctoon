#!/bin/sh
# Licensed to the Apache Software Foundation (ASF) under one
# or more contributor license agreements.  See the NOTICE file
# distributed with this work for additional information
# regarding copyright ownership.  The ASF licenses this file
# to you under the Apache License, Version 2.0 (the
# "License"); you may not use this file except in compliance
# with the License.  You may obtain a copy of the License at
#
#    http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing,
# software distributed under the License is distributed on an
# "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
# KIND, either express or implied.  See the License for the
# specific language governing permissions and limitations
# under the License.
#
# This shell script is based on the mvnw script used by the Maven project.

if [ -z "$MAVEN_EXECLDIR" ] ; then
  MAVEN_EXECLDIR=$(dirname "$0")
fi

cd "$(dirname "$0")"

if [ ! -d "$MAVEN_EXECLDIR/.mvn" ]; then
  mkdir -p "$MAVEN_EXECLDIR/.mvn"
fi

# Prepare environment variables
if [ ! -f "$MAVEN_EXECLDIR/.mvn/wrapper/maven-wrapper.jar" ]; then
  echo "Downloading maven wrapper..."
  mkdir -p "$MAVEN_EXECLDIR/.mvn/wrapper"
  wget -q https://repo.maven.apache.org/maven2/org/apache/maven/wrapper/maven-wrapper/3.2.0/maven-wrapper-3.2.0.jar -O "$MAVEN_EXECLDIR/.mvn/wrapper/maven-wrapper.jar"
fi

export MAVEN_OPTS="${MAVEN_OPTS:--Xms1024m -Xmx2048m}"
exec java -cp "$MAVEN_EXECLDIR/.mvn/wrapper/maven-wrapper.jar" org.apache.maven.wrapper.MavenWrapperMain "$@"
