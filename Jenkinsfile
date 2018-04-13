pipeline {
  agent any
  stages {
    stage('get github data') {
      steps {
        git(url: 'https://github.com/usetech-llc/taklimakan-alpha', branch: 'develop')
        sh '''dir

if [ ! -d "taklimakan-alpha" ]
then
    git clone https://github.com/usetech-llc/taklimakan-alpha -b develop
else
    cd taklimakan-alpha
    git fetch --all
    cd ..
fi

zip -r deploy.zip taklimakan-alpha'''
      }
    }
    stage('Archive') {
      steps {
        archiveArtifacts '*.zip'
      }
    }
  }
}