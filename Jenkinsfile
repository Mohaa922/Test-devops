pipeline {
  agent any
  options { timestamps() }

  parameters {
    string(name: 'COMPOSE_PROFILES', defaultValue: 'prod', description: 'Profiles docker-compose (dev, prod)')
    booleanParam(name: 'HARD_CLEAN', defaultValue: false, description: 'Nettoyage complet Docker (danger sur un agent partagé)')
  }

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
      steps {
        bat '''
          docker version
          docker compose version
        '''
      }
    }

    stage('Clean (safe)') {
      steps {
        bat '''
          echo === Nettoyage Docker local (stack du projet) ===
          docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --profile %COMPOSE_PROFILES% down -v --remove-orphans || ver >NUL
        '''
      }
    }

    stage('Clean (HARD - optionnel)') {
      when { expression { return params.HARD_CLEAN } }
      steps {
        bat '''
          echo === HARD CLEAN (tous conteneurs/images/volumes) ===
          for /f "tokens=*" %%i in ('docker ps -aq') do docker rm -f %%i 2>NUL || ver >NUL
          for /f "tokens=*" %%i in ('docker images -q') do docker rmi -f %%i 2>NUL || ver >NUL
          docker volume prune -f
          docker network prune -f
        '''
      }
    }

    stage('Compose build & up') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES%
          echo === Build et lancement des services ===
          %COMPOSE% pull
          %COMPOSE% build --no-cache
          %COMPOSE% up -d
          %COMPOSE% ps
        '''
      }
    }

    stage('Wait for MySQL (healthy)') {
      steps {
        bat '''
          echo === Attente MySQL ===
          setlocal ENABLEDELAYEDEXPANSION
          for /L %%i in (1,1,60) do (
            for /f "usebackq delims=" %%s in (`docker inspect -f "{{.State.Health.Status}}" vitrine2-mysql 2^>NUL`) do set STATUS=%%s
            if "!STATUS!"=="healthy" goto :ready
            timeout /t 2 >NUL
          )
          echo MySQL non prêt après 2min & exit /b 1
          :ready
          echo MySQL est prêt !
        '''
      }
    }

    stage('Debug env (optionnel)') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES%
          %COMPOSE% exec -T app sh -lc "php -r 'echo \\\"APP_ENV=\\\".getenv(\\\"APP_ENV\\\").PHP_EOL;'"
        '''
      }
    }

    stage('Composer install (prod)') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES%
          set SYMFONY_DIR=/var/www/html

          echo === Installation dépendances Symfony ===
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app composer install --no-dev --prefer-dist --no-interaction --no-progress
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 app php !SYMFONY_DIR!/bin/console about
        '''
      }
    }

    stage('Migrations & Assets (prod)') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES%
          set SYMFONY_DIR=/var/www/html

          echo === Migration base de données ===
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console doctrine:migrations:migrate -n || ^
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console doctrine:schema:update --force --no-interaction

          echo === Compilation assets Symfony ===
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app php bin/console asset-map:compile || ver >NUL

          echo === Build Tailwind (si utilisé) ===
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 -w !SYMFONY_DIR! app sh -lc "TAILWINDCSS_PLATFORM=linux-x64 php bin/console tailwind:build || true"
        '''
      }
    }

    stage('Smoke Test (prod)') {
      steps {
        bat '''
          set COMPOSE=docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES%
          echo === Vérification application ===
          %COMPOSE% ps
          %COMPOSE% exec -T -e APP_ENV=prod -e APP_DEBUG=0 app php /var/www/html/bin/console dbal:run-sql "SELECT 1"
        '''
      }
    }
  }

  post {
    always {
      bat '''
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES% ps
        docker compose -f %COMPOSE_FILE% --env-file %COMPOSE_ENV% --ansi=never --profile %COMPOSE_PROFILES% logs --no-color > compose.log || ver >NUL
      '''
      archiveArtifacts artifacts: 'compose.log', allowEmptyArchive: true
    }
    cleanup {
      echo '✅ Pipeline terminé proprement.'
    }
  }
}
