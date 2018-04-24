pipeline {
  agent any
  stages {
    stage('get github data') {
      steps {
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
