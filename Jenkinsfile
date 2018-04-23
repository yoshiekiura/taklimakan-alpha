pipeline {
  agent any
  stages {
    stage('Test') {
      steps {
        echo 'Add UnitTests and build them'
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

#if [ -f pylint.log ]
#then
#  #remove previous execution log
#  rm -rf pylint.log
#fi
rm -rf pylint_*.log

for entry in `ls services/analytics/*.py`; do
    echo $entry
    name=$(basename $entry)
    pylint --rcfile=pylint.cfg --msg-template="{path}:{line}: [{msg_id}, {obj}] {msg} ({symbol})" $entry > pylint_$name.log
#  pylint --rcfile=pylint.cfg --output-format=json $entry > $name.json
    #pylint --msg-template="{path}:{line}: [{msg_id}({symbol}), {obj}] {msg}" > pylint_$name.log
done

#for entry in `ls *.json`; do
#  echo $entry
#  if [ ! -f pylint.json ]
#  then
#     cp -f $entry pylint.json
#  else
#     if [ $entry != "pylint.json" ]
#     then
#       json-merge pylint.json $entry
#     fi
#  fi
#done

#return 0 to be able to continue execution of jenkins steps
exit 0

'''
            warnings(consoleParsers: [[parserName: 'PyLint']], parserConfigurations: [[parserName: 'PyLint', pattern: 'pylint*.log']])
          }
        }
      }
    }
    stage('Archive & Deploy') {
      agent any
      when {
        branch 'develop'
      }
      steps {
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

mv tmpenv .env
'''
        sh '''#!/bin/bash
if [ -d taklimakan-alpha ]
then
# remove previous deploy data
rm -rf taklimakan-alpha
fi

mkdir taklimakan-alpha

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
zip -r taklimakan-alpha.zip taklimakan-alpha'''
        archiveArtifacts 'taklimakan-alpha.zip'
        sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
          sh '''#!/bin/bash
dir
echo "Cleanup previous deploy (if any)"
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT rm -rf /home/tkln/tmpdeploy
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT rm -rf /home/tkln/tmpwww

echo "Upload file to host"
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT mkdir /home/tkln/tmpdeploy
scp -P $DEPLOY_DEV_PORT taklimakan-alpha.zip tkln@$DEPLOY_DEV_HOST:/home/tkln/tmpdeploy/taklimakan-alpha.zip

echo "Unzip file into temp folder"
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT mkdir /home/tkln/tmpwww
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT unzip /home/tkln/tmpdeploy/taklimakan-alpha.zip -d /home/tkln/tmpwww

echo "Remove target folder"
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT rm -fr /var/www/

echo "Move unzipped files into target"
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT mv /home/tkln/tmpwww/taklimakan-alpha/* /var/www/

echo "install composer in /var/www folder"
ssh tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT \' cd /var/www/; compose install\''''
        }

        sh '''#!/bin/bash

echo $BRANCH_NAME
if [ "$BRANCH_NAME" == "master" ]
then
  #some special action for master branch
  echo execute special steps for master branch
else
  if [ "$BRANCH_NAME" == "develop" ]
  then
    #some special action for develop branch
    echo execute special steps for develop branch
  else
    # all other branches should not perform these actions
    echo skip deploy step for $BRANCH_NAME branch
  fi
fi'''
      }
    }
  }
  environment {
    DEPLOY_DEV_HOST = '192.168.100.125'
    DEPLOY_DEV_PORT = '8022'
  }
}