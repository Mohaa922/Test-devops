pipeline {
  agent any
  environment {
    DOCKER_BUILDKIT = '1'
    COMPOSE_DOCKER_CLI_BUILD = '1'
  }
  stages {
    stage('Checkout'){ steps { checkout scm } }

    stage('Compose Build & Up'){
      steps {
        sh '''
          docker compose -f serveur/docker-compose.yml --env-file serveur/.env.compose build
          docker compose -f serveur/docker-compose.yml --env-file serveur/.env.compose up -d
        '''
      }
    }

    stage('DB Ready'){
      steps {
        sh '''
          for i in $(seq 1 40); do
            docker compose -f serveur/docker-compose.yml exec -T mysql sh -lc "mysql -usymfony -psymfony -e 'SELECT 1'" && exit 0
            sleep 2
          done
          exit 1
        '''
      }
    }

    stage('Migrations & Assets'){
      steps {
        sh '''
          docker compose -f serveur/docker-compose.yml exec -T app php bin/console doctrine:migrations:migrate -n || true
          docker compose -f serveur/docker-compose.yml exec -T app php bin/console asset-map:compile
          docker compose -f serveur/docker-compose.yml exec -T app php bin/console tailwind:build || true
        '''
      }
    }

    stage('Smoke'){
      steps {
        sh '''
          docker compose -f serveur/docker-compose.yml ps
          docker compose -f serveur/docker-compose.yml exec -T app php bin/console dbal:run-sql "SELECT 1"
        '''
      }
    }
  }
}
