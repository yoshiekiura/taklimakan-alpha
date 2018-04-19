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

if [  -f pylint.log ]
then
  #remove previous execution log
  rm -rf pylint.log
fi

for entry in `ls services/analytics/*.py`; do
  echo $entry
  name=$(basename $entry .py)
  if [ ! -f pylint.log ]
  then
    pylint --rcfile=pylint.cfg $entry > pylint.log
  else
    pylint --rcfile=pylint.cfg $entry >> pylint.log
  fi
done

#return 0 to be able to continue execution of jenkins steps
exit 0

'''
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