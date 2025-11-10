pipeline {
  agent any
  options { timestamps() }

  environment {
    DOCKER_BUILDKIT          = '1'
    COMPOSE_DOCKER_CLI_BUILD = '1'
    COMPOSE_FILE             = 'serveur\\docker-compose.yaml'
    COMPOSE_ENV              = 'serveur\\.env.compose'
  }

  stages {
    stage('Checkout') {
      steps { checkout scm }
    }

    stage('Docker version') {
      steps { bat 'docker version && docker compose version' }
    }

    stage('Clean (safe)') {
      steps {
        bat '''
          echo === Compose down (safe) ===
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% down -v --remove-orphans || ver >NUL
        '''
      }
    }

    stage('Build & Up') {
      steps {
        bat '''
          echo === Build & Up ===
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% pull
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% build --no-cache
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% up -d
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
        '''
      }
    }

    stage('Wait for MySQL (healthy)') {
      steps {
        bat '''
          echo === Waiting for MySQL health ===
          for /L %%i in (1,1,60) do (
            for /f "usebackq delims=" %%s in (`docker inspect -f "{{.State.Health.Status}}" vitrine2-mysql 2^>NUL`) do set STATUS=%%s
            if "!STATUS!"=="healthy" goto :ready
            timeout /t 2 >NUL
          )
          echo MySQL not healthy after 2min & exit /b 1
          :ready
          echo MySQL is healthy
        '''
      }
    }

    stage('Composer install (prod)') {
      steps {
        bat '''
          set SYMFONY_DIR=/var/www/html
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app composer install --no-dev --prefer-dist --no-interaction --no-progress
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 app php !SYMFONY_DIR!/bin/console about
        '''
      }
    }

    stage('Migrations & Assets (prod)') {
      steps {
        bat '''
          set SYMFONY_DIR=/var/www/html

          rem Migrate (fallback schema:update si pas de migrations)
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console doctrine:migrations:migrate -n || ^
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console doctrine:schema:update --force --no-interaction

          rem AssetMapper (tolérant)
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console asset-map:compile || ver >NUL

          rem Tailwind (si utilisé)
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app sh -lc "TAILWINDCSS_PLATFORM=linux-x64 php bin/console tailwind:build || true"
        '''
      }
    }

    stage('Smoke Test') {
      steps {
        bat '''
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -e APP_ENV=prod -e APP_DEBUG=0 app php /var/www/html/bin/console dbal:run-sql "SELECT 1"
        '''
      }
    }
  }

  post {
    always {
      bat '''
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% logs > compose.log || ver >NUL
      '''
      archiveArtifacts artifacts: 'compose.log', allowEmptyArchive: true
    }
    cleanup {
      echo '✅ Done.'
    }
  }
}
