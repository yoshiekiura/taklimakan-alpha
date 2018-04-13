pipeline {
  agent any
  stages {
    stage('get github data') {
      steps {
        git(url: 'https://github.com/usetech-llc/taklimakan-alpha', branch: 'develop')
        sh '''git clone https://github.com/usetech-llc/taklimakan-alpha -b develop
dir
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