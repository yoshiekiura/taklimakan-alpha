pipeline {
  agent any
  stages {
    stage('get github data') {
      steps {
        sh '''cd ..
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