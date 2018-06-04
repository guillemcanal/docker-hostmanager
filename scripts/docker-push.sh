#!/usr/bin/env bash

set -e

main() {
    local DOCKER_TAG="${1:-latest}"
    [ -n "$DOCKER_USERNAME" ] || { echo 'DOCKER_USERNAME need to be declared'; exit 1; }
    [ -n "$DOCKER_PASSWORD" ] || { echo 'DOCKER_PASSWORD need to be declared'; exit 1; }

    echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
    docker build -t gcanal/docker-hostmanager:$DOCKER_TAG .
    docker push gcanal/docker-hostmanager:$DOCKER_TAG
}

main "$@"
