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
    stage('Archive') {
      steps {
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
    cp $D taklimakan-alpha/
  fi
done

#zip deploy file
zip -r taklimakan-alpha.zip taklimakan-alpha'''
        archiveArtifacts 'taklimakan-alpha.zip'
      }
    }
    stage('Deploy') {
      agent any
      when {
        branch 'develop'
      }
      steps {
        sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
          sh '''# Cleanup previous deploy (if any)
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT rm -rf /home/tkln/tmpdeploy
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT rm -rf /home/tkln/tmpwww

# Upload file to host
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT mkdir /home/tkln/tmpdeploy
scp -o StrictHostKeyChecking=no -P $DEPLOY_DEV_PORT taklimakan-alpha.zip tkln@$DEPLOY_DEV_HOST:/home/tkln/tmpdeploy/taklimakan-alpha.zip

# Unzip file into temp folder
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT mkdir /home/tkln/tmpwww
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT unzip /home/tkln/tmpdeploy/taklimakan-alpha.zip -d /home/tkln/tmpwww

# Remove target folder
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT rm -fr /var/www/

# Move unzipped files into target
ssh -o StrictHostKeyChecking=no tkln@$DEPLOY_DEV_HOST -p $DEPLOY_DEV_PORT mv /home/tkln/tmpwww/taklimakan-alpha/* /var/www/'''
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