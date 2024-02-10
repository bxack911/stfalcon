#!/bin/bash

# *** STYLESHEETS ***
RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'
BOLD=$(tput bold) #
NORMAL=$(tput sgr0)

NOW=$(date +"%Y-%m-%d %H:%M:%S")
APP_DIR="./"

if [ ! -e "./.env.local"  ]; then cp -n ./.env.local.example .env.local ; fi
source .env

getDockerComposeCommandName() {
  COMMAND=''

  docker compose version >/dev/null 2>&1
  RETURN_CODE=$?
  if [ $RETURN_CODE -eq 0 ]; then
    COMMAND='docker compose'
  fi

  docker-compose --version >/dev/null 2>&1
  RETURN_CODE=$?
  if [ -z "$COMMAND" ] && [ $RETURN_CODE -eq 0 ]; then
    COMMAND='docker-compose'
  fi

  echo "$COMMAND"
}

while getopts "hiusdpcm" optname;
do
  case $optname in
    "h")
        echo "Allow to run useful commands"
        echo
        echo "  -h Help documentation"
        echo "  -i Install the application"
        echo
        echo "  -u Docker Up"
        echo "  -s Docker Stop"
        echo "  -d Docker Down"
        echo "  -p Docker ps"
        echo
        echo "  -c Cache clear"
        echo "  -m Migrations up"
        ;;
    "i")
        echo -e
        echo -e "${GREEN}${BOLD}Welcome to the app installation. Follow instructions to run application on your machine ${NOW} ${NC}"

        echo -e "Docker will be restarted if it is up."
        echo -e "Make sure that your are on the master branch."
        read -p "Are you sure you need to run it? (y/n) `echo $'\n> '`" AGREEMENT
        if [[ $AGREEMENT != 'y' ]];
          then exit 1;
        fi
        echo -e

        cp -n ./.env.local.example ./.env.local &&
        echo -e "${BOLD}Configure environment parameters on your needs in .env file${NC}${NORMAL}" &&
        read -p "Press 'Enter' after complete" &&
        source .env &&
        echo -e "${GREEN}OK${NORMAL}" &&
        echo &&

        echo -e "${BOLD}Creating composer cache directory${NC}${NORMAL}" &&
        cd docker &&
        mkdir composer_cache &&
        cd ../
        echo -e "${GREEN}Done${NORMAL}" &&
        echo &&

        echo -e "${BOLD}Docker compose up${NC}${NORMAL}" &&
        ./run.sh -u &&
        echo -e "${GREEN}Done${NORMAL}" &&
        echo &&

        echo -e "${GREEN}Composer installation${NORMAL}" &&
        $(getDockerComposeCommandName) exec php composer install &&
        $(getDockerComposeCommandName) exec php composer dump-env &&

        EXEC_TIME=$(printf "%dh:%dm:%ds\n" $((SECONDS/3600)) $((SECONDS%3600/60)) $((SECONDS%60))) &&
        NOW=$(date +"%Y-%m-%d %H:%M:%S") &&

        echo -e "${GREEN}${BOLD}Setup script finished at ${NOW} ${NORMAL}" &&
        echo -e "${BOLD}Total execution time: ${EXEC_TIME}${NORMAL}" &&
        echo -e "${BOLD}Run bin/console app:parse-bank --bank=mono to parse exchange rates from monobank" &&
        echo -e &&
        $(getDockerComposeCommandName) exec php bash
        ;;
      #
    "c")
        echo -e "${BOLD}Cache clear${NC}${NORMAL}"
        docker exec -t  stfalcon-php bin/console cache:clear --env=${APP_ENV}
        echo -e "${GREEN}Done${NORMAL}"
        echo
        ;;
    "u")
      echo -e "${BOLD}Docker Compose Up${NC}${NORMAL}"
      $(getDockerComposeCommandName) up -d && \
      echo -e "${GREEN}Done${NORMAL}"
      ;;
    "s")
      echo -e "${BOLD}Docker Compose Stop${NC}${NORMAL}"
      $(getDockerComposeCommandName) stop && \
      echo -e "${GREEN}Done${NORMAL}"
      ;;
    "d")
      echo -e "${BOLD}Docker Compose Down${NC}${NORMAL}"
      $(getDockerComposeCommandName) down && \
      echo -e "${GREEN}Done${NORMAL}"
      ;;
    "p")
      echo -e "${BOLD}Docker Compose PS${NC}${NORMAL}"
      $(getDockerComposeCommandName) ps
      ;;
    *)
      echo -e "${RED}${BOLD}Unknown error while processing options${NORMAL}${NC}"
      ;;
  esac
done

