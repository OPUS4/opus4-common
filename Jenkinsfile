// Jenkinsfile for the application

// Defining Area
def jobNameParts = JOB_NAME.tokenize('/') as String[]
def projectName = jobNameParts[0]
def buildType = "short"

// Set buildType to a complete Build with Coverage, if projectName contains night and Branch-Name fits
if (projectName.contains('night')) {
    buildType = "long"
}

pipeline {
    // Set agent -> Where the pipeline is executed -> Docker build from dockerfile and run as root (necessary)
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}

    // Set trigger if build is long -> Every day 3:00
    triggers {
        cron( buildType.equals('long') ? 'H 3 * * *' : '')
    }

    stages {
        stage('prepare') {
            steps {
                // Update and install XDebug and Composer
                sh 'curl -s http://getcomposer.org/installer | php && php composer.phar self-update && php composer.phar install'
                sh 'sudo apt-get update'
                sh 'pecl install xdebug-2.8.0 && echo "zend_extension=/usr/lib/php/20151012/xdebug.so" >> /etc/php/7.0/cli/php.ini'
            }
        }

        stage('test') {
            steps {
                script{
                    switch (buildType) {
                        case "long":
                            sh 'php composer.phar check-full'
                            break
                        default:
                            sh 'php composer.phar test'
                            sh 'php composer.phar analyse'
                            break
                    }
                }
            }
        }
    }
    post {
        always {
            // Change Permissions -> So workspace can be deleted
            sh "chmod -R 777 ."
            step([
                $class: 'JUnitResultArchiver',
                testResults: 'build/phpunit.xml'
            ])
            step([
                $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
                pattern: 'build/checkstyle.xml'
            ])
            step([
                $class: 'CloverPublisher',
                cloverReportDir: 'build',
                cloverReportFileName: 'clover.xml'
            ])
            step([
                $class: 'hudson.plugins.pmd.PmdPublisher',
                pattern: 'build/phpmd.xml'
            ])
            step([$class: 'WsCleanup', externalDelete: 'rm -rf *'])
        }
    }
}
