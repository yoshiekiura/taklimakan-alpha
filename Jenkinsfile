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

if [ -f pylint.log ]
then
  #remove previous execution log
  rm -rf pylint.log
fi

for entry in `ls services/analytics/*.py`; do
  echo $entry
  name=$(basename $entry)
  if [ ! -f pylint.log ]
  then
    pylint --rcfile=pylint.cfg --msg-template="{path}:{line}: [{msg_id}({symbol}), {obj}] {msg}" > pylint.log
  else
    pylint --rcfile=pylint.cfg --msg-template="{path}:{line}: [{msg_id}({symbol}), {obj}] {msg}" > pylint.log
  fi
#  pylint --rcfile=pylint.cfg --output-format=json $entry > $name.json
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
            archiveArtifacts 'pylint.log'
          }
        }
      }
    }
    stage('Archive') {
      steps {
        sh '''dir

if [ ! -d "taklimakan-alpha" ]
then
    git clone https://github.com/usetech-llc/taklimakan-alpha -b develop
else
    cd taklimakan-alpha
    git fetch --all
    cd ..
fi

#remove git folder
cd taklimakan-alpha
rm -rf .git
rm -f Jenkinsfile
rm -f .gitignore
cd ..

zip -r taklimakan-alpha.zip taklimakan-alpha'''
        archiveArtifacts '*.zip'
      }
    }
    stage('Deploy') {
      steps {
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
}