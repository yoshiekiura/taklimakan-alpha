pipeline {
  agent any
  stages {
    stage('Ask for Branch Id & checkout this revision') {
      when {
        branch 'release/**'
      }
      steps {
        script {
          def commitId = input(
            id: 'userInput', message: 'Enter branch commit ID (Empty for latest)?',
            parameters: [
              string(defaultValue: '',
              description: 'Branch commit ID',
              name: 'CommitId'),
            ])
            if (commitId != "") {
              def command = "git cat-file -t ${commitId}"
              def commitExist=sh(returnStdout: true, script: command)
              echo("commitExist= \"${commitExist}\"; commitId= \"${commitId}\"")
              assert commitExist != "commit" && commitId != "": "Branch with commit Id: ${commitId} not exist"
              echo ("Commit exist. Proceed Deployment.")

              echo("${commitId}")
              def fetchcmd = sh(returnStdout: true, script: 'git fetch')
              command = "git checkout ${commitId}"
              def checkoutcmd = sh(returnStdout: true, script: command)
              echo("${checkoutcmd}")
            }
            else
            {
              echo("Use HEAD revision")
            }
          }

        }
      }
      stage('Test') {
        steps {
          sh 'echo "execute Unit tests"'
        }
      }
      stage('Static Analysis') {
        parallel {
          stage('Static Analysis') {
            steps {
              echo 'Static Analysis'
            }
          }
          stage('Analitics') {
            steps {
              sh '''#!/bin/bash

if [ ! -f pylint.cfg ]
then
  # generate pylint configuration file if not exist
  pylint --generate-rcfile > pylint.cfg
fi

for entry in `ls services/analytics/*.py`; do
    echo $entry
    name=$(basename $entry)
    pylint --rcfile=pylint.cfg --msg-template="{path}:{line}: [{msg_id}, {obj}] {msg} ({symbol})" $entry > pylint_$name.log
done



#return 0 to be able to continue execution of jenkins steps
exit 0

'''
              warnings(consoleParsers: [[parserName: 'PyLint']], parserConfigurations: [[parserName: 'PyLint', pattern: 'pylint*.log']])
              archiveArtifacts 'pylint_*.log'
            }
          }
        }
      }
      stage('Archive & Deploy') {
        when {
          anyOf {
            branch 'master'
            branch 'release/**'
            branch 'develop'
          }

        }
        steps {
          sh '''echo "display git branch info to make sure that branch is switch to Commit"
git branch'''
          sh '''echo "# This file is a "template" of which env vars need to be defined for your application" > tmpenv
echo "# Copy this file to .env file for development, create environment variables when deploying to production" >> tmpenv
echo "# https://symfony.com/doc/current/best_practices/configuration.html#infrastructure-related-configuration" >> tmpenv
echo " " >> tmpenv
echo "###> symfony/framework-bundle ###" >> tmpenv
echo "APP_ENV=dev" >> tmpenv
echo "#APP_ENV=prod" >> tmpenv
echo "APP_SECRET=e3d9bc1b4ad39a7c6e025ee8e7d6f1d5" >> tmpenv
echo "#TRUSTED_PROXIES=127.0.0.1,127.0.0.2" >> tmpenv
echo "#TRUSTED_HOSTS=localhost,example.com" >> tmpenv
echo "###< symfony/framework-bundle ###" >> tmpenv
echo " " >> tmpenv
echo "###> doctrine/doctrine-bundle ###" >> tmpenv
echo "# Format described at http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#connecting-using-a-url" >> tmpenv
echo "# For an SQLite database, use: \'sqlite:///%kernel.project_dir%/var/data.db\'" >> tmpenv
echo "# Configure your db driver and server_version in config/packages/doctrine.yaml" >> tmpenv
echo "DATABASE_URL=mysql://root:pan01MAT1@127.0.0.1:3306/crypto" >> tmpenv
echo "###< doctrine/doctrine-bundle ###" >> tmpenv
echo " " >> tmpenv

mv tmpenv .env'''
          sh '''#!/bin/bash
if [ -d taklimakan-alpha ]
then
# remove previous deploy data
rm -rf taklimakan-alpha
fi

mkdir taklimakan-alpha

if [ -f ".env" ]
then
  cp .env taklimakan-alpha/
else
  echo "For unknown reason .env not exist"
  dir

fi

for D in *; do
if [ $D != "taklimakan-alpha" ] && [ $D != ".git" ] && [ $D != "Jenkinsfile" ] && [ $D != "CodeAnalysis" ]
then
  # copy to taklimakan-alpha
  if [ -d "${D}" ]
  then
    cp -R $D taklimakan-alpha/
  else
    cp $D taklimakan-alpha/
  fi
fi
done

#zip deploy file
zip -r taklimakan-alpha.zip taklimakan-alpha
'''
          archiveArtifacts '*.zip'
          sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
            sh '''#!/bin/bash
dir
echo "Branch Name: $BRANCH_NAME"
if [ "$BRANCH_NAME" == "master" ]
then
  DEPLOY_HOST="192.168.100.127"
  DEPLOY_PORT="8022"
else
  if [ "$BRANCH_NAME" == "develop" ]
  then
    DEPLOY_HOST="192.168.100.125"
    DEPLOY_PORT="8022"
  else
    #release branch
    DEPLOY_HOST="192.168.100.126"
    DEPLOY_PORT="8022"
  fi
fi
echo "Deploy Host: $DEPLOY_HOST:$DEPLOY_PORT"

echo "Cleanup previous deploy (if any)"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT rm -rf /home/tkln/tmpdeploy
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT rm -rf /home/tkln/tmpwww

echo "Upload file to host"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT mkdir /home/tkln/tmpdeploy
scp -P $DEPLOY_PORT taklimakan-alpha.zip tkln@$DEPLOY_HOST:/home/tkln/tmpdeploy/taklimakan-alpha.zip

echo "Unzip file into temp folder"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT mkdir /home/tkln/tmpwww
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT unzip /home/tkln/tmpdeploy/taklimakan-alpha.zip -d /home/tkln/tmpwww

echo "Remove target folder"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT rm -fr /var/www/

echo "Move unzipped files into target"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT mv /home/tkln/tmpwww/taklimakan-alpha/* /var/www/
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT mv /home/tkln/tmpwww/taklimakan-alpha/.env /var/www/.env

echo "install composer in /var/www folder"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT \' cd /var/www/; composer install\'

#suppress error created by composer install
exit 0'''
          }

        }
      }
    }
  }