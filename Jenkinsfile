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

    // 🔥 Nettoyage Docker local (compatible Windows)
    stage('Clean local Docker environment') {
      steps {
        bat '''
          echo === Nettoyage Docker local ===
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never down -v --remove-orphans || ver >NUL

          echo Supprime tous les conteneurs...
          for /f "tokens=*" %%i in ('docker ps -aq') do docker rm -f %%i 2>NUL || ver >NUL

          echo Supprime toutes les images...
          for /f "tokens=*" %%i in ('docker images -q') do docker rmi -f %%i 2>NUL || ver >NUL

          echo Supprime les volumes non utilises...
          docker volume prune -f

          echo Supprime les reseaux non utilises...
          docker network prune -f

          echo Nettoyage termine.
        '''
      }
    }

    stage('Compose: build & up') {
      steps {
        bat '''
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never pull
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never build --no-cache
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never up -d
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never ps
        '''
      }
    }

    stage('Wait for MySQL') {
      steps {
        bat '''
          echo Waiting for MySQL...
          for /L %%i in (1,1,60) do (
            docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never exec -T mysql sh -lc "mysql -usymfony -psymfony -e 'SELECT 1'" && goto :ready
            timeout /t 2 >NUL
          )
          echo MySQL not ready after timeout & exit /b 1
          :ready
          echo MySQL is ready!
        '''
      }
    }

    stage('Composer install (APP_ENV=prod)') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never
          set SYMFONY_DIR=/var/www/html

          REM IMPORTANT: forcer l’env PROD pour composer et le cache:clear auto
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app composer install --no-dev --prefer-dist --no-interaction --no-progress
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 app php !SYMFONY_DIR!/bin/console about
        '''
      }
    }

    stage('Migrations & assets (APP_ENV=prod)') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never
          set SYMFONY_DIR=/var/www/html

          REM (debug) voir les binaires
          %COMPOSE% exec -T app sh -lc "pwd; ls -la !SYMFONY_DIR!/bin"

          REM migrations (si aucune, ignorer l’erreur)
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console doctrine:migrations:migrate -n || ver >NUL

          REM --- Tailwind d'abord, puis asset mapper (et purge le cache binaire) ---
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app sh -lc "rm -rf var/tailwind || true"
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app sh -lc "TAILWINDCSS_PLATFORM=linux-x64 php bin/console tailwind:build || true"
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console asset-map:compile
        '''
      }
    }

    stage('Smoke test') {
      steps {
        bat '''
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never ps
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never exec -T -e APP_ENV=prod -e APP_DEBUG=0 app php /var/www/html/bin/console dbal:run-sql "SELECT 1"
        '''
      }
    }
  }

  post {
    always {
      bat '''
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never ps
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never logs --no-color > compose.log || ver >NUL
      '''
      archiveArtifacts artifacts: 'compose.log', allowEmptyArchive: true
    }
  }
}
