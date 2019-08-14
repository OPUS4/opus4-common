pipeline {
    agent { dockerfile {args "-u root -v /var/run/docker.sock:/var/run/docker.sock"}}

    stages {

        stage('prepare') {
            steps {
                sh 'composer install'
                sh 'composer update'
            }
        }

        stage('test') {
            steps {
                sh 'composer check-full'
            }
        }

        stage('publish') {
            steps {
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
                    cloverReportDir: 'build/coverage/',
                    cloverReportFileName: 'clover.xml'
                ])
                step([$class: 'WsCleanup'])
            }
        }
    }
}
