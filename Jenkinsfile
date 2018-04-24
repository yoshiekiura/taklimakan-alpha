pipeline {
  agent any
  stages {
    stage('Ask for Branch Id') {
      steps {
        script {
          def commitId = input(
            id: 'userInput', message: 'Enter branch commit ID (Empty for latest)?',
            parameters: [
              string(defaultValue: '',
              description: 'Branch commit ID',
              name: 'CommitId'),
            ])

            //            echo("${commitId}")
            def command = "git cat-file -t ${commitId}"
            //            echo("${command}")
            def commitExist=sh(returnStdout: true, script: command)

            assert commitExist != "commit" && commitId != "": "Branch with commit Id: ${commitId} not exist"
            echo ("Commit exist. Proceed Deployment.")
          }

        }
      }
      stage('get github data') {
        steps {
          checkout([$class: 'GitSCM',
                                                                                                                                                                                                                    branches: [[name: commitId ]],
                                                                                                                                                                                                                      userRemoteConfigs: [[
                                                                                                                                                                                                                                                                credentialsId: 'deploy key for your repo', 
                                                                                                                                                                                                                                                                  url: 'https://github.com/usetech-llc/taklimakan-alpha']]])
            sh '''dir

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
      }
    }