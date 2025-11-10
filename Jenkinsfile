pipeline {
  agent any

  options {
    timestamps()
  }

  environment {
    DOCKER_BUILDKIT            = '1'
    COMPOSE_DOCKER_CLI_BUILD   = '1'
    // chemins côté Windows pour Jenkins
    COMPOSE_FILE               = 'serveur\\docker-compose.yaml'
    COMPOSE_ENV                = 'serveur\\.env.compose'
  }

  stages {

    stage('Checkout') {
      steps {
        checkout scm
      }
    }

    // Nettoyage complet optionnel (décommente si besoin)
    // stage('Clean local Docker environment') {
    //   steps {
    //     bat '''
    //       echo === Nettoyage Docker local ===
    //       docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% down -v --remove-orphans || ver >NUL
    //       echo Supprime tous les conteneurs...
    //       for /f "delims=" %%i in ('docker ps -aq') do docker rm -f %%i 2>NUL
    //       echo Supprime toutes les images...
    //       for /f "delims=" %%i in ('docker images -q') do docker rmi -f %%i 2>NUL
    //       echo Supprime les volumes non utilises...
    //       docker volume prune -f
    //       echo Supprime les reseaux non utilises...
    //       docker network prune -f
    //       echo Nettoyage termine.
    //     '''
    //   }
    // }

    stage('Compose: build & up') {
      steps {
        bat '''
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
          echo Waiting for MySQL...
          for /L %%i in (1,1,60) do (
            docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T mysql sh -lc "mysql -hmysql -usymfony -psymfony -e 'SELECT 1'" && goto :ready
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
          set "COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never"
          set "SYMFONY_DIR=/var/www/html"

          REM IMPORTANT: forcer l'env PROD pour composer et le cache:clear auto
          %COMPOSE% exec -T -env-file COMPOSE_ENV -w %SYMFONY_DIR% app composer install --no-dev --prefer-dist --no-interaction --no-progress

          %COMPOSE% exec -T -env-file COMPOSE_ENV app php %SYMFONY_DIR%/bin/console about
        '''
      }
    }

    stage('Migrations & assets (APP_ENV=prod)') {
      steps {
        bat '''
          set "COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never"
          set "SYMFONY_DIR=/var/www/html"

          REM (debug) voir les binaires Symfony
          %COMPOSE% exec -T app sh -lc "pwd; ls -la %SYMFONY_DIR%/bin"

          REM migrations (si aucune, ignorer l’erreur)
          %COMPOSE% exec -T -env-file COMPOSE_ENV -w %SYMFONY_DIR% app php bin/console doctrine:migrations:migrate -n || ver >NUL

          REM --- Tailwind : purge + download (linux-x64) + build ---
          %COMPOSE% exec -T -env-file COMPOSE_ENV -w %SYMFONY_DIR% app sh -lc "rm -rf var/tailwind || true"
          %COMPOSE% exec -T -env-file COMPOSE_ENV -w %SYMFONY_DIR% app php bin/console tailwind:build

          REM --- Ensuite, compilation des assets ---
          %COMPOSE% exec -T -env-file COMPOSE_ENV -w %SYMFONY_DIR% app php bin/console asset-map:compile
        '''
      }
    }

    stage('Smoke test') {
      steps {
        bat '''
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% exec -T -env-file COMPOSE_ENV app php /var/www/html/bin/console dbal:run-sql "SELECT 1"
        '''
      }
    }
  }

  post {
    always {
      bat '''
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% ps
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% logs --no-color > compose.log || ver >NUL
      '''
      archiveArtifacts artifacts: 'compose.log', allowEmptyArchive: true
    }
  }
}
