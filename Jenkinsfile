pipeline {
  agent any
  stages {
    stage('get github data') {
      steps {
        git(url: 'https://github.com/usetech-llc/taklimakan-alpha', branch: 'develop')
        sh 'git clone https://github.com/usetech-llc/taklimakan-alpha -b develop'
      }
    }
    stage('Archive') {
      steps {
        archiveArtifacts '*.*'
      }
    }
  }
}