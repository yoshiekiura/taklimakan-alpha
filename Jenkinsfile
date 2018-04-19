pipeline {
  agent any
  stages {
    stage('get github data') {
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

zip -r taklimakan-alpha.zip taklimakan-alpha
'''
      }
    }
    stage('Archive') {
      steps {
        archiveArtifacts '*.zip'
      }
    }
    stage('deploy') {
      steps {
        sh '''if [ $BRANCH_NAME="master" ]
then
  #some special action for master branch
  echo execute special steps for master branch
else
  if [ $BRANCH_NAME="develop" ]
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