pipeline {
    agent {
        docker {
            image 'php:7.2'
        }
    }
    stages {
        stage('Build') {            
            steps {
                composer 'install'
            }
        }        
        stage('Test') {            
            steps {                
                phpunit 'tests/'
            }        
        }
        stage('Deploy - Staging') {            
            steps {                
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
