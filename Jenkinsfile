pipeline {
  agent any
  options { timestamps() }

  environment {
    DOCKER_BUILDKIT = '1'
    COMPOSE_DOCKER_CLI_BUILD = '1'
    COMPOSE_FILE = 'serveur\\docker-compose.yaml'
    COMPOSE_ENV  = 'serveur\\.env.compose'
  }

  stages {

    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    stage('Compose: build & up') {
      steps {
        bat '''
          echo === Docker Compose build & up ===
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% pull
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% build
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% up -d
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
        '''
      }
    }

    stage('Wait for MySQL') {
      steps {
        bat '''
          echo Waiting for MySQL to be ready...
          for /L %%i in (1,1,60) do (
            docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T mysql sh -lc "mysql -hmysql -usymfony -psymfony -e 'SELECT 1'" && goto :ready
            timeout /t 2 >NUL
          )
          echo MySQL not ready after timeout & exit /b 1
          :ready
          echo ✅ MySQL is ready!
        '''
      }
    }

    stage('Composer install') {
      steps {
        bat '''
          set "COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never"
          set "SYMFONY_DIR=/var/www/html"

          echo === Composer install (auto APP_ENV / APP_DEBUG) ===
          %COMPOSE% exec -T -w %SYMFONY_DIR% app composer install --no-interaction --prefer-dist
          %COMPOSE% exec -T app php %SYMFONY_DIR%/bin/console about
        '''
      }
    }

    stage('Migrations & assets') {
      steps {
        bat '''
          set "COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never"
          set "SYMFONY_DIR=/var/www/html"

          echo === Doctrine migrations ===
          %COMPOSE% exec -T -w %SYMFONY_DIR% app php bin/console doctrine:migrations:migrate -n || ver >NUL

          echo === Tailwind build ===
          %COMPOSE% exec -T -w %SYMFONY_DIR% app sh -lc "rm -rf var/tailwind || true"
          %COMPOSE% exec -T -w %SYMFONY_DIR% app php bin/console tailwind:build

          echo === Asset map compile ===
          %COMPOSE% exec -T -w %SYMFONY_DIR% app php bin/console asset-map:compile
        '''
      }
    }

    stage('Smoke test') {
      steps {
        bat '''
          echo === Smoke test (DB connectivity) ===
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T app php /var/www/html/bin/console dbal:run-sql "SELECT 1"
        '''
      }
    }
  }

  post {
    always {
      bat '''
        echo === Docker logs ===
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% logs --no-color > compose.log || ver >NUL
      '''
      archiveArtifacts artifacts: 'compose.log', allowEmptyArchive: true
    }
  }
}
