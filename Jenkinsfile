pipeline {
  agent any
  stages {
    stage('Ask for Branch Id & checkout this revision') {
      when {
        branch 'release/**'
      }
      steps {
        script {
          def commitId = input(
            id: 'userInput', message: 'Enter branch commit ID (Empty for latest)?',
            parameters: [
              string(defaultValue: '',
              description: 'Branch commit ID',
              name: 'CommitId'),
            ])
            if (commitId != "") {
              def command = "git cat-file -t ${commitId}"
              def commitExist=sh(returnStdout: true, script: command)
              echo("commitExist= \"${commitExist}\"; commitId= \"${commitId}\"")
              assert commitExist != "commit" && commitId != "": "Branch with commit Id: ${commitId} not exist"
              echo ("Commit exist. Proceed Deployment.")

              echo("${commitId}")
              def fetchcmd = sh(returnStdout: true, script: 'git fetch')
              command = "git checkout ${commitId}"
              def checkoutcmd = sh(returnStdout: true, script: command)
              echo("${checkoutcmd}")
            }
            else
            {
              echo("Use HEAD revision")
            }
          }

        }
      }
      stage('Archive') {
        steps {
          sh '''#!/bin/bash
if [ -d taklimakan-alpha ]; then
  # remove previous deploy data
  rm -rf taklimakan-alpha
fi

mkdir taklimakan-alpha

for D in *; do
  if [ $D != "taklimakan-alpha" ] &&
     [ $D != ".git" ] &&
     [ $D != "Jenkinsfile" ] &&
     [ $D != "CodeAnalysis" ] &&
     [ $D != "deploy" ] &&
     [ $D != "phpUnitRes" ] &&
     [ $D != "tests" ] &&
     [ $D != "createSL.bash" ] &&
     [[ $D != *"pylint"* ]]; then
    # copy to taklimakan-alpha
    if [ -d "${D}" ]; then
      cp -R $D taklimakan-alpha/
    else
      cp $D taklimakan-alpha/
    fi
  fi
done

#zip deploy file
zip -r -q -m taklimakan-alpha.zip taklimakan-alpha

'''
          archiveArtifacts '*.zip'
        }
      }
      stage('Install Composer') {
        when {
          not {
            branch 'master'
          }

        }
        steps {
          sh '''#!/bin/bash
echo "create dummy symfony environment file"
echo "" > ".env"'''
          sh '''#!/bin/bash
echo "install composer"
composer install'''
          sh '''#!/bin/bash
echo "create catalog for Code Analysis results"
mkdir -p results/CALogs'''
        }
      }
      stage('Tests') {
        when {
          not {
            branch 'master'
          }

        }
        steps {
          sh '''#!/bin/bash
mkdir -p results/phpUnitRes
./vendor/bin/simple-phpunit --log-junit results/phpUnitRes/junit.xml --coverage-html=results/phpUnitRes/'''
        }
      }
      stage('Static Analysis') {
        when {
          not {
            branch 'master'
          }

        }
        parallel {
          stage('Python Lint') {
            steps {
              sh '''#!/bin/bash

if [ ! -f pylint.cfg ]
then
  # generate pylint configuration file if not exist
  pylint --generate-rcfile > pylint.cfg
fi

for entry in `ls services/analytics/*.py`; do
    echo $entry
    name=$(basename $entry)
    pylint --rcfile=pylint.cfg --msg-template="{path}:{line}: [{msg_id}, {obj}] {msg} ({symbol})" $entry > pylint_$name.log
done



#return 0 to be able to continue execution of jenkins steps
exit 0

'''
              warnings(consoleParsers: [[parserName: 'PyLint']], parserConfigurations: [[parserName: 'PyLint', pattern: 'pylint*.log']])
              archiveArtifacts 'pylint_*.log'
            }
          }
          stage('Copy paste detection') {
            steps {
              sh './vendor/bin/phpcpd --log-pmd results/CALogs/pmd-cpd.xml --exclude vendor . || exit 0'
              dry(canRunOnFailed: true, pattern: 'results/CALogs/pmd-cpd.xml')
            }
          }
          stage('Mess Detection') {
            steps {
              sh './vendor/bin/phpmd . xml cleancode,codesize,controversial,design,unusedcode,naming --reportfile results/CALogs/pmd.xml --exclude vendor,.git,tests,var || exit 0'
              pmd(canRunOnFailed: true, pattern: 'results/CALogs/pmd.xml')
            }
          }
        }
      }
      stage('Compare Symfony ENV') {
        when {
          anyOf {
            branch 'master'
            branch 'release/**'
          }

        }
        steps {
          sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
            sh '''#!/bin/bash
# take symfony enviroment file to make sure that
#   deploy process not crash server
#   (it could be if symfony environment variables are missed)

if [ "$BRANCH_NAME" != "master" ]; then
 echo "get Symfony enviroment file from Develop"
 scp -P $DEVELOP_PORT tkln@$DEVELOP_HOST:/var/www/.env develop.env

 if [ ! -f develop.env ]; then
    echo "Symfony environment file not exist"
    rm -rf *.env
    exit 1
  fi
fi'''
          }

          sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
            sh '''#!/bin/bash
# take symfony enviroment file to make sure that
#   deploy process not crash server
#   (it could be if symfony environment variables are missed)

echo "get Symfony enviroment file from Release"
scp -P $RELEASE_PORT tkln@$RELEASE_HOST:/var/www/.env release.env

if [ ! -f release.env ]; then
  echo "Symfony environment file not exist"
  rm -rf *.env
  exit 1
fi
'''
          }

          sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
            sh '''#!/bin/bash
# take symfony enviroment file to make sure that
#   deploy process not crash server
#   (it could be if symfony environment variables are missed)

if [ "$BRANCH_NAME" == "master" ]; then
  echo "get Symfony enviroment file from Release"
  scp -P $PRODUCTION_PORT tkln@$PRODUCTION_HOST:/var/www/.env master.env

  if [ ! -f master.env ]; then
    echo "Symfony environment file not exist"
    rm -rf *.env
    exit 1
  fi
fi
'''
          }

          sh '''#!/bin/bash
echo "Branch Name: $BRANCH_NAME"
if [ "$BRANCH_NAME" == "master" ]; then
  # Verify that .env file of realease branch contain the
  #   same Symfony environment variables as in develop branch
  FROM="release.env"
  TO="master.env"
else
  if [ "$BRANCH_NAME" != "develop" ]; then
    # Verify that .env file of realease branch contain the
    #   same symfony environment variables as in develop branch
    echo "Verify that Symfony enviroment variables are exist both in release and develop branch"
    FROM="develop.env"
    TO="release.env"
  fi
fi

while IFS= read -r line
do
  if [[ $line != "#"* ]] && [[ $line == *"="* ]]; then
    envvariable=$(echo $line| cut -d\'=\' -f 1)
    if ! grep "^[^#;]" $TO | grep "$envvariable=" >> /dev/null; then
      echo "Symfony enviroment variable files are different. Merge required"
      rm -rf *.env
      exit 1
    fi
  fi
done <"$FROM"

echo "Symfony enviromnt variable file is correct. Proceed with deploy"
'''
          sh 'rm -rf *.env'
        }
      }
      stage('Deploy') {
        when {
          anyOf {
            branch 'master'
            branch 'release/**'
            branch 'develop'
          }

        }
        steps {
          sh '''echo "display git branch info to make sure that branch is switch to Commit"
git branch'''
          sh '''echo "#!/bin/bash" > deploy
echo "#########################################################" >> deploy
echo "# deploy" >> deploy
echo "#" >> deploy
echo "# WARNING: This script is autogenerated by Jenkins" >> deploy
echo "#   all changes maid into this script will be lost" >> deploy
echo "#   after Jenkins deploy project one more time" >> deploy
echo "#" >> deploy
echo "# ALERT: DO NOT EXECUTE this script manually" >> deploy
echo "#" >> deploy
echo "# Purpose:" >> deploy
echo "#   This script is used to deploy new version of site" >> deploy
echo "# Inputs:" >> deploy
echo "#   \\$1 - zip file name which contain new version of site" >> deploy
echo "#   \\$2 - version of this deploy (generated by Jenkins)" >> deploy
echo "#" >> deploy
echo "#########################################################" >> deploy
echo "" >> deploy
echo "cd /var/www/" >> deploy
echo "if [ \\$# != 2 ]; then" >> deploy
echo "  echo \\"Deploy is not successful. Wrong number of arguments\\"" >> deploy
echo "  exit 1" >> deploy
echo "fi" >> deploy
echo "" >> deploy
echo "if [ ! -f DEPLOY/\\$1.zip ]; then" >> deploy
echo "  echo \\"Deploy is not successful. Deploy file is not exist: \\$1.zip\\"" >> deploy
echo "  exit 1" >> deploy
echo "fi" >> deploy
echo "" >> deploy
echo "zip_name=\\$1" >> deploy
echo "version_id=\\$2" >> deploy
echo "" >> deploy
echo "#1. Create version folder" >> deploy
echo "if [ ! -d DEPLOY/\\$version_id ]; then" >> deploy
echo "  mkdir DEPLOY/\\$version_id" >> deploy
echo "  #1. unzip taklimakan-alpha.zip" >> deploy
echo "  mv DEPLOY/\\$zip_name.zip DEPLOY/\\$version_id" >> deploy
echo "  unzip -q DEPLOY/\\$version_id/\\$zip_name.zip -d DEPLOY/\\$version_id" >> deploy
echo "  mv -f DEPLOY/\\$version_id/\\$zip_name/* DEPLOY/\\$version_id" >> deploy
echo "  #remove temp folder" >> deploy
echo "  rm -rf DEPLOY/\\$version_id/\\$zip_name" >> deploy
echo "  #remove zip file which used to deploy" >> deploy
echo "  rm -rf DEPLOY/\\$version_id/\\$zip_name.zip" >> deploy
echo "else" >> deploy
echo "  echo \\"Deploy is not successful. Deploy version already exist\\"" >> deploy
echo "  exit 1" >> deploy
echo "fi" >> deploy
echo "" >> deploy
echo "#2. install composer." >> deploy
echo "#      If composer installation is failed then it finished with error code !=0" >> deploy
echo "#        and deploy script stopped and exit with the same error code" >> deploy
echo "#        and Jenkins treat it as error and jenkins build will failed" >> deploy
echo "" >> deploy
echo "# copy .env to DEPLOY/<version> which is necessary for symphony" >> deploy
echo "cp .env DEPLOY/\\$version_id/.env" >> deploy
echo "cd DEPLOY/\\$version_id" >> deploy
echo "composer install" >> deploy
echo "" >> deploy
echo "# return to /var/www/ folder" >> deploy
echo "cd /var/www/" >> deploy
echo "" >> deploy
echo "#Create symlinks" >> deploy
echo "./createSL.bash \\$version_id" >> deploy
'''
          sh '''echo "#!/bin/bash" > createSL.bash
echo "#########################################################" >> createSL.bash
echo "# createSL.bash" >> createSL.bash
echo "#" >> createSL.bash
echo "# WARNING: This script is autogenerated by Jenkins" >> createSL.bash
echo "#   all changes maid into this script will be lost" >> createSL.bash
echo "#   after Jenkins deploy project one more time" >> createSL.bash
echo "#" >> createSL.bash
echo "# Purpose:" >> createSL.bash
echo "#   This script is used to create Symbolic Links from" >> createSL.bash
echo "#     files and folders to the deployed version of site" >> createSL.bash
echo "#   It is possible to use this script to rollback" >> createSL.bash
echo "#     changes maid by the deploy just mention version" >> createSL.bash
echo "#     which need to be set now as working version." >> createSL.bash
echo "#" >> createSL.bash
echo "# Input:" >> createSL.bash
echo "#   versionId - is version uniq id which used to set SL" >> createSL.bash
echo "#     folder with versionId should exist in DEPLOY folder" >> createSL.bash
echo "#     If folder not exist then versionId.zip should exist" >> createSL.bash
echo "#     in DEPLOY folder and in this case it means that" >> createSL.bash
echo "#     version will be rolled back to previous version" >> createSL.bash
echo "#     current version will be zipped and could be used" >> createSL.bash
echo "#     in future" >> createSL.bash
echo "#" >> createSL.bash
echo "# Examples:" >> createSL.bash
echo "#   createSL.bash 1d3f74d.23" >> createSL.bash
echo "#   createSL.bash 15" >> createSL.bash
echo "#" >> createSL.bash
echo "#########################################################" >> createSL.bash
echo "" >> createSL.bash
echo "cd /var/www/" >> createSL.bash
echo "if [ \\$# != 1 ]; then" >> createSL.bash
echo "  echo \\"Deploy is not success. Deploy version is not set\\"" >> createSL.bash
echo "  exit 1;" >> createSL.bash
echo "fi" >> createSL.bash
echo "" >> createSL.bash
echo "versionId=\\$1" >> createSL.bash
echo "" >> createSL.bash
echo "if [ ! -d DEPLOY/\\$versionId ]; then" >> createSL.bash
echo "  if [ ! -f DEPLOY/\\$versionId.zip ]; then" >> createSL.bash
echo "    echo \\"Deploy is not success. Previous version is not exist\\"" >> createSL.bash
echo "    exit 1;" >> createSL.bash
echo "  fi" >> createSL.bash
echo "  # unzip previous version" >> createSL.bash
echo "  unzip -q DEPLOY/\\$versionId.zip -d DEPLOY" >> createSL.bash
echo "" >> createSL.bash
echo "  # remove zip file" >> createSL.bash
echo "  rm -rf DEPLOY/\\$versionId.zip" >> createSL.bash
echo "fi" >> createSL.bash
echo "" >> createSL.bash
echo "#folder DEPLOY/\\$versioId exist now just create SL" >> createSL.bash
echo "#  for all files/folder in it except public" >> createSL.bash
echo "#  create links for all objects in public folder" >> createSL.bash
echo "#  except \\"images\\" it will remain the same as before" >> createSL.bash
echo "for entry in \\`ls -d DEPLOY/\\$versionId/*\\`; do" >> createSL.bash
echo "  name=\\$(basename \\$entry)" >> createSL.bash
echo "" >> createSL.bash
echo "  if [ \\"\\$name\\" != \\"public\\" ] && [ \\"\\$name\\" != \\"var\\" ] && [[ \\$name != *\\".zip\\"* ]]; then" >> createSL.bash
echo "    if [ -f \\$entry ] || [ -d \\$entry ]; then" >> createSL.bash
echo "      # remove file or folder" >> createSL.bash
echo "      rm -rf \\$name" >> createSL.bash
echo "    fi" >> createSL.bash
echo "    # create new symbolic link" >> createSL.bash
echo "    ln -sfn \\$entry \\$name" >> createSL.bash
echo "    echo \\"Creating symbolic link from \\$name to .. \\$entry ... done\\"" >> createSL.bash
echo "  fi" >> createSL.bash
echo "done" >> createSL.bash
echo "" >> createSL.bash
echo "# Special folder public is not used as" >> createSL.bash
echo "#   symlink since it is necessary to leave" >> createSL.bash
echo "#   /var/www/public/images as is all other" >> createSL.bash
echo "#   files and folders will be used as symlink" >> createSL.bash
echo "if [ ! -d public ]; then" >> createSL.bash
echo "  mkdir public" >> createSL.bash
echo "  mkdir public/images" >> createSL.bash
echo "fi" >> createSL.bash
echo "" >> createSL.bash
echo "for public_entry in \\`ls -d DEPLOY/\\$versionId/public/*\\`; do" >> createSL.bash
echo "  shortname=\\$(basename \\$public_entry)" >> createSL.bash
echo "  #create symbolic links inside public for all items except images" >> createSL.bash
echo "  if [ \\"\\$shortname\\" != \\"images\\" ]; then" >> createSL.bash
echo "    # create new symbolic link" >> createSL.bash
echo "    cd public" >> createSL.bash
echo "    # remove existing file/folder/symlink" >> createSL.bash
echo "    if [ -f \\$shortname ] || [ -d \\$shortname ]; then" >> createSL.bash
echo "      # remove file or folder" >> createSL.bash
echo "      rm -rf \\$shortname" >> createSL.bash
echo "    fi" >> createSL.bash
echo "" >> createSL.bash
echo "    ln -sfn ../\\$public_entry \\$shortname" >> createSL.bash
echo "    cd .." >> createSL.bash
echo "  fi" >> createSL.bash
echo "done" >> createSL.bash
echo "" >> createSL.bash
echo "" >> createSL.bash
echo "cd DEPLOY" >> createSL.bash
echo "#zip previous version of deploy" >> createSL.bash
echo "for folderToZip in \\`ls -d *\\`; do" >> createSL.bash
echo "  if [ -d \\$folderToZip ]; then" >> createSL.bash
echo "    if [ \\"\\$folderToZip\\" != \\"\\$versionId\\" ]; then" >> createSL.bash
echo "      echo \\"zip previous version: \\$folderToZip\\"" >> createSL.bash
echo "      zip -r -m -q  \\$folderToZip.zip \\$folderToZip" >> createSL.bash
echo "      rm -rf \\$folderToZip" >> createSL.bash
echo "    fi" >> createSL.bash
echo "  fi" >> createSL.bash
echo "done" >> createSL.bash
echo "" >> createSL.bash
echo "echo \\"Deploy succeed. Used version: \\$versionId\\"" >> createSL.bash
'''
          sshagent(credentials: ['BlockChain'], ignoreMissing: true) {
            sh '''#!/bin/bash
echo "Branch Name: $BRANCH_NAME"
if [ "$BRANCH_NAME" == "master" ]; then
  DEPLOY_HOST=$PRODUCTION_HOST
  DEPLOY_PORT=$PRODUCTION_PORT
elif [ "$BRANCH_NAME" == "develop" ]; then
  DEPLOY_HOST=$DEVELOP_HOST
  DEPLOY_PORT=$DEVELOP_PORT
else
  #release branch
  DEPLOY_HOST=$RELEASE_HOST
  DEPLOY_PORT=$RELEASE_PORT
fi
echo "Deploy Host: $DEPLOY_HOST:$DEPLOY_PORT"

echo "Upload file to host"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT mkdir -p /var/www/DEPLOY
scp -P $DEPLOY_PORT taklimakan-alpha.zip tkln@$DEPLOY_HOST:/var/www/DEPLOY/taklimakan-alpha.zip
scp -P $DEPLOY_PORT deploy tkln@$DEPLOY_HOST:/var/www/deploy
scp -P $DEPLOY_PORT createSL.bash tkln@$DEPLOY_HOST:/var/www/createSL.bash

echo "Run deploy script"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT chmod -f 777 /var/www/deploy
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT chmod -f 777 /var/www/createSL.bash
OUTPUT="$(git log --pretty=format:\'%h\' -n 1)"
ssh tkln@$DEPLOY_HOST -p $DEPLOY_PORT /var/www/deploy taklimakan-alpha $BUILD_NUMBER.$OUTPUT'''
          }

        }
      }
    }
    environment {
      DEVELOP_HOST = '192.168.100.125'
      DEVELOP_PORT = '8022'
      RELEASE_HOST = '192.168.100.126'
      RELEASE_PORT = '8022'
      PRODUCTION_HOST = '192.168.100.127'
      PRODUCTION_PORT = '8022'
    }
    post {
      always {
        junit 'results/phpUnitRes/*.xml'
        publishHTML(allowMissing: false, alwaysLinkToLastBuild: false, keepAll: true, reportDir: 'results/phpUnitRes', reportFiles: 'index.html', reportName: 'PHP Unit tests Report', reportTitles: '')

      }

    }
  }