// Jenkinsfile for the opus4-common

pipeline {
    /*
    Agent (location where the pipeline is executed) is the docker file.
    This must have root privileges, because MySQL and Solr must be installed.
    Also, creating a docker on the server requires root privileges.
    Furthermore, the docker socket of the server must be connected to the docker socket of the docker.
    */
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}

    // Build master-branch daily at 3:00. So if there is no commit, the master branch is build regulary
    triggers {
        cron(env.BRANCH_NAME == "master" ? 'H 3 * * *' : '')
    }

    stages {
        stage('prepare') {
            steps {
                // Update Operating system
                sh 'sudo apt-get update'

                // Install and update Composer. Additionally install dependencies of OPUS4-Common
                sh 'curl -s http://getcomposer.org/installer | php && php composer.phar self-update && php composer.phar install'

                // Install XDebug with Pecl -> Using apt-get would install a old version
                sh 'pecl install xdebug-2.8.0 && echo "zend_extension=/usr/lib/php/20151012/xdebug.so" >> /etc/php/7.0/cli/php.ini'
            }
        }

        stage('test') {
            steps {
                sh 'php composer.phar check-full'
            }
        }
    }

    post {
        always {
            /*
           For the cleanup the entire workspace is deleted.
           This reduces the server load, since Jenkins does not track the workspaces unnecessarily.
           It may be possible to turn off tracking, but I couldn't find an option.
           Jenkins must have permissions to delete the workspaces.
           */
           sh "chmod -R 777 ."

           // Publishing test-results (unit-tests)
           step([
               $class: 'JUnitResultArchiver',
               testResults: 'build/phpunit.xml'
           ])

           // Publishing checkstyle-results (coding-style)
           step([
               $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
               pattern: 'build/checkstyle.xml'
           ])

           // Publishing coverage-report if exists
           step([
               $class: 'CloverPublisher',
               cloverReportDir: 'build',
               cloverReportFileName: 'clover.xml'
           ])

           // Publishing PMD-results (static codeanalysis)
           step([
               $class: 'hudson.plugins.pmd.PmdPublisher',
               pattern: 'build/phpmd.xml'
           ])

           // Publishing CPD-results (find duplicated code-fragments)
           step([
               $class: 'hudson.plugins.dry.DryPublisher',
               pattern: 'build/pmd-cpd.xml'
           ])

           // Cleanup
           step([$class: 'WsCleanup', externalDelete: 'rm -rf *'])
        }
    }
}
