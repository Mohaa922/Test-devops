pipeline {
  agent any
  environment {
    DOCKER_BUILDKIT = '1'
    COMPOSE_DOCKER_CLI_BUILD = '1'
    // File Compose + .env (projet=serveur)
    COMPOSE = 'docker compose -f serveur/docker-compose.yaml --env-file serveur/.env.compose'
  }

  stages {
    stage('Checkout') { steps { checkout scm } }

    stage('Preflight: workspace') {
      steps {
        bat '''
          echo ==== Workspace tree (top) ====
          dir /b
          echo ==== vitrine presence check ====
          if not exist vitrine\\bin\\console (
            echo [ERROR] vitrine\\bin\\console absent du workspace. Assure-toi que le repo contient vitrine/.
            exit /b 1
          )
          dir /b vitrine\\bin
        '''
      }
    }

    stage('Compose: build & up'){
      steps {
        bat '''
          %COMPOSE% build
          %COMPOSE% up -d
          %COMPOSE% ps
        '''
      }
    }

    stage('DB ready'){
      steps {
        bat '''
          echo Attente de MySQL...
          for /l %%i in (1,1,40) do (
            %COMPOSE% exec -T mysql sh -lc "mysql -usymfony -psymfony -e 'SELECT 1'" && goto :ready
            timeout /t 2 >NUL
          )
          echo [ERROR] MySQL indisponible
          exit /b 1
          :ready
          echo MySQL OK
        '''
      }
    }

    stage('Preflight: bin/console in container') {
      steps {
        bat '''
          %COMPOSE% exec -T app sh -lc "pwd; ls -la; test -f bin/console || (echo '❌ bin/console manquant dans /var/www/html'; exit 1)"
        '''
      }
    }

    stage('Migrations & assets'){
      steps {
        bat '''
          rem Migrations (si aucune, continuer)
          %COMPOSE% exec -T app sh -lc "php bin/console doctrine:migrations:migrate -n" || ver >NUL

          rem Asset Map
          %COMPOSE% exec -T app sh -lc "php bin/console asset-map:compile"

          rem Tailwind (tolérant si pas nécessaire)
          %COMPOSE% exec -T app sh -lc "php bin/console tailwind:build" || ver >NUL
        '''
      }
    }

    stage('Smoke test'){
      steps {
        bat '''
          %COMPOSE% ps
          %COMPOSE% exec -T app sh -lc "php bin/console dbal:run-sql \\\"SELECT 1\\\"" 
        '''
      }
    }
  }

  post {
    always {
      bat '%COMPOSE% logs --no-color > compose.log || ver >NUL'
      archiveArtifacts artifacts: 'compose.log', fingerprint: false
    }
  }
}
