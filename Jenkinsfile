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
cd ..

zip -r taklimakan-alpha.zip taklimakan-alpha'''
      }
    }
    stage('Archive') {
      steps {
        archiveArtifacts '*.zip'
      }
    }
  }
}