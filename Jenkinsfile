pipeline {
    agent any
    stages {
        stage('Build') {            
            steps {
                sh 'cd ${WORKSPACE}/fanyi.juejin-lumen/ && composer install'
            }
        }        
        stage('Test') {            
            steps {                
                sh 'cd ${WORKSPACE}/fanyi.juejin-lumen/ && phpunit tests/'
            }        
        }
        stage('Deploy - Staging') {            
            steps {
                sh 'php -S 0.0.0.0:80 -t ${WORKSPACE}/fanyi.juejin-lumen/public/'
                echo 'Deploying onto staging'                
            }        
        }        
        stage('Sanity check') {            
            steps {                
                input "Does the staging environment look ok?"            
            }        
        }        
        stage('Deploy - Production') {            
            steps {                
                echo 'Deploying onto production'
            }        
        }    
    }
 
    post {        
        always {            
            echo 'One way or another, I have finished'            
            deleteDir() /* clean up our workspace */        
        }        
        success {            
            echo 'I succeeded!'
        }        
        unstable {            
            echo 'I am unstable :/'        
        }        
        failure {            
            echo 'I failed :('        
        }        
        changed {            
            echo 'Things were different before...'        
        }    
    }
}
