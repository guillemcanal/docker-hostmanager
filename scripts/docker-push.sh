#!/usr/bin/env bash

set -e

main() {
    local PROJECT_NAME="docker-hostmanager"
    [ -n "$DOCKER_USERNAME" ] || { echo 'DOCKER_USERNAME need to be declared'; exit 1; }
    [ -n "$DOCKER_PASSWORD" ] || { echo 'DOCKER_PASSWORD need to be declared'; exit 1; }

    echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin

    docker build -t $DOCKER_USERNAME/$PROJECT_NAME:latest .
    docker push $DOCKER_USERNAME/$PROJECT_NAME:latest

    # Tag image
    if [ -n "$TRAVIS_TAG" ];then
        # Extract major, minor and revision from tag
        local PARTS=( ${TRAVIS_TAG//./ } )
        local MAJOR=${PARTS[0]}
        local MINOR=${PARTS[1]}
        local REVISION=${PARTS[2]}
        
        docker tag $DOCKER_USERNAME/$PROJECT_NAME:latest $DOCKER_USERNAME/$PROJECT_NAME:$MAJOR
        docker tag $DOCKER_USERNAME/$PROJECT_NAME:latest $DOCKER_USERNAME/$PROJECT_NAME:$MAJOR.$MINOR
        docker tag $DOCKER_USERNAME/$PROJECT_NAME:latest $DOCKER_USERNAME/$PROJECT_NAME:$MAJOR.$MINOR.$REVISION

        docker push $DOCKER_USERNAME/$PROJECT_NAME:$MAJOR
        docker push $DOCKER_USERNAME/$PROJECT_NAME:$MAJOR.$MINOR
        docker push $DOCKER_USERNAME/$PROJECT_NAME:$MAJOR.$MINOR.$REVISION
    fi
}

main "$@"
